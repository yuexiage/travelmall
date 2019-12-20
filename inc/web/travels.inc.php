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
            pdo_update('yuexiage_travelmall_travels', $update, array('id' => $id));
        }
        message('景点排序更新成功！', 'refresh', 'success');
    }

    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['travels'])) {
        if($_GPC['travels']['parentid'] != '0'){
            $condition .= " AND country_id = :country_id";
            $params[':country_id'] = $_GPC['travels']['parentid'];
        }
        if($_GPC['travels']['childid'] != '0'){
            $condition .= " AND city_id = :city_id";
            $params[':city_id'] = $_GPC['travels']['childid'];
        }
    }

    /**地区分布**/
    $countrys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    $citys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(),'id');
    foreach ($citys as $city) {
        $_citys[$city['pcate']][] = $city;
    }
    /**地区分布结束**/

    $travels = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_travels')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    
    $total=pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_travels') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
    $pager = pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    $id = intval($_GPC['id']);
    if(!empty($id)) {
        $travels = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_travels')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
        if(empty($travels)) {
            message('主题不存在或已删除', '', 'error');
        }
    } else {
        $travels = array(
            'displayorder' => 0
        );
    }

    //解析图片内容
    $travels['thumbs']  = iunserializer($travels['thumbs']);
    $travels['tabs']    = iunserializer($travels['tabs']);
    /**地区分布**/
    $countrys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    $citys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array());
    foreach ($citys as $city) {
        $_citys[$city['pcate']][] = $city;
    }
    /**地区分布结束**/

    if (checksubmit('submit')) {
        if (empty($_GPC['name'])) {
            message('抱歉，请输入主题名称！');
        }

        $data = array(
            'uniacid'       => $_W['uniacid'],
            'name'          => $_GPC['name'],
            'displayorder'  => intval($_GPC['displayorder']),
            'enabled'       => intval($_GPC['enabled']),
            'country_id'           => $_GPC['travels']['parentid'],
            'city_id'           => $_GPC['travels']['childid'],
            'thumbs'        => $_GPC['thumbs'],
            'description'   => $_GPC['description'],
            'destination'   => $_GPC['destination'],
            'season'        => $_GPC['season'],
            'introduction'  => $_GPC['introduction'],
            'tabs'          => iserializer($_GPC['tabs']),
            'click'         => $_GPC['click'],
            'comment'       => $_GPC['comment'],
            'createtime'    => time()
        );

        if (!empty($id)) {
            pdo_update('yuexiage_travelmall_travels', $data, array('id' => $id));
        } else {
            pdo_insert('yuexiage_travelmall_travels', $data);
            $id = pdo_insertid();
        }
        message('更新游记成功！', $this->createWebUrl('travels', array('op' => 'display')), 'success');
    }
}elseif ($do == 'delete') { 
    $id = intval($_GPC['id']);
    if(!empty($id)) {
        $travels = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_travels')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
        if(empty($travels)) {
            message('主题不存在或已删除', '', 'error');
        }
    }else{
        $travels = array(
            'displayorder' => 0
        );
    }
    pdo_delete('yuexiage_travelmall_travels',  array('id' => $id));
    message('删除主题成功！', $this->createWebUrl('travels', array('op' => 'display')), 'success');
}
include $this->template('travels');