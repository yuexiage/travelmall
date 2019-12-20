<?php
global $_GPC, $_W;

$scratchcard = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_scratchcard')." WHERE  enabled=1 and uniacid = {$_W['uniacid']}");
if($scratchcard['follow']){
    //如果必须关注，验证关注
    checkauth();
}
$gifts=array('1'=>'one','2'=>'two','3'=>'three','4'=>'four','5'=>'five','6'=>'six');
foreach ($gifts as $gift) {
   if($scratchcard['c_type_'.$gift]=='2' || $scratchcard['c_type_'.$gift]=='4'){
    $scratchcard['c_coupon_'.$gift] = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE  enabled=1 and uniacid = {$_W['uniacid']} and id={$scratchcard['c_name_'.$gift]}");
    }
}


//每人最多获奖次数
$award_times  = $scratchcard['award_times'];
//每人最多抽奖次数
$number_times = $scratchcard['number_times'];
//每人每天最多抽奖次数
$most_num_times = $scratchcard['most_num_times'];

//查询已参加次数
$uid = $_W['member']['uid'];
$winner = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_scratchcard_winner')." WHERE  from_user={$uid} and uniacid = {$_W['uniacid']}");


//已经参加次数
$number_times_uid = count($winner);

foreach ($winner as $key => $value) {
    if($value['status']>0){
        //获奖次数
        $award_times_uid += 1;
        $winners[]=$winner[$key];
    }
    if(date("Y-m-d",$value['createtime'])==date("Y-m-d")){
        //今日抽奖次数
        $most_num_times_uid += 1;
    } 
}

//判断时间
$datelimit_start = strtotime($scratchcard['datelimit_start']);
$datelimit_end   = strtotime($scratchcard['datelimit_end']);
$start=0;
if($datelimit_start>time()){
    //未开始
    $start = 1;
}elseif($datelimit_end<time()){
    //活动已结束
    $start = 2;
}
$end = 0;
if(isset($number_times_uid) && $number_times_uid>=$number_times){
    //不能再参加
    $end = 1;
}
if(isset($award_times_uid) && $award_times_uid>=$award_times){
    //不能再参加
    $end = 2;
}
if(isset($most_num_times_uid) && $most_num_times_uid>=$most_num_times){
    //不能再参加
    $end = 3;
}

if($_GPC['wid']){
    $update=array();
    $update['realname']=$_GPC['realname'];
    $update['tel']=$_GPC['tel'];
    pdo_update('yuexiage_travelmall_scratchcard_winner', $update, array('id' => $_GPC['wid']));
    mc_update($_W['member']['uid'],array('realname' => $_GPC['realname'],'mobile'=>$_GPC['tel']));
    message('保存成功', referer(), 'success');
}
$_share = array(
    'title'   => $scratchcard['title'],
    'link'    => $_W['siteurl'],
    'imgUrl'  => $_W['siteroot'].'addons/yuexiage_travelmall/images/images/p.png',
    'content' => $scratchcard['title']
);
include $this->template('scratchcard');

