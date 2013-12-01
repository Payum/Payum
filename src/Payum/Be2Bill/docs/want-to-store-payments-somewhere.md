# Want to store payments somewhere?

There are two storage supported out of the box. [doctrine2](https://github.com/Payum/Payum/blob/master/src/Payum/Bridge/Doctrine/Storage/DoctrineStorage.php)([offsite](http://www.doctrine-project.org/)) and [filesystem](https://github.com/Payum/Payum/blob/master/src/Payum/Storage/FilesystemStorage.php).
The filesystem storage is easy to setup, does not have any requirements. It is expected to be used more in tests.
To use doctrine2 storage you have to follow several steps:

* [Install](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/installation.html) doctrine2 lib.
* [Add](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html#obtaining-an-entitymanager) mapping [schema](https://github.com/Payum/Be2Bill/blob/master/src/Payum/Be2Bill/Bridge/Doctrine/Resources/mapping/PaymentDetails.orm.xml) to doctrine configuration.
* Extend provided [model](https://github.com/Payum/Be2Bill/blob/master/src/Payum/Be2Bill/Model/PaymentDetails.php) and add `id` field.

Want another storage? Contribute!
