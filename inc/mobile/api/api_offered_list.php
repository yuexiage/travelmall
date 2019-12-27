<?php
//线路列表
global $_GPC, $_W;
try{
    $pindex     = max(1, intval($_GPC['page']));
    $psize      = 10;
    $condition  = ' ';
    $order_by   = ' order by id asc';

    //特色标签 filter_tab
    if (!empty($_GPC['tab_id'])){
        $condition  .= " AND tabs like  '%".$_GPC['tab_id']."%' ";
    }
    $html_arr = ['left'=>'','right'=>''];
    $params[':uniacid'] = $_W['uniacid'];
    $offered  = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_offered')." WHERE uniacid = :uniacid $condition $order_by LIMIT ".($pindex - 1) * $psize.','.$psize,$params);

    //验证
    if(empty($offered)){
        throw new Exception("没有更多数据!",42);
    }

    $offered = array_chunk($offered,2);
    foreach ($offered as $val){
        foreach ($val as $k => $v){
            if(!empty($v)){
                $imgs = iunserializer($v['thumbs']);
                $span = '';
                if(!empty($v['tabs'])){
                    $tabs = iunserializer($v['tabs']);
                    foreach ($tabs as $tab){
                        $span .='<span>'.$tab.'</span>';
                    }
                }
                $html = '
                    <div class=" mui-col-sm-12 mui-col-xs-12 waterfall-item">
                        <div class="mui-card" offered_id = "'.$v['offered_id'].'">
                            <div class="mui-card-header mui-card-media">
                                <img src="'.tomedia($imgs[0]).'" width="100%">
                                <div class="price">'.$v['adult_price'].'￥</div>
                            </div>
                            <div class="mui-card-content">
                                <div class="mui-card-content-inner">
                                    <p style="color: #333;">'.$v['name'].'</p>
                                    <p>'.$span.'</p>
                                </div>
                            </div>
                        </div>
                    </div>';
            }
            if($k == 0 ){
                $html_arr['left'] .=$html;
            }else{
                $html_arr['right'] .=$html;
            }
        }
    }
    echo json($html_arr);
} catch (Exception $e) {
    echo json('',$e->getCode(),$e->getMessage());
}
