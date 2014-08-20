<?php
/**
 * XMLStorage
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
 * XML storage class for KlarnaPClass
 *
 * This class is an XML implementation of the PCStorage interface.
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
class XMLStorage extends PCStorage
{

    /**
     * The internal XML document.
     *
     * @var DOMDocument
     */
    protected $dom;

    /**
     * XML version for the DOM document.
     *
     * @var string
     */
    protected $version = '1.0';

    /**
     * Encoding for the DOM document.
     *
     * @var string
     */
    protected $encoding = 'ISO-8859-1';

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->dom = new DOMDocument($this->version, $this->encoding);
        $this->dom->formatOutput = true;
        $this->dom->preserveWhiteSpace = false;
    }

    /**
     * return the name of the storage type
     *
     * @return string
     */
    public function getName()
    {
        return "xml";
    }

    /**
     * Checks if the file is writeable, readable or if the directory is.
     *
     * @param string $xmlFile URI to XML file.
     *
     * @throws KlarnaException
     * @return void
     */
    protected function checkURI($xmlFile)
    {
        //If file doesn't exist, check the directory.
        if (!file_exists($xmlFile)) {
            $xmlFile = dirname($xmlFile);
        }

        if (!is_writable($xmlFile)) {
            throw new Klarna_FileNotWritableException($xmlFile);
        }

        if (!is_readable($xmlFile)) {
            throw new Klarna_FileNotReadableException($xmlFile);
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
        if (!@$this->dom->load($uri)) {
            throw new Klarna_XMLParseException($uri);
        }

        $xpath = new DOMXpath($this->dom);
        foreach ($xpath->query('/klarna/estore') as $estore) {
            $eid = $estore->getAttribute('id');

            foreach ($xpath->query('pclass', $estore) as $node) {
                $pclass = new KlarnaPClass();
                $pclass->setId(
                    $node->getAttribute('pid')
                );
                $pclass->setType(
                    $node->getAttribute('type')
                );
                $pclass->setEid($eid);
                $pclass->setDescription(
                    $xpath->query('description', $node)->item(0)->textContent
                );
                $pclass->setMonths(
                    $xpath->query('months', $node)->item(0)->textContent
                );
                $pclass->setStartFee(
                    $xpath->query('startfee', $node)->item(0)->textContent
                );
                $pclass->setInvoiceFee(
                    $xpath->query('invoicefee', $node)->item(0)->textContent
                );
                $pclass->setInterestRate(
                    $xpath->query('interestrate', $node)->item(0)->textContent
                );
                $pclass->setMinAmount(
                    $xpath->query('minamount', $node)->item(0)->textContent
                );
                $pclass->setCountry(
                    $xpath->query('country', $node)->item(0)->textContent
                );
                $pclass->setExpire(
                    $xpath->query('expire', $node)->item(0)->textContent
                );

                $this->addPClass($pclass);
            }
        }
    }

    /**
     * Creates DOMElement for all fields for specified PClass.
     *
     * @param KlarnaPClass $pclass pclass object
     *
     * @return array Array of DOMElements.
     */
    protected function createFields($pclass)
    {
        $fields = array();

        //This is to prevent HTMLEntities to be converted to the real character.
        $fields[] = $this->dom->createElement('description');
        end($fields)->appendChild(
            $this->dom->createTextNode($pclass->getDescription())
        );
        $fields[] = $this->dom->createElement(
            'months', $pclass->getMonths()
        );
        $fields[] = $this->dom->createElement(
            'startfee', $pclass->getStartFee()
        );
        $fields[] = $this->dom->createElement(
            'invoicefee', $pclass->getInvoiceFee()
        );
        $fields[] = $this->dom->createElement(
            'interestrate', $pclass->getInterestRate()
        );
        $fields[] = $this->dom->createElement(
            'minamount', $pclass->getMinAmount()
        );
        $fields[] = $this->dom->createElement(
            'country', $pclass->getCountry()
        );
        $fields[] = $this->dom->createElement(
            'expire', $pclass->getExpire()
        );

        return $fields;
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
        $this->checkURI($uri);

        //Reset DOMDocument.
        if (!$this->dom->loadXML(
            "<?xml version='$this->version' encoding='$this->encoding'?"
            ."><klarna/>"
        )
        ) {
            throw new Klarna_XMLParseException($uri);
        }

        ksort($this->pclasses, SORT_NUMERIC);
        $xpath = new DOMXpath($this->dom);

        foreach ($this->pclasses as $eid => $pclasses) {
            $estore = $xpath->query('/klarna/estore[@id="'.$eid.'"]');

            if ($estore === false || $estore->length === 0) {
                //No estore with matching eid, create it.
                $estore = $this->dom->createElement('estore');
                $estore->setAttribute('id', $eid);
                $this->dom->documentElement->appendChild($estore);
            } else {
                $estore = $estore->item(0);
            }

            foreach ($pclasses as $pclass) {
                if ($eid != $pclass->getEid()) {
                    //This should never occur, failsafe.
                    continue;
                }

                $pnode = $this->dom->createElement('pclass');

                foreach ($this->createFields($pclass) as $field) {
                    $pnode->appendChild($field);
                }

                $pnode->setAttribute('pid', $pclass->getId());
                $pnode->setAttribute('type', $pclass->getType());

                $estore->appendChild($pnode);
            }
        }

        if (!$this->dom->save($uri)) {
            throw new KlarnaException('Failed to save XML document!');
        }
    }

    /**
     * This uses unlink (delete) to clear the pclasses!
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
}
