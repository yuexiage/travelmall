<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class module_filter{
    /**
     * @param $params
     * 样式缓存
     * key格式：pid:1:filter_id
     * pid 位置
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'module_filter'.SEP.implode(SEP,$params);
        $pid            = array_shift($params);
        if( empty($pid)){
            throw new Exception('信息不全',42);
        }
        $filter_id        = array_shift($params);
        if( empty($filter_id)){
            throw new Exception("key格式错误！");
        }

        $filter = pdo_fetch('SELECT * FROM ' . tablename('yuexiage_travelmall_module_filter') . ' WHERE uniacid = :uniacid and enabled = :enabled and pid=:pid and is_del = :is_del and filter_id = :filter_id', array(
            ':uniacid' => $_W['uniacid'],':pid'=>$pid,':is_del'=>0,':enabled'=>1,':filter_id'=>$filter_id));
        cache_write($cache_key,$filter);
        return cache_load($cache_key);
    }
}

