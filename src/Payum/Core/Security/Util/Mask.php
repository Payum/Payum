<?php
namespace Payum\Core\Security\Util;

class Mask
{
    /**
     * @param string $value
     * @param string $maskSymbol
     * @param int $showLast
     *
     * @return string
     */
    public static function mask($value, $maskSymbol = null, $showLast = 3)
    {
        $maskSymbol = $maskSymbol ?: 'X';

        return preg_replace("/(?!^.?)[0-9a-zA-Z](?!(.){0,$showLast}$)/", $maskSymbol, $value);
    }
}