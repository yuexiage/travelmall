<?php
$slideshow = $thisModel;
$slideshow['img'] = iunserializer($slideshow['img']);
$slideshow['lnk'] = iunserializer($slideshow['lnk']);
$count = count($slideshow['img']);
include $this->template('module'.DS.'slideshow');