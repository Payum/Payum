<?php

namespace Payum\Core\Bridge\Propel\Model\om;

use \Criteria;
use \Exception;
use \ModelCriteria;
use \PDO;
use \Propel;
use \PropelException;
use \PropelObjectCollection;
use \PropelPDO;
use Payum\Core\Bridge\Propel\Model\PaymentSecurityToken;
use Payum\Core\Bridge\Propel\Model\PaymentSecurityTokenPeer;
use Payum\Core\Bridge\Propel\Model\PaymentSecurityTokenQuery;

/**
 * @method PaymentSecurityTokenQuery orderByHash($order = Criteria::ASC) Order by the hash column
 * @method PaymentSecurityTokenQuery orderByDetails($order = Criteria::ASC) Order by the details column
 * @method PaymentSecurityTokenQuery orderByAfterUrl($order = Criteria::ASC) Order by the after_url column
 * @method PaymentSecurityTokenQuery orderByTargetUrl($order = Criteria::ASC) Order by the target_url column
 * @method PaymentSecurityTokenQuery orderByPaymentName($order = Criteria::ASC) Order by the payment_name column
 *
 * @method PaymentSecurityTokenQuery groupByHash() Group by the hash column
 * @method PaymentSecurityTokenQuery groupByDetails() Group by the details column
 * @method PaymentSecurityTokenQuery groupByAfterUrl() Group by the after_url column
 * @method PaymentSecurityTokenQuery groupByTargetUrl() Group by the target_url column
 * @method PaymentSecurityTokenQuery groupByPaymentName() Group by the payment_name column
 *
 * @method PaymentSecurityTokenQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method PaymentSecurityTokenQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method PaymentSecurityTokenQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method PaymentSecurityToken findOne(PropelPDO $con = null) Return the first PaymentSecurityToken matching the query
 * @method PaymentSecurityToken findOneOrCreate(PropelPDO $con = null) Return the first PaymentSecurityToken matching the query, or a new PaymentSecurityToken object populated from the query conditions when no match is found
 *
 * @method PaymentSecurityToken findOneByDetails( $details) Return the first PaymentSecurityToken filtered by the details column
 * @method PaymentSecurityToken findOneByAfterUrl(string $after_url) Return the first PaymentSecurityToken filtered by the after_url column
 * @method PaymentSecurityToken findOneByTargetUrl(string $target_url) Return the first PaymentSecurityToken filtered by the target_url column
 * @method PaymentSecurityToken findOneByPaymentName(string $payment_name) Return the first PaymentSecurityToken filtered by the payment_name column
 *
 * @method array findByHash(string $hash) Return PaymentSecurityToken objects filtered by the hash column
 * @method array findByDetails( $details) Return PaymentSecurityToken objects filtered by the details column
 * @method array findByAfterUrl(string $after_url) Return PaymentSecurityToken objects filtered by the after_url column
 * @method array findByTargetUrl(string $target_url) Return PaymentSecurityToken objects filtered by the target_url column
 * @method array findByPaymentName(string $payment_name) Return PaymentSecurityToken objects filtered by the payment_name column
 */
abstract class BasePaymentSecurityTokenQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BasePaymentSecurityTokenQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = null, $modelName = null, $modelAlias = null)
    {
        if (null === $dbName) {
            $dbName = 'default';
        }
        if (null === $modelName) {
            $modelName = 'Payum\\Core\\Bridge\\Propel\\Model\\PaymentSecurityToken';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new PaymentSecurityTokenQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   PaymentSecurityTokenQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return PaymentSecurityTokenQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof PaymentSecurityTokenQuery) {
            return $criteria;
        }
        $query = new PaymentSecurityTokenQuery(null, null, $modelAlias);

        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return   PaymentSecurityToken|PaymentSecurityToken[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = PaymentSecurityTokenPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(PaymentSecurityTokenPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Alias of findPk to use instance pooling
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 PaymentSecurityToken A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByHash($key, $con = null)
     {
        return $this->findPk($key, $con);
     }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 PaymentSecurityToken A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `hash`, `details`, `after_url`, `target_url`, `payment_name` FROM `PaymentSecurityToken` WHERE `hash` = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $obj = new PaymentSecurityToken();
            $obj->hydrate($row);
            PaymentSecurityTokenPeer::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return PaymentSecurityToken|PaymentSecurityToken[]|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($stmt);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return PropelObjectCollection|PaymentSecurityToken[]|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection($this->getDbName(), Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($stmt);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return PaymentSecurityTokenQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PaymentSecurityTokenPeer::HASH, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return PaymentSecurityTokenQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PaymentSecurityTokenPeer::HASH, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the hash column
     *
     * Example usage:
     * <code>
     * $query->filterByHash('fooValue');   // WHERE hash = 'fooValue'
     * $query->filterByHash('%fooValue%'); // WHERE hash LIKE '%fooValue%'
     * </code>
     *
     * @param     string $hash The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PaymentSecurityTokenQuery The current query, for fluid interface
     */
    public function filterByHash($hash = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($hash)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $hash)) {
                $hash = str_replace('*', '%', $hash);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaymentSecurityTokenPeer::HASH, $hash, $comparison);
    }

    /**
     * Filter the query on the details column
     *
     * @param     mixed $details The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PaymentSecurityTokenQuery The current query, for fluid interface
     */
    public function filterByDetails($details = null, $comparison = null)
    {
        if (is_object($details)) {
            $details = serialize($details);
        }

        return $this->addUsingAlias(PaymentSecurityTokenPeer::DETAILS, $details, $comparison);
    }

    /**
     * Filter the query on the after_url column
     *
     * Example usage:
     * <code>
     * $query->filterByAfterUrl('fooValue');   // WHERE after_url = 'fooValue'
     * $query->filterByAfterUrl('%fooValue%'); // WHERE after_url LIKE '%fooValue%'
     * </code>
     *
     * @param     string $afterUrl The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PaymentSecurityTokenQuery The current query, for fluid interface
     */
    public function filterByAfterUrl($afterUrl = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($afterUrl)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $afterUrl)) {
                $afterUrl = str_replace('*', '%', $afterUrl);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaymentSecurityTokenPeer::AFTER_URL, $afterUrl, $comparison);
    }

    /**
     * Filter the query on the target_url column
     *
     * Example usage:
     * <code>
     * $query->filterByTargetUrl('fooValue');   // WHERE target_url = 'fooValue'
     * $query->filterByTargetUrl('%fooValue%'); // WHERE target_url LIKE '%fooValue%'
     * </code>
     *
     * @param     string $targetUrl The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PaymentSecurityTokenQuery The current query, for fluid interface
     */
    public function filterByTargetUrl($targetUrl = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($targetUrl)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $targetUrl)) {
                $targetUrl = str_replace('*', '%', $targetUrl);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaymentSecurityTokenPeer::TARGET_URL, $targetUrl, $comparison);
    }

    /**
     * Filter the query on the payment_name column
     *
     * Example usage:
     * <code>
     * $query->filterByPaymentName('fooValue');   // WHERE payment_name = 'fooValue'
     * $query->filterByPaymentName('%fooValue%'); // WHERE payment_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $paymentName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return PaymentSecurityTokenQuery The current query, for fluid interface
     */
    public function filterByPaymentName($paymentName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($paymentName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $paymentName)) {
                $paymentName = str_replace('*', '%', $paymentName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PaymentSecurityTokenPeer::PAYMENT_NAME, $paymentName, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   PaymentSecurityToken $paymentSecurityToken Object to remove from the list of results
     *
     * @return PaymentSecurityTokenQuery The current query, for fluid interface
     */
    public function prune($paymentSecurityToken = null)
    {
        if ($paymentSecurityToken) {
            $this->addUsingAlias(PaymentSecurityTokenPeer::HASH, $paymentSecurityToken->getHash(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

}
