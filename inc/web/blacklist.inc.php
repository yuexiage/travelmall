<?php
global $_GPC, $_W;
$dos = array('display', 'post','del', 'add', 'group', 'credit_record', 'credit_stat');
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do = in_array($do, $dos) ? $do : 'display';
load()->model('mc');
if($do == 'display') {
    $_W['page']['title'] = '会员列表 - 会员 - 会员中心';
    $groups = mc_groups();
    $pindex = max(1, intval($_GPC['page']));
    $psize = 50;
    $condition = '';
    $params = array(':uniacid' => $_W['uniacid']);
    $starttime = empty($_GPC['createtime']['start']) ? strtotime('-90 days') : strtotime($_GPC['createtime']['start']);
    $endtime = empty($_GPC['createtime']['end']) ? TIMESTAMP + 86399 : strtotime($_GPC['createtime']['end']) + 86399;
    $condition .= " AND createtime >= {$starttime} AND createtime <= {$endtime}";
    $condition .= empty($_GPC['username']) ? '' : " AND ((`uid` = :openid) OR ( `realname` LIKE :username ) OR ( `nickname` LIKE :username ) OR ( `mobile` LIKE :username ))";
    if (!empty($_GPC['username'])) {
        $params[':username'] =  '%'.trim($_GPC['username']).'%';
        if (!is_numeric(trim($_GPC['username']))) {
            $uid = pdo_fetchcolumn('SELECT `uid` FROM'. tablename('mc_mapping_fans')." WHERE openid = :openid", array(':openid' => trim($_GPC['username'])));
            $params[':openid'] =  $uid;
        } else {
            $params[':openid'] =  $_GPC['username'];
        }
    }
    if (!empty($_GPC['uid'])) {
        $condition .= " AND uid = :uid";
        $params[':uid'] = $_GPC['uid'];
    }
    $condition .= intval($_GPC['groupid']) > 0 ?  " AND `groupid` = '".intval($_GPC['groupid'])."'" : '';

    $sql = "SELECT uid, uniacid, groupid, realname, nickname, email, mobile, credit1, credit2, credit6, createtime  FROM ".tablename('mc_members')." WHERE uniacid = :uniacid ".$condition." ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    if(!empty($list)) {
        foreach($list as &$li) {
            if(empty($li['email']) || (!empty($li['email']) && substr($li['email'], -6) == 'we7.cc' && strlen($li['email']) == 39)) {
                $li['email_effective'] = 0;
            } else {
                $li['email_effective'] = 1;
            }
        }
    }
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('mc_members')." WHERE uniacid = '{$_W['uniacid']}' ".$condition);
    $pager = pagination($total, $pindex, $psize);
    $stat['total'] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
    $stat['today'] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime('today'), ':endtime' => strtotime('today') + 86399));
    $stat['yesterday'] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime('today')-86399, ':endtime' => strtotime('today')));
}
if($do == 'post') {
    $_W['page']['title'] = '黑名单操作';
    $uid = intval($_GPC['uid']);
    $blacklist = pdo_fetch('SELECT* FROM ' . tablename('yuexiage_travelmall_blacklist') . ' WHERE uniacid = :uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'],':uid'=>$uid));
    if (checksubmit('submit')) {
        $data = array();
        $data['uid']            = $uid;
        $data['comment']        = $_GPC['comment']?$_GPC['comment']:'0';
        $data['place_an_order'] = $_GPC['place_an_order']?$_GPC['place_an_order']:'0';
        $data['withdraw']       = $_GPC['withdraw']?$_GPC['withdraw']:'0';
        $data['visa']           = $_GPC['visa']?$_GPC['visa']:'0';
        $data['withdraw_code']  = $_GPC['withdraw_code']?$_GPC['withdraw_code']:'0';
        $data['uniacid']        = $_W['uniacid'];
        
        $blacklist = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_blacklist') . ' WHERE uniacid = :uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'],':uid'=>$uid));
        if($blacklist){
            pdo_update('yuexiage_travelmall_blacklist', $data, array('uid' => $uid));
            cache_delete('blacklist:'.$uid);
        }else{
            pdo_insert('yuexiage_travelmall_blacklist', $data);
        }

        message('更新资料成功！', referer(), 'success');
    }
}
include $this->template('blacklist');