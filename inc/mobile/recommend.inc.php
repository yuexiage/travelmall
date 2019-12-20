<?php
global $_GPC, $_W;
$do = !empty($_GPC['op']) ? $_GPC['op'] : 'recommend';
$do = in_array($do, array('recommend', 'recommend_item')) ? $do : 'recommend';
load()->func('communication');
$uid  = $_W['member']['uid'];
$id   = $_GPC['id'];
if($do=='recommend'){
            $arr = array(
                '1'=>'吃',
                '2'=>'住',
                '3'=>'行',
                '4'=>'游',
                '5'=>'购',
                '6'=>'娱'
                );
            $title = $arr[$id];
            $categorys = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_businessmen_categorys')." where uniacid = '{$_W['uniacid']}' and enabled=1 and first_category = '{$id}'" , array(), 'id');
            $first = current($categorys);
            if($_GPC['c2']){
                $first_id = $_GPC['c2'];
            }else{
                $first_id = $first['id'];
            }
            $recommends = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_businessmen_info')." where uniacid = '{$_W['uniacid']}' and enabled=1 and businessmen_category_1 = '{$id}' and businessmen_category_2 ='{$first_id}' " , array(), 'id');

            foreach ($recommends as $key => &$recommend) {
                $category= pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_businessmen_categorys')." where uniacid = '{$_W['uniacid']}' and enabled=1 and id = '{$recommend['businessmen_category_2']}'" , array(), 'id');
                $recommend['cname'] = $category['name'];
            }
            //当前位置

            if($_W['isajax']){
                $latitude   = $_GPC['latitude'];
                $longitude  = $_GPC['longitude'];
                $type       = $_GPC['t']; //距离
                if($type>0){
                    foreach ($recommends as $key => &$recommend) {
                        $arr = explode(',', $recommend['coordinate']);
                        $recommend['distance'] = $this->GetDistance($arr['0'],$arr['1'],$latitude,$longitude);
                        if($recommend['distance']>$type){
                            unset($recommends[$key]);
                        }
                    }
                    echo json_encode($recommends);
                    exit;
                }else{
                    echo json_encode($recommends);
                    exit;
                }
            }else{
                include $this->template('recommend');
            }

    
    
}elseif($do=='recommend_item'){
    $recommend = pdo_fetch("SELECT bi.*,bc.name as bname FROM ".tablename('yuexiage_travelmall_businessmen_info')." as bi LEFT JOIN ".tablename('yuexiage_travelmall_businessmen_categorys')." as bc ON bi.businessmen_category_2 = bc.id where bi.uniacid = '{$_W['uniacid']}' and bi.enabled=1 and bi.id = '{$_GPC['id']}'" , array(), 'id');
    $title = $recommend['name'];
    checkauth();
    if (checksubmit('submit')) {
        $businessmen_pwd    = $_GPC['businessmen_pwd'];
        $user_pwd           = $_GPC['user_pwd'];
        $recommend_id       = $_GPC['recommend_id'];

        //查询商家密码
        $code = pdo_fetch("SELECT code FROM ".tablename('yuexiage_travelmall_businessmen_info')." WHERE id='{$recommend_id}' ");
        
        $code = trim($code['code']);
        $businessmen_pwd = trim($businessmen_pwd);
        if($code != $businessmen_pwd){
            message('商户密码错误!', referer(), 'error');
        }
        //查询用户密码
        $code = pdo_fetch("SELECT code FROM ".tablename('yuexiage_travelmall_member_code')." WHERE uid='{$uid}' ");
        $code = trim($code['code']);
        $user_pwd = trim($user_pwd);
        if($code != $user_pwd){
            message('用户密码错误!', referer(), 'error');
        }
        $data = array();
        $ordersn = 'C'.date("YmdHis").str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $data['order_sn']           = $ordersn;
        $data['businessmen_pwd']    = '1';
        $data['user_pwd']           = '1';
        $data['recommend_id']       =  $recommend_id;   
        $data['createtime']         = time();
        $data['uniacid']            = $_W['uniacid'];
        pdo_insert('yuexiage_travelmall_businessmen_order', $data);
        message('提交成功', referer(), 'success');
    }
    include $this->template('recommend_item');
}
