<?php

namespace Payum\Core\Bridge\Zend\Storage;

use \Payum\Core\Bridge\Laminas\Storage\TableGatewayStorage as LaminasTableGatewayStorage;

@trigger_error(sprintf('The class %s is deprecated and will be removed in version 2. Please use %s instead.', TableGatewayStorage::class, LaminasTableGatewayStorage::class));
class TableGatewayStorage extends LaminasTableGatewayStorage
{
}
