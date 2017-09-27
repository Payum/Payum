<?php
namespace Payum\Offline\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Request\Convert;
use Payum\Offline\Constants;

class ConvertPayoutAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PayoutInterface $payout */
        $payout = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payout->getDetails());
        $details['amount'] = $payout->getTotalAmount();
        $details['currency'] = $payout->getCurrencyCode();
        $details['description'] = $payout->getDescription();
        $details['recipient_email'] = $payout->getRecipientEmail();
        $details['recipient_id'] = $payout->getRecipientId();
        
        $details->defaults(array(
            Constants::FIELD_PAYOUT => true,
        ));

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PayoutInterface &&
            $request->getTo() == 'array'
        ;
    }
}
