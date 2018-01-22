<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 基于swoole多进程任务服务端
 * 
 * @author  v.r
 * @package Common
 * 
 */

if(!defined('ASYS_TASK_LIB_PATH'))
    define('ASYS_TASK_LIB_PATH', dirname(__FILE__));

require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'util.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'gateWay.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'pushClient.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'pdo.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'interface'.DIRECTORY_SEPARATOR.'Itask.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'Factory.class.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'hashTable'.DIRECTORY_SEPARATOR.'Iterator.php';
require_once ASYS_TASK_LIB_PATH.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'hashTable'.DIRECTORY_SEPARATOR.'Set.php';


date_default_timezone_set('Asia/Shanghai');
spl_autoload_register("Util::_loadOfClassPhp"); 

/**
 * 服务管理
 */
class serviceManage 
{
	
    /**
     * 启动 
     * @return mixed $data default array, else Exception 
     */
	public static function start() {
		return false;
	}  
    
    /**
     * 重载 
     * @return mixed $data default array, else Exception 
     */
	public static function reload() {
		return false;
	}

    /**
     * 停止 
     * @return mixed $data default array, else Exception 
     */
	public static function stop() {
		return false;
	}

	/**
     * 重新启动 
     * @return mixed $data default array, else Exception 
     */
	public static function restart() {
		return false;
	}

    /**
     * 重载 
     * @return mixed $data default array, else Exception 
     */
	public static function help() {
 		$help=<<<'EOF'
NAME
      run.php - manage daemons
SYNOPSIS
      run.php command [options]
          Manage multi process daemons.

WORKFLOWS
      help [command]
      Show this help, or workflow help for command.
      restart
      Stop, then start the standard daemon loadout.
      start
      Start the standard configured collection of Phabricator daemons. This
      is appropriate for most installs. Use phd launch to customize which
      daemons are launched.
      stop
      Stop all running daemons, or specific daemons identified by PIDs. Use
      run.php status to find PIDs.
      reload
      Gracefully restart daemon processes in-place to pick up changes to
      source. This will not disrupt running jobs. This is an advanced
      workflow; most publishing should use run.php reload
EOF;
   print $help;

	}

}


/**
 * 任务服务
 */
class Worker {

  /**
   * 开始
   * @return mixed $data default array, else Exception 
   */
  public static function run($srv, $fd, $from_id, $data,&$hashTable) {
      if ($hashTable->get($data['_uq'])) 
          return false;

        
      $hashTable->set($data['_uq'],
         array(
            'num'=>0,
            'len'=>$data['tp'],
            'success'=>0,
            'fail'=>0,
            'uq'=>$data['_uq'],
         )
      );

      if (empty($data['tp'])) 
          Util::_writelog("任务分配参数为空".PHP_EOL);

      for ($i = 0;$i < $data['tp']; $i++) {
          $data['num'] = $i;
          $srv->task(Util::jsonEncode($data));
      }
  }

  /**
   * 任务 
   * @return mixed $data default array, else Exception 
   */
  public static function onTask($srv, $task_id, $from_id, $data,&$hashTable) {
      $class = Util::getTaskClass(Util::jsonDecode($data)['type']);
      return $class::run($srv,$task_id,$from_id,$data,function($data)use(&$hashTable) {
          $element = $hashTable->get($data['_uq']);
          $element['num'] += 1;
          $element['success'] += $data['data']['success'];
          $element['fail'] += $data['data']['fail'];
          $hashTable->set($data['_uq'],$element);
          return Util::jsonEncode($data);
      });
  } 



  /**
   * 任务汇总 
   * @return mixed $data default array, else Exception 
   */
  public static function onFinish($srv, $task_id, $data,&$hashTable) {
      $class = Util::getTaskClass(Util::jsonDecode($data)['type']);
      $element = $hashTable->get(Util::jsonDecode($data)['_uq']);

      $msg = '';
      $msg .='任务数'.$element['num'].'总数'.$element['len'].PHP_EOL;
      $msg .='数据:'.PHP_EOL;  
      Util::_writelog($msg);

      if ($element['num'] == $element['len']) {
          return $class::Finish($srv,$task_id,$element,$data,function($data)use(&$hashTable) {
              $_uq = Util::jsonDecode($data)['_uq'];
              $element = $hashTable->get($_uq);
              $hashTable->del($_uq);
          });
      } 

  }


}

/**
 * 任务服务
 */
class asysTaskSrv 
{
   
    /**
     * 服务点
     * @var 
     */
    private $serivce;

    /**
     * 任务点
     * @var 
     */
    private $worker;


    public $hashTable;

    /**
     * 任务服务初始化 
     * @return mixed $data default array, else Exception 
     */
    public function __construct() {
  	  $this->serivce = new swoole_server("0.0.0.0", 9501);
  	  $this->worker = new Worker;
      $this->hashTable = new swoole_table(1024);

      $this->hashTable->column('num', swoole_table::TYPE_INT, 4);       
      $this->hashTable->column('len', swoole_table::TYPE_INT, 4);
      $this->hashTable->column('success',swoole_table::TYPE_INT, 4);
      $this->hashTable->column('fail',swoole_table::TYPE_INT, 4);
      $this->hashTable->column('uq',swoole_table::TYPE_STRING, 33);
      
      $this->hashTable->create();

    	$this->serivce->set(SERVER_CONFIG::$set);
	    $this->serivce->on('Start',array($this,'onStart'));//swoole启动主进程主线程回调
      $this->serivce->on('Shutdown',array($this,'onShutdown'));//服务关闭回调
      $this->serivce->on("Connect",array($this,'onConnect'));       //新连接进入回调
      $this->serivce->on("Receive",array($this,'onReceive'));       //接收数据回调
      $this->serivce->on("Close",array($this,'onClose'));           //客户端关闭回调
      $this->serivce->on("Task",array($this,'onTask'));     //task进程回调
      $this->serivce->on("Finish",array($this,'onFinish')); //进程投递的任务在task_worker中完成时回调 exit("服务已经在运行!");
      
      $this->serivce->start();

    }

    /**
     * 服务启动 
     * @return mixed $data default array, else Exception 
     */
  	public function onStart($srv = NULL) {
      $msg = '任务服务启动...'.PHP_EOL;
      Util::_writePid(SERVER_CONFIG::$set['master_pid'],$srv->master_pid);
      Util::_writePid(SERVER_CONFIG::$set['manager_pid'],$srv->manager_pid);
      Util::_writelog($msg);
  	}

    /**
     * 客户端连接 
     * @return mixed $data default array, else Exception 
     */
    public function onConnect($srv, $fd, $from_id){
      $msg = '客户端连接成功'.PHP_EOL;
      $msg .= "fd:$fd,from_id:$from_id".PHP_EOL;
      Util::_writelog($msg);
    }

    
    /**
     * 接受消息
     * @param  obj $srv      swoole对象
     * @param  resource $fd  客户端连接
     * @param  int  $from_id 不同进程的id(workerid)
     * @param  string  $data 数据
     * @return string
     *
     */
  	public function onReceive($srv, $fd, $from_id, $data) {
      return Worker::run($srv,$fd, $from_id,Util::jsonDecode($data),$this->hashTable);
  	}

    /**
     * 任务 
     * @return mixed $data default array, else Exception 
     */
    public function onTask($srv, $task_id, $from_id, $data) {
    		return Worker::onTask($srv, $task_id, $from_id,$data,$this->hashTable);
    }

    /**
     * 汇总 

     * @return mixed $data default array, else Exception 
     */
    public function onFinish($srv, $task_id, $data) {
        return Worker::onFinish($srv, $task_id,$data,$this->hashTable);
    }

    /**
     * 服务关闭 
     * @return mixed $data default array, else Exception 
     */
    public function onShutdown(){
    	  $msg = '任务服务关闭成功'.PHP_EOL;
    	  $msg .= "fd:$fd,from_id:$from_id".PHP_EOL;
        Util::_writelog($msg);
    }

    /**
     * 连接关闭 
     * @return mixed $data default array, else Exception 
     */
    public function onClose($serv, $fd, $reactorId){
    	$msg = '客户端'.$fd.'关闭成功'.PHP_EOL;
      Util::_writelog($msg);
    }

    /**
     * 检查命令 
     * @return mixed $data default array, else Exception 
     */
    private function command(){
    	global $argv;
    	$param  = $argv[1];
    	if (empty($param)) 
    	    serviceManage::help();
      $master_pid = Util::_readPid(SERVER_CONFIG::$set['master_pid']);
	    switch($param){
  			case "reload":
  			        $msg = 'Swoole Reload 完成!';
  			        exec("kill -USR1 $master_pid");
  			        Util::_writelog($msg);
  			    exit;
  			    break;
  			case "shutdown":
  			    	$msg = 'Swoole Shutdown 完成!';
  					exec("kill -15 $master_pid");
  					Util::_writelog($msg);
  			    exit;
  			    break;
		  }
    }
}
$asysTaskSrv = new asysTaskSrv();