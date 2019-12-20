<?php
global $_GPC, $_W;
load()->func('tpl');
$title      = $this->settings['shopname'];
$spike_end  = $this->settings['spike_end']*60;
$sql = "SELECT of.*,lc.name as lname FROM ".tablename('yuexiage_travelmall_fit')." as of LEFT JOIN ".tablename('yuexiage_travelmall_city')." as lc ON of.yid1 = lc.id WHERE of.id = {$_GPC['id']}";
$spike_item = pdo_fetch($sql); 
$spike_item['thumbs'] = iunserializer($spike_item['thumbs']);
if($spike_item['upper_shelf'] && ($spike_item['lower_shelf'] < time()) ){
    $spike_item['end'] = 1;
    $update = array('enabled' => 0);
    pdo_update('yuexiage_travelmall_fit', $update, array('id' => $_GPC['id']));
}

//获取日期
$sql = "SELECT FROM_UNIXTIME(start_date,'%Y-%m-%d') AS oa FROM ".tablename('yuexiage_travelmall_fit_amount')." WHERE offered_id = {$_GPC['id']} and uniacid = '{$_W['uniacid']}'";
$dates = pdo_fetchall($sql); 
foreach ($dates as $key => $value) {
    $dates[]=$value['oa'];
    unset($dates[$key]);
}

//判断收藏
$uid = $_W['member']['uid'];
$collection= pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_collection')." WHERE  uniacid = {$_W['uniacid']} AND uid={$uid} AND offered_id={$_GPC['id']}");


//获取行程
$sql = "SELECT * FROM ".tablename('yuexiage_travelmall_stroke')." WHERE offered_id = {$_GPC['id']} and uniacid = '{$_W['uniacid']}' order by displayorder";
$strokes = pdo_fetchall($sql); 

foreach ($strokes as $key => $stroke) {
    $strokes[$key]['stay']      = iunserializer($stroke['stay']);
    foreach ($strokes[$key]['stay'] as $k => $value) {
        $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_hotel')." WHERE id = {$k} and uniacid = '{$_W['uniacid']}' ";
        $strokes[$key]['stay'][$k] = pdo_fetch($sql); 
        $strokes[$key]['stay'][$k]['thumbs']=iunserializer($strokes[$key]['stay'][$k]['thumbs']);
        $strokes[$key]['stay'][$k]['facilities']=iunserializer($strokes[$key]['stay'][$k]['facilities']);
        $strokes[$key]['stay'][$k]['service']=iunserializer($strokes[$key]['stay'][$k]['service']);
    }
    $strokes[$key]['viewpoint'] = iunserializer($stroke['viewpoint']);
    foreach ($strokes[$key]['viewpoint'] as $k => $value) {
        $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_viewpoint')." WHERE id = {$k} and uniacid = '{$_W['uniacid']}' ";
        $strokes[$key]['viewpoint'][$k] = pdo_fetch($sql); 
        $strokes[$key]['viewpoint'][$k]['thumbs'] = iunserializer($strokes[$key]['viewpoint'][$k]['thumbs']);
    }
}

//获取航班
$flights = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_flight')." WHERE uniacid = '{$_W['uniacid']}'  and enabled=1 ", array(),'id');
//城市
$citys  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(),'id');

$coupon = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 AND id={$spike_item['coupon_id']}", array(),'id');

//判断是否有权发表评论
//黑名单
$blacklist = pdo_fetch('SELECT* FROM ' . tablename('yuexiage_travelmall_blacklist') . ' WHERE uniacid = :uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'],':uid'=>$uid));

$comment = 1;
if($blacklist['comment']){
    //加入黑名单，所以禁止评论
    $comment = 0;
}
//是否有订单
$order = pdo_fetch('SELECT * FROM ' . tablename('yuexiage_travelmall_orders') . ' WHERE uniacid = :uniacid and uid=:uid and goods_id=:goods_id order by createtime DESC', array(':uniacid' => $_W['uniacid'],':uid'=>$uid,':goods_id'=>$_GPC['id']));
if(!$order){
    $comment = 0;
}else{
    //是否已经评论过
    $con = pdo_fetch('SELECT * FROM ' . tablename('yuexiage_travelmall_comment') . ' WHERE uniacid = :uniacid and uid=:uid and goods_id=:goods_id and type=1 and order_id = :order_id', array(':uniacid' => $_W['uniacid'],':uid'=>$uid,':goods_id'=>$_GPC['id'],':order_id'=>$order['id']));
    if($con){
        $comment = 0;
    }
}

$params [':uniacid']    = $_W['uniacid'];
$params [':goods_id']   = $_GPC['id'];

if($_GPC['t']){
    $condition = " AND tc.attitude = :attitude";
    $params[':attitude']   = $_GPC['t'];
}
$commentlist = pdo_fetchall('SELECT * FROM ' . tablename('yuexiage_travelmall_comment') . ' as tc LEFT JOIN ' . tablename('mc_members') . " as mm ON mm.uid = tc.uid WHERE tc.uniacid = :uniacid and  tc.goods_id=:goods_id  $condition",$params);

$commentlist2 = pdo_fetchall('SELECT * FROM ' . tablename('yuexiage_travelmall_comment') . ' as tc LEFT JOIN ' . tablename('mc_members') . ' as mm ON mm.uid = tc.uid WHERE tc.uniacid = :uniacid and  tc.goods_id=:goods_id  ',array(':uniacid' => $_W['uniacid'],':goods_id'=>$_GPC['id']));
foreach($commentlist2 as $comlist){
    if($comlist['attitude'] == 1){
        $attitude['1'] +=1;
    }
    if($comlist['attitude'] == 2){
        $attitude['2'] +=1;
    }
    if($comlist['attitude'] == 3){
        $attitude['3'] +=1;
    }
    $attitude['4']+=1;
}
include $this->template('fit_spike_item');
