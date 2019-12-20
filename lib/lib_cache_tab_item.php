<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class tab_item{
    /**
     * @param $params
     * 标签详情缓存
     * key格式：stroke
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'tab_item'.SEP.implode(SEP,$params);
        $tab_id         = array_shift($params);
        if( empty($tab_id)){
            throw new Exception('信息不全','42');
        }

        $tab = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_tabs')." WHERE uniacid = :uniacid and tab_id = :tab_id and is_del = :is_del and enabled = :enabled  ORDER BY displayorder ASC, id ASC ", array(
            ':uniacid'      => $_W['uniacid'],
            ':tab_id'       => $tab_id,
            ':is_del'       => 0,
            ':enabled'      => 1,
        ));
        cache_write($cache_key,$tab);
        return cache_load($cache_key);
    }
}

