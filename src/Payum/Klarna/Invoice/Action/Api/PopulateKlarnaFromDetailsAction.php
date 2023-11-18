<?php

namespace Payum\Klarna\Invoice\Action\Api;

use KlarnaAddr;
use KlarnaFlags;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;

class PopulateKlarnaFromDetailsAction implements ActionInterface
{
    /**
     * @param PopulateKlarnaFromDetails $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());
        $klarna = $request->getKlarna();

        if ($details['articles']) {
            foreach ($details['articles'] as $article) {
                $article = ArrayObject::ensureArrayObject($article);

                $klarna->addArticle(
                    mb_convert_encoding((string) $article['qty'], 'ISO-8859-1'),
                    mb_convert_encoding((string) $article['artNo'], 'ISO-8859-1'),
                    mb_convert_encoding((string) $article['title'], 'ISO-8859-1'),
                    mb_convert_encoding((string) $article['price'], 'ISO-8859-1'),
                    mb_convert_encoding((string) $article['vat'], 'ISO-8859-1'),
                    mb_convert_encoding((string) $article['discount'], 'ISO-8859-1'),
                    $article['flags'] ?: KlarnaFlags::NO_FLAG
                );
            }
        }

        if ($details['partial_articles']) {
            foreach ($details['partial_articles'] as $article) {
                $klarna->addArtNo(
                    mb_convert_encoding((string) $article['qty'], 'ISO-8859-1'),
                    mb_convert_encoding((string) $article['artNo'], 'ISO-8859-1')
                );
            }
        }

        if ($details['shipping_address']) {
            $address = ArrayObject::ensureArrayObject($details['shipping_address']);

            $klarna->setAddress(KlarnaFlags::IS_SHIPPING, new KlarnaAddr(
                mb_convert_encoding((string) $address['email'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['telno'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['cellno'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['fname'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['lname'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['careof'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['street'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['zip'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['city'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['country'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['house_number'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['house_extension'], 'ISO-8859-1')
            ));
        }

        if ($details['billing_address']) {
            $address = ArrayObject::ensureArrayObject($details['billing_address']);

            $klarna->setAddress(KlarnaFlags::IS_BILLING, new KlarnaAddr(
                mb_convert_encoding((string) $address['email'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['telno'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['cellno'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['fname'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['lname'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['careof'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['street'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['zip'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['city'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['country'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['house_number'], 'ISO-8859-1'),
                mb_convert_encoding((string) $address['house_extension'], 'ISO-8859-1')
            ));
        }

        if ($details['estore_info']) {
            $estoreInfo = ArrayObject::ensureArrayObject($details['estore_info']);

            $klarna->setEstoreInfo(
                mb_convert_encoding((string) $estoreInfo['order_id1'], 'ISO-8859-1'),
                mb_convert_encoding((string) $estoreInfo['order_id2'], 'ISO-8859-1'),
                mb_convert_encoding((string) $estoreInfo['username'], 'ISO-8859-1')
            );
        }

        $klarna->setComment(mb_convert_encoding((string) $details['comment'], 'ISO-8859-1'));
    }

    public function supports($request)
    {
        return $request instanceof PopulateKlarnaFromDetails;
    }
}
