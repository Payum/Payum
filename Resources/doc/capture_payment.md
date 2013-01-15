## Capture payment

### Step 1. Simple pay model

```php
<?php
namespace AcmeDemoBundle\Controller;

use Payum\Domain\SimpleSell;

class PaymentCapture extends Controller
{
    public function simpleSellAction()
    {
        $storage = $this->get('payum')->getContext('theContextName')->getStorage();
    
        $simpleSell = $storage->createModel();
        $simpleSell->setCurrency('EUR');
        $simpleSell->setPrice(100.05);

        $storage->updateModel($simpleSell);
        
        return $this->forward('PayumBundle:Capture:do', array(
            'contextName' => 'theContextName',
            'modelId' => $simpleSell->getId()
        ));
    }
} 
```

That's it!