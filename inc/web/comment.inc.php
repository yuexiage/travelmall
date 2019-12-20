<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize = 5;
if ($do == 'display') {
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND tc.content LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['type']) && $_GPC['type']!='-1') {
        $condition .= " AND tc.type = :type";
        $params[':type'] = $_GPC['type'];
    }

    if (!empty($_GPC['status']) && $_GPC['status']!='-1') {
        $condition .= " AND tc.status = :status";
        $params[':status'] = $_GPC['status'];
    }

    $comment = pdo_fetchall("SELECT tc.*,mm.realname,mm.nickname,mm.mobile FROM ".tablename('yuexiage_travelmall_comment')." as tc LEFT JOIN ".tablename('mc_members')." as mm ON tc.uid = mm.uid WHERE tc.uniacid = '{$_W['uniacid']}' $condition ORDER BY  tc.createtime DESC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    
    foreach($comment as &$com){
        if($com['type']=='1'){
            //常规线路
            $o = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE uniacid = '{$_W['uniacid']}' and id = '{$com['goods_id']}' ");
            $com['good'] = $o;
        }else{
            //自由行
        }
    }

    $total=pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_comment') . " as tc WHERE tc.uniacid = '{$_W['uniacid']}' $condition ",$params);
    $pager = pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    $id = intval($_GPC['id']);
    if(!empty($id)) {
        $comment = pdo_fetch("SELECT tc.*,mm.realname,mm.nickname,mm.mobile,o.name FROM ".tablename('yuexiage_travelmall_comment')." as tc LEFT JOIN ".tablename('mc_members')." as mm ON tc.uid = mm.uid LEFT JOIN ".tablename('yuexiage_travelmall_offered')." as o ON o.id = tc.goods_id WHERE tc.uniacid = '{$_W['uniacid']}' and  tc.id = '$id' ");
        if(empty($comment)) {
            message('评论不存在或已删除', '', 'error');
        }
    } else {
        $comment = array(
            'displayorder' => 0
        );
    }

    if (checksubmit('submit')) {
        if($_GPC['submit']=='通过'){
            $status = 1;
        }else{
            $status = 0;
        }
        $data = array(
            'status'   => $status
        );

        pdo_update('yuexiage_travelmall_comment', $data, array('id' => $id));
        message('更新评论成功！', $this->createWebUrl('comment', array('op' => 'display')), 'success');
    }
}elseif ($do == 'delete') { 
    $id = intval($_GPC['id']);
    if(!empty($id)) {
        $visa = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_comment')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
        if(empty($visa)) {
            message('评论不存在或已删除', '', 'error');
        }
    }else{
        $visa = array(
            'displayorder' => 0
        );
    }
    pdo_delete('yuexiage_travelmall_comment',  array('id' => $id));
    message('删除评论成功！', $this->createWebUrl('comment', array('op' => 'display')), 'success');
}
include $this->template('comment');