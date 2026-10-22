<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

use LogicException;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Action\RenderTemplateAction;
use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction as PlainPhpGetHttpRequestAction;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction as TwigRenderTemplateAction;
use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Exception\CommandNotSupportedException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayInterface;
use Payum\Core\Legacy\LegacyGatewayAdapter;
use Payum\Core\Legacy\LegacyReply;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\PayumBuilder;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\Result;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Throwable;
use function array_filter;
use function array_key_exists;
use function array_shift;
use function explode;
use function file_get_contents;
use function is_a;
use function is_string;
use function json_decode;
use function sprintf;

/**
 * Replays a gateway's scenarios on 2.x and compares them with what 1.x did.
 *
 * The weak reading drives the gateway with 1.x requests, unmodified. The strong reading drives the same
 * gateway with 2.x commands through {@see LegacyGatewayAdapter} and expects the result to say what the
 * 1.x reply and status said. Both compare against the golden transcript recorded on 1.x; see README.md.
 */
abstract class BcTestCase extends TestCase
{
    /**
     * Core answers GetHttpRequest and RenderTemplate from the PSR-7 request and the renderer it injects,
     * with its own actions in place of the PlainPhp and Twig ones. What they answer is compared by the
     * scenarios that dispatch them; only the class names differ.
     */
    private const REPLACED_ACTIONS = [
        PlainPhpGetHttpRequestAction::class => GetHttpRequestAction::class,
        TwigRenderTemplateAction::class => RenderTemplateAction::class,
    ];

    /**
     * @dataProvider provideScenarios
     */
    public function testWeakReading(Scenario $scenario): void
    {
        $this->assertSame(
            $this->expected($scenario),
            (new Recorder(static::register()))->record($scenario),
            sprintf('%s drifted from 1.x when driven by 1.x requests.', $scenario->name),
        );
    }

    /**
     * @dataProvider provideScenariosWithOperations
     */
    public function testStrongReading(Scenario $scenario): void
    {
        $expected = [];

        foreach ($this->expected($scenario) as $step) {
            if (isset($step['outcome'])) {
                $step['outcome'] = self::resultFor($step['op'], $step['outcome'], $step['human']);
                unset($step['writes']);
            }

            $expected[] = $step;
        }

        $actual = (new Recorder(static::register(), static::dispatch(...)))->record($scenario);

        foreach ($actual as $i => $step) {
            unset($actual[$i]['writes']);
        }

        $this->assertSame($expected, $actual, sprintf('%s drifted from 1.x when driven by 2.x commands.', $scenario->name));
    }

    /**
     * @return iterable<string, array{Scenario}>
     */
    public static function provideScenarios(): iterable
    {
        foreach (self::load() as $scenario) {
            yield $scenario->name => [$scenario];
        }
    }

    /**
     * @return iterable<string, array{Scenario}>
     */
    public static function provideScenariosWithOperations(): iterable
    {
        foreach (self::load() as $scenario) {
            $operations = array_filter($scenario->steps, static fn (array $step): bool => isset(Recorder::REQUESTS[$step['op']]));

            if ([] !== $operations) {
                yield $scenario->name => [$scenario];
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function dispatch(GatewayInterface $gateway, string $op, object $target): array
    {
        $command = self::commandFor($op, $target);

        try {
            $result = static::executeCommand($gateway, $command);
        } catch (Throwable $exception) {
            return Recorder::exception($exception);
        }

        return self::describe($result);
    }

    /**
     * How the strong reading dispatches a command.
     *
     * @param CommandInterface<Result> $command
     */
    protected static function executeCommand(GatewayInterface $gateway, CommandInterface $command): Result
    {
        return LegacyGatewayAdapter::wrap($gateway)->execute($command);
    }

    /**
     * The name of the file under Scenarios/ and golden/.
     */
    abstract protected static function scenarios(): string;

    /**
     * Differences from 1.x that are known and accepted. Each one is listed in the exception list that
     * feeds UPGRADE.md.
     *
     * @return array<string, array<int, array<string, mixed>>> scenario => step => dotted path => what 2.x does instead
     */
    protected static function accepted(): array
    {
        return [];
    }

    /**
     * Registers the gateway under test as {@see Recorder::GATEWAY}, or null for the factory the scenario
     * names.
     *
     * @return (callable(PayumBuilder, Scenario, MockHttpClient): void)|null
     */
    protected static function register(): ?callable
    {
        return null;
    }

    /**
     * @return list<Scenario>
     */
    private static function load(): array
    {
        /** @var list<Scenario> */
        return require __DIR__ . '/Scenarios/' . static::scenarios() . '.php';
    }

    /**
     * The golden transcript, with the accepted differences applied.
     *
     * @return list<array<string, mixed>>
     */
    private function expected(Scenario $scenario): array
    {
        $golden = json_decode((string) file_get_contents(__DIR__ . '/golden/' . static::scenarios() . '.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey($scenario->name, $golden['scenarios'], sprintf('%s has no golden transcript. Record one with record.php.', $scenario->name));

        $steps = $golden['scenarios'][$scenario->name];

        foreach ($steps as $i => $step) {
            if ('select' === $step['op']) {
                $steps[$i]['actions'] = self::renameActions($step['actions']);
                $steps[$i]['selected'] = self::renameActions($step['selected']);
            }
        }

        foreach (static::accepted()[$scenario->name] ?? [] as $i => $paths) {
            foreach ($paths as $path => $value) {
                $steps[$i] = self::replace($steps[$i], explode('.', $path), $value, $scenario->name . '#' . $i . ' ' . $path);
            }
        }

        return $steps;
    }

    /**
     * @param array<array-key, string|null> $actions
     *
     * @return array<array-key, string|null>
     */
    private static function renameActions(array $actions): array
    {
        foreach ($actions as $key => $action) {
            $actions[$key] = self::REPLACED_ACTIONS[$action] ?? $action;
        }

        return $actions;
    }

    /**
     * @param array<string, mixed> $array
     * @param list<string> $keys
     *
     * @return array<string, mixed>
     */
    private static function replace(array $array, array $keys, mixed $value, string $where): array
    {
        $key = array_shift($keys);

        if (! array_key_exists($key, $array)) {
            throw new LogicException(sprintf('Accepted difference %s points at nothing in the golden transcript.', $where));
        }

        $array[$key] = [] === $keys ? $value : self::replace($array[$key], $keys, $value, $where);

        return $array;
    }

    /**
     * What a 2.x caller should get for what a 1.x caller got.
     *
     * @param array<string, mixed> $outcome
     *
     * @return array<string, mixed>
     */
    private static function resultFor(string $op, array $outcome, mixed $human): array
    {
        if ('exception' === $outcome['type']) {
            // Nothing claiming the operation itself is a command the gateway does not support. A
            // sub-request going unanswered is the gateway's own problem, and stays what it was.
            if (RequestNotSupportedException::class === $outcome['class'] && Recorder::REQUESTS[$op] === ($outcome['request'] ?? null)) {
                return [
                    'type' => 'exception',
                    'class' => CommandNotSupportedException::class,
                ];
            }

            return $outcome;
        }

        $result = [
            'type' => 'result',
            'status' => is_string($human) ? $human : null,
        ];

        if ('notify' === $op) {
            return $result + [
                'acknowledgement' => 'reply' === $outcome['type'] && is_a($outcome['class'], HttpResponse::class, true)
                    ? [
                        'status' => $outcome['statusCode'],
                        'body' => $outcome['content'],
                        'headers' => $outcome['headers'],
                    ]
                    : null,
            ];
        }

        $next = match (true) {
            'none' === $outcome['type'] => null,
            HttpRedirect::class === $outcome['class'] => [
                'class' => Redirect::class,
                'url' => $outcome['url'],
                'statusCode' => $outcome['statusCode'],
            ],
            HttpPostRedirect::class === $outcome['class'] => [
                'class' => PostRedirect::class,
                'url' => $outcome['url'],
                'fields' => $outcome['fields'],
            ],
            default => [
                'class' => LegacyReply::class,
                'reply' => $outcome,
            ],
        };

        return $result + [
            'next' => $next,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function describe(Result $result): array
    {
        $described = [
            'type' => 'result',
            'status' => $result->status?->value,
        ];

        if ($result instanceof NotifyResult) {
            return $described + [
                'acknowledgement' => null === $result->acknowledgement ? null : [
                    'status' => $result->acknowledgement->status,
                    'body' => $result->acknowledgement->body,
                    'headers' => Recorder::plain($result->acknowledgement->headers),
                ],
            ];
        }

        $next = $result->next;

        return $described + [
            'next' => match (true) {
                ! $next instanceof NextAction => null,
                $next instanceof Redirect => [
                    'class' => Redirect::class,
                    'url' => $next->url,
                    'statusCode' => $next->statusCode,
                ],
                $next instanceof PostRedirect => [
                    'class' => PostRedirect::class,
                    'url' => $next->url,
                    'fields' => Recorder::plain($next->fields),
                ],
                $next instanceof LegacyReply => [
                    'class' => LegacyReply::class,
                    'reply' => Recorder::reply($next->reply),
                ],
                default => [
                    'class' => $next::class,
                ],
            },
        ];
    }

    /**
     * @return CommandInterface<Result>
     */
    private static function commandFor(string $op, object $target): CommandInterface
    {
        $class = match ($op) {
            'capture' => CaptureCommand::class,
            'authorize' => AuthorizeCommand::class,
            'refund' => RefundCommand::class,
            'cancel' => CancelCommand::class,
            'sync' => SyncCommand::class,
            'notify' => NotifyCommand::class,
            default => throw new LogicException(sprintf('There is no command for the %s step.', $op)),
        };

        return match (true) {
            $target instanceof TokenInterface => $class::forToken($target),
            $target instanceof PaymentInterface => $class::forPayment($target),
            default => throw new LogicException(sprintf('A %s step runs on a token or a payment.', $op)),
        };
    }
}
