<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class city_all{
    /**
     * @param $params
     * 地区信息缓存
     * key格式：region
     */
    function make_chache($params){
        global $_W;
        $cache_key= 'city_all';
        $citys    = pdo_fetchall("SELECT city_id,city_id as id,name,pcate FROM ".tablename('yuexiage_travelmall_city')." WHERE is_del = :is_del and uniacid = :uniacid  ORDER BY displayorder ASC, id ASC ",
            array(':uniacid'=>$_W['uniacid'],':is_del'=>0),'city_id');
        cache_write($cache_key,$citys);
        return cache_load($cache_key);
    }
}

