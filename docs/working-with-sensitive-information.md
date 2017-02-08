# Working with sensitive information

All sensitive information (credit card number, cvv, card owner name etc) should be passed directly to a gateway.
It is not allowed to store such information even temporally.
If you want to store it you have to do it according to [PCI SSC Data Security Standards](https://www.pcisecuritystandards.org/security_standards/).
It is very a challenging task and it is out of scope of this chapter.
Here we describe some practices that helps you not to accidentally store sensitive info anywhere.

All info like credit cards have to be wrapped by `SensitiveValue` class.

```php
<?php

use Payum\Core\Security\SensitiveValue;

$cardNumber = new SensitiveValue('theCreditCardNumber');

serialize($cardNumber);
//null

clone $cardNumber;
// exception

$cardNumber->erase();
// remove value forever.

$cardNumber->get();
// get sensitive value and erase it

$cardNumber->peek();
// get sensitive value but do not erase it. use this method carefully

(string) $cardNumber;
// empty string

json_encode($cardNumber);
// {}

var_dump($cardNumber);
// does not print sensitive data
```

All supported gateways are aware of this class and will handle it safely.

Back to [index](index.md).
