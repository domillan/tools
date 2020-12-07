<?php
function __autoload($class){
    require_once( $class.".php");
}
function requestFields($fields)
{
    $retorno = array();
    foreach($fields as $field){
        $retorno[$field] = $_REQUEST[$fields];
    }
}

session_start();


?>