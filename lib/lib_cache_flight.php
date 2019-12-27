<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class flight{
    /**
     * @param $params
     * 普通线路航班信息缓存
     * key格式：flight:1
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'flight'.SEP.implode(SEP,$params);
        $flight_id      = array_shift($params);
        if( empty($flight_id)){
            throw new Exception('信息不全',42);
        }
        $flight     = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_flight')." WHERE uniacid = :uniacid  and enabled = :enabled and flight_id =:flight_id and is_del = :is_del",
            array(':is_del'=>0,":uniacid"=>$_W['uniacid'],':enabled'=>1,':flight_id'=>$flight_id),'id');
        cache_write($cache_key,$flight);
        return cache_load($cache_key);
    }
}

