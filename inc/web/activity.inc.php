<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do = in_array($do, array('display', 'post', 'receive_bigwheel','receive_scratchcard')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize = 5;
load()->func('tpl');
if ($do == 'display') {
    if ($_GPC['action']=='bigwheel') {
        //大转盘
        $activity = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_bigwheel')." WHERE  uniacid = {$_W['uniacid']}");

        include $this->template('bigwheel');
    }if ($_GPC['action']=='scratchcard') {
        //大转盘
        $activity = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_scratchcard')." WHERE  uniacid = {$_W['uniacid']}");

        include $this->template('scratchcard');
    }elseif($_GPC['action']=='scratchcardlist'){
        //刮刮乐
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (realname LIKE :realname) OR  (tel LIKE :tel)";
            $params[':realname'] = "%{$_GPC['keyword']}%";
            $params[':tel'] = "%{$_GPC['keyword']}%";
        }

        $scratchcardlist = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_scratchcard_winner')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
        $gifts=array('0'=>'one','1'=>'two','2'=>'three','3'=>'four','4'=>'five','5'=>'six');
        foreach ($scratchcardlist as $key => $row) {
            foreach ($gifts as $gift) {
               if($row['tab_id']=='2' || $row['tab_id']=='4'){
                $scratchcardlist[$key]['coupon'] = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE  enabled=1 and uniacid = {$_W['uniacid']} and id={$row['award']}");
                }
            }
        }

        $total=pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_scratchcard_winner') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('scratchcardlist');
    }elseif($_GPC['action']=='bigwheellist'){
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (realname LIKE :realname) OR  (tel LIKE :tel)";
            $params[':realname'] = "%{$_GPC['keyword']}%";
            $params[':tel'] = "%{$_GPC['keyword']}%";
        }

        $bigwheellist = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_bigwheel_winner')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
        $gifts=array('0'=>'one','1'=>'two','2'=>'three','3'=>'four','4'=>'five','5'=>'six');
        foreach ($bigwheellist as $key => $row) {
            foreach ($gifts as $gift) {
               if($row['tab_id']=='2' || $row['tab_id']=='4'){
                $bigwheellist[$key]['coupon'] = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE  enabled=1 and uniacid = {$_W['uniacid']} and id={$row['award']}");
                }
            }
        }

        $total=pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_bigwheel_winner') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('bigwheellist');
    }else{
        include $this->template('activity_pane');
    }
}elseif ($do == 'post') {
    $id = intval($_GPC['id']);
        if ($_GPC['action']=='bigwheel') {
            if(!empty($id)) {
            $activity = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_bigwheel')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
            if(empty($activity)) {
                message('活动不存在或已删除', '', 'error');
            }
        } else {
            $activity = array(
                'displayorder' => 0
            );
        }
        //查询优惠券
        $vouchers = pdo_fetchall("SELECT id,name FROM ".tablename('yuexiage_travelmall_coupon')." WHERE theme_id = 1 AND enabled=1 and uniacid = {$_W['uniacid']}");

        $coupons = pdo_fetchall("SELECT id,name FROM ".tablename('yuexiage_travelmall_coupon')." WHERE theme_id = 2 AND enabled=1 and uniacid = {$_W['uniacid']}");

        if (checksubmit('submit')) {
            if (empty($_GPC['title'])) {
                message('抱歉，请输入活动名称！');
            }

            $data = array(
                'uniacid'           => $_W['uniacid'],
                'title'             => $_GPC['title'],
                'enabled'           => intval($_GPC['enabled']),
                'description'       => $_GPC['description'],
                'datelimit_start'   => $_GPC['datelimit']['start'],
                'datelimit_end'   => $_GPC['datelimit']['end'],
                'follow'            => $_GPC['follow'],
               

                'c_type_one'   => $_GPC['c_type_one'],
                'c_name_one'   => $_GPC['c_name_one'],
                'c_num_one'    => $_GPC['c_num_one'],
                'c_rate_one'   => $_GPC['c_rate_one'],

                'c_type_two'   => $_GPC['c_type_two'],
                'c_name_two'   => $_GPC['c_name_two'],
                'c_num_two'    => $_GPC['c_num_two'],
                'c_rate_two'   => $_GPC['c_rate_two'],

                'c_type_three'   => $_GPC['c_type_three'],
                'c_name_three'   => $_GPC['c_name_three'],
                'c_num_three'    => $_GPC['c_num_three'],
                'c_rate_three'   => $_GPC['c_rate_three'],

                'c_type_four'   => $_GPC['c_type_four'],
                'c_name_four'   => $_GPC['c_name_four'],
                'c_num_four'    => $_GPC['c_num_four'],
                'c_rate_four'   => $_GPC['c_rate_four'],

                'c_type_five'   => $_GPC['c_type_five'],
                'c_name_five'   => $_GPC['c_name_five'],
                'c_num_five'    => $_GPC['c_num_five'],
                'c_rate_five'   => $_GPC['c_rate_five'],

                'c_type_six'   => $_GPC['c_type_six'],
                'c_name_six'   => $_GPC['c_name_six'],
                'c_num_six'    => $_GPC['c_num_six'],
                'c_rate_six'   => $_GPC['c_rate_six'],
                'award_times'   => $_GPC['award_times'],
                'number_times'   => $_GPC['number_times'],                
                'most_num_times'   => $_GPC['most_num_times'],
                'show_num'   => $_GPC['show_num']
            );

            if (!empty($id)) {
                pdo_update('yuexiage_travelmall_bigwheel', $data, array('id' => $id));
            } else {
                pdo_insert('yuexiage_travelmall_bigwheel', $data);
                $id = pdo_insertid();
            }
            message('更新活动成功！', $this->createWebUrl('activity', array('op' => 'display','action'=>'bigwheel')), 'success');
        }

        include $this->template('bigwheel');
    }elseif ($_GPC['action']=='scratchcard') {

        if(!empty($id)) {
        $activity = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_scratchcard')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
        }
        if(empty($activity)) {
            message('活动不存在或已删除', '', 'error');
        }
    
        //查询优惠券
        $vouchers = pdo_fetchall("SELECT id,name FROM ".tablename('yuexiage_travelmall_coupon')." WHERE theme_id = 1 AND enabled=1 and uniacid = {$_W['uniacid']}");

        $coupons = pdo_fetchall("SELECT id,name FROM ".tablename('yuexiage_travelmall_coupon')." WHERE theme_id = 2 AND enabled=1 and uniacid = {$_W['uniacid']}");

        if (checksubmit('submit')) {
            if (empty($_GPC['title'])) {
                message('抱歉，请输入活动名称！');
            }

            $data = array(
                'uniacid'           => $_W['uniacid'],
                'title'             => $_GPC['title'],
                'enabled'           => intval($_GPC['enabled']),
                'description'       => $_GPC['description'],
                'datelimit_start'   => $_GPC['datelimit']['start'],
                'datelimit_end'   => $_GPC['datelimit']['end'],
                'follow'            => $_GPC['follow'],
               

                'c_type_one'   => $_GPC['c_type_one'],
                'c_name_one'   => $_GPC['c_name_one'],
                'c_num_one'    => $_GPC['c_num_one'],
                'c_rate_one'   => $_GPC['c_rate_one'],

                'c_type_two'   => $_GPC['c_type_two'],
                'c_name_two'   => $_GPC['c_name_two'],
                'c_num_two'    => $_GPC['c_num_two'],
                'c_rate_two'   => $_GPC['c_rate_two'],

                'c_type_three'   => $_GPC['c_type_three'],
                'c_name_three'   => $_GPC['c_name_three'],
                'c_num_three'    => $_GPC['c_num_three'],
                'c_rate_three'   => $_GPC['c_rate_three'],

                'c_type_four'   => $_GPC['c_type_four'],
                'c_name_four'   => $_GPC['c_name_four'],
                'c_num_four'    => $_GPC['c_num_four'],
                'c_rate_four'   => $_GPC['c_rate_four'],

                'c_type_five'   => $_GPC['c_type_five'],
                'c_name_five'   => $_GPC['c_name_five'],
                'c_num_five'    => $_GPC['c_num_five'],
                'c_rate_five'   => $_GPC['c_rate_five'],

                'c_type_six'   => $_GPC['c_type_six'],
                'c_name_six'   => $_GPC['c_name_six'],
                'c_num_six'    => $_GPC['c_num_six'],
                'c_rate_six'   => $_GPC['c_rate_six'],
                'award_times'   => $_GPC['award_times'],
                'number_times'   => $_GPC['number_times'],                
                'most_num_times'   => $_GPC['most_num_times'],
                'show_num'   => $_GPC['show_num']
            );

            if (!empty($id)) {
                pdo_update('yuexiage_travelmall_scratchcard', $data, array('id' => $id));
            } else {
                pdo_insert('yuexiage_travelmall_scratchcard', $data);
                $id = pdo_insertid();
            }
            message('更新活动成功！', $this->createWebUrl('activity', array('op' => 'display','action'=>'scratchcard')), 'success');
        }

        include $this->template('scratchcard');
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
