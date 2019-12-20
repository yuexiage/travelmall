<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'travel';
$do = in_array($do, array('travel', 'travels_item')) ? $do : 'travel';
load()->func('communication');
$uid  = $_W['member']['uid'];
if($do=='travel'){
    $title = '游记';
    $travels = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_travels')." where uniacid = '{$_W['uniacid']}' and enabled=1" , array(), 'id');
	include $this->template('travels');
}elseif($do=='travels_item'){
    $travel  = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_travels')." where uniacid = '{$_W['uniacid']}' and enabled=1 and id = {$_GPC['id']}");
    $title = $travel['name'];
    $countrys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    $citys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(),'id');

    $season = array('1'=>'春季','2'=>'夏季','3'=>"秋季",'4'=>'冬季');
    include $this->template('travels_item');
}
