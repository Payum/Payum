# Changelog

## 1.0.3 (2015-12-11)

* Support PHP7

## 1.0.2 (2015-12-11)

# Support Payum's versions 1.x

## 1.0.1 (2015-11-28)

* [omnipay] Fix fatal error when omnipay offsite factory is used.
* [sonata] Add exception when sonata_admin option is true but the admin bundle is not installed
* [dev] Add symfony's phpunit bridge.

## 1.0.0 (2015-11-09)

* [command] allow create a capture token without model
* [factory] add token storage to default gateway factory config.
* [omnipay] add support of universal omnipay factory.
* [validation] Add validation on expire date
* [paypal][ec] register confirm order template.

## 1.0.0-BETA2 (2015-09-25)

* [di] Fix key conflicts in payum.gateway_factory tag
* [doc] Update container_tags.md doc.
* Licence owner is changed

## 1.0.0-BETA1 (2015-08-17)

* Php minimum required version is 5.5
* Symfony minimum required version is 2.7
* [storage] Add Propel1StorageFactory
* [storage] Add Propel2StorageFactory
* [factory] Fix the core factory namespace.
* [factory] remove FixedGatewayFactory.

## 0.15.0 (2015-04-28)

* [doc][stripe] Fix misleading title
* [doc] document iso4217 use case.
* [iso4217] add iso4217 service, use it in factory config.
* Rename Payment and PaymentXXX classes to Gateway and GatewayXXX ones.
* Rename Order to Payment.
* Update configure-payment-in-backend.md

## 0.14.4 (2015-03-17)

* [config] should merge different payments in different config files and overwrite payments with same name.

## 0.14.3 (2015-03-15)

[sonata] do not load sonata admin class if the feature disabled (when sonata_admin config option is set to false). 
[form] fix typo in form service name.

## 0.14.2 (2015-03-02)
 
* [config] Do not prepend Doctrine config with mappings if dbal not configured.

## 0.14.1 (2015-02-27)
 
* [factory] fix custom factory. actions\apis\extensions were not added to it.

## 0.14.0 (2015-02-23)

* [form] Add french translations
* [security] token factory extension.
* [security] use token factories composition.
* [doctrine] prepend doctrine config with correct path to Payum mappings files.
* [docs] add docs about container tabs and payment configuration in a backend
* Dynamic payments.
* [registry] add support of payment factory registry.
* [payment-facotry] reuse default configure while creating factories.
* add tests, replace context with payment, add payment_factory tag.
* [factory] cleanup AbstractPaymentFactory. DRY.
* pass actions\apis\extensions tags to payment factory.
* [payment-factory] reuse factories from the lib.
* [twig] require twig bundle with the bug fixed.
* [twig] automatically add paths to twig bundle via prepend config
* [Omnipay] added note about custom gateway.
* [Omnipay] allow user to add custom gateway.
* [Debug cmd] skip get choice list payments for <= sf 2.4.
* [Debug cmd] get choice list payments  if no payment found.

## 0.13.0 (2014-12-28)

* [action] Delete `ObtainCreditCardAction` action. Use one from bridge
* [action] remove generic order action.
* [forms] remove deprecated forms. Use same from from bridge.
* [forms] Fix form `setDefaultOptions` method. Fix for Symfony 2.6.
* [buzz] use buzz client with ssl fix, sync with latesty changes in the lib.
* [paypal-pro] add refund action.
* [controller] add refund controller.
* [composer] change loader to psr4.
* [composer] add Omnipay bridge and JmsPaymentBridge to require-dev section.

## 0.12.4 (2014-12-13)

* [action] back port `GenericOrderAction` from master. Fixes "Attempted to call method "getClientId" in done script but got array instead of order"

## 0.12.3 (2014-12-12)

* [form] fix forms onsymfony 2.6
* [command] add `payum:payment:debug` command.

## 0.12.2 (2014-11-20)

* [action] Add `NotifyOrderAction` action.

## 0.12.1 (2014-11-07)

* [security] Force TLSv1 encription. This POODLE bites: exploiting the SSL 3.0 fallback
* [doc][paypal-pro] add twig config.

## 0.12.0 (2014-10-29)

* [doc] simple examples -> custom examples.
* [doc] update get it started.
* [request] remove secured requests usages, replace with generic+token.
* [order] add support of unified order protocol.
* [paypal-ec] inject token factory to allow auto notifyurl set.
* [klarna-checkout] reuse Config from the lib, 
* [klarna-checkout] remove capture action and add authorize one.
* [klarna-invoice] add refund action to the factory.
* [klarna] add credit part action to services.

## 0.11.0 (2014-09-11)

* [storage] add short version of config for custom storage.
* [storage] add custom storage.
* [klarna] add klarna invoice payment support.
* [translation] add croatian translation for credit card form
* [request] add support of authorize request.
* [request] remove Request postfix, move interactive request to reply namespace.
* [tests] phpunit 4.x upgrade

## 0.10.0 (2014-09-04)

* [omnipay] add support custom gateways.

## 0.10.0 (2014-08-01)

* [paypal] reflect changes in api. constructor arguments order has been changed.
* [stripe] add stripe checkout payment factory and services.
* [stripe] add stripe js payment factory and services.
* [request] remove some previously deprecated requests.
* [action] remove get http query action. one from bridge will be used.
* [templates] add render template action.
* [templates] add support of twig templates.
* [doc] simplify get it started. add storages section.
*  added PHP 5.6 and HHVM to travis.yml

## 0.9.0 (2014-06-17)

* [symfony] require minimum version symfony 2.3.
* [storage-factory][doctrine] allow shorten way of configuration: `doctrine: orm`.
* [payment-factory] add ability to disable obtain credit card feature. Added boolean option `obtain_credit_card`.
* [payment-factory] simplify payments configuration, remove api.options subsection.
* [payment-factory][be2bill] add `Be2billOnsitePaymentFactory` to the bundle.
* [registry] make bundle work with latest changes in `StorageRegistry`.
* [doc] add example of how to set cc fields explisitly.
* [doc] update simple purchase examples to demonstrate obtain credit card credit card feature.
* [action] add `ObtainCreditCardAction`.
* [form] add `CreditCardType` and `CreditCardExpirationDateType`.
* [be2bill] add a note that be2bill does support onsite payments any more.
* buzz client service public now.
* [payment] add `prototype` post fix to api service which are abstract.
* [payment-factory] reuse `payum.action` tag, simplify factories.
* [omnipay] support omnipay v2.0, drop support of v1.0
* [paypal] add do reference transaction action in payment factory.
* [paypal] add create billing agreement action in payment factory.
* [paypal] add notify action in payment factory.

## 0.8.5 (2014-05-26)

* [action] add ability to add action by container tag
* [composer] Add klarna to package keywords

## 0.8.4 (2014-05-08)

* [cli][security] add create capture token cli command.
* [cli] add a command to get payment status.

## 0.8.3 (2014-05-01)

* [cli][security] add create notify token cli command.

## 0.8.2 (2014-04-14)

* [paypal][factory] add generic NotifyAction to payment.
* [security] add deprecate notes. extend classes from bridge.
* [doc] fix mapping links

## 0.8.1 (2014-04-08)

* [security] Mark TokenFactory as deprecated. Use one from bridge.
* [security] mark HttpRequestVerifier as deprecated. Use one from bridge.
* [doc] Missing argument in purchase_done_action caused fatal error.

## 0.8.0 (2014-04-02)

* [klarna][doc] add a klarna purchase example doc
* [security] add functional test for TokenFactory.
* [security] extend TokenFactory from GenericTokenFactory.

## 0.7.3 (2014-01-20)

* fix composer requires payum/core 0.8.

## 0.7.2 (2014-01-16)

* [notify] add posiblity to track unsafe notifications (one without token).

## 0.7.1 (2013-12-28)

* [be2bill] add missed capture onsite action to factory.

## 0.7.0 (2013-12-26)

* [composer] add browser kit to dev requirments.
* [action] add common action that can execute `GetHttpQueryRequest`.
* [capture] add ability to store token to session and reuse it when come back.
* [request] add support for POST redirection
* [di] fix payment class parameter after moving classes to core namespace.
* [travis] test on different symfony versions.
* apply changes after repos merge and moving core stuff to its own namespace.
* [request] use execute same request with model details action
* remove previously deprecated code.

## 0.6.2 (2013-10-31)

* [composer] lower required php version from 5.3.9 to 5.3.3. remove usages of is_a() newer feature.

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

