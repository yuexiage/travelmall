<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'personal_center';
$do = in_array($do, array('personal_center','getCode')) ? $do : 'personal_center';
load()->func('communication');
$uid  = $_W['member']['uid'];
$sql  = "SELECT code FROM ".tablename('yuexiage_travelmall_member_code')." WHERE uniacid = :uniacid and uid=".$uid;
$code = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));

if($do=='personal_center'){

   include $this->template('personal_center');
}
if($do=='getCode'){
    $code = 'U'.date("YmdHis").str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $data       = array();
    $data['uniacid'] = $_W['uniacid'];
    $data['uid']     = $uid;
    $data['code']    = $code;
    $save_code = pdo_insert('yuexiage_travelmall_member_code', $data);
    if($save_code){
        echo  json_encode(['code'=>'1','data'=>$code,'msg'=>'获取成功']);
    }else{echo 333333;
        echo  json_encode(['code'=>'0','data'=>'','msg'=>'获取失败']);
    }
    
}