<?php
global $_GPC, $_W;
$id      = $_GPC['id'];
load()->func('logging');
$op      = $_GPC['op']?$_GPC['op']:'calendar';
$uid     = $_W['member']['uid'];
$credit_cash      = $this->settings['credit_cash'];
$credit1 = $_W['member']['credit1'];
//1积分多少钱
$proportion = 1/$credit_cash;
load()->func('communication');
if($_W['isajax']){
    $rid      = $_GPC['rid'];
    $ucid      = $_GPC['ucid'];
    $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_fit')." WHERE id = :id and uniacid = :uniacid";
    $offered = pdo_fetch($sql, array(':id'=>$rid,':uniacid' => $_W['uniacid']));
    if($offered['coupon_id'] != $id){
        $d['error'] =1;
        echo json_encode($d);
        exit;
    }

    $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_coupon_user')." WHERE id = :id and uniacid = :uniacid";
    $coupon = pdo_fetch($sql, array(':id'=>$ucid,':uniacid' => $_W['uniacid']));
    if($coupon['endtime']<=time()){
        $d['error'] =1;
        echo json_encode($d);
        exit;
    }

    $sql = "SELECT tcu.*,tc.name,tc.achieve FROM ".tablename('yuexiage_travelmall_coupon_user')." as tcu LEFT JOIN ".tablename('yuexiage_travelmall_coupon')." as tc ON tcu.country_id = tc.id WHERE  tcu.id = :id and tcu.enabled >0";
    $coupon = pdo_fetch($sql, array(':id'=>$ucid)); 
    //判断是否符合满额
    if($coupon['achieve']<$_GPC['subtotal']){
        $d['id']    =$coupon['country_id'];
        $d['name']  =$coupon['name'];
        $d['error'] =0;
        $_SESSION['coupon'] = $d;
    }else{
        $d['error'] =1;
    }
    echo json_encode($d);
    exit;
}
//如果优惠券ID存在查询优惠券信息
if($_GPC['country_id']){
    $sql = "SELECT tcu.*,tc.name,tc.achieve FROM ".tablename('yuexiage_travelmall_coupon_user')." as tcu LEFT JOIN ".tablename('yuexiage_travelmall_coupon')." as tc ON tcu.country_id = tc.id WHERE  tcu.id = :id and tcu.theme_id=2 and tcu.enabled >0 and tcu.endtime >".time();
    $coupon = pdo_fetch($sql, array(':id'=>$_GPC['country_id'])); 
}

//如果代金券ID存在查询优惠券信息
if($_GPC['choice_cash']){
    $sql = "SELECT tcu.*,tc.name,tc.achieve FROM ".tablename('yuexiage_travelmall_coupon_user')." as tcu LEFT JOIN ".tablename('yuexiage_travelmall_coupon')." as tc ON tcu.country_id = tc.id WHERE  tcu.id = :id and tcu.theme_id=1 and tcu.enabled >0 and tcu.endtime >".time();
    $cash = pdo_fetch($sql, array(':id'=>$_GPC['choice_cash'])); 
}

checkauth();
if($op=='join'){
    $sql = "SELECT tcu.*,tc.name FROM ".tablename('yuexiage_travelmall_coupon_user')." as tcu LEFT JOIN ".tablename('yuexiage_travelmall_coupon')." as tc ON tcu.country_id = tc.id WHERE tcu.uid = :uid and tcu.theme_id=1 and tcu.status=1 and tcu.rid = :rid and tcu.uniacid = :uniacid and tcu.enabled >0 and tcu.endtime >".time();
    $cashs_num = pdo_fetchall($sql, array(':uid'=>$uid,':uniacid' => $_W['uniacid'],':rid'=>$id));

    
    $sql = "SELECT tcu.*,tc.name FROM ".tablename('yuexiage_travelmall_coupon_user')." as tcu LEFT JOIN ".tablename('yuexiage_travelmall_coupon')." as tc ON tcu.country_id = tc.id WHERE tcu.uid = :uid and tcu.theme_id=2 and tcu.rid = :rid and tcu.uniacid = :uniacid and tcu.enabled >0 and tcu.endtime >".time();
    $coupons_num = pdo_fetchall($sql, array(':uid'=>$uid,':uniacid' => $_W['uniacid'],':rid'=>$id));

    //获得优惠券后
    $country_id = $_GPC['country_id'];
    if($country_id){
        $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE id = :id and uniacid = :uniacid";
        $coupon = pdo_fetch($sql, array(':id'=>$country_id,':uniacid' => $_W['uniacid']));
    }



    $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_fit')." WHERE id = :id and uniacid = :uniacid";
    $offered = pdo_fetch($sql, array(':id'=>$id,':uniacid' => $_W['uniacid']));
    


    include $this->template('fit_join');
}elseif($op=='confirm'){
    $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_fit')." WHERE id = :id and uniacid = :uniacid";
    $offered = pdo_fetch($sql, array(':id'=>$id,':uniacid' => $_W['uniacid']));
    $country_id = $_GPC['country_id'];
    
    //优惠券
    $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE id = :id and uniacid = :uniacid";
    $coupon = pdo_fetch($sql, array(':id'=>$country_id,':uniacid' => $_W['uniacid']));
    
    include $this->template('fit_confirm');
}elseif($op=='saveorder'){
    //保存订单信息
    //检查线路
    $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_fit')." WHERE id = :id and uniacid = :uniacid";
    $offered = pdo_fetch($sql, array(':id'=>$id,':uniacid' => $_W['uniacid']));

    if(!$offered['enabled']){
        $order_status['error']=1;
        $order_status['msg']='产品已下架!!!';
        include $this->template('saveorder');
        exit;
    }elseif($offered['upper_shelf']){
        if($offered['lower_shelf']<time()){
            $order_status['error']=1;
            $order_status['msg']='产品已下架!!'; 
            $user_data = array('enabled'=>0);
            pdo_update('yuexiage_travelmall_fit', $user_data, array('id' => $_GPC['id']));
            include $this->template('saveorder');
            exit;
        }
    }

    //检查积分
    if($_GPC['credit_num']){
        if($credit1 && ($credit1<$_GPC['credit_num'])){
            $order_status['error']=1;
            $order_status['msg']='积分不足!';
            include $this->template('fit_saveorder');
            exit;
        }
    }
    $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_coupon_user')."  WHERE id = :id";
    $coupon = pdo_fetch($sql, array(':id'=>$_GPC['ucid'])); 

    //检查优惠券
    if($_GPC['coupon_id']){
        if( (isset($offered['coupon_id']) && $offered['coupon_id']>0)!=$coupon['country_id']){
            $order_status['error']=1;
            $order_status['msg']='优惠券错误1!';
            include $this->template('fit_saveorder');
            exit;
        }
    }
    //检查优惠券是否过期
    if($_GPC['coupon_id']){
        $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_coupon_user')." WHERE id = :id and uniacid = :uniacid";
        $coupon = pdo_fetch($sql, array(':id'=>$_GPC['ucid'],':uniacid' => $_W['uniacid']));
        if($coupon['endtime']<=time()){
            $order_status['error']=1;
            $order_status['msg']='优惠券错误2!';
            include $this->template('fit_saveorder');
            exit;
        }
    }

    //检查优惠券是否可以使用
    $_GPC['coupon_id'] = $_GPC['ucid']?$_GPC['ucid']:'0';
    $sql = "SELECT tcu.*,tc.name,tc.achieve FROM ".tablename('yuexiage_travelmall_coupon_user')." as tcu LEFT JOIN ".tablename('yuexiage_travelmall_coupon')." as tc ON tcu.country_id = tc.id WHERE tcu.uid = :uid and tcu.rid = :rid and tcu.uniacid = :uniacid and tcu.id=".$_GPC['coupon_id']." and tcu.enabled >0 and tcu.endtime >".time();
    $coupon = pdo_fetchall($sql, array(':uid'=>$uid,':uniacid' => $_W['uniacid'],':rid'=>$id));
    if($coupon['amount']>$_GPC['subtotal']){
        $order_status['error']=1;
        $order_status['msg']='现金券错误!';
        include $this->template('fit_saveorder');
        exit;
    }

    //检查代金券
    if($_GPC['cash_id']){
        $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_coupon_user')." WHERE id = :id and status=1 and uid = :uid and uniacid = :uniacid and enabled>0 and endtime>".time();
        $cash = pdo_fetch($sql, array(':id'=>$_GPC['cash_id'],':uniacid' => $_W['uniacid'],':uid'=>$uid));
        if(!$cash){
            $order_status['error']=1;
            $order_status['msg']='优惠券错误3!';
            include $this->template('fit_saveorder');
            exit;
        }
    }    
    //检查是否被拉入黑名单
    $blacklist = pdo_fetch('SELECT* FROM ' . tablename('yuexiage_travelmall_blacklist') . ' WHERE uniacid = :uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'],':uid'=>$uid));
    if($blacklist['place_an_order']){
        $order_status['error']=1;
        $order_status['msg']='当前用户没有权限!!';
        include $this->template('fit_saveorder');
        exit;
    }

    //库存
    if( ($offered['stock']-($data['adult_num']+$data['child_num']))<=0 ){
        $order_status['error']=1;
        $order_status['msg']='库存不足!!';
        include $this->template('saveorder');
        exit;
    }

    //检查邀请码
    $sql  = "SELECT code,uid FROM ".tablename('yuexiage_travelmall_member_code')." WHERE uniacid = :uniacid and code =:code";
    $invitation = pdo_fetch($sql, array(':uniacid' => $_W['uniacid'],':code'=>$_GPC['invitation']));
    if($_GPC['invitation']){
        if(!$invitation){
            $order_status['error']=1;
            $order_status['msg']='邀请码不可用!!';
            include $this->template('fit_saveorder');
            exit;
        }
    }
    

    //判断用户是否被拉黑
    $blacklist = pdo_fetch('SELECT* FROM ' . tablename('yuexiage_travelmall_blacklist') . ' WHERE uniacid = :uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'],':uid'=>$invitation['uid']));
    if($blacklist['withdraw_code']){
        $order_status['error']=1;
        $order_status['msg']='权限错误!!';
        include $this->template('fit_saveorder');
        exit;
    }


    $ordersn = 'T'.date("YmdHis").str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    load()->model('mc');

    $data = array();
    $data['goods_name']     = $_GPC['offered_name'];
    $data['goods_id']       = $_GPC['id'];
    $data['ordersn']        = $ordersn;
    $data['event_day']      = strtotime($_GPC['event_day']);
    $data['adult_price']    = $_GPC['adult_price'];
    $data['adult_num']      = $_GPC['adult_num'];
    $data['child_price']    = $_GPC['child_price'];
    $data['child_num']      = $_GPC['child_num'];
    $data['subtotal']       = $_GPC['subtotal'];
    $data['coupon_id']      = $_GPC['coupon_id'];
    $data['coupon_price']   = $_GPC['coupon_price'];
    $data['credit_num']     = $_GPC['credit_num'];
    $data['credit_value']   = $_GPC['credit_value'];
    $data['total']          = $_GPC['total'];
	$data['uniacid']        = $_W['uniacid'];
    $data['invitation']     = $_GPC['invitation'];
    $data['contact_name']   = $_GPC['contact_name'];
    $data['contact_tel']    = $_GPC['contact_tel'];
    $data['contact_id']     = $_GPC['contact_id'];
    $data['contact_email']  = $_GPC['contact_email'];
    $data['status']         = 0;
    $data['type']           = 1;
    $data['remark']         = '';
    $data['createtime']     = time();
    $data['cash_id']        = $_GPC['cash_id'];
    $data['cash_price']     = $_GPC['cash_price'];
	$data['uid']     = $uid;
    $email           = $data['contact_email'];
    $ordersn         = $data['ordersn'];
    $contact_name    = $data['contact_name'];
    $goods_name      = $data['goods_name'];
	
    $save_order = pdo_insert('yuexiage_travelmall_orders', $data);
    if($save_order){
        $orderid = pdo_insertid();
        for($i=0;$i<count($_GPC['join_name']);$i++){
            $data=array(
                'offered_id'       =>$orderid,
                'join_name' =>$_GPC['join_name'][$i],
                'join_tel'  =>$_GPC['join_tel'][$i],
                'join_id'  =>$_GPC['join_id'][$i]
            );
           pdo_insert('yuexiage_travelmall_orders_option', $data);
        }


        //更新库存
        $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_fit_amount')." WHERE offered_id = :offered_id and  uniacid = :uniacid and start_date= :start_date ";

        logging_run('库存sql'. $sql);
        logging_run('offered_id'. $_GPC['id']);
        logging_run('uniacid'.$_W['uniacid']);
        $amount = pdo_fetch($sql, array(':offered_id'=>$_GPC['id'],':uniacid' => $_W['uniacid'],':start_date'=>strtotime($_GPC['event_day'])));
                


        logging_run('库存结果'.var_export($amount,true));
        logging_run('开始库存'.$amount['stock']);
        $stock = array();
        $stock['stock'] =  $amount['stock'] - ($_GPC['adult_num']+$_GPC['child_num']); 
        pdo_update('yuexiage_travelmall_fit_amount',$stock, array('offered_id' => $_GPC['id'],'uniacid'=>$_W['uniacid'],'start_date'=>strtotime($_GPC['event_day'])));
        logging_run('更新库存'.$stock['stock']);


        //更新优惠码/代金券状态
        if($_GPC['coupon_id']){
            $data = array();
            $data['status'] = 2;
            pdo_update('yuexiage_travelmall_coupon_user',$data, array('id' => $_GPC['coupon_id']));
        }
        
        if($_GPC['cash_id']){
            $data = array();
            $data['status'] = 2;
            pdo_update('yuexiage_travelmall_coupon_user',$data, array('id' => $_GPC['cash_id']));
        }

        //扣除相应积分,
        mc_credit_update($_W['member']['uid'], 'credit1', '-'.$_GPC['credit_num'], array($_W['member']['uid'], '订单'.$ordersn.'减积分'.$_GPC['credit_num']));
        load()->func('logging');
        logging_run('订单'.$ordersn.'减积分'.$_GPC['credit_num']);

        //能够找到邀请码并且邀请码的主人没有被拉黑
        if($invitation && !$blacklist['withdraw_code']){
            $data = array();
            $data['uniacid'] = $_W['uniacid'];
            $data['offered_id']     = $orderid;
            $data['uid']     = $uid;
            $data['code']    = $invitation['code'];
            $data['status']  = 0;
            $data['type']    = 1;
            $data['withdraw']= 0;
            pdo_insert('yuexiage_travelmall_orders_code', $data);
        }
        $order_status['error']=0;
        $order_status['msg']='订单保存成功!开始支付';

        $text = "亲爱的:<br>".$contact_name.'您预订的:'.$goods_name.'订单已保存！订单号:'.$ordersn.'。<br>'.date("Y-m-d H:i:s");
        ihttp_email($email,'订单通知',$text);


        //获取用户要充值的金额数
        $fee = floatval($_GPC['total']);
        if($fee <= 0) {
            message('支付错误, 金额小于0');
        }
        // 一些业务代码。
        //构造支付请求中的参数
        $params = array(
            'theme_id' => $ordersn,      //充值模块中的订单号，此号码用于业务模块中区分订单，交易的识别码
            'ordersn' => $ordersn,  //收银台中显示的订单号
            'title' => '微信支付',          //收银台中显示的标题
            'fee' => $fee,      //收银台中显示需要支付的金额,只能大于 0
            'user' => $_W['member']['uid'],     //付款用户, 付款的用户名(选填项)
        );
        //调用pay方法
        $this->pay($params);
        include $this->template('fit_saveorder');
    }else{
        $order_status['error']=1;
        $order_status['msg']='订单保存失败!!';
        include $this->template('fit_saveorder');
        exit;
    }
}else{
    $sql = "SELECT *,FROM_UNIXTIME(start_date,'%m-%d-%Y') as dat FROM ".tablename('yuexiage_travelmall_fit_amount')." WHERE offered_id = :offered_id and uniacid = :uniacid and stock>0 and start_date>".time();
    $amounts = pdo_fetchall($sql, array(':offered_id'=>$id,':uniacid' => $_W['uniacid']));

    foreach ($amounts as $key => $value) {
        $json[$value['dat']] = "<div class='price_day' id='".$value['id']."' stock='".$value['stock']."' adult='".$value['adult_price']."' child='".$value['child_price']."'>".$value['adult_price']."</div>";
    }
    $json=json_encode($json);
    include $this->template('fit_calendar');
}
