# Contribute from a sub repository

Each Payum package, such as `payum/stripe` or `payum/core`, is published from the
[Payum repository](https://github.com/Payum/Payum) as a read-only copy. Those copies are generated
and overwritten on every push, so pull requests opened against them cannot be merged. If you fixed
a bug in a package you installed on its own, move the fix to the Payum repository and open the pull
request there. This page shows how, using `payum/stripe` as the example.

## Fork and clone Payum

Fork [Payum/Payum](https://github.com/Payum/Payum) on GitHub, clone your fork, and create a branch
from the branch you are fixing (`2.x` for Payum 2):

```bash
git clone git@github.com:your-name/Payum.git
cd Payum
git checkout -b fix-stripe-refund origin/2.x
```

## Move your commits

In your clone of the package repository, export the commits you made as patches. Replace
`origin/2.x` with the commit your changes start from:

```bash
cd ../Stripe
git format-patch origin/2.x --stdout > ../stripe-fix.patch
```

Back in the Payum clone, apply them under the package's directory:

```bash
cd ../Payum
git am --directory=src/Payum/Stripe ../stripe-fix.patch
```

`--directory` prefixes every path in the patch, so a change to `Action/CaptureAction.php` lands in
`src/Payum/Stripe/Action/CaptureAction.php`. The packages and their directories are listed in
`scripts/subsplits` at the root of the repository.

## Open the pull request

Push the branch to your fork and open a pull request against the same branch of Payum/Payum:

```bash
git push origin fix-stripe-refund
```
