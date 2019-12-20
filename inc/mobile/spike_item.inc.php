<?php
try {
    global $_GPC, $_W;
    if($_W['isajax']){
        include_once 'api/api_spike_item.php';
    }else{
        load()->func('tpl');
        include $this->template('spike_item');
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

