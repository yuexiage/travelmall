<?php
global $_GPC, $_W;
$title      = $this->settings['shopname'];
$spike_end  = $this->settings['spike_end']*60;

$sql = "SELECT * FROM ".tablename('yuexiage_travelmall_module_spike')." WHERE id = {$_GPC['id']} and enabled = 1 and uniacid = :uniacid order by displayorder";
$spike = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
$spike['time'] = iunserializer($spike['time']);
$spike['line'] = iunserializer($spike['line']);
foreach ($spike['time'] as $key => $value) {
    $spike['time'][$key] = strtotime($value);
}
asort($spike['time']);
foreach ($spike['time'] as $key => $row) {
    $next = current($spike['time']);
    if( ($row+$spike_end)<time()){
        unset($spike['time'][$key]);
        unset($spike['line'][$key]);
    }elseif( (time()<$next) && time()>$row && time()<($row+$spike_end) ){
        //当前时间小于下一时间，大于当前时间段，小于当前时间段+结束时间
        //判断当前正在进行的时间段
        $spike['ing'] = $key;
    }
}

foreach ($spike['line'] as $key => $value) {
    foreach ($value as $k => $val) {
        $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE id = $k";
       $spike['line'][$key][$k] = pdo_fetch($sql); 
    }
}

include $this->template('spikelist');