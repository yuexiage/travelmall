<?php 
global $_GPC, $_W;
$id     = intval($_GPC['id']);
$amount = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_fit_amount')." WHERE uniacid = '{$_W['uniacid']}'  and offered_id='{$id}' ", array(), 'id');
$array=array();
if(count($amount)){
    foreach ($amount as $value) {
        $array[]=array(
            'title' => "成人:".$value['adult_price']."￥,儿童:".$value['child_price']."￥,库存:".$value['stock'],
            'start' => date('Y-m-d',$value['start_date'])
        );
    }
    echo json_encode($array);
} 
?>