<?php
global $_GPC, $_W;
try{
    $viewpoint = com_load_cache(array(
        'cache_key'  =>'viewpoint',
        'viewpoint_id' =>$_GPC['id'],
    ));

    //判断线路结果
    if(empty($viewpoint)){
        throw new Exception("数据错误!",42);
    }

    //获取机票信息
    $country = com_load_cache(array(
        'cache_key'  =>'country',
    ));
    $city = com_load_cache(array(
        'cache_key'  =>'city',
    ));
    $viewpoint['country'] = $country[$viewpoint['country_id']]['name'];
    $viewpoint['city']    = $city[$viewpoint['city_id']]['name'];
    echo json($viewpoint);
} catch (Exception $e) {
    echo json('',$e->getCode(),$e->getMessage());
}





















