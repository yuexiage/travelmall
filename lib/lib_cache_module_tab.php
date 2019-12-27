<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class module_tab{
    /**
     * @param $params
     * 标签模块缓存
     * key格式：pid:1:tab_id
     * pid 位置
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'module_tab'.SEP.implode(SEP,$params);
        $pid            = array_shift($params);
        if( empty($pid)){
            throw new Exception('信息不全',42);
        }
        $tab_id         = array_shift($params);
        if( empty($tab_id)){
            throw new Exception("key格式错误！");
        }
        $tab = pdo_fetch('SELECT * FROM ' . tablename('yuexiage_travelmall_module_tab') . ' WHERE uniacid = :uniacid and enabled = :enabled and pid=:pid and is_del = :is_del and tab_id = :tab_id', array(
            ':uniacid' => $_W['uniacid'],':pid'=>$pid,':is_del'=>0,':enabled'=>1,':tab_id'=>$tab_id));
        cache_write($cache_key,$tab);
        return cache_load($cache_key);
    }
}

