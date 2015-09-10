<?php
namespace Payum\Be2Bill\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Core\Storage\StorageInterface;

class NotifyPaymentAction extends GatewayAwareAction
{
    /**
     * @var StorageInterface
     */
    protected $paymentStorage;

    /**
     * @var string
     */
    private $idField;

    /**
     * @param StorageInterface $paymentStorage
     * @param string $idField
     */
    public function __construct(StorageInterface $paymentStorage, $idField)
    {
        $this->paymentStorage = $paymentStorage;
        $this->idField = $idField;
    }

    /**
     * {@inheritDoc}
     *
     * @param $request Notify
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (empty($httpRequest->query['ORDERID'])) {
            throw new HttpResponse('The notification is invalid. Code 3', 400);
        }

        $payment = $this->paymentStorage->findBy([$this->idField => $httpRequest->query['ORDERID']]);
        if (null === $payment) {
            throw new HttpResponse('The notification is invalid. Code 4', 400);
        }

        $this->gateway->execute(new Notify($payment));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            null === $request->getModel()
        ;
    }
}