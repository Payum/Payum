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
use Payum\Core\Bridge\Propel\Model\NotificationDetails;
use Payum\Core\Bridge\Propel\Model\NotificationDetailsPeer;
use Payum\Core\Bridge\Propel\Model\NotificationDetailsQuery;

/**
 * @method NotificationDetailsQuery orderById($order = Criteria::ASC) Order by the id column
 * @method NotificationDetailsQuery orderByPaymentName($order = Criteria::ASC) Order by the payment_name column
 * @method NotificationDetailsQuery orderByDetails($order = Criteria::ASC) Order by the details column
 * @method NotificationDetailsQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method NotificationDetailsQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 *
 * @method NotificationDetailsQuery groupById() Group by the id column
 * @method NotificationDetailsQuery groupByPaymentName() Group by the payment_name column
 * @method NotificationDetailsQuery groupByDetails() Group by the details column
 * @method NotificationDetailsQuery groupByCreatedAt() Group by the created_at column
 * @method NotificationDetailsQuery groupByUpdatedAt() Group by the updated_at column
 *
 * @method NotificationDetailsQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method NotificationDetailsQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method NotificationDetailsQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method NotificationDetails findOne(PropelPDO $con = null) Return the first NotificationDetails matching the query
 * @method NotificationDetails findOneOrCreate(PropelPDO $con = null) Return the first NotificationDetails matching the query, or a new NotificationDetails object populated from the query conditions when no match is found
 *
 * @method NotificationDetails findOneByPaymentName(string $payment_name) Return the first NotificationDetails filtered by the payment_name column
 * @method NotificationDetails findOneByDetails(string $details) Return the first NotificationDetails filtered by the details column
 * @method NotificationDetails findOneByCreatedAt(string $created_at) Return the first NotificationDetails filtered by the created_at column
 * @method NotificationDetails findOneByUpdatedAt(string $updated_at) Return the first NotificationDetails filtered by the updated_at column
 *
 * @method array findById(int $id) Return NotificationDetails objects filtered by the id column
 * @method array findByPaymentName(string $payment_name) Return NotificationDetails objects filtered by the payment_name column
 * @method array findByDetails(string $details) Return NotificationDetails objects filtered by the details column
 * @method array findByCreatedAt(string $created_at) Return NotificationDetails objects filtered by the created_at column
 * @method array findByUpdatedAt(string $updated_at) Return NotificationDetails objects filtered by the updated_at column
 */
abstract class BaseNotificationDetailsQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseNotificationDetailsQuery object.
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
            $modelName = 'Payum\\Core\\Bridge\\Propel\\Model\\NotificationDetails';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new NotificationDetailsQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   NotificationDetailsQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return NotificationDetailsQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof NotificationDetailsQuery) {
            return $criteria;
        }
        $query = new NotificationDetailsQuery(null, null, $modelAlias);

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
     * @return   NotificationDetails|NotificationDetails[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = NotificationDetailsPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(NotificationDetailsPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 NotificationDetails A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneById($key, $con = null)
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
     * @return                 NotificationDetails A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id`, `payment_name`, `details`, `created_at`, `updated_at` FROM `PaymentNotificationDetails` WHERE `id` = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $obj = new NotificationDetails();
            $obj->hydrate($row);
            NotificationDetailsPeer::addInstanceToPool($obj, (string) $key);
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
     * @return NotificationDetails|NotificationDetails[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|NotificationDetails[]|mixed the list of results, formatted by the current formatter
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
     * @return NotificationDetailsQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(NotificationDetailsPeer::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return NotificationDetailsQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(NotificationDetailsPeer::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id >= 12
     * $query->filterById(array('max' => 12)); // WHERE id <= 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return NotificationDetailsQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(NotificationDetailsPeer::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(NotificationDetailsPeer::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(NotificationDetailsPeer::ID, $id, $comparison);
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
     * @return NotificationDetailsQuery The current query, for fluid interface
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

        return $this->addUsingAlias(NotificationDetailsPeer::PAYMENT_NAME, $paymentName, $comparison);
    }

    /**
     * Filter the query on the details column
     *
     * Example usage:
     * <code>
     * $query->filterByDetails('fooValue');   // WHERE details = 'fooValue'
     * $query->filterByDetails('%fooValue%'); // WHERE details LIKE '%fooValue%'
     * </code>
     *
     * @param     string $details The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return NotificationDetailsQuery The current query, for fluid interface
     */
    public function filterByDetails($details = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($details)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $details)) {
                $details = str_replace('*', '%', $details);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(NotificationDetailsPeer::DETAILS, $details, $comparison);
    }

    /**
     * Filter the query on the created_at column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedAt('2011-03-14'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt('now'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt(array('max' => 'yesterday')); // WHERE created_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $createdAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return NotificationDetailsQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(NotificationDetailsPeer::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(NotificationDetailsPeer::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(NotificationDetailsPeer::CREATED_AT, $createdAt, $comparison);
    }

    /**
     * Filter the query on the updated_at column
     *
     * Example usage:
     * <code>
     * $query->filterByUpdatedAt('2011-03-14'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt('now'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt(array('max' => 'yesterday')); // WHERE updated_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $updatedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return NotificationDetailsQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(NotificationDetailsPeer::UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(NotificationDetailsPeer::UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(NotificationDetailsPeer::UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   NotificationDetails $notificationDetails Object to remove from the list of results
     *
     * @return NotificationDetailsQuery The current query, for fluid interface
     */
    public function prune($notificationDetails = null)
    {
        if ($notificationDetails) {
            $this->addUsingAlias(NotificationDetailsPeer::ID, $notificationDetails->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    // timestampable behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     NotificationDetailsQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(NotificationDetailsPeer::UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     NotificationDetailsQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(NotificationDetailsPeer::UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return     NotificationDetailsQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(NotificationDetailsPeer::UPDATED_AT);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     NotificationDetailsQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(NotificationDetailsPeer::CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     NotificationDetailsQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(NotificationDetailsPeer::CREATED_AT);
    }

    /**
     * Order by create date asc
     *
     * @return     NotificationDetailsQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(NotificationDetailsPeer::CREATED_AT);
    }
}
