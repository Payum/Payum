0.3 to 0.5

* Storage factory names has been changed. The `_storage` post fix removed. For example `doctrine_storage` now `doctrine`.
* Payment factory names has been changed. The `_payment` post fix removed. For example `omnipay_payment` now `omnipay`.
* `StorageFactoryInterface::create` method signature has been changed. Now it requires additional parameter `modelClass`. 
* Doctrine storage configuration does not have `model_class` option any more.
* Filesystem storage configuration does not have `model_class` option any more.
* Storages are not added automatically as payment extension any more. Set `payment_extension.enabled` storage's option to true to enable it.  

0.2 to 0.3
==========

* `capture_interactive_controller` option removed from config. Now `InteractiveRequestListener` does the job.
* `status_request_class` option was removed.
* `capture_finished_controller` option was removed.
* `ContextInterface::createStatusRequest` method was removed.
* `ContextInterface::getCaptureFinishedController` method was removed.
* `CaptureController` was removed. Use your own.

0.1 to 0.2
==========

* The option `payum.context.a_context.xxx_payment.create_instruction_from_model_action` was removed. use `...actions` instead.
* `CaptureController::doCapture` method argument `modelId` was renamed to `model`. The route is also updated.