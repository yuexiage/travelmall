<?php
global $_GPC, $_W;
$title = '选择城市';
$citys = pdo_fetchall("SELECT tc.* FROM ".tablename('yuexiage_travelmall_city')." as tc LEFT JOIN ".tablename('yuexiage_travelmall_country')." as tu ON tc.pcate = tu.id WHERE tc.uniacid = '{$_W['uniacid']}' and tc.enabled = 1 and tu.enabled = 1 and tu.name='中国' ORDER BY tc.displayorder ASC, tc.id ASC" , array(), 'id');
include $this->template('city');