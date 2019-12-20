<?php
/**
 * 大爱旅游商城模块订阅器
 *
 * @author yuexiage
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class Yuexiage_travelmallModuleReceiver extends WeModuleReceiver {
	public function receive() {
		global $_GPC, $_W;
        load()->func('logging');
        //记录文本日志
        logging_run('记录字符串日志数据');
		$type = $this->message['type'];
        $code = 'U'.date("ymdHis").str_pad(mt_rand(1, 999), 2, '0', STR_PAD_LEFT);
        $uid  = $_W['member']['uid'];
		if($uid){
			$sql  = "SELECT code FROM ".tablename('yuexiage_travelmall_member_code')." WHERE uniacid = :uniacid and uid=".$uid;
			$co = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
			if(!$co){
				$data = array();
				$data['uid']        = $uid;
				$data['uniacid']    = $_W['uniacid'];
				$data['code']       = $code;
				pdo_insert('yuexiage_travelmall_member_code', $data); 
			}
		} 
	}
}