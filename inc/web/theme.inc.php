<?php
global $_GPC, $_W;
$do     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do     = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize = 10;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $theme_id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            pdo_update('yuexiage_travelmall_theme', $update, array('theme_id' => $theme_id));
        }
        message('主题排序更新成功！', 'refresh', 'success');
    }

    $params     = [':is_del'=>0];
    $condition  = " and is_del = :is_del";
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    $params[':uniacid'] = $_W['uniacid'];
    $theme  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_theme')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);

    $total  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_theme') . " WHERE uniacid = :uniacid $condition ",$params);
    $pager  = pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    $theme_id = $_GPC['id'];
    if(!empty($theme_id)) {
        $theme = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_theme')." WHERE theme_id = :theme_id AND uniacid = :uniacid and is_del = :is_del",array(
            ":uniacid"      => $_W['uniacid'],
            ":theme_id"     =>$theme_id,
            ':is_del'       =>0,
        ));
        if(empty($theme)) {
            message('主题不存在或已删除', '', 'error');
        }
    } else {
        $theme = array(
            'displayorder' => 0
        );
    }
    if (checksubmit('submit')) {
        if (empty($_GPC['name'])) {
            message('抱歉，请输入主题名称！');
        }
        $data = array(
            'uniacid'       => $_W['uniacid'],
            'name'          => $_GPC['name'],
            'img'           => $_GPC['img'],
            'displayorder'  => intval($_GPC['displayorder']),
            'enabled'       => intval($_GPC['enabled']),
            'description'   => $_GPC['description']
        );
        if (!empty($id)) {
            pdo_update('yuexiage_travelmall_theme', $data, array('theme_id' => $theme_id));
        } else {
            $data['theme_id'] = uuid();
            pdo_insert('yuexiage_travelmall_theme', $data);
            //$id = pdo_insertid();
        }
        message('更新主题成功！', $this->createWebUrl('theme', array('op' => 'display')), 'success');
    }
}elseif ($do == 'delete') { 
    $theme_id = $_GPC['id'];
    if(!empty($theme_id)) {
        $theme = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_theme')." WHERE theme_id = :theme_id AND uniacid = :uniacid and is_del = :is_del",array(
            ":uniacid"      => $_W['uniacid'],
            ":theme_id"     => $theme_id,
            ':is_del'       =>0,
        ));
        if(empty($theme)) {
            message('主题不存在或已删除', '', 'error');
        }
    }else{
        $theme = array(
            'displayorder' => 0
        );
    }
    //如果有线路（上架+下架）存在，拒绝删除
    $count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_offered')." WHERE theme_id =:theme_id AND uniacid = :uniacid",
        array(':uniacid'=>$_W['uniacid'],':theme_id'=>$theme_id));
    if(!empty($count)){
        message('被线路使用的主题不能删除', '', 'error');
    }
    pdo_update('yuexiage_travelmall_theme', ['is_del'=>1], array('theme_id' => $theme_id));
    message('删除主题成功！', $this->createWebUrl('theme', array('op' => 'display')), 'success');
}
include $this->template('theme');