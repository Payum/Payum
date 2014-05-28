# Upgrades

## 0.8 to 0.9

* Payment factory does not create action services any more. Instead, it uses actions defined in payment/foo.xml by tag.
* Payment factories configurations were simplified. Sub options `api.options` were moved to the root, section was removed.

    before:

    ```yml
    payum:
        a_context:
            a_factory:
                api:
                    options:
                        foo: foo
                        bar: bar
    ```

    after:

    ```yml
    payum:
        a_context:
            a_factory:
                foo: foo
                bar: bar
    ```

* `be2bill` payment factory does not provide support of onsite payments any more, use `be2bill_onsite` factory instead.

## 0.7 to 0.8

* `TokenFactory::createTokenForRoute` was renamed to `createToken`.

## 0.5 to 0.6

* AbstractPaymentFactory::addCommonExtensions method signature has been changed.
* AbstractPaymentFactory::addCommonActions method signature has been changed.
* `TokenManager` was removed. Its work was partially moved to `TokenFactory` and `HttpRequestVerifier`.
* `CaptureTokenizedDetailsRequest` was removed, use `Payum\Request\SecuredCaptureRequest` instead.
* `capture` url was changed if you still want use old one add `payum_deprecated_capture_do`.
* `notify` url was changed if you still want use old one add `payum_deprecated_notify_do`.
* `sync` url was changed if you still want use old one add `payum_deprecated_sync_do`.
* bundle configuration was changed. Now you have to configure `payum.security` section.

    before:

    ```yml
    payum:
        contexts:
            foo:
                storages:
                    Acme\PaymentBundle\Entity\TokenizedDetails:
                        filesystem:
                            storage_dir: %kernel.root_dir%/Resources/payments
                            id_property: token
    ```

    after:

    ```yml
    payum:
        security:
            token_storage:
                Acme\PaymentBundle\Entity\PayumSecurityToken:
                    filesystem:
                        storage_dir: %kernel.root_dir%/Resources/payments
                        id_property: hash
    ```

## 0.3 to 0.5

* Storage factory names has been changed. The `_storage` post fix removed. For example `doctrine_storage` now `doctrine`.
* Payment factory names has been changed. The `_payment` post fix removed. For example `omnipay_payment` now `omnipay`.
* `StorageFactoryInterface::create` method signature has been changed. Now it requires additional parameter `modelClass`. 
* Doctrine storage configuration does not have `model_class` option any more.
* Filesystem storage configuration does not have `model_class` option any more.
* `LazyContext` was removed in favor of `ContainerAwareRegistry`.
* `ContextInterface` was removed in favor of `ContainerAwareRegistry`.
* `ContextRegistry` was removed in favor of `ContainerAwareRegistry`.
* `payum` service now instance of `ContainerAwareRegistry` class. So the method `getContext` is not present any more.

## 0.2 to 0.3

* `capture_interactive_controller` option removed from config. Now `InteractiveRequestListener` does the job.
* `status_request_class` option was removed.
* `capture_finished_controller` option was removed.
* `ContextInterface::createStatusRequest` method was removed.
* `ContextInterface::getCaptureFinishedController` method was removed.
* `CaptureController` was removed. Use your own.

## 0.1 to 0.2

* The option `payum.context.a_context.xxx_payment.create_instruction_from_model_action` was removed. use `...actions` instead.
* `CaptureController::doCapture` method argument `modelId` was renamed to `model`. The route is also updated.
