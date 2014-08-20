<?php
/**
 * Klarna API
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
 * This API provides a way to integrate with Klarna's services over the
 * XMLRPC protocol.
 *
 * All strings inputted need to be encoded with ISO-8859-1.<br>
 * In addition you need to decode HTML entities, if they exist.<br>
 *
 * For more information see our
 *
 * Dependencies:
 *
 *  xmlrpc-3.0.0.beta/lib/xmlrpc.inc
 *      from {@link http://phpxmlrpc.sourceforge.net/}
 *
 * xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc
 *      from {@link http://phpxmlrpc.sourceforge.net/}
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
class Klarna
{
    /**
     * Klarna PHP API version identifier.
     *
     * @var string
     */
    protected $VERSION = 'php:api:3.0.0';

    /**
     * Klarna protocol identifier.
     *
     * @var string
     */
    protected $PROTO = '4.1';

    /**
     * Constants used with LIVE mode for the communications with Klarna.
     *
     * @var int
     */
    const LIVE = 0;

    /**
     * URL/Address to the live Klarna Online server.
     * Port used is 443 for SSL and 80 without.
     *
     * @var string
     */
    private static $_live_addr = 'payment.klarna.com';

    /**
     * Constants used with BETA mode for the communications with Klarna.
     *
     * @var int
     */
    const BETA = 1;

    /**
     * URL/Address to the beta test Klarna Online server.
     * Port used is 443 for SSL and 80 without.
     *
     * @var string
     */
    private static $_beta_addr = 'payment.testdrive.klarna.com';

    /**
     * Indicates whether the communications is over SSL or not.
     *
     * @var bool
     */
    protected $ssl = false;

    /**
     * An object of xmlrpc_client, used to communicate with Klarna.
     *
     * @link http://phpxmlrpc.sourceforge.net/
     *
     * @var xmlrpc_client
     */
    protected $xmlrpc;

    /**
     * Which server the Klarna API is using, LIVE or BETA (TESTING).
     *
     * @see Klarna::LIVE
     * @see Klarna::BETA
     *
     * @var int
     */
    protected $mode;

    /**
     * Associative array holding url information.
     *
     * @var array
     */
    private $_url;

    /**
     * The estore's identifier received from Klarna.
     *
     * @var int
     */
    private $_eid;

    /**
     * The estore's shared secret received from Klarna.
     *
     * <b>Note</b>:<br>
     * DO NOT SHARE THIS WITH ANYONE!
     *
     * @var string
     */
    private $_secret;

    /**
     * KlarnaCountry constant.
     *
     * @see KlarnaCountry
     *
     * @var int
     */
    private $_country;

    /**
     * KlarnaCurrency constant.
     *
     * @see KlarnaCurrency
     *
     * @var int
     */
    private $_currency;

    /**
     * KlarnaLanguage constant.
     *
     * @see KlarnaLanguage
     *
     * @var int
     */
    private $_language;

    /**
     * An array of articles for the current order.
     *
     * @var array
     */
    protected $goodsList;

    /**
     * An array of article numbers and quantity.
     *
     * @var array
     */
    protected $artNos;

    /**
     * An KlarnaAddr object containing the billing address.
     *
     * @var KlarnaAddr
     */
    protected $billing;

    /**
     * An KlarnaAddr object containing the shipping address.
     *
     * @var KlarnaAddr
     */
    protected $shipping;

    /**
     * Estore's user(name) or identifier.
     * Only used in {@link Klarna::addTransaction()}.
     *
     * @var string
     */
    protected $estoreUser = "";

    /**
     * External order numbers from other systems.
     *
     * @var string
     */
    protected $orderid = array("", "");

    /**
     * Reference (person) parameter.
     *
     * @var string
     */
    protected $reference = "";

    /**
     * Reference code parameter.
     *
     * @var string
     */
    protected $reference_code = "";

    /**
     * An array of named extra info.
     *
     * @var array
     */
    protected $extraInfo = array();

    /**
     * An array of named bank info.
     *
     * @var array
     */
    protected $bankInfo = array();

    /**
     * An array of named income expense info.
     *
     * @var array
     */
    protected $incomeInfo = array();

    /**
     * An array of named shipment info.
     *
     * @var array
     */
    protected $shipInfo = array();

    /**
     * An array of named travel info.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected $travelInfo = array();

    /**
     * An array of named activate info
     *
     * @ignore
     * @var array
     */
    protected $activateInfo = array();

    /**
     * An array of named session id's.<br>
     * E.g. "dev_id_1" => ...<br>
     *
     * @var array
     */
    protected $sid = array();

    /**
     * A comment sent in the XMLRPC communications.
     * This is resetted using clear().
     *
     * @var string
     */
    protected $comment = "";

    /**
     * An array with all the checkoutHTML objects.
     *
     * @var array
     */
    protected $coObjects = array();

    /**
     * Flag to indicate if the API should output verbose
     * debugging information.
     *
     * @var bool
     */
    public static $debug = false;

    /**
     * Turns on the internal XMLRPC debugging.
     *
     * @var bool
     */
    public static $xmlrpcDebug = false;

    /**
     * If this is set to true, XMLRPC invocation is disabled.
     *
     * @var bool
     */
    public static $disableXMLRPC = false;

    /**
     * If the estore is using a proxy which populates the clients IP to
     * x_forwarded_for
     * then and only then should this be set to true.
     *
     * <b>Note</b>:<br>
     * USE WITH CARE!
     *
     * @var bool
     */
    public static $x_forwarded_for = false;

    /**
     * Array of HTML entities, used to create numeric htmlentities.
     *
     * @ignore Do not show this in PHPDoc.
     * @var array
     */
    protected static $htmlentities = false;

    /**
     * Populated with possible proxy information.
     * A comma separated list of IP addresses.
     *
     * @var string
     */
    private $_x_fwd;

    /**
     * The storage class for PClasses.
     *
     * Use 'xml' for xmlstorage.class.php.<br>
     * Use 'mysql' for mysqlstorage.class.php.<br>
     * Use 'json' for jsonstorage.class.php.<br>
     *
     * @var string
     */
    protected $pcStorage;

    /**
     * The storage URI for PClasses.
     *
     * Use the absolute or relative URI to a file if
     * {@link Klarna::$pcStorage} is set as 'xml' or 'json'.<br>
     * Use a HTTP-auth similar URL if {@link Klarna::$pcStorage} is set
     * as 'mysql', <br>
     * e.g. user:passwd@addr:port/dbName.dbTable.<br>
     * Or an associative array (recommended) {@see MySQLStorage}
     *
     * @var mixed
     */
    protected $pcURI;

    /**
     * PCStorage instance.
     *
     * @ignore Do not show this in PHPDoc.
     * @var PCStorage
     */
    protected $pclasses;

    /**
     * ArrayAccess instance.
     *
     * @ignore Do not show this in PHPDoc.
     * @var ArrayAccess
     */
    protected $config;

    /**
     * Empty constructor, because sometimes it's needed.
     */
    public function __construct()
    {
    }

    /**
     * Checks if the config has fields described in argument.<br>
     * Missing field(s) is in the exception message.
     *
     * To check that the config has eid and secret:<br>
     * <code>
     * try {
     *     $this->hasFields('eid', 'secret');
     * }
     * catch(Exception $e) {
     *     echo "Missing fields: " . $e->getMessage();
     * }
     * </code>
     *
     * @throws Exception
     * @return void
     */
    protected function hasFields(/*variable arguments*/)
    {
        $missingFields = array();
        $args = func_get_args();
        foreach ($args as $field) {
            if (!isset($this->config[$field])) {
                $missingFields[] = $field;
            }
        }
        if (count($missingFields) > 0) {
            throw new Klarna_ConfigFieldMissingException(
                implode(', ', $missingFields)
            );
        }
    }

    /**
     * Initializes the Klarna object accordingly to the set config object.
     *
     * @throws KlarnaException
     * @return void
     */
    protected function init()
    {
        $this->hasFields('eid', 'secret', 'mode', 'pcStorage', 'pcURI');

        if (!is_int($this->config['eid'])) {
            $this->config['eid'] = intval($this->config['eid']);
        }

        if ($this->config['eid'] <= 0) {
            throw new Klarna_ConfigFieldMissingException('eid');
        }

        if (!is_string($this->config['secret'])) {
            $this->config['secret'] = strval($this->config['secret']);
        }

        if (strlen($this->config['secret']) == 0) {
            throw new Klarna_ConfigFieldMissingException('secret');
        }

        //Set the shop id and secret.
        $this->_eid = $this->config['eid'];
        $this->_secret = $this->config['secret'];

        //Set the country specific attributes.
        try {
            $this->hasFields('country', 'language', 'currency');

            //If hasFields doesn't throw exception we can set them all.
            $this->setCountry($this->config['country']);
            $this->setLanguage($this->config['language']);
            $this->setCurrency($this->config['currency']);
        } catch(Exception $e) {
            //fields missing for country, language or currency
            $this->_country = $this->_language = $this->_currency = null;
        }

        //Set addr and port according to mode.
        $this->mode = (int)$this->config['mode'];

        $this->_url = array();

        // If a custom url has been added to the config, use that as xmlrpc
        // recipient.
        if (isset($this->config['url'])) {
            $this->_url = parse_url($this->config['url']);
            if ($this->_url === false) {
                $message = "Configuration value 'url' could not be parsed. " .
                    "(Was: '{$this->config['url']}')";
                Klarna::printDebug(__METHOD__, $message);
                throw new InvalidArgumentException($message);
            }
        } else {

            $this->_url['scheme'] = 'https';

            if ($this->mode === self::LIVE) {
                $this->_url['host'] = self::$_live_addr;
            } else {
                $this->_url['host'] = self::$_beta_addr;
            }

            if (isset($this->config['ssl'])
                && (bool)$this->config['ssl'] === false
            ) {
                $this->_url['scheme'] = 'http';
            }
        }

        // If no port has been specified, deduce from url scheme
        if (!array_key_exists('port', $this->_url)) {
            if ($this->_url['scheme'] === 'https') {
                $this->_url['port'] = 443;
            } else {
                $this->_url['port'] = 80;
            }
        }

        try {
            $this->hasFields('xmlrpcDebug');
            Klarna::$xmlrpcDebug = $this->config['xmlrpcDebug'];
        } catch(Exception $e) {
            //No 'xmlrpcDebug' field ignore it...
        }

        try {
            $this->hasFields('debug');
            Klarna::$debug = $this->config['debug'];
        } catch(Exception $e) {
            //No 'debug' field ignore it...
        }

        $this->pcStorage = $this->config['pcStorage'];
        $this->pcURI = $this->config['pcURI'];

        // Default path to '/' if not set.
        if (!array_key_exists('path', $this->_url)) {
            $this->_url['path'] = '/';
        }

        $this->xmlrpc = new xmlrpc_client(
            $this->_url['path'],
            $this->_url['host'],
            $this->_url['port'],
            $this->_url['scheme']
        );

        $this->xmlrpc->request_charset_encoding = 'ISO-8859-1';
    }

    /**
     * Method of ease for setting common config fields.
     *
     * The storage module for PClasses:<br>
     * Use 'xml' for xmlstorage.class.php.<br>
     * Use 'mysql' for mysqlstorage.class.php.<br>
     * Use 'json' for jsonstorage.class.php.<br>
     *
     * The storage URI for PClasses:<br>
     * Use the absolute or relative URI to a file if {@link Klarna::$pcStorage}
     * is set as 'xml' or 'json'.<br>
     * Use a HTTP-auth similar URL if {@link Klarna::$pcStorage} is set as
     * mysql', e.g. user:passwd@addr:port/dbName.dbTable.
     * Or an associative array (recommended) {@see MySQLStorage}
     *
     * <b>Note</b>:<br>
     * This disables the config file storage.<br>
     *
     * @param int    $eid       Merchant ID/EID
     * @param string $secret    Secret key/Shared key
     * @param int    $country   {@link KlarnaCountry}
     * @param int    $language  {@link KlarnaLanguage}
     * @param int    $currency  {@link KlarnaCurrency}
     * @param int    $mode      {@link Klarna::LIVE} or {@link Klarna::BETA}
     * @param string $pcStorage PClass storage module.
     * @param string $pcURI     PClass URI.
     * @param bool   $ssl       Whether HTTPS (HTTP over SSL) or HTTP is used.
     *
     * @see Klarna::setConfig()
     * @see KlarnaConfig
     *
     * @throws KlarnaException
     * @return void
     */
    public function config(
        $eid, $secret, $country, $language, $currency,
        $mode = Klarna::LIVE, $pcStorage = 'json', $pcURI = 'pclasses.json',
        $ssl = true
    ) {
        try {
            KlarnaConfig::$store = false;
            $this->config = new KlarnaConfig(null);

            $this->config['eid'] = $eid;
            $this->config['secret'] = $secret;
            $this->config['country']  = $country;
            $this->config['language'] = $language;
            $this->config['currency'] = $currency;
            $this->config['mode'] = $mode;
            $this->config['ssl'] = $ssl;
            $this->config['pcStorage'] = $pcStorage;
            $this->config['pcURI'] = $pcURI;

            $this->init();
        } catch(Exception $e) {
            $this->config = null;
            throw new KlarnaException(
                $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Sets and initializes this Klarna object using the supplied config object.
     *
     * @param KlarnaConfig &$config Config object.
     *
     * @see KlarnaConfig
     * @throws  KlarnaException
     * @return  void
     */
    public function setConfig(&$config)
    {
        $this->_checkConfig($config);

        $this->config = $config;
        $this->init();
    }

    /**
     * Get the complete locale (country, language, currency) to use for the
     * values passed, or the configured value if passing null.
     *
     * @param mixed $country  country  constant or code
     * @param mixed $language language constant or code
     * @param mixed $currency currency constant or code
     *
     * @throws KlarnaException
     * @return array
     */
    public function getLocale(
        $country = null, $language = null, $currency = null
    ) {
        $locale = array(
            'country' => null,
            'language' => null,
            'currency' => null
        );

        if ($country === null) {
            // Use the configured country / language / currency
            $locale['country'] = $this->_country;
            if ($this->_language !== null) {
                $locale['language'] = $this->_language;
            }

            if ($this->_currency !== null) {
                $locale['currency'] = $this->_currency;
            }
        } else {
            // Use the given country / language / currency
            if (!is_numeric($country)) {
                $country = KlarnaCountry::fromCode($country);
            }
            $locale['country'] = intval($country);

            if ($language !== null) {
                if (!is_numeric($language)) {
                    $language = KlarnaLanguage::fromCode($language);
                }
                $locale['language'] = intval($language);
            }

            if ($currency !== null) {
                if (!is_numeric($currency)) {
                    $currency = KlarnaCurrency::fromCode($currency);
                }
                $locale['currency'] = intval($currency);
            }
        }

        // Complete partial structure with defaults
        if ($locale['currency'] === null) {
            $locale['currency'] = $this->getCurrencyForCountry(
                $locale['country']
            );
        }

        if ($locale['language'] === null) {
            $locale['language'] = $this->getLanguageForCountry(
                $locale['country']
            );
        }

        $this->_checkCountry($locale['country']);
        $this->_checkCurrency($locale['currency']);
        $this->_checkLanguage($locale['language']);

        return $locale;
    }

    /**
     * Sets the country used.
     *
     * <b>Note</b>:<br>
     * If you input 'dk', 'fi', 'de', 'nl', 'no' or 'se', <br>
     * then currency and language will be set to mirror that country.<br>
     *
     * @param string|int $country {@link KlarnaCountry}
     *
     * @see KlarnaCountry
     *
     * @throws KlarnaException
     * @return void
     */
    public function setCountry($country)
    {
        if (!is_numeric($country)
            && (strlen($country) == 2 || strlen($country) == 3)
        ) {
            $country = KlarnaCountry::fromCode($country);
        }
        $this->_checkCountry($country);
        $this->_country = $country;
    }

    /**
     * Returns the country code for the set country constant.
     *
     * @param int $country {@link KlarnaCountry Country} constant.
     *
     * @return string  Two letter code, e.g. "se", "no", etc.
     */
    public function getCountryCode($country = null)
    {
        if ($country === null) {
            $country = $this->_country;
        }

        $code = KlarnaCountry::getCode($country);
        return (string) $code;
    }

    /**
     * Returns the {@link KlarnaCountry country} constant from the country code.
     *
     * @param string $code Two letter code, e.g. "se", "no", etc.
     *
     * @throws KlarnaException
     * @return int {@link KlarnaCountry Country} constant.
     */
    public static function getCountryForCode($code)
    {
        $country = KlarnaCountry::fromCode($code);
        if ($country === null) {
            throw new Klarna_UnknownCountryException($code);
        }
        return $country;
    }

    /**
     * Returns the country constant.
     *
     * @return int  {@link KlarnaCountry}
     */
    public function getCountry()
    {
        return $this->_country;
    }

    /**
     * Sets the language used.
     *
     * <b>Note</b>:<br>
     * You can use the two letter language code instead of the constant.<br>
     * E.g. 'da' instead of using {@link KlarnaLanguage::DA}.<br>
     *
     * @param string|int $language {@link KlarnaLanguage}
     *
     * @see KlarnaLanguage
     *
     * @throws KlarnaException
     * @return void
     */
    public function setLanguage($language)
    {
        if (!is_numeric($language) && strlen($language) == 2) {
            $this->setLanguage(self::getLanguageForCode($language));
        } else {
            $this->_checkLanguage($language);
            $this->_language = $language;
        }
    }

    /**
     * Returns the language code for the set language constant.
     *
     * @param int $language {@link KlarnaLanguage Language} constant.
     *
     * @return string Two letter code, e.g. "da", "de", etc.
     */
    public function getLanguageCode($language = null)
    {
        if ($language === null) {
            $language = $this->_language;
        }
        $code = KlarnaLanguage::getCode($language);

        return (string) $code;
    }

    /**
     * Returns the {@link KlarnaLanguage language} constant from the language code.
     *
     * @param string $code Two letter code, e.g. "da", "de", etc.
     *
     * @throws KlarnaException
     * @return int  {@link KlarnaLanguage Language} constant.
     */
    public static function getLanguageForCode($code)
    {
        $language = KlarnaLanguage::fromCode($code);

        if ($language === null) {
            throw new Klarna_UnknownLanguageException($code);
        }
        return $language;
    }

    /**
     * Returns the language constant.
     *
     * @return int  {@link KlarnaLanguage}
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * Sets the currency used.
     *
     * <b>Note</b>:<br>
     * You can use the three letter shortening of the currency.<br>
     * E.g. "dkk", "eur", "nok" or "sek" instead of the constant.<br>
     *
     * @param string|int $currency {@link KlarnaCurrency}
     *
     * @see KlarnaCurrency
     *
     * @throws KlarnaException
     * @return void
     */
    public function setCurrency($currency)
    {
        if (!is_numeric($currency) && strlen($currency) == 3) {
            $this->setCurrency(self::getCurrencyForCode($currency));
        } else {
            $this->_checkCurrency($currency);
            $this->_currency = $currency;
        }
    }

    /**
     * Returns the {@link KlarnaCurrency currency} constant from the currency
     * code.
     *
     * @param string $code Two letter code, e.g. "dkk", "eur", etc.
     *
     * @throws KlarnaException
     * @return int  {@link KlarnaCurrency Currency} constant.
     */
    public static function getCurrencyForCode($code)
    {
        $currency = KlarnaCurrency::fromCode($code);
        if ($currency === null) {
            throw new Klarna_UnknownCurrencyException($code);
        }
        return $currency;
    }

    /**
     * Returns the the currency code for the set currency constant.
     *
     * @param int $currency {@link KlarnaCurrency Currency} constant.
     *
     * @return string  Three letter currency code.
     */
    public function getCurrencyCode($currency = null)
    {
        if ($currency === null) {
            $currency = $this->_currency;
        }

        $code = KlarnaCurrency::getCode($currency);
        return (string) $code;
    }

    /**
     * Returns the set currency constant.
     *
     * @return int  {@link KlarnaCurrency}
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * Returns the {@link KlarnaLanguage language} constant for the specified
     * or set country.
     *
     * @param int $country {@link KlarnaCountry Country} constant.
     *
     * @deprecated Do not use.
     *
     * @return int|false if no match otherwise KlarnaLanguage constant.
     */
    public function getLanguageForCountry($country = null)
    {
        if ($country === null) {
            $country = $this->_country;
        }
        // Since getLanguage defaults to EN, check so we actually have a match
        $language = KlarnaCountry::getLanguage($country);
        if (KlarnaCountry::checkLanguage($country, $language)) {
            return $language;
        }
        return false;
    }

    /**
     * Returns the {@link KlarnaCurrency currency} constant for the specified
     * or set country.
     *
     * @param int $country {@link KlarnaCountry country} constant.
     *
     * @deprecated Do not use.
     *
     * @return int|false {@link KlarnaCurrency currency} constant.
     */
    public function getCurrencyForCountry($country = null)
    {
        if ($country === null) {
            $country = $this->_country;
        }
        return KlarnaCountry::getCurrency($country);
    }

    /**
     * Sets the session id's for various device identification,
     * behaviour identification software.
     *
     * <b>Available named session id's</b>:<br>
     * string - dev_id_1<br>
     * string - dev_id_2<br>
     * string - dev_id_3<br>
     * string - beh_id_1<br>
     * string - beh_id_2<br>
     * string - beh_id_3<br>
     *
     * @param string $name Session ID identifier, e.g. 'dev_id_1'.
     * @param string $sid  Session ID.
     *
     * @throws KlarnaException
     * @return void
     */
    public function setSessionID($name, $sid)
    {
        $this->_checkArgument($name, "name");
        $this->_checkArgument($sid, "sid");

        $this->sid[$name] = $sid;
    }

    /**
     * Sets the shipment information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * <b>Available named values are</b>:<br>
     * int    - delay_adjust<br>
     * string - shipping_company<br>
     * string - shipping_product<br>
     * string - tracking_no<br>
     * array  - warehouse_addr<br>
     *
     * "warehouse_addr" is sent using {@link KlarnaAddr::toArray()}.
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.
     *
     * @param string $name  key
     * @param mixed  $value value
     *
     * @throws KlarnaException
     * @return void
     */
    public function setShipmentInfo($name, $value)
    {
        $this->_checkArgument($name, "name");

        $this->shipInfo[$name] = $value;
    }

    /**
     * Sets the Activation information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * <b>Available named values are</b>:<br>
     * int    - flags<br>
     * int    - bclass<br>
     * string - orderid1<br>
     * string - orderid2<br>
     * string - ocr<br>
     * string - reference<br>
     * string - reference_code<br>
     * string - cust_no<br>
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.
     *
     * @param string $name  key
     * @param mixed  $value value
     *
     * @see setShipmentInfo
     *
     * @return void
     */
    public function setActivateInfo($name, $value)
    {
        $this->activateInfo[$name] = $value;
    }

    /**
     * Sets the extra information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * <b>Available named values are</b>:<br>
     * string - cust_no<br>
     * string - estore_user<br>
     * string - ready_date<br>
     * string - rand_string<br>
     * int    - bclass<br>
     * string - pin<br>
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.
     *
     * @param string $name  key
     * @param mixed  $value value
     *
     * @throws KlarnaException
     * @return void
     */
    public function setExtraInfo($name, $value)
    {
        $this->_checkArgument($name, "name");

        $this->extraInfo[$name] = $value;
    }

    /**
     * Sets the income expense information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.
     *
     * @param string $name  key
     * @param mixed  $value value
     *
     * @throws KlarnaException
     * @return void
     */
    public function setIncomeInfo($name, $value)
    {
        $this->_checkArgument($name, "name");

        $this->incomeInfo[$name] = $value;
    }

    /**
     * Sets the bank information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.
     *
     * @param string $name  key
     * @param mixed  $value value
     *
     * @throws KlarnaException
     * @return void
     */
    public function setBankInfo($name, $value)
    {
        $this->_checkArgument($name, "name");

        $this->bankInfo[$name] = $value;
    }

    /**
     * Sets the travel information for the upcoming transaction.<br>
     *
     * Using this method is optional.
     *
     * Make sure you send in the values as the right data type.<br>
     * Use strval, intval or similar methods to ensure the right type is sent.
     *
     * @param string $name  key
     * @param mixed  $value value
     *
     * @throws KlarnaException
     * @return void
     */
    public function setTravelInfo($name, $value)
    {
        $this->_checkArgument($name, "name");

        $this->travelInfo[$name] = $value;
    }

    /**
     * Returns the clients IP address.
     *
     * @return string
     */
    public function getClientIP()
    {
        $tmp_ip = '';
        $x_fwd = null;

        //Proxy handling.
        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $tmp_ip = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $x_fwd = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }

        if (self::$x_forwarded_for && ($x_fwd !== null)) {
            $forwarded = explode(",", $x_fwd);
            return trim($forwarded[0]);
        }

        return $tmp_ip;
    }

    /**
     * Sets the specified address for the current order.
     *
     * <b>Address type can be</b>:<br>
     * {@link KlarnaFlags::IS_SHIPPING}<br>
     * {@link KlarnaFlags::IS_BILLING}<br>
     *
     * @param int        $type Address type.
     * @param KlarnaAddr $addr Specified address.
     *
     * @throws KlarnaException
     * @return void
     */
    public function setAddress($type, $addr)
    {
        if (!($addr instanceof KlarnaAddr)) {
            throw new Klarna_InvalidKlarnaAddrException;
        }

        if ($addr->isCompany === null) {
            $addr->isCompany = false;
        }

        if ($type === KlarnaFlags::IS_SHIPPING) {
            $this->shipping = $addr;
            self::printDebug("shipping address array", $this->shipping);
            return;
        }

        if ($type === KlarnaFlags::IS_BILLING) {
            $this->billing = $addr;
            self::printDebug("billing address array", $this->billing);
            return;
        }
        throw new Klarna_UnknownAddressTypeException($type);
    }

    /**
     * Sets order id's from other systems for the upcoming transaction.<br>
     * User is only sent with {@link Klarna::addTransaction()}.<br>
     *
     * @param string $orderid1 order id 1
     * @param string $orderid2 order id 2
     * @param string $user     username
     *
     * @see Klarna::setExtraInfo()
     *
     * @throws KlarnaException
     * @return void
     */
    public function setEstoreInfo($orderid1 = "", $orderid2 = "", $user = "")
    {
        if (!is_string($orderid1)) {
            $orderid1 = strval($orderid1);
        }

        if (!is_string($orderid2)) {
            $orderid2 = strval($orderid2);
        }

        if (!is_string($user)) {
            $user = strval($user);
        }

        if (strlen($user) > 0 ) {
            $this->setExtraInfo('estore_user', $user);
        }

        $this->orderid[0] = $orderid1;
        $this->orderid[1] = $orderid2;
    }

    /**
     * Sets the reference (person) and reference code, for the upcoming
     * transaction.
     *
     * If this is omitted, it can grab first name, last name from the address
     * and use that as a reference person.
     *
     * @param string $ref  Reference person / message to customer on invoice.
     * @param string $code Reference code / message to customer on invoice.
     *
     * @return void
     */
    public function setReference($ref, $code)
    {
        $this->_checkRef($ref, $code);
        $this->reference = $ref;
        $this->reference_code = $code;
    }

    /**
     * Returns the reference (person).
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Returns an associative array used to send the address to Klarna.
     * TODO: Kill it all
     *
     * @param KlarnaAddr $addr Address object to assemble.
     *
     * @throws KlarnaException
     * @return array The address for the specified method.
     */
    protected function assembleAddr($addr)
    {
        if (!($addr instanceof KlarnaAddr)) {
            throw new Klarna_InvalidKlarnaAddrException;
        }

        return $addr->toArray();
    }

    /**
     * Sets the comment field, which can be shown in the invoice.
     *
     * @param string $data comment to set
     *
     * @return void
     */
    public function setComment($data)
    {
        $this->comment = $data;
    }

    /**
     * Adds an additional comment to the comment field. Appends with a newline.
     *
     * @param string $data comment to add
     *
     * @see Klarna::setComment()
     *
     * @return void
     */
    public function addComment($data)
    {
        $this->comment .= "\n".$data;
    }

    /**
     * Returns the PNO/SSN encoding constant for currently set country.
     *
     * <b>Note</b>:<br>
     * Country, language and currency needs to match!
     *
     * @throws KlarnaException
     * @return int  {@link KlarnaEncoding} constant.
     */
    public function getPNOEncoding()
    {
        $this->_checkLocale();

        $country = KlarnaCountry::getCode($this->_country);

        return KlarnaEncoding::get($country);
    }

    /**
     * Purpose: The get_addresses function is used to retrieve a customer's
     * address(es). Using this, the customer is not required to enter any
     * information, only confirm the one presented to him/her.<br>
     *
     * The get_addresses function can also be used for companies.<br>
     * If the customer enters a company number, it will return all the
     * addresses where the company is registered at.<br>
     *
     * The get_addresses function is ONLY allowed to be used for Swedish
     * persons with the following conditions:
     * <ul>
     *     <li>
     *          It can be only used if invoice or part payment is
     *          the default payment method
     *     </li>
     *     <li>
     *          It has to disappear if the customer chooses another
     *          payment method
     *     </li>
     *     <li>
     *          The button is not allowed to be called "get address", but
     *          "continue" or<br>
     *          it can be picked up automatically when all the numbers have
     *          been typed.
     *     </li>
     * </ul>
     *
     * <b>Type can be one of these</b>:<br>
     * {@link KlarnaFlags::GA_ALL},<br>
     * {@link KlarnaFlags::GA_LAST},<br>
     * {@link KlarnaFlags::GA_GIVEN}.<br>
     *
     * @param string $pno      Social security number, personal number, ...
     * @param int    $encoding {@link KlarnaEncoding PNO Encoding} constant.
     * @param int    $type     Specifies returned information.
     *
     * @throws KlarnaException
     * @return array   An array of {@link KlarnaAddr} objects.
     */
    public function getAddresses(
        $pno, $encoding = null, $type = KlarnaFlags::GA_GIVEN
    ) {
        if ($this->_country !== KlarnaCountry::SE) {
            throw new Klarna_UnsupportedMarketException("Sweden");
        }

        //Get the PNO/SSN encoding constant.
        if ($encoding === null) {
            $encoding = $this->getPNOEncoding();
        }

        $this->_checkPNO($pno, $encoding);

        $digestSecret = self::digest(
            $this->colon(
                $this->_eid, $pno, $this->_secret
            )
        );

        $paramList = array(
            $pno,
            $this->_eid,
            $digestSecret,
            $encoding,
            $type,
            $this->getClientIP()
        );

        self::printDebug("get_addresses array", $paramList);

        $result = $this->xmlrpc_call('get_addresses', $paramList);

        self::printDebug("get_addresses result array", $result);

        $addrs = array();
        foreach ($result as $tmpAddr) {
            try {
                $addr = new KlarnaAddr();
                if ($type === KlarnaFlags::GA_GIVEN) {
                    $addr->isCompany = (count($tmpAddr) == 5) ? true : false;
                    if ($addr->isCompany) {
                        $addr->setCompanyName($tmpAddr[0]);
                        $addr->setStreet($tmpAddr[1]);
                        $addr->setZipCode($tmpAddr[2]);
                        $addr->setCity($tmpAddr[3]);
                        $addr->setCountry($tmpAddr[4]);
                    } else {
                        $addr->setFirstName($tmpAddr[0]);
                        $addr->setLastName($tmpAddr[1]);
                        $addr->setStreet($tmpAddr[2]);
                        $addr->setZipCode($tmpAddr[3]);
                        $addr->setCity($tmpAddr[4]);
                        $addr->setCountry($tmpAddr[5]);
                    }
                } else if ($type === KlarnaFlags::GA_LAST) {
                    // Here we cannot decide if it is a company or not?
                    // Assume private person.
                    $addr->setLastName($tmpAddr[0]);
                    $addr->setStreet($tmpAddr[1]);
                    $addr->setZipCode($tmpAddr[2]);
                    $addr->setCity($tmpAddr[3]);
                    $addr->setCountry($tmpAddr[4]);
                } else if ($type === KlarnaFlags::GA_ALL) {
                    if (strlen($tmpAddr[0]) > 0) {
                        $addr->setFirstName($tmpAddr[0]);
                        $addr->setLastName($tmpAddr[1]);
                    } else {
                        $addr->isCompany = true;
                        $addr->setCompanyName($tmpAddr[1]);
                    }
                    $addr->setStreet($tmpAddr[2]);
                    $addr->setZipCode($tmpAddr[3]);
                    $addr->setCity($tmpAddr[4]);
                    $addr->setCountry($tmpAddr[5]);
                } else {
                    continue;
                }
                $addrs[] = $addr;
            } catch(Exception $e) {
                //Silently fail
            }
        }

        return $addrs;
    }

    /**
     * Adds an article to the current goods list for the current order.
     *
     * <b>Note</b>:<br>
     * It is recommended that you use {@link KlarnaFlags::INC_VAT}.<br>
     *
     * <b>Flags can be</b>:<br>
     * {@link KlarnaFlags::INC_VAT}<br>
     * {@link KlarnaFlags::IS_SHIPMENT}<br>
     * {@link KlarnaFlags::IS_HANDLING}<br>
     * {@link KlarnaFlags::PRINT_1000}<br>
     * {@link KlarnaFlags::PRINT_100}<br>
     * {@link KlarnaFlags::PRINT_10}<br>
     * {@link KlarnaFlags::NO_FLAG}<br>
     *
     * Some flags can be added to each other for multiple options.
     *
     * @param int    $qty      Quantity.
     * @param string $artNo    Article number.
     * @param string $title    Article title.
     * @param int    $price    Article price.
     * @param float  $vat      VAT in percent, e.g. 25% is inputted as 25.
     * @param float  $discount Possible discount on article.
     * @param int    $flags    Options which specify the article
     *                         ({@link KlarnaFlags::IS_HANDLING}) and it's price
     *                         ({@link KlarnaFlags::INC_VAT})
     *
     * @see Klarna::addTransaction()
     * @see Klarna::reserveAmount()
     * @see Klarna::activateReservation()
     *
     * @throws KlarnaException
     * @return void
     */
    public function addArticle(
        $qty, $artNo, $title, $price, $vat, $discount = 0,
        $flags = KlarnaFlags::INC_VAT
    ) {
        $this->_checkQty($qty);

        // Either artno or title has to be set
        if ((($artNo === null ) || ($artNo == ""))
            && (($title === null ) || ($title == ""))
        ) {
            throw new Klarna_ArgumentNotSetException('Title and ArtNo', 50026);
        }

        $this->_checkPrice($price);
        $this->_checkVAT($vat);
        $this->_checkDiscount($discount);
        $this->_checkInt($flags, 'flags');

        //Create goodsList array if not set.
        if (!$this->goodsList || !is_array($this->goodsList)) {
            $this->goodsList = array();
        }

        //Populate a temp array with the article details.
        $tmpArr = array(
            "artno" => $artNo,
            "title" => $title,
            "price" => $price,
            "vat" => $vat,
            "discount" => $discount,
            "flags" => $flags
        );

        //Add the temp array and quantity field to the internal goods list.
        $this->goodsList[] = array(
                "goods" => $tmpArr,
                "qty"   => $qty
        );

        if (count($this->goodsList) > 0) {
            self::printDebug(
                "article added",
                $this->goodsList[count($this->goodsList)-1]
            );
        }
    }

    /**
     * Assembles and sends the current order to Klarna.<br>
     * This clears all relevant data if $clear is set to true.<br>
     *
     * <b>This method returns an array with</b>:<br>
     * Invoice number<br>
     * Order status flag<br>
     *
     * If the flag {@link KlarnaFlags::RETURN_OCR} is used:<br>
     * Invoice number<br>
     * OCR number <br>
     * Order status flag<br>
     *
     * <b>Order status can be</b>:<br>
     * {@link KlarnaFlags::ACCEPTED}<br>
     * {@link KlarnaFlags::PENDING}<br>
     * {@link KlarnaFlags::DENIED}<br>
     *
     * Gender is only required for Germany and Netherlands.<br>
     *
     * <b>Flags can be</b>:<br>
     * {@link KlarnaFlags::NO_FLAG}<br>
     * {@link KlarnaFlags::TEST_MODE}<br>
     * {@link KlarnaFlags::AUTO_ACTIVATE}<br>
     * {@link KlarnaFlags::SENSITIVE_ORDER}<br>
     * {@link KlarnaFlags::RETURN_OCR}<br>
     * {@link KlarnaFlags::M_PHONE_TRANSACTION}<br>
     * {@link KlarnaFlags::M_SEND_PHONE_PIN}<br>
     *
     * Some flags can be added to each other for multiple options.
     *
     * <b>Note</b>:<br>
     * Normal shipment type is assumed unless otherwise specified,
     * ou can do this by calling:<br>
     * {@link Klarna::setShipmentInfo() setShipmentInfo('delay_adjust', ...)}
     * with either:<br>
     * {@link KlarnaFlags::NORMAL_SHIPMENT NORMAL_SHIPMENT} or
     * {@link KlarnaFlags::EXPRESS_SHIPMENT EXPRESS_SHIPMENT}<br>
     *
     * @param string $pno      Personal number, SSN, date of birth, etc.
     * @param int    $gender   {@link KlarnaFlags::FEMALE} or
     *                         {@link KlarnaFlags::MALE},
     *                         null or "" for unspecified.
     * @param int    $flags    Options which affect the behaviour.
     * @param int    $pclass   PClass id used for this invoice.
     * @param int    $encoding {@link KlarnaEncoding Encoding} constant for the
     *                         PNO parameter.
     * @param bool   $clear    Whether customer info should be cleared after
     *                         this call or not.
     *
     * @throws KlarnaException
     * @return array An array with invoice number and order status. [string, int]
     */
    public function addTransaction(
        $pno, $gender, $flags = KlarnaFlags::NO_FLAG,
        $pclass = KlarnaPClass::INVOICE, $encoding = null, $clear = true
    ) {
        $this->_checkLocale(50023);

        //Get the PNO/SSN encoding constant.
        if ($encoding === null) {
            $encoding = $this->getPNOEncoding();
        }

        if (!($flags & KlarnaFlags::PRE_PAY)) {
            $this->_checkPNO($pno, $encoding);
        }

        if ($gender === 'm') {
            $gender = KlarnaFlags::MALE;
        } else if ($gender === 'f') {
            $gender = KlarnaFlags::FEMALE;
        }

        if ($gender !== null && strlen($gender) > 0) {
            $this->_checkInt($gender, 'gender');
        }

        $this->_checkInt($flags,  'flags');
        $this->_checkInt($pclass, 'pclass');

        //Check so required information is set.
        $this->_checkGoodslist();

        //We need at least one address set
        if (!($this->billing instanceof KlarnaAddr)
            && !($this->shipping instanceof KlarnaAddr)
        ) {
            throw new Klarna_MissingAddressException;
        }

        //If only one address is set, copy to the other address.
        if (!($this->shipping instanceof KlarnaAddr)
            && ($this->billing instanceof KlarnaAddr)
        ) {
            $this->shipping = $this->billing;
        } else if (!($this->billing instanceof KlarnaAddr)
            && ($this->shipping instanceof KlarnaAddr)
        ) {
            $this->billing = $this->shipping;
        }

        //Assume normal shipment unless otherwise specified.
        if (!isset($this->shipInfo['delay_adjust'])) {
            $this->setShipmentInfo('delay_adjust', KlarnaFlags::NORMAL_SHIPMENT);
        }

        //Make sure we get any session ID's or similar
        $this->initCheckout();

        //function add_transaction_digest
        $string = "";
        foreach ($this->goodsList as $goods) {
            $string .= $goods['goods']['title'] .':';
        }
        $digestSecret = self::digest($string . $this->_secret);
        //end function add_transaction_digest

        $billing = $this->assembleAddr($this->billing);
        $shipping = $this->assembleAddr($this->shipping);

        //Shipping country must match specified country!
        if (strlen($shipping['country']) > 0
            && ($shipping['country'] !== $this->_country)
        ) {
            throw new Klarna_ShippingCountryException;
        }

        $paramList = array(
            $pno,
            $gender,
            $this->reference,
            $this->reference_code,
            $this->orderid[0],
            $this->orderid[1],
            $shipping,
            $billing,
            $this->getClientIP(),
            $flags,
            $this->_currency,
            $this->_country,
            $this->_language,
            $this->_eid,
            $digestSecret,
            $encoding,
            $pclass,
            $this->goodsList,
            $this->comment,
            $this->shipInfo,
            $this->travelInfo,
            $this->incomeInfo,
            $this->bankInfo,
            $this->sid,
            $this->extraInfo
        );

        self::printDebug('add_invoice', $paramList);

        $result = $this->xmlrpc_call('add_invoice', $paramList);

        if ($clear === true) {
            //Make sure any stored values that need to be unique between
            //purchases are cleared.
            foreach ($this->coObjects as $co) {
                $co->clear();
            }
            $this->clear();
        }

        self::printDebug('add_invoice result', $result);

        return $result;
    }


    /**
     * Activates previously created invoice
     * (from {@link Klarna::addTransaction()}).
     *
     * <b>Note</b>:<br>
     * If you want to change the shipment type, you can specify it using:
     * {@link Klarna::setShipmentInfo() setShipmentInfo('delay_adjust', ...)}
     * with either: {@link KlarnaFlags::NORMAL_SHIPMENT NORMAL_SHIPMENT} or
     * {@link KlarnaFlags::EXPRESS_SHIPMENT EXPRESS_SHIPMENT}
     *
     * @param string $invNo  Invoice number.
     * @param int    $pclass PClass id used for this invoice.
     * @param bool   $clear  Whether customer info should be cleared after this
     *                       call.
     *
     * @see Klarna::setShipmentInfo()
     *
     * @throws KlarnaException
     * @return string  An URL to the PDF invoice.
     */
    public function activateInvoice(
        $invNo, $pclass = KlarnaPClass::INVOICE, $clear = true
    ) {
        $this->_checkInvNo($invNo);

        $digestSecret = self::digest(
            $this->colon($this->_eid, $invNo, $this->_secret)
        );

        $paramList = array(
            $this->_eid,
            $invNo,
            $digestSecret,
            $pclass,
            $this->shipInfo
        );

        self::printDebug('activate_invoice', $paramList);

        $result = $this->xmlrpc_call('activate_invoice', $paramList);

        if ($clear === true) {
            $this->clear();
        }

        self::printDebug('activate_invoice result', $result);

        return $result;
    }

    /**
     * Removes a passive invoices which has previously been created with
     * {@link Klarna::addTransaction()}.
     * True is returned if the invoice was successfully removed, otherwise an
     * exception is thrown.<br>
     *
     * @param string $invNo Invoice number.
     *
     * @throws KlarnaException
     * @return bool
     */
    public function deleteInvoice($invNo)
    {
        $this->_checkInvNo($invNo);

        $digestSecret = self::digest(
            $this->colon($this->_eid, $invNo, $this->_secret)
        );

        $paramList = array(
            $this->_eid,
            $invNo,
            $digestSecret
        );

        self::printDebug('delete_invoice', $paramList);

        $result = $this->xmlrpc_call('delete_invoice', $paramList);

        return ($result == 'ok') ? true : false;
    }

    /**
     * Summarizes the prices of the held goods list
     *
     * @return int total amount
     */
    public function summarizeGoodsList()
    {
        $amount = 0;
        if (!is_array($this->goodsList)) {
            return $amount;
        }
        foreach ($this->goodsList as $goods) {
            $price = $goods['goods']['price'];

            // Add VAT if price is Excluding VAT
            if (($goods['goods']['flags'] & KlarnaFlags::INC_VAT) === 0) {
                $vat = $goods['goods']['vat'] / 100.0;
                $price *= (1.0 + $vat);
            }

            // Reduce discounts
            if ($goods['goods']['discount'] > 0) {
                $discount = $goods['goods']['discount'] / 100.0;
                $price *= (1.0 - $discount);
            }

            $amount += $price * (int)$goods['qty'];
        }
        return $amount;
    }

    /**
     * Reserves a purchase amount for a specific customer. <br>
     * The reservation is valid, by default, for 7 days.<br>
     *
     * <b>This method returns an array with</b>:<br>
     * A reservation number (rno)<br>
     * Order status flag<br>
     *
     * <b>Order status can be</b>:<br>
     * {@link KlarnaFlags::ACCEPTED}<br>
     * {@link KlarnaFlags::PENDING}<br>
     * {@link KlarnaFlags::DENIED}<br>
     *
     * <b>Please note</b>:<br>
     * Activation must be done with activate_reservation, i.e. you cannot
     * activate through Klarna Online.
     *
     * Gender is only required for Germany and Netherlands.<br>
     *
     * <b>Flags can be set to</b>:<br>
     * {@link KlarnaFlags::NO_FLAG}<br>
     * {@link KlarnaFlags::TEST_MODE}<br>
     * {@link KlarnaFlags::RSRV_SENSITIVE_ORDER}<br>
     * {@link KlarnaFlags::RSRV_PHONE_TRANSACTION}<br>
     * {@link KlarnaFlags::RSRV_SEND_PHONE_PIN}<br>
     *
     * Some flags can be added to each other for multiple options.
     *
     * <b>Note</b>:<br>
     * Normal shipment type is assumed unless otherwise specified, you can do
     * this by calling:<br>
     * {@link Klarna::setShipmentInfo() setShipmentInfo('delay_adjust', ...)}
     * with either: {@link KlarnaFlags::NORMAL_SHIPMENT NORMAL_SHIPMENT} or
     * {@link KlarnaFlags::EXPRESS_SHIPMENT EXPRESS_SHIPMENT}<br>
     *
     * @param string $pno      Personal number, SSN, date of birth, etc.
     * @param int    $gender   {@link KlarnaFlags::FEMALE} or
     *                         {@link KlarnaFlags::MALE}, null for unspecified.
     * @param int    $amount   Amount to be reserved, including VAT.
     * @param int    $flags    Options which affect the behaviour.
     * @param int    $pclass   {@link KlarnaPClass::getId() PClass ID}.
     * @param int    $encoding {@link KlarnaEncoding PNO Encoding} constant.
     * @param bool   $clear    Whether customer info should be cleared after
     *                         this call.
     *
     * @throws KlarnaException
     * @return array An array with reservation number and order
     *               status. [string, int]
     */
    public function reserveAmount(
        $pno, $gender, $amount, $flags = 0, $pclass = KlarnaPClass::INVOICE,
        $encoding = null, $clear = true
    ) {
        $this->_checkLocale();

        //Get the PNO/SSN encoding constant.
        if ($encoding === null) {
            $encoding = $this->getPNOEncoding();
        }

        $this->_checkPNO($pno, $encoding);

        if ($gender === 'm') {
            $gender = KlarnaFlags::MALE;
        } else if ($gender === 'f') {
            $gender = KlarnaFlags::FEMALE;
        }
        if ($gender !== null && strlen($gender) > 0) {
            $this->_checkInt($gender, 'gender');
        }

        $this->_checkInt($flags,  'flags');
        $this->_checkInt($pclass, 'pclass');

        //Check so required information is set.
        $this->_checkGoodslist();


        //Calculate automatically the amount from goodsList.
        if ($amount === -1) {
            $amount = (int)round($this->summarizeGoodsList());
        } else {
            $this->_checkAmount($amount);
        }

        if ($amount < 0) {
            throw new Klarna_InvalidPriceException($amount);
        }

        //No addresses used for phone transactions
        if ($flags & KlarnaFlags::RSRV_PHONE_TRANSACTION) {
            $billing = $shipping = '';
        } else {
            $billing = $this->assembleAddr($this->billing);
            $shipping = $this->assembleAddr($this->shipping);

            if (strlen($shipping['country']) > 0
                && ($shipping['country'] !== $this->_country)
            ) {
                throw new Klarna_ShippingCountryException;
            }
        }

        //Assume normal shipment unless otherwise specified.
        if (!isset($this->shipInfo['delay_adjust'])) {
            $this->setShipmentInfo('delay_adjust', KlarnaFlags::NORMAL_SHIPMENT);
        }

        //Make sure we get any session ID's or similar
        $this->initCheckout($this, $this->_eid);

        $digestSecret = self::digest(
            "{$this->_eid}:{$pno}:{$amount}:{$this->_secret}"
        );

        $paramList = array(
            $pno,
            $gender,
            $amount,
            $this->reference,
            $this->reference_code,
            $this->orderid[0],
            $this->orderid[1],
            $shipping,
            $billing,
            $this->getClientIP(),
            $flags,
            $this->_currency,
            $this->_country,
            $this->_language,
            $this->_eid,
            $digestSecret,
            $encoding, $pclass,
            $this->goodsList,
            $this->comment,
            $this->shipInfo,
            $this->travelInfo,
            $this->incomeInfo,
            $this->bankInfo,
            $this->sid,
            $this->extraInfo
        );

        self::printDebug('reserve_amount', $paramList);

        $result = $this->xmlrpc_call('reserve_amount', $paramList);

        if ($clear === true) {
            //Make sure any stored values that need to be unique between
            //purchases are cleared.
            foreach ($this->coObjects as $co) {
                $co->clear();
            }
            $this->clear();
        }

        self::printDebug('reserve_amount result', $result);

        return $result;
    }

    /**
     * Cancels a reservation.
     *
     * @param string $rno Reservation number.
     *
     *
     * @throws KlarnaException
     * @return bool True, if the cancellation was successful.
     */
    public function cancelReservation($rno)
    {
        $this->_checkRNO($rno);

        $digestSecret = self::digest(
            $this->colon($this->_eid, $rno, $this->_secret)
        );
        $paramList = array(
            $rno,
            $this->_eid,
            $digestSecret
        );

        self::printDebug('cancel_reservation', $paramList);

        $result = $this->xmlrpc_call('cancel_reservation', $paramList);

        return ($result == 'ok');
    }

    /**
     * Changes specified reservation to a new amount.
     *
     * <b>Flags can be either of these</b>:<br>
     * {@link KlarnaFlags::NEW_AMOUNT}<br>
     * {@link KlarnaFlags::ADD_AMOUNT}<br>
     *
     * @param string $rno    Reservation number.
     * @param int    $amount Amount including VAT.
     * @param int    $flags  Options which affect the behaviour.
     *
     *
     * @throws KlarnaException
     * @return bool    True, if the change was successful.
     */
    public function changeReservation(
        $rno, $amount, $flags = KlarnaFlags::NEW_AMOUNT
    ) {
        $this->_checkRNO($rno);
        $this->_checkAmount($amount);
        $this->_checkInt($flags, 'flags');

        $digestSecret = self::digest(
            $this->colon($this->_eid, $rno, $amount, $this->_secret)
        );
        $paramList = array(
            $rno,
            $amount,
            $this->_eid,
            $digestSecret,
            $flags
        );

        self::printDebug('change_reservation', $paramList);

        $result = $this->xmlrpc_call('change_reservation', $paramList);

        return ($result  == 'ok') ? true : false;
    }

    /**
     * Update the reservation matching the given reservation number.
     *
     * @param string  $rno   Reservation number
     * @param boolean $clear clear set data aftre updating. Defaulted to true.
     *
     * @throws KlarnaException if no RNO is given, or if an error is recieved
     *         from Klarna Online.
     *
     * @return true if the update was successful
     */
    public function update($rno, $clear = true)
    {
        $rno = strval($rno);

        // All info that is sent in is part of the digest secret, in this order:
        // [
        //      proto_vsn, client_vsn, eid, rno, careof, street, zip, city,
        //      country, fname, lname, careof, street, zip, city, country,
        //      fname, lname, artno, qty, orderid1, orderid2
        // ].
        // The address part appears twice, that is one per address that
        // changes. If no value is sent in for an optional field, there
        // is no entry for this field in the digest secret. Shared secret
        // is added at the end of the digest secret.
        $digestArray = array(
            str_replace('.', ':', $this->PROTO),
            $this->VERSION,
            $this->_eid,
            $rno
        );
        $digestArray = array_merge(
            $digestArray, $this->_addressDigestPart($this->shipping)
        );
        $digestArray = array_merge(
            $digestArray, $this->_addressDigestPart($this->billing)
        );
        if (is_array($this->goodsList) && $this->goodsList !== array()) {
            foreach ($this->goodsList as $goods) {
                if (strlen($goods["goods"]["artno"]) > 0) {
                    $digestArray[] = $goods["goods"]["artno"];
                } else {
                    $digestArray[] = $goods["goods"]["title"];
                }
                $digestArray[] = $goods["qty"];
            }
        }
        foreach ($this->orderid as $orderid) {
            $digestArray[] = $orderid;
        }
        $digestArray[] = $this->_secret;

        $digestSecret = $this->digest(
            call_user_func_array(
                array('self', 'colon'), $digestArray
            )
        );

        $shipping = array();
        $billing = array();
        if ($this->shipping !== null && $this->shipping instanceof KlarnaAddr) {
            $shipping = $this->shipping->toArray();
        }
        if ($this->billing !== null && $this->billing instanceof KlarnaAddr) {
            $billing = $this->billing->toArray();
        }
        $paramList = array(
            $this->_eid,
            $digestSecret,
            $rno,
            array(
                'goods_list' => $this->goodsList,
                'dlv_addr' => $shipping,
                'bill_addr' => $billing,
                'orderid1' => $this->orderid[0],
                'orderid2' => $this->orderid[1]
            )
        );

        self::printDebug('update array', $paramList);

        $result = $this->xmlrpc_call('update', $paramList);

        self::printDebug('update result', $result);

        return ($result === 'ok');
    }

    /**
     * Help function to sort the address for update digest.
     *
     * @param KlarnaAddr|null $address KlarnaAddr object or null
     *
     * @return array
     */
    private function _addressDigestPart(KlarnaAddr $address = null)
    {
        if ($address === null) {
            return array();
        }

        $keyOrder = array(
            'careof', 'street', 'zip', 'city', 'country', 'fname', 'lname'
        );

        $holder = $address->toArray();
        $digest = array();

        foreach ($keyOrder as $key) {
            if ($holder[$key] != "") {
                $digest[] = $holder[$key];
            }
        }

        return $digest;
    }

    /**
     * Activate the reservation matching the given reservation number.
     * Optional information should be set in ActivateInfo.
     *
     * To perform a partial activation, use the addArtNo function to specify
     * which items in the reservation to include in the activation.
     *
     * @param string  $rno   Reservation number
     * @param string  $ocr   optional OCR number to attach to the reservation when
     *                       activating. Overrides OCR specified in activateInfo.
     * @param string  $flags optional flags to affect behavior. If specified it
     *                       will overwrite any flag set in activateInfo.
     * @param boolean $clear clear set data after activating. Defaulted to true.
     *
     * @throws KlarnaException when the RNO is not specified, or if an error
     *         is recieved from Klarna Online.
     * @return A string array with risk status and reservation number.
     */
    public function activate(
        $rno, $ocr = null, $flags = null, $clear = true
    ) {
        $this->_checkRNO($rno);

        // Overwrite any OCR set on activateInfo if supplied here since this
        // method call is more specific.
        if ($ocr !== null) {
            $this->setActivateInfo('ocr', $ocr);
        }

        // If flags is specified set the flag supplied here to activateInfo.
        if ($flags !== null) {
            $this->setActivateInfo('flags', $flags);
        }

        //Assume normal shipment unless otherwise specified.
        if (!array_key_exists('delay_adjust', $this->shipInfo)) {
            $this->setShipmentInfo('delay_adjust', KlarnaFlags::NORMAL_SHIPMENT);
        }

        // Append shipment info to activateInfo
        $this->activateInfo['shipment_info'] = $this->shipInfo;

        // Unlike other calls, if NO_FLAG is specified it should not be sent in
        // at all.
        if (array_key_exists('flags', $this->activateInfo)
            && $this->activateInfo['flags'] === KlarnaFlags::NO_FLAG
        ) {
            unset($this->activateInfo['flags']);
        }

        // Build digest. Any field in activateInfo that is set is included in
        // the digest.
        $digestArray = array(
            str_replace('.', ':', $this->PROTO),
            $this->VERSION,
            $this->_eid,
            $rno
        );

        $optionalDigestKeys = array(
            'bclass',
            'cust_no',
            'flags',
            'ocr',
            'orderid1',
            'orderid2',
            'reference',
            'reference_code'
        );

        foreach ($optionalDigestKeys as $key) {
            if (array_key_exists($key, $this->activateInfo)) {
                $digestArray[] = $this->activateInfo[$key];
            }
        }

        if (array_key_exists('delay_adjust', $this->activateInfo['shipment_info'])) {
            $digestArray[] = $this->activateInfo['shipment_info']['delay_adjust'];
        }

        // If there are any artnos added with addArtNo, add them to the digest
        // and to the activateInfo
        if (is_array($this->artNos)) {
            foreach ($this->artNos as $artNo) {
                $digestArray[] = $artNo['artno'];
                $digestArray[] = $artNo['qty'];
            }
            $this->setActivateInfo('artnos', $this->artNos);
        }

        $digestArray[] = $this->_secret;
        $digestSecret = self::digest(
            call_user_func_array(
                array('self', 'colon'), $digestArray
            )
        );

        // Create the parameter list.
        $paramList = array(
            $this->_eid,
            $digestSecret,
            $rno,
            $this->activateInfo
        );

        self::printDebug('activate array', $paramList);

        $result = $this->xmlrpc_call('activate', $paramList);

        self::printDebug('activate result', $result);

        // Clear the state if specified.
        if ($clear) {
            $this->clear();
        }

        return $result;
    }

    /**
     * Activates a previously created reservation.
     *
     * <b>This method returns an array with</b>:<br>
     * Risk status ("no_risk", "ok")<br>
     * Invoice number<br>
     *
     * Gender is only required for Germany and Netherlands.<br>
     *
     * Use of the OCR parameter is optional.
     * An OCR number can be retrieved by using: {@link Klarna::reserveOCR()}.
     *
     * <b>Flags can be set to</b>:<br>
     * {@link KlarnaFlags::NO_FLAG}<br>
     * {@link KlarnaFlags::TEST_MODE}<br>
     * {@link KlarnaFlags::RSRV_SEND_BY_MAIL}<br>
     * {@link KlarnaFlags::RSRV_SEND_BY_EMAIL}<br>
     * {@link KlarnaFlags::RSRV_PRESERVE_RESERVATION}<br>
     * {@link KlarnaFlags::RSRV_SENSITIVE_ORDER}<br>
     *
     * Some flags can be added to each other for multiple options.
     *
     * <b>Note</b>:<br>
     * Normal shipment type is assumed unless otherwise specified, you can
     * do this by calling:
     * {@link Klarna::setShipmentInfo() setShipmentInfo('delay_adjust', ...)}
     * with either: {@link KlarnaFlags::NORMAL_SHIPMENT NORMAL_SHIPMENT} or
     * {@link KlarnaFlags::EXPRESS_SHIPMENT EXPRESS_SHIPMENT}<br>
     *
     * @param string $pno      Personal number, SSN, date of birth, etc.
     * @param string $rno      Reservation number.
     * @param int    $gender   {@link KlarnaFlags::FEMALE} or
     *                         {@link KlarnaFlags::MALE}, null for unspecified.
     * @param string $ocr      A OCR number.
     * @param int    $flags    Options which affect the behaviour.
     * @param int    $pclass   {@link KlarnaPClass::getId() PClass ID}.
     * @param int    $encoding {@link KlarnaEncoding PNO Encoding} constant.
     * @param bool   $clear    Whether customer info should be cleared after
     *                         this call.
     *
     * @see Klarna::reserveAmount()
     *
     * @throws KlarnaException
     * @return array An array with risk status and invoice number [string, string].
     */
    public function activateReservation(
        $pno, $rno, $gender, $ocr = "", $flags = KlarnaFlags::NO_FLAG,
        $pclass = KlarnaPClass::INVOICE, $encoding = null, $clear = true
    ) {
        $this->_checkLocale();

        //Get the PNO/SSN encoding constant.
        if ($encoding === null) {
            $encoding = $this->getPNOEncoding();
        }

        // Only check PNO if it is not explicitly null.
        if ($pno !== null) {
            $this->_checkPNO($pno, $encoding);
        }

        $this->_checkRNO($rno);

        if ($gender !== null && strlen($gender) > 0) {
            $this->_checkInt($gender, 'gender');
        }

        $this->_checkOCR($ocr);
        $this->_checkRef($this->reference, $this->reference_code);

        $this->_checkGoodslist();

        //No addresses used for phone transactions
        $billing = $shipping = '';
        if ( !($flags & KlarnaFlags::RSRV_PHONE_TRANSACTION) ) {
            $billing = $this->assembleAddr($this->billing);
            $shipping = $this->assembleAddr($this->shipping);

            if (strlen($shipping['country']) > 0
                && ($shipping['country'] !== $this->_country)
            ) {
                throw new Klarna_ShippingCountryException;
            }
        }

        //activate digest
        $string = $this->_eid . ":" . $pno . ":";
        foreach ($this->goodsList as $goods) {
            $string .= $goods["goods"]["artno"] . ":" . $goods["qty"] . ":";
        }
        $digestSecret = self::digest($string . $this->_secret);
        //end digest

        //Assume normal shipment unless otherwise specified.
        if (!isset($this->shipInfo['delay_adjust'])) {
            $this->setShipmentInfo('delay_adjust', KlarnaFlags::NORMAL_SHIPMENT);
        }

        $paramList = array(
            $rno,
            $ocr,
            $pno,
            $gender,
            $this->reference,
            $this->reference_code,
            $this->orderid[0],
            $this->orderid[1],
            $shipping,
            $billing,
            "0.0.0.0",
            $flags,
            $this->_currency,
            $this->_country,
            $this->_language,
            $this->_eid,
            $digestSecret,
            $encoding,
            $pclass,
            $this->goodsList,
            $this->comment,
            $this->shipInfo,
            $this->travelInfo,
            $this->incomeInfo,
            $this->bankInfo,
            $this->extraInfo
        );

        self::printDebug('activate_reservation', $paramList);

        $result = $this->xmlrpc_call('activate_reservation', $paramList);

        if ($clear === true) {
            $this->clear();
        }

        self::printDebug('activate_reservation result', $result);

        return $result;
    }


    /**
     * Splits a reservation due to for example outstanding articles.
     *
     * <b>For flags usage see</b>:<br>
     * {@link Klarna::reserveAmount()}<br>
     *
     * @param string $rno    Reservation number.
     * @param int    $amount The amount to be subtracted from the reservation.
     * @param int    $flags  Options which affect the behaviour.
     *
     *
     * @throws KlarnaException
     * @return string A new reservation number.
     */
    public function splitReservation(
        $rno, $amount, $flags = KlarnaFlags::NO_FLAG
    ) {
        //Check so required information is set.
        $this->_checkRNO($rno);
        $this->_checkAmount($amount);

        if ($amount <= 0) {
            throw new Klarna_InvalidPriceException($amount);
        }

        $digestSecret = self::digest(
            $this->colon($this->_eid, $rno, $amount, $this->_secret)
        );
        $paramList = array(
            $rno,
            $amount,
            $this->orderid[0],
            $this->orderid[1],
            $flags,
            $this->_eid,
            $digestSecret
        );

        self::printDebug('split_reservation array', $paramList);

        $result = $this->xmlrpc_call('split_reservation', $paramList);

        self::printDebug('split_reservation result', $result);

        return $result;
    }

    /**
     * Reserves a specified number of OCR numbers.<br>
     * For the specified country or the {@link Klarna::setCountry() set country}.<br>
     *
     * @param int $no      The number of OCR numbers to reserve.
     * @param int $country {@link KlarnaCountry} constant.
     *
     *
     * @throws KlarnaException
     * @return array An array of OCR numbers.
     */
    public function reserveOCR($no, $country = null)
    {
        $this->_checkNo($no);
        if ($country === null) {
            if (!$this->_country) {
                throw new Klarna_MissingCountryException;
            }
            $country = $this->_country;
        } else {
            $this->_checkCountry($country);
        }

        $digestSecret = self::digest(
            $this->colon($this->_eid, $no, $this->_secret)
        );
        $paramList = array(
            $no,
            $this->_eid,
            $digestSecret,
            $country
        );

        self::printDebug('reserve_ocr_nums array', $paramList);

        return $this->xmlrpc_call('reserve_ocr_nums', $paramList);
    }

    /**
     * Checks if the specified SSN/PNO has an part payment account with Klarna.
     *
     * @param string $pno      Social security number, Personal number, ...
     * @param int    $encoding {@link KlarnaEncoding PNO Encoding} constant.
     *
     *
     * @throws KlarnaException
     * @return bool    True, if customer has an account.
     */
    public function hasAccount($pno, $encoding = null)
    {
        //Get the PNO/SSN encoding constant.
        if ($encoding === null) {
            $encoding = $this->getPNOEncoding();
        }

        $this->_checkPNO($pno, $encoding);

        $digest = self::digest(
            $this->colon($this->_eid, $pno, $this->_secret)
        );

        $paramList = array(
            $this->_eid,
            $pno,
            $digest,
            $encoding
        );

        self::printDebug('has_account', $paramList);

        $result = $this->xmlrpc_call('has_account', $paramList);

        return ($result === 'true');
    }

    /**
     * Adds an article number and quantity to be used in
     * {@link Klarna::activatePart()}, {@link Klarna::creditPart()}
     * and {@link Klarna::invoicePartAmount()}.
     *
     * @param int    $qty   Quantity of specified article.
     * @param string $artNo Article number.
     *
     * @throws KlarnaException
     * @return void
     */
    public function addArtNo($qty, $artNo)
    {
        $this->_checkQty($qty);
        $this->_checkArtNo($artNo);

        if (!is_array($this->artNos)) {
            $this->artNos = array();
        }

        $this->artNos[] = array('artno' => $artNo, 'qty' => $qty);
    }

    /**
     * Partially activates a passive invoice.
     *
     * Returned array contains index "url" and "invno".<br>
     * The value of "url" is a URL pointing to a temporary PDF-version of the
     * activated invoice.<br>
     * The value of "invno" is either 0 if the entire invoice was activated or
     * the number on the new passive invoice.<br>
     *
     * <b>Note</b>:<br>
     * You need to call {@link Klarna::addArtNo()} first, to specify which
     * articles and how many you want to partially activate.<br>
     * If you want to change the shipment type, you can specify it using:
     * {@link Klarna::setShipmentInfo() setShipmentInfo('delay_adjust', ...)}
     * with either: {@link KlarnaFlags::NORMAL_SHIPMENT NORMAL_SHIPMENT}
     * or {@link KlarnaFlags::EXPRESS_SHIPMENT EXPRESS_SHIPMENT}
     *
     * @param string $invNo  Invoice numbers.
     * @param int    $pclass PClass id used for this invoice.
     * @param bool   $clear  Whether customer info should be cleared after
     *                       this call.
     *
     * @see Klarna::addArtNo()
     * @see Klarna::activateInvoice()
     *
     * @throws KlarnaException
     * @return array An array with invoice URL and invoice number.
     *         ['url' => val, 'invno' => val]
     */
    public function activatePart(
        $invNo, $pclass = KlarnaPClass::INVOICE, $clear = true
    ) {
        $this->_checkInvNo($invNo);
        $this->_checkArtNos($this->artNos);

        self::printDebug('activate_part artNos array', $this->artNos);

        //function activate_part_digest
        $string = $this->_eid . ":" . $invNo . ":";
        foreach ($this->artNos as $artNo) {
            $string .= $artNo["artno"] . ":". $artNo["qty"] . ":";
        }
        $digestSecret = self::digest($string . $this->_secret);
        //end activate_part_digest

        $paramList = array(
            $this->_eid,
            $invNo,
            $this->artNos,
            $digestSecret,
            $pclass,
            $this->shipInfo
        );

        self::printDebug('activate_part array', $paramList);

        $result = $this->xmlrpc_call('activate_part', $paramList);

        if ($clear === true) {
            $this->clear();
        }

        self::printDebug('activate_part result', $result);

        return $result;
    }

    /**
     * Retrieves the total amount for an active invoice.
     *
     * @param string $invNo Invoice number.
     *
     *
     * @throws KlarnaException
     * @return float The total amount.
     */
    public function invoiceAmount($invNo)
    {
        $this->_checkInvNo($invNo);

        $digestSecret = self::digest(
            $this->colon($this->_eid, $invNo, $this->_secret)
        );

        $paramList = array(
            $this->_eid,
            $invNo,
            $digestSecret
        );

        self::printDebug('invoice_amount array', $paramList);

        $result = $this->xmlrpc_call('invoice_amount', $paramList);

        //Result is in cents, fix it.
        return ($result / 100);
    }

    /**
     * Changes the order number of a purchase that was set when the order was
     * made online.
     *
     * @param string $invNo   Invoice number.
     * @param string $orderid Estores order number.
     *
     *
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function updateOrderNo($invNo, $orderid)
    {
        $this->_checkInvNo($invNo);
        $this->_checkEstoreOrderNo($orderid);

        $digestSecret = self::digest(
            $this->colon($invNo, $orderid, $this->_secret)
        );

        $paramList = array(
            $this->_eid,
            $digestSecret,
            $invNo,
            $orderid
        );

        self::printDebug('update_orderno array', $paramList);

        $result = $this->xmlrpc_call('update_orderno', $paramList);

        return $result;
    }

    /**
     * Sends an activated invoice to the customer via e-mail. <br>
     * The email is sent in plain text format and contains a link to a
     * PDF-invoice.<br>
     *
     * <b>Please note!</b><br>
     * Regular postal service is used if the customer has not entered his/her
     * e-mail address when making the purchase (charges may apply).<br>
     *
     * @param string $invNo Invoice number.
     *
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function emailInvoice($invNo)
    {
        $this->_checkInvNo($invNo);

        $digestSecret = self::digest(
            $this->colon($this->_eid, $invNo, $this->_secret)
        );
        $paramList = array(
            $this->_eid,
            $invNo,
            $digestSecret
        );

        self::printDebug('email_invoice array', $paramList);

        return $this->xmlrpc_call('email_invoice', $paramList);
    }

    /**
     * Requests a postal send-out of an activated invoice to a customer by
     * Klarna (charges may apply).
     *
     * @param string $invNo Invoice number.
     *
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function sendInvoice($invNo)
    {
        $this->_checkInvNo($invNo);

        $digestSecret = self::digest(
            $this->colon($this->_eid, $invNo, $this->_secret)
        );
        $paramList = array(
            $this->_eid,
            $invNo,
            $digestSecret
        );

        self::printDebug('send_invoice array', $paramList);

        return $this->xmlrpc_call('send_invoice', $paramList);
    }

    /**
     * Gives discounts on invoices.<br>
     * If you are using standard integration and the purchase is not yet
     * activated (you have not yet delivered the goods), <br>
     * just change the article list in our online interface Klarna Online.<br>
     *
     * <b>Flags can be</b>:<br>
     * {@link KlarnaFlags::INC_VAT}<br>
     * {@link KlarnaFlags::NO_FLAG}, <b>NOT RECOMMENDED!</b><br>
     *
     * @param string $invNo       Invoice number.
     * @param int    $amount      The amount given as a discount.
     * @param float  $vat         VAT in percent, e.g. 22.2 for 22.2%.
     * @param int    $flags       If amount is
     *                            {@link KlarnaFlags::INC_VAT including} or
     *                            {@link KlarnaFlags::NO_FLAG excluding} VAT.
     * @param string $description Optional custom text to present as discount
     *                            in the invoice.
     *
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function returnAmount(
        $invNo, $amount, $vat, $flags = KlarnaFlags::INC_VAT, $description = ""
    ) {
        $this->_checkInvNo($invNo);
        $this->_checkAmount($amount);
        $this->_checkVAT($vat);
        $this->_checkInt($flags, 'flags');

        if ($description == null) {
            $description = "";
        }

        $digestSecret = self::digest(
            $this->colon($this->_eid, $invNo, $this->_secret)
        );
        $paramList = array(
            $this->_eid,
            $invNo,
            $amount,
            $vat,
            $digestSecret,
            $flags,
            $description
        );

        self::printDebug('return_amount', $paramList);

        return $this->xmlrpc_call('return_amount', $paramList);
    }

    /**
     * Performs a complete refund on an invoice, part payment and mobile
     * purchase.
     *
     * @param string $invNo  Invoice number.
     * @param string $credNo Credit number.
     *
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function creditInvoice($invNo, $credNo = "")
    {
        $this->_checkInvNo($invNo);
        $this->_checkCredNo($credNo);

        $digestSecret = self::digest(
            $this->colon($this->_eid, $invNo, $this->_secret)
        );
        $paramList = array(
            $this->_eid,
            $invNo,
            $credNo,
            $digestSecret
        );

        self::printDebug('credit_invoice', $paramList);

        return $this->xmlrpc_call('credit_invoice', $paramList);
    }

    /**
     * Performs a partial refund on an invoice, part payment or mobile purchase.
     *
     * <b>Note</b>:<br>
     * You need to call {@link Klarna::addArtNo()} first.<br>
     *
     * @param string $invNo  Invoice number.
     * @param string $credNo Credit number.
     *
     * @see  Klarna::addArtNo()
     *
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function creditPart($invNo, $credNo = "")
    {
        $this->_checkInvNo($invNo);
        $this->_checkCredNo($credNo);

        if ($this->goodsList === null || empty($this->goodsList)) {
            $this->_checkArtNos($this->artNos);
        }

        //function activate_part_digest
        $string = $this->_eid . ":" . $invNo . ":";

        if ($this->artNos !== null && !empty($this->artNos)) {
            foreach ($this->artNos as $artNo) {
                $string .= $artNo["artno"] . ":". $artNo["qty"] . ":";
            }
        }

        $digestSecret = self::digest($string . $this->_secret);
        //end activate_part_digest

        $paramList = array(
            $this->_eid,
            $invNo,
            $this->artNos,
            $credNo,
            $digestSecret
        );

        if ($this->goodsList !== null && !empty($this->goodsList)) {
            $paramList[] = 0;
            $paramList[] = $this->goodsList;
        }

        $this->artNos = array();

        self::printDebug('credit_part', $paramList);

        return $this->xmlrpc_call('credit_part', $paramList);
    }

    /**
     * Changes the quantity of a specific item in a passive invoice.
     *
     * @param string $invNo Invoice number.
     * @param string $artNo Article number.
     * @param int    $qty   Quantity of specified article.
     *
     *
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function updateGoodsQty($invNo, $artNo, $qty)
    {
        $this->_checkInvNo($invNo);
        $this->_checkQty($qty);
        $this->_checkArtNo($artNo);

        $digestSecret = self::digest(
            $this->colon($invNo, $artNo, $qty, $this->_secret)
        );

        $paramList = array(
            $this->_eid,
            $digestSecret,
            $invNo,
            $artNo,
            $qty
        );

        self::printDebug('update_goods_qty', $paramList);

        return $this->xmlrpc_call('update_goods_qty', $paramList);
    }

    /**
     * Changes the amount of a fee (e.g. the invoice fee) in a passive invoice.
     *
     * <b>Type can be</b>:<br>
     * {@link KlarnaFlags::IS_SHIPMENT}<br>
     * {@link KlarnaFlags::IS_HANDLING}<br>
     *
     * @param string $invNo     Invoice number.
     * @param int    $type      Charge type.
     * @param int    $newAmount The new amount for the charge.
     *
     *
     * @throws KlarnaException
     * @return string  Invoice number.
     */
    public function updateChargeAmount($invNo, $type, $newAmount)
    {
        $this->_checkInvNo($invNo);
        $this->_checkInt($type, 'type');
        $this->_checkAmount($newAmount);

        if ($type === KlarnaFlags::IS_SHIPMENT) {
            $type = 1;
        } else if ($type === KlarnaFlags::IS_HANDLING) {
            $type = 2;
        }

        $digestSecret = self::digest(
            $this->colon($invNo, $type, $newAmount, $this->_secret)
        );

        $paramList = array(
            $this->_eid,
            $digestSecret,
            $invNo,
            $type,
            $newAmount
        );

        self::printDebug('update_charge_amount', $paramList);

        return $this->xmlrpc_call('update_charge_amount', $paramList);
    }

    /**
     * The invoice_address function is used to retrieve the address of a
     * purchase.
     *
     * @param string $invNo Invoice number.
     *
     * @throws KlarnaException
     * @return KlarnaAddr
     */
    public function invoiceAddress($invNo)
    {
        $this->_checkInvNo($invNo);

        $digestSecret = self::digest(
            $this->colon($this->_eid, $invNo, $this->_secret)
        );
        $paramList = array(
            $this->_eid,
            $invNo,
            $digestSecret
        );

        self::printDebug('invoice_address', $paramList);

        $result = $this->xmlrpc_call('invoice_address', $paramList);

        $addr = new KlarnaAddr();
        if (strlen($result[0]) > 0) {
            $addr->isCompany = false;
            $addr->setFirstName($result[0]);
            $addr->setLastName($result[1]);
        } else {
            $addr->isCompany = true;
            $addr->setCompanyName($result[1]);
        }
        $addr->setStreet($result[2]);
        $addr->setZipCode($result[3]);
        $addr->setCity($result[4]);
        $addr->setCountry($result[5]);

        return $addr;
    }

    /**
     * Retrieves the amount of a specific goods from a purchase.
     *
     * <b>Note</b>:<br>
     * You need to call {@link Klarna::addArtNo()} first.<br>
     *
     * @param string $invNo Invoice number.
     *
     * @see  Klarna::addArtNo()
     *
     * @throws KlarnaException
     * @return float The amount of the goods.
     */
    public function invoicePartAmount($invNo)
    {
        $this->_checkInvNo($invNo);
        $this->_checkArtNos($this->artNos);

        //function activate_part_digest
        $string = $this->_eid . ":" . $invNo . ":";
        foreach ($this->artNos as $artNo) {
            $string .= $artNo["artno"] . ":". $artNo["qty"] . ":";
        }
        $digestSecret = self::digest($string . $this->_secret);
        //end activate_part_digest

        $paramList = array(
            $this->_eid,
            $invNo,
            $this->artNos,
            $digestSecret
        );
        $this->artNos = array();

        self::printDebug('invoice_part_amount', $paramList);

        $result = $this->xmlrpc_call('invoice_part_amount', $paramList);

        return ($result / 100);
    }

    /**
     * Returns the current order status for a specific reservation or invoice.
     * Use this when {@link Klarna::addTransaction()} or
     * {@link Klarna::reserveAmount()} returns a {@link KlarnaFlags::PENDING}
     * status.
     *
     * <b>Order status can be</b>:<br>
     * {@link KlarnaFlags::ACCEPTED}<br>
     * {@link KlarnaFlags::PENDING}<br>
     * {@link KlarnaFlags::DENIED}<br>
     *
     * @param string $id   Reservation number or invoice number.
     * @param int    $type 0 if $id is an invoice or reservation, 1 for order id
     *
     *
     * @throws KlarnaException
     * @return string  The order status.
     */
    public function checkOrderStatus($id, $type = 0)
    {
        $this->_checkArgument($id, "id");

        $this->_checkInt($type, 'type');
        if ($type !== 0 && $type !== 1) {
            throw new Klarna_InvalidTypeException(
                'type', "0 or 1"
            );
        }

        $digestSecret = self::digest(
            $this->colon($this->_eid, $id, $this->_secret)
        );
        $paramList = array(
            $this->_eid,
            $digestSecret,
            $id,
            $type
        );

        self::printDebug('check_order_status', $paramList);

        return $this->xmlrpc_call('check_order_status', $paramList);
    }

    /**
     * Retrieves a list of all the customer numbers associated with the
     * specified pno.
     *
     * @param string $pno      Social security number, Personal number, ...
     * @param int    $encoding {@link KlarnaEncoding PNO Encoding} constant.
     *
     * @throws KlarnaException
     * @return array An array containing all customer numbers associated
     *               with that pno.
     */
    public function getCustomerNo($pno, $encoding = null)
    {
        //Get the PNO/SSN encoding constant.
        if ($encoding === null) {
            $encoding = $this->getPNOEncoding();
        }
        $this->_checkPNO($pno, $encoding);

        $digestSecret = self::digest(
            $this->colon($this->_eid, $pno, $this->_secret)
        );
        $paramList = array(
            $pno,
            $this->_eid,
            $digestSecret,
            $encoding
        );

        self::printDebug('get_customer_no', $paramList);

        return $this->xmlrpc_call('get_customer_no', $paramList);
    }

    /**
     * Associates a pno with a customer number when you want to make future
     * purchases without a pno.
     *
     * @param string $pno      Social security number, Personal number, ...
     * @param string $custNo   The customer number.
     * @param int    $encoding {@link KlarnaEncoding PNO Encoding} constant.
     *
     * @throws KlarnaException
     * @return bool  True, if the customer number was associated with the pno.
     */
    public function setCustomerNo($pno, $custNo, $encoding = null)
    {
        //Get the PNO/SSN encoding constant.
        if ($encoding === null) {
            $encoding = $this->getPNOEncoding();
        }
        $this->_checkPNO($pno, $encoding);

        $this->_checkArgument($custNo, 'custNo');

        $digestSecret = self::digest(
            $this->colon($this->_eid, $pno, $custNo, $this->_secret)
        );
        $paramList = array(
            $pno,
            $custNo,
            $this->_eid,
            $digestSecret,
            $encoding
        );

        self::printDebug('set_customer_no', $paramList);

        $result = $this->xmlrpc_call('set_customer_no', $paramList);

        return ($result == 'ok');
    }

    /**
     * Removes a customer number from association with a pno.
     *
     * @param string $custNo The customer number.
     *
     * @throws KlarnaException
     * @return bool    True, if the customer number association was removed.
     */
    public function removeCustomerNo($custNo)
    {
        $this->_checkArgument($custNo, 'custNo');

        $digestSecret = self::digest(
            $this->colon($this->_eid, $custNo, $this->_secret)
        );

        $paramList = array(
            $custNo,
            $this->_eid,
            $digestSecret
        );

        self::printDebug('remove_customer_no', $paramList);

        $result = $this->xmlrpc_call('remove_customer_no', $paramList);

        return ($result == 'ok');
    }

    /**
     * Returns the configured PCStorage object.
     *
     * @throws Exception|KlarnaException
     * @return PCStorage
     */
    public function getPCStorage()
    {
        if (isset($this->pclasses)) {
            return $this->pclasses;
        }

        include_once 'pclasses/storage.intf.php';
        $className = $this->pcStorage.'storage';
        $pclassStorage = dirname(__FILE__) . "/pclasses/{$className}.class.php";

        include_once $pclassStorage;
        $storage = new $className;

        if (!($storage instanceof PCStorage)) {
            throw new Klarna_PCStorageInvalidException(
                $className, $pclassStorage
            );
        }
        return $storage;
    }

    /**
     * Fetch pclasses
     *
     * @param PCStorage $storage  PClass Storage
     * @param int       $country  KlarnaCountry constant
     * @param int       $language KlarnaLanguage constant
     * @param int       $currency KlarnaCurrency constant
     *
     * @return void
     */
    private function _fetchPClasses($storage, $country, $language, $currency)
    {
        $digestSecret = self::digest(
            $this->_eid . ":" . $currency . ":" . $this->_secret
        );
        $paramList = array(
            $this->_eid,
            $currency,
            $digestSecret,
            $country,
            $language
        );

        self::printDebug('get_pclasses array', $paramList);

        $result = $this->xmlrpc_call('get_pclasses', $paramList);

        self::printDebug('get_pclasses result', $result);

        foreach ($result as &$pclass) {
            //numeric htmlentities
            $pclass[1] = Klarna::num_htmlentities($pclass[1]);

            //Below values are in "cents", fix them.
            $pclass[3] /= 100; //divide start fee with 100
            $pclass[4] /= 100; //divide invoice fee with 100
            $pclass[5] /= 100; //divide interest rate with 100
            $pclass[6] /= 100; //divide min amount with 100

            if ($pclass[9] != '-') {
                //unix timestamp instead of yyyy-mm-dd
                $pclass[9] = strtotime($pclass[9]);
            }

            //Associate the PClass with this estore.
            array_unshift($pclass, $this->_eid);

            $storage->addPClass(new KlarnaPClass($pclass));
        }
    }

    /**
     * Fetches the PClasses from Klarna Online.<br>
     * Removes the cached/stored pclasses and updates.<br>
     * You are only allowed to call this once, or once per update of PClasses
     * in KO.<br>
     *
     * <b>Note</b>:<br>
     * If language and/or currency is null, then they will be set to mirror
     * the specified country.<br/>
     * Short codes like DE, SV or EUR can also be used instead of the constants.
     *
     * @param string|int $country  {@link KlarnaCountry Country} constant,
     *                             or two letter code.
     * @param mixed      $language {@link KlarnaLanguage Language} constant,
     *                             or two letter code.
     * @param mixed      $currency {@link KlarnaCurrency Currency} constant,
     *                             or three letter code.
     *
     * @throws KlarnaException
     * @return void
     */
    public function fetchPClasses(
        $country = null, $language = null, $currency = null
    ) {
        extract(
            $this->getLocale($country, $language, $currency),
            EXTR_OVERWRITE
        );

        $this->_checkConfig();

        $pclasses = $this->getPCStorage();
        try {
            //Attempt to load previously stored pclasses, so they aren't
            // accidentially removed.
            $pclasses->load($this->pcURI);
        }
        catch(Exception $e) {
            self::printDebug('load pclasses', $e->getMessage());
        }

        $this->_fetchPClasses($pclasses, $country, $language, $currency);

        $pclasses->save($this->pcURI);
        $this->pclasses = $pclasses;
    }

    /**
     * Removes the stored PClasses, if you need to update them.
     *
     * @throws KlarnaException
     * @return void
     */
    public function clearPClasses()
    {
        $this->_checkConfig();

        $pclasses = $this->getPCStorage();
        $pclasses->clear($this->pcURI);
    }

    /**
     * Retrieves the specified PClasses.
     *
     * <b>Type can be</b>:<br>
     * {@link KlarnaPClass::CAMPAIGN}<br>
     * {@link KlarnaPClass::ACCOUNT}<br>
     * {@link KlarnaPClass::SPECIAL}<br>
     * {@link KlarnaPClass::FIXED}<br>
     * {@link KlarnaPClass::DELAY}<br>
     * {@link KlarnaPClass::MOBILE}<br>
     *
     * @param int $type PClass type identifier.
     *
     * @throws KlarnaException
     * @return array An array of PClasses. [KlarnaPClass]
     */
    public function getPClasses($type = null)
    {
        $this->_checkConfig();

        if (!$this->pclasses) {
            $this->pclasses = $this->getPCStorage();
            $this->pclasses->load($this->pcURI);
        }
        $tmp = $this->pclasses->getPClasses(
            $this->_eid, $this->_country, $type
        );
        $this->sortPClasses($tmp[$this->_eid]);
        return $tmp[$this->_eid];
    }

    /**
     * Retrieve a flattened array of all pclasses stored in the configured
     * pclass storage.
     *
     * @return array
     */
    public function getAllPClasses()
    {
        if (!$this->pclasses) {
            $this->pclasses = $this->getPCStorage();
            $this->pclasses->load($this->pcURI);
        }
        return $this->pclasses->getAllPClasses();
    }

    /**
     * Returns the specified PClass.
     *
     * @param int $id The PClass ID.
     *
     * @return KlarnaPClass
     */
    public function getPClass($id)
    {
        if (!is_numeric($id)) {
            throw new Klarna_InvalidTypeException('id', 'integer');
        }

        $this->_checkConfig();

        if (!$this->pclasses || !($this->pclasses instanceof PCStorage)) {
            $this->pclasses = $this->getPCStorage();
            $this->pclasses->load($this->pcURI);
        }
        return $this->pclasses->getPClass(
            intval($id), $this->_eid, $this->_country
        );
    }

    /**
     * Sorts the specified array of KlarnaPClasses.
     *
     * @param array &$array An array of {@link KlarnaPClass PClasses}.
     *
     * @return void
     */
    public function sortPClasses(&$array)
    {
        if (!is_array($array)) {
            //Input is not an array!
            $array = array();
            return;
        }
        //Sort pclasses array after natural sort (natcmp)
        if (!function_exists('pcCmp')) {
            /**
             * Comparison function
             *
             * @param KlarnaPClass $a object 1
             * @param KlarnaPClass $b object 2
             *
             * @return int
             */
            function pcCmp($a, $b)
            {
                if ($a->getDescription() == null
                    && $b->getDescription() == null
                ) {
                    return 0;
                } else if ($a->getDescription() == null) {
                    return 1;
                } else if ($b->getDescription() == null) {
                    return -1;
                } else if ($b->getType() === 2 && $a->getType() !== 2) {
                    return 1;
                } else if ($b->getType() !== 2 && $a->getType() === 2) {
                    return -1;
                }

                return strnatcmp($a->getDescription(), $b->getDescription())*-1;
            }
        }
        usort($array, "pcCmp");
    }

    /**
     * Returns the cheapest, per month, PClass related to the specified sum.
     *
     * <b>Note</b>: This choose the cheapest PClass for the current country.<br>
     * {@link Klarna::setCountry()}
     *
     * <b>Flags can be</b>:<br>
     * {@link KlarnaFlags::CHECKOUT_PAGE}<br>
     * {@link KlarnaFlags::PRODUCT_PAGE}<br>
     *
     * @param float $sum   The product cost, or total sum of the cart.
     * @param int   $flags Which type of page the info will be displayed on.
     *
     * @throws KlarnaException
     * @return KlarnaPClass or false if none was found.
     */
    public function getCheapestPClass($sum, $flags)
    {
        if (!is_numeric($sum)) {
            throw new Klarna_InvalidPriceException($sum);
        }

        if (!is_numeric($flags)
            || !in_array(
                $flags, array(
                    KlarnaFlags::CHECKOUT_PAGE, KlarnaFlags::PRODUCT_PAGE)
            )
        ) {
            throw new Klarna_InvalidTypeException(
                'flags',
                KlarnaFlags::CHECKOUT_PAGE . ' or ' . KlarnaFlags::PRODUCT_PAGE
            );
        }

        $lowest_pp = $lowest = false;

        foreach ($this->getPClasses() as $pclass) {
            $lowest_payment = KlarnaCalc::get_lowest_payment_for_account(
                $pclass->getCountry()
            );
            if ($pclass->getType() < 2 && $sum >= $pclass->getMinAmount()) {
                $minpay = KlarnaCalc::calc_monthly_cost(
                    $sum, $pclass, $flags
                );

                if ($minpay < $lowest_pp || $lowest_pp === false) {
                    if ($pclass->getType() == KlarnaPClass::ACCOUNT
                        || $minpay >= $lowest_payment
                    ) {
                        $lowest_pp = $minpay;
                        $lowest = $pclass;
                    }
                }
            }
        }

        return $lowest;
    }

    /**
     * Initializes the checkoutHTML objects.
     *
     * @see Klarna::checkoutHTML()
     * @return void
     */
    protected function initCheckout()
    {
        $dir = dirname(__FILE__);

        //Require the CheckoutHTML interface/abstract class
        include_once $dir . '/checkout/checkouthtml.intf.php';

        //Iterate over all .class.php files in checkout/
        foreach (glob($dir.'/checkout/*.class.php') as $checkout) {
            if (!self::$debug) {
                ob_start();
            }
            include_once $checkout;

            $className = basename($checkout, '.class.php');
            $cObj = new $className;

            if ($cObj instanceof CheckoutHTML) {
                $cObj->init($this, $this->_eid);
                $this->coObjects[$className] = $cObj;
            }

            if (!self::$debug) {
                ob_end_clean();
            }
        }
    }

    /**
     * Returns the checkout page HTML from the checkout classes.
     *
     * <b>Note</b>:<br>
     * This method uses output buffering to silence unwanted echoes.<br>
     *
     * @see CheckoutHTML
     *
     * @return string  A HTML string.
     */
    public function checkoutHTML()
    {
        if (empty($this->coObjects)) {
            $this->initCheckout();
        }
        $dir = dirname(__FILE__);

        //Require the CheckoutHTML interface/abstract class
        include_once $dir . '/checkout/checkouthtml.intf.php';

        //Iterate over all .class.php files in
        $html = "\n";
        foreach ($this->coObjects as $cObj) {
            if (!self::$debug) {
                ob_start();
            }
            if ($cObj instanceof CheckoutHTML) {
                $html .= $cObj->toHTML() . "\n";
            }
            if (!self::$debug) {
                ob_end_clean();
            }
        }

        return $html;
    }

    /**
     * Creates a XMLRPC call with specified XMLRPC method and parameters from array.
     *
     * @param string $method XMLRPC method.
     * @param array  $array  XMLRPC parameters.
     *
     * @throws KlarnaException
     * @return mixed
     */
    protected function xmlrpc_call($method, $array)
    {
        $this->_checkConfig();

        if (!isset($method) || !is_string($method)) {
            throw new Klarna_InvalidTypeException('method', 'string');
        }
        if ($array === null || count($array) === 0) {
            throw new KlarnaException("Parameterlist is empty or null!", 50067);
        }
        if (self::$disableXMLRPC) {
            return true;
        }
        try {
            /*
             * Disable verifypeer for CURL, so below error is avoided.
             * CURL error: SSL certificate problem, verify that the CA
             * cert is OK.
             * Details: error:14090086:SSL
             * routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed (#8)
             */
            $this->xmlrpc->verifypeer = false;

            $timestart = microtime(true);

            //Create the XMLRPC message.
            $msg = new xmlrpcmsg($method);
            $params = array_merge(
                array(
                    $this->PROTO, $this->VERSION
                ), $array
            );

            $msg = new xmlrpcmsg($method);
            foreach ($params as $p) {
                if (!$msg->addParam(
                    php_xmlrpc_encode($p, array('extension_api'))
                )
                ) {
                    throw new KlarnaException(
                        "Failed to add parameters to XMLRPC message.",
                        50068
                    );
                }
            }

            //Send the message.
            $selectDateTime = microtime(true);
            if (self::$xmlrpcDebug) {
                $this->xmlrpc->setDebug(2);
            }
            $xmlrpcresp = $this->xmlrpc->send($msg);

            //Calculate time and selectTime.
            $timeend = microtime(true);
            $time = (int) (($selectDateTime - $timestart) * 1000);
            $selectTime = (int) (($timeend - $timestart) * 1000);

            $status = $xmlrpcresp->faultCode();

            if ($status !== 0) {
                throw new KlarnaException($xmlrpcresp->faultString(), $status);
            }

            return php_xmlrpc_decode($xmlrpcresp->value());
        }
        catch(KlarnaException $e) {
            //Otherwise it is caught below, and rethrown.
            throw $e;
        }
        catch(Exception $e) {
            throw new KlarnaException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Removes all relevant order/customer data from the internal structure.
     *
     * @return void
     */
    public function clear()
    {
        $this->goodsList = null;
        $this->comment = "";

        $this->billing = null;
        $this->shipping = null;

        $this->shipInfo = array();
        $this->extraInfo = array();
        $this->bankInfo = array();
        $this->incomeInfo = array();
        $this->activateInfo = array();

        $this->reference = "";
        $this->reference_code = "";

        $this->orderid[0] = "";
        $this->orderid[1] = "";

        $this->artNos = array();
        $this->coObjects = array();
    }

    /**
     * Implodes parameters with delimiter ':'.
     * Null and "" values are ignored by the colon function to
     * ensure there is not several colons in succession.
     *
     * @return string Colon separated string.
     */
    public static function colon(/* variable parameters */)
    {
        $args = func_get_args();
        return implode(
            ':',
            array_filter(
                $args,
                array('self', 'filterDigest')
            )
        );
    }

    /**
     * Implodes parameters with delimiter '|'.
     *
     * @return string Pipe separated string.
     */
    public static function pipe(/* variable parameters */)
    {
        $args = func_get_args();
        return implode('|', $args);
    }

    /**
     * Check if the value has a string length larger than 0
     *
     * @param mixed $value The value to check.
     *
     * @return boolean True if string length is larger than 0
     */
    public static function filterDigest($value)
    {
        return strlen(strval($value)) > 0;
    }

    /**
     * Creates a digest hash from the inputted string,
     * and the specified or the preferred hash algorithm.
     *
     * @param string $data Data to be hashed.
     * @param string $hash hash algoritm to use
     *
     * @throws KlarnaException
     * @return string  Base64 encoded hash.
     */
    public static function digest($data, $hash = null)
    {
        if ($hash===null) {
            $preferred = array(
                'sha512',
                'sha384',
                'sha256',
                'sha224',
                'md5'
            );

            $hashes = array_intersect($preferred, hash_algos());

            if (count($hashes) == 0) {
                throw new KlarnaException(
                    "No available hash algorithm supported!"
                );
            }
            $hash = array_shift($hashes);
        }
        self::printDebug('digest() using hash', $hash);

        return base64_encode(pack("H*", hash($hash, $data)));
    }

    /**
     * Converts special characters to numeric htmlentities.
     *
     * <b>Note</b>:<br>
     * If supplied string is encoded with UTF-8, o umlaut ("ö") will become two
     * HTML entities instead of one.
     *
     * @param string $str String to be converted.
     *
     * @return string String converted to numeric HTML entities.
     */
    public static function num_htmlentities($str)
    {
        if (!self::$htmlentities) {
            self::$htmlentities = array();
            $table = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
            foreach ($table as $char => $entity) {
                self::$htmlentities[$entity] = '&#' . ord($char) . ';';
            }
        }

        return str_replace(
            array_keys(
                self::$htmlentities
            ), self::$htmlentities, htmlentities($str, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')
        );
    }

    /**
     * Prints debug information if debug is set to true.
     * $msg is used as header/footer in the output.
     *
     * if FirePHP is available it will be used instead of
     * dumping the debug info into the document.
     *
     * It uses print_r and encapsulates it in HTML/XML comments.
     * (<!-- -->)
     *
     * @param string $msg   Debug identifier, e.g. "my array".
     * @param mixed  $mixed Object, type, etc, to be debugged.
     *
     * @return void
     */
    public static function printDebug($msg, $mixed)
    {
        if (self::$debug) {
            if (class_exists('FB', false)) {
                FB::send($mixed, $msg);
            } else {
                echo "\n<!-- ".$msg.": \n";
                print_r($mixed);
                echo "\n end ".$msg." -->\n";
            }
        }
    }

    /**
     * Checks/fixes so the invNo input is valid.
     *
     * @param string &$invNo Invoice number.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkInvNo(&$invNo)
    {
        if (!isset($invNo)) {
            throw new Klarna_ArgumentNotSetException("Invoice number");
        }
        if (!is_string($invNo)) {
            $invNo = strval($invNo);
        }
        if (strlen($invNo) == 0) {
            throw new Klarna_ArgumentNotSetException("Invoice number");
        }
    }

    /**
     * Checks/fixes so the quantity input is valid.
     *
     * @param int &$qty Quantity.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkQty(&$qty)
    {
        if (!isset($qty)) {
            throw new Klarna_ArgumentNotSetException("Quantity");
        }
        if (is_numeric($qty) && !is_int($qty)) {
            $qty = intval($qty);
        }
        if (!is_int($qty)) {
            throw new Klarna_InvalidTypeException("Quantity", "integer");
        }
    }

    /**
     * Checks/fixes so the artTitle input is valid.
     *
     * @param string &$artTitle Article title.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkArtTitle(&$artTitle)
    {
        if (!is_string($artTitle)) {
            $artTitle = strval($artTitle);
        }
        if (!isset($artTitle) || strlen($artTitle) == 0) {
            throw new Klarna_ArgumentNotSetException("artTitle", 50059);
        }
    }

    /**
     * Checks/fixes so the artNo input is valid.
     *
     * @param int|string &$artNo Article number.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkArtNo(&$artNo)
    {
        if (is_numeric($artNo) && !is_string($artNo)) {
            //Convert artNo to string if integer.
            $artNo = strval($artNo);
        }
        if (!isset($artNo) || strlen($artNo) == 0 || (!is_string($artNo))) {
            throw new Klarna_ArgumentNotSetException("artNo");
        }
    }

    /**
     * Checks/fixes so the credNo input is valid.
     *
     * @param string &$credNo Credit number.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkCredNo(&$credNo)
    {
        if (!isset($credNo)) {
            throw new Klarna_ArgumentNotSetException("Credit number");
        }

        if ($credNo === false || $credNo === null) {
            $credNo = "";
        }
        if (!is_string($credNo)) {
            $credNo = strval($credNo);
            if (!is_string($credNo)) {
                throw new Klarna_InvalidTypeException("Credit number", "string");
            }
        }
    }

    /**
     * Checks so that artNos is an array and is not empty.
     *
     * @param array &$artNos Array from {@link Klarna::addArtNo()}.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkArtNos(&$artNos)
    {
        if (!is_array($artNos)) {
            throw new Klarna_InvalidTypeException("artNos", "array");
        }
        if (empty($artNos)) {
            throw new KlarnaException('ArtNo array is empty!', 50064);
        }
    }

    /**
     * Checks/fixes so the integer input is valid.
     *
     * @param int    &$int  {@link KlarnaFlags flags} constant.
     * @param string $field Name of the field.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkInt(&$int, $field)
    {
        if (!isset($int)) {
            throw new Klarna_ArgumentNotSetException($field);
        }
        if (is_numeric($int) && !is_int($int)) {
            $int = intval($int);
        }
        if (!is_numeric($int) || !is_int($int)) {
            throw new Klarna_InvalidTypeException($field, "integer");
        }
    }

    /**
     * Checks/fixes so the VAT input is valid.
     *
     * @param float &$vat VAT.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkVAT(&$vat)
    {
        if (!isset($vat)) {
            throw new Klarna_ArgumentNotSetException("VAT");
        }
        if (is_numeric($vat) && (!is_int($vat) || !is_float($vat))) {
            $vat = floatval($vat);
        }
        if (!is_numeric($vat) || (!is_int($vat) && !is_float($vat))) {
            throw new Klarna_InvalidTypeException("VAT", "integer or float");
        }
    }

    /**
     * Checks/fixes so the amount input is valid.
     *
     * @param int &$amount Amount.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkAmount(&$amount)
    {
        if (!isset($amount)) {
            throw new Klarna_ArgumentNotSetException("Amount");
        }
        if (is_numeric($amount)) {
            $this->_fixValue($amount);
        }
        if (is_numeric($amount) && !is_int($amount)) {
            $amount = intval($amount);
        }
        if (!is_numeric($amount) || !is_int($amount)) {
            throw new Klarna_InvalidTypeException("amount", "integer");
        }
    }

    /**
     * Checks/fixes so the price input is valid.
     *
     * @param int &$price Price.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkPrice(&$price)
    {
        if (!isset($price)) {
            throw new Klarna_ArgumentNotSetException("Price");
        }
        if (is_numeric($price)) {
            $this->_fixValue($price);
        }
        if (is_numeric($price) && !is_int($price)) {
            $price = intval($price);
        }
        if (!is_numeric($price) || !is_int($price)) {
            throw new Klarna_InvalidTypeException("Price", "integer");
        }
    }

    /**
     * Multiplies value with 100 and rounds it.
     * This fixes value/price/amount inputs so that KO can handle them.
     *
     * @param float &$value value
     *
     * @return void
     */
    private function _fixValue(&$value)
    {
        $value = round($value * 100);
    }

    /**
     * Checks/fixes so the discount input is valid.
     *
     * @param float &$discount Discount amount.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkDiscount(&$discount)
    {
        if (!isset($discount)) {
            throw new Klarna_ArgumentNotSetException("Discount");
        }
        if (is_numeric($discount)
            && (!is_int($discount) || !is_float($discount))
        ) {
            $discount = floatval($discount);
        }

        if (!is_numeric($discount)
            || (!is_int($discount) && !is_float($discount))
        ) {
            throw new Klarna_InvalidTypeException("Discount", "integer or float");
        }
    }

    /**
     * Checks/fixes so that the estoreOrderNo input is valid.
     *
     * @param string &$estoreOrderNo Estores order number.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkEstoreOrderNo(&$estoreOrderNo)
    {
        if (!isset($estoreOrderNo)) {
            throw new Klarna_ArgumentNotSetException("Order number");
        }

        if (!is_string($estoreOrderNo)) {
            $estoreOrderNo = strval($estoreOrderNo);
            if (!is_string($estoreOrderNo)) {
                throw new Klarna_InvalidTypeException("Order number", "string");
            }
        }
    }

    /**
     * Checks/fixes to the PNO/SSN input is valid.
     *
     * @param string &$pno Personal number, social security  number, ...
     * @param int    $enc  {@link KlarnaEncoding PNO Encoding} constant.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkPNO(&$pno, $enc)
    {
        if (!$pno) {
            throw new Klarna_ArgumentNotSetException("PNO/SSN");
        }

        if (!KlarnaEncoding::checkPNO($pno)) {
            throw new Klarna_InvalidPNOException;
        }
    }

    /**
     * Checks/fixes to the country input is valid.
     *
     * @param int &$country {@link KlarnaCountry Country} constant.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkCountry(&$country)
    {
        if (!isset($country)) {
            throw new Klarna_ArgumentNotSetException("Country");
        }
        if (is_numeric($country) && !is_int($country)) {
            $country = intval($country);
        }
        if (!is_numeric($country) || !is_int($country)) {
            throw new Klarna_InvalidTypeException("Country", "integer");
        }
    }

    /**
     * Checks/fixes to the language input is valid.
     *
     * @param int &$language {@link KlarnaLanguage Language} constant.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkLanguage(&$language)
    {
        if (!isset($language)) {
            throw new Klarna_ArgumentNotSetException("Language");
        }
        if (is_numeric($language) && !is_int($language)) {
            $language = intval($language);
        }
        if (!is_numeric($language) || !is_int($language)) {
            throw new Klarna_InvalidTypeException("Language", "integer");
        }
    }

    /**
     * Checks/fixes to the currency input is valid.
     *
     * @param int &$currency {@link KlarnaCurrency Currency} constant.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkCurrency(&$currency)
    {
        if (!isset($currency)) {
            throw new Klarna_ArgumentNotSetException("Currency");
        }
        if (is_numeric($currency) && !is_int($currency)) {
            $currency = intval($currency);
        }
        if (!is_numeric($currency) || !is_int($currency)) {
            throw new Klarna_InvalidTypeException("Currency", "integer");
        }
    }

    /**
     * Checks/fixes so no/number is a valid input.
     *
     * @param int &$no Number.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkNo(&$no)
    {
        if (!isset($no)) {
            throw new Klarna_ArgumentNotSetException("no");
        }
        if (is_numeric($no) && !is_int($no)) {
            $no = intval($no);
        }
        if (!is_numeric($no) || !is_int($no) || $no <= 0) {
            throw new Klarna_InvalidTypeException('no', 'integer > 0');
        }
    }

    /**
     * Checks/fixes so reservation number is a valid input.
     *
     * @param string &$rno Reservation number.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkRNO(&$rno)
    {
        if (!is_string($rno)) {
            $rno = strval($rno);
        }
        if (strlen($rno) == 0) {
            throw new Klarna_ArgumentNotSetException("RNO");
        }
    }

    /**
     * Checks/fixes so that reference/refCode are valid.
     *
     * @param string &$reference Reference string.
     * @param string &$refCode   Reference code.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkRef(&$reference, &$refCode)
    {
        if (!is_string($reference)) {
            $reference = strval($reference);
            if (!is_string($reference)) {
                throw new Klarna_InvalidTypeException("Reference", "string");
            }
        }

        if (!is_string($refCode)) {
            $refCode = strval($refCode);
            if (!is_string($refCode)) {
                throw new Klarna_InvalidTypeException("Reference code", "string");
            }
        }
    }

    /**
     * Checks/fixes so that the OCR input is valid.
     *
     * @param string &$ocr OCR number.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkOCR(&$ocr)
    {
        if (!is_string($ocr)) {
            $ocr = strval($ocr);
            if (!is_string($ocr)) {
                throw new Klarna_InvalidTypeException("OCR", "string");
            }
        }
    }

     /**
     * Check so required argument is supplied.
     *
     * @param string $argument argument to check
     * @param string $name     name of argument
     *
     * @throws Klarna_ArgumentNotSetException
     * @return void
     */
    private function _checkArgument($argument, $name)
    {
        if (!is_string($argument)) {
            $argument = strval($argument);
        }

        if (strlen($argument) == 0) {
            throw new Klarna_ArgumentNotSetException($name);
        }
    }

    /**
     * Check so Locale settings (country, currency, language) are set.
     *
     * @throws KlarnaException
     * @return void
     */
    private function _checkLocale()
    {
        if (!is_int($this->_country)
            || !is_int($this->_language)
            || !is_int($this->_currency)
        ) {
            throw new Klarna_InvalidLocaleException;
        }
    }

    /**
     * Checks wether a goodslist is set.
     *
     * @throws Klarna_MissingGoodslistException
     * @return void
     */
    private function _checkGoodslist()
    {
        if (!is_array($this->goodsList) || empty($this->goodsList)) {
            throw new Klarna_MissingGoodslistException;
        }
    }

    /**
     * Set the pcStorage method used for this instance
     *
     * @param PCStorage $pcStorage PCStorage implementation
     *
     * @return void
     */
    public function setPCStorage($pcStorage)
    {
        if (!($pcStorage instanceof PCStorage)) {
            throw new Klarna_InvalidTypeException('pcStorage', 'PCStorage');
        }
        $this->pcStorage = $pcStorage->getName();
        $this->pclasses = $pcStorage;
    }

    /**
     * Ensure the configuration is of the correct type.
     *
     * @param array|ArrayAccess|null $config an optional config to validate
     *
     * @return void
     */
    private function _checkConfig($config = null)
    {
        if ($config === null) {
            $config = $this->config;
        }
        if (!($config instanceof ArrayAccess)
            && !is_array($config)
        ) {
            throw new Klarna_IncompleteConfigurationException;
        }
    }

} //End Klarna

/**
 * Include the {@link KlarnaConfig} class.
 */
require_once 'klarnaconfig.php';

/**
 * Include the {@link KlarnaPClass} class.
 */
require_once 'klarnapclass.php';

/**
 * Include the {@link KlarnaCalc} class.
 */
require_once 'klarnacalc.php';

/**
 * Include the {@link KlarnaAddr} class.
 */
require_once 'klarnaaddr.php';

/**
 * Include the Exception classes.
 */
require_once 'Exceptions.php';

/**
 * Include the KlarnaEncoding class.
 */
require_once 'Encoding.php';


/**
 * Include the KlarnaFlags class.
 */
require_once 'Flags.php';

/**
 * Include KlarnaCountry, KlarnaCurrency, KlarnaLanguage classes
 */
require_once 'Country.php';
require_once 'Currency.php';
require_once 'Language.php';
