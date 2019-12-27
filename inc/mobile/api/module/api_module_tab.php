<?php
$tab  = $thisModel;
$tab['img'] = iunserializer($tab['img']);
$style = !empty($tab['style']) ? $tab['style'] : 'default';
if(!empty($tab['img'])){
    foreach($tab['img'] as $tab_id =>&$v){
        $t = com_load_cache(array(
            'cache_key'          =>'tab_item',
            'tab_id'             =>$tab_id,
        ));
        $v = ['name'=>$t['name'],'tab_id'=>$tab_id];
    }
    $tab['img'] = array_values($tab['img']);
    include $this->template('module'.DS.'tab'.DS.$style);
}
