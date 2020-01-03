<?php 
global $_GPC, $_W;
try {
    if($_GPC['op']=='all'){
        $offered_id         = $_GPC['offered_id'];
        if ( empty($offered_id) ){
            throw new Exception("线路ID不能为空!");
        }
        $amount = pdo_fetchall("SELECT amount_id,FROM_UNIXTIME(start_date,'%Y-%m-%d') as start_date,adult_price,child_price,stock,single FROM ".tablename('yuexiage_travelmall_offered_amount')." WHERE uniacid = :uniacid  and offered_id=:offered_id and is_del = :is_del ", array(
            ':uniacid'      =>$_W['uniacid'],
            ':offered_id'   =>$offered_id,
            ':is_del'       =>0,
        ), 'start_date');
        echo json($amount);
    }
} catch (Exception $e) {
    echo json('',$e->getCode(),$e->getMessage());
}






?>