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
            pdo_update('yuexiage_travelmall_fit', $update, array('id' => $id));
        }
        message('景点排序更新成功！', 'refresh', 'success');
    }

    if (!empty($_GPC['keyword'])) {
        $condition .= " AND name LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }

    if (!empty($_GPC['fit'])) {
        if($_GPC['fit']['parentid'] != '0'){
            $condition .= " AND country_id = :country_id";
            $params[':country_id'] = $_GPC['fit']['parentid'];
        }
        if($_GPC['fit']['childid'] != '0'){
            $condition .= " AND city_id = :city_id";
            $params[':city_id'] = $_GPC['fit']['childid'];
        }
    }

    if(isset($_GPC['category_id']) && $_GPC['category_id'] != '-1'){
        $condition .= " AND category_id = :category_id";
        $params[':category_id'] = $_GPC['category_id'];
    }

    //分类
    $categorys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_categorys')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');

    /**地区分布**/
    $countrys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    $citys  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array());
    foreach ($citys as $city) {
        $_citys[$city['pcate']][] = $city;
    }
    /**地区分布结束**/
    $fit = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_fit')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
    
    $total   =pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_fit') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
    $pager   = pagination($total, $pindex, $psize);
}elseif ($do == 'post') {
    $id         = intval($_GPC['id']);
    $stroke_id  = intval($_GPC['stroke_id']);
    if(!empty($id)) {
        $fit = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_fit')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
        if(empty($fit)) {
            message('自由行不存在或已删除', '', 'error');
        }
    } else {
        $fit = array(
            'displayorder' => 0
        );
    }

    //解析图片内容
    $fit['thumbs'] = iunserializer($fit['thumbs']);
    
    /**地区分布**/
    $countrys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    $citys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array());
    foreach ($citys as $city) {
        $_citys[$city['pcate']][] = $city;
    }
    /**地区分布结束**/

    //标签
    $tabs = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_tabs')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    //主题
    $theme = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_theme')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    //分类
    $categorys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_categorys')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');

    //获取行程列表
    $strokes = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_stroke')." WHERE uniacid = '{$_W['uniacid']}' and offered_id='{$id}'  ORDER BY displayorder ASC, id ASC ", array(), 'id');
    foreach ($strokes as &$_stroke) {
        $_stroke['stay']      = iunserializer($_stroke['stay']);
        $_stroke['viewpoint'] = iunserializer($_stroke['viewpoint']);
    }
    //获取单个行程
    $stroke = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_stroke')." WHERE uniacid = '{$_W['uniacid']}'  and id='{$stroke_id}' ", array(), 'id');
    $stroke['stay']      = iunserializer($stroke['stay']);
    $stroke['viewpoint'] = iunserializer($stroke['viewpoint']);

    //获取日期价格
    $amount = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_fit_amount')." WHERE uniacid = '{$_W['uniacid']}'  and offered_id='{$id}' ", array(), 'id');

    //获取优惠券列表
    $coupons = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE uniacid = '{$_W['uniacid']}'  and enabled=1 and theme_id=2 ", array());
    //获取现金券
    $cashs = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE uniacid = '{$_W['uniacid']}'  and enabled=1 and theme_id=1 ", array());

    //获取航班
    $flights = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_flight')." WHERE uniacid = '{$_W['uniacid']}'  and enabled=1 ", array());

    //保存基础信息
    if (checksubmit('submit')) {
        if (empty($_GPC['name'])) {
            message('抱歉，请输入自由行名称！');
        }

        $data = array(
            'uniacid'       => $_W['uniacid'],
            'name'          => trim($_GPC['name']),
            'displayorder'  => intval($_GPC['displayorder']),
            'enabled'       => intval($_GPC['enabled']),
            'country_id'           => $_GPC['fit']['parentid'],
            'city_id'           => $_GPC['fit']['childid'],
            'cid1'          => $_GPC['fit1']['parentid'],
            'yid1'          => $_GPC['fit1']['childid'],
            'thumbs'        => iserializer($_GPC['thumbs']),
            'tab_id'           => $_GPC['tab_id'],
            'theme_id'           => $_GPC['theme_id'],
            'category_id'           => $_GPC['category_id'],
            'adult_price'         => $_GPC['adult_price'],
            'child_price'         => $_GPC['child_price'],
            'day_night'     => trim($_GPC['day_night']),
            'lower_shelf'         => strtotime($_GPC['lower_shelf']),
            'upper_shelf'    => $_GPC['upper_shelf'],
            'contain'       => $_GPC['contain'],
            'not_included'  => $_GPC['not_included'],
            'booked'        => $_GPC['booked'],
            'visa'          => $_GPC['visa'],
            'customer'      => $_GPC['customer'],
            'stock'         => $_GPC['stock'],
            'credit'        => $_GPC['credit'],
            'credit_value'  => $_GPC['credit_value'],
            'coupon_id'     => $_GPC['coupon_id'],
            'cash_id'       => $_GPC['cash_id'],
            'trip'          => $_GPC['trip'],
            'return_trip'           => $_GPC['return_trip'],
            'comment'       => $_GPC['comment'],
            'description'   => $_GPC['description']
        );

        if (!empty($id)) {
            pdo_update('yuexiage_travelmall_fit', $data, array('id' => $id));
        } else {
            pdo_insert('yuexiage_travelmall_fit', $data);
            $id = pdo_insertid();
        }
        message('更新自由行成功！', $this->createWebUrl('fit', array('op' => 'post','id'=>$id)), 'success');
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
            'offered_id'           => $id,
            'shopping'      => $_GPC['shopping']
        );
        if (!empty($stroke_id)) {
            pdo_update('yuexiage_travelmall_stroke', $data, array('id' => $stroke_id));
        } else {
            pdo_insert('yuexiage_travelmall_stroke', $data);
        }
        message('添加行程成功!', $this->createWebUrl('fit', array('op' => 'post','id'=>$id)), 'success');
    }

    //保存日期价格信息
    if (checksubmit('submit_date_price')) {
        pdo_delete('yuexiage_travelmall_fit_amount',  array('offered_id' => $_GPC['id']));
        $data = array(
            'uniacid'       => $_W['uniacid'],
            'offered_id'           => intval($_GPC['id'])
        );
        $date = $_GPC['date'];
        foreach ($date as $k => $row) {
            $data['start_date'] = strtotime($k);
            $price = explode(':',$row);
            $data['adult_price'] = $price['0'];
            $data['child_price']  = $price['1'];
            $data['stock']          = $price['2'];

            pdo_insert('yuexiage_travelmall_fit_amount', $data);
        }
        message('添加日期成功！', 'refresh', 'success');
    }

    //更新行程排序
    if (checksubmit('displayorder_stroke')) {
        if (!empty($_GPC['stroke_displayorder'])) {
            foreach ($_GPC['stroke_displayorder'] as $id => $displayorder) {
                $update = array('displayorder' => $displayorder);
                pdo_update('yuexiage_travelmall_stroke', $update, array('id' => $id));
            }
            message('行程排序更新成功！', 'refresh', 'success');
        }
    }
}elseif ($do == 'delete') { 
    $id     = intval($_GPC['id']);
    $stroke = intval($_GPC['stroke']);
    if($stroke){
        if(!empty($id)) {
            $stroke = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_stroke')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
            if(empty($stroke)) {
                message('行程不存在或已删除', '', 'error');
            }
        }else{
            $stroke = array(
                'displayorder' => 0
            );
        }
        pdo_delete('yuexiage_travelmall_stroke',  array('id' => $id));
        message('删除自由行成功！', $this->createWebUrl('fit', array('op' => 'display')), 'success');
    }else{
        if(!empty($id)) {
            $fit = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_fit')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
            if(empty($fit)) {
            message('自由行不存在或已删除', '', 'error');
            }
        }else{
            $fit = array(
                'displayorder' => 0
            );
        }
        pdo_delete('yuexiage_travelmall_fit',  array('id' => $id));
        message('删除自由行成功！', $this->createWebUrl('fit', array('op' => 'display')), 'success');
    }
    
}
include $this->template('fit');