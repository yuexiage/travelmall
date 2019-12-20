<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class tab{
    /**
     * @param $params
     * 标签缓存
     * key格式：stroke
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'tab';
        $tab = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_tabs')." WHERE uniacid = :uniacid and  is_del = :is_del and enabled = :enabled  ORDER BY displayorder ASC, id ASC ", array(
            ':uniacid'      => $_W['uniacid'],
            ':is_del'       => 0,
            ':enabled'      => 1,
        ), 'tab_id');
        cache_write($cache_key,$tab);
        return cache_load($cache_key);
    }
}

