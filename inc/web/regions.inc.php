<?php
//地域管理
global $_GPC, $_W;
$action = $_GPC['action']?$_GPC['action']:'regions_panel';
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';

$pindex = max(1, intval($_GPC['page']));
$psize = 10;
$params     = [':is_del'=>0];
if ($action=='country') {
    if ($do == 'display') {
        if (!empty($_GPC['displayorder'])) {
            foreach ($_GPC['displayorder'] as $country_id => $displayorder) {
                $update = array('displayorder' => $displayorder);
                update('yuexiage_travelmall_country', $update, array('country_id' => $country_id));
            }
            message('国家排序更新成功！', 'refresh', 'success');
        }
        $condition  = " and is_del = :is_del";
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND name LIKE :keyword";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        $params[':uniacid'] = $_W['uniacid'];
        $country    =   pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
        $total      =   pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_country') . " WHERE uniacid = :uniacid $condition ",$params);
        $pager      =   pagination($total, $pindex, $psize);
    }elseif ($do == 'post') {
        $country_id = $_GPC['id'];
        if(!empty($country_id)) {
            $country = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE is_del = :is_del and  country_id =:country_id AND uniacid = :uniacid",array(':uniacid'=>$_W['uniacid'],':country_id'=>$country_id,':is_del'=>0));
            if(empty($country)) {
                message('国家不存在或已删除', '', 'error');
            }
        } else {
            $country = array(
                'displayorder' => 0
            );
        }
        if (checksubmit('submit')) {
            if (empty($_GPC['cname'])) {
                message('抱歉，请输入国家名称！');
            }
            $data = array(
                'uniacid'       => $_W['uniacid'],
                'name'          => $_GPC['cname'],
                'displayorder'  => intval($_GPC['displayorder']),
                'enabled'       => intval($_GPC['enabled']),
                'description'   => $_GPC['description']
            );
            if (!empty($country_id)) {
                try {
                    pdo_begin();
                    update('yuexiage_travelmall_country', $data, array('country_id' => $country_id));
                    //如果更新了国家的发布状态，同步更新城市的发布状态
                    if(!empty($country) && ($country['enabled']!=intval($_GPC['enabled'])) ){
                        update('yuexiage_travelmall_city', ['enabled'=>intval($_GPC['enabled'])], array('pcate' => $country_id));
                    }
                    pdo_commit();
                } catch (Exception $e) {
                    pdo_rollback();
                    echo $e->getMessage();
                    exit;
                }

            } else {
                $data['country_id'] = uuid();
                $re = insert('yuexiage_travelmall_country', $data);
            }
            message('更新国家成功！', $this->createWebUrl('regions', array('op' => 'display','action'=>'country')), 'success');
        }
    }elseif ($do == 'delete') { 
        $country_id = $_GPC['id'];
        if(!empty($country_id)) {
            $country = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE is_del = :is_del and  country_id = :country_id AND uniacid = :uniacid",array(":country_id"=>$country_id,':uniacid'=>$_W['uniacid'],':is_del'=>0));
            if(empty($country)) {
                message('国家不存在或已删除', '', 'error');
            }
        }else{
            $country = array(
                'displayorder' => 0
            );
        }

        //如果有线路（上架+下架）存在，拒绝删除
        $count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_offered')." WHERE (departure_country =:country_id OR destination_country = :country_id) AND uniacid = :uniacid",
            array(':uniacid'=>$_W['uniacid'],':country_id'=>$country_id));
        if(!empty($count)){
            message('被线路使用的国家不能删除', '', 'error');
        }
        //查询酒店是否使用此国家
        $count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_hotel')." WHERE country_id =:country_id  AND uniacid = :uniacid",
            array(':uniacid'=>$_W['uniacid'],':country_id'=>$country_id));
        if(!empty($count)){
            message('被酒店使用的国家不能删除', '', 'error');
        }
        //查询景点是否使用此国家
        $count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_viewpoint')." WHERE country_id =:country_id  AND uniacid = :uniacid",
            array(':uniacid'=>$_W['uniacid'],':country_id'=>$country_id));
        if(!empty($count)){
            message('被景点使用的国家不能删除', '', 'error');
        }

        try {
            pdo_begin();
            update('yuexiage_travelmall_country', ['is_del'=>1], array('country_id' => $country_id));
            //国家删除，城市同步删除
            update('yuexiage_travelmall_city', ['is_del'=>1], array('pcate' => $country_id));
            pdo_commit();
            //pdo_debug();exit;
        } catch (Exception $e) {
            pdo_rollback();
            echo $e->getMessage();
            exit;
        }
        message('更新国家成功！', $this->createWebUrl('regions', array('op' => 'display','action'=>'country')), 'success');
    }
}elseif ($action=='city'){ 
    if ($do == 'display') {
        if (!empty($_GPC['displayorder'])) {
            foreach ($_GPC['displayorder'] as $city_id => $displayorder) {
                $update = array('displayorder' => $displayorder);
                update('yuexiage_travelmall_city', $update, array('city_id' => $city_id));
            }
            message('城市排序更新成功！', 'refresh', 'success');
        }
        $condition  = " and u.is_del = :is_del and c.is_del = :is_del";
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND c.name LIKE :keyword";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        $params[':uniacid'] = $_W['uniacid'];
        $city = pdo_fetchall("SELECT c.*,u.name as u_name,u.country_id as pcate FROM ".tablename('yuexiage_travelmall_city')." as c LEFT JOIN 
        ".tablename('yuexiage_travelmall_country')." as u ON c.pcate = u.country_id WHERE u.uniacid = :uniacid AND  c.uniacid = :uniacid $condition  ORDER BY  c.displayorder ASC, c.id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
        //pdo_debug();exit;
        $total=pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('yuexiage_travelmall_city')." as c LEFT JOIN 
        ".tablename('yuexiage_travelmall_country')." as u ON c.pcate = u.country_id WHERE u.uniacid = :uniacid AND  c.uniacid = :uniacid $condition",$params);
        $pager = pagination($total, $pindex, $psize);

    }elseif ($do == 'post') {
        $city_id    = $_GPC['id'];
        //缓存获取国家列表
        //编辑的时候下架国家也取出来
        $cache_key  = !empty($city_id) ? 'country_all' : 'country';
        $country    =  com_load_cache(array(
            'cache_key' => $cache_key,
        ));
        if(!empty($city_id)) {
            $city = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE is_del = :is_del and  city_id =:city_id AND uniacid = :uniacid",array(':uniacid'=>$_W['uniacid'],':city_id'=>$city_id,':is_del'=>0));
            if(empty($city)) {
                message('城市不存在或已删除', '', 'error');
            }
        }
        if (checksubmit('submit')) {
            if (empty($_GPC['cname'])) {
                message('抱歉，请输入城市名称！');
            }
            $data = array(
                'uniacid'       => $_W['uniacid'],
                'name'          => $_GPC['cname'],
                'displayorder'  => intval($_GPC['displayorder']),
                'pcate'         => $_GPC['pcate'],
                'enabled'       => intval($_GPC['enabled']),
                'description'   => $_GPC['description']
            );

            //如果当前城市的国家为未发布状态，自动设置当前城市为未发布
            if($country[$_GPC['pcate']]['enabled'] == 0){
                $data['enabled'] = 0;
            }
            if (!empty($city_id)) {
                if( empty($country[$_GPC['pcate']]['enabled'])
                    && (intval($_GPC['enabled'])== 1)  ){
                    message('当前城市的国家信息不适合更改发布状态', '', 'error');
                }
                update('yuexiage_travelmall_city', $data, array('city_id' => $city_id));
            } else {
                $data['city_id'] = uuid();
                insert('yuexiage_travelmall_city', $data);
                //$id = insertid();
            }
            message('更新城市成功！', $this->createWebUrl('regions', array('op' => 'display','action'=>'city')), 'success');
        }
    }elseif ($do == 'delete') { 
        $city_id = $_GPC['id'];
        if(!empty($city_d)) {
            $city = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE is_del = :is_del and  city_id =:city_id AND uniacid = :uniacid",array(':uniacid'=>$_W['uniacid'],':city_id'=>$city_id,':is_del'=>0));
            //pdo_debug();exit;
            if(empty($city)) {
                message('城市不存在或已删除', '', 'error');
            }
        }else{
            $city = array(
                'displayorder' => 0
            );
        }

        //如果有线路（上架+下架）线路存在，拒绝删除
        $count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_offered')." WHERE (departure_city =:city_id OR destination_city = :city_id) AND uniacid = :uniacid",
            array(':uniacid'=>$_W['uniacid'],':city_id'=>$city_id));
        if(empty($count)){
            message('有线路存在的城市不能删除', '', 'error');
        }
        //查询酒店是否使用此城市
        $count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_hotel')." WHERE city_id =:city_id  AND uniacid = :uniacid",
            array(':uniacid'=>$_W['uniacid'],':city_id'=>$city_id));
        if(!empty($count)){
            message('有酒店存在的城市不能删除', '', 'error');
        }
        //查询景点是否使用此城市
        $count = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('yuexiage_travelmall_viewpoint')." WHERE city_id =:city_id  AND uniacid = :uniacid",
            array(':uniacid'=>$_W['uniacid'],':city_id'=>$city_id));
        if(!empty($count)){
            message('有景点存在的城市不能删除', '', 'error');
        }

        update('yuexiage_travelmall_city', ['is_del'=>1], array('city_id' => $city_id));
        message('更新城市成功！', $this->createWebUrl('regions', array('op' => 'display','action'=>'city')), 'success');
    }
}
include $this->template($action);