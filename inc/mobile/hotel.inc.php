<?php
global $_GPC, $_W;
try{
    $hotel = com_load_cache(array(
        'cache_key'  =>'hotel',
        'hotel_id' =>$_GPC['id'],
    ));

    //判断线路结果
    if(empty($hotel)){
        throw new Exception("数据错误!",42);
    }

    //获取机票信息
    $country = com_load_cache(array(
        'cache_key'  =>'country',
    ));
    $city = com_load_cache(array(
        'cache_key'  =>'city',
    ));
    $hotel['country'] = $country[$hotel['country_id']]['name'];
    $hotel['city']    = $city[$hotel['city_id']]['name'];
    if($_W['isajax']){
        echo json($hotel);
    }else{
        include $this->template('hotel');
    }
} catch (Exception $e) {
    echo json('',$e->getCode(),$e->getMessage());
}





















