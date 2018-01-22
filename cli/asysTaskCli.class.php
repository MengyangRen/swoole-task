<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 基于swoole任务异步客户端
 * @author  v.r
 * @package Common
 * 
 */

class asysTaskCli 
{
    private $client;
    public static $ip = "127.0.0.1";
    public static $port = 9501;
    
    private $type = array(
        '1011'=>'pushApp',
        '2022'=>'pushMsg',
        '3033'=>'changeDesktopApp',
    );

    public function __construct() {
        if (empty($this->client)) {
           $this->client = new swoole_client(SWOOLE_SOCK_TCP);
        }
    }

    public function connect() {
        $fp = $this->client->connect(asysTaskCli::$ip,asysTaskCli::$port, 1); 
        if( !$fp ) {
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            return;
        }
    }
    
    private function send($data) {
        if (!$this->client->isConnected()) 
            throw new \Exception("Swoole server does not commected !");
        if (!is_string($data))
           $data = json_encode($data);
        return $this->client->send($data);

    }
    public function close() {
       return $this->client->close();
    }
    public function create($type,$condition,$tp,$_uq) {
        if (empty($this->type[$type]))
            throw new \Exception("Task type error");
        $data['type'] = $this->type[$type];
        $data['condition'] = $condition;
        $data['tp'] = $tp;
        $data['_uq'] = $_uq;
        $this->send($data);
        $this->close();
    }
    public function isConnected() {
        return $this->client->isConnected();
    }
}

try {
    $asysTaskCli = new asysTaskCli;
    $asysTaskCli->connect();
    $asysTaskCli->create(4044,array('pro_id'=>'3393','city_id'=>'3394'),3,'127a07a8ff74281a9bc6bcc85dbdc93a');
} catch (Exception $e) {
    print $e->getCode();
    print $e->getMessage();
}