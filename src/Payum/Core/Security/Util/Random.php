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
        return random_bytes(32);
    }
}
