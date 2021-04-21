<?php
namespace Payum\Core\Debug;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Reply\HttpRedirect;

abstract class Humanify
{
    /**
     * @param  mixed  $request
     * @return string
     */
    public static function request($request)
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

    /**
     * @param mixed $value
     * @param bool  $shortClass
     *
     * @return string
     */
    public static function value($value, $shortClass = true)
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
