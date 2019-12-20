<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class spike_order{
    /**
     * @param $params
     * 当前用户是否有当前线路订单信息缓存
     * key格式：spike_order:offered_id:uid
     */
    function make_chache($params){
        global $_W;
        $cache_key  = 'spike_order'.SEP.implode(SEP,$params);
        //普通线路id
        $offered_id        = array_shift($params);
        if( empty($offered_id)){
            throw new Exception("key格式错误！");
        }
        $uid        = array_shift($params);
        if( empty($uid)){
            throw new Exception("key格式错误！");
        }
        //订单信息
        $order      = pdo_fetch('SELECT id FROM ' . tablename('yuexiage_travelmall_orders') . ' WHERE uniacid = :uniacid and uid=:uid and goods_id=:goods_id order by createtime DESC', array(':uniacid' => $_W['uniacid'],':uid'=>$uid,':goods_id'=>$offered_id));
        cache_write($cache_key,$order);
        return cache_load($cache_key);
    }
}

