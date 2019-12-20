<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class module_style{
    /**
     * @param $params
     * 样式模块缓存
     * key格式：pid:1:style_id
     * pid 位置
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'module_style'.SEP.implode(SEP,$params);
        $pid            = array_shift($params);
        if( empty($pid)){
            throw new Exception('信息不全','42');
        }
        $style_id        = array_shift($params);
        if( empty($style_id)){
            throw new Exception("key格式错误！");
        }
        $style = pdo_fetch('SELECT * FROM ' . tablename('yuexiage_travelmall_module_style') . ' WHERE uniacid = :uniacid and enabled = :enabled and pid=:pid and is_del = :is_del and style_id = :style_id', array(
            ':uniacid' => $_W['uniacid'],':pid'=>$pid,':is_del'=>0,':enabled'=>1,':style_id'=>$style_id));
        cache_write($cache_key,$style);
        return cache_load($cache_key);
    }
}

