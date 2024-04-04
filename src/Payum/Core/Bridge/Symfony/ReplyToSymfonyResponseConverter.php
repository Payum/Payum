<?php

namespace Payum\Core\Bridge\Symfony;

use Payum\Core\Bridge\Symfony\Reply\HttpResponse as SymfonyHttpResponse;
use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use ReflectionObject;
use Symfony\Component\HttpFoundation\Response;
use function trigger_error;

@trigger_error('The ' . __NAMESPACE__ . '\ReplyToSymfonyResponseConverter class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
class ReplyToSymfonyResponseConverter
{
    /**
     * @return Response
     */
    public function convert(ReplyInterface $reply)
    {
        if ($reply instanceof SymfonyHttpResponse) {
            return $reply->getResponse();
        } elseif ($reply instanceof HttpResponse) {
            $headers = $reply->getHeaders();
            $headers['X-Status-Code'] = $reply->getStatusCode();

            return new Response($reply->getContent(), $reply->getStatusCode(), $headers);
        }

        $ro = new ReflectionObject($reply);

        throw new LogicException(
            sprintf('Cannot convert reply %s to http response.', $ro->getShortName()),
            0,
            $reply
        );
    }
}
