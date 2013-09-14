### How to persist payment details?

```php
<?php
//Source: Payum\Examples\ReadmeTest::persistPaymentDetails()
use Payum\Payment;
use Payum\Storage\FilesystemStorage;
use Payum\Extension\StorageExtension;

$storage = new FilesystemStorage('path_to_storage_dir', 'YourModelClass', 'idProperty');

$payment = new Payment;
$payment->addExtension(new StorageExtension($storage));

//do capture for example.
```
What's inside?

* The extension will try to find model on `onPreExecute` if an id given.
* Second, It saves the model after execute, on `onInteractiveRequest` and `postRequestExecute`.

Back to [index](index.md).