<?php

	// Automatic ban for accessing this page!
	
	class VSFSpiderTrap
	{
		// see wp-config.php for these values!
		private static $DB_NAME = '*%DB_NAME%*'; // The name of the database
		private static $DB_USER = '*%DB_USER%*'; // Your MySQL username
		private static $DB_PASSWORD = '*%DB_PASSWORD%*'; // MySQL password
		private static $DB_HOST = '*%DB_HOST%*'; // and address
		
		public static function block()
		{			
			$ipAddressOfUser = $_SERVER['REMOTE_ADDR'];
			$hostByAddress = gethostbyaddr($ipAddressOfUser);
			$browser = $_SERVER['HTTP_USER_AGENT'];
			
			$query = "SELECT vsfBlockSpiderTrapHit ('$ipAddressOfUser', '$hostByAddress', '$browser')";
		
			session_start();

			$connection = mysql_connect(self::$DB_HOST, self::$DB_USER, self::$DB_PASSWORD);
			mysql_select_db(self::$DB_NAME, $connection);
			mysql_query($query);
			mysql_close($connection);
		}
	}
	
	VSFSpiderTrap::block();
?>