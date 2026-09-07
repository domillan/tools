<?php

spl_autoload_register(function ($name) {
	if(file_exists("tools$name.php"))
		include_once("tools/$name.php");
	else
		include_once("model/$name.php");
});

function init($infos)
{
	foreach($infos as $key => $data)
	{
		if (!isset($GLOBALS[$key]))
			$GLOBALS[$key] = data;
	}
	$GLOBALS['PATH_INFO'] = pathinfo($_SERVER['SCRIPT_FILENAME']);
	$GLOBALS['ROOT'] = str_replace ( '/'.$GLOBALS['PATH_INFO']['basename'] , '' , $_SERVER['SCRIPT_NAME']);
}

function redirect($url, $permanent = false)
{
    if (headers_sent() === false)
        header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    exit();
}


function request($campos)
{
	if(is_array($campos))
	{
		$retorno = [];
		foreach ($campos as $campo)
		{
			$retorno[$campo] = (isset($_REQUEST[$campo])? $_REQUEST[$campo] :null);
		}
		return $retorno;
	}
	else
	{
		return (isset($_REQUEST[$campos]) ? $_REQUEST[$campos] :null);
	}

}

function url()
{
	return root($_SERVER['REQUEST_URI']);
}

function path()
{
	$path = explode("?", $_SERVER['REQUEST_URI'])[0];

	$path = str_replace ( '#'.root() , '' , '#'.$path);

	if($path == '')
	$path = 'index';

	if(@end(explode(".", $path)) == $path)
		$path = $path.'.php';
	
	if(file_exists("control/$path"))
		return "control/$path";
	
	return "control/404.php";
}

function root($path='')
{
	$path = preg_replace ('/^\//' , '',  $path);

	if($_SERVER['HTTP_HOST']!='localhost')
		return "/$path";
	
	return "/$path";
}

session_start();

include_once(path());

?>