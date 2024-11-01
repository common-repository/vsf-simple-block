<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

class VSFSimpleBlockSpidertrapDownload
{
	public function checkDownloadSpidertrapPost()
	{
		$stringToTest = $_POST['vsfBlockDownloadSpidertrap'];
		if (!(is_null($stringToTest) || $stringToTest == ""))
		{
			$dir_path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
			
			if( false == ($fileContent = file_get_contents($dir_path . 'spidertrap.php')) )
			{
   				echo "Could not read spidertrap file.";
			}
			else
			{
				// replace the 4 required db values
				$fileContent = str_replace('*%DB_NAME%*', constant('DB_NAME'), $fileContent);
				$fileContent = str_replace('*%DB_USER%*', constant('DB_USER'), $fileContent);
				$fileContent = str_replace('*%DB_PASSWORD%*', constant('DB_PASSWORD'), $fileContent);
				$fileContent = str_replace('*%DB_HOST%*', constant('DB_HOST'), $fileContent);
				
				$this->download($fileContent);
			}
		}
	}
	
	private function download($fileContent)
	{
		header("Content-Type:text/php");
		header('Content-Disposition: attachment; filename="spidertrap.php"');
		echo $fileContent;
		exit;
	}
}

?>