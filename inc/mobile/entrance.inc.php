<?php
global $_GPC, $_W;
$title      = $this->settings['shopname'];
if($_W['isajax']){
    //查询首页所有模块
    $sql     = "SELECT name,mid,type FROM ".tablename('yuexiage_travelmall_module')." WHERE pid = :pid and enabled = :enabled and uniacid = :uniacid order by displayorder";
    $modules = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'],':pid'=>1,':enabled'=>1));
    foreach ($modules as $key => $value) {
        $thisModel      = com_load_cache(array(
            'cache_key'          =>'module_'.$value['type'],
            'pid'                =>1,
            $value['type'].'_id' =>$value['mid'],
        ));
        switch ($value['type']) {
            case 'slideshow':
                include 'api/module/api_module_slideshow.php';
                break;
            case 'recommend':
                include 'api/module/api_module_recommend.php';
                break;
            case 'tab':
                include 'api/module/api_module_tab.php';
                break;
            case 'poop':
                $_poops = iunserializer($thisModel['line'])[1];
                $ids    = implode(',', array_keys($_poops));
                $sql    = "SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE id in ($ids)";
                $offeredList = pdo_fetchall($sql);
                break;
            case 'spike':
                $thisModel['time'] = iunserializer($thisModel['time']);
                $thisModel['line'] = iunserializer($thisModel['line']);
                foreach ($thisModel['time'] as $key => $value) {
                    $thisModel['time'][$key] = strtotime($value);
                }

                asort($thisModel['time']);
                $home   = array(end(array_keys($thisModel['time']))=>end($thisModel['time']));
                $toDayOver = 0;
                foreach ($thisModel['time'] as $key => $value) {
                    if($value > time()){
                        $toDayOver = 0;
                        //未开始
                        break;
                    }elseif ( time()>end($thisModel['time']) ) {
                        //结束
                        $toDayOver = 3;
                        $home = array(array_search(end($thisModel['time']),$thisModel['time']) => end($thisModel['time']) );
                        $homeEndTime = end($thisModel['time'])+$spike_end;
                        break;
                    }elseif ( $value < time() && ($value+$spike_end)>time()) {
                        //进行中
                        $toDayOver = 1;
                        $home = array($key => $value);
                        $homeEndTime = $value+$spike_end;
                        break;
                    }elseif (($value+$spike_end)<time() && time()<current($thisModel['time'])) {
                        //等待中
                        $toDayOver = 2;
                        $home = array(array_search(current($thisModel['time']),$thisModel['time']) => current($thisModel['time']) );
                        $homeEndTime = current($thisModel['time'])+$spike_end;
                        break;
                    }
                }

                foreach ($thisModel['line'] as $key => &$value) {
                    foreach ($value as $k => $val) {
                        $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE id = $k";
                        $thisModel['line'][$key][$k] = pdo_fetch($sql);
                    }
                }

                //首页显示内容的Key
                $todyKey    = current(array_keys($home));
                $homeItem   = $thisModel['line'][$todyKey];
                break;
            case 'filter':
                $thisModel['img']         = iunserializer($thisModel['img']);
                $thisModel['condition']   = iunserializer($thisModel['condition']);
                break;
            default:
                # code...
                break;
        }
        $home_modules[] = $thisModel;
    }
}else{
    $citys = pdo_fetchall("SELECT tc.* FROM ".tablename('yuexiage_travelmall_city')." as tc LEFT JOIN ".tablename('yuexiage_travelmall_country')." as tu ON tc.pcate = tu.id WHERE tc.uniacid = '{$_W['uniacid']}' and tc.enabled = 1 and tu.enabled = 1 and tu.name='中国' ORDER BY tc.displayorder ASC, tc.id ASC" , array(), 'id');
    $uid = $_W['fans']['uid'];
    $params[':uniacid'] = $_W['uniacid'];
    $sql = 'SELECT * FROM ' . tablename('yuexiage_travelmall_search_keyword') . " WHERE `uniacid` = :uniacid AND `uid` = '{$uid}' GROUP BY  keyword  order by id desc LIMIT 10 ";
    $keywords = pdo_fetchall($sql,$params);

    include $this->template('entrance');
}






















