<?php
global $_GPC, $_W;
load()->func('tpl');
$do     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do     = in_array($do, array('display', 'post', 'delete','tablist')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize  = 10;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $tab_id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            update('yuexiage_travelmall_tabs', $update, array('id' => $tab_id));
        }
        message('标签排序更新成功！', 'refresh', 'success');
    }
    $params     = [':is_del'=>0];
    $condition  = " and is_del = :is_del";
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    $params[':uniacid'] = $_W['uniacid'];
    $tabs   = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_tabs')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    $total  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_tabs') . " WHERE uniacid = :uniacid $condition ",$params);
    $pager  = pagination($total, $pindex, $psize);
    include $this->template('tabs');
}elseif ($do == 'post') {
    $tab_id = $_GPC['id'];
    if(!empty($tab_id)) {
        $tabs = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_tabs')." WHERE tab_id = :tab_id AND uniacid = :uniacid and is_del = :is_del",array(
            ":uniacid"  => $_W['uniacid'],
            ":tab_id"   =>$tab_id,
            ':is_del'   =>0,
        ));
        if(empty($tabs)) {
            message('标签不存在或已删除', '', 'error');
        }
    } else {
        $tabs = array(
            'displayorder' => 0
        );
    }

    if (checksubmit('submit')) {
        if (empty($_GPC['name'])) {
            message('抱歉，请输入标签名称！');
        }
        $data = array(
            'uniacid'       => $_W['uniacid'],
            'name'          => $_GPC['name'],
            'displayorder'  => intval($_GPC['displayorder']),
            'enabled'       => intval($_GPC['enabled']),
            'thumb'         => $_GPC['thumb'],
            'description'   => $_GPC['description']
        );
        if (!empty($tab_id)) {
            update('yuexiage_travelmall_tabs', $data, array('tab_id' => $tab_id));
        } else {
            $data['tab_id'] = uuid();
            insert('yuexiage_travelmall_tabs', $data);
            //$id = insertid();
        }
        message('更新标签成功！', $this->createWebUrl('tabs', array('op' => 'display')), 'success');
    }
    include $this->template('tabs');
}elseif ($do == 'delete') { 
    $tab_id = $_GPC['id'];
    if(!empty($tab_id)) {
        $tabs = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_tabs')." WHERE tab_id = :tab_id AND uniacid = :uniacid and is_del = :is_del",array(
            ":uniacid"  => $_W['uniacid'],
            ":tab_id"   =>$tab_id,
            ':is_del'   =>0,
        ));
        if(empty($tabs)) {
            message('标签不存在或已删除', '', 'error');
        }
    }else{
        $tabs = array(
            'displayorder' => 0
        );
    }

    //TODO 删除规则待完善
    //如果有线路（上架+下架）存在，拒绝删除
    $count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_offered')." WHERE tabs like :tab_id AND uniacid = :uniacid and is_del = :is_del",
        array(':uniacid'=>$_W['uniacid'],':tab_id'=>"%$tab_id%",':is_del'=>0));
    if(!empty($count)){
        message('被线路使用的标签不能删除', '', 'error');
    }

    pdo_update('yuexiage_travelmall_tabs', ['is_del'=>1], array('tab_id' => $tab_id));
    message('删除标签成功！', $this->createWebUrl('tabs', array('op' => 'display')), 'success');
    include $this->template('tabs');
}elseif ($do == 'tablist') {
    $params     = [':is_del'=>0];
    $condition  = " and is_del = :is_del";
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    $regions = select_country_city_all();
    $params[':uniacid'] = $_W['uniacid'];
    $tabs   = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_tabs')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    $total  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_tabs') . " WHERE uniacid = :uniacid $condition ",$params);
    $pager  = pagination($total, $pindex, $psize);
    include $this->template('template/tablist');
}
