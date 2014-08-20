<?php
/**
 * JsonStorage
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
 * Include the {@link PCStorage} interface.
 */
require_once 'storage.intf.php';

/**
 * JSON storage class for KlarnaPClass
 *
 * This class is an JSON implementation of the PCStorage interface.
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */

class JSONStorage extends PCStorage
{


    /**
     * return the name of the storage type
     *
     * @return string
     */
    public function getName()
    {
        return "json";
    }

    /**
     * Checks if the file is writeable, readable or if the directory is.
     *
     * @param string $jsonFile json file that holds the pclasses
     *
     * @throws error
     * @return void
     */
    protected function checkURI($jsonFile)
    {
        //If file doesn't exist, check the directory.
        if (!file_exists($jsonFile)) {
            $jsonFile = dirname($jsonFile);
        }

        if (!is_writable($jsonFile)) {
            throw new Klarna_FileNotWritableException($jsonFile);
        }

        if (!is_readable($jsonFile)) {
            throw new Klarna_FileNotReadableException($jsonFile);
        }
    }

    /**
     * Clear the pclasses
     *
     * @param string $uri uri to file to clear
     *
     * @throws KlarnaException
     * @return void
     */
    public function clear($uri)
    {
        $this->checkURI($uri);
        unset($this->pclasses);
        if (file_exists($uri)) {
            unlink($uri);
        }
    }

    /**
     * Load pclasses from file
     *
     * @param string $uri uri to file to load
     *
     * @throws KlarnaException
     * @return void
     */
    public function load($uri)
    {
        $this->checkURI($uri);
        if (!file_exists($uri)) {
            //Do not fail, if file doesn't exist.
            return;
        }
        $arr = json_decode(file_get_contents($uri), true);

        if (count($arr) == 0) {
            return;
        }

        foreach ($arr as $pclasses) {
            if (count($pclasses) == 0) {
                continue;
            }
            foreach ($pclasses as $pclass) {
                $this->addPClass(new KlarnaPClass($pclass));
            }
        }
    }

    /**
     * Save pclasses to file
     *
     * @param string $uri uri to file to save
     *
     * @throws KlarnaException
     * @return void
     */
    public function save($uri)
    {
        try {
            $this->checkURI($uri);

            $output = array();
            foreach ($this->pclasses as $eid => $pclasses) {
                foreach ($pclasses as $pclass) {
                    if (!isset($output[$eid])) {
                        $output[$eid] = array();
                    }
                    $output[$eid][] = $pclass->toArray();
                }
            }
            if (count($this->pclasses) > 0) {
                file_put_contents($uri, json_encode($output));
            } else {
                file_put_contents($uri, "");
            }
        } catch(Exception $e) {
            throw new KlarnaException($e->getMessage());
        }
    }
}
