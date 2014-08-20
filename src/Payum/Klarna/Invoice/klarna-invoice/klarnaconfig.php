<?php
/**
 * KlarnaConfig
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
 * Configuration class for the Klarna instance.
 *
 * KlarnaConfig stores added fields in JSON, it also prepends.<br>
 * Loads/saves specified file, or default file, if {@link KlarnaConfig::$store}
 * is set to true.<br>
 *
 * You add settings using the ArrayAccess:<br>
 * $arr['field'] = $val or $arr->offsetSet('field', $val);<br>
 *
 * Available settings are:<br>
 * eid         - Merchant ID (int)
 * secret      - Shared secret (string)
 * country     - Country constant or code  (int|string)
 * language    - Language constant or code (int|string)
 * currency    - Currency constant or code (int|string)
 * mode        - Klarna::BETA or Klarna::LIVE
 * ssl         - Use HTTPS or HTTP. (bool)
 * pcStorage   - Storage module, e.g. 'json'
 * pcURI       - URI to where the PClasses are stored, e.g.
 *               '/srv/shop/pclasses.json'
 * xmlrpcDebug - XMLRPC debugging (bool)
 * debug       - Normal debugging (bool)
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
class KlarnaConfig implements ArrayAccess
{

    /**
     * An array containing all the options for this config.
     *
     * @ignore Do not show in PHPDoc.
     * @var array
     */
    protected $options;

    /**
     * If set to true, saves the config.
     *
     * @var bool
     */
    public static $store = true;

    /**
     * URI to the config file.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $file;

    /**
     * Class constructor
     *
     * Loads specified file, or default file,
     * if {@link KlarnaConfig::$store} is set to true.
     *
     * @param string $file URI to config file, e.g. ./config.json
     */
    public function __construct($file = null)
    {
        $this->options = array();
        if ($file) {
            $this->file = $file;
            if (is_readable($this->file)) {
                $this->options = json_decode(
                    file_get_contents(
                        $this->file
                    ), true
                );
            }
        }
    }

    /**
     * Clears the config.
     *
     * @return void
     */
    public function clear()
    {
        $this->options = array();
    }

    /**
     * Class destructor
     *
     * Saves specified file, or default file,
     * if {@link KlarnaConfig::$store} is set to true.
     */
    public function __destruct()
    {
        if (self::$store && $this->file) {
            if ((!file_exists($this->file)
                && is_writable(dirname($this->file)))
                || is_writable($this->file)
            ) {
                file_put_contents($this->file, json_encode($this->options));
            }
        }
    }

    /**
     * Returns true whether the field exists.
     *
     * @param mixed $offset field
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->options[$offset]);
    }

    /**
     * Used to get the value of a field.
     *
     * @param mixed $offset field
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }
        return $this->options[$offset];
    }

    /**
     * Used to set a value to a field.
     *
     * @param mixed $offset field
     * @param mixed $value  value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->options[$offset] = $value;
    }

    /**
     * Removes the specified field.
     *
     * @param mixed $offset field
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->options[$offset]);
    }
}
