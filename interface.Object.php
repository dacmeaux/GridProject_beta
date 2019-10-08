<?php
 
interface Object_Interface{
    public function __set($name, $value);
    public function __get($name);
    public function __isset($name);
    public function process();
    public function save($data = array());
    public function delete($id, $type = 'none');
    public function getActionName();
    public function render();
    public static function getInstance();
}