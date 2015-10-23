# Custom purchase examples

## Configure

```php
<?php
namespace Acme\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\ArrayObject;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class PaymentDetails extends ArrayObject
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer $id
     */
    protected $id;
}
```

```yml
#app/config/config.yml

payum:
    storages:
        Acme\PaymentBundle\Entity\PaymentDetails: { doctrine: orm }
```

_**Note**: You should use commented path if you install payum/payum package._

* [Paypal express checkout](custom_purchase_examples/paypal_express_checkout.md).
* [Paypal pro checkout](custom_purchase_examples/paypal_pro_checkout.md).
* [Payex](custom_purchase_examples/payex.md).
* [Authorize.Net AIM](custom_purchase_examples/authorize_net_aim.md).
* [Be2Bill credit card](custom_purchase_examples/be2bill.md).
* [Be2Bill onsite](custom_purchase_examples/be2bill_onsite.md).
* [Klarna Checkout](custom_purchase_examples/klarna_checkout.md).
* [Klarna Invoice](custom_purchase_examples/klarna_invoice.md).
* [Stripe.Js](custom_purchase_examples/stripe_js.md).
* [Stripe Checkout](custom_purchase_examples/stripe_checkout.md).
* [Stripe Direct (via omnipay)](custom_purchase_examples/stripe_via_omnipay.md).
* [Paypal express checkout (via omnipay)](custom_purchase_examples/paypal_via_omnipay.md).
* [JMS payment plugins](https://github.com/Payum/JMSPaymentBridge/blob/master/docs/get-it-started.md).