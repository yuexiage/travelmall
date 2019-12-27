<?php
global $_GPC, $_W;
$do     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do     = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize  = 10;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $hotel_id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            update('yuexiage_travelmall_hotel', $update, array('hotel_id' => $hotel_id));
        }
        message('酒店排序更新成功！', 'refresh', 'success');
    }
    $params     = [':is_del'=>0];
    $condition  = " and is_del = :is_del";
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
    /**地区分布结束**/
    $params[':uniacid'] = $_W['uniacid'];
    $hotel  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_hotel')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    $total  = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_hotel') . " WHERE uniacid = :uniacid $condition ",$params);
    $pager  = pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    $hotel_id= $_GPC['id'];
    if(!empty($hotel_id)) {
        $hotel = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_hotel')." WHERE is_del = :is_del and  hotel_id =:hotel_id AND uniacid = :uniacid",array(
            ':uniacid'=>$_W['uniacid'],
            ':hotel_id'=>$hotel_id,
            ':is_del'=>0
        ));
        if(empty($hotel)) {
            message('酒店不存在或已删除', '', 'error');
        }
    } else {
        $hotel = array(
            'displayorder' => 0
        );
    }
    //解析图片内容
    $hotel['thumbs']        = iunserializer($hotel['thumbs']);
    $hotel['facilities']    = iunserializer($hotel['facilities']);
    $hotel['service']       = iunserializer($hotel['service']);

    /**地区分布**/
    if (!empty($hotel_id)){
        //编辑的时候下架国家也取出来
        $regions = select_country_city_all();
    }else{
        $regions = select_country_city();
    }
    /**地区分布结束**/
    if (checksubmit('submit')) {
        if (empty($_GPC['name'])) {
            message('抱歉，请输入酒店名称！');
        }

        $data = array(
            'uniacid'       => $_W['uniacid'],
            'name'          => $_GPC['name'],
            'displayorder'  => intval($_GPC['displayorder']),
            'enabled'       => intval($_GPC['enabled']),
            'country_id'    => $_GPC['hotel']['parentid'],
            'city_id'       => $_GPC['hotel']['childid'],
            'thumbs'        => iserializer($_GPC['thumbs']),
            'facilities'    => iserializer($_GPC['facilities']),
            'service'       => iserializer($_GPC['service']),
            'wifi'          => $_GPC['wifi'],
            'park'          => $_GPC['park'],
            'address'       => $_GPC['address'],
            'coordinate'    => $_GPC['coordinate'],
            'contact'       => $_GPC['contact'],
            'star'          => $_GPC['star'],
            'description'   => $_GPC['description']
        );

        if (!empty($hotel_id)) {
            update('yuexiage_travelmall_hotel', $data, array('hotel_id' => $hotel_id));
        } else {
            $data['hotel_id'] = uuid();
            insert('yuexiage_travelmall_hotel', $data);
        }
        message('更新酒店成功！', $this->createWebUrl('hotel', array('op' => 'display')), 'success');
    }
}elseif ($do == 'delete') { 
    $hotel_id = $_GPC['id'];
    if(!empty($hotel_id)) {
        $hotel = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_hotel')." WHERE is_del = :is_del and  hotel_id = :hotel_id AND uniacid = :uniacid",array(":hotel_id"=>$hotel_id,':uniacid'=>$_W['uniacid'],':is_del'=>0));
        if(empty($hotel)) {
            message('酒店不存在或已删除', '', 'error');
        }
    }else{
        $hotel = array(
            'displayorder' => 0
        );
    }

    //如果有线路（上架+下架）存在，拒绝删除
    //TODO 删除规则待完善
    /*$count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_offered')." WHERE hotel_id =:hotel_id AND uniacid = :uniacid",
        array(':uniacid'=>$_W['uniacid'],':hotel_id'=>$hotel_id));
    if(!empty($count)){
        message('被线路使用的酒店不能删除', '', 'error');
    }*/

    update('yuexiage_travelmall_hotel', ['is_del'=>1], array('hotel_id' => $hotel_id));
    message('删除酒店成功！', $this->createWebUrl('hotel', array('op' => 'display')), 'success');
}
include $this->template('hotel');