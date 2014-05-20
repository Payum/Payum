<?php

namespace Payum\Core\Bridge\Propel\Model\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'PaymentDetails' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator.src.Payum.Core.Bridge.Propel.Model.map
 */
class PaymentDetailsTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'src.Payum.Core.Bridge.Propel.Model.map.PaymentDetailsTableMap';

    /**
     * Initialize the table attributes, columns and validators
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('PaymentDetails');
        $this->setPhpName('PaymentDetails');
        $this->setClassname('Payum\\Core\\Bridge\\Propel\\Model\\PaymentDetails');
        $this->setPackage('src.Payum.Core.Bridge.Propel.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('details', 'Details', 'LONGVARCHAR', true, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
    } // buildRelations()

} // PaymentDetailsTableMap
