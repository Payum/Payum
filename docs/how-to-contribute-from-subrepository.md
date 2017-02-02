# How to contribute from sub repository.

Some time you do not need the whole payum stuff and decided to install only a single gateway.
Let's say it was a `payum/stripe`.
Later on you found a bug fixed it and want to contribute it back to payum main repository.
The patch could not be accepted on payum/stripe repository. It is readonly subtree split repository.
Here I show you how to move your fixes to payum/payum repository and push them.

1. Fork [payum/payum](https://github.com/Payum/Payum) repository on GitHub.
2. Clone forked repository locally.

```bash
$ git clone git@github.com:Foo/Payum.git Payum
$ cd Payum
$ git checkout -b patch1
```

3. Add your fork of `payum/stripe` repository as remote and fetch changes

```bash
$ git remote add foo git@github.com:foo/Stripe.git
$ git fetch foo
```

4. Merge changes from sub repository

```bash
$ git subtree pull --prefix=src/Payum/Stripe/ foo patch1
```

5. Push changes to GitHub and open a Pull Request as usual.

```bash
$ git push origin patch1
```

Back to [index](index.md).
