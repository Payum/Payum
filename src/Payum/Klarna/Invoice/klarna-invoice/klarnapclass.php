<?php
/**
 * KlarnaPClass
 *
 * PHP Version 5.3
 *
 * @ignore  Do not show in PHPDoc.
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */

/**
 * PClass object used for part payment.
 *
 * PClasses are used in conjunction with KlarnaCalc to determine part payment costs.
 *
 * @ignore    Do not show in PHPDoc.
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
class KlarnaPClass
{

    /**
     * Invoice type/identifier, used for invoice purchases.
     *
     * @var int
     */
    const INVOICE = -1;

    /**
     * Campaign type pclass.
     *
     * @var int
     */
    const CAMPAIGN = 0;

    /**
     * Account type pclass.
     *
     * @var int
     */
    const ACCOUNT = 1;

    /**
     * Special campaign type pclass.<br>
     * "Buy now, pay in x month"<br>
     *
     * @var int
     */
    const SPECIAL = 2;

    /**
     * Fixed campaign type pclass
     *
     * @var int
     */
    const FIXED = 3;

    /**
     * Delayed campaign type pclass.<br>
     * "Pay in X months"<br>
     *
     * @var int
     */
    const DELAY = 4;

    /**
     * Klarna Mobile type pclass
     *
     * @var int
     */
    const MOBILE = 5;

    /**
     * The description for this PClass.
     * HTML entities for special characters.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $description;

    /**
     * Number of months for this PClass.
     *
     * @ignore Do not show in PHPDoc.
     * @var int
     */
    protected $months;

    /**
     * PClass starting fee.
     *
     * @ignore Do not show in PHPDoc.
     * @var float
     */
    protected $startFee;

    /**
     * PClass invoice/handling fee.
     *
     * @ignore Do not show in PHPDoc.
     * @var float
     */
    protected $invoiceFee;

    /**
     * PClass interest rate.
     *
     * @ignore Do not show in PHPDoc.
     * @var float
     */
    protected $interestRate;

    /**
     * PClass minimum amount for purchase/product.
     *
     * @ignore Do not show in PHPDoc.
     * @var float
     */
    protected $minAmount;

    /**
     * PClass country.
     *
     * @ignore Do not show in PHPDoc.
     * @see KlarnaCountry
     * @var int
     */
    protected $country;

    /**
     * PClass ID.
     *
     * @ignore Do not show in PHPDoc.
     * @var int
     */
    protected $id;

    /**
     * PClass type.
     *
     * @see self::CAMPAIGN
     * @see self::ACCOUNT
     * @see self::SPECIAL
     * @see self::FIXED
     * @see self::DELAY
     * @see self::MOBILE
     *
     * @ignore Do not show in PHPDoc.
     * @var int
     */
    protected $type;

    /**
     * Expire date / valid until date as unix timestamp.<br>
     * Compare it with e.g. $_SERVER['REQUEST_TIME'].<br>
     *
     * @ignore Do not show in PHPDoc.
     * @var int
     */
    protected $expire;

    /**
     * Merchant ID / Estore ID.
     *
     * @ignore Do not show in PHPDoc.
     * @var int
     */
    protected $eid;

    /**
     * Class constructor
     *
     * The optional array argument can be:
     * array (
     *   0 = eid (this is created in the API)
     *   1 = id number
     *   2 = description
     *   3 = amount of months for part payment
     *   4 = start fee
     *   5 = invoice fee
     *   6 = interest rate
     *   7 = minimum purchase amount for pclass
     *   8 = country
     *   9 = type
     *     (This is used to determine which pclass-id is an account and
     *     a campaign, 0 = campaign, 1 = account, 2 = special campaign
     *     i.e. x-mas campaign)
     *  10 = expire date
     *
     * @param null|array $arr Associative or numeric array of PClass data.
     */
    public function __construct($arr = null)
    {
        if (!is_array($arr) || count($arr) < 11) {
            return;
        }

        foreach ($arr as $key => $val) {
            switch($key) {
            case "0":
            case "eid":
                $this->setEid($val);
                break;
            case "1":
            case "id":
                $this->setId($val);
                break;
            case "2":
            case "desc":
            case "description":
                $this->setDescription($val);
                break;
            case "3":
            case "months":
                $this->setMonths($val);
                break;
            case "4":
            case "startfee":
                $this->setStartFee($val);
                break;
            case "5":
            case "invoicefee":
                $this->setInvoiceFee($val);
                break;
            case "6":
            case "interestrate":
                $this->setInterestRate($val);
                break;
            case "7":
            case "minamount":
                $this->setMinAmount($val);
                break;
            case "8":
            case "country":
                $this->setCountry($val);
                break;
            case "9":
            case "type":
                $this->setType($val);
                break;
            case "10":
            case "expire":
                $this->setExpire($val);
                break;
            default:
                //Array index not supported.
                break;
            }
        }
    }

    /**
     * Returns an associative array mirroring this PClass.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
                'eid'          => $this->eid,
                'id'           => $this->id,
                'description'  => $this->description,
                'months'       => $this->months,
                'startfee'     => $this->startFee,
                'invoicefee'   => $this->invoiceFee,
                'interestrate' => $this->interestRate,
                'minamount'    => $this->minAmount,
                'country'      => $this->country,
                'type'         => $this->type,
                'expire'       => $this->expire
        );
    }

    /**
     * Sets the descriptiton, converts to HTML entities.
     *
     * @param string $description PClass description.
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Sets the number of months.
     *
     * @param int $months Number of months.
     *
     * @return void
     */
    public function setMonths($months)
    {
        $this->months = intval($months);
    }

    /**
     * Sets the starting fee.
     *
     * @param float $startFee Starting fee.
     *
     * @return void
     */
    public function setStartFee($startFee)
    {
        $this->startFee = floatval($startFee);
    }

    /**
     * Sets the invoicing/handling fee.
     *
     * @param float $invoiceFee Invoicing fee.
     *
     * @return void
     */
    public function setInvoiceFee($invoiceFee)
    {
        $this->invoiceFee = floatval($invoiceFee);
    }

    /**
     * Sets the interest rate.
     *
     * @param float $interestRate Interest rate.
     *
     * @return void
     */
    public function setInterestRate($interestRate)
    {
        $this->interestRate = floatval($interestRate);
    }

    /**
     * Sets the Minimum amount to use this PClass.
     *
     * @param float $minAmount Minimum amount.
     *
     * @return void
     */
    public function setMinAmount($minAmount)
    {
        $this->minAmount = floatval($minAmount);
    }

    /**
     * Sets the country for this PClass.
     *
     * @param int $country {@link KlarnaCountry} constant.
     *
     * @see KlarnaCountry
     *
     * @return void
     */
    public function setCountry($country)
    {
        $this->country = intval($country);
    }

    /**
     * Sets the ID for this pclass.
     *
     * @param int $id PClass identifier.
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = intval($id);
    }

    /**
     * Sets the type for this pclass.
     *
     * @param int $type PClass type identifier.
     *
     * @see self::CAMPAIGN
     * @see self::ACCOUNT
     * @see self::SPECIAL
     * @see self::FIXED
     * @see self::DELAY
     * @see self::MOBILE
     *
     * @return void
     */
    public function setType($type)
    {
        $this->type = intval($type);
    }


    /**
     * Returns the ID for this PClass.
     *
     * @return int  PClass identifier.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns this PClass's type.
     *
     * @see self::CAMPAIGN
     * @see self::ACCOUNT
     * @see self::SPECIAL
     * @see self::FIXED
     * @see self::DELAY
     * @see self::MOBILE
     *
     * @return int  PClass type identifier.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the Merchant ID or Estore ID connected to this PClass.
     *
     * @return int
     */
    public function getEid()
    {
        return $this->eid;
    }

    /**
     * Merchant ID or Estore ID connected to this PClass.
     *
     * @param int $eid Merchant ID.
     *
     * @return void
     */
    public function setEid($eid)
    {
        $this->eid = intval($eid);
    }

    /**
     * Checks whether this PClass is valid.
     *
     * @param int $now Unix timestamp
     *
     * @return bool
     */
    public function isValid($now = null)
    {
        if ($this->expire == null
            || $this->expire == '-'
            || $this->expire <= 0
        ) {
            //No expire, or unset? assume valid.
            return true;
        }

        if ($now === null || !is_numeric($now)) {
            $now = time();
        }

        //If now is before expire, it is still valid.
        return ($now > $this->expire) ? false : true;
    }

    /**
     * Returns the valid until/expire date unix timestamp.
     *
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Sets the valid until/expire date unix timestamp.
     *
     * @param int $expire unix timestamp for expire
     *
     * @return void
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
    }

    /**
     * Returns the description for this PClass.
     *
     * <b>Note</b>:<br>
     * Encoded with HTML entities.
     *
     * @return string  PClass description.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the number of months for this PClass.
     *
     * @return int  Number of months.
     */
    public function getMonths()
    {
        return $this->months;
    }

    /**
     * Returns the starting fee for this PClass.
     *
     * @return float  Starting fee.
     */
    public function getStartFee()
    {
        return $this->startFee;
    }

    /**
     * Returns the invoicing/handling fee for this PClass.
     *
     * @return float  Invoicing fee.
     */
    public function getInvoiceFee()
    {
        return $this->invoiceFee;
    }

    /**
     * Returns the interest rate for this PClass.
     *
     * @return float  Interest rate.
     */
    public function getInterestRate()
    {
        return $this->interestRate;
    }

    /**
     * Returns the minimum order/product amount for which this PClass is allowed.
     *
     * @return float  Minimum amount to use this PClass.
     */
    public function getMinAmount()
    {
        return $this->minAmount;
    }

    /**
     * Returns the country related to this PClass.
     *
     * @see KlarnaCountry
     * @return int {@link KlarnaCountry} constant.
     */
    public function getCountry()
    {
        return $this->country;
    }

}
