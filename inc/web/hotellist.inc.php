<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize = 10;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $hotel_id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            pdo_update('yuexiage_travelmall_hotel', $update, array('hotel_id' => $hotel_id));
        }
        message('酒店排序更新成功！', 'refresh', 'success');
    }

    $params = [':is_del'=>0];
    $condition = " and is_del = :is_del";
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['hotel'])) {
        if($_GPC['hotel']['parentid'] != '0'){
            $condition .= " AND country_id = :country_id";
            $params[':country_id'] = $_GPC['hotel']['parentid'];
        }
        if($_GPC['hotel']['childid'] != '0'){
            $condition .= " AND city_id = :city_id";
            $params[':city_id'] = $_GPC['hotel']['childid'];
        }
    }

    /**地区分布**/
    $regions    =   select_country_city_all();
    $citys     =  com_load_cache(array(
        'cache_key'=>'city',
    ));
    /**地区分布结束**/

    $params[':uniacid'] = $_W['uniacid'];
    $hotel  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_hotel')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    $total  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_hotel') . " WHERE uniacid = :uniacid $condition ",$params);
    $pager  = pagination($total, $pindex, $psize);
}
include $this->template('template/hotellist');