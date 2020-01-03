<?php
//线路详情
global $_GPC, $_W;
try{
load()->func('tpl');
$outarr = array(
    'offered'   =>[],
    'shopname'  =>$this->settings['shopname'],
    'spike_end' =>$this->settings['spike_end']*60,
    'collection'=>'',
    'city'      =>'',
    'comment'   =>'',
);

//获取常规线路基础信息缓存
$spike_item = com_load_cache(array(
    'cache_key'  =>'offered_item',
    'offered_id' =>$_GPC['id'],
));

//判断线路结果
if(empty($spike_item)){
    throw new Exception("数据错误!",42);
}

if($spike_item['upper_shelf'] && ($spike_item['lower_shelf'] < time()) ){
    $update = array('enabled' => 0);
    //更新线路状态
    update('yuexiage_travelmall_offered', $update, array('offered_id' => $_GPC['id']));
    $spike_item['enabled'] = 0;
}

//获取机票信息
$country = com_load_cache(array(
    'cache_key'  =>'country',
));
$city = com_load_cache(array(
    'cache_key'  =>'city',
));
//去程机票
if(!empty($spike_item['trip'])){
    foreach ($spike_item['trip'] as $flight_id => &$flight){
        $flight = com_load_cache(array(
            'cache_key'  =>'flight',
            'flight_id'  => $flight_id,
        ));
        //出发国家
        $flight['departure_country'] = $country[$flight['departure_country']]['name'];
        //出发城市
        $flight['departure_city']    = $city[$flight['departure_city']]['name'];
        //停留国家
        $flight['stop_country']      = $country[$flight['stop_country']]['name'];
        //停留城市
        $flight['stop_city']         = $city[$flight['stop_city']]['name'];
        //抵达国家
        $flight['arrival_country']   = $country[$flight['arrival_country']]['name'];
        //抵达城市
        $flight['arrival_city']      = $city[$flight['arrival_city']]['name'];
        //出发时间
        $flight['departure_time']    = date("H:i",$flight['departure_time']);
        //抵达时间
        $flight['arrival_time']      = date("H:i",$flight['arrival_time']);
        //飞行时间
        $flight['flight_duration']   = intval($flight['flight_duration']/60).'小时'.($flight['flight_duration']%60).'分钟';
    }
}
//返程机票
if(!empty($spike_item['return_trip'])){
    foreach ($spike_item['return_trip'] as $flight_id => &$flight){
        $flight = com_load_cache(array(
            'cache_key'  =>'flight',
            'flight_id'  => $flight_id,
        ));
        //出发国家
        $flight['departure_country'] = $country[$flight['departure_country']]['name'];
        //出发城市
        $flight['departure_city']    = $city[$flight['departure_city']]['name'];
        //停留国家
        $flight['stop_country']      = $country[$flight['stop_country']]['name'];
        //停留城市
        $flight['stop_city']         = $city[$flight['stop_city']]['name'];
        //抵达国家
        $flight['arrival_country']   = $country[$flight['arrival_country']]['name'];
        //抵达城市
        $flight['arrival_city']      = $city[$flight['arrival_city']]['name'];
        //出发时间
        $flight['departure_time']    = date("H:i",$flight['departure_time']);
        //抵达时间
        $flight['arrival_time']      = date("H:i",$flight['arrival_time']);
        //飞行时间
        $flight['flight_duration']   = intval($flight['flight_duration']/60).'小时'.($flight['flight_duration']%60).'分钟';
    }
}

//行程
$strokes = com_load_cache(array(
    'cache_key'  =>'stroke',
    'offered_id' =>$_GPC['id'],
));

//获取行程
foreach ($strokes as $key => &$stroke) {
    //住宿
    $stroke['stay']      = iunserializer($stroke['stay']);
    foreach ($stroke['stay'] as $k => &$value) {
        //查找酒店信息
        $value = com_load_cache(array(
            'cache_key'  =>'hotel',
            'hotel_id'   =>$k,
        ));
        $value['thumbs']    =iunserializer($value['thumbs']);
        $value['facilities']=iunserializer($value['facilities']);
        $value['service']   =iunserializer($value['service']);
    }
    //景点
    $stroke['viewpoint'] = iunserializer($stroke['viewpoint']);
    foreach ($stroke['viewpoint'] as $k => &$value) {
        $value = com_load_cache(array(
            'cache_key'     =>'viewpoint',
            'viewpoint_id'  =>$k,
        ));
        $value['thumbs'] = iunserializer($value['thumbs']);
    }
}
$spike_item['strokes'] = array_values($strokes);




























$outarr['offered']  = $spike_item;
//var_export(get_first_pinyin('啊那里'));exit;
//获取城市信息缓存
$outarr['city']     = $city;

/*
//获取去程航班
if ($spike_item['trip']>0){
    $outarr['flight_tfid'] = com_load_cache('flight'.SEP.$spike_item['trip']);
}
//获取返程航班
if ($spike_item['bidy']>0){
    $outarr['flight_bid'] = com_load_cache('flight'.SEP.$spike_item['return_trip']);
}

//判断是否有权发表评论
//黑名单
$blacklist  = com_load_cache('blacklist'.SEP.$_W['member']['uid']);
$outarr['comment']    = 1;
if($blacklist['comment']){
    //加入黑名单，所以禁止评论
    $outarr['comment']= 0;
}
*/
//是否有订单，有订单才能评论
/*$order      = com_load_cache('spike_order'.SEP.$_GPC['id'].SEP.$_W['member']['uid']);
if(!$order){
    $outarr['comment'] = 0;
}else{
    //是否已经评论过，只能评论一次
    $comment = com_load_cache('spike_comment'.SEP.$_GPC['id'].SEP.$_W['member']['uid']);
    if($comment){
        $outarr['comment'] = 0;
    }
}*/
echo json($outarr);
} catch (Exception $e) {
    echo json('',$e->getCode(),$e->getMessage());
}