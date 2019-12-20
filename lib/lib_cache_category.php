<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class category{
    /**
     * @param $params
     * 分类缓存
     * key格式：category
     */
    function make_chache($params){
        global $_W;
        $cache_key  = 'category';
        $categorys  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_categorys')." WHERE is_del = :is_del and uniacid = :uniacid and enabled = :enabled ORDER BY displayorder ASC, id ASC ",
            array(':uniacid'=>$_W['uniacid'],':is_del'=>0, ':enabled'=>1),'category_id');
        cache_write($cache_key,$categorys);
        return cache_load($cache_key);
    }
}

