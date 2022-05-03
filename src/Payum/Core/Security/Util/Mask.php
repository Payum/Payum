<?php
namespace Payum\Core\Security\Util;

class Mask
{
    public static function mask(string $value, string $maskSymbol = null, int $showLast = 3): string
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
