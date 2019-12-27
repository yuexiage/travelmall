<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class hotel{
    /**
     * @param $params
     * 酒店信息缓存
     * key格式：hotel:1
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'hotel'.SEP.implode(SEP,$params);
        $hotel_id      = array_shift($params);
        if( empty($hotel_id)){
            throw new Exception('信息不全',42);
        }
        $hotel     = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_hotel')." WHERE uniacid = :uniacid  and enabled = :enabled and hotel_id =:hotel_id and is_del = :is_del",
            array(':is_del'=>0,":uniacid"=>$_W['uniacid'],':enabled'=>1,':hotel_id'=>$hotel_id),'id');
        $hotel['thumbs']     = unserialize($hotel['thumbs']);
        $hotel['facilities'] = unserialize($hotel['facilities']);
        $hotel['service']    = unserialize($hotel['service']);
        cache_write($cache_key,$hotel);
        return cache_load($cache_key);
    }
}

