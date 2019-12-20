<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize = 5;
if ($do == 'display') {
    if (!empty($_GPC['keyword'])) {
        $condition = " and (( `goods_name` LIKE :keyword ) OR ( `ordersn` LIKE :keyword ))";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if($_GPC['t']=='f' || !isset($_GPC['t'])){
        $condition .=" and type = '0' ";
        $orders = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_orders')." WHERE uniacid = '{$_W['uniacid']}' $condition  ORDER BY  id DESC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);

        $total=pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_orders') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
        if($_GPC['export_submit']) {
            $header = array(
                'id' => 'ID', 
                'ordersn' => '订单号', 
                'goods_id' => '线路ID', 
                'goods_name' => '线路名称', 
                'event_day' => '出团日期', 
                'adult_price' => '成人价格',
                'adult_num' => '成人数量', 
                'child_price' => '儿童价格', 
                'child_num' => '儿童数量',
                'subtotal' => '小计',
                'coupon_id' => '优惠券ID',
                'coupon_price' => '优惠券金额',
                'credit_num' => '积分数量',
                'credit_value' => '积分价格',
                'total' => '总价',
                'contact_name' => '联系人姓名',
                'contact_tel' => '联系人电话',
                'contact_id' => '联系人身份证',
                'contact_email' => '联系人邮箱',
                'status' => '状态',
                'remark' => '备注',
                'createtime' => '创建时间',
                'cash_id' => '代金券ID',
                'cash_price' => '代金券金额',
                'invitation' => '推荐号码',
                'cancel' => '取消订单',
                'type' => '订单类型'
            );
            $keys = array_keys($header);
            $html = "\xEF\xBB\xBF";
            foreach ($header as $li) {
                $html .= $li . "\t ,";
            }
            $html .= "\n";
            foreach ($orders as $order) {
                $html .= $order['id'] . "\t ,";
                $html .= $order['ordersn']."\t ,";
                
                $html .= $order['goods_id'] . "\t ,";
                $html .= $order['goods_name'] . "\t ,";
                $html .= $order['event_day'] . "\t ,";
                $html .= $order['adult_price'] . "\t ,";
                $html .= $order['adult_num'] . "\t ,";
                $html .= $order['child_price'] . "\t ,";
                $html .= $order['child_num'] . "\t ,";
                $html .= $order['subtotal'] . "\t ,";
                $html .= $order['coupon_id'] . "\t ,";
                $html .= $order['coupon_price'] . "\t ,";
                $html .= $order['credit_num'] . "\t ,";
                $html .= $order['credit_value'] . "\t ,";
                $html .= $order['total'] . "\t ,";
                $html .= $order['contact_name'] . "\t ,";
                $html .= $order['contact_tel'] . "\t ,";
                $html .= $order['contact_id'] . "\t ,";
                $html .= $order['contact_email'] . "\t ,";
                $html .= $order['status'] . "\t ,";
                $html .= $order['remark'] . "\t ,";
                $html .= $order['createtime'] . "\t ,";
                $html .= $order['cash_id'] . "\t ,";
                $html .= $order['cash_price'] . "\t ,";
                $html .= $order['invitation'] . "\t ,";
                $html .= $order['cancel'] . "\t ,";
                $html .= $order['type'] . "\t ,";
                $html .= "\n";
            }
            header("Content-type:text/csv");
            header("Content-Disposition:attachment; filename=会员数据.csv");
            echo $html;
            exit();
        }
    }elseif($_GPC['t']=='q' || !isset($_GPC['t'])){
        $orders = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_visa_orders')." WHERE uniacid = '{$_W['uniacid']}' $condition  ORDER BY  id DESC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
        $total=pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_visa_orders') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
        if($_GPC['export_submit']) {
            $header = array(
                'id' => 'ID', 
                'ordersn' => '订单号', 
                'vid' => '签证ID', 
                'vname' => '签证名称', 
                'price' => '价格', 
                'time' => '创建时间', 
                'contact_name' => '联系人姓名',
                'contact_tel' => '联系人电话',
                'contact_id' => '联系人身份证',
                'contact_email' => '联系人邮箱',
                'status' => '状态',
                'cancel' => '取消订单'
            );
            $keys = array_keys($header);
            $html = "\xEF\xBB\xBF";
            foreach ($header as $li) {
                $html .= $li . "\t ,";
            }
            $html .= "\n";

            foreach ($orders as $order) {
                $html .= $order['id'] . "\t ,";
                $html .= $order['ordersn']."\t ,";
                
                $html .= $order['vid'] . "\t ,";
                $html .= $order['vname'] . "\t ,";
                $html .= $order['price'] . "\t ,";
                $html .= $order['time'] . "\t ,";
                $html .= $order['contact_name'] . "\t ,";

                $html .= $order['contact_tel'] . "\t ,";
                $html .= $order['contact_id'] . "\t ,";
                $html .= $order['contact_email'] . "\t ,";
                $html .= $order['status'] . "\t ,";
             
                $html .= $order['cancel'] . "\t ,";

                $html .= "\n";
            }
            header("Content-type:text/csv");
            header("Content-Disposition:attachment; filename=会员数据.csv");
            echo $html;
            exit();
        }
        include $this->template('visa_order');
    }elseif($_GPC['t']=='z' || !isset($_GPC['t'])){
        $condition .=" and type = '1' ";
        $orders = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_orders')." WHERE uniacid = '{$_W['uniacid']}' $condition  ORDER BY  id DESC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);

        $total=pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_orders') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
        if($_GPC['export_submit']) {
            $header = array(
                'id' => 'ID', 
                'ordersn' => '订单号', 
                'goods_id' => '线路ID', 
                'goods_name' => '线路名称', 
                'event_day' => '出团日期', 
                'adult_price' => '成人价格',
                'adult_num' => '成人数量', 
                'child_price' => '儿童价格', 
                'child_num' => '儿童数量',
                'subtotal' => '小计',
                'coupon_id' => '优惠券ID',
                'coupon_price' => '优惠券金额',
                'credit_num' => '积分数量',
                'credit_value' => '积分价格',
                'total' => '总价',
                'contact_name' => '联系人姓名',
                'contact_tel' => '联系人电话',
                'contact_id' => '联系人身份证',
                'contact_email' => '联系人邮箱',
                'status' => '状态',
                'remark' => '备注',
                'createtime' => '创建时间',
                'cash_id' => '代金券ID',
                'cash_price' => '代金券金额',
                'invitation' => '推荐号码',
                'cancel' => '取消订单',
                'type' => '订单类型'
            );
            $keys = array_keys($header);
            $html = "\xEF\xBB\xBF";
            foreach ($header as $li) {
                $html .= $li . "\t ,";
            }
            $html .= "\n";
            foreach ($orders as $order) {
                $html .= $order['id'] . "\t ,";
                $html .= $order['ordersn']."\t ,";
                
                $html .= $order['goods_id'] . "\t ,";
                $html .= $order['goods_name'] . "\t ,";
                $html .= $order['event_day'] . "\t ,";
                $html .= $order['adult_price'] . "\t ,";
                $html .= $order['adult_num'] . "\t ,";
                $html .= $order['child_price'] . "\t ,";
                $html .= $order['child_num'] . "\t ,";
                $html .= $order['subtotal'] . "\t ,";
                $html .= $order['coupon_id'] . "\t ,";
                $html .= $order['coupon_price'] . "\t ,";
                $html .= $order['credit_num'] . "\t ,";
                $html .= $order['credit_value'] . "\t ,";
                $html .= $order['total'] . "\t ,";
                $html .= $order['contact_name'] . "\t ,";
                $html .= $order['contact_tel'] . "\t ,";
                $html .= $order['contact_id'] . "\t ,";
                $html .= $order['contact_email'] . "\t ,";
                $html .= $order['status'] . "\t ,";
                $html .= $order['remark'] . "\t ,";
                $html .= $order['createtime'] . "\t ,";
                $html .= $order['cash_id'] . "\t ,";
                $html .= $order['cash_price'] . "\t ,";
                $html .= $order['invitation'] . "\t ,";
                $html .= $order['cancel'] . "\t ,";
                $html .= $order['type'] . "\t ,";
                $html .= "\n";
            }
            header("Content-type:text/csv");
            header("Content-Disposition:attachment; filename=会员数据.csv");
            echo $html;
            exit();
        }
    }
    $pager = pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    load()->func('communication');
    $id = intval($_GPC['id']);

    if($_GPC['t']=='f' || !isset($_GPC['t'])){
        if(!empty($id)) {
            $order = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_orders')." WHERE id = '$id' and uniacid = '{$_W['uniacid']}'");
            if(empty($order)) {
                message('订单不存在或已删除', '', 'error');
            }
            $order_option = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_orders_option')." WHERE order_id = '{$id}' ");

            if (checksubmit('submit')) {
                if (empty($_GPC['remark'])) {
                    message('抱歉，请输入备注内容！');
                }

                $data = array(
                    'remark'       => $_GPC['remark']
                );

                if (!empty($id)) {
                    pdo_update('yuexiage_travelmall_orders', $data, array('id' => $id));
                } 
                message('更新订单成功！', $this->createWebUrl('order', array('op' => 'post','id'=>$id)), 'success');
            }
            if (checksubmit('submit2')) {
                $data = array(
                    'status'       => 1
                );
                pdo_update('yuexiage_travelmall_orders', $data, array('id' => $id));
                ihttp_email($order['contact_email'],'订单状态变更通知','亲爱的:'.$order['contact_name'].',您的订单'.$order['ordersn'].'变更订单状态为【已付款】');
                message('更新订单成功！', $this->createWebUrl('order', array('op' => 'post','id'=>$id)), 'success');
            }
            if (checksubmit('submit3')) {
                $data = array(
                    'status'       => 2
                );
                pdo_update('yuexiage_travelmall_orders', $data, array('id' => $id));
                ihttp_email($order['contact_email'],'订单状态变更通知','亲爱的:'.$order['contact_name'].',您的订单'.$order['ordersn'].'变更订单状态为【已完成】');
                message('更新订单成功！', $this->createWebUrl('order', array('op' => 'post','id'=>$id)), 'success');
            }
            if (checksubmit('submit4')) {
                $data = array(
                    'cancel'       => 1
                );
                pdo_update('yuexiage_travelmall_orders', $data, array('id' => $id));
                ihttp_email($order['contact_email'],'订单状态变更通知','亲爱的:'.$order['contact_name'].',您的订单'.$order['ordersn'].'变更订单状态为【已取消】');
                message('更新订单成功！', $this->createWebUrl('order', array('op' => 'post','id'=>$id)), 'success');
            }
        }else {
            //创建订单
            include $this->template('order2');
            exit;
        }
    }elseif($_GPC['t']=='q' || !isset($_GPC['t'])){ 
        if(!empty($id)) {
            $order = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_visa_orders')." WHERE id = '$id' and uniacid = '{$_W['uniacid']}'");
            if(empty($order)) {
                message('订单不存在或已删除', '', 'error');
            }
            
            if (checksubmit('submit2')) {
                $data = array(
                    'status'       => 1
                );
                pdo_update('yuexiage_travelmall_visa_orders', $data, array('id' => $id));
                ihttp_email($order['contact_email'],'订单状态变更通知','亲爱的:'.$order['contact_name'].',您的订单'.$order['ordersn'].'变更订单状态为【已付款】');
                message('更新订单成功！', $this->createWebUrl('order', array('op' => 'post','id'=>$id,'t'=>'q')), 'success');
            }
            if (checksubmit('submit3')) {
                $data = array(
                    'status'       => 2
                );
                pdo_update('yuexiage_travelmall_visa_orders', $data, array('id' => $id));
                ihttp_email($order['contact_email'],'订单状态变更通知','亲爱的:'.$order['contact_name'].',您的订单'.$order['ordersn'].'变更订单状态为【已完成】');
                message('更新订单成功！', $this->createWebUrl('order', array('op' => 'post','id'=>$id,'t'=>'q')), 'success');
            }
            if (checksubmit('submit4')) {
                $data = array(
                    'cancel'       => 1
                );
                pdo_update('yuexiage_travelmall_visa_orders', $data, array('id' => $id));
                ihttp_email($order['contact_email'],'订单状态变更通知','亲爱的:'.$order['contact_name'].',您的订单'.$order['ordersn'].'变更订单状态为【已取消】');
                message('更新订单成功！', $this->createWebUrl('order', array('op' => 'post','id'=>$id,'t'=>'q')), 'success');
            }
            include $this->template('visa_order');exit;
        }else {
            //创建订单
            include $this->template('order2');
            exit;
        }
    }



    
 
}
include $this->template('order');