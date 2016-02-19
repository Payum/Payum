#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH = git name-rev --name-only HEAD
sleep 3

function split()
{
    git subtree push --prefix=$1 $2 $CURRENT_BRANCH
}

function forceSplit()
{
    git push $2 `git subtree split --prefix $1 $CURRENT_BRANCH`:$CURRENT_BRANCH --force
}

function remote()
{
    git remote add $1 $2
}

#remote core git@github.com:Payum/Core.git
#remote paypal-pro-checkout git@github.com:Payum/PaypalProCheckoutNvp.git
#remote authorize-net-aim git@github.com:Payum/AuthorizeNetAim.git
#remote be2bill git@github.com:Payum/Be2Bill.git
#remote paypal-express-checkout git@github.com:Payum/PaypalExpressCheckoutNvp.git
#remote paypal-ipn git@github.com:Payum/PaypalIpn.git
#remote paypal-rest git@github.com:Payum/PaypalRest.git
#remote offline git@github.com:Payum/Offline.git
#remote payex git@github.com:Payum/Payex.git
#remote klarna-checkout git@github.com:Payum/KlarnaCheckout.git
#remote klarna-invoice git@github.com:Payum/KlarnaInvoice.git
#remote stripe git@github.com:Payum/Stripe.git
remote sofort git@github.com:Payum/Sofort.git
#remote skeleton git@github.com:Payum/Skeleton.git

#split 'src/Payum/Core/' core
#split 'src/Payum/AuthorizeNet/Aim/' authorize-net-aim
#split 'src/Payum/Be2Bill' be2bill
#split 'src/Payum/Paypal/ExpressCheckout/Nvp' paypal-express-checkout
#split 'src/Payum/Paypal/ProCheckout/Nvp' paypal-pro-checkout
#split 'src/Payum/Paypal/Ipn' paypal-ipn
#split 'src/Payum/Paypal/Rest' paypal-rest
#split 'src/Payum/Offline' offline
#split 'src/Payum/Payex' payex
#split 'src/Payum/Klarna/Checkout' klarna-checkout
#split 'src/Payum/Klarna/Invoice klarna-invoice
#split 'src/Payum/Sofort' sofort
split 'src/Payum/Stripe' stripe
#split 'src/Payum/Skeleton' skeleton