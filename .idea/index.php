<?php
require_once 'class.Grid_Object.php';

$grid = Grid_Object::getInstance();
$grid->setColumns(array(5,4,2,1));
$grid->setClassName('thumbs');
$html = $grid->process();