<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Invoice\Request\Api\ReserveAmount;

class ReserveAmountAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param ReserveAmount $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $klarna = $this->createKlarna();

        if ($details['articles']) {
            foreach ($details['articles'] as $article) {
                $article = ArrayObject::ensureArrayObject($article);

                $klarna->addArticle(
                    $article['qty'],
                    $article['artNo'],
                    $article['title'],
                    $article['price'],
                    $article['vat'],
                    $article['discount'],
                    $article['flags']
                );
            }
        }

        if ($details['shipping_address']) {
            $klarna->setAddress(\KlarnaFlags::IS_SHIPPING, new \KlarnaAddr(
                $details['email'],
                $details['telno'],
                $details['cellno'],
                $details['fname'],
                $details['lname'],
                $details['careof'],
                $details['street'],
                $details['zip'],
                $details['city'],
                $details['country'],
                $details['houseNo'],
                $details['houseExt']
            ));
        }

        if ($details['billing_address']) {
            $klarna->setAddress(\KlarnaFlags::IS_BILLING, new \KlarnaAddr(
                $details['email'],
                $details['telno'],
                $details['cellno'],
                $details['fname'],
                $details['lname'],
                $details['careof'],
                $details['street'],
                $details['zip'],
                $details['city'],
                $details['country'],
                $details['houseNo'],
                $details['houseExt']
            ));
        }

        try {
            $result = $klarna->reserveAmount($details['pno'], $details['gender'], $details['amount'], $details['reservation_flags']);

            $details['rno'] = $result[0];
            $details['status'] = $result[1];
        } catch (\KlarnaException $e) {
            $details['error_code'] = $e->getCode();
            $details['error_message'] = $e->getMessage();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ReserveAmount &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}