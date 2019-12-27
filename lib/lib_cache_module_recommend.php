<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class module_recommend{
    /**
     * @param $params
     * 推荐模块缓存
     * key格式：pid:1:recommend_id
     * pid 位置
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'module_recommend'.SEP.implode(SEP,$params);
        $pid            = array_shift($params);
        if( empty($pid)){
            throw new Exception('信息不全',42);
        }
        $recommend_id   = array_shift($params);
        if( empty($recommend_id)){
            throw new Exception("key格式错误！");
        }

        $recommend = pdo_fetch('SELECT * FROM ' . tablename('yuexiage_travelmall_module_recommend') . ' WHERE uniacid = :uniacid and enabled = :enabled and pid=:pid and is_del = :is_del and recommend_id = :recommend_id', array(
            ':uniacid' => $_W['uniacid'],':pid'=>$pid,':is_del'=>0,':enabled'=>1,':recommend_id'=>$recommend_id));
        cache_write($cache_key,$recommend);
        return cache_load($cache_key);
    }
}

