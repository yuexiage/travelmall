<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class theme{
    /**
     * @param $params
     * 主题缓存
     * key格式：theme
     */
    function make_chache($params){
        global $_W;
        $cache_key  = 'theme';
        $themes  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_theme')." WHERE is_del = :is_del and uniacid = :uniacid and enabled = :enabled ORDER BY displayorder ASC, id ASC ",
            array(':uniacid'=>$_W['uniacid'],':is_del'=>0, ':enabled'=>1),'theme_id');
        cache_write($cache_key,$themes);
        return cache_load($cache_key);
    }
}

