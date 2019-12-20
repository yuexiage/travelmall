<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize = 5;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            pdo_update('yuexiage_travelmall_visa', $update, array('id' => $id));
        }
        message('签证排序更新成功！', 'refresh', 'success');
    }

    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['visa'])) {
        if($_GPC['visa']['parentid'] != '0'){
            $condition .= " AND country_id = :country_id";
            $params[':country_id'] = $_GPC['visa']['parentid'];
        }
        if($_GPC['visa']['childid'] != '0'){
            $condition .= " AND city_id = :city_id";
            $params[':city_id'] = $_GPC['visa']['childid'];
        }
    }

    /**地区分布**/
    $countrys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    $citys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(),'id');
    foreach ($citys as $city) {
        $_citys[$city['pcate']][] = $city;
    }
    /**地区分布结束**/

    $visa = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_visa')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    
    $total=pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_visa') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
    $pager = pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    $id = intval($_GPC['id']);
    if(!empty($id)) {
        $visa = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_visa')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
        if(empty($visa)) {
            message('签证不存在或已删除', '', 'error');
        }
    } else {
        $visa = array(
            'displayorder' => 0
        );
    }

    //解析图片内容
    $visa['thumbs'] = iunserializer($visa['thumbs']);
    
    /**地区分布**/
    $countrys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    $citys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array());
    foreach ($citys as $city) {
        $_citys[$city['pcate']][] = $city;
    }
    /**地区分布结束**/

    if (checksubmit('submit')) {
        if (empty($_GPC['name'])) {
            message('抱歉，请输入签证名称！');
        }

        $data = array(
            'uniacid'       => $_W['uniacid'],
            'name'          => $_GPC['name'],
            'displayorder'  => intval($_GPC['displayorder']),
            'enabled'       => intval($_GPC['enabled']),
            'country_id'           => $_GPC['visa']['parentid'],
            'city_id'           => $_GPC['visa']['childid'],
            'thumbs'        => iserializer($_GPC['thumbs']),
            'contact'       => $_GPC['contact'],
            'countrys'      => $_GPC['countrys'],
            'price'         => $_GPC['price'],
            'credit'        => $_GPC['credit'],
            'description'   => $_GPC['description']
        );

        if (!empty($id)) {
            pdo_update('yuexiage_travelmall_visa', $data, array('id' => $id));
        } else {
            pdo_insert('yuexiage_travelmall_visa', $data);
            $id = pdo_insertid();
        }
        message('更新签证成功！', $this->createWebUrl('visa', array('op' => 'display')), 'success');
    }
}elseif ($do == 'delete') { 
    $id = intval($_GPC['id']);
    if(!empty($id)) {
        $visa = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_visa')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
        if(empty($visa)) {
            message('签证不存在或已删除', '', 'error');
        }
    }else{
        $visa = array(
            'displayorder' => 0
        );
    }
    pdo_delete('yuexiage_travelmall_visa',  array('id' => $id));
    message('删除签证成功！', $this->createWebUrl('visa', array('op' => 'display')), 'success');
}
include $this->template('visa');