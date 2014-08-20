<?php
/**
 * MySQL Storage
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
 * MySQL storage class for KlarnaPClass
 *
 * This class is an MySQL implementation of the PCStorage interface.<br>
 * Config field pcURI needs to match format:
 * user:passwd@addr:port/dbName.dbTable<br>
 * Port can be omitted.<br>
 *
 * <b>Acceptable characters</b>:<br>
 * Username: [A-Za-z0-9_]<br>
 * Password: [A-Za-z0-9_]<br>
 * Address:  [A-Za-z0-9_.]<br>
 * Port:     [0-9]<br>
 * DB name:  [A-Za-z0-9_]<br>
 * DB table: [A-Za-z0-9_]<br>
 *
 * To allow for more special characters, and to avoid having<br>
 * a regular expression that is too hard to understand, you can<br>
 * use an associative array:<br>
 * <code>
 * array(
 *   "user" => "myuser",
 *   "passwd" => "mypass",
 *   "dsn" => "localhost",
 *   "db" => "mydatabase",
 *   "table" => "mytable"
 * );
 * </code>
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      https://developers.klarna.com/
 */
class MySQLStorage extends PCStorage
{

    /**
     * Database name.
     *
     * @var string
     */
    protected $dbName;

    /**
     * Database table.
     *
     * @var string
     */
    protected $dbTable;

    /**
     * Database address.
     *
     * @var string
     */
    protected $addr;

    /**
     * Database username.
     *
     * @var string
     */
    protected $user;

    /**
     * Database password.
     *
     * @var string
     */
    protected $passwd;

    /**
     * MySQL DB link resource.
     *
     * @var resource
     */
    protected $link;

    /**
     * return the name of the storage type
     *
     * @return string
     */
    public function getName()
    {
        return "mysql";
    }

    /**
     * Establish a connection to the DB
     *
     * @throws Klarna_DatabaseException If a connection could not be made
     *
     * @return void
     */
    public function connect()
    {
        $this->link = mysql_connect($this->addr, $this->user, $this->passwd);
        if ($this->link === false) {
            throw new Klarna_DatabaseException(
                'Failed to connect to database! ('.mysql_error().')'
            );
        }
    }

    /**
     * Initialize the DB by creating the necessary database tables.
     *
     * @throws Klarna_DatabaseException If tables could not be created
     *
     * @return void
     */
    public function create()
    {
        if (!mysql_query(
            "CREATE DATABASE IF NOT EXISTS `{$this->dbName}`",
            $this->link
        )
        ) {
            throw new Klarna_DatabaseException(
                'Failed to create! ('.mysql_error().')'
            );
        }

        $create = mysql_query(
            "CREATE TABLE IF NOT EXISTS `{$this->dbName}`.`{$this->dbTable}` (
                `eid` int(10) unsigned NOT NULL,
                `id` int(10) unsigned NOT NULL,
                `type` tinyint(4) NOT NULL,
                `description` varchar(255) NOT NULL,
                `months` int(11) NOT NULL,
                `interestrate` decimal(11,2) NOT NULL,
                `invoicefee` decimal(11,2) NOT NULL,
                `startfee` decimal(11,2) NOT NULL,
                `minamount` decimal(11,2) NOT NULL,
                `country` int(11) NOT NULL,
                `expire` int(11) NOT NULL,
                KEY `id` (`id`)
            )", $this->link
        );

        if (!$create) {
            throw new Klarna_DatabaseException(
                'Table not existing, failed to create! ('.mysql_error().')'
            );
        }
    }

    /**
     * Splits the URI in format: user:passwd@addr/dbName.dbTable<br>
     *
     * To allow for more special characters, and to avoid having<br>
     * a regular expression that is too hard to understand, you can<br>
     * use an associative array:<br>
     * <code>
     * array(
     *   "user" => "myuser",
     *   "passwd" => "mypass",
     *   "dsn" => "localhost",
     *   "db" => "mydatabase",
     *   "table" => "mytable"
     * );
     * </code>
     *
     * @param string|array $uri Specified URI to database and table.
     *
     * @throws KlarnaException
     * @return void
     */
    protected function splitURI($uri)
    {
        if (is_array($uri)) {
            $this->user = $uri['user'];
            $this->passwd = $uri['passwd'];
            $this->addr = $uri['dsn'];
            $this->dbName = $uri['db'];
            $this->dbTable = $uri['table'];
        } else if (preg_match(
            '/^([\w-]+):([\w-]+)@([\w\.-]+|[\w\.-]+:[\d]+)\/([\w-]+).([\w-]+)$/',
            $uri,
            $arr
        ) === 1
        ) {
            /*
              [0] => user:passwd@addr/dbName.dbTable
              [1] => user
              [2] => passwd
              [3] => addr
              [4] => dbName
              [5] => dbTable
            */
            if (count($arr) != 6) {
                throw new Klarna_DatabaseException(
                    'URI is invalid! Missing field or invalid characters used!'
                );
            }

            $this->user = $arr[1];
            $this->passwd = $arr[2];
            $this->addr = $arr[3];
            $this->dbName = $arr[4];
            $this->dbTable = $arr[5];
        } else {
            throw new Klarna_DatabaseException(
                'URI to MySQL is not valid! ( user:passwd@addr/dbName.dbTable )'
            );
        }
    }

    /**
     * Load pclasses
     *
     * @param string $uri pclass uri
     *
     * @throws KlarnaException
     * @return void
     */
    public function load($uri)
    {
        $this->splitURI($uri);
        $this->connect();
        $result = mysql_query(
            "SELECT * FROM `{$this->dbName}`.`{$this->dbTable}`",
            $this->link
        );
        if ($result === false) {
            throw new Klarna_DatabaseException(
                'SELECT query failed! ('.mysql_error().')'
            );
        }
        while ($row = mysql_fetch_assoc($result)) {
            $this->addPClass(new KlarnaPClass($row));
        }
    }

    /**
     * Save pclasses to database
     *
     * @param string $uri pclass uri
     *
     * @throws KlarnaException
     * @return void
     */
    public function save($uri)
    {
        $this->splitURI($uri);

        $this->connect();
        if (!is_array($this->pclasses) || count($this->pclasses) == 0) {
            return;
        }

        foreach ($this->pclasses as $pclasses) {
            foreach ($pclasses as $pclass) {
                //Remove the pclass if it exists.
                mysql_query(
                    "DELETE FROM `{$this->dbName}`.`{$this->dbTable}`
                     WHERE `id` = '{$pclass->getId()}'
                     AND `eid` = '{$pclass->getEid()}'"
                );

                //Insert it again.
                $result = mysql_query(
                    "INSERT INTO `{$this->dbName}`.`{$this->dbTable}`
                       (`eid`,
                        `id`,
                        `type`,
                        `description`,
                        `months`,
                        `interestrate`,
                        `invoicefee`,
                        `startfee`,
                        `minamount`,
                        `country`,
                        `expire`
                        )
                     VALUES
                       ('{$pclass->getEid()}',
                        '{$pclass->getId()}',
                        '{$pclass->getType()}',
                        '{$pclass->getDescription()}',
                        '{$pclass->getMonths()}',
                        '{$pclass->getInterestRate()}',
                        '{$pclass->getInvoiceFee()}',
                        '{$pclass->getStartFee()}',
                        '{$pclass->getMinAmount()}',
                        '{$pclass->getCountry()}',
                        '{$pclass->getExpire()}')", $this->link
                );
                if ($result === false) {
                    throw new Klarna_DatabaseException(
                        'INSERT INTO query failed! ('.mysql_error().')'
                    );
                }
            }
        }
    }

    /**
     * Clear the pclasses
     *
     * @param string $uri pclass uri
     *
     * @throws KlarnaException
     * @return void
     */
    public function clear($uri)
    {
        try {
            $this->splitURI($uri);
            unset($this->pclasses);
            $this->connect();

            mysql_query(
                "DELETE FROM `{$this->dbName}`.`{$this->dbTable}`",
                $this->link
            );
        } catch(Exception $e) {
            throw new Klarna_DatabaseException(
                $e->getMessage(), $e->getCode()
            );
        }
    }
}
