<?php
global $_GPC, $_W;
$do     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do     = in_array($do, array('display', 'post', 'delete','return_trip','flight_trip')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize  = 10;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $flight_id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            pdo_update('yuexiage_travelmall_flight', $update, array('flight_id' => $flight_id));
        }
        message('航班排序更新成功！', 'refresh', 'success');
    }

    $params     = [':is_del'=>0];
    $condition  = " and is_del = :is_del";
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND (airline LIKE :keyword) OR (air_num LIKE :keyword) ";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['departure'])) {
        if($_GPC['departure']['parentid'] != '0'){
            $condition .= " AND departure_country = :country_id";
            $params[':country_id'] = $_GPC['departure']['parentid'];
        }
        if($_GPC['departure']['childid'] != '0'){
            $condition .= " AND departure_city = :city_id";
            $params[':city_id'] = $_GPC['departure']['childid'];
        }
    }

    if (!empty($_GPC['stop_city'])) {
        if($_GPC['stop_city']['parentid'] != '0'){
            $condition .= " AND stop_country = :country_id";
            $params[':country_id'] = $_GPC['stop_city']['parentid'];
        }
        if($_GPC['stop_city']['childid'] != '0'){
            $condition .= " AND stop_city = :city_id";
            $params[':city_id'] = $_GPC['stop_city']['childid'];
        }
    }

    if (!empty($_GPC['arrival_city'])) {
        if($_GPC['arrival_city']['parentid'] != '0'){
            $condition .= " AND arrival_country = :country_id";
            $params[':country_id'] = $_GPC['arrival_city']['parentid'];
        }
        if($_GPC['arrival_city']['childid'] != '0'){
            $condition .= " AND arrival_city = :city_id";
            $params[':city_id'] = $_GPC['arrival_city']['childid'];
        }
    }

    /**地区分布**/
    $regions    =   select_country_city_all();
    $citys      =  com_load_cache(array(
        'cache_key'=>'city',
    ));
    /**地区分布结束**/

    $params[':uniacid'] = $_W['uniacid'];
    $flight = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_flight')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    $total  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_flight') . " WHERE uniacid = :uniacid $condition ",$params);
    $pager  = pagination($total, $pindex, $psize);
    if ($_GPC['tp'] == 'flight_trip'){
        include $this->template('template/flightlist');
    }elseif ($_GPC['tp'] == 'return_trip'){
        include $this->template('template/return_trip');
    }else{
        include $this->template('flight');
    }
}elseif ($do == 'post') {
    $flight_id     = $_GPC['id'];
    if(!empty($flight_id)) {
        $flight = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_flight')." WHERE is_del = :is_del and  flight_id =:flight_id AND uniacid = :uniacid",array(
            ':uniacid'  =>$_W['uniacid'],
            ':flight_id'=>$flight_id,
            ':is_del'   =>0
        ));
        if(empty($flight)) {
            message('航班不存在或已删除', '', 'error');
        }
    } else {
        $flight = array(
            'displayorder' => 0
        );
    }

    /**地区分布**/
    if (!empty($flight_id)){
        //编辑的时候下架国家也取出来
        $regions = select_country_city_all();
    }else{
        $regions = select_country_city();
    }
    /**地区分布结束**/

    if (checksubmit('submit')) {
        if (empty($_GPC['airline'])) {
            message('抱歉，请输入航班名称！');
        }

        $data = array(
            'uniacid'           => $_W['uniacid'],
            'airline'           => $_GPC['airline'],
            'displayorder'      => intval($_GPC['displayorder']),
            'enabled'           => intval($_GPC['enabled']),
            'air_num'           => $_GPC['air_num'],
            'aircraft_type'     => $_GPC['aircraft_type'],
            'departure_country' => $_GPC['departure']['parentid'],
            'departure_city'    => $_GPC['departure']['childid'],
            'departure_airport' => $_GPC['departure_airport'],
            'departure_time'    => strtotime($_GPC['departure_time']),
            'stop_country'      => $_GPC['stop_city']['parentid'],
            'stop_city'         => $_GPC['stop_city']['childid'],
            'stop_airport'      => $_GPC['stop_airport'],
            'stop_time'         => $_GPC['stop_time'],
            'arrival_country'   => $_GPC['arrival_city']['parentid'],
            'arrival_city'      => $_GPC['arrival_city']['childid'],
            'arrival_airport'   => $_GPC['arrival_airport'],
            'flight_id'         => $flight_id,
            'arrival_time'      => strtotime($_GPC['arrival_time']),
            'flight_duration'   => $_GPC['flight_duration']
        );

        if (!empty($flight_id)) {
            update('yuexiage_travelmall_flight', $data, array('flight_id' => $flight_id));
        } else {
            $data['flight_id'] = uuid();
            insert('yuexiage_travelmall_flight', $data);
        }
        message('更新航班成功！', $this->createWebUrl('flight', array('op' => 'display')), 'success');
    }
    include $this->template('flight');
}elseif ($do == 'delete') { 
    $flight_id  = $_GPC['id'];
    if(!empty($flight_id)) {
        $flight = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_flight')." WHERE is_del = :is_del and  flight_id =:flight_id AND uniacid = :uniacid",array(
            ':uniacid'  =>$_W['uniacid'],
            ':flight_id'=>$flight_id,
            ':is_del'   =>0
        ));
        if(empty($flight)) {
            message('航班不存在或已删除', '', 'error');
        }
    }else{
        $flight = array(
            'displayorder' => 0
        );
    }

    //如果有线路（上架+下架）存在，拒绝删除
    $count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_offered')." WHERE ( (trip like :flight_id) or (return_trip like :flight_id) ) AND uniacid = :uniacid and is_del = :is_del",
        array(':uniacid'=>$_W['uniacid'],':flight_id'=>"%$flight_id%",':is_del'=>0));
    if(!empty($count)){
        message('被线路使用的航班不能删除', '', 'error');
    }

    update('yuexiage_travelmall_flight', ['is_del'=>1,'flight_id' => $flight_id], array('flight_id' => $flight_id));
    message('删除航班成功！', $this->createWebUrl('flight', array('op' => 'display')), 'success');
}
