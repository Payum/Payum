<?php
/**
 * KlarnaAddr
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
 * KlarnaAddr is an object of convenience, to parse and create addresses.
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
class KlarnaAddr
{

    /**
     * Email address.
     *
     * @var string
     */
    protected $email;

    /**
     * Phone number.
     *
     * @var string
     */
    protected $telno;

    /**
     * Cellphone number.
     *
     * @var string
     */
    protected $cellno;

    /**
     * First name.
     *
     * @var string
     */
    protected $fname;

    /**
     * Last name.
     *
     * @var string
     */
    protected $lname;

    /**
     * Company name.
     *
     * @var string
     */
    protected $company;

    /**
     * Care of, C/O.
     *
     * @var string
     */
    protected $careof;

    /**
     * Street address.
     *
     * @var string
     */
    protected $street;

    /**
     * Zip code.
     *
     * @var string
     */
    protected $zip;

    /**
     * City.
     *
     * @var string
     */
    protected $city;

    /**
     * KlarnaCountry constant
     *
     * @var int
     */
    protected $country;

    /**
     * House number.
     * Only for NL and DE!
     *
     * @var string
     */
    protected $houseNo;

    /**
     * House extension.
     * Only for NL!
     *
     * @var string
     */
    protected $houseExt;

    /**
     * When using {@link Klarna::getAddresses()} this might be guessed
     * depending on type used.
     *
     * Signifies if address is for a company or a private person.
     * If isCompany is null, then it is unknown and will be assumed to
     * be a private person.
     *
     * <b>Note</b>:<br>
     * This has no effect on transmitted data.
     *
     * @var bool|null
     */
    public $isCompany = null;

    /**
     * Class constructor.
     *
     * Calls the set methods for all arguments.
     *
     * @param string     $email    Email address.
     * @param string     $telno    Phone number.
     * @param string     $cellno   Cellphone number.
     * @param string     $fname    First name.
     * @param string     $lname    Last name.
     * @param string     $careof   Care of, C/O.
     * @param string     $street   Street address.
     * @param string     $zip      Zip code.
     * @param string     $city     City.
     * @param string|int $country  KlarnaCountry constant or two letter code.
     * @param string     $houseNo  House number, only used in DE and NL.
     * @param string     $houseExt House extension, only used in NL.
     *
     * @throws KlarnaException
     */
    public function __construct(
        $email = null, $telno = null, $cellno = null, $fname = null,
        $lname = null, $careof = "", $street = null, $zip = null,
        $city = null, $country = null, $houseNo = "", $houseExt = ""
    ) {
        //Set all string values to ""
        $this->company = "";
        $this->telno = "";
        $this->careof = "";
        $this->cellno = "";
        $this->city = "";
        $this->email = "";
        $this->fname = "";
        $this->lname = "";
        $this->zip = "";

        if ($email !== null) {
            $this->setEmail($email);
        }

        if ($telno !== null) {
            $this->setTelno($telno);
        }

        if ($cellno !== null) {
            $this->setCellno($cellno);
        }

        if ($fname !== null) {
            $this->setFirstName($fname);
        }

        if ($lname !== null) {
            $this->setLastName($lname);
        }

        $this->setCareof($careof);

        if ($street !== null) {
            $this->setStreet($street);
        }

        if ($zip !== null) {
            $this->setZipCode($zip);
        }

        if ($city !== null) {
            $this->setCity($city);
        }

        if ($country !== null) {
            $this->setCountry($country);
        }

        $this->setHouseNumber($houseNo);
        $this->setHouseExt($houseExt);
    }

    /**
     * Returns the email address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email address.
     *
     * @param string $email email address
     *
     * @return void
     */
    public function setEmail($email)
    {
        if (!is_string($email)) {
            $email = strval($email);
        }

        $this->email = $email;
    }

    /**
     * Returns the phone number.
     *
     * @return string
     */
    public function getTelno()
    {
        return $this->telno;
    }

    /**
     * Sets the phone number.
     *
     * @param string $telno telno
     *
     * @return void
     */
    public function setTelno($telno)
    {
        if (!is_string($telno)) {
            $telno = strval($telno);
        }
        $this->telno = $telno;
    }

    /**
     * Returns the cellphone number.
     *
     * @return string
     */
    public function getCellno()
    {
        return $this->cellno;
    }

    /**
     * Sets the cellphone number.
     *
     * @param string $cellno mobile number
     *
     * @return void
     */
    public function setCellno($cellno)
    {
        if (!is_string($cellno)) {
            $cellno = strval($cellno);
        }

        $this->cellno = $cellno;
    }

    /**
     * Returns the first name.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->fname;
    }

    /**
     * Sets the first name.
     *
     * @param string $fname firstname
     *
     * @return void
     */
    public function setFirstName($fname)
    {
        if (!is_string($fname)) {
            $fname = strval($fname);
        }

        $this->fname = $fname;
    }

    /**
     * Returns the last name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lname;
    }

    /**
     * Sets the last name.
     *
     * @param string $lname lastname
     *
     * @return void
     */
    public function setLastName($lname)
    {
        if (!is_string($lname)) {
            $lname = strval($lname);
        }

        $this->lname = $lname;
    }

    /**
     * Returns the company name.
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->company;
    }

    /**
     * Sets the company name.
     * If the purchase results in a company purchase,
     * reference person will be used from first and last name,
     * or the value set with {@link Klarna::setReference()}.
     *
     * @param string $company company name
     *
     * @see Klarna::setReference
     * @return void
     */
    public function setCompanyName($company)
    {
        if (!is_string($company)) {
            $company = strval($company);
        }

        $this->company = $company;
    }

    /**
     * Returns the care of, C/O.
     *
     * @return string
     */
    public function getCareof()
    {
        return $this->careof;
    }

    /**
     * Sets the care of, C/O.
     *
     * @param string $careof care of address
     *
     * @return void
     */
    public function setCareof($careof)
    {
        if (!is_string($careof)) {
            $careof = strval($careof);
        }

        $this->careof = $careof;
    }

    /**
     * Returns the street address.
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Sets the street address.
     *
     * @param string $street street address
     *
     * @return void
     */
    public function setStreet($street)
    {
        if (!is_string($street)) {
            $street = strval($street);
        }

        $this->street = $street;
    }

    /**
     * Returns the zip code.
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zip;
    }

    /**
     * Sets the zip code.
     *
     * @param string $zip zip code
     *
     * @return void
     */
    public function setZipCode($zip)
    {

        if (!is_string($zip)) {
            $zip = strval($zip);
        }

        $zip = str_replace(' ', '', $zip); //remove spaces

        $this->zip = $zip;
    }

    /**
     * Returns the city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the city.
     *
     * @param string $city city
     *
     * @return void
     */
    public function setCity($city)
    {
        if (!is_string($city)) {
            $city = strval($city);
        }

        $this->city = $city;
    }

    /**
     * Returns the country as a integer constant.
     *
     * @return int {@link KlarnaCountry}
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Returns the country as a two letter representation.
     *
     * @throws KlarnaException
     * @return string  E.g. 'de', 'dk', ...
     */
    public function getCountryCode()
    {
        return KlarnaCountry::getCode($this->country);
    }

    /**
     * Sets the country, use either a two letter representation or the integer
     * constant.
     *
     * @param int $country {@link KlarnaCountry}
     *
     * @throws KlarnaException
     * @return void
     */
    public function setCountry($country)
    {
        if ($country === null) {
            throw new Klarna_ArgumentNotSetException('Country');
        }
        if (is_numeric($country)) {
            if (!is_int($country)) {
                $country = intval($country);
            }
            $this->country = $country;
            return;
        }
        if (strlen($country) == 2 || strlen($country) == 3) {
            $this->setCountry(KlarnaCountry::fromCode($country));
            return;
        }
        throw new KlarnaException("Failed to set country! ($country)");
    }

    /**
     * Returns the house number.<br>
     * Only used in Germany and Netherlands.<br>
     *
     * @return string
     */
    public function getHouseNumber()
    {
        return $this->houseNo;
    }

    /**
     * Sets the house number.<br>
     * Only used in Germany and Netherlands.<br>
     *
     * @param string $houseNo house number
     *
     * @return void
     */
    public function setHouseNumber($houseNo)
    {
        if (!is_string($houseNo)) {
            $houseNo = strval($houseNo);
        }

        $this->houseNo = $houseNo;
    }

    /**
     * Returns the house extension.<br>
     * Only used in Netherlands.<br>
     *
     * @return string
     */
    public function getHouseExt()
    {
        return $this->houseExt;
    }

    /**
     * Sets the house extension.<br>
     * Only used in Netherlands.<br>
     *
     * @param string $houseExt house extension
     *
     * @return void
     */
    public function setHouseExt($houseExt)
    {
        if (!is_string($houseExt)) {
            $houseExt = strval($houseExt);
        }

        $this->houseExt = $houseExt;
    }

    /**
     * Returns an associative array representing this object.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'email' => $this->getEmail(),
            'telno' => $this->getTelno(),
            'cellno' => $this->getCellno(),
            'fname' => $this->getFirstName(),
            'lname' => $this->getLastName(),
            'company' => $this->getCompanyName(),
            'careof' => $this->getCareof(),
            'street' => $this->getStreet(),
            'house_number' => $this->getHouseNumber(),
            'house_extension' => $this->getHouseExt(),
            'zip' => $this->getZipCode(),
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
        );
    }

}
