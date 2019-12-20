<?php
global $_GPC, $_W;
$kewword  = trim($_GPC['keyword']);
$uid = $_W['member']['uid'];
$params = array();
$params[':uniacid'] = $_W['uniacid'];
$sql = 'SELECT * FROM ' . tablename('yuexiage_travelmall_search_keyword') . " WHERE `uniacid` = :uniacid AND `uid` = '{$uid}' GROUP BY  keyword order by id desc  LIMIT 10 ";
$keywords = pdo_fetchall($sql,$params);
if($kewword !=''){
    if(IS_POST){
        $data=array();
        $data['uid']    = $uid;
        $data['keyword']= $kewword;
        $data['uniacid']= $_W['uniacid'];
        pdo_insert('yuexiage_travelmall_search_keyword', $data);
    }
    //常规 线路
    $sql = 'SELECT id,name,day_night,thumbs,adult_price,description FROM ' . tablename('yuexiage_travelmall_offered') . ' WHERE `uniacid` = :uniacid AND `enabled` = 1 AND name LIKE :name';
    $params = array();
    $params[':uniacid'] = $_W['uniacid'];
    $params[':name']    = "%$kewword%";
    $offereds = pdo_fetchall($sql, $params);

    //自由行
    $sql = 'SELECT id,name,day_night,thumbs,adult_price,description FROM ' . tablename('yuexiage_travelmall_fit') . ' WHERE `uniacid` = :uniacid AND `enabled` = 1 AND name LIKE :name';
    $params = array();
    $params[':uniacid'] = $_W['uniacid'];
    $params[':name']    = "%$kewword%";
    $fits = pdo_fetchall($sql, $params);

    //签证
    $sql = 'SELECT id,name,thumbs FROM ' . tablename('yuexiage_travelmall_visa') . ' WHERE `uniacid` = :uniacid AND `enabled` = 1 AND name LIKE :name';
    $params = array();
    $params[':uniacid'] = $_W['uniacid'];
    $params[':name']    = "%$kewword%";
    $visas = pdo_fetchall($sql, $params);

    //游记
    $sql = 'SELECT id,name,thumbs,season FROM ' . tablename('yuexiage_travelmall_travels') . ' WHERE `uniacid` = :uniacid AND `enabled` = 1 AND name LIKE :name';
    $params = array();
    $params[':uniacid'] = $_W['uniacid'];
    $params[':name']    = "%$kewword%";
    $travels = pdo_fetchall($sql, $params);
}

include $this->template('search');

