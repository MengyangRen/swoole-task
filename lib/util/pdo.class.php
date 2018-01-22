<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *   常驻内存服务中基于Pdo连接数据库
 * @author  v.r
 * @package util.pdo 
 * 
 * 注: 辅助方法为私有方法 命名以下滑线开头
 * 
 */

class PdoDrive
{
    /**
     * @var \PDO
     */
    private $pdo;
    private $dbName;
    private $tableName;
    private $className;
    private $config;
    private $lastTime;
    private $lastSql;

    /**
     * Returns DB instance or create initial connection
     *
     * 
     * @param $config
     * @param null $className
     * entityDemo
     * <?php
     *    假设数据库有user表,表含有id(自增主键), username, password三个字段
     *    class UserEntity {
     *         const TABLE_NAME = 'user';  //对应的数据表名
     *         const PK_ID = 'id';         //主键id名
     *         public $id;                 //public属性与表字段一一对应
     *         public $username;
     *         public $password;
     *    }
     * @param $objInstance
     */
    public function __construct($config=null, $className = null, $dbName = null)
    {
   
        $this->config = $config;

        if(empty($this->config['pingtime'])) 
            $this->config['pingtime'] = 3600;
        
        if (!empty($className)) 
            $this->className = $className;

        if (empty($dbName)) 
            $this->dbName = $config['dbname'];
        else 
            $this->dbName = $dbName;

        $this->lastTime = time() + $this->config['pingtime'];
        $this->checkPing();
    }

    /**
     * 检查数据库连接
     * @param $config
     * @param null $className
     * @param null $dbName
     */
    public function checkPing()
    {
        if (empty($this->pdo)) {
            $this->pdo = $this->connect();
        } elseif (!empty($this->config['ping'])) {
            $this->ping();
        }
    }

    /**
     * 连接数据库
     * @param $config
     * @param null $className
     * @param null $dbName
     */
    public function connect() {

        if (IS_LONG_SERVER) 
            $persistent = 0;
        else 
            $persistent = empty($this->config['pconnect']) ? 0 : 1;
        return new \PDO($this->config['dsn'], $this->config['user'], $this->config['pass'], array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$this->config['charset']}';",
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_PERSISTENT => $persistent
        ));
    }

    /**
     * ping数据
     * @param $config
     * @param null $className
     * @param null $dbName
     */
    public function ping()
    {
        $now = time();
        if($this->lastTime < $now) {
            if (empty($this->pdo)) {
                $this->pdo = $this->connect();
            } else {
                try {
                    $status = $this->pdo->getAttribute(\PDO::ATTR_SERVER_INFO);
                } catch (\Exception $e) {
                    if ($e->getCode() == 'HY000') {
                        $this->pdo = $this->connect();
                    } else {
                        throw $e;
                    }
                }
            }
        }
        $this->lastTime = $now + $this->config['pingtime'];
        return $this->pdo;
    }

    /**
     *Close database connection address
     *
     * @param
     * @return null;
     */
    public function close()
    {
        if(empty($this->config['pconnect'])) {
            $this->pdo = null;
        }
    }
    public function getLastSql()
    {
        return $this->lastSql;
    }

    public function fetchBySql($sql, $mode=\PDO::FETCH_ASSOC)
    {
        $statement = $this->pdo->prepare($sql);
        $this->lastSql = $sql;
        $statement->execute();
        $statement->setFetchMode($mode);
        return $statement->fetchAll();
    }

    public function queryBySql($query)
    {
        $statement = $this->pdo->prepare($query);
        $this->lastSql = $query;
        $statement->execute();
        return $statement->rowCount();
    }

    public function fetchArray($_as = '',$where = '1', $params = null, $fields = '*', $orderBy = null, $limit = null)
    {
        if (empty($_as)) 
            throw new \Exception("Please enter the table name", 1);
        $query = "SELECT {$fields} FROM $_as WHERE {$where}";
        if ($orderBy) 
           $query .= " ORDER BY {$orderBy}";
        if ($limit) 
            $query .= " limit {$limit}";
        $statement = $this->pdo->prepare($query);
        $this->lastSql = $query;
        $statement->execute($params);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        return $statement->fetchAll();
    }

    public function getLibName()
    {
        return "`{$this->getDBName()}`.`{$this->getTableName()}`";
    }
}

/*$db = array(
    'zhiCloudCustoms'=> array(
        'dsn'=>'mysql:dbname=zhiCloudCustoms;host=172.18.10.168',
        'user'=>'guest',
        'pass'=>'guest123456',
        'pingtime'=>3600,
        'dbname'=>'zhiCloudCustoms',
        'pconnect'=>1,
        'charset'=>'utf8',
    ),
);
$sql ='SELECT id,uid,created FROM customs_feature LIMIT 0,10';
$model = Factory::getInstance($db['zhiCloudCustoms']);
$list = $model->fetchBySql($sql);
print_r($list);*/



