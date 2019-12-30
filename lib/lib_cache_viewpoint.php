<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class viewpoint{
    /**
     * @param $params
     * 景点信息缓存
     * key格式：viewpoint:1
     */
    function make_chache($params){
        global $_W;
        $cache_key      = 'viewpoint'.SEP.implode(SEP,$params);
        $viewpoint_id      = array_shift($params);
        if( empty($viewpoint_id)){
            throw new Exception('信息不全',42);
        }
        $viewpoint     = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_viewpoint')." WHERE uniacid = :uniacid  and enabled = :enabled and viewpoint_id =:viewpoint_id and is_del = :is_del",
            array(':is_del'=>0,":uniacid"=>$_W['uniacid'],':enabled'=>1,':viewpoint_id'=>$viewpoint_id),'id');
        $viewpoint['thumbs']     = unserialize($viewpoint['thumbs']);
        cache_write($cache_key,$viewpoint);
        return cache_load($cache_key);
    }
}

