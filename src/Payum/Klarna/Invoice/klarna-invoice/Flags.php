<?php
/**
 * KlarnaFlags
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
 * Flag Constants class
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
class KlarnaFlags
{

    /**
     * Specifies that no flag is to be used.
     *
     * @var int
     */
    const NO_FLAG = 0;

    //Gender flags
    /**
     * Indicates that the person is a female.<br>
     * Use "" or null when unspecified.<br>
     *
     * @var int
     */
    const FEMALE = 0;

    /**
     * Indicates that the person is a male.<br>
     * Use "" or null when unspecified.<br>
     *
     * @var int
     */
    const MALE = 1;

    //Order status constants
    /**
     * This signifies that the invoice or reservation is accepted.
     *
     * @var int
     */
    const ACCEPTED = 1;

    /**
     * This signifies that the invoice or reservation is pending, will be set
     * to accepted or denied.
     *
     * @var int
     */
    const PENDING = 2;

    /**
     * This signifies that the invoice or reservation is <b>denied</b>.
     *
     * @var int
     */
    const DENIED = 3;

    //Get_address constants
    /**
     * A code which indicates that all first names should be returned with the
     * address.
     *
     * Formerly refered to as GA_OLD.
     *
     * @var int
     */
    const GA_ALL = 1;

    /**
     * A code which indicates that only the last name should be returned with
     * the address.
     *
     * Formerly referd to as GA_NEW.
     *
     * @var int
     */
    const GA_LAST = 2;

    /**
     * A code which indicates that the given name should be returned with
     * the address. If no given name is registered, this will behave as
     * {@link KlarnaFlags::GA_ALL GA_ALL}.
     *
     */
    const GA_GIVEN = 5;

    //Article/goods constants
    /**
     * Quantity measured in 1/1000s.
     *
     * @var int
     */
    const PRINT_1000 = 1;

    /**
     * Quantity measured in 1/100s.
     *
     * @var int
     */
    const PRINT_100 = 2;

    /**
     * Quantity measured in 1/10s.
     *
     * @var int
     */
    const PRINT_10 = 4;

    /**
     * Indicates that the item is a shipment fee.
     *
     * Update_charge_amount (1)
     *
     * @var int
     */
    const IS_SHIPMENT = 8;

    /**
     * Indicates that the item is a handling fee.
     *
     * Update_charge_amount (2)
     *
     * @var int
     */
    const IS_HANDLING = 16;

    /**
     * Article price including VAT.
     *
     * @var int
     */
    const INC_VAT = 32;

    //Miscellaneous
    /**
     * Signifies that this is to be displayed in the checkout.<br>
     * Used for part payment.<br>
     *
     * @var int
     */
    const CHECKOUT_PAGE = 0;

    /**
     * Signifies that this is to be displayed in the product page.<br>
     * Used for part payment.<br>
     *
     * @var int
     */
    const PRODUCT_PAGE = 1;

    /**
     * Signifies that the specified address is billing address.
     *
     * @var int
     */
    const IS_BILLING = 100;

    /**
     * Signifies that the specified address is shipping address.
     *
     * @var int
     */
    const IS_SHIPPING = 101;

    //Invoice and Reservation
    /**
     * Indicates that the purchase is a test invoice/part payment.
     *
     * @var int
     */
    const TEST_MODE = 2;

    /**
     * PClass id/value for invoices.
     *
     * @see KlarnaPClass::INVOICE.
     * @var int
     */
    const PCLASS_INVOICE = -1;

    //Invoice
    /**
     * Activates an invoices automatically, requires setting in Klarna Online.
     *
     * If you designate this flag an invoice is created directly in the active
     * state, i.e. Klarna will buy the invoice immediately.
     *
     * @var int
     */
    const AUTO_ACTIVATE = 1;

    /**
     * Creates a pre-pay invoice.
     *
     * @var int
     *
     * @deprecated Do not use.
     */
    const PRE_PAY = 8;

    /**
     * Used to flag a purchase as sensitive order.
     *
     * @var int
     */
    const SENSITIVE_ORDER = 1024;

    /**
     * Used to return an array with long and short ocr number.
     *
     * @see Klarna::addTransaction()
     * @var int
     */
    const RETURN_OCR = 8192;

    /**
     * Specifies the shipment type as normal.
     *
     * @var int
     */
    const NORMAL_SHIPMENT = 1;

    /**
     * Specifies the shipment type as express.
     *
     * @var int
     */
    const EXPRESS_SHIPMENT = 2;

    //Mobile (Invoice) flags
    /**
     * Marks the transaction as Klarna mobile.
     *
     * @var int
     */
    const M_PHONE_TRANSACTION = 262144;

    /**
     * Sends a pin code to the phone sent in pno.
     *
     * @var int
     */
    const M_SEND_PHONE_PIN = 524288;

    //Reservation flags
    /**
     * Signifies that the amount specified is the new amount.
     *
     * @var int
     */
    const NEW_AMOUNT = 0;

    /**
     * Signifies that the amount specified is to be added.
     *
     * @var int
     */
    const ADD_AMOUNT = 1;

    /**
     * Sends the invoice by mail when activating a reservation.
     *
     * @var int
     */
    const RSRV_SEND_BY_MAIL = 4;

    /**
     * Sends the invoice by e-mail when activating a reservation.
     *
     * @var int
     */
    const RSRV_SEND_BY_EMAIL = 8;

    /**
     * Used for partial deliveries, this flag saves the reservation number so
     * it can be used again.
     *
     * @var int
     */
    const RSRV_PRESERVE_RESERVATION = 16;

    /**
     * Used to flag a purchase as sensitive order.
     *
     * @var int
     */
    const RSRV_SENSITIVE_ORDER = 32;

    /**
     * Marks the transaction as Klarna mobile.
     *
     * @var int
     */
    const RSRV_PHONE_TRANSACTION = 512;

    /**
     * Sends a pin code to the mobile number.
     *
     * @var int
     */
    const RSRV_SEND_PHONE_PIN = 1024;
}
