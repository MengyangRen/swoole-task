<?php

//是否长驻服务 (swoole模式)
define('IS_LONG_SERVER',1);
class Factory
{
    /**
     * @var Pdo
     */
    private static $instance = [];

    /**
     * db驱动工厂
     * @param null $config
     * @param null $className
     * @param null $dbName
     * @return Pdo
     * @throws \Exception
     */
    public static function DB($config=null, $className=null, $dbName=null)
    {

        if(empty(self::$instance[$config['dsn']])) 
            self::$instance[$config['dsn']] = new PdoDrive($config);
        else if(IS_LONG_SERVER)
            self::$instance[$config['dsn']]->ping();
        
        if ($className) 
           self::$instance[$config['dsn']]->setClassName($className);
        if ($dbName) 
            self::$instance[$config['dsn']]->setDBName($dbName);
        return self::$instance[$config['dsn']];
    }

    public static function removeDB($config) {
        unset(self::$instance[$config]);
    }

    /**
     * 模型工厂
     * @param null $config
     * @param null $className
     * @param null $dbName
     * @return Pdo
     * @throws \Exception
     */
    public static function model($className = NULL) {
        if (empty($className)) 
            throw new \Exception("Model name cannot be empty", 1);
        $_as = md5($className);

        if (!empty(self::$instance[$_as]))
            return self::$instance[$_as];
        
        $file = ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR.$className.'.class.php';

        if (!is_file($file)) 
            throw new \Exception("The model class does not exist. Check the file name", 1);

        require_once $file;
        self::$instance[$_as] = new $className;
        return  self::$instance[$_as];
    }
}