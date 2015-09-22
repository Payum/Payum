<?php
namespace Payum\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpControllerInterface
{
    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function authorize(ServerRequestInterface $request);

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function capture(ServerRequestInterface $request);

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function refund(ServerRequestInterface $request);

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function sync(ServerRequestInterface $request);

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function notify(ServerRequestInterface $request);

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function notifyUnsafe(ServerRequestInterface $request);
}
