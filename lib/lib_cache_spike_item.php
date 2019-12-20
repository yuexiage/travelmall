<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:22
 */

class spike_item{
    /**
     * @param $params
     * 生成常规线路详情缓存
     * key格式：spike_item:123
     */
    function make_chache($params){
        global $_W;
        $cache_key  = 'spike_item'.SEP.implode(SEP,$params);
        $offered_id        = array_shift($params);
        if( empty($offered_id)){
            throw new Exception("线路id错误!");
        }
        //查询常规线路基础信息
        $spike_item = pdo_fetch("SELECT * FROM ".tablename("yuexiage_travelmall_offered")." WHERE id = :offered_id",array(':offered_id'=>$offered_id));
        $spike_item['thumbs']       = iunserializer($spike_item['thumbs']);
        $spike_item['contain']      = htmlspecialchars_decode($spike_item['contain']);
        $spike_item['not_included'] = htmlspecialchars_decode($spike_item['not_included']);
        $spike_item['visa']         = htmlspecialchars_decode($spike_item['visa']);
        $spike_item['booked']       = htmlspecialchars_decode($spike_item['booked']);

        //获取日期
        $time = strtotime(date("Y-m-d"))+86400;
        $sql        = "SELECT FROM_UNIXTIME(start_date,'%m-%d') AS oa,adult_price,child_price,stock FROM ".tablename('yuexiage_travelmall_offered_amount')." WHERE offered_id = :offered_id and uniacid = :uniacid and start_date >:time order by start_date limit 0,6";
        $dates      = pdo_fetchall($sql,array(':offered_id'=>$offered_id,':uniacid'=>$_W['uniacid'],':time'=>$time));
        // pdo_debug();
        $spike_item['date']         = is_array($dates) ? $dates : [];
        //判断收藏
        $spike_item['collection']   = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_collection')." WHERE  uniacid = :uniacid AND uid=:uid AND offered_id=:offered_id",array(':uniacid'=>$_W['uniacid'],':uid'=>$_W['member']['uid'],':offered_id'=>$offered_id));

        //获取行程
        $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_stroke')." WHERE offered_id = :offered_id and uniacid = :uniacid order by displayorder";
        $strokes = pdo_fetchall($sql,array(':offered_id'=>$offered_id,':uniacid'=>$_W['uniacid']));
        foreach ($strokes as $key => &$stroke) {
            //住宿
            $stroke['stay']      = iunserializer($stroke['stay']);
            foreach ($stroke['stay'] as $k => &$value) {
                //查找酒店信息
                $sql    = "SELECT * FROM ".tablename('yuexiage_travelmall_hotel')." WHERE id = :id and uniacid = :uniacid ";
                $value              = pdo_fetch($sql,array(":id"=>$k,':uniacid'=>$_W['uniacid']));
                $value['thumbs']    =iunserializer($value['thumbs']);
                $value['facilities']=iunserializer($value['facilities']);
                $value['service']   =iunserializer($value['service']);
            }
            //酒店
            $stroke['viewpoint'] = iunserializer($stroke['viewpoint']);
            foreach ($stroke['viewpoint'] as $k => &$value) {
                $sql = "SELECT * FROM ".tablename('yuexiage_travelmall_viewpoint')." WHERE id = :id and uniacid = :uniacid";
                $value = pdo_fetch($sql,array(":id"=>$k,':uniacid'=>$_W['uniacid']));
                $value['thumbs'] = iunserializer($value['thumbs']);
            }
        }
        $spike_item['strokes'] = $strokes;
        cache_write($cache_key,$spike_item);
        return cache_load($cache_key);
    }

}
