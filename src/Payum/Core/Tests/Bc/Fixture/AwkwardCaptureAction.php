<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\RenderTemplate;

/**
 * Writes what every information request answered into the details, so the transcript compares them, then
 * redirects through a sub-request and writes to the details again on the way out.
 */
final class AwkwardCaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Capture $request */
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($http = new GetHttpRequest());
        $model['http'] = [
            'method' => $http->method,
            'uri' => $http->uri,
            'query' => $http->query,
            'request' => $http->request,
            'client_ip' => $http->clientIp,
        ];

        $this->gateway->execute($currency = new GetCurrency($model['currency']));
        $model['currency_numeric'] = $currency->numeric;
        $model['currency_exp'] = $currency->exp;

        if ($request->getToken()) {
            $this->gateway->execute($token = new GetToken($request->getToken()->getHash()));
            $model['token_target'] = $token->getToken()->getTargetUrl();
        }

        $this->gateway->execute($template = new RenderTemplate('@AwkwardBc/form.html.twig', [
            'amount' => $model['amount'],
        ]));
        $model['form'] = $template->getResult();

        if ($http->query['psp_ref'] ?? null) {
            $model['status'] = 'captured';

            return;
        }

        $model['psp_ref'] = 'psp-1';
        $model['status'] = 'pending';

        try {
            $this->gateway->execute(new AwkwardRedirect($model));
        } catch (ReplyInterface $reply) {
            $model['left_for_psp'] = true;

            throw $reply;
        }
    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getModel() instanceof ArrayAccess;
    }
}
