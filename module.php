<?php
/**
 * 大爱旅游商城模块定义
 *
 * @author yuexiage
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class Yuexiage_travelmallModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}

	public function settingsDisplay($settings) {
        global $_GPC, $_W;
        include_once 'helper.php';
        //优惠券
        $coupons =  com_load_cache(array(
            'cache_key'=>'coupon_type',
            'type'     =>2,
        ));

        //代金券
        $cash_coupons =  com_load_cache(array(
            'cache_key'=>'coupon_type',
            'type'     =>1,
        ));
        $settings['coupon_id']  = unserialize($settings['coupon_id']);
        $settings['cash_id']    = unserialize($settings['cash_id']);
        if (checksubmit()) {
            $cfg = array(
                'shopname' 		=> $_GPC['shopname'],
				'officialweb' 	=> $_GPC['officialweb'],
                'address' 		=> $_GPC['address'],
                'phone' 		=> $_GPC['phone'],
                'defaultCity'   => $_GPC['defaultCity'],
                'ak'            => $_GPC['ak'],
                'spike_end'     => $_GPC['spike_end'],
                'credit_cash'   => $_GPC['credit_cash'],
                'coupon_id'     => serialize($_GPC['coupon_id']),
                'cash_id'       => serialize($_GPC['cash_id']),
                'credit_cash'   => $_GPC['credit_cash'],
                'consumer'      => $_GPC['consumer'],
                'description'	 => htmlspecialchars_decode($_GPC['description'])
            );
            if (!empty($_GPC['logo'])) {
                $cfg['logo'] = $_GPC['logo'];
            }
            if ($this->saveSettings($cfg)) {
                message('保存成功', 'refresh');
            }
        }
        load()->func('tpl');
		include $this->template('setting');
    }

}