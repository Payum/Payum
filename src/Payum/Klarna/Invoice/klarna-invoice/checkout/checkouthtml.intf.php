<?php
/**
 * CheckoutHTML interface for threatmetrix
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
 * This interface provides methods to supply checkout page specific HTML.<br>
 * Can be used to insert device identification, fraud prevention,<br>
 * client side validation code into the checkout page.
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
abstract class CheckoutHTML
{

    /**
     * Creates a session ID used for e.g. client identification and fraud
     * prevention.
     *
     * This method creates a 40 character long integer.
     * The first 30 numbers is microtime + random numbers.
     * The last 10 numbers is the eid zero-padded.
     *
     * All random functions are automatically seeded as of PHP 4.2.0.
     *
     * E.g. for eid 1004 output could be:
     * 1624100001298454658880354228080000001004
     *
     * @param int $eid merchant id
     *
     * @return string A integer with a string length of 40.
     */
    public static function getSessionID($eid)
    {
        $eid = strval($eid);
        while (strlen($eid) < 10) {
            $eid = "0" . $eid; //Zero-pad the eid.
        }

        $sid = str_replace(array(' ', ',', '.'), '', microtime());
        $sid[0] = rand(1, 9); //Make sure we always have a non-zero first.

        //microtime + rand = 30 numbers in length
        while (strlen($sid) < 30) {
            //rand is automatically seeded as of PHP 4.2.0
            $sid .= rand(0, 9999);
        }
        $sid = substr($sid, 0, 30);
        $sid .= $eid;

        return $sid;
    }

    /**
     * Initializes this object, this method is always called
     * before {@link CheckoutHTML::toHTML()}.
     * This method is used in {@link Klarna::addTransaction()},
     * {@link Klarna::reserveAmount()} and in {@link Klarna::checkoutHTML()}
     *
     * @param Klarna $klarna The API instance
     * @param int    $eid    merchant id
     *
     * @return void
     */
    abstract public function init($klarna, $eid);

    /**
     * This returns the HTML code for this object,
     * which will be used in the checkout page.
     *
     * @return string HTML
     */
    abstract public function toHTML();

    /**
     * This function is used to clear any stored values
     * (in SESSION, COOKIE or similar)
     * which are required to be unique between purchases.
     *
     * @return void
     */
    abstract public function clear();
}
