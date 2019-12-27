<?php
try {
    global $_GPC, $_W;
    if($_W['isajax']){
        if($_GPC['op'] == 'offered_list'){
            //查询线路列表
            include_once 'api/api_offered_list.php';
        }elseif ($_GPC['op'] == 'offered_item'){
            //查询线路详情
            include_once 'api/api_offered_item.php';
        }
    }else{
        load()->func('tpl');
        include $this->template('offered_item');
    }
} catch (Exception $e) {
    echo $e->getMessage();
}