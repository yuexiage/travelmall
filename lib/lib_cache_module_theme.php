<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class module_theme{
    /**
     * @param $params
     * 主题模块缓存
     * key格式：pid:1:theme_id
     * pid 位置
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'module_theme'.SEP.implode(SEP,$params);
        $pid            = array_shift($params);
        if( empty($pid)){
            throw new Exception('信息不全','42');
        }
        $theme_id       = array_shift($params);
        if( empty($theme_id)){
            throw new Exception("key格式错误！");
        }
        $style = pdo_fetch('SELECT * FROM ' . tablename('yuexiage_travelmall_module_theme') . ' WHERE uniacid = :uniacid and enabled = :enabled and pid=:pid and is_del = :is_del and theme_id = :theme_id', array(
            ':uniacid' => $_W['uniacid'],':pid'=>$pid,':is_del'=>0,':enabled'=>1,':theme_id'=>$theme_id));
        cache_write($cache_key,$style);
        return cache_load($cache_key);
    }
}

