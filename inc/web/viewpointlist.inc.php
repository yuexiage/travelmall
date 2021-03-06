<?php
global $_GPC, $_W;
$do     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do     = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize  = 5;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $viewpoint_id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            update('yuexiage_travelmall_viewpoint', $update, array('viewpoint_id' => $viewpoint_id));
        }
        message('景点排序更新成功！', 'refresh', 'success');
    }
    $params     = [':is_del'=>0];
    $condition  = " and is_del = :is_del";
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['viewpoint'])) {
        if($_GPC['viewpoint']['parentid'] != '0'){
            $condition .= " AND country_id = :country_id";
            $params[':country_id'] = $_GPC['viewpoint']['parentid'];
        }
        if($_GPC['viewpoint']['childid'] != '0'){
            $condition .= " AND city_id = :city_id";
            $params[':city_id'] = $_GPC['viewpoint']['childid'];
        }
    }

    /**地区分布**/
    $regions    = select_country_city();
    //pdo_debug();exit;
    /**地区分布结束**/

    $params[':uniacid'] = $_W['uniacid'];
    $viewpoint  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_viewpoint')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    $total      = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_viewpoint') . " WHERE uniacid = :uniacid $condition ",$params);
    $pager      = pagination($total, $pindex, $psize);

}elseif ($do == 'post') {
    $viewpoint_id = $_GPC['id'];
    if(!empty($viewpoint_id)) {
        $viewpoint = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_viewpoint')." WHERE id = '$viewpoint_id' AND uniacid = {$_W['uniacid']}");
        if(empty($viewpoint)) {
            message('景点不存在或已删除', '', 'error');
        }
    } else {
        $viewpoint = array(
            'displayorder' => 0
        );
    }

    //解析图片内容
    $viewpoint['thumbs'] = iunserializer($viewpoint['thumbs']);

    /**地区分布**/
    $regions = select_country_city();
    //pdo_debug();exit;
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
            'country_id'           => $_GPC['viewpoint']['parentid'],
            'city_id'           => $_GPC['viewpoint']['childid'],
            'thumbs'        => iserializer($_GPC['thumbs']),
            'description'   => $_GPC['description']
        );

        if (!empty($id)) {
            update('yuexiage_travelmall_viewpoint', $data, array('viewpoint_id' => $viewpoint_id));
        } else {
            insert('yuexiage_travelmall_viewpoint', $data);
            $id = insertid();
        }
        message('更新参团游成功！', $this->createWebUrl('viewpoint', array('op' => 'display')), 'success');
    }
}elseif ($do == 'delete') { 
    $id = $_GPC['id'];
    if(!empty($id)) {
        $viewpoint = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_viewpoint')." WHERE viewpoint_id = '$viewpoint_id' AND uniacid = {$_W['uniacid']}");
        if(empty($viewpoint)) {
            message('参团游不存在或已删除', '', 'error');
        }
    }else{
        $viewpoint = array(
            'displayorder' => 0
        );
    }
    pdo_update('yuexiage_travelmall_viewpoint', ['is_del'=>1], array('viewpoint_id' => $viewpoint_id));
    message('删除参团游成功！', $this->createWebUrl('viewpoint', array('op' => 'display')), 'success');
}
include $this->template('template/viewpointlist');