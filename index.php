<?php
require_once 'class.LayoutTemplate.php';
require_once 'class.Grid_Object.php';

$styles = file_get_contents('styles/grid.css', true);

$_tpl = new LayoutTemplate('common.tpl');
$_tpl->default_style = 'css/default.css';
$_tpl->styles = preg_replace('/[\\r\\n\\t]*\s\s+/', ' ', $styles);

$grid = Grid_Object::getInstance();
$grid->setColumns(array(5=>1025,4=>1024,3=>960,2=>768,1=>480));
$grid->setClassName('thumbs');
$grid->setContent(array('Cell One', 'Cell Two', 'Cell Three', 'Cell Four', 'Cell FIve', 'Cell Six', 'Cell Seven', 'Cell Eight', 'Cell Nine', 'Cell Ten'));
$grid->generateCss(true);
$grid->setDebug(true);

$html = $grid->process();
$_tpl->styles .= $grid->getCss();
$_tpl->content = $html;

echo $_tpl->getHtml(true);