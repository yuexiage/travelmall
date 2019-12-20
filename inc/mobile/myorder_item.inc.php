<?php
global $_GPC, $_W;
$title      = '我的订单';
$id = $_GPC['id'];
$order = pdo_fetch('SELECT *,o.status as ost,o.id as order_id FROM ' . tablename('yuexiage_travelmall_orders') . ' as o LEFT JOIN ' . tablename('yuexiage_travelmall_offered') . ' as s on o.goods_id = s.id WHERE  o.id=:id', array(':id'=>$id));

include $this->template('myorder_item');

