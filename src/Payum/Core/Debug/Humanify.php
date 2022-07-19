<?php

namespace Payum\Core\Debug;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Reply\HttpRedirect;
use ReflectionObject;

abstract class Humanify
{
    final private function __construct()
    {
    }

    /**
     * @param  mixed  $request
     */
    public static function request($request): string
    {
        $return = self::value($request);

        $details = [];

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

    /**
     * @param mixed $value
     *
     * @return string
     */
    public static function value($value, bool $shortClass = true)
    {
        if (is_object($value)) {
            if ($shortClass) {
                $ro = new ReflectionObject($value);

                return $ro->getShortName();
            }

            return get_class($value);
        }

        return gettype($value);
    }
}
