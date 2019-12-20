<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class country{
    /**
     * @param $params
     * 地区信息缓存 - 不包含未上架国家
     * key格式：region
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'country';
        $countrys = pdo_fetchall("SELECT country_id,country_id as id,name FROM ".tablename('yuexiage_travelmall_country')." WHERE is_del = :is_del and uniacid = :uniacid and enabled = :enabled ORDER BY displayorder ASC, id ASC ",
            array(':uniacid'=>$_W['uniacid'],':is_del'=>0, ':enabled'=>1), 'country_id');

        cache_write($cache_key,$countrys);
        return cache_load($cache_key);
    }
}

