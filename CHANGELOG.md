# Changelog

## 0.6.1 (2013-10-31)

* [payment-factory] added support of offline payments.

## 0.6.0 (2013-10-25)

* security reworked. added HttpRequestVerifier and TokenFactory
* [storage][doctrine] add doctrine mongodb odm support
* [payment-factory] make abstract payment factory method use same signature for all template methods
* [log] add how to debug doc 
* [log] add support of LoggerExtension and LogExecutedActionsExtension

## 0.5.7 (2013-08-18)

* better code for: call addApi before addAction in AbstractPaymentFactory
* [payment-factory] add `AbstractPaymentFactory`.

## 0.5.6 (2013-08-12)

* [request] set correct status from response in the interactive request. now it always set 500 status

## 0.5.5 (2013-08-02)

* [payex][composer] use stable version of the lib
* [payex][doc] add doc 
* [payex] add missing actions recurring, sync etc.
* [payex] add some missing action for auto pay logic.
* [payex][payment-factory] configure status actions for payment and agreement.
* [payex][payment-factory] add agreement related api and actions
* [payex] add a check that lib installed and if not responed gracefully
* [payment-factory] add payex payment factory

## 0.5.4 (2013-07-31)

* [payment-factory] api must be added before any actions (currently custom actions are added before api)
* [config] fix merge of configurations defined in different files

## 0.5.3 (2013-07-15)

* [notify] allow get token by custom http query parameters
* [notify] do not delete token when notification is sent
* [notify] add notify controller.

## 0.5.2 (2013-06-24)

* No changes

## 0.5.1 (2013-06-24)

* [request] move the capture tokenized details request to core lib

## 0.5.0 (2013-06-03)

* [payment-facotry] add custom payment factory
* use registry interface
* add sync controller
* move token checks to manager
* add tests for tokenized request
* add tokenized details request
* rework token service. rename to manager
* [security] allow define after url for custom token
* [capture] use token for capture and status requests
* remove canBeEnabled method usage, make compatible with sf2.1
* [doc] replace instruction with PaymentDetails
* [doc] add basic setup doc
* fix 5.3 compatibility
* add standard capture controller
* fix paypal pro payment name
* configuration changes
* remove context interface, lazy context etc
* [di] set registry as payum service
* add container aware registry
* [di] restrict to use only class as storage entry
* [config] allow configure several storages
* remove old code for adding custom actions\apis\extensions
* [payment-factory] introduce abstract payment factory

## 0.4.1 (2013-05-16)

* [di][paypal] add manage subscritpion action

## 0.4.0 (2013-04-04)

* use details aware aggregated
* [paypal] add recuring payment sync action
* [di][paypal] add recurring payment status action
* [di][paypal] add get recurring payment profile details action to di
* correct Sync and Status action names
* [paypal] add create paypal recurring payments actions
* add api postfix to api actions
* skip tests if related libs not installed

## 0.3.0 (2013-03-19)

* add tests for apis and extensions configuration
* add ability to add custom apis and extensions
* [di] api services should be public
* remove capture controller
* [action ]add support of payment instruction aggregate actions
* fix add storage
* move manage interactive request logic to listener
* use storage extension
* [payment-factory] add omnipay factory 
* fix exception message if lib not installed
* add travis
* remove custom payment classes
* add support of omnipay via bridge
* [di] add test for payum extension
* update `ResponseInteractiveRequest` base class

## 0.2.2 (2013-03-07)

* [di] fix filesystem storage definition

## 0.2.1 (2013-03-05)

* require stable version of payum.

## 0.2.0 (2013-02-20)

* fix bugs in DI configuration
* fix custom events configuration.
* [composer] add paypal pro to suggests
* add ability to set null storage.
* fix storage clasess.
* [config] replace create_instr option with actions array one.
* remove ModelInterface usage.

## 0.1.3 (2013-02-15)

* [payment-factory]  add paypal pro payment factory

## 0.1.2 (2013-02-15)

* [composer] add paypal pro to suggests

## 0.1.1 (2013-01-16)

* [payment-factory] add support of authorize.net

## 0.1.0 (2013-01-06)

* add paypal and be2bill doc
* add readme and license
* rename change status controller to capture finished
* change payum bundle namespace
* [buzz] make curl timeout 20 sec
* [payment factory] add paypal express checkout factory
* [request] add symfony response interactive request
* [payment factory] add be2bill factory
* [composer] allow any version of symfony2 in composer
* draft version

