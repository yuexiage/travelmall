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
     * 当前用户是否有当前线路评论信息
     * key格式：spike_comment:offered_id:uid
     */
    function make_chache($params){
        global $_W;
        $cache_key  = 'spike_comment'.SEP.implode(SEP,$params);
        //普通线路id
        $offered_id        = array_shift($params);
        if( empty($offered_id)){
            throw new Exception("key格式错误！");
        }
        $uid        = array_shift($params);
        if( empty($uid)){
            throw new Exception("key格式错误！");
        }
        //评论信息
        $comment = pdo_fetch('SELECT * FROM ' . tablename('yuexiage_travelmall_comment') . ' WHERE uniacid = :uniacid and uid=:uid and goods_id=:goods_id and type=1', array(':uniacid' => $_W['uniacid'],':uid'=>$uid,':goods_id'=>$offered_id));
        cache_write($cache_key,$comment);
        return cache_load($cache_key);
    }
}

