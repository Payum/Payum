<?php

namespace Payum\Core;

interface GatewayAwareInterface
{
    public function setGateway(GatewayInterface $gateway);
}
