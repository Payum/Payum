<?php

namespace Payum\Core\Bridge\Propel\Model\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'PaymentSecurityToken' table.
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
class PaymentSecurityTokenTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'src.Payum.Core.Bridge.Propel.Model.map.PaymentSecurityTokenTableMap';

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
        $this->setName('PaymentSecurityToken');
        $this->setPhpName('PaymentSecurityToken');
        $this->setClassname('Payum\\Core\\Bridge\\Propel\\Model\\PaymentSecurityToken');
        $this->setPackage('src.Payum.Core.Bridge.Propel.Model');
        $this->setUseIdGenerator(false);
        // columns
        $this->addPrimaryKey('hash', 'Hash', 'VARCHAR', true, 100, null);
        $this->addColumn('details', 'Details', 'OBJECT', true, null, null);
        $this->addColumn('after_url', 'AfterUrl', 'LONGVARCHAR', true, null, null);
        $this->addColumn('target_url', 'TargetUrl', 'LONGVARCHAR', true, null, null);
        $this->addColumn('payment_name', 'PaymentName', 'VARCHAR', true, 255, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
    } // buildRelations()

} // PaymentSecurityTokenTableMap
