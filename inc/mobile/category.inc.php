<?php
global $_GPC, $_W;
$category = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_categorys')." where id=".$_GPC['id'] , array(), 'id');
if(!empty($category)){
    $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_offered')." where category_id=:category_id and uniacid = :uniacid order by displayorder";
    $condition = " AND tof.enabled = 1 AND tc.id = {$category['id']}";
    $offered = pdo_fetchall("SELECT tof.*,tc.name as tname,ti.name as tiname,tt.name as ttname,th.name as thname FROM ".tablename('yuexiage_travelmall_offered')." as tof LEFT JOIN ".tablename('yuexiage_travelmall_categorys')." AS tc ON tof.category_id = tc.id  LEFT JOIN ".tablename('yuexiage_travelmall_city')." as ti ON tof.departure_city = ti.id LEFT JOIN ".tablename('yuexiage_travelmall_tabs')." as tt ON tof.tab_id = tt.id LEFT JOIN ".tablename('yuexiage_travelmall_theme')." as th ON tof.theme_id = th.id WHERE tof.uniacid = '{$_W['uniacid']}' $condition ORDER BY  tof.displayorder ASC, tof.id ");

    foreach ($offereds as $key=> $value) {
        if($offereds['upper_shelf']){
            if($offereds['lower_shelf']<time()){
                unset($offereds[$key]);
            }
        }
    }
    $category['goods'] = $offereds;
}
//var_export($offered);
$title = $category['name'];

include $this->template('category');