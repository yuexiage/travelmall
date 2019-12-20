<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class stroke_item{
    /**
     * @param $params
     * 行程详情缓存
     * key格式：stroke
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'stroke'.SEP.implode(SEP,$params);
        $stroke_id      = array_shift($params);
        if( empty($stroke_id)){
            throw new Exception('信息不全','42');
        }

        $stroke = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_stroke')." WHERE uniacid = :uniacid and stroke_id = :stroke_id and is_del = :is_del  ORDER BY displayorder ASC, id ASC ", array(
            ':uniacid'      => $_W['uniacid'],
            ':stroke_id'    => $stroke_id,
            ':is_del'       => 0,
        ), 'stroke_id');
        $stroke['stay']      = iunserializer($stroke['stay']);
        $stroke['viewpoint'] = iunserializer($stroke['viewpoint']);
        cache_write($cache_key,$stroke);
        return cache_load($cache_key);
    }
}

