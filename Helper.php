<?php
class Helper
{
    
    public static function email($to, $subject, $message, $from)
	{
		return mail($to, $subject, $message, "From: $from");
	}

	public static function saveArchive($file, $name, $path)
	{
		//salva o arquivo
	}
	
	public static function deleteArchive($path)
	{
		//apaga o arquivo
	}

?>