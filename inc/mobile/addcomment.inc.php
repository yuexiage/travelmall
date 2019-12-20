<?php
global $_GPC, $_W;
load()->func('tpl');
$uid = $_W['member']['uid'];

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
if($_W['ispost']){
    $data = array();
    $data['uniacid']    = $_W['uniacid'];
    $data['goods_id']   = $_GPC['id'];
    $data['uid']        = $uid;
    $data['content']    = $_GPC['content'];
    $data['type']       = $_GPC['t'];
    $data['createtime'] = time();
    $data['attitude']   = $_GPC['attitude'];
    $data['order_id']   = $order['id'];
    $data['status']     = 0;
    $data['images']     = iserializer($_GPC['images']);
    pdo_insert('yuexiage_travelmall_comment', $data);
    message('评论成功！','', 'success');
}

include $this->template('addcomment');
