<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */
class coupon_type{
    /**
     * @param $params
     * 优惠券缓存
     * key格式：coupon_type
     */
    function make_chache($params){
        global $_W;
        $cache_key  = 'coupon_type'.SEP.implode(SEP,$params);
        $type       = array_shift($params);
        $coupons    = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE is_del = :is_del and uniacid = :uniacid and enabled = :enabled and type = :type ORDER BY displayorder ASC, id ASC ",
            array(':uniacid'=>$_W['uniacid'],':is_del'=>0, ':enabled'=>1,':type'=>$type),'coupon_id');
        cache_write($cache_key,$coupons);
        return cache_load($cache_key);
    }
}

