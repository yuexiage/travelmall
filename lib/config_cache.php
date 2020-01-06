<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/12/1
 * Time: 2:14
 */
//缓存创建规则
//$_W['cache_create'] = array(
//    'cache_key'=>array('影响的数据表'=>''),
//);
$_W['cache_create'] = array(
    'blacklist'         =>array('table'=>'ims_yuexiage_travelmall_blacklist'),
    'spike_comment'     =>array('table'=>'ims_yuexiage_travelmall_comment'),
    'spike_order'       =>array('table'=>'ims_yuexiage_travelmall_orders'),
    'coupon_type'       =>array('table'=>'ims_yuexiage_travelmall_coupon'),             //优惠券
    'category'          =>array('table'=>'ims_yuexiage_travelmall_categorys'),          //分类

    //可用
    'country'           =>array('table'=>'ims_yuexiage_travelmall_country'),            //有效国家
    'country_all'       =>array('table'=>'ims_yuexiage_travelmall_country'),            //包含下架国家
    'city'              =>array('table'=>'ims_yuexiage_travelmall_city'),               //有效城市
    'city_all'          =>array('table'=>'ims_yuexiage_travelmall_city'),               //包含下架城市
    'stroke'            =>array('table'=>'ims_yuexiage_travelmall_offered_stroke'),     //线路所有行程
    'stroke_item'       =>array('table'=>'ims_yuexiage_travelmall_offered_stroke'),     //线路某个行程
    'offered_item'      =>array('table'=>'ims_yuexiage_travelmall_offered'),            //行程
    'tab_item'          =>array('table'=>'ims_yuexiage_travelmall_tabs'),               //标签
    'tab'               =>array('table'=>'ims_yuexiage_travelmall_tabs'),               //所有标签
    'hotel'             =>array('table'=>'ims_yuexiage_travelmall_hotel'),              //酒店信息
    'viewpoint'         =>array('table'=>'ims_yuexiage_travelmall_viewpoint'),          //景点信息
    'flight'            =>array('table'=>'ims_yuexiage_travelmall_flight'),             //航班信息
    'theme'             =>array('table'=>'ims_yuexiage_travelmall_theme'),              //主题信息
    'amount_item'       =>array('table'=>'ims_yuexiage_travelmall_offered_amount'),     //线路价格信息

    /** 模块缓存**/
    'module_slideshow'  =>array('table'=>'ims_yuexiage_travelmall_module_slideshow'),   //行程
    'module_filter'     =>array('table'=>'ims_yuexiage_travelmall_module_filter'),      //过滤模块
    'module_tab'        =>array('table'=>'ims_yuexiage_travelmall_module_tab'),         //标签
    'module_recommend'  =>array('table'=>'ims_yuexiage_travelmall_module_recommend'),   //推荐
);

//缓存清除规则
$_W['cache_clear'] = array(
    'ims_yuexiage_travelmall_blacklist'=>array(
        'blacklist'             =>  array( 1=>'uid'),
    ),
    'ims_yuexiage_travelmall_country'=>array(
        'country'               => array(),
        'country_all'           => array(),
    ),
    'ims_yuexiage_travelmall_city'=>array(
        'city'                  => array(),
        'city_all'              => array(),
    ),
    'ims_yuexiage_travelmall_comment'=>array(
        'spike_comment'         =>array(1=>'uid',2=>'offered_id'),
    ),
    'ims_yuexiage_travelmall_orders'=>array(
        'spike_order'           =>array(1=>'uid',2=>'offered_id'),
    ),
    'ims_yuexiage_travelmall_coupon'=>array(
        'coupon_type'           =>array(1=>'type'),
    ),
    'ims_yuexiage_travelmall_categorys'=>array(
        'category'              =>array(),
    ),
    'ims_yuexiage_travelmall_offered_stroke'=>array(
        'stroke'                =>array(1=>'offered_id'),
        'stroke_item'           =>array(1=>'stroke_id'),
    ),
    'ims_yuexiage_travelmall_offered'=>array(
        'offered_item'           =>array(1=>'offered_id'),
    ),
    'ims_yuexiage_travelmall_tabs'=>array(
        'tab'                   =>array(),
        'tab_item'              =>array(1=>'tab_id'),
    ),

    'ims_yuexiage_travelmall_module_slideshow'=>array(
        'module_slideshow'     =>array(1=>'pid',2=>'slideshow_id'),
    ),
    'ims_yuexiage_travelmall_module_filter'=>array(
        'module_filter'        =>array(1=>'pid',2=>'filter_id'),
    ),
    'ims_yuexiage_travelmall_module_tab'=>array(
        'module_tab'           =>array(1=>'pid',2=>'tab_id'),
    ),
    'ims_yuexiage_travelmall_module_recommend'=>array(
        'module_recommend'     =>array(1=>'pid',2=>'recommend_id'),
    ),

    'ims_yuexiage_travelmall_flight'=>array(
        'flight'               =>  array( 1=>'flight_id'),
    ),
    'ims_yuexiage_travelmall_hotel'=>array(
        'hotel'                =>  array( 1=>'hotel_id'),
    ),
    'ims_yuexiage_travelmall_theme'=>array(
        'theme'                =>  array(),
    ),
    'ims_yuexiage_travelmall_viewpoint'=>array(
        'viewpoint'            =>  array( 1=>'viewpoint_id'),
    ),
    'ims_yuexiage_travelmall_offered_amount'=>array(
        'amount_item'          =>array(1=>'amount_id'),
    ),
);