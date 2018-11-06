<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Payum Bundle. Creating custom view for payment page

Sometimes you may want to integrate the payment page into your own checkout/payment flow so it "blends" with other pages.

In such case you will most likely need to pass some extra data (arrays and/or objects) to the templating engine / payum controller.

For example - you may want to show some extra order details on the payment page so passing the "order" object would be a good idea.

In order to achieve that you must replace the default payum templates with your own and add the extra data to the data that is sent to the payum controller responsible for rendering the payment page. 

Edit your payum configuration file and add your templates configuration:

```php
    gateways:
        [...]
        your_gateway:
            [...]
            payum.template.layout: 'MyBundle:Default:myLayout.html.twig'
            payum.template.obtain_token: 'MyBundle:Default:payment.html.twig'
```
"myLayout.html.twig" will be most likely the main layout template for your site (or checkout).

The easiest way to create your own version of the "payment.html.twig" template is to make a copy of the original gateway template. For example - the template for Stripe_js can be found here (if you installed the package):

```php
[...]/vendor/payum/stripe/Payum/Stripe/Resources/views/Action/obtain_checkout_token.html
```
 
 ...so just copy it to payment.html.twig and add all the extra content you need there.
 
 The next step is creating an extension that will add your data to the data used when the view is created.
 
 ```php
 <?php
  
 namespace MyBundle\Extension;
   
 use Payum\Core\Extension\ExtensionInterface;
 use Payum\Core\Extension\Context;
 use Payum\Core\Request\RenderTemplate;
   
 class PayumOrderExtension implements ExtensionInterface
 {
     protected $data = [];
 
     public function __construct(array $data)
     {
         $this->data = $data;
     }
 
     public function onPreExecute(Context $context)
     {
         $request = $context->getRequest();
         if ($request instanceof RenderTemplate)
         {
             $request->addParameter('data', $this->data);
         }
     }
    [...]
 }
 ```
 
 Finally you need to add your data in controller:
 
 ```php
 <?php
     
     [...]
     
     /**
      * @Route(
      *     "/someorder/{orderUid}/payment",
      *     name = "someorder_payment",
      *     requirements = { "orderUid" = "^[A-Z0-9]+$" }
      * )
      */
     public function someorderPaymentAction(Request $request, $orderUid)
     {
         [...]
   
         $order = /* get your order from database ? */
   
         $payum = $this->get('payum');
   
         $gateway = $payum->getGateway('your_gateway');
  
         /** add $order and whatever else you may need to data */
         $gateway->addExtension(new PayumOrderExtension(
                array(
                    'order'         => $order,
                    'other_stuff'   => 'hi',
                    [...]
                )
             )
         );
   
         $storage = $payum->getStorage('[...]\OrderPayment');
         
         $payment = $storage->create();
         [...]
         $storage->update($payment);
 
         $captureToken = $this->get('payum')->getTokenFactory()->createCaptureToken(
             $order->getPaymentMethod(),
             $payment,
             'order_confirmation', /* your order confirmation route */
             array('orderUid' => $order->getUid())
         );
 
         $gateway->execute(new Capture($captureToken));
 
 ```

You should now be able to have your customized payment page...

* [Back to index](../index.md).