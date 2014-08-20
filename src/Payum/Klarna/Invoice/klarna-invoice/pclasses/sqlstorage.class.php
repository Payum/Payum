<?php
/**
 * SQL Storage
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
 * SQL storage class for KlarnaPClass
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
class SQLStorage extends PCStorage
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
     * PDO DSN notation.
     *
     * @var string
     */
    protected $dsn;

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
     * PDO DB link resource.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * return the name of the storage type
     *
     * @return string
     */
    public function getName()
    {
        return "sql";
    }

    /**
     * Splits the URI for the following formats:<br>
     * user:passwd@addr/dbName.dbTable (assumes MySQL)<br>
     * user:password@pdo:dsn/dbName.dbTable<br>
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
        /* If you want to have some characters that would make the
            regexp too complex, you can use an array as input instead. */
        if (is_array($uri)) {
            $this->user = $uri['user'];
            $this->passwd = $uri['passwd'];
            $this->dsn = $uri['dsn'];
            $this->dbName = $uri['db'];
            $this->dbTable = $uri['table'];

            return array(
                $uri,
                $this->user,
                $this->passwd,
                $this->dsn,
                $this->dbName,
                $this->dbTable
            );
        }
        $pdo_rex
            = '/^([\w-]+):([\w-]+)@pdo:([\w.,:;\/ \\\t=\(\){}\*-]+)\/([\w-]+)'.
            '.([\w-]+)$/';
        $pcuri_rex
            = '/^([\w-]+):([\w-]+)@([\w\.-]+|[\w\.-]+:[\d]+|[\w\.-]+:'.
            '[\w\.\/-]+|:[\w\.\/-]+)\/([\w-]+).([\w-]+)$/';
        $arr = null;
        if (preg_match($pdo_rex, $uri, $arr) === 1) {
            /*
             * [0] => user:password@pdo:dsn/dbName.dbTable
             * [1] => user
             * [2] => passwd
             * [3] => dsn
             * [4] => dbName
             * [5] => dbTable
             */
            if (count($arr) != 6) {
                throw new Klarna_DatabaseException(
                    'URI is invalid! Missing field or invalid characters used!'
                );
            }

            $this->user = $arr[1];
            $this->passwd = $arr[2];
            $this->dsn = $arr[3];
            $this->dbName = $arr[4];
            $this->dbTable = $arr[5];
        } else if (preg_match($pcuri_rex, $uri, $arr) === 1) {
            //user:pass@127.0.0.1:3306/dbName.dbTable
            //user:pass@localhost:/tmp/mysql.sock/dbName.dbTable
            /*
             * [0] => user:passwd@addr/dbName.dbTable
             * [1] => user
             * [2] => passwd
             * [3] => addr
             * [4] => dbName
             * [5] => dbTable
             */
            if (count($arr) != 6) {
                throw new Klarna_DatabaseException(
                    'URI is invalid! Missing field or invalid characters used!'
                );
            }

            $this->user = $arr[1];
            $this->passwd = $arr[2];
            $this->addr = $arr[3];
            $this->port = 3306;
            if (preg_match(
                '/^([0-9.]+(:([0-9]+))?)$/', $this->addr, $tmp
            ) === 1
            ) {
                if (isset($tmp[3])) {
                    $this->port = $tmp[3];
                }
            }
            $this->dbName = $arr[4];
            $this->dbTable = $arr[5];
            $this->dsn = "mysql:host={$this->addr};port={$this->port};";
        } else {
            throw new Klarna_DatabaseException(
                'URI to SQL is not valid! ( user:passwd@addr/dbName.dbTable )'
            );
        }

        return $arr;
    }

    /**
     * Connects to the DB.
     *
     * @param string|array $uri pclass uri
     *
     * @throws Klarna_DatabaseException If connection could not be established.
     *
     * @deprecated Use the connect method instead.
     *
     * @return void
     */
    protected function getConnection($uri)
    {
        if ($this->pdo) {
            return; //Already have a connection
        }

        $this->splitURI($uri);

        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->passwd);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Klarna_DatabaseException('Failed to connect to database!');
        }
    }

    /**
     * Attempt to create the database and tables needed to store pclasses.
     *
     * @throws Klarna_DatabaseException If the table could not be created.
     *
     * @deprecated Use the create method instead
     *
     * @return void
     */
    protected function initDB()
    {
        $this->create();
    }

    /**
     * Connects to the DB.
     *
     * @param string|array $uri pclass uri
     *
     * @throws Klarna_DatabaseException If connection could not be established.
     *
     * @return void
     */
    public function connect($uri)
    {
        $this->getConnection($uri);
    }

    /**
     * Attempt to create the database and tables needed to store pclasses.
     *
     * @throws Klarna_DatabaseException If the table could not be created.
     *
     * @return void
     */
    public function create()
    {
        try {
            $this->pdo->exec("CREATE DATABASE `{$this->dbName}`");
        } catch (PDOException $e) {
            //SQLite does not support this...
        }

        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `{$this->dbName}`.`{$this->dbTable}` (
                `eid` int(10) NOT NULL,
                `id` int(10) NOT NULL,
                `type` int(4) NOT NULL,
                `description` varchar(255) NOT NULL,
                `months` int(11) NOT NULL,
                `interestrate` decimal(11,2) NOT NULL,
                `invoicefee` decimal(11,2) NOT NULL,
                `startfee` decimal(11,2) NOT NULL,
                `minamount` decimal(11,2) NOT NULL,
                `country` int(11) NOT NULL,
                `expire` int(11) NOT NULL
            );
SQL;
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            throw new Klarna_DatabaseException(
                'Table non-existant, failed to create it!'
            );
        }
    }

    /**
     * Loads the PClasses.
     *
     * @param string|array $uri pclass uri
     *
     * @return void
     * @throws KlarnaException
     */
    public function load($uri)
    {
        $this->connect($uri);
        $this->loadPClasses();
    }

    /**
     * Loads the PClasses.
     *
     * @return void
     * @throws KlarnaException
     */
    protected function loadPClasses()
    {
        try {
            $sth = $this->pdo->prepare(
                "SELECT * FROM `{$this->dbName}`.`{$this->dbTable}`",
                array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
            );
            $sth->execute();

            while ($row = $sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                $this->addPClass(new KlarnaPClass($row));
            }

            $sth->closeCursor();
            $sth = null;
        } catch (PDOException $e) {
            throw new Klarna_DatabaseException(
                'Could not fetch PClasses from database!'
            );
        }
    }

    /**
     * Saves the PClasses.
     *
     * @param string|array $uri pclass uri
     *
     * @return void
     * @throws KlarnaException
     */
    public function save($uri)
    {
        $this->connect($uri);
        //Only attempt to savePClasses if there are any.
        if (!is_array($this->pclasses)) {
            return;
        }
        if (count($this->pclasses) == 0) {
            return;
        }
        $this->savePClasses();
    }

    /**
     * Saves the PClasses.
     *
     * @return void
     * @throws KlarnaException
     */
    protected function savePClasses()
    {
        //Insert PClass SQL statement.
        $sql = <<<SQL
            INSERT INTO `{$this->dbName}`.`{$this->dbTable}`
                (`eid`, `id`, `type`, `description`, `months`, `interestrate`,
       	         `invoicefee`, `startfee`, `minamount`, `country`, `expire`)
           	VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
SQL;

        foreach ($this->pclasses as $pclasses) {
            foreach ($pclasses as $pclass) {
                try {
                    //Remove the pclass if it exists.
                    $sth = $this->pdo->prepare(
                        "DELETE FROM `{$this->dbName}`.`{$this->dbTable}`
                        WHERE `id` = ? AND `eid` = ?"
                    );
                    $sth->execute(
                        array(
                            $pclass->getId(), $pclass->getEid()
                        )
                    );

                    $sth->closeCursor();
                    $sth = null;
                } catch(PDOException $e) {
                    //Fail silently, we don't care if the removal failed.
                }

                try {
                    //Attempt to insert the PClass into the DB.
                    $sth = $this->pdo->prepare($sql);
                    $sth->execute(
                        array(
                            $pclass->getEid(),
                            $pclass->getId(),
                            $pclass->getType(),
                            $pclass->getDescription(),
                            $pclass->getMonths(),
                            $pclass->getInterestRate(),
                            $pclass->getInvoiceFee(),
                            $pclass->getStartFee(),
                            $pclass->getMinAmount(),
                            $pclass->getCountry(),
                            $pclass->getExpire()
                        )
                    );

                    $sth->closeCursor();
                    $sth = null;
                } catch(PDOException $e) {
                    throw new Klarna_DatabaseException(
                        'Failed to insert PClass into database!'
                    );
                }
            }
        }
    }

    /**
     * Drops the database table, to clear the PClasses.
     *
     * @param string|array $uri pclass uri
     *
     * @return void
     * @throws KlarnaException
     */
    public function clear($uri)
    {
        try {
            $this->connect($uri);
            unset($this->pclasses);
            $this->clearTable();
        } catch(Exception $e) {
            throw new Klarna_DatabaseException(
                $e->getMessage(), $e->getCode()
            );
        }
    }

    /**
     * Drops the database table, to clear the PClasses.
     *
     * @return void
     * @throws KlarnaException
     */
    protected function clearTable()
    {
        try {
            $this->pdo->exec("DELETE FROM `{$this->dbName}`.`{$this->dbTable}`");
        } catch (PDOException $e) {
            throw new Klarna_DatabaseException('Could not clear the database!');
        }
    }
}
