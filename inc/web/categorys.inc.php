<?php
global $_GPC, $_W;
$do     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do     = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize  = 10;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $category_id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            pdo_update('yuexiage_travelmall_categorys', $update, array('category_id' => $category_id));
        }
        message('分类排序更新成功！', 'refresh', 'success');
    }

    $params     = [':is_del'=>0];
    $condition  = " and is_del = :is_del";
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    $params[':uniacid'] = $_W['uniacid'];
    $categorys  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_categorys')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    $total      = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_categorys') . " WHERE uniacid = :uniacid $condition ",$params);
    $pager      = pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    $category_id = $_GPC['id'];
    if(!empty($category_id)) {
        $categorys = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_categorys')." WHERE category_id = :category_id AND uniacid = :uniacid and is_del = :is_del",array(
            ':uniacid'      =>$_W['uniacid'],
            ':category_id'  =>$category_id,
            ':is_del'       =>0
        ));

        if(empty($categorys)) {
            message('分类不存在或已删除', '', 'error');
        }
    } else {
        $categorys = array(
            'displayorder' => 0
        );
    }
    if (checksubmit('submit')) {
        if (empty($_GPC['name'])) {
            message('抱歉，请输入分类名称！');
        }
        $data = array(
            'uniacid'       => $_W['uniacid'],
            'name'          => $_GPC['name'],
            'displayorder'  => intval($_GPC['displayorder']),
            'enabled'       => intval($_GPC['enabled']),
            'img'           => $_GPC['img'],
            'description'   => $_GPC['description']
        );
        if (!empty($category_id)) {
            pdo_update('yuexiage_travelmall_categorys', $data, array('category_id' => $category_id));
        } else {
            $data['category_id'] = uuid();
            pdo_insert('yuexiage_travelmall_categorys', $data);
            //$id = pdo_insertid();
        }
        message('更新分类成功！', $this->createWebUrl('categorys', array('op' => 'display')), 'success');
    }
}elseif ($do == 'delete') {
    $category_id = $_GPC['id'];
    if(!empty($category_id)) {
        $categorys = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_categorys')." WHERE category_id = :category_id AND uniacid = :uniacid and is_del = :is_del",array(
            ':uniacid'      =>$_W['uniacid'],
            ':category_id'  =>$category_id,
            ':is_del'       =>0
        ));
        if(empty($categorys)) {
            message('分类不存在或已删除', '', 'error');
        }
    }else{
        $categorys = array(
            'displayorder' => 0
        );
    }
    update('yuexiage_travelmall_categorys', ['is_del'=>1,'category_id' => $category_id], array('category_id' => $category_id));
    message('删除分类成功！', $this->createWebUrl('categorys', array('op' => 'display')), 'success');
}
include $this->template('categorys');