<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';

$tab = !empty($_GPC['t']) ? $_GPC['t'] : 'base';
$tab = in_array($tab, array('base', 'stroke','date')) ? $tab : 'base';

$pindex = max(1, intval($_GPC['page']));
$psize = 5;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            pdo_update('yuexiage_travelmall_offered', $update, array('id' => $id));
        }
        message('景点排序更新成功！', 'refresh', 'success');
    }

    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['offered'])) {
        if($_GPC['offered']['parentid'] != '0'){
            $condition .= " AND country_id = :country_id";
            $params[':country_id'] = $_GPC['offered']['parentid'];
        }
        if($_GPC['offered']['childid'] != '0'){
            $condition .= " AND city_id = :city_id";
            $params[':city_id'] = $_GPC['offered']['childid'];
        }
    }

    /**地区分布**/
    $countrys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    $citys  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array());
    foreach ($citys as $city) {
        $_citys[$city['pcate']][] = $city;
    }
    /**地区分布结束**/

    $offered = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    
    $total   =pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_offered') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
    $pager   = pagination($total, $pindex, $psize);
}
include $this->template('offeredlist');