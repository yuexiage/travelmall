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
    'country'           =>array('table'=>'ims_yuexiage_travelmall_country'),
    'country_all'       =>array('table'=>'ims_yuexiage_travelmall_country'),
    'city'              =>array('table'=>'ims_yuexiage_travelmall_city'),
    'city_all'          =>array('table'=>'ims_yuexiage_travelmall_city'),
    'spike_comment'     =>array('table'=>'ims_yuexiage_travelmall_comment'),
    'spike_order'       =>array('table'=>'ims_yuexiage_travelmall_orders'),
    'coupon_type'       =>array('table'=>'ims_yuexiage_travelmall_coupon'),     //优惠券
    'category'          =>array('table'=>'ims_yuexiage_travelmall_categorys'),  //分类
    'stroke'            =>array('table'=>'ims_yuexiage_travelmall_stroke'),     //行程
    'stroke_item'       =>array('table'=>'ims_yuexiage_travelmall_stroke'),     //行程
    'tab_item'          =>array('table'=>'ims_yuexiage_travelmall_tabs'),       //标签

    /** 模块缓存**/
    'module_slideshow'  =>array('table'=>'ims_yuexiage_travelmall_module_slideshow'),   //行程
    'module_style'      =>array('table'=>'ims_yuexiage_travelmall_module_style'),       //样式
    'module_filter'     =>array('table'=>'ims_yuexiage_travelmall_module_filter'),      //过滤模块
    'module_theme'      =>array('table'=>'ims_yuexiage_travelmall_theme'),              //主题
    'module_category'   =>array('table'=>'ims_yuexiage_travelmall_module_category'),    //分类
    'module_tab'        =>array('table'=>'ims_yuexiage_travelmall_module_tab'),         //标签
    'module_recommend'  =>array('table'=>'ims_yuexiage_travelmall_module_recommend'),   //推荐

    'flight'            =>array('table'=>'ims_yuexiage_travelmall_flight'),
    'tab'               =>array('table'=>'ims_yuexiage_travelmall_tabs'),      //过滤模块
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
    'ims_yuexiage_travelmall_stroke'=>array(
        'stroke'                =>array(1=>'offered_id'),
        'stroke_item'           =>array(1=>'stroke_id'),
    ),
    'ims_yuexiage_travelmall_tabs'=>array(
        'tab_item'              =>array(1=>'tab_id'),
    ),
    'ims_yuexiage_travelmall_tabs'=>array(
        'tab'                   =>array(),
    ),

    'ims_yuexiage_travelmall_module_slideshow'=>array(
        'module_slideshow'     =>array(1=>'pid',2=>'slideshow_id'),
    ),
    'ims_yuexiage_travelmall_module_style'=>array(
        'module_style'         =>array(1=>'pid',2=>'style_id'),
    ),
    'ims_yuexiage_travelmall_module_filter'=>array(
        'module_filter'        =>array(1=>'pid',2=>'filter_id'),
    ),
    'ims_yuexiage_travelmall_module_theme'=>array(
        'module_theme'         =>array(1=>'pid',2=>'theme_id'),
    ),
    'ims_yuexiage_travelmall_module_category'=>array(
        'module_category'      =>array(1=>'pid',2=>'category_id'),
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

);