<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *  定义任务的接口
 * 
 * @author  v.r
 * @copyright copyright http://my.oschina.net/u/1246814
 * 
 */
interface Itask
{
	public static function run($srv, $task_id, $from_id,$data,$callback); //任务
	public static function Finish($srv, $task_id, $element,$data,$callback); //汇总
}
