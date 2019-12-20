<?php
global $_GPC, $_W;
$title      = '代金券';
load()->func('logging');
$uid  = $_W['member']['uid'];
$cashs = pdo_fetchall('SELECT *,cu.id as cuid FROM ' . tablename('yuexiage_travelmall_coupon_user') . ' as cu LEFT JOIN '.tablename('yuexiage_travelmall_coupon').' as c ON c.id = cu.country_id WHERE  cu.uid=:uid and c.theme_id = 1', array(':uid'=>$uid));
    foreach ($cashs as $key => &$value) {
        if($value['status'] == '1'){
            $total+=$value['amount'];
        }
    }
if($_W['ispost']){
    $user_data = array(
        'status' => '2',
    );
    if($_GPC['id']){
        foreach ($_GPC['id'] as $id) {
            $result = pdo_update('yuexiage_travelmall_coupon_user', $user_data, array('id' => $id));
            if(!$result){
                logging_run('uid='.$uid."&coupon_user_id=".$id);
            }
        }
        
    }
    $data = array();
    $ordersn = 'F'.date("YmdHis").str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $data['ordersn'] = $ordersn;
    $data['country_id'] = iserializer($_GPC['id']);
    $data['status'] = 1;
    $data['uid'] = $uid;
    $data['total'] = $_GPC['total'];
    $data['createtime'] = time();
    $result = pdo_insert('yuexiage_travelmall_cash_order', $data);
    message('提交成功');
}
include $this->template('mycash');

