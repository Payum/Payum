<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\Gateway;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Request\Sync;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Storage\StorageInterface;
use ReflectionMethod;
use ReflectionProperty;
use Stripe\ApiRequestor;
use Throwable;
use Traversable;
use function basename;
use function class_exists;
use function get_debug_type;
use function glob;
use function is_array;
use function is_dir;
use function is_object;
use function is_scalar;
use function iterator_to_array;
use function json_decode;
use function json_encode;
use function lcfirst;
use function method_exists;
use function mkdir;
use function rmdir;
use function str_replace;
use function substr;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * Runs a {@see Scenario} against whichever Payum is autoloaded and returns what happened, in a shape that
 * can be written to JSON on 1.x and compared on 2.x.
 *
 * Only API that 1.7 and 2.x both have is used here: this file is loaded by `record.php` against a 1.x
 * install. Every step builds a fresh Payum, the way every HTTP hit builds one in an application, so
 * nothing carries over between steps except what storage persisted.
 */
final class Recorder
{
    public const GATEWAY = 'bc';

    /**
     * @var array<string, class-string>
     */
    public const REQUESTS = [
        'capture' => Capture::class,
        'authorize' => Authorize::class,
        'refund' => Refund::class,
        'cancel' => Cancel::class,
        'sync' => Sync::class,
        'notify' => Notify::class,
        'payout' => Payout::class,
    ];

    private string $dir;

    /**
     * @param (callable(PayumBuilder, Scenario, MockHttpClient): void)|null $register registers the gateway as {@see self::GATEWAY}
     * @param (callable(GatewayInterface, string, object): array<string, mixed>)|null $operate runs one operation step and describes the outcome
     */
    public function __construct(
        private $register = null,
        private $operate = null,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function record(Scenario $scenario): array
    {
        $this->dir = sys_get_temp_dir() . '/payum-bc-' . uniqid('', true);
        mkdir($this->dir);
        BcToken::$created = 0;

        $_SERVER = [
            'HTTP_HOST' => 'shop.test',
            'SERVER_NAME' => 'shop.test',
            'SERVER_PORT' => '80',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
            'SCRIPT_NAME' => '/index.php',
            'SCRIPT_FILENAME' => '/index.php',
            'PHP_SELF' => '/index.php',
        ];

        try {
            $payment = new BcPayment();
            $payment->setNumber('bc-' . $scenario->name);
            $payment->setTotalAmount($scenario->amount);
            $payment->setCurrencyCode($scenario->currency);
            $payment->setDescription('BC scenario ' . $scenario->name);
            $payment->setClientEmail('buyer@example.test');
            $payment->setClientId('buyer-1');
            $payment->setDetails($scenario->details);
            $this->paymentStorage()->update($payment);

            $transcript = [];
            /** @var array<string, string> $tokens */
            $tokens = [];

            foreach ($scenario->steps as $step) {
                $transcript[] = $this->step($scenario, $step, $tokens);
            }

            return $this->normalise($transcript);
        } finally {
            foreach (glob($this->dir . '/*/*') ?: [] as $file) {
                unlink($file);
            }

            foreach (glob($this->dir . '/*') ?: [] as $subdir) {
                rmdir($subdir);
            }

            rmdir($this->dir);
        }
    }

    /**
     * Requests core and every gateway are expected to have an opinion on.
     *
     * @return array<string, object>
     */
    public static function probes(): array
    {
        $payment = new BcPayment();
        $payment->setNumber('probe');
        $payment->setTotalAmount(100);
        $payment->setCurrencyCode('EUR');

        $probes = [];

        foreach (self::REQUESTS as $op => $class) {
            $probes[$op . '(details)'] = new $class(new ArrayObject());
            $probes[$op . '(payment)'] = new $class($payment);
        }

        return $probes + [
            'GetHumanStatus(details)' => new GetHumanStatus(new ArrayObject()),
            'GetHumanStatus(payment)' => new GetHumanStatus($payment),
            'GetBinaryStatus(details)' => new GetBinaryStatus(new ArrayObject()),
            'Convert(payment, array)' => new Convert($payment, 'array'),
            'GetHttpRequest' => new GetHttpRequest(),
            'GetCurrency' => new GetCurrency('EUR'),
            'GetToken' => new GetToken('hash'),
            'RenderTemplate' => new RenderTemplate('@PayumCore/layout.html.twig', []),
            'ObtainCreditCard' => new ObtainCreditCard(),
        ];
    }

    /**
     * Runs one operation through the 1.x request API.
     *
     * @return array<string, mixed>
     */
    public static function execute(GatewayInterface $gateway, string $op, object $target): array
    {
        $class = self::REQUESTS[$op];

        try {
            $gateway->execute(new $class($target));
        } catch (ReplyInterface $reply) {
            return self::reply($reply);
        } catch (Throwable $exception) {
            return self::exception($exception);
        }

        return [
            'type' => 'none',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function reply(ReplyInterface $reply): array
    {
        $described = [
            'type' => 'reply',
            'class' => $reply::class,
        ];

        foreach (['getStatusCode', 'getUrl', 'getFields', 'getHeaders', 'getContent', 'getPayload'] as $getter) {
            if (method_exists($reply, $getter)) {
                $described[lcfirst(substr($getter, 3))] = self::plain($reply->{$getter}());
            }
        }

        return $described;
    }

    /**
     * @return array<string, mixed>
     */
    public static function exception(Throwable $exception): array
    {
        $described = [
            'type' => 'exception',
            'class' => $exception::class,
        ];

        if ($exception instanceof RequestNotSupportedException && is_object($exception->getRequest())) {
            $described['request'] = $exception->getRequest()::class;
        }

        return $described;
    }

    /**
     * Details as JSON can hold them. Objects are named rather than dumped: what matters is that a
     * SensitiveValue or a token is still there, not its internals.
     *
     * @return mixed
     */
    public static function plain(mixed $value)
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_array($value)) {
            $plain = [];

            foreach ($value as $key => $item) {
                $plain[$key] = self::plain($item);
            }

            return $plain;
        }

        if (null === $value || is_scalar($value)) {
            return $value;
        }

        return [
            '@object' => get_debug_type($value),
        ];
    }

    /**
     * @param array<string, mixed> $step
     * @param array<string, string> $tokens
     *
     * @return array<string, mixed>
     */
    private function step(Scenario $scenario, array $step, array &$tokens): array
    {
        $op = $step['op'];

        $_GET = $step['get'] ?? [];
        $_POST = $step['post'] ?? [];
        $_REQUEST = $_GET + $_POST;
        $_SERVER['REQUEST_METHOD'] = [] === $_POST ? 'GET' : 'POST';

        $http = new MockHttpClient($step['http'] ?? []);
        $stripe = class_exists(ApiRequestor::class) ? new StripeMockClient($step['stripe'] ?? []) : null;

        if ($stripe instanceof StripeMockClient) {
            ApiRequestor::setHttpClient($stripe);
        }

        $payum = $this->payum($scenario, $http);
        $gateway = $payum->getGateway(self::GATEWAY);

        $record = [
            'op' => $op,
        ];

        if ('select' === $op) {
            return $record + [
                'actions' => $this->actionsOf($gateway),
                'extensions' => $this->extensionsOf($gateway),
                'selected' => $this->select($gateway, $scenario),
            ];
        }

        if ('status' === $op) {
            return $record + [
                'details' => $step['details'],
            ] + $this->statusOf($gateway, new ArrayObject($step['details']));
        }

        BcPayment::$writes = 0;

        $on = $step['on'] ?? 'payment';
        $target = 'token' === $on
            ? $this->token($payum, $scenario, $op, $tokens)
            : $payum->getStorage(BcPayment::class)->find('bc-' . $scenario->name);

        $record['on'] = $on;
        $record['outcome'] = ($this->operate ?? self::execute(...))($gateway, $op, $target);
        $record['writes'] = BcPayment::$writes;
        $record['http'] = $http->sent;

        if ($stripe instanceof StripeMockClient) {
            $record['stripe'] = $stripe->sent;
        }

        // Read back from disk in a fresh Payum, which is what the next hit would see.
        $reloaded = $this->payum($scenario, new MockHttpClient([]));
        /** @var BcPayment $stored */
        $stored = $reloaded->getStorage(BcPayment::class)->find('bc-' . $scenario->name);

        $record['details'] = self::plain($stored->getDetails());

        return $record + $this->statusOf($reloaded->getGateway(self::GATEWAY), new ArrayObject(self::plain($stored->getDetails())));
    }

    /**
     * @return array<string, mixed>
     */
    private function statusOf(GatewayInterface $gateway, ArrayObject $details): array
    {
        $described = [];

        foreach ([
            'human' => new GetHumanStatus($details),
            'binary' => new GetBinaryStatus($details),
        ] as $key => $status) {
            try {
                $gateway->execute($status);
                $described[$key] = $status->getValue();
            } catch (Throwable $exception) {
                $described[$key] = self::exception($exception);
            }
        }

        return $described;
    }

    /**
     * @return list<string>
     */
    private function actionsOf(GatewayInterface $gateway): array
    {
        $actions = (new ReflectionProperty(Gateway::class, 'actions'))->getValue($gateway);
        $classes = [];

        foreach ($actions as $action) {
            $classes[] = is_object($action) ? $action::class : $action;
        }

        return $classes;
    }

    /**
     * @return list<string>
     */
    private function extensionsOf(GatewayInterface $gateway): array
    {
        $collection = (new ReflectionProperty(Gateway::class, 'extensions'))->getValue($gateway);
        $classes = [];

        foreach ((new ReflectionProperty(ExtensionCollection::class, 'extensions'))->getValue($collection) as $extension) {
            $classes[] = $extension::class;
        }

        return $classes;
    }

    /**
     * @return array<string, string|null>
     */
    private function select(GatewayInterface $gateway, Scenario $scenario): array
    {
        $find = new ReflectionMethod(Gateway::class, 'findActionSupported');
        $probes = self::probes();

        foreach ($scenario->probes as $probe) {
            $probes[basename(str_replace('\\', '/', $probe[0]))] = new $probe[0](new ArrayObject($probe[1] ?? []));
        }

        $selected = [];

        foreach ($probes as $name => $request) {
            $action = $find->invoke($gateway, $request);
            $selected[$name] = $action ? $action::class : null;
        }

        return $selected;
    }

    /**
     * @param Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>> $payum
     * @param array<string, string> $tokens
     */
    private function token(Payum $payum, Scenario $scenario, string $op, array &$tokens): TokenInterface
    {
        if (! isset($tokens[$op])) {
            $payment = $payum->getStorage(BcPayment::class)->find('bc-' . $scenario->name);
            $factory = $payum->getTokenFactory();

            $token = match ($op) {
                'capture' => $factory->createCaptureToken(self::GATEWAY, $payment, 'done.php'),
                'authorize' => $factory->createAuthorizeToken(self::GATEWAY, $payment, 'done.php'),
                'refund' => $factory->createRefundToken(self::GATEWAY, $payment, 'done.php'),
                'notify' => $factory->createNotifyToken(self::GATEWAY, $payment),
                default => $factory->createToken(self::GATEWAY, $payment, $op . '.php'),
            };

            $tokens[$op] = $token->getHash();
        }

        return $payum->getTokenStorage()->find($tokens[$op]);
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function payum(Scenario $scenario, MockHttpClient $http): Payum
    {
        $builder = (new PayumBuilder())
            ->setTokenStorage(new BcTokenStorage($this->subdir('tokens')))
            ->addStorage(BcPayment::class, $this->paymentStorage())
        ;

        if (null !== $scenario->factoryClass) {
            $class = $scenario->factoryClass;
            $builder->addGatewayFactory($scenario->factory, static fn (array $config, $core) => new $class($config, $core));
        }

        if (null !== $this->register) {
            ($this->register)($builder, $scenario, $http);
        } else {
            $builder->addGateway(self::GATEWAY, [
                'factory' => $scenario->factory,
                'payum.http_client' => $http,
            ] + $scenario->config);
        }

        return $builder->getPayum();
    }

    /**
     * @return FilesystemStorage<object>
     */
    private function paymentStorage(): FilesystemStorage
    {
        return new FilesystemStorage($this->subdir('payments'), BcPayment::class, 'number');
    }

    private function subdir(string $name): string
    {
        $dir = $this->dir . '/' . $name;

        if (! is_dir($dir)) {
            mkdir($dir);
        }

        return $dir;
    }

    /**
     * What the transcript reads as once written to JSON and back, which is what the golden file holds.
     *
     * @param list<array<string, mixed>> $transcript
     *
     * @return list<array<string, mixed>>
     */
    private function normalise(array $transcript): array
    {
        return json_decode(json_encode($transcript, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }
}
