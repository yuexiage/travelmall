<?php
global $_GPC, $_W;
$do     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do     = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize  = 10;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $coupon_id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            pdo_update('yuexiage_travelmall_coupon', $update, array('coupon_id' => $coupon_id));
        }
        message('优惠券排序更新成功！', 'refresh', 'success');
    }

    $params     = [':is_del'=>0];
    $condition  = " and is_del = :is_del";
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['coupon'])) {
        if($_GPC['coupon']['parentid'] != '0'){
            $condition .= " AND country_id = :country_id";
            $params[':country_id'] = $_GPC['coupon']['parentid'];
        }
        if($_GPC['coupon']['childid'] != '0'){
            $condition .= " AND city_id = :city_id";
            $params[':city_id'] = $_GPC['coupon']['childid'];
        }
    }

    /**地区分布**/
    $regions    =   select_country_city_all();
    //pdo_debug();exit;
    /**地区分布结束**/
    $params[':uniacid'] = $_W['uniacid'];
    $coupon = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    $total  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_coupon') . " WHERE uniacid = :uniacid $condition ",$params);
    $pager  = pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    $coupon_id = $_GPC['id'];
    if(!empty($coupon_id)) {
        $coupon = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE coupon_id = :coupon_id AND uniacid = :uniacid and is_del = :is_del",array(
            ':uniacid'  =>$_W['uniacid'],
            ':coupon_id'=>$coupon_id,
            ':is_del'   =>0
        ));
        if(empty($coupon)) {
            message('优惠券不存在或已删除', '', 'error');
        }
    } else {
        $coupon = array(
            'displayorder' => 0
        );
    }

    //解析图片内容
    $coupon['thumbs'] = iunserializer($coupon['thumbs']);

    /**地区分布**/
    if (!empty($coupon_id)){
        //编辑的时候下架国家也取出来
        $regions = select_country_city_all();
    }else{
        $regions = select_country_city();
    }
    /**地区分布结束**/

    if (checksubmit('submit')) {
        if (empty($_GPC['name'])) {
            message('抱歉，请输入参团游名称！');
        }

        $data = array(
            'uniacid'       => $_W['uniacid'],
            'name'          => $_GPC['name'],
            'displayorder'  => intval($_GPC['displayorder']),
            'enabled'       => intval($_GPC['enabled']),
            'type'          => $_GPC['type'],
            'amount'        => $_GPC['amount'],
            'achieve'       => $_GPC['achieve'],
            'days'          => $_GPC['days'],
            'thumbs'        => iserializer($_GPC['thumbs']),
            'description'   => $_GPC['description']
        );

        if (!empty($coupon_id)) {
            update('yuexiage_travelmall_coupon', $data, array('coupon_id' => $coupon_id));
        } else {
            $data['coupon_id'] = uuid();
            insert('yuexiage_travelmall_coupon', $data);
        }
        message('更新优惠券成功！', $this->createWebUrl('coupon', array('op' => 'display')), 'success');
    }
}elseif ($do == 'delete') {
    $coupon_id = $_GPC['id'];
    if(!empty($coupon_id)) {
        $coupon = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE coupon_id = :coupon_id AND uniacid = :uniacid and is_del = :is_del",array(
            ':uniacid'  =>$_W['uniacid'],
            ':coupon_id'=>$coupon_id,
            ':is_del'   =>0
        ));
        if(empty($coupon)) {
            message('优惠券不存在或已删除', '', 'error');
        }
    }else{
        $coupon = array(
            'displayorder' => 0
        );
    }

    //如果有线路（上架+下架）存在，拒绝删除
    $count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_offered')." WHERE coupons like :coupons AND uniacid = :uniacid and is_del = :is_del",
        array(':uniacid'=>$_W['uniacid'],':coupons'=>"%$coupon_id%",':is_del'=>0));
    if(!empty($count)){
        message('被线路使用的优惠券不能删除', '', 'error');
    }

    update('yuexiage_travelmall_coupon', ['is_del'=>1,'coupon_id' => $coupon_id,'type'=>$coupon['type']], array('coupon_id' => $coupon_id));
    message('删除优惠券成功！', $this->createWebUrl('coupon', array('op' => 'display')), 'success');
}
include $this->template('coupon');