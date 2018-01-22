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


class customsFeatureModel 
{
	private $table = 'customs_feature';
	private $model = NULL;
	private $useDb = 'zhiCloudCustoms';

	public function __construct() {
		$this->model = 
		Factory::DB(SERVER_CONFIG::$dbs[$this->useDb]);
	}

	/**
     * 
     * 通过条件获取用户集
     * @param   mixed $increment default true, else full crawler
     *  getWriteQueueFailedItems
     */
	public function getSendUserMapByCondition($condition = NULL,$p = NULL) {
		var_dump($condition);
        $ps = ($p-1) * SERVER_CONFIG::$num;  
        $limit = $ps.','.SERVER_CONFIG::$num;
        $sql = "SELECT uid FROM ".$this->table;
        if (!empty($condition)) 
        	$sql .= " WHERE ".$this->makeSqlWhere($condition);
        $sql .= " LIMIT {$limit}";

        print $sql;
        $list = $this->model->fetchBySql($sql);
        return $list;
	}
	public function  makeSqlWhere($condition) {
		$sql = '';
		$len = count($condition);
		$num = 0;
		foreach ($condition as $key => $value) {
			$num +=1;
			$sql.= " {$key} = {$value}" ;
			if ($len != $num) 
				$sql .=" AND ";
		}
		return $sql;
	}	
}