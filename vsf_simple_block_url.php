<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

class VSFBlockURL
{
	public static $BLOCKED = true;
	
	private static $UUID = "UUID";
	private static $BLOCK_STATUS = "BLOCK_STATUS";
	private static $ONE_HOUR = 3600;
	
	public function checkCookie()
	{
		if( get_option("vsf_block_cookie_urls") == 1 )
		{
			return $this->checkBlockStatus();
		}
	}

	public function setCookie($value)
	{
		if( get_option("vsf_block_cookie_urls") == 1 )
		{
			$this->checkUUID();
			$this->setBlockStatus($value);
		}
	}
	
	private function checkUUID()
	{
		$uuid = $_COOKIE[self::$UUID];
		if( !isset($uuid) )
		{
			setcookie(self::$UUID, uniqid(), $this->getExpiryTime());
		}
	}
	
	private function setBlockStatus($value)
	{
		if( isset($value) && ($value == self::$BLOCKED) )
		{
			setcookie($BLOCK_STATUS, $value, $this->getExpiryTime());
		}
	}
	
	private function checkBlockStatus()
	{
		$blockStatus = $_COOKIE[$BLOCK_STATUS];
		if( isset($blockStatus) && $blockStatus )
		{
			return self::$BLOCKED;
		}
	}
	
	private function getExpiryTime()
	{
		$expiryChosen = get_option("vsf_block_cookie_expire_time");
		if( isset($expiryChosen) )
		{
			if( $expiryChosen < self::$ONE_HOUR )
			{
				$expiryChosen = self::$ONE_HOUR;
			}
		}
		
		$expire = time() + $expiryChosen;
		
		return $expire;
	}
}

?>