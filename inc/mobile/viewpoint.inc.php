<?php
global $_GPC, $_W;
try {
    global $_GPC, $_W;
    if($_W['isajax']){
        include_once 'api/api_viewpoint.php';
    }else{
        load()->func('tpl');
        include $this->template('viewpoint');
    }
} catch (Exception $e) {
    echo $e->getMessage();
}















