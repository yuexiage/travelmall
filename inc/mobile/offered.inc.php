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
    }elseif ($_GPC['op'] == 'calendar'){
        //线路id
        if(empty($_GPC['id'])){
            throw new Exception("数据错误!",42);
        }
        //查询线路详情
        $spike_item = com_load_cache(array(
            'cache_key'  =>'offered_item',
            'offered_id' =>$_GPC['id'],
        ));

        load()->func('tpl');
        include $this->template('offered/calendar');
    }elseif ($_GPC['op'] == 'booking'){
        if(empty($_GPC['amount_id']) || empty($_GPC['id'])){
            throw new Exception("数据错误!",42);
        }
        //查询线路详情
        $spike_item = com_load_cache(array(
            'cache_key'  =>'offered_item',
            'offered_id' =>$_GPC['id'],
        ));

        //查询线路价格详情
        $amount_item = com_load_cache(array(
            'cache_key'  =>'amount_item',
            'amount_id'  =>$_GPC['amount_id'],
        ));

        load()->func('tpl');
        include $this->template('offered/booking');
    }else{
        load()->func('tpl');
        include $this->template('offered/offered_item');
    }
} catch (Exception $e) {
    echo json('',$e->getCode(),$e->getMessage());
}