<?php 
global $_GPC, $_W;
try {
    if($_GPC['op']=='all'){
        $offered_id         = $_GPC['offered_id'];
        if ( empty($offered_id) ){
            throw new Exception("线路ID不能为空!");
        }
        $amount = pdo_fetchall("SELECT FROM_UNIXTIME(start_date,'%Y-%m-%d') as start_date,adult_price,child_price,stock,single FROM ".tablename('yuexiage_travelmall_offered_amount')." WHERE uniacid = :uniacid  and offered_id=:offered_id and is_del = :is_del ", array(
            ':uniacid'      =>$_W['uniacid'],
            ':offered_id'   =>$offered_id,
            ':is_del'       =>0,
        ), 'start_date');
        echo json($amount);
    }elseif ($_GPC['op']=='delete'){
        //获取选中日期
        $select_date        = $_GPC['select_date'];
        if ( empty($select_date) ){
            throw new Exception("选择日期不能为空");
        }
        //获取线路ID
        $offered_id         = $_GPC['offered_id'];
        if ( empty($offered_id) ){
            throw new Exception("线路ID不能为空");
        }
        $amount = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_offered_amount')." WHERE uniacid = :uniacid  and offered_id=:offered_id and FROM_UNIXTIME(start_date,'%Y-%m-%d') = :start_date and is_del=:is_del ", array(
            ':uniacid'      =>$_W['uniacid'],
            ':offered_id'   =>$offered_id,
            ':start_date'   =>$select_date,
            ':is_del'       =>0,
        ));

        if ( empty($amount) ){
            throw new Exception("当前日期信息为空");
        }
        //删除日期信息
        $amount_id          = $amount['amount_id'];
        $re                 = update('yuexiage_travelmall_offered_amount', ['is_del'=>1], array('amount_id' => $amount_id));

        //查询最低价
        $offered_amount = pdo_fetch("SELECT min(adult_price) as adult_price,min(child_price) as child_price,sum(stock) as stock FROM ".tablename('yuexiage_travelmall_offered_amount')." WHERE uniacid = :uniacid  and offered_id=:offered_id and is_del=:is_del ", array(
            ':uniacid'      =>$_W['uniacid'],
            ':offered_id'   =>$offered_id,
            ':is_del'       =>0,
        ));
        update('yuexiage_travelmall_offered', ['adult_price'=>$offered_amount['adult_price'],'child_price'=>$offered_amount['child_price'],'stock'=>$offered_amount['stock']], array(
            'offered_id' => $offered_id,
        ));
        echo json($re);
    }elseif ($_GPC['op']=='update'){
        //获取选中日期
        $select_date        = $_GPC['select_date'];
        if ( empty($select_date) ){
            throw new Exception("选择日期不能为空");
        }
        //获取线路ID
        $offered_id         = $_GPC['offered_id'];
        if ( empty($offered_id) ){
            throw new Exception("线路ID不能为空");
        }
        $params             = array(
            'adult_price'   =>$_GPC['adult'],
            'child_price'   =>$_GPC['child'],
            'stock'         =>$_GPC['stock'],
            'single'        =>$_GPC['single'],
        );
        $re                 = update('yuexiage_travelmall_offered_amount', $params, array(
            'offered_id' => $offered_id,
            'start_date' => strtotime($select_date),
        ));

        //查询最低价
        $offered_amount = pdo_fetch("SELECT min(adult_price) as adult_price,min(child_price) as child_price,sum(stock) as stock FROM ".tablename('yuexiage_travelmall_offered_amount')." WHERE uniacid = :uniacid  and offered_id=:offered_id and is_del=:is_del ", array(
            ':uniacid'      =>$_W['uniacid'],
            ':offered_id'   =>$offered_id,
            ':is_del'       =>0,
        ));
        update('yuexiage_travelmall_offered', ['adult_price'=>$offered_amount['adult_price'],'child_price'=>$offered_amount['child_price'],'stock'=>$offered_amount['stock']], array(
            'offered_id' => $offered_id,
        ));
        echo json($re);
    }elseif ($_GPC['op']=='ok'){
        //获取线路ID
        $offered_id         = $_GPC['offered_id'];
        if ( empty($offered_id) ){
            throw new Exception("线路ID不能为空");
        }
        //获取线路ID
        $date_list          = $_GPC['date_list'];
        if ( empty($date_list) ){
            throw new Exception("选择日期不能为空");
        }
        try {
            //开启事务
            pdo_begin();
            foreach ($date_list as $date){
                $params             = array(
                    'amount_id'     =>uuid(),
                    'offered_id'    =>$offered_id,
                    'adult_price'   =>$_GPC['adult'],
                    'uniacid'       =>$_W['uniacid'],
                    'child_price'   =>$_GPC['child'],
                    'stock'         =>$_GPC['stock'],
                    'single'        =>$_GPC['single'],
                    'start_date'    =>strtotime($date),
                );
                insert('yuexiage_travelmall_offered_amount', $params);
            }

            //查询最低价
            $offered_amount = pdo_fetch("SELECT min(adult_price) as adult_price,min(child_price) as child_price,sum(stock) as stock FROM ".tablename('yuexiage_travelmall_offered_amount')." WHERE uniacid = :uniacid  and offered_id=:offered_id and is_del=:is_del ", array(
                ':uniacid'      =>$_W['uniacid'],
                ':offered_id'   =>$offered_id,
                ':is_del'       =>0,
            ));
            update('yuexiage_travelmall_offered', ['adult_price'=>$offered_amount['adult_price'],'child_price'=>$offered_amount['child_price'],'stock'=>$offered_amount['stock']], array(
                'offered_id' => $offered_id,
            ));
            //提交事务
            pdo_commit();
            echo json('',20,'添加成功!');
        } catch (Exception $e) {
            pdo_rollback();
            echo json('',42,$e->getMessage());
        }
    }

} catch (Exception $e) {
    echo json('',42,$e->getMessage());
}






?>