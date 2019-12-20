<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class country_all{
    /**
     * @param $params
     * 地区信息缓存 - 包含未上架国家
     * key格式：region
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'country_all';
        $countrys = pdo_fetchall("SELECT country_id,country_id as id,name,enabled FROM ".tablename('yuexiage_travelmall_country')." WHERE is_del = :is_del and uniacid = :uniacid  ORDER BY displayorder ASC, id ASC ",
            array(':uniacid'=>$_W['uniacid'],':is_del'=>0), 'country_id');

        cache_write($cache_key,$countrys);
        return cache_load($cache_key);
    }
}

