<?php
$recommend  = $thisModel;
$recommend['img'] = iunserializer($recommend['img']);
$style = !empty($recommend['style']) ? $recommend['style'] : 'default';
if(!empty($recommend['img']['img'])){
    //改变数组格式
    //$recommend_content = array_chunk($recommend['img']['img'],6,true);
    $html = '';
    include $this->template('module'.DS.'recommend'.DS.$style);
}
