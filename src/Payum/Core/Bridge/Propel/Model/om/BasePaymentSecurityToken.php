<?php

namespace Payum\Core\Bridge\Propel\Model\om;

use \BaseObject;
use \BasePeer;
use \Criteria;
use \Exception;
use \PDO;
use \Persistent;
use \Propel;
use \PropelException;
use \PropelPDO;
use Payum\Core\Bridge\Propel\Model\PaymentSecurityToken;
use Payum\Core\Bridge\Propel\Model\PaymentSecurityTokenPeer;
use Payum\Core\Bridge\Propel\Model\PaymentSecurityTokenQuery;

abstract class BasePaymentSecurityToken extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'Payum\\Core\\Bridge\\Propel\\Model\\PaymentSecurityTokenPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        PaymentSecurityTokenPeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the hash field.
     * @var        string
     */
    protected $hash;

    /**
     * The value for the details field.
     * @var
     */
    protected $details;

    /**
     * The unserialized $details value - i.e. the persisted object.
     * This is necessary to avoid repeated calls to unserialize() at runtime.
     * @var        object
     */
    protected $details_unserialized;

    /**
     * The value for the after_url field.
     * @var        string
     */
    protected $after_url;

    /**
     * The value for the target_url field.
     * @var        string
     */
    protected $target_url;

    /**
     * The value for the payment_name field.
     * @var        string
     */
    protected $payment_name;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Flag to prevent endless clearAllReferences($deep=true) loop, if this object is referenced
     * @var        boolean
     */
    protected $alreadyInClearAllReferencesDeep = false;

    /**
     * Get the [hash] column value.
     *
     * @return string
     */
    public function getHash()
    {

        return $this->hash;
    }

    /**
     * Get the [details] column value.
     *
     * @return
     */
    public function getDetails()
    {
        if (null == $this->details_unserialized && null !== $this->details) {
            $this->details_unserialized = unserialize($this->details);
        }

        return $this->details_unserialized;
    }

    /**
     * Get the [after_url] column value.
     *
     * @return string
     */
    public function getAfterUrl()
    {

        return $this->after_url;
    }

    /**
     * Get the [target_url] column value.
     *
     * @return string
     */
    public function getTargetUrl()
    {

        return $this->target_url;
    }

    /**
     * Get the [payment_name] column value.
     *
     * @return string
     */
    public function getPaymentName()
    {

        return $this->payment_name;
    }

    /**
     * Set the value of [hash] column.
     *
     * @param  string $v new value
     * @return PaymentSecurityToken The current object (for fluent API support)
     */
    public function setHash($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->hash !== $v) {
            $this->hash = $v;
            $this->modifiedColumns[] = PaymentSecurityTokenPeer::HASH;
        }


        return $this;
    } // setHash()

    /**
     * Set the value of [details] column.
     *
     * @param   $v new value
     * @return PaymentSecurityToken The current object (for fluent API support)
     */
    public function setDetails($v)
    {
        if ($this->details_unserialized !== $v) {
            $this->details_unserialized = $v;
            $this->details = serialize($v);
            $this->modifiedColumns[] = PaymentSecurityTokenPeer::DETAILS;
        }


        return $this;
    } // setDetails()

    /**
     * Set the value of [after_url] column.
     *
     * @param  string $v new value
     * @return PaymentSecurityToken The current object (for fluent API support)
     */
    public function setAfterUrl($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->after_url !== $v) {
            $this->after_url = $v;
            $this->modifiedColumns[] = PaymentSecurityTokenPeer::AFTER_URL;
        }


        return $this;
    } // setAfterUrl()

    /**
     * Set the value of [target_url] column.
     *
     * @param  string $v new value
     * @return PaymentSecurityToken The current object (for fluent API support)
     */
    public function setTargetUrl($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->target_url !== $v) {
            $this->target_url = $v;
            $this->modifiedColumns[] = PaymentSecurityTokenPeer::TARGET_URL;
        }


        return $this;
    } // setTargetUrl()

    /**
     * Set the value of [payment_name] column.
     *
     * @param  string $v new value
     * @return PaymentSecurityToken The current object (for fluent API support)
     */
    public function setPaymentName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->payment_name !== $v) {
            $this->payment_name = $v;
            $this->modifiedColumns[] = PaymentSecurityTokenPeer::PAYMENT_NAME;
        }


        return $this;
    } // setPaymentName()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return true
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array $row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
     * @param int $startcol 0-based offset column which indicates which resultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false)
    {
        try {

            $this->hash = ($row[$startcol + 0] !== null) ? (string) $row[$startcol + 0] : null;
            $this->details = $row[$startcol + 1];
            $this->after_url = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->target_url = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->payment_name = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 5; // 5 = PaymentSecurityTokenPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating PaymentSecurityToken object", $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {

    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param boolean $deep (optional) Whether to also de-associated any related objects.
     * @param PropelPDO $con (optional) The PropelPDO connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getConnection(PaymentSecurityTokenPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = PaymentSecurityTokenPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param PropelPDO $con
     * @return void
     * @throws PropelException
     * @throws Exception
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete(PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(PaymentSecurityTokenPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = PaymentSecurityTokenQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param PropelPDO $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @throws Exception
     * @see        doSave()
     */
    public function save(PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(PaymentSecurityTokenPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                PaymentSecurityTokenPeer::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param PropelPDO $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see        save()
     */
    protected function doSave(PropelPDO $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param PropelPDO $con
     *
     * @throws PropelException
     * @see        doSave()
     */
    protected function doInsert(PropelPDO $con)
    {
        $modifiedColumns = array();
        $index = 0;


         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(PaymentSecurityTokenPeer::HASH)) {
            $modifiedColumns[':p' . $index++]  = '`hash`';
        }
        if ($this->isColumnModified(PaymentSecurityTokenPeer::DETAILS)) {
            $modifiedColumns[':p' . $index++]  = '`details`';
        }
        if ($this->isColumnModified(PaymentSecurityTokenPeer::AFTER_URL)) {
            $modifiedColumns[':p' . $index++]  = '`after_url`';
        }
        if ($this->isColumnModified(PaymentSecurityTokenPeer::TARGET_URL)) {
            $modifiedColumns[':p' . $index++]  = '`target_url`';
        }
        if ($this->isColumnModified(PaymentSecurityTokenPeer::PAYMENT_NAME)) {
            $modifiedColumns[':p' . $index++]  = '`payment_name`';
        }

        $sql = sprintf(
            'INSERT INTO `PaymentSecurityToken` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`hash`':
                        $stmt->bindValue($identifier, $this->hash, PDO::PARAM_STR);
                        break;
                    case '`details`':
                        $stmt->bindValue($identifier, $this->details, PDO::PARAM_STR);
                        break;
                    case '`after_url`':
                        $stmt->bindValue($identifier, $this->after_url, PDO::PARAM_STR);
                        break;
                    case '`target_url`':
                        $stmt->bindValue($identifier, $this->target_url, PDO::PARAM_STR);
                        break;
                    case '`payment_name`':
                        $stmt->bindValue($identifier, $this->payment_name, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), $e);
        }

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param PropelPDO $con
     *
     * @see        doSave()
     */
    protected function doUpdate(PropelPDO $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();
        BasePeer::doUpdate($selectCriteria, $valuesCriteria, $con);
    }

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @param mixed $columns Column name or an array of column names.
     * @return boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    public function validate($columns = null)
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();

            return true;
        }

        $this->validationFailures = $res;

        return false;
    }

    /**
     * This function performs the validation work for complex object models.
     *
     * In addition to checking the current object, all related objects will
     * also be validated.  If all pass then <code>true</code> is returned; otherwise
     * an aggregated array of ValidationFailed objects will be returned.
     *
     * @param array $columns Array of column names to validate.
     * @return mixed <code>true</code> if all validations pass; array of <code>ValidationFailed</code> objects otherwise.
     */
    protected function doValidate($columns = null)
    {
        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;

            $failureMap = array();


            if (($retval = PaymentSecurityTokenPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }



            $this->alreadyInValidation = false;
        }

        return (!empty($failureMap) ? $failureMap : true);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param string $name name
     * @param string $type The type of fieldname the $name is of:
     *               one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *               BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *               Defaults to BasePeer::TYPE_PHPNAME
     * @return mixed Value of field.
     */
    public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = PaymentSecurityTokenPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getHash();
                break;
            case 1:
                return $this->getDetails();
                break;
            case 2:
                return $this->getAfterUrl();
                break;
            case 3:
                return $this->getTargetUrl();
                break;
            case 4:
                return $this->getPaymentName();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
     *                    BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *                    Defaults to BasePeer::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to true.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array())
    {
        if (isset($alreadyDumpedObjects['PaymentSecurityToken'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['PaymentSecurityToken'][$this->getPrimaryKey()] = true;
        $keys = PaymentSecurityTokenPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getHash(),
            $keys[1] => $this->getDetails(),
            $keys[2] => $this->getAfterUrl(),
            $keys[3] => $this->getTargetUrl(),
            $keys[4] => $this->getPaymentName(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }


        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param string $name peer name
     * @param mixed $value field value
     * @param string $type The type of fieldname the $name is of:
     *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *                     Defaults to BasePeer::TYPE_PHPNAME
     * @return void
     */
    public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = PaymentSecurityTokenPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

        $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @param mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setHash($value);
                break;
            case 1:
                $this->setDetails($value);
                break;
            case 2:
                $this->setAfterUrl($value);
                break;
            case 3:
                $this->setTargetUrl($value);
                break;
            case 4:
                $this->setPaymentName($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
     * BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     * The default key type is the column's BasePeer::TYPE_PHPNAME
     *
     * @param array  $arr     An array to populate the object from.
     * @param string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = PaymentSecurityTokenPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setHash($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setDetails($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setAfterUrl($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setTargetUrl($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setPaymentName($arr[$keys[4]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(PaymentSecurityTokenPeer::DATABASE_NAME);

        if ($this->isColumnModified(PaymentSecurityTokenPeer::HASH)) $criteria->add(PaymentSecurityTokenPeer::HASH, $this->hash);
        if ($this->isColumnModified(PaymentSecurityTokenPeer::DETAILS)) $criteria->add(PaymentSecurityTokenPeer::DETAILS, $this->details);
        if ($this->isColumnModified(PaymentSecurityTokenPeer::AFTER_URL)) $criteria->add(PaymentSecurityTokenPeer::AFTER_URL, $this->after_url);
        if ($this->isColumnModified(PaymentSecurityTokenPeer::TARGET_URL)) $criteria->add(PaymentSecurityTokenPeer::TARGET_URL, $this->target_url);
        if ($this->isColumnModified(PaymentSecurityTokenPeer::PAYMENT_NAME)) $criteria->add(PaymentSecurityTokenPeer::PAYMENT_NAME, $this->payment_name);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(PaymentSecurityTokenPeer::DATABASE_NAME);
        $criteria->add(PaymentSecurityTokenPeer::HASH, $this->hash);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->getHash();
    }

    /**
     * Generic method to set the primary key (hash column).
     *
     * @param  string $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setHash($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getHash();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of PaymentSecurityToken (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setDetails($this->getDetails());
        $copyObj->setAfterUrl($this->getAfterUrl());
        $copyObj->setTargetUrl($this->getTargetUrl());
        $copyObj->setPaymentName($this->getPaymentName());
        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setHash(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return PaymentSecurityToken Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Returns a peer instance associated with this om.
     *
     * Since Peer classes are not to have any instance attributes, this method returns the
     * same instance for all member of this class. The method could therefore
     * be static, but this would prevent one from overriding the behavior.
     *
     * @return PaymentSecurityTokenPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new PaymentSecurityTokenPeer();
        }

        return self::$peer;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->hash = null;
        $this->details = null;
        $this->details_unserialized = null;
        $this->after_url = null;
        $this->target_url = null;
        $this->payment_name = null;
        $this->alreadyInSave = false;
        $this->alreadyInValidation = false;
        $this->alreadyInClearAllReferencesDeep = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep && !$this->alreadyInClearAllReferencesDeep) {
            $this->alreadyInClearAllReferencesDeep = true;

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(PaymentSecurityTokenPeer::DEFAULT_STRING_FORMAT);
    }

    /**
     * return true is the object is in saving state
     *
     * @return boolean
     */
    public function isAlreadyInSave()
    {
        return $this->alreadyInSave;
    }

}
