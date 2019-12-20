<?php
global $_GPC, $_W;
$title      = '我的订单';
$uid  = $_W['member']['uid'];
$orders = pdo_fetchall('SELECT *,o.status as ost,o.id as order_id FROM ' . tablename('yuexiage_travelmall_orders') . ' as o LEFT JOIN ' . tablename('yuexiage_travelmall_offered') . ' as s on o.goods_id = s.id WHERE  o.uid=:uid order by type DESC', array(':uid'=>$uid));

include $this->template('myorder');

