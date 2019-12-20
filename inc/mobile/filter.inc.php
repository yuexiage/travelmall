<?php
try {
    global $_GPC, $_W;
    /**地区分布**/
    $countrys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(), 'id');
    $citys    = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = '{$_W['uniacid']}' and enabled = 1 ORDER BY displayorder ASC, id ASC ", array(),'id');
    foreach ($citys as $city) {
        $_citys[$city['pcate']][] = $city;
    }
    /**地区分布结束**/
    if($_W['isajax']){
        $pindex     = max(1, intval($_GPC['page']));
        $psize      = 10;
        $condition  = ' ';
        $order_by   = ' order by id asc';
        //目的地 destination
        if (!empty($_GPC['filter_destination'])){
            $destination    = '\''.implode('\',\'',$_GPC['filter_destination']).'\'';
            $condition  .= " AND destination_city in ({$destination}) ";
        }
        //行程天数
        if (!empty($_GPC['filter_date_day'])){
            $day_night  = ' and (';
            $days       = array(
                '1'=>'1-5',
                '2'=>'6-9',
                '3'=>'10-15',
                '4'=>'16-20',
                '5'=>'21-10000000'
            );
            $day_nights         = [];
            foreach($_GPC['filter_date_day'] as $val){
                $_days          = explode('-',$days[$val]);
                $day_nights[]   = "(day_night between $_days[0] and $_days[1])";
            }
            $day_night  .= implode(' or ',$day_nights);
            $day_night  .= ')';
            $condition .=$day_night;
        }
        //行程月份
        //排序
        if (!empty($_GPC['orderby'])){
            switch ($_GPC['orderby']){
                case '2':
                    //价格从低到高
                    $order_by = ' order by adult_price asc';
                    break;
                case '3':
                    //价格从高到低
                    $order_by = ' order by adult_price desc';
                    break;
                case '4':
                    //销量从高到低
                    $order_by = ' order by sales_volume desc';
                    break;
                default:

            }
        }

        //出发城市 filter_departure
        if (!empty($_GPC['filter_departure'])){
            $filter_destination    = '\''.implode('\',\'',$_GPC['filter_destination']).'\'';
            $condition  .= " AND departure_city in ({$filter_destination}) ";
        }

        //特色标签 filter_tab
        if (!empty($_GPC['filter_tab'])){
            $filter_tab    = '\''.implode('\',\'',$_GPC['filter_tab']).'\'';
            $condition  .= " AND tab_id in ({$filter_tab}) ";
        }

        //特色主题 filter_theme
        if (!empty($_GPC['filter_theme'])){
            $filter_theme    = '\''.implode('\',\'',$_GPC['filter_theme']).'\'';
            $condition  .= " AND theme_id in ({$filter_theme}) ";
        }

        $params[':uniacid'] = $_W['uniacid'];
        $filter  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE uniacid = :uniacid $condition $order_by LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
        $html    = '';
        foreach ($filter as $key => $value) {
            $imgs = iunserializer($value['thumbs']);
            $url = $this->createMobileUrl('spike_item',array('id'=>$value['id']));
            $html .= '<li class="mui-table-view-cell mui-media"><a href="'.$url.'"><div class="card_tab">参团游</div><img class="mui-media-object mui-pull-left" src="'.tomedia($imgs['0']).'">
                    <div class="mui-media-body">'.'['.$citys[$value["destination_city"]]['id'].']'.$value["name"].'<p class="mui-ellipsis">'.$value['description'].'</p><p class="adult_price"><sub>￥</sub>'.$value['adult_price'].'<sub>起</sub></p></div></a></li>';
        }
        echo $html;
    }else{
        global $_GPC, $_W;
        //出行天数
        $days = array(
            ['id'=>1,'name'=>'1-5天'],
            ['id'=>2,'name'=>'6-9天'],
            ['id'=>3,'name'=>'10-15天'],
            ['id'=>4,'name'=>'16-20天'],
            ['id'=>5,'name'=>'21天以上'],
        );

        //出行月份
        $moonths = [];
        for($i=0;$i<6;$i++){
            $month      = date('m',strtotime("midnight first day of $i month"));
            $moonths[]  = ['id'=>intval($month),'name'=>$month."月"];
        }

        //排序
        $order_bys = array(
            ['id'=>1,'name'=>'默认总和排序'],
            ['id'=>2,'name'=>'价格从低到高'],
            ['id'=>3,'name'=>'价格从高到低'],
            ['id'=>4,'name'=>'销量从高到低'],
        );

        //查询中国country_id
        $country_id = pdo_fetch("SELECT id FROM ".tablename('yuexiage_travelmall_country')." WHERE uniacid = :uniacid and enabled = :enabled and name like '%中国%' ",array(
            ':uniacid'  =>$_W['uniacid'],
            ':enabled'  =>1,
        ));
        $country_id = $country_id['id'];
        $city_list = pdo_fetchall("SELECT id,name FROM ".tablename('yuexiage_travelmall_city')." WHERE uniacid = :uniacid and enabled = :enabled and pcate = :pcate ",array(
            ':uniacid'  =>$_W['uniacid'],
            ':enabled'  =>1,
            ':pcate'    =>$country_id,
        ));

        //查询标签
        $tabs = pdo_fetchall("SELECT id,name FROM ".tablename('yuexiage_travelmall_tabs')." WHERE uniacid = :uniacid and enabled = :enabled",array(
            ':uniacid'  =>$_W['uniacid'],
            ':enabled'  =>1,
        ));

        //查询主题
        $themes = pdo_fetchall("SELECT id,name FROM ".tablename('yuexiage_travelmall_theme')." WHERE uniacid = :uniacid and enabled = :enabled",array(
            ':uniacid'  =>$_W['uniacid'],
            ':enabled'  =>1,
        ));

        //标题
        $title = $citys[$_GPC['val']]['name'];
        include $this->template('filter');
    }
} catch (Exception $e) {
    echo $e->getMessage();
}