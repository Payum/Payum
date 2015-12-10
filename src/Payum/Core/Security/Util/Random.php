<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Payum\Core\Security\Util;

/**
 * This is adopted version ot TokenGenerator class from FOSUserBundle
 *
 * @link https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Util/TokenGenerator.php
 */
class Random
{
    public static function generateToken()
    {
        return rtrim(strtr(base64_encode(self::getRandomNumber()), '+/', '-_'), '=');
    }

    private static function getRandomNumber()
    {
        // determine whether to use OpenSSL
        if (defined('PHP_WINDOWS_VERSION_BUILD') && version_compare(PHP_VERSION, '5.3.4', '<')) {
            $useOpenSsl = false;
        } elseif (!function_exists('openssl_random_pseudo_bytes')) {
            $useOpenSsl = false;
        } else {
            $useOpenSsl = true;
        }

        $nbBytes = 32;

        if ($useOpenSsl) {
            $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);

            if (false !== $bytes && true === $strong) {
                return $bytes;
            }
        }

        return hash('sha256', uniqid(mt_rand(), true), true);
    }
}
