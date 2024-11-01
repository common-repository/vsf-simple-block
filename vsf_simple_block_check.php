<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

class VSFBlockCheck
{
	private static $FINE = 0;
	private static $FILTERED = 1;
	
	public function checkHit()
	{
		VSFBlockEssentials::log("checking the hit");
		
		$ipAddressOfUser = $_SERVER['REMOTE_ADDR'];
		$hostByAddress = gethostbyaddr($ipAddressOfUser);
		$browser = $_SERVER['HTTP_USER_AGENT'];
		
		$vsfSimpleStatsVersion = get_option('vsf_stats_version');
		VSFBlockEssentials::log("checking the hit, vsfSimpleStatsVersion " . strlen($vsfSimpleStatsVersion));
		$simpleStats = (strlen($vsfSimpleStatsVersion) > 0) ? 'true' : 'false';
		VSFBlockEssentials::log("checking the hit, " . $simpleStats);
		
		// CREATE FUNCTION vsfBlockCheckHit(ipAddress VARCHAR(15), hostAddress TEXT, browserSummary TEXT, simpleStats BOOLEAN)
		// Call the function to see if the current hit should be blocked.  Any value above filtered is to be blocked.
		$checkHitQuery = "SELECT vsfBlockCheckHit('$ipAddressOfUser', '$hostByAddress', '$browser', $simpleStats)";
		$vsfBlockCheckHitValue = $this->checkQueryResult($checkHitQuery);
		
		VSFBlockEssentials::log("filtered? " . $vsfBlockCheckHitValue);
		VSFBlockEssentials::log("url check needed - isset? " . (isset($vsfBlockCheckHitValue)));
		VSFBlockEssentials::log("url check needed - fine? " . (1 == intval(self::$FINE)));
		VSFBlockEssentials::log("strcmp? " . (strcmp($vsfBlockCheckHitValue, self::$FINE)));
		
		if ( isset($vsfBlockCheckHitValue) && ($vsfBlockCheckHitValue == self::$FINE) )
		{
			VSFBlockEssentials::log("checking urls");
			
			$url = $_SERVER['REQUEST_URI'];
			$administrative = strpos($url,'wp-admin/');
			if( !$administrative && !VSFBlockEssentials::isEmpty($url) )
			{
				if( substr($url, 0, 1) == '/' )
				{
					$url = substr($url, 1);
				}
				
				if( !VSFBlockEssentials::isEmpty($url) )
				{
					// vsfBlockCheckURL(ipAddress VARCHAR(15), hostAddress TEXT, browserSummary TEXT, simpleStats BOOLEAN, urlAccessing TEXT)";
					$checkUrlQuery = "SELECT vsfBlockCheckURL('$ipAddressOfUser', '$hostByAddress', '$browser', $simpleStats, '$url')";
					$this->checkQueryResult($checkUrlQuery);
				}
			}
		}
		VSFBlockEssentials::log("after check");
	}
	
	private function checkQueryResult($query)
	{
		VSFBlockEssentials::log("checking the hit " . $query);
		$queryValue = mysql_query($query);
		
		if( $queryValue != false )
		{
			$checkHitReturnValue = mysql_fetch_row($queryValue);
			
			$functionReturnValue = $checkHitReturnValue[0];
			VSFBlockEssentials::log("checking the hit, " . $functionReturnValue);
			if( $functionReturnValue > self::$FILTERED )
			{
				// bounce!
				header('Location: ' . get_option("vsf_block_bounce_address"));
				// exit to stop rest of site loading.
				exit;
			}
			
			return $functionReturnValue;
		}
	}
}

?>