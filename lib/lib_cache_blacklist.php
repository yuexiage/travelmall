<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class blacklist{
    /**
     * @param $params
     * 用户黑名单信息缓存
     * key格式：blacklist:1
     */
    function make_chache($params){
        global $_W;
        $cache_key  = 'blacklist'.SEP.implode(SEP,$params);
        $uid        = array_shift($params);
        if( empty($uid)){
            throw new Exception('信息不全',42);
        }

        //黑名单
        $blacklist = pdo_fetch('SELECT* FROM ' . tablename('yuexiage_travelmall_blacklist') . ' WHERE uniacid = :uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'],':uid'=>$uid));
        cache_write($cache_key,$blacklist);
        return cache_load($cache_key);
    }
}

