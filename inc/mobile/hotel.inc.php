<?php
global $_GPC, $_W;
try {
    global $_GPC, $_W;
    if($_W['isajax']){
        include_once 'api/api_hotel.php';
    }else{
        load()->func('tpl');
        include $this->template('hotel');
    }
} catch (Exception $e) {
    echo $e->getMessage();
}



















