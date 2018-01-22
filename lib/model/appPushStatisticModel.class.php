<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *   所用数据库相关模型
 * @author  v.r
 * @package task.services 
 * 
 * 注: 辅助方法为私有方法 命名以下滑线开头
 * 
 */


class appPushStatisticModel 
{
	private $table = 'app_push_statistic';
	private $model = NULL;
	private $useDb = 'zhiCloudCommon';

	public function __construct() {
		$this->model = 
		Factory::DB(SERVER_CONFIG::$dbs[$this->useDb]);
	}
    
	/**
     * 
     * 更新用户发送应用的状态
     * @param   mixed $increment default true, else full crawler
     *  getWriteQueueFailedItems
     *  
     */
    public function updateAppPushSendStatus($success = NULL,$uq = NULL) {
    	$sql = "UPDATE ".$this->table." SET push_num ={$success},is_push = 1";
    	$sql.= " WHERE mucode = '{$uq}'";
        $list = $this->model->queryBySql($sql);
        return $list;
    }
    
}
