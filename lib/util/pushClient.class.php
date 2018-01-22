<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *  推送服务php客户端
 * @author v.r
 * @package         推送服务
 * @subpackage      protocol.class.php
 * 
 */
class PushClient 
{
     
    /**
     * 向所有客户端连接广播消息
     *
     * @param string $msg   消息
     * @return void
     */
  public static function sendToAll($msg = NULL) {
    try {
      GatewayClient\Gateway::$registerAddress = PushClient::$registerAddress;
      GatewayClient\Gateway::sendToAll(json_encode($msg));
    } catch (\Exception $e) {
            throw new \Exception($e->getMessage(),$e->getCode());
    }
  }
   
    /**
     * 向所有 uid 发送
     *
     * @param int|string|array $uid
     * @param string           $msg
     */
  public static function sendToUid($uid = NULL, $msg = NULL) {
    try {
      GatewayClient\Gateway::$registerAddress = PushClient::$registerAddress;
      GatewayClient\Gateway::sendToUid($uid,json_encode($msg));
    } catch (\Exception $e) {
            throw new \Exception($e->getMessage(),$e->getCode());
    }  
  }

  /**
     * 向 group 发送
     *
     * @param int|string|array $group     组
     * @param string           $msg      消息
     * 
     */
  public static function sendToGroup($group = NULL,$msg = NULL) {
    try {
      GatewayClient\Gateway::$registerAddress = PushClient::$registerAddress;
      GatewayClient\Gateway::sendToGroup($group,json_encode($msg));
    } catch (\Exception $e) {
            throw new \Exception($e->getMessage(),$e->getCode());
    }  
  }

    /**
     * 获取与 uid 绑定的 client_id 列表
     *
     * @param string $uid
     * @return array
     */
  public static function getClientIdByUid($uid = NULL) {
    try {
      GatewayClient\Gateway::$registerAddress = PushClient::$registerAddress;
      GatewayClient\Gateway::sendToGroup($uid);
    } catch (\Exception $e) {
            throw new \Exception($e->getMessage(),$e->getCode());
    }  
  }

    /**
     * 
     * 加载gatway集群注册地址
     *
     * @param string $uid
     * @return array
     */
  public static function loadRegisterAddress() {
         PushClient::$registerAddress = '127.0.0.1:1238'; 
  } 
  
  /**
   * 获取gatway注册地址
   * @var string
   */
  public static $registerAddress;

}

/*try {
    
    PushClient::loadRegisterAddress();
    $data = array('_mucode'=>md5(time()),'uuid'=>'YCSIDCODE21','alias'=>'zhiCloudCloudDeskTopChangeApp');
 //   $data = array('type'=>'push','content'=>$cet);
    //PushClient::sendToAll(json_encode($data));
    PushClient::sendToUid('YCSIDCODE21',json_encode($data)); //发送个人  
  
} catch (\Exception $e) {
  var_dump($e->getMessage());
  var_dump($e->getCode());
}
*/
/***
 使用案例

 try {


  //发送到所有人
  PushClient::loadRegisterAddress();
  PushClient::sendToAll(json_encode(array('_mucode'=>md5(time()),
    'uuid'=>'all','alias'=>'zhiCloudCloudDeskTopChangeApp')));


  //发送给个人YCSIDCODE7
  PushClient::loadRegisterAddress();
  PushClient::sendToUid('YCSIDCODE7',
    json_encode(array('_mucode'=>md5(time()),'uuid'=>'YCSIDCODE7','alias'=>'zhiCloudCloudDeskTopChangeApp')));


 //发送到某个组 （省id.市id.公司id = 2345.2345.1234 ）
 
  PushClient::loadRegisterAddress();
  PushClient::sendToGroup('2345.2345.1234',
    json_encode(array('_mucode'=>md5(time()),'uuid'=>'group','alias'=>'zhiCloudCloudDeskTopChangeApp')));



} catch (Exception $e) {
  var_dump($e->getMessage());
  var_dump($e->getCode());
}
*/