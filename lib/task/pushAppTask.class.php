<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *   推送应用服务
 * @author  v.r
 * @package task.services 
 * 
 * 注: 辅助方法为私有方法 命名以下滑线开头
 * 
 */
class pushAppTask implements Itask
{
	

	/**
     *  运行
	 * @return mixed 
     */
	public static function run($srv, $task_id,$from_id,$data,$callback) {
		try {
		
			$data = Util::jsonDecode($data);
			$p = $data['num'] + 1;

			$condition = (array)$data['condition'];
			$customsFeatureModel = Factory::model('customsFeatureModel');
			$asyzeQueue = $customsFeatureModel->getSendUserMapByCondition($condition,$p);
			if (empty($asyzeQueue)) 
				return $callback(array('data'=>array('success'=>$len,'fail'=>0),'type'=>$data['type'],'_uq'=>$data['_uq']));

			
			$len = count($asyzeQueue);
	        $num =  0;
			
			do {

			    try {
					$item = $asyzeQueue[$num];	
					$uuid = 'YCSIDCODE'.$item['uid'];
					PushClient::loadRegisterAddress();
					PushClient::sendToUid($uuid,Util::__makeJsonProtocol($data['_uq'],$uuid,ZHI_CLOUD_PUSH_APP_ALIAS)); 
			//		$p  = "用户UID(".$uuid.")"."已推送".PHP_EOL;
			//		Util::_writeLog($p);

				} catch (\Exception $e) {
					$log  = "推送服务异常->应用异常：Code(".$e->getCode().")".PHP_EOL;
					$log .="异常信息:".$e->getMessage().PHP_EOL;   
					Util::_writeLog($log);
				}
			    $num++;
			} while ($len > $num);

			return $callback(
				array(
					 'data'=>array(
						'success'=>$len,
						'fail'=>0
					  ),
					 'type'=>$data['type'],
					 '_uq'=>$data['_uq']
				)
			);

		} catch (\Exception $e) {
		    var_dump($e->getMessage());
		    var_dump($e->getCode());
		}
	}

    /**
     *  汇总
	 * @return mixed 
     */
	public static function Finish($srv, $task_id,$element,$data,$callback) {
	    $appPushStatisticModel = Factory::model('appPushStatisticModel');
	    $appPushStatisticModel->updateAppPushSendStatus($element['success'],$element['uq']);
	    return $callback($data);
	}
}