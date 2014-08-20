<?php

/**
 * KlarnaLanguage
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
 * Language Constants class
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
class KlarnaLanguage
{

    /**
     * Language constant for Danish (DA).<br>
     * ISO639_DA
     *
     * @var int
     */
    const DA = 27;

    /**
     * Language constant for German (DE).<br>
     * ISO639_DE
     *
     * @var int
     */
    const DE = 28;

    /**
     * Language constant for English (EN).<br>
     * ISO639_EN
     *
     * @var int
     */
    const EN = 31;

    /**
     * Language constant for Finnish (FI).<br>
     * ISO639_FI
     *
     * @var int
     */
    const FI = 37;

    /**
     * Language constant for Norwegian (NB).<br>
     * ISO639_NB
     *
     * @var int
     */
    const NB = 97;

    /**
     * Language constant for Dutch (NL).<br>
     * ISO639_NL
     *
     * @var int
     */
    const NL = 101;

    /**
     * Language constant for Swedish (SV).<br>
     * ISO639_SV
     *
     * @var int
     */
    const SV = 138;

    /**
     * Converts a language code, e.g. 'de' to the KlarnaLanguage constant.
     *
     * @param string $val language code
     *
     * @return int|null
     */
    public static function fromCode($val)
    {
        $val = strtoupper($val);
        if (array_key_exists($val, self::$_languages)) {
            return self::$_languages[$val];
        }
        return null;
    }

    /**
     * Converts a KlarnaLanguage constant to the respective language code.
     *
     * @param int $val KlarnaLanguage constant
     *
     * @return lowercase string|null
     */
    public static function getCode($val)
    {
        if (self::$_languageFlip === array()) {
            self::$_languageFlip = array_flip(self::$_languages);
        }
        if (array_key_exists($val, self::$_languageFlip)) {
            return strtolower(self::$_languageFlip[$val]);
        }
        return null;
    }

    /**
     * Cache for the flipped language array
     *
     * @var array
     */
    private static $_languageFlip = array();

    /**
     * Array containing all languages and their KRED Code
     *
     * @var array
     */
    private static $_languages = array(
        'AA' => 1,    // Afar
        'AB' => 2,    // Abkhazian
        'AE' => 3,    // Avestan
        'AF' => 4,    // Afrikaans
        'AM' => 5,    // Amharic
        'AR' => 6,    // Arabic
        'AS' => 7,    // Assamese
        'AY' => 8,    // Aymara
        'AZ' => 9,    // Azerbaijani
        'BA' => 10,    // Bashkir
        'BE' => 11,    // Byelorussian; Belarusian
        'BG' => 12,    // Bulgarian
        'BH' => 13,    // Bihari
        'BI' => 14,    // Bislama
        'BN' => 15,    // Bengali; Bangla
        'BO' => 16,    // Tibetan
        'BR' => 17,    // Breton
        'BS' => 18,    // Bosnian
        'CA' => 19,    // Catalan
        'CE' => 20,    // Chechen
        'CH' => 21,    // Chamorro
        'CO' => 22,    // Corsican
        'CS' => 23,    // Czech
        'CU' => 24,    // Church Slavic
        'CV' => 25,    // Chuvash
        'CY' => 26,    // Welsh
        'DA' => 27,    // Danish
        'DE' => 28,    // German
        'DZ' => 29,    // Dzongkha; Bhutani
        'EL' => 30,    // Greek
        'EN' => 31,    // English
        'EO' => 32,    // Esperanto
        'ES' => 33,    // Spanish
        'ET' => 34,    // Estonian
        'EU' => 35,    // Basque
        'FA' => 36,    // Persian
        'FI' => 37,    // Finnish
        'FJ' => 38,    // Fijian; Fiji
        'FO' => 39,    // Faroese
        'FR' => 40,    // French
        'FY' => 41,    // Frisian
        'GA' => 42,    // Irish
        'GD' => 43,    // Scots; Gaelic
        'GL' => 44,    // Gallegan; Galician
        'GN' => 45,    // Guarani
        'GU' => 46,    // Gujarati
        'GV' => 47,    // Manx
        'HA' => 48,    // Hausa
        'HE' => 49,    // Hebrew (formerly iw)
        'HI' => 50,    // Hindi
        'HO' => 51,    // Hiri Motu
        'HR' => 52,    // Croatian
        'HU' => 53,    // Hungarian
        'HY' => 54,    // Armenian
        'HZ' => 55,    // Herero
        'IA' => 56,    // Interlingua
        'ID' => 57,    // Indonesian (formerly in)
        'IE' => 58,    // Interlingue
        'IK' => 59,    // Inupiak
        'IO' => 60,    // Ido
        'IS' => 61,    // Icelandic
        'IT' => 62,    // Italian
        'IU' => 63,    // Inuktitut
        'JA' => 64,    // Japanese
        'JV' => 65,    // Javanese
        'KA' => 66,    // Georgian
        'KI' => 67,    // Kikuyu
        'KJ' => 68,    // Kuanyama
        'KK' => 69,    // Kazakh
        'KL' => 70,    // Kalaallisut; Greenlandic
        'KM' => 71,    // Khmer; Cambodian
        'KN' => 72,    // Kannada
        'KO' => 73,    // Korean
        'KS' => 74,    // Kashmiri
        'KU' => 75,    // Kurdish
        'KV' => 76,    // Komi
        'KW' => 77,    // Cornish
        'KY' => 78,    // Kirghiz
        'LA' => 79,    // Latin
        'LB' => 80,    // Letzeburgesch
        'LN' => 81,    // Lingala
        'LO' => 82,    // Lao; Laotian
        'LT' => 83,    // Lithuanian
        'LV' => 84,    // Latvian; Lettish
        'MG' => 85,    // Malagasy
        'MH' => 86,    // Marshall
        'MI' => 87,    // Maori
        'MK' => 88,    // Macedonian
        'ML' => 89,    // Malayalam
        'MN' => 90,    // Mongolian
        'MO' => 91,    // Moldavian
        'MR' => 92,    // Marathi
        'MS' => 93,    // Malay
        'MT' => 94,    // Maltese
        'MY' => 95,    // Burmese
        'NA' => 96,    // Nauru
        'NB' => 97,    // Norwegian Bokmål
        'ND' => 98,    // Ndebele, North
        'NE' => 99,    // Nepali
        'NG' => 100,    // Ndonga
        'NL' => 101,    // Dutch
        'NN' => 102,    // Norwegian Nynorsk
        'NO' => 103,    // Norwegian
        'NR' => 104,    // Ndebele, South
        'NV' => 105,    // Navajo
        'NY' => 106,    // Chichewa; Nyanja
        'OC' => 107,    // Occitan; Provençal
        'OM' => 108,    // (Afan) Oromo
        'OR' => 109,    // Oriya
        'OS' => 110,    // Ossetian; Ossetic
        'PA' => 111,    // Panjabi; Punjabi
        'PI' => 112,    // Pali
        'PL' => 113,    // Polish
        'PS' => 114,    // Pashto, Pushto
        'PT' => 115,    // Portuguese
        'QU' => 116,    // Quechua
        'RM' => 117,    // Rhaeto-Romance
        'RN' => 118,    // Rundi; Kirundi
        'RO' => 119,    // Romanian
        'RU' => 120,    // Russian
        'RW' => 121,    // Kinyarwanda
        'SA' => 122,    // Sanskrit
        'SC' => 123,    // Sardinian
        'SD' => 124,    // Sindhi
        'SE' => 125,    // Northern Sami
        'SG' => 126,    // Sango; Sangro
        'SI' => 127,    // Sinhalese
        'SK' => 128,    // Slovak
        'SL' => 129,    // Slovenian
        'SM' => 130,    // Samoan
        'SN' => 131,    // Shona
        'SO' => 132,    // Somali
        'SQ' => 133,    // Albanian
        'SR' => 134,    // Serbian
        'SS' => 135,    // Swati; Siswati
        'ST' => 136,    // Sesotho; Sotho, Southern
        'SU' => 137,    // Sundanese
        'SV' => 138,    // Swedish
        'SW' => 139,    // Swahili
        'TA' => 140,    // Tamil
        'TE' => 141,    // Telugu
        'TG' => 142,    // Tajik
        'TH' => 143,    // Thai
        'TI' => 144,    // Tigrinya
        'TK' => 145,    // Turkmen
        'TL' => 146,    // Tagalog
        'TN' => 147,    // Tswana; Setswana
        'TO' => 148,    // Tongan
        'TR' => 149,    // Turkish
        'TS' => 150,    // Tsonga
        'TT' => 151,    // Tatar
        'TW' => 152,    // Twi
        'TY' => 153,    // Tahitian
        'UG' => 154,    // Uighur
        'UK' => 155,    // Ukrainian
        'UR' => 156,    // Urdu
        'UZ' => 157,    // Uzbek
        'VI' => 158,    // Vietnamese
        'VO' => 159,    // Volapuk
        'WA' => 160,    // Walloon
        'WO' => 161,    // Wolof
        'XH' => 162,    // Xhosa
        'YI' => 163,    // Yiddish (formerly ji)
        'YO' => 164,    // Yoruba
        'ZA' => 165,    // Zhuang
        'ZH' => 166,    // Chinese
        'ZU' => 167     // Zulu
    );
}
