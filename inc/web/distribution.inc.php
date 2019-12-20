<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do = in_array($do, array('display', 'post', 'delete','distribution_order')) ? $do : 'display';

$pindex = max(1, intval($_GPC['page']));
$psize = 5;
if ($do == 'display') {
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND ((oc.code LIKE :keyword) OR (oc.uid LIKE :keyword) OR (mm.realname LIKE :keyword) OR (o.ordersn LIKE :keyword)) ";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if(isset($_GPC['type']) && $_GPC['type'] != '-1'){
        $condition .= " AND oc.type = :category_id";
        $params[':category_id'] = $_GPC['type'];
    }
    $distribution= pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_orders_code')." as oc LEFT JOIN ".tablename('yuexiage_travelmall_orders')." as o ON oc.order_id = o.id LEFT JOIN ".tablename('mc_members')." as mm on oc.uid = mm.uid WHERE oc.uniacid = '{$_W['uniacid']}' $condition ORDER BY oc.id DESC LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    
    $total   =pdo_fetchcolumn("SELECT * FROM ".tablename('yuexiage_travelmall_orders_code')." as oc LEFT JOIN ".tablename('yuexiage_travelmall_orders')." as o ON oc.order_id = o.id LEFT JOIN ".tablename('mc_members')." as mm on oc.uid = mm.uid WHERE oc.uniacid = '{$_W['uniacid']}' $condition ",$params);
    $pager   = pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    $code         = $_GPC['code'];
    $distribution= pdo_fetchall("SELECT *,mc.uid as muid ,oc.id as ocid FROM ".tablename('yuexiage_travelmall_orders_code')." as oc LEFT JOIN ".tablename('yuexiage_travelmall_orders')." as o ON oc.order_id = o.id LEFT JOIN ".tablename('mc_members')." as mm on oc.uid = mm.uid LEFT JOIN ".tablename('yuexiage_travelmall_member_code')." as mc ON o.invitation=mc.code WHERE oc.uniacid = '{$_W['uniacid']}' and oc.code = '{$code}' ORDER BY oc.withdraw ASC  ");
    foreach ($distribution as $key => &$row) {
        $row['user'] = pdo_fetch("SELECT * FROM ".tablename('mc_members')." WHERE uid = {$row['muid']}");
    }
}elseif ($do == 'delete') { 
    $id     = intval($_GPC['id']);
    $stroke = intval($_GPC['stroke']);
    if($stroke){
        if(!empty($id)) {
            $stroke = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_stroke')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
            if(empty($stroke)) {
                message('行程不存在或已删除', '', 'error');
            }
        }else{
            $stroke = array(
                'displayorder' => 0
            );
        }
        pdo_delete('yuexiage_travelmall_stroke',  array('id' => $id));
        message('删除参团游成功！', $this->createWebUrl('offered', array('op' => 'display')), 'success');
    }else{
        if(!empty($id)) {
            $offered = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
            if(empty($offered)) {
            message('参团游不存在或已删除', '', 'error');
            }
        }else{
            $offered = array(
                'displayorder' => 0
            );
        }
        pdo_delete('yuexiage_travelmall_offered',  array('id' => $id));
        message('删除参团游成功！', $this->createWebUrl('offered', array('op' => 'display')), 'success');
    }
    
}elseif($do == 'distribution_order'){
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND ((co.uid LIKE :keyword) OR (mm.realname LIKE :keyword) OR (co.ordersn LIKE :keyword)) ";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['status']) && $_GPC['status'] != '0') {
        $condition .= "  and co.status = :status ";
        $params[':status'] = $_GPC['status'];
    }

    $sql = "SELECT co.*,mm.realname,mm.nickname,mm.mobile FROM ".tablename('yuexiage_travelmall_cash_order')." as co LEFT JOIN ".tablename('mc_members')." as mm ON mm.uid = co.uid where co.id>0 $condition";
    $cash = pdo_fetchall($sql,$params);

    foreach ($cash as $val) {
       $total += $val['total'];
    }
    
    if($_W['ispost'] && $_GPC['id']){

        $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_cash_order')." WHERE id = {$_GPC['id']}";
        $result = pdo_fetch($sql);
        $country_id = iunserializer($result['country_id']);
        foreach($country_id as $val){
            switch ($_GPC['status']) {
                case '-1':
                    # 已取消
                    $status = 1;
                    break;
                case '1':
                    # 已申请
                    $status = 2;
                    break;
                case '2':
                    # 已完成
                    $status = 3;
                    break;
                default:
                    # code...
                    break;
            }
            $user_data=array('status'=>$status);
            pdo_update('yuexiage_travelmall_coupon_user', $user_data, array('id' => $val));
        }

        $user_data = array(
            'status' => $_GPC['status'],
        );
        $result = pdo_update('yuexiage_travelmall_cash_order', $user_data, array('id' => $_GPC['id']));
        message('更新成功', '', 'success');
    }
}
include $this->template('distribution');