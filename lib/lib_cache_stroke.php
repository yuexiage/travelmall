<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class stroke{
    /**
     * @param $params
     * 行程缓存
     * key格式：stroke
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'stroke'.SEP.implode(SEP,$params);
        $offered_id     = array_shift($params);
        if( empty($offered_id)){
            throw new Exception('信息不全',42);
        }

        $strokes = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_offered_stroke')." WHERE uniacid = :uniacid and offered_id = :offered_id and is_del = :is_del  ORDER BY displayorder ASC, id ASC ", array(
            ':uniacid'      => $_W['uniacid'],
            ':offered_id'   => $offered_id,
            ':is_del'       => 0,
        ), 'stroke_id');
        foreach ($strokes as &$_stroke) {
            $_stroke['stay']      = iunserializer($_stroke['stay']);
            $_stroke['viewpoint'] = iunserializer($_stroke['viewpoint']);
        }
        cache_write($cache_key,$strokes);
        return cache_load($cache_key);
    }
}

