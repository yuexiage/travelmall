<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do = in_array($do, array('display', 'post', 'businessmen_categorys','businessmen_info','businessmen_order')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize = 5;
load()->func('tpl');

$state = uni_permission($_W['uid'],$_W['uniacid']);
$permission = pdo_fetchall("SELECT * FROM ".tablename('uni_account_users')." AS au LEFT JOIN ".tablename('users')." as u ON au.uid = u.uid WHERE au.uniacid = '{$_W['uniacid']}' and au.role = :role  ORDER BY au.uid ASC, au.role DESC", array(':role' => 'operator'), 'uid');

if ($do == 'display') {
    if ($_GPC['action']=='businessmen_categorys') {
        //类别
        $categorys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_businessmen_categorys')." WHERE  uniacid = {$_W['uniacid']}");

        include $this->template('businessmen_categorys');
    }if ($_GPC['action']=='businessmen_info') {
        //信息
        $info = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_businessmen_info')." WHERE  uniacid = {$_W['uniacid']}");

        include $this->template('businessmen_info');
    }if ($_GPC['action']=='businessmen_order') {
        //信息
        if($_GPC['keyword']){
            $condition = ' and bi.name like "%'.$_GPC['keyword'].'%"';
        }
        if($state=='operator'){
            $uid = $_W['user']['uid'];//操作员uid
            $info = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_businessmen_info')." WHERE  uniacid = {$_W['uniacid']} and operator='{$uid}' ");
            $condition .= " and bo.recommend_id={$info['id']}";
        }
        
        $info = pdo_fetchall("SELECT bo.*,bi.name FROM ".tablename('yuexiage_travelmall_businessmen_order')." as bo LEFT JOIN  ".tablename('yuexiage_travelmall_businessmen_info')." as bi  ON bo.recommend_id =  bi.id WHERE  bo.uniacid = '{$_W['uniacid']}' $condition ");
        include $this->template('businessmen_order');
    }else{
        include $this->template('businessmen');
    }
}elseif ($do == 'post') {
    $id = intval($_GPC['id']);
        if ($_GPC['action']=='categorys') {
            if(!empty($id)) {
            $category = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_businessmen_categorys')." WHERE id = '{$id}' AND uniacid = {$_W['uniacid']}");
            if(empty($category)) {
                message('类别不存在或已删除', '', 'error');
            }
        } else {
            $activity = array(
                'displayorder' => 0
            );
        }

        if (checksubmit('submit')) {
            if (empty($_GPC['name'])) {
                message('抱歉，请输入分类名称！');
            }

            $data = array(
                'uniacid'           => $_W['uniacid'],
                'name'             => $_GPC['name'],
                'enabled'           => intval($_GPC['enabled']),
                'description'       => $_GPC['description'],
                'first_category'   => $_GPC['first_category'],
                'displayorder'   => $_GPC['displayorder']
            );

            if (!empty($id)) {
                pdo_update('yuexiage_travelmall_businessmen_categorys', $data, array('id' => $id));
            } else {
                pdo_insert('yuexiage_travelmall_businessmen_categorys', $data);
                $id = pdo_insertid();
            }
            message('更新类别成功！', $this->createWebUrl('businessmen', array('op' => 'display','action'=>'businessmen_categorys')), 'success');
        }

        include $this->template('businessmen_categorys');
    }elseif ($_GPC['action']=='info') {
        if(!empty($id)) {
        $category_info = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_businessmen_info')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
        }
        if(empty($activity)) {
            //message('活动不存在或已删除', '', 'error');
        }

    /**地区分布**/
    $first_categorys = array();

    $first_categorys =array (
        '1' => 
            array (
                'id' => '1',
                'name' => '吃'
            ),
        '2' => 
            array (
            'id' => '2',
            'name' => '住',
        ),
        '3' => 
            array (
            'id' => '3',
            'name' => '行',
        ),
        '4' => 
            array (
            'id' => '4',
            'name' => '游',
        ),
        '5' => 
            array (
            'id' => '5',
            'name' => '购',
        ),
        '6' => 
            array (
            'id' => '6',
            'name' => '娱',
        ),
    );

    $categorys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_businessmen_categorys')." WHERE uniacid = {$_W['uniacid']} and enabled='1' ");
    foreach ($categorys as $category) {
        $_categorys[$category['first_category']][] = $category;
    }

        if (checksubmit('submit')) {
            if (empty($_GPC['name'])) {
                message('抱歉，请输入商家名称！');
            }

            $data = array(
                'uniacid'           => $_W['uniacid'],
                'name'             => $_GPC['name'],
                'enabled'           => intval($_GPC['enabled']),
                'description'       => $_GPC['description'],
                'displayorder'            => $_GPC['displayorder'],
                'address'            => $_GPC['address'],
                'operator'            => $_GPC['operator'],
                'coordinate'            => trim($_GPC['coordinate']),
                'tel'            => $_GPC['tel'],
                'img'            => $_GPC['img'],
                'features'       => $_GPC['features'],
                'businessmen_category_1'           => $_GPC['businessmen_category']['parentid'],
                'businessmen_category_2'           => $_GPC['businessmen_category']['childid']
            );

            if(!$_GPC['code']){
                $data['code'] = $this->generate_password(8);
            }

            if (!empty($id)) {
                pdo_update('yuexiage_travelmall_businessmen_info', $data, array('id' => $id));
            } else {
                pdo_insert('yuexiage_travelmall_businessmen_info', $data);
                $id = pdo_insertid();
            }
            message('更新活动成功！', $this->createWebUrl('businessmen', array('op' => 'display','action'=>'businessmen_info')), 'success');
        }

        include $this->template('businessmen_info');
    }
}elseif ($do == 'receive_bigwheel') { 

    $id = intval($_GPC['id']);
    if(!empty($id)) {
        $activity = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_bigwheel_winner')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
        if(empty($activity)) {
            message('名单不存在或已删除', '', 'error');
        }
    }else{
        $activity = array(
            'displayorder' => 0
        );
    }
    $data = array();
    $data['status'] = 2;
    $data['receive'] = time();
    pdo_update('yuexiage_travelmall_bigwheel_winner',$data,  array('id' => $id));
    message('更新成功！', $this->createWebUrl('activity', array('op' => 'display','action'=>'bigwheellist')), 'success');
}elseif ($do == 'receive_scratchcard') { 

    $id = intval($_GPC['id']);
    if(!empty($id)) {
        $activity = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_scratchcard_winner')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
        if(empty($activity)) {
            message('名单不存在或已删除', '', 'error');
        }
    }else{
        $activity = array(
            'displayorder' => 0
        );
    }
    $data = array();
    $data['status'] = 2;
    $data['receive'] = time();
    pdo_update('yuexiage_travelmall_scratchcard_winner',$data,  array('id' => $id));
    message('更新成功！', $this->createWebUrl('activity', array('op' => 'display','action'=>'scratchcardlist')), 'success');
}
