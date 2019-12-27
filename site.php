<?php
/**
 * 大爱旅游商城模块微站定义
 *
 * @author yuexiage
 * @url http://bbs.we7.cc/
 */

defined('IN_IA') or exit('Access Denied');
define('PI',3.1415926535898);
define('EARTH_RADIUS',6378.137);
load()->func('logging');
class Yuexiage_travelmallModuleSite extends WeModuleSite {

	public function __construct(){
		global $_GPC, $_W;
		include_once 'helper.php';
		$sql        = 'SELECT `settings` FROM ' . tablename('uni_account_modules') . ' WHERE `uniacid` = :uniacid AND `module` = :module';
		$settings   = pdo_fetchcolumn($sql, array(':uniacid' => $_W['uniacid'], ':module' => 'yuexiage_travelmall'));
		$this->settings = iunserializer($settings);
		$defaultCity = $this->settings['defaultCity'];
		//拒绝授权获取位置的时候，可以使用的默认城市
		$_SESSION['nowGPSCity'] = $defaultCity;
		if($_GPC['newCity']){
			$_SESSION['nowGPSCity'] = $_GPC['newCity'];
			//手选城市
			$_SESSION['newGPSCity'] = $_GPC['newCity'];
		}
	}

	//计算范围，可以做搜索用户
	function GetRange($lat,$lon,$raidus){
	  //计算纬度
		$degree = (24901 * 1609) / 360.0;
		$dpmLat = 1 / $degree;
		$radiusLat = $dpmLat * $raidus;
		$minLat = $lat - $radiusLat; //得到最小纬度
		$maxLat = $lat + $radiusLat; //得到最大纬度
	  	//计算经度
		$mpdLng = $degree * cos($lat * (PI / 180));
		$dpmLng = 1 / $mpdLng;
		$radiusLng = $dpmLng * $raidus;
		$minLng = $lon - $radiusLng; //得到最小经度
		$maxLng = $lon + $radiusLng; //得到最大经度
	  	//范围
		$range = array(
		    'minLat' => $minLat,
		    'maxLat' => $maxLat,
		    'minLon' => $minLng,
		    'maxLon' => $maxLng
		);
	  	return $range;
	}
	

	//更新订单
	public function payResult($params) {
		global $_W;
		$fee = intval($params['fee']);
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		$paytype = array('credit' => '1', 'wechat' => '2', 'alipay' => '2', 'delivery' => '3');

		//$data['paytype'] = $paytype[$params['type']];
		if ($paytype[$params['type']] == '') {
			//$data['paytype'] = 4;
		}
		if ($params['type'] == 'wechat') {
			//微信流水号
			$data['transid'] = $params['tag']['transaction_id'];
		}
		if ($params['type'] == 'delivery') {
			$data['status'] = 1;
		}
		if($params['result'] == 'success'){
			//更新订单状态
			$order = pdo_get('yuexiage_travelmall_orders', array('ordersn' =>$params['theme_id']));
			pdo_update('yuexiage_travelmall_orders',$data, array('ordersn' => $params['theme_id']));
			//结束更新订单状态

			//更新优惠券状态
			if($order['coupon_id']){
				$coupon = array();
	            $coupon['status'] = 3;
	            pdo_update('yuexiage_travelmall_coupon_user',$coupon, array('id' => $order['coupon_id']));
			}


			//更新现金券状态
			/*if($order['cash_id']){
				$cash = array();
	            $cash['status'] = 3;
	            pdo_update('yuexiage_travelmall_coupon_user',$cash, array('id' => $order['cash_id']));
			}*/
			
			if(strpos($params['theme_id'], 'F')!==false){
			    $offered = pdo_get('yuexiage_travelmall_offered', array('id' =>$order['goods_id']));
			}else{
			    $offered = pdo_get('yuexiage_travelmall_fit', array('id' =>$order['goods_id']));
			}

			

			$uid     = $_W['member']['uid'];
			//更新邀请码状态
			if( $order['invitation'] && $offered['cash_id'] ){
				//获取代金券
				$cash = pdo_get('yuexiage_travelmall_coupon', array('id' =>$offered['cash_id']));
				if($cash['enabled']=='1'){
					//发送现金券
					$data = array();
					$data['uniacid'] = $_W['uniacid'];
					$data['uid']	 = $uid;
					$data['rid']	 = $order['goods_id'];
					$data['enabled'] = '0';
					$data['country_id']	 = $offered['cash_id'];
					$data['createtime'] = time();
					$data['endtime']	= time()+($cash['days']*86400);
					$data['theme_id'] 		= '1';
					$data['status'] 	= '1';
					pdo_insert('yuexiage_travelmall_coupon_user', $data);
				}

				//更新号码状态
				$data = array();
	            $data['uniacid'] = $_W['uniacid'];
	            $data['offered_id']     = $order['id'];
	            $data['uid']     = $uid;
	            $data['code']    = $order['invitation'];
	            pdo_update('yuexiage_travelmall_orders_code',array('status'=>'1'),$data);
			}

			//返利积分
			if($offered['credit']){
				mc_credit_update($_W['member']['uid'], 'credit1', $offered['credit'], array($_W['member']['uid'], '订单'.$params['theme_id'].'返利积分'.$offered['credit']));
			}
			
			if ($params['from'] == 'return') {
				message('支付成功！', '../../app/' . $this->createMobileUrl('spike_item',array('id'=>$order['goods_id'])), 'success');
			}
			
		}else{
			message('支付失败！', '../../app/' . $this->createMobileUrl('spike_item',array('id'=>$order['goods_id'])), 'success');
		}
		
	}


	//获取2点之间的距离
	function GetDistance($lat1, $lng1, $lat2, $lng2){
		$radLat1 = $lat1 * (PI / 180);
		$radLat2 = $lat2 * (PI / 180);
		$a = $radLat1 - $radLat2;
		$b = ($lng1 * (PI / 180)) - ($lng2 * (PI / 180));
		$s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
		$s = $s * EARTH_RADIUS;
		$s = round($s * 10000) / 10000;
	  	return $s;
	}


	public function getRegions($type,$id){ 
		global $_GPC, $_W;
		$Regions= pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_'.$type)." WHERE  uniacid = {$_W['uniacid']} AND enabled=1 AND pcate={$id}");
		return $Regions;
	}

	public function doMobileCollection(){
		global $_GPC, $_W;
		$uid = $_W['member']['uid'];
		$collection= pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_collection')." WHERE  uniacid = {$_W['uniacid']} AND uid={$uid} AND offered_id={$_GPC['id']}");
		if ($collection) {
			if($collection['status']=='0'){
				//更新状态
				$data = array('status'=>'1');
				$result = pdo_update('yuexiage_travelmall_collection',$data, array('id' => $collection['id']));
			}
		}else{
			//插入内容
			$data = array();
			$data['uniacid'] = $_W['uniacid'];
			$data['uid'] = $uid;
			$data['offered_id'] = $_GPC['id'];
			$data['type'] = 1;
			$data['status'] = 1;
			pdo_insert('yuexiage_travelmall_collection', $data);
		}
		$msg['error']	='0';
		$msg['msg']		='收藏成功!';
		echo json_encode($msg);
	}
	//gpg 转百度坐标
	public function doMobileGetgps(){
		global $_GPC, $_W;
		$settings	=$this->module['config'];
		$ak 		= $settings['ak'];
    	$lat 		=	$_GPC['latitude'];
    	$lng 		=	$_GPC['longitude'];
    	if($ak=='' || $lat=='' || $lng==''){
    		echo 0;
    		exit;
    	}
    	$c 			=	file_get_contents("http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x=$lng&y=$lat");
    	$arr 		=(array)json_decode($c);
	    if(!$arr['error'])
	    {
	        $lat 	=base64_decode($arr['y']);
	        $lng 	=base64_decode($arr['x']);
	    }
	    $address 	= file_get_contents("http://api.map.baidu.com/geocoder/v2/?output=json&ak=$ak&location=$lat,$lng");
	    if(!$_SESSION['newGPSCity']){
	    	echo json_encode($address);
	    	$address = json_decode($address,true);
            $city = $address['result']['addressComponent']['city'];
            $city = mb_ereg_replace('市','',$city);
    		$_SESSION['nowGPSCity'] = $city;
	    }elseif($_GPC['getGps']=='1') {
	    	echo json_encode($address);
	    }
	}   

	//获取转换坐标
	public function doMobileConversion(){
		global $_GPC, $_W;
		$settings	=$this->module['config'];
		$ak 		= $settings['ak'];
		$lat 		=	$_GPC['latitude'];
    	$lng 		=	$_GPC['longitude'];
    	if($ak=='' || $lat=='' || $lng==''){
    		echo 0;
    		exit;
    	}
    	$c 			=	file_get_contents("http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x=$lng&y=$lat");
    	$arr =(array)json_decode($c);
    	if(!$arr['error'])
	    {
	        $lat 	=base64_decode($arr['y']);
	        $lng 	=base64_decode($arr['x']);
	    }
    	$a = array('y'=>$lat,'x'=>$lng);
    	echo json_encode($a);
	}

	//获取模块详情
	public function getModuleInfo($id,$type){
		global $_GPC, $_W;
		$sql 	= "SELECT * FROM ".tablename('yuexiage_travelmall_module_'.$type)." WHERE uniacid = :uniacid and ".$type."_id= :".$type."_id";
		$module = pdo_fetch($sql,array(
		    ":uniacid"      => $_W['uniacid'],
            ':'.$type.'_id' => $id,
        ));
		$module['type'] = $type;
		return $module;
	}

	//生成密码
	function generate_password( $length = 8 ) {
	// 密码字符集，可任意添加你需要的字符
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$password = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
		}
		return $password;
	}

	//领取优惠券
	public function doMobileGetCoupon (){
		global $_GPC, $_W;
		$country_id = $_GPC['country_id'];//优惠券ID
		$id = $_GPC['id'];  //线路id
		$uid = $_W['member']['uid'];
		//查询用户是否已经领取
		//检查是否有此优惠券
		$sql = "SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE uniacid ={$_W['uniacid']} and enabled=1 and theme_id = 2 and id=$country_id";
		$coupon = pdo_fetch($sql); 
		if(!$coupon){
			$msg['error'] = 1;
			$msg['msg'] = '优惠券无效';
			echo json_encode($msg);
			exit;
		}
		$sql = "SELECT * FROM ".tablename('yuexiage_travelmall_coupon_user')." WHERE uid= $uid and rid=$id and endtime >".time();
		$coupon_user = pdo_fetch($sql); 

		if(!$coupon_user){
			$sql = "SELECT coupon_id FROM ".tablename('yuexiage_travelmall_offered')." WHERE id= $id";
			$coupon_id = pdo_fetch($sql); 
			if($coupon_id['coupon_id'] == $country_id){
				//发优惠券
				$endtime = $coupon['days']*86400+time();
				$data = array();
				$data['uniacid'] = $_W['uniacid'];
				$data['uid']     = $uid;
				$data['rid']     = $id;
				$data['enabled'] = 1;
				$data['country_id']     = $country_id;
				$data['theme_id']     = 2;
				$data['status']  = 1;
				$data['createtime']  = time();
				$data['endtime']  = $endtime;
				pdo_insert('yuexiage_travelmall_coupon_user', $data);
				$msg['error'] = 0;
				$msg['msg'] = '优惠券已保存进您的账户!';
				echo json_encode($msg);
			}else{
				$msg['error'] = 1;
				$msg['msg'] = '优惠券无效';
				echo json_encode($msg);
			}
		}else{
			$msg['error'] = 1;
			$msg['msg'] = '不能重复领取!';
			echo json_encode($msg);
		}

		

	}

	//获取标签对应商品
	public function doMobileGetTabGoods(){
		global $_GPC, $_W;
		$tabs = $this->getAllTab();
		$tab  = array_shift($tabs);
		$tabId = $_GPC['tabId']?$_GPC['tabId']:$tab['id'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 5;
		//查询城市名称ID
        $cityName = $_SESSION['newGPSCity']?$_SESSION['newGPSCity']:mb_ereg_replace('市','',$_SESSION['nowGPSCity']);
		$city = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE name like '%{$cityName}%' and enabled=1 ");
		if(!$city){
			$defaultCity = mb_ereg_replace('市','',$this->settings['defaultCity']);
			$city = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_city')." WHERE name = '%{$defaultCity}%' and enabled=1 ");
		}

		//查询线路
		$condition = " AND tof.enabled = 1 AND tof.tab_id = $tabId AND tof.departure_city= {$city['id']}";
		$offered = pdo_fetchall("SELECT tof.*,tc.name as tname,ti.name as tiname,tt.name as ttname,th.name as thname FROM ".tablename('yuexiage_travelmall_offered')." as tof LEFT JOIN ".tablename('yuexiage_travelmall_categorys')." AS tc ON tof.category_id = tc.id  LEFT JOIN ".tablename('yuexiage_travelmall_city')." as ti ON tof.departure_city = ti.id LEFT JOIN ".tablename('yuexiage_travelmall_tabs')." as tt ON tof.tab_id = tt.id LEFT JOIN ".tablename('yuexiage_travelmall_theme')." as th ON tof.theme_id = th.id WHERE tof.uniacid = '{$_W['uniacid']}' $condition ORDER BY  tof.displayorder ASC, tof.id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);

		foreach ($offered as $key => $value) {
			$imgs = iunserializer($value['thumbs']);
			$url = $this->createMobileUrl('spike_item',array('id'=>$value['id']));
            $html .= '<li class="mui-table-view-cell mui-media"><a href="'.$url.'"><div class="card_tab">'.$value["ttname"].'</div><img class="mui-media-object mui-pull-left" src="'.tomedia($imgs['0']).'">
                    <div class="mui-media-body">'.'['.$value["tiname"].']'.$value["name"].'<p class="mui-ellipsis">'.$value['description'].'</p><p class="adult_price"><sub>￥</sub>'.$value['adult_price'].'<sub>起</sub></p></div></a></li>';

        }
		echo $html;
	}

	public function doMobileChecklottery(){
		global $_GPC, $_W;
		$type = $_GPC['t']?$_GPC['t']:'1';
		if($type=='1'){
			$activity = 'yuexiage_travelmall_bigwheel';
			$activity_winner = 'yuexiage_travelmall_bigwheel_winner';
		}elseif($type=='2'){
			$activity = 'yuexiage_travelmall_scratchcard';
			$activity_winner = 'yuexiage_travelmall_scratchcard_winner';
		}
		$bigwheel = pdo_fetch("SELECT * FROM ".tablename($activity)." WHERE  enabled=1 and uniacid = {$_W['uniacid']}");
		if($bigwheel['follow']){
		    //如果必须关注，验证关注
		    checkauth();
		}


		//判断时间
		$datelimit_start = strtotime($bigwheel['datelimit_start']);
		$datelimit_end   = strtotime($bigwheel['datelimit_end']);
		$end=0;

		if($datelimit_start>time()){
		    //未开始
		   echo $end = 4;
		    exit;
		}elseif($datelimit_end<time()){
		    //活动已结束

		    echo $end = 5;
		    exit;
		}

		//每人最多获奖次数
		$award_times  = $bigwheel['award_times'];
		//每人最多抽奖次数
		$number_times = $bigwheel['number_times'];
		//每人每天最多抽奖次数
		$most_num_times = $bigwheel['most_num_times'];

		//查询已参加次数
		$uid = $_W['member']['uid'];
		$winner = pdo_fetchall("SELECT * FROM ".tablename($activity_winner)." WHERE  from_user={$uid} and uniacid = {$_W['uniacid']}");

		//已经参加次数
		$number_times_uid = count($winner);

		
		foreach ($winner as $key => $value) {
		    if($value['status']>0){
		        //获奖次数
		        $award_times_uid += 1;
		    }
		    if(date("Y-m-d",$value['createtime'])==date("Y-m-d")){
		        //今日抽奖次数
		        $most_num_times_uid += 1;
		    } 
		}

		if(isset($number_times_uid) && $number_times_uid>=$number_times){
		    //不能再参加
		   echo  $end = 1;
		    exit;
		}
		if(isset($award_times_uid) && $award_times_uid>=$award_times){
		    //不能再参加
		    echo  $end = 2;
		    exit;
		}
		if(isset($most_num_times_uid) && $most_num_times_uid>=$most_num_times){
		    //不能再参加
		    echo $end = 3;
		    exit;
		}
		echo $end;
	}

	public function doMobilegetAward(){
		global $_GPC, $_W;
		$type = $_GPC['t']?$_GPC['t']:'1';
		if($type=='1'){
			$activity = 'yuexiage_travelmall_bigwheel';
			$activity_winner = 'yuexiage_travelmall_bigwheel_winner';
		}elseif($type=='2'){
			$activity = 'yuexiage_travelmall_scratchcard';
			$activity_winner = 'yuexiage_travelmall_scratchcard_winner';
		}
		//计算每个礼物的概率
		$bigwheel = pdo_fetch("SELECT * FROM ".tablename($activity)." WHERE  enabled=1 and uniacid = {$_W['uniacid']}");
		$award = array();
		$gifts=array('1'=>'one','2'=>'two','3'=>'three','4'=>'four','5'=>'five','6'=>'six');

		foreach ($gifts as $key=> $gift){
			if (empty($bigwheel['c_rate_'.$gift])) {
				//如果几率为空
				continue;
			}
			for($i=0;$i<$bigwheel['c_rate_'.$gift];$i++){
				$award[]=$key;
			}
			
		}

		$s= 100-count($award);
		for($i=0;$i<$s;$i++){
			$award[]=0;
		}

		$z = $award[array_rand($award)];
		$column = pdo_fetchcolumn("SELECT count(*) FROM ".tablename($activity_winner)." WHERE  rid={$z} and uniacid = {$_W['uniacid']}");
		for($i=0;$i<6;$i++){
			if( ($bigwheel['c_num_'.$gifts[$z]]-$column)<=0 ){
				continue;
			}else{
				break;
			}
		}

		if( ($bigwheel['c_num_'.$gifts[$z]]-$column)<=0 ){
			$z =0;
		}

		$credit = array(
			'uniacid' => $_W['uniacid'],
			'rid'=>$z,
			'tab_id'     => $bigwheel['c_type_'.$gifts[$z]]?$bigwheel['c_type_'.$gifts[$z]]:'0',
			'award'   => $bigwheel['c_name_'.$gifts[$z]]?$bigwheel['c_name_'.$gifts[$z]]:'0',
			'description' => $z?'恭喜您，获得了'.$z.'等奖':'很遗憾,您没能中奖!',
			'from_user'=>$_W['member']['uid'],
			'status'=>$z?'1':'0',
			'createtime'=>time()
		);
		pdo_insert($activity_winner, $credit);
		$credit['wid'] = pdo_insertid();
		load()->model('mc');
		switch ($credit['tab_id']) {
			case 0:
				# code...
				break;
			case 1:
				//积分
mc_credit_update($_W['member']['uid'], 'credit1', $bigwheel['c_name_'.$gifts[$z]], array($_W['member']['uid'], '抽奖'.$credit['wid'].'增加积分'.$bigwheel['c_name_'.$gifts[$z]]));
			load()->func('logging');
    		logging_run('积分'.$ra);
				break;
			case 2:
				//优惠券
			 $conpun = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE  enabled=1 and uniacid = {$_W['uniacid']} and id={$bigwheel['c_type_'.$gifts[$z]]}");
			$data = array();
			$data['uniacid'] = $_W['uniacid'];
   			$data['uid']     = $_W['member']['uid'];
			$data['rid']     = '-1';
			$data['enabled'] = '1';
			$data['country_id'] 		= $conpun['id'];
			$data['endtime'] = ($conpun['days']*86400)+time();
			$data['createtime'] = time();
			$data['theme_id'] = 2;
			$data['status'] = 1;
			pdo_insert('yuexiage_travelmall_coupon_user', $data);
				break;
			case 3:
				//其他
				break;
			case 4:
				//代金券
				$conpun = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_coupon')." WHERE  enabled=1 and uniacid = {$_W['uniacid']} and id={$bigwheel['c_type_'.$gifts[$z]]}");
				$data = array();
				$data['uniacid'] = $_W['uniacid'];
	   			$data['uid']     = $_W['member']['uid'];
				$data['rid']     = '-1';
				$data['enabled'] = '1';
				$data['country_id'] 		= $conpun['id'];
				$data['endtime'] = ($conpun['days']*86400)+time();
				$data['createtime'] = time();
				$data['theme_id'] = 1;
				$data['status'] = 1;
				pdo_insert('yuexiage_travelmall_coupon_user', $data);
				break;
			default:
				# code...
				break;
		}


		echo json_encode($credit);
	}


	public function doMobileCheckuser(){
		global $_GPC, $_W;
		$id  = $_GPC['id'];
		$t   = $_GPC['t'];
		$uid = $_W['member']['uid'];
		$blacklist = pdo_fetch('SELECT* FROM ' . tablename('yuexiage_travelmall_blacklist') . ' WHERE uniacid = :uniacid and uid=:uid', array(':uniacid' => $_W['uniacid'],':uid'=>$uid));
		if($blacklist){
			if($blacklist[$t]==1){
				$m['error']=1;
				echo json_encode($m);
			}else{
				$m['error']=0;
				$m['msg']  =$blacklist;
				echo json_encode($m);
			}
			
		}else{
			$m['error']=0;
			$m['msg']  =$blacklist;
			echo json_encode($m);
		}
	}

	public function doWebMail() {
		//这个操作被定义用来呈现 管理中心导航菜单
	}
	public function doWebNotice() {
		//这个操作被定义用来呈现 管理中心导航菜单
	}

	public function doMobileCoupon() {
		//这个操作被定义用来呈现 微站个人中心导航
	}

}