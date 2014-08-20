<?php

/**
 * KlarnaCurrency
 *
 * PHP Version 5.3
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */

/**
 * Currency Constants class
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
class KlarnaCurrency
{

    /**
     * Currency constant for Swedish Crowns (SEK).
     *
     * @var int
     */
    const SEK = 0;

    /**
     * Currency constant for Norwegian Crowns (NOK).
     *
     * @var int
     */
    const NOK = 1;

    /**
     * Currency constant for Euro.
     *
     * @var int
     */
    const EUR = 2;

    /**
     * Currency constant for Danish Crowns (DKK).
     *
     * @var int
     */
    const DKK = 3;

    /**
     * Converts a currency code, e.g. 'eur' to the KlarnaCurrency constant.
     *
     * @param string $val currency code
     *
     * @return int|null
     */
    public static function fromCode($val)
    {
        switch(strtolower($val)) {
        case 'dkk':
            return self::DKK;
        case 'eur':
        case 'euro':
            return self::EUR;
        case 'nok':
            return self::NOK;
        case 'sek':
            return self::SEK;
        default:
            return null;
        }
    }

    /**
     * Converts a KlarnaCurrency constant to the respective language code.
     *
     * @param int $val KlarnaCurrency constant
     *
     * @return string|null
     */
    public static function getCode($val)
    {
        switch($val) {
        case self::DKK:
            return 'dkk';
        case self::EUR:
            return 'eur';
        case self::NOK:
            return 'nok';
        case self::SEK:
            return 'sek';
        default:
            return null;
        }
    }

}
