## Capture simple controller

This chapter described how to create capture controller to manage your payments. 
These examples were taken from [sandbox](https://github.com/Payum/PayumBundleSandbox) app.

### Step 1. Create controller

Create a controller in your bundle. 

```php
<?php
//src/Acme/PaymentBundle/Controller
namespace Acme\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Payum\Bundle\PayumBundle\Context\ContextRegistry;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;

class CaptureController extends Controller
{
    public function simpleCaptureAction($contextName, $model)
    {
        $context = $this->getPayum()->getContext($contextName);

        $captureRequest = new CaptureRequest($model);
        $context->getPayment()->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $context->getPayment()->execute($statusRequest);

        return $this->render('AcmePaymentBundle:Capture:simpleCapture.html.twig', array(
            'status' => $statusRequest
        ));
    }

    /**
     * @return ContextRegistry
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }
}
```

_**Note:** You may want to adapt namespace._

### Step 2. Create a view

```twig    
<div class="block">
    Payment status:
    {% if status.success %}
        Successfully finished!
    {% elseif status.canceled %}
        Canceled!
    {% elseif status.failed %}
        Failed!
    {% elseif status.inProgress %}
        In progress!
    {% elseif status.inProgress %}
        Status unknown!
    {% endif %}
</div>

<div class="block">
    <ul>
        {% for key, value in status.model %}
            <li>{{ key }}: {{ value }}</li>
        {% endfor %}
    </ul>
</div>
```

_**Note:** You may want to do a redirect somewhere instead of rendering a page._

### Step 3. Add route for the controller. 

Now that you have created a capture controller, all that is left to do is to add routing for it.

* In yaml:

    ```yaml
    # app/config/routing.yml
    acme_payment_capture_simple:
        pattern:  /payment/simple_purchase/{contextName}/capture/{model}
        defaults: { _controller: AcmePaymentBundle:Capture:simpleCapture }
    ```

* or, if you prefer XML:

    ```xml
    <!-- app/config/routing.xml -->
    <?xml version="1.0" encoding="UTF-8" ?>

    <routes xmlns="http://symfony.com/schema/routing"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

        <route id="acme_payment_capture_simple" path="/payment/simple_purchase/{contextName}/capture/{model}">
            <default key="_controller">AcmePaymentBundle:Capture:simpleCapture</default>
        </route>
    </routes>
    ```

### Step 4. Use it!

* You can do a Kernel::forward

    ```php
    <?php
    
    return $this->forward('AcmePaymentBundle:Capture:simpleCapture', array(
        'contextName' => 'aContextName',
        'model' => $instruction,
    ));
    ```
    
* or, Http redirect:

    ```php
    <?php
    
    return $this->redirect($this->generateUrl('acme_payment_capture_simple', array(
        'contextName' => 'aContextName',
        'model' => $instruction->getId(),
    ));
    ```

Back to [index](index.md).

### Other documents: 

* [Configuration reference](configuration_reference.md).