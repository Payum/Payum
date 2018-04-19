<?php
namespace Payum\Core\Security\Util;

class Mask
{
    /**
     * @param string $value
     * @param string $maskSymbol
     * @param int    $showLast
     *
     * @return string
     */
    public static function mask($value, $maskSymbol = null, $showLast = 3)
    {
        $maskSymbol = $maskSymbol ?: 'X';
        $showLast = max(0, $showLast);

        if (mb_strlen($value) <= ($showLast + 1) * 2 || false == $showLast) {
            $showRegExpPart = "";
        } else {
            $showRegExpPart = "(?!(.){0,$showLast}$)";
        }

        return preg_replace("/(?!^.?)[^-_\s]$showRegExpPart/u", $maskSymbol, $value);
    }
}
