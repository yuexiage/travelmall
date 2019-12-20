<?php
global $_GPC, $_W;
$title      = '优惠券';
$uid  = $_W['member']['uid'];
$collections = pdo_fetchall('SELECT * FROM ' . tablename('yuexiage_travelmall_collection') . ' as cu LEFT JOIN ' . tablename('yuexiage_travelmall_offered') . ' as of on cu.offered_id = of.id  WHERE  cu.uid=:uid', array(':uid'=>$uid));

include $this->template('mycollection');

