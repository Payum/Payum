<?php
namespace Payum\Core\Debug;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Reply\HttpRedirect;

abstract class Humanify
{
    public static function request(mixed $request): string
    {
        $return = self::value($request);

        $details = array();

        if ($request instanceof ModelAggregateInterface) {
            $details[] = sprintf('model: %s', self::value($request->getModel()));
        }
        if ($request instanceof HttpRedirect) {
            $details[] = sprintf('url: %s', $request->getUrl());
        }

        if (false == empty($details)) {
            $return .= sprintf('{%s}', implode(', ', $details));
        }

        return $return;
    }

    public static function value(mixed $value, bool $shortClass = true): string
    {
        if (is_object($value)) {
            if ($shortClass) {
                $ro = new \ReflectionObject($value);

                return $ro->getShortName();
            }

            return get_class($value);
        }

        return gettype($value);
    }

    final private function __construct()
    {
    }
}
