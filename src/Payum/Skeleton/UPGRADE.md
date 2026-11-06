# Upgrades

## 2.0.0

The skeleton generates a 2.x gateway: `SkeletonGateway`, `SkeletonConfig`, `SkeletonApi` and one handler per
operation under `Handler/`. `SkeletonGatewayFactory`, the `Action/` classes and `Api` are gone. A gateway
created from the 1.x skeleton keeps working on Payum 2.0 with deprecations; port it with the
[migration guide](https://payum.dev/docs/v2/upgrading/migrating-from-v1).

See https://github.com/Payum/Payum/blob/2.x/UPGRADE.md for Payum itself.
