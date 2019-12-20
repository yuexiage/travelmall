<?php
global $_GPC, $_W;
$title      = '优惠券';
$uid  = $_W['member']['uid'];
$coupons = pdo_fetchall('SELECT * FROM ' . tablename('yuexiage_travelmall_coupon_user') . ' as cu LEFT JOIN '.tablename('yuexiage_travelmall_coupon').' as c ON c.id = cu.country_id WHERE  cu.uid=:uid and c.theme_id = 2', array(':uid'=>$uid));

include $this->template('mycoupon');

