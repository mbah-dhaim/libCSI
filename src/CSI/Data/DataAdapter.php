<?php
/**
 * Simple Mysql/MariaDB query builder components
 *
 * @author Mahmud A Hakim
 * @copyright 2017 Mahmud A Hakim
 * @package CSI
 * @category Data
 * @version 1.0.2 Add Last Insert Id
 */
namespace CSI\Data;

use Exception;

/**
 * <pre>
 * Simple database adapter
 * Supported drivers:
 * * Mysql/MariaDB (default)
 * * Firebird
 * * Oracle
 * </pre>
 */
final class DataAdapter
{

    /**
     *
     * @var array
     */
    private $settings = array();

    /**
     *
     * @var string
     */
    private $dsn = '';

    /**
     *
     * @var string $driver
     */
    private $driver = 'MYSQL';

    /**
     *
     * @var \PDO
     */
    private $pdo = null;

    /**
     *
     * @var \PDOStatement
     */
    private $sth = null;

    /**
     *
     * @var \CSI\Data\Sql
     */
    private $sql = null;

    /**
     *
     * @var DataAdapter
     */
    private static $instance;

    /**
     * Last Insert ID
     *
     * @var string
     */
    private $lastInsertId = "";

    /**
     * ctor
     *
     * @param array $config
     *            konfigurasi yang digunakan untuk menyambung ke dalam database
     * @throws \Exception
     */
    public function __construct($config = array())
    {
        // make default
        if (! isset($config['dbdriver'])) {
            $config['dbdriver'] = 'mysql';
        }
        if (! isset($config['dbserver'])) {
            $config['dbserver'] = 'localhost';
        }
        if (! isset($config['dbname'])) {
            $config['dbname'] = 'test';
        }
        if (! isset($config['dbuser'])) {
            $config['dbuser'] = 'root';
        }
        if (! isset($config['dbpass'])) {
            $config['dbpass'] = '';
        }
        $dsn = "";
        switch ($config["dbdriver"]) {
            case 'mysql':
                $dsn = $config["dbdriver"] . ":host=" . $config["dbserver"] . ";dbname=" . $config["dbname"];
                break;
            case 'firebird':
                $dsn = $config["dbdriver"] . ":dbname=" . $config["dbserver"] . ":" . $config["dbname"];
                break;
            case 'oci':
                $dsn = $config["dbdriver"] . ":dbname=//" . $config["dbserver"] . (empty($config["dbport"]) ? "" : ":" . $config["dbport"]) . "/" . $config["dbname"];
                break;
            default:
                throw new Exception("Unsupported driver");
        }
        $this->settings = $config;
        $this->dsn = $dsn;
        $this->driver = strtoupper($config["dbdriver"]);
        if (! isset(self::$instance))
            self::$instance = $this;
    }

    /**
     * get default instance;
     *
     * @return \CSI\Data\DataAdapter
     */
    public static function getInstace()
    {
        return self::$instance;
    }

    /**
     * Menyambung ke database
     *
     * @return \CSI\Data\DataAdapter
     */
    public function connect()
    {
        $this->pdo = new \PDO($this->dsn, $this->settings["dbuser"], $this->settings["dbpass"], array(
            \PDO::ATTR_PERSISTENT => true
        ));
        // set raising error attribute
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        // lower/upper case field casing
        $this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, true);
        if (! empty($this->settings['fieldcasing'])) {
            if ($this->settings['fieldcasing'] == 1) {
                $this->pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
            } elseif ($this->settings['fieldcasing'] == 2) {
                $this->pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_UPPER);
            }
        }
        if ($this->driver == "OCI") {
            $sql = "ALTER SESSION SET NLS_DATE_FORMAT='yyyy-mm-dd hh24:mi:ss'";
            $this->pdo->exec($sql);
        }
        return $this;
    }

    /**
     * Get pdo object atau null
     *
     * @return \PDO object
     */
    public function pdo()
    {
        return $this->pdo;
    }

    /**
     * Cek apakah database tersambung
     *
     * @return boolean
     */
    public function connected()
    {
        return ! is_null($this->pdo);
    }

    /**
     * Close PDO Connection
     *
     * @return \CSI\Data\DataAdapter
     */
    public function close()
    {
        if (is_null($this->pdo)) {
            return $this;
        }
        $this->pdo = null;
        return $this;
    }

    /**
     * Menyiapkan untuk eksekusi sebuah sql query
     *
     * @param \CSI\Data\Sql $sql
     * @return NULL|\CSI\Data\DataAdapter
     */
    public function prepare(Sql $sql)
    {
        if (! $this->connected()) {
            return null;
        }
        if (! $sql) {
            return null;
        }
        $this->sql = $sql;
        $this->sth = $this->pdo->prepare($sql->text());
        if (! $this->sth) {
            return null;
        }
        $this->sql = $sql;
        return $this;
    }

    /**
     * shortcut untuk count data, sql query harus 'SELECT count(* atau field) from query'
     *
     * @return number
     */
    public function count()
    {
        $result = 0;
        $data = $this->sth->fetchColumn();
        if ($data !== FALSE) {
            $result = intval($data);
        }
        return $result;
    }

    /**
     * memuat next data dari sebuah query
     *
     * @return mixed
     */
    public function fetch()
    {
        if (! $this->sth->execute($this->sql->params())) {
            return null;
        }
        return $this->sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * memuat semua data data sebuah query
     *
     * @return mixed
     */
    public function fetchAll()
    {
        if (! $this->sth->execute($this->sql->params())) {
            return null;
        }
        return $this->sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * memuat data selanjutnya dari sebuah query dan mengembalikan dalam bentuk object
     *
     * @param string $classname
     *            nama object (optional)
     * @param array $ctor_args
     *            parameter konstruksi object (optional)
     * @return object
     */
    public function fetchObject($classname = null, $ctor_args = array())
    {
        if (! $this->sth->execute($this->sql->params())) {
            return null;
        }
        if ($classname) {
            return $this->sth->fetchObject($classname, $ctor_args);
        } else {
            return $this->sth->fetchObject();
        }
    }

    /**
     * memuat semua data dari sebuah query dan mengembalikan dalam bentuk list object
     *
     * @param string $classname
     *            nama object (optional)
     * @param array $ctor_args
     *            parameter konstruksi object (optional)
     * @return object
     */
    public function fetchObjects($classname = null, $ctor_args = array())
    {
        if (! $this->sth->execute($this->sql->params())) {
            return null;
        }
        if ($classname) {
            return $this->sth->fetchAll(\PDO::FETCH_CLASS, $classname, $ctor_args);
        } else {
            return $this->sth->fetchAll(\PDO::FETCH_CLASS);
        }
    }

    /**
     * memuat data scalar (field pertama) dari sebuah sql select query
     */
    public function fetchOne()
    {
        if (! $this->sth->execute($this->sql->params())) {
            return null;
        }
        return $this->sth->fetchColumn();
    }

    /**
     * memuat data selanjutnya dari hasil eksekusi fetch
     *
     * @return NULL|mixed
     */
    public function next()
    {
        if (! $this->sth)
            return null;
        return $this->sth->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * memuat data selanjutnya dari hasil eksekusi fetchObject
     *
     * @param string $classname
     * @param array $ctor_args
     * @return NULL|mixed
     */
    public function nextObject($classname = null, $ctor_args = array())
    {
        if (! $this->sth)
            return null;
        if ($classname) {
            return $this->sth->fetchObject($classname, $ctor_args);
        } else {
            return $this->sth->fetchObject();
        }
    }

    /**
     * Eksekusi data (insert/update/delete)
     *
     * @param \CSI\Data\Sql $sql
     * @param boolean $withTransaction
     *            default true
     * @throws \Exception
     * @return NULL|\CSI\Data\DataAdapter
     */
    public function execute(\CSI\Data\Sql $sql, $withTransaction = true)
    {
        if (! $this->connected()) {
            return null;
        }
        if (! $sql) {
            return null;
        }
        $this->sth = $this->pdo->prepare($sql->text());
        if (! $this->sth) {
            return null;
        }
        try {
            if ($withTransaction)
                $this->pdo->beginTransaction();
            if ($sql->isBatch()) {
                $params = $sql->params();
                for ($i = 0; $i < count($params); $i ++) {
                    if (! $this->sth->execute($params[$i])) {
                        throw new \Exception('Execute failed');
                    }
                }
            } else {
                if (! $this->sth->execute($sql->params())) {
                    throw new \Exception('Execute failed');
                }
            }
            $this->lastInsertId = $this->pdo->lastInsertId();
            if ($withTransaction)
                $this->pdo->commit();
        } catch (\Exception $e) {
            if ($withTransaction)
                $this->pdo->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * execute prepared statemen
     *
     * @param boolean $withTransaction
     * @throws \Exception
     * @return NULL|\CSI\Data\DataAdapter
     */
    public function executePrepared($withTransaction = false)
    {
        if (! $this->connected()) {
            return null;
        }
        try {
            if ($withTransaction)
                $this->pdo->beginTransaction();
            if ($this->sql->isBatch()) {
                $params = $this->sql->params();
                for ($i = 0; $i < count($params); $i ++) {
                    if (! $this->sth->execute($params[$i])) {
                        throw new \Exception('Execute failed');
                    }
                }
            } else {
                if (! $this->sth->execute($this->sql->params())) {
                    throw new \Exception('Execute failed');
                }
            }
            if ($withTransaction)
                $this->pdo->commit();
        } catch (\Exception $e) {
            if ($withTransaction)
                $this->pdo->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Memuat semua data dari sebuah tabel, gunakan untuk tabel referensi yang sedikit datanya
     *
     * @param string $tableName
     * @return mixed
     */
    public function fetchTable($tableName)
    {
        $sql = new \CSI\Data\Sql();
        $sql->addText("SELECT * FROM $tableName");
        return $this->prepare($sql)
            ->fetchObjects();
    }

    /**
     * Melihat sql query yang terakhir dieksekusi
     *
     * @return string
     */
    public function sql()
    {
        if ($this->sql) {
            return $this->sql->text();
        } else {
            return null;
        }
    }

    /**
     * membuat response untuk jqGrid
     *
     * @param \CSI\Data\Sql $sqlCount
     * @param \CSI\Data\Sql $sql
     * @param array $gridparams
     * @return \CSI\CSIResponse
     */
    public function buildjqGridResponse(\CSI\Data\Sql $sqlCount, \CSI\Data\Sql $sql, array $gridparams)
    {
        $theparams = $sql->params();
        if ($theparams) {
            foreach ($theparams as $key => $value) {
                $sqlCount->addParam($value, $key);
            }
        }
        $page = isset($gridparams['page']) ? $gridparams['page'] : 1;
        $rows = isset($gridparams['rows']) ? $gridparams['rows'] : 10;
        $sidx = isset($gridparams['sidx']) ? $gridparams['sidx'] : '';
        $sord = isset($gridparams['sord']) ? $gridparams['sord'] : '';
        if (empty($page)) {
            $page = 1;
        }
        if (empty($rows)) {
            $rows = 10;
        }
        if ($page < 1) {
            $page = 1;
        }
        if ($rows < 1) {
            $rows = 10;
        }
        $response = new \CSI\CSIResponse();
        $records = $this->prepare($sqlCount)
            ->count();
        $total = ceil($records / $rows);
        if ($page > $total && $total > 0) {
            $page = $total;
        }
        if (! empty($sidx)) {
            $sql->addText("order by $sidx $sord");
        }
        $skip = $page * $rows - $rows;
        $sql->addText("limit $skip, $rows");
        $data = $this->prepare($sql)
            ->fetchAll();
        $response->page = (integer) $page;
        $response->total = $total;
        $response->records = $records;
        $response->rows = $data;
        return $response;
    }

    /**
     * Clear internal SQL parameter
     *
     * @return \CSI\Data\DataAdapter
     */
    public function clearSqlParams()
    {
        $this->sql->clearParams();
        return $this;
    }

    /**
     * Add Sql Parameter (not bound)
     *
     * @param mixed $value
     * @return \CSI\Data\DataAdapter
     */
    public function addSqlParams($value)
    {
        $this->sql->addParameter($value);
        return $this;
    }

    /**
     * serialize
     */
    public function __sleep()
    {
        return $this->settings;
    }

    /**
     * unserialize
     *
     * @return \CSI\Data\DataAdapter
     */
    public function __wakeup()
    {
        return $this->connect();
    }

    /**
     * Get Last Inserted Id
     * 
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }
}
?>