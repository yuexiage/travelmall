<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */

class amount_item{
    /**
     * @param $params
     * 生成常规线路详情缓存
     * key格式：offered_item:123
     */
    function make_chache($params){
        global $_W;
        $cache_key  = 'amount_item'.SEP.implode(SEP,$params);
        $amount_id  = array_shift($params);
        if( empty($amount_id)){
            throw new Exception("线路价格id错误!");
        }

        //查询常规线路基础信息
        $amount = pdo_fetch("SELECT * FROM ".tablename("yuexiage_travelmall_offered_amount")." WHERE amount_id = :amount_id and uniacid = :uniacid and is_del = :is_del",array(
            ':amount_id'=>$amount_id,':uniacid'=>$_W['uniacid'],':is_del'=> 0));
        cache_write($cache_key,$amount);
        return cache_load($cache_key);
    }

}
