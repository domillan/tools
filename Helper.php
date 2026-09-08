<?php
class Helper
{
    
    public static function email($to, $subject, $message, $from)
	{
		return mail($to, $subject, $message, "From: $from");
	}

	public static function saveArchive($file, $name, $path)
	{
		if (!is_dir($path)){
			mkdir($path, 0755, true);
		}
		$filePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
		
		self::deleteArchive($filePath);
		
		if (!move_uploaded_file($file['tmp_name'], $filePath)) {
			throw new RuntimeException("Não foi possível salvar o arquivo.");
		}

		return $filePath;
	}
	
	public static function deleteArchive($path)
	{
		if (file_exists($path))
			unlink($path);
		
	}

?>