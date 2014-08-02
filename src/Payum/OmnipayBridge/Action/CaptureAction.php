<?php
namespace Payum\OmnipayBridge\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\PaymentAwareInterface;
use Payum\Core\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Security\SensitiveValue;

class CaptureAction extends BaseApiAwareAction implements PaymentAwareInterface
{
    /**
     * @var PaymentInterface
     */
    protected $payment;

    /**
     * {@inheritDoc}
     */
    public function setPayment(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        if (!$this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['_status']) {
            return;
        }

        if (false == $model->validateNotEmpty(array('card'), false)) {
            try {
                $creditCardRequest = new ObtainCreditCard;
                $this->payment->execute($creditCardRequest);
                $card = $creditCardRequest->obtain();

                $firstName = $lastName = '';
                list($firstName, $lastName) = explode(' ', $card->getHolder(), 1);

                $model['card'] = new SensitiveValue(array(
                    'number' => $card->getNumber(),
                    'cvv' => $card->getSecurityCode(),
                    'expiryMonth' => $card->getExpireAt()->format('m'),
                    'expiryYear' => $card->getExpireAt()->format('y'),
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                ));
            } catch (RequestNotSupportedException $e) {
                throw new LogicException('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCardRequest request.');
            }
        }

        $response = $this->gateway->purchase($model->toUnsafeArray())->send();

        $model['_reference']      = $response->getTransactionReference();
        $model['_status']         = $response->isSuccessful() ? 'success' : 'failed';
        $model['_status_code']    = $response->getCode();
        $model['_status_message'] = $response->isSuccessful() ? '' : $response->getMessage();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
