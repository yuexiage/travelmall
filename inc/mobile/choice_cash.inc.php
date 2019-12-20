<?php
global $_GPC, $_W;
checkauth();
$id = $_GPC['id'];
$uid    = $_W['member']['uid'];
$sql = "SELECT tcu.*,tc.name,tc.amount FROM ".tablename('yuexiage_travelmall_coupon_user')." as tcu LEFT JOIN ".tablename('yuexiage_travelmall_coupon')." as tc ON tcu.country_id = tc.id WHERE tcu.uid = :uid and tcu.rid = :rid and tcu.theme_id=1 and tcu.uniacid = :uniacid and tcu.enabled >0 and tcu.endtime >".time();
$cashs = pdo_fetchall($sql, array(':uid'=>$uid,':uniacid' => $_W['uniacid'],':rid'=>$id));

include $this->template('choice_coupon');

