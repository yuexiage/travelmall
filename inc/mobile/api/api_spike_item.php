<?php
global $_GPC, $_W;
load()->func('tpl');
$outarr = array(
    'spike'      =>[],
    'shopname'  =>$this->settings['shopname'],
    'spike_end' =>$this->settings['spike_end']*60,
    'collection'=>'',
    'city'      =>'',
    'flight_tfid'=>'',
    'flight_bid'=>'',
    'comment'   =>'',
);

//获取常规线路基础信息缓存
$spike_item = com_load_cache('spike_item'.SEP.$_GPC['id']);
if($spike_item['upper_shelf'] && ($spike_item['lower_shelf'] < time()) ){
    $spike_item['end'] = 1;
    $update = array('enabled' => 0);
    //更新线路状态
    pdo_update('yuexiage_travelmall_offered', $update, array('id' => $_GPC['id']));
}
$outarr['spike'] = $spike_item;

//获取城市信息缓存
$outarr['city'] = com_load_cache('region'.SEP.'3');

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