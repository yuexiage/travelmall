<?php
global $_GPC, $_W;
$do     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do     = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$tab    = !empty($_GPC['t']) ? $_GPC['t'] : 'base';
$tab    = in_array($tab, array('base', 'stroke','date')) ? $tab : 'base';
$pindex = max(1, intval($_GPC['page']));
$psize  = 10;
if ($do == 'display') {
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $offered_id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            update('yuexiage_travelmall_offered', $update, array('offered_id' => $offered_id));
        }
        message('景点排序更新成功！', 'refresh', 'success');
    }

    $params     = [':is_del'=>0];
    $condition  = " and is_del = :is_del";
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['offered'])) {
        if($_GPC['offered']['parentid'] != '0'){
            $condition .= " AND departure_country = :departure_country";
            $params[':departure_country'] = $_GPC['offered']['parentid'];
        }
        if($_GPC['offered']['childid'] != '0'){
            $condition .= " AND departure_city = :departure_city";
            $params[':departure_city'] = $_GPC['offered']['childid'];
        }
    }

    if(isset($_GPC['category_id']) && $_GPC['category_id'] != '-1'){
        $condition .= " AND category_id = :category_id";
        $params[':category_id'] = $_GPC['category_id'];
    }

    /**地区分布**/
    $regions   =  select_country_city_all();
    $citys     =  com_load_cache(array(
        'cache_key'=>'city',
    ));
    /**地区分布结束**/

    $params[':uniacid'] = $_W['uniacid'];

    //分类
    $categorys  =  com_load_cache(array(
        'cache_key'=>'category',
    ));
    $offered    =   pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    $total      =   pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_offered') . " WHERE uniacid = :uniacid $condition ",$params);
    $pager      =   pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    $offered_id     = $_GPC['id'];
    if(!empty($offered_id)) {
        $offered    = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE offered_id = :offered_id AND uniacid = :uniacid and is_del = :is_del",array(
            ':uniacid'      =>$_W['uniacid'],
            ':offered_id'   =>$offered_id,
            ':is_del'       =>0
        ));
        if(empty($offered)) {
            message('参团游不存在或已删除', '', 'error');
        }
        $offered['tabs']        = iunserializer($offered['tabs']);
        $offered['trip']        = iunserializer($offered['trip']);
        $offered['return_trip'] = iunserializer($offered['return_trip']);
        $offered['coupons']     = iunserializer($offered['coupons']);
        $offered['cashs']       = iunserializer($offered['cashs']);
    } else {
        $offered = array(
            'displayorder' => 0
        );
    }

    //解析图片内容
    $offered['thumbs'] = iunserializer($offered['thumbs']);

    //优惠券
    $coupons     =  com_load_cache(array(
        'cache_key'=>'coupon_type',
        'type'     =>2,
    ));

    //代金券
    $cash_coupons =  com_load_cache(array(
        'cache_key'=>'coupon_type',
        'type'     =>1,
    ));

    /**地区分布**/
    if (!empty($offered_id)){
        //编辑的时候下架国家也取出来
        $regions = select_country_city_all();
    }else{
        $regions = select_country_city();
    }
    /**地区分布结束**/

    //主题
    $themes     =  com_load_cache(array(
        'cache_key'=>'theme',
    ));
    echo 222222;
    //分类
    $categorys  =  com_load_cache(array(
        'cache_key'=>'category',
    ));

    //获取行程列表
    if (!empty($offered_id)){
        $strokes  =  com_load_cache(array(
            'cache_key' =>'stroke',
            'offered_id'=>$offered_id,
        ));
    }

    //获取单个行程
    if(!empty($_GPC['stroke_id'])){
        $stroke_id  = $_GPC['stroke_id'];
        $stroke     =  com_load_cache(array(
            'cache_key' =>'stroke_item',
            'stroke_id'=>$stroke_id,
        ));
    }

    //保存基础信息
    if (checksubmit('submit')) {
        if (empty($_GPC['name'])) {
            message('抱歉，请输入参团游名称！');
        }

        $data = array(
            'uniacid'       => $_W['uniacid'],
            'name'          => trim($_GPC['name']),
            'displayorder'  => intval($_GPC['displayorder']),
            'enabled'       => intval($_GPC['enabled']),
            'destination_country'    => $_GPC['offered']['parentid'],
            'destination_city'       => $_GPC['offered']['childid'],
            'departure_country' => $_GPC['departure']['parentid'],
            'departure_city'    => $_GPC['departure']['childid'],
            'thumbs'        => iserializer($_GPC['thumbs']),
            'tabs'          => iserializer($_GPC['tablist']),
            'theme_id'      => $_GPC['theme_id'],
            'category_id'   => $_GPC['category_id'],
            'adult_price'   => $_GPC['adult_price'],
            'child_price'   => $_GPC['child_price'],
            'day_night'     => trim($_GPC['day_night']),
            'lower_shelf'   => strtotime($_GPC['lower_shelf']),
            'upper_shelf'   => $_GPC['upper_shelf'],
            'characteristic'=> $_GPC['characteristic'],
            'contain'       => $_GPC['contain'],
            'not_included'  => $_GPC['not_included'],
            'booked'        => $_GPC['booked'],
            'visa'          => $_GPC['visa'],
            'customer'      => $_GPC['customer'],
            'stock'         => $_GPC['stock'],
            'credit_setting'=> $_GPC['credit_setting'],
            'credit'        => $_GPC['credit'],
            'credit_value_setting'=>$_GPC['credit_value_setting'],
            'credit_value'  => $_GPC['credit_value'],
            'coupons'       => iserializer($_GPC['coupon']),
            'cashs'         => iserializer($_GPC['cashs']),
            'trip'          => iserializer($_GPC['flightlist']),
            'return_trip'   => iserializer($_GPC['returntrip']),
            'comment'       => $_GPC['comment'],
            'description'   => $_GPC['description']
        );

        if (!empty($offered_id)) {
            update('yuexiage_travelmall_offered', $data, array('offered_id' => $offered_id));
            //删除当前线路缓存
        } else {
            $data['offered_id'] = uuid();
            insert('yuexiage_travelmall_offered', $data);
            $offered_id = $data['offered_id'];
        }
        message('更新参团游成功！', $this->createWebUrl('offered', array('op' => 'post','id'=>$offered_id)), 'success');
    }

    //保存行程信息
    if (checksubmit('submit_stroke')) {
        $data = array(
            'uniacid'       => $_W['uniacid'],
            'stroke'        => trim($_GPC['stroke']),
            'displayorder'  => intval($_GPC['displayorder']),
            'dinner'        => trim($_GPC['dinner']),
            'desc'          => trim($_GPC['desc']),
            'lunch'         => trim($_GPC['lunch']),
            'breakfast'     => trim($_GPC['breakfast']),
            'viewpoint'     => iserializer($_GPC['viewpoint']),
            'stay'          => iserializer($_GPC['hotellist']),
            'offered_id'    => $offered_id,
            'shopping'      => $_GPC['shopping']
        );
        if (!empty($stroke_id)) {
            update('yuexiage_travelmall_offered_stroke', $data, array('stroke_id' => $stroke_id));
        } else {
            $data['stroke_id'] = uuid();
            insert('yuexiage_travelmall_offered_stroke', $data);
        }
        message('添加行程成功!', $this->createWebUrl('offered', array('op' => 'post','id'=>$offered_id)), 'success');
    }

    //更新行程排序
    if (checksubmit('displayorder_stroke')) {
        if (!empty($_GPC['stroke_displayorder'])) {
            foreach ($_GPC['stroke_displayorder'] as $stroke_id => $displayorder) {
                $update = array('displayorder' => $displayorder);
                update('yuexiage_travelmall_offered_stroke', $update, array('stroke_id' => $stroke_id));
            }
            message('行程排序更新成功！', 'refresh', 'success');
        }
    }
}elseif ($do == 'delete') {
    $offered_id     = $_GPC['id'];
    $stroke_id      = $_GPC['stroke_id'];
    if($stroke_id){
        if(!empty($stroke_id)) {
            $stroke     =  com_load_cache(array(
                'cache_key' =>'stroke_item',
                'stroke_id'=>$stroke_id,
            ));
            if(empty($stroke)) {
                message('行程不存在或已删除', '', 'error');
            }
        }else{
            $stroke = array(
                'displayorder' => 0
            );
        }
        update('yuexiage_travelmall_offered_stroke', ['is_del'=>1], array('stroke_id' => $stroke_id));
        message('删除参团游行程成功！', $this->createWebUrl('offered', array('op' => 'display')), 'success');
    }else{
        if(!empty($offered_id)) {
            $offered    = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE offered_id = :offered_id AND uniacid = :uniacid and is_del = :is_del",array(
                ':uniacid'      =>$_W['uniacid'],
                ':offered_id'   =>$offered_id,
                ':is_del'       =>0
            ));
            if(empty($offered)) {
            message('参团游不存在或已删除', '', 'error');
            }
        }else{
            $offered = array(
                'displayorder' => 0
            );
        }
        update('yuexiage_travelmall_offered', ['is_del'=>1], array('offered_id' => $offered_id));
        message('删除参团游成功！', $this->createWebUrl('offered', array('op' => 'display')), 'success');
    }
    
}
include $this->template('offered');