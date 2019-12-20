<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'visa';
$do = in_array($do, array('visa', 'visa_item')) ? $do : 'visa';
load()->func('communication');
$uid  = $_W['member']['uid'];
if($do=='visa'){
    $title = '签证';
    $visas = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_visa')." where uniacid = '{$_W['uniacid']}' and enabled=1" , array(), 'id');
    include $this->template('visa');
}elseif($do=='visa_item'){
    $visa  = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_visa')." where uniacid = '{$_W['uniacid']}' and enabled=1 and id = {$_GPC['id']}");
    $title = $visa['name'];

    $visa_order  = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_visa_orders')." where uniacid = '{$_W['uniacid']}' and  vid = {$_GPC['id']} and status <2");

    $title = $visa['name'];

    if($_GPC['submit1']){
        $ordersn = 'V'.date("YmdHis").str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $data['vid']        = $_GPC['id'];
        $data['vname']      = $visa['name'];
        $data['ordersn']    = $ordersn;
        $data['uniacid']    = $_W['uniacid'];
        $data['time']       = time();
        $data['status']     = 0;
        $data['price']      = $visa['price'];
        $data['contact_name']       = $_GPC['contact_name'];
        $data['contact_tel']        = $_GPC['contact_tel'];
        $data['contact_id']         = $_GPC['contact_id'];
        $data['contact_email']      = $_GPC['contact_email'];   
        $data['uid']      = $uid;  
        pdo_insert('yuexiage_travelmall_visa_orders',$data);

        $text = "亲爱的:<br>".$data['contact_name'].'您预订的签证:'.$visa['name'].'订单已保存！订单号:'.$ordersn.'。<br>'.date("Y-m-d H:i:s");
        ihttp_email($data['contact_email'],'订单通知',$text);

        message('保存成功', referer(), 'success');
    }
   
    include $this->template('visa_item');
}
