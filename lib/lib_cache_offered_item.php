<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */

class offered_item{
    /**
     * @param $params
     * 生成常规线路详情缓存
     * key格式：offered_item:123
     */
    function make_chache($params){
        global $_W;
        $cache_key  = 'offered_item'.SEP.implode(SEP,$params);
        $offered_id        = array_shift($params);
        if( empty($offered_id)){
            throw new Exception("线路id错误!");
        }

        //查询常规线路基础信息
        $spike_item = pdo_fetch("SELECT * FROM ".tablename("yuexiage_travelmall_offered")." WHERE offered_id = :offered_id",array(':offered_id'=>$offered_id));
        $spike_item['thumbs']       = iunserializer($spike_item['thumbs']);
        $spike_item['tabs']         = iunserializer($spike_item['tabs']);       //标签
        $spike_item['coupons']      = iunserializer($spike_item['coupons']);    //优惠券
        $spike_item['cashs']        = iunserializer($spike_item['cashs']);      //代金券
        $spike_item['trip']         = iunserializer($spike_item['trip']);       //去程航班
        $spike_item['return_trip']  = iunserializer($spike_item['return_trip']);//返程航班

        $spike_item['characteristic'] = htmlspecialchars_decode($spike_item['characteristic']);
        $spike_item['contain']      = htmlspecialchars_decode($spike_item['contain']);
        $spike_item['not_included'] = htmlspecialchars_decode($spike_item['not_included']);
        $spike_item['visa']         = htmlspecialchars_decode($spike_item['visa']);
        $spike_item['booked']       = htmlspecialchars_decode($spike_item['booked']);

        //获取日期
        $time = strtotime(date("Y-m-d"))+86400;
        $sql        = "SELECT FROM_UNIXTIME(start_date,'%m-%d') AS d,FROM_UNIXTIME(start_date,'%w') AS w,amount_id,adult_price,child_price FROM ".tablename('yuexiage_travelmall_offered_amount')." WHERE offered_id = :offered_id and uniacid = :uniacid and start_date >:time and stock > :stock and is_del = :is_del order by start_date limit 0,7";
        $dates      = pdo_fetchall($sql,array(':offered_id'=>$offered_id,':uniacid'=>$_W['uniacid'],':time'=>$time,':stock'=>0,':is_del'=> 0));
        // pdo_debug();
        $spike_item['date']         = is_array($dates) ? $dates : [];

        //判断收藏
        $spike_item['collection']   = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_collection')." WHERE  uniacid = :uniacid AND uid=:uid AND offered_id=:offered_id",array(':uniacid'=>$_W['uniacid'],':uid'=>$_W['member']['uid'],':offered_id'=>$offered_id));

        cache_write($cache_key,$spike_item);
        return cache_load($cache_key);
    }

}
