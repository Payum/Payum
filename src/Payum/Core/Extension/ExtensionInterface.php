<?php
namespace Payum\Core\Extension;

interface ExtensionInterface
{
    public function onPreExecute(Context $context): void;

    public function onExecute(Context $context): void;

    public function onPostExecute(Context $context): void;
}
