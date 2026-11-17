# Symfony

PayumBundle integrates Payum with Symfony. You configure storages and gateways in
`config/packages/payum.yaml`, inject the `Payum` service where you take payments, and the bundle's routes
run the payment pages for you. The bundle works with Payum 1 and Payum 2. These pages describe Payum 2.

- [Get started](get-it-started.md) takes you through your first payment.
- [Upgrade to Payum 2](upgrading.md) is for applications that already use the bundle with Payum 1.
- [Register gateways](gateways.md) covers credentials, environment variables and gateways that have not
  moved to Payum 2.
- [Use your services in Payum](services.md) shows how gateways get your services and how to listen to
  Payum's events.
- [Configuration reference](configuration-reference.md) lists every configuration key.
