<?php
/**
 * PClass Storage Interface
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
 * KlarnaPClass Storage interface
 *
 * This class provides an interface with which to save the PClasses easily.
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
abstract class PCStorage
{

    /**
     * An array of KlarnaPClasses.
     *
     * @var array
     */
    protected $pclasses;

    /**
     * Thhe name of the implementation.
     * The file should be <name>storage.class.php
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Adds a PClass to the storage.
     *
     * @param KlarnaPClass $pclass PClass object.
     *
     * @throws KlarnaException
     * @return void
     */
    public function addPClass($pclass)
    {
        if (! $pclass instanceof KlarnaPClass) {
            throw new Klarna_InvalidTypeException('pclass', 'KlarnaPClass');
        }

        if (!isset($this->pclasses) || !is_array($this->pclasses)) {
            $this->pclasses = array();
        }

        if ($pclass->getDescription() === null || $pclass->getType() === null) {
            //Something went wrong, do not save these!
            return;
        }

        if (!isset($this->pclasses[$pclass->getEid()])) {
            $this->pclasses[$pclass->getEid()] = array();
        }
        $this->pclasses[$pclass->getEid()][$pclass->getId()] = $pclass;
    }

    /**
     * Gets the PClass by ID.
     *
     * @param int $id      PClass ID.
     * @param int $eid     Merchant ID.
     * @param int $country {@link KlarnaCountry Country} constant.
     *
     * @throws KlarnaException
     * @return KlarnaPClass
     */
    public function getPClass($id, $eid, $country)
    {
        if (!is_int($id)) {
            throw new InvalidArgumentException('Supplied ID is not an integer!');
        }

        if (!is_array($this->pclasses)) {
            throw new Klarna_PClassException('No match for that eid!');
        }

        if (!isset($this->pclasses[$eid]) || !is_array($this->pclasses[$eid])) {
            throw new Klarna_PClassException('No match for that eid!');
        }

        if (!isset($this->pclasses[$eid][$id])
            || !$this->pclasses[$eid][$id]->isValid()
        ) {
            throw new Klarna_PClassException('No such pclass available!');
        }

        if ($this->pclasses[$eid][$id]->getCountry() !== $country) {
            throw new Klarna_PClassException(
                'You cannot use this pclass with set country!'
            );
        }

        return $this->pclasses[$eid][$id];
    }

    /**
     * Returns an array of KlarnaPClasses, keyed with pclass ID.
     * If type is specified, only that type will be returned.
     *
     * <b>Types available</b>:<br>
     * {@link KlarnaPClass::ACCOUNT}<br>
     * {@link KlarnaPClass::CAMPAIGN}<br>
     * {@link KlarnaPClass::SPECIAL}<br>
     * {@link KlarnaPClass::DELAY}<br>
     * {@link KlarnaPClass::MOBILE}<br>
     *
     * @param int $eid     Merchant ID.
     * @param int $country {@link KlarnaCountry Country} constant.
     * @param int $type    PClass type identifier.
     *
     * @throws KlarnaException
     * @return array An array of {@link KlarnaPClass PClasses}.
     */
    public function getPClasses($eid, $country, $type = null)
    {
        if (!is_int($country)) {
            throw new Klarna_ArgumentNotSetException('country');
        }

        $tmp = false;
        if (!is_array($this->pclasses)) {
            return;
        }

        $tmp = array();
        foreach ($this->pclasses as $eid => $pclasses) {
            $tmp[$eid] = array();

            foreach ($pclasses as $pclass) {
                if (!$pclass->isValid()) {
                    continue; //Pclass invalid, skip it.
                }
                if ($pclass->getEid() === $eid
                    && $pclass->getCountry() === $country
                    && ($pclass->getType() === $type || $type === null)
                ) {
                    $tmp[$eid][$pclass->getId()] = $pclass;
                }
            }
        }

        return $tmp;
    }

    /**
     * Returns a flattened array of all pclasses
     *
     * @return array
     */
    public function getAllPClasses()
    {
        if (!is_array($this->pclasses)) {
            return array();
        }
        return $this->_flatten(array_values($this->pclasses));
    }

    /**
     * Flatten an array
     *
     * @param array $array array to flatten
     *
     * @return array
     */
    private function _flatten($array)
    {
        if (!is_array($array)) {
            // nothing to do if it's not an array
            return array($array);
        }
        $result = array();
        foreach ($array as $value) {
            // explode the sub-array, and add the parts
            $result = array_merge($result, $this->_flatten($value));
        }
        return $result;
    }

    /**
     * Loads the PClasses and calls {@link self::addPClass()} to store them
     * in runtime.
     * URI can be location to a file, or a db prefixed table.
     *
     * @param string $uri URI to stored PClasses.
     *
     * @throws KlarnaException|Exception
     * @return void
     */
    abstract public function load($uri);

    /**
     * Takes the internal PClass array and stores it.
     * URI can be location to a file, or a db prefixed table.
     *
     * @param string $uri URI to stored PClasses.
     *
     * @throws KlarnaException|Exception
     * @return void
     */
    abstract public function save($uri);

    /**
     * Removes the internally stored pclasses.
     *
     * @param string $uri URI to stored PClasses.
     *
     * @throws KlarnaException|Exception
     * @return void
     */
    abstract public function clear($uri);

}
