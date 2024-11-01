<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

include_once('vsf_simple_block.php');
include_once('vsf_simple_block_setup.php');
include_once('vsf_simple_block_port_i.php');
include_once('vsf_simple_block_url_rule.php');
include_once('vsf_simple_block_user_rule.php');
include_once('vsf_simple_block_rules.php');
include_once('vsf_simple_block_essentials.php');

/** Relies on php 5+ */
class VSFSimpleBlockExport implements IVSFSimpleBlockPort
{
	private $tablePrefix = "";
	
	function generateExport()
	{
		global $wpdb;
		$this->tablePrefix = $wpdb->prefix;

		// user rules
		$user_rules_query_result = mysql_query("SELECT " 
				. BlockSetup::$TABLE_RULES_RULE . ", " 
				. BlockSetup::$TABLE_RULES_TYPE . ", " 
				. BlockSetup::$TABLE_RULES_IP1 . ", " 
				. BlockSetup::$TABLE_RULES_IP2 . ", " 
				. BlockSetup::$TABLE_RULES_HOST . ", " 
				. BlockSetup::$TABLE_RULES_BROWSER_KEYWORD . ", " 
				. BlockSetup::$TABLE_RULES_DESCRIPTION 
				. " FROM " . $this->tablePrefix . BlockSetup::$TABLE_RULES . 
				" ORDER BY " . BlockSetup::$TABLE_RULES_ID . " ASC");
		
		$rules = array();
		$x = 0;
		while ( $c = mysql_fetch_row($user_rules_query_result) )
		{
			$rules[$x++] = new VSFSimpleBlockUserRule($c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6]);
		}
		
		
		// url rules
		$url_rules_query_result = mysql_query("SELECT " 
				. BlockSetup::$TABLE_URL_RULE . ", " 
				. BlockSetup::$TABLE_URL_TYPE . ", " 
				. BlockSetup::$TABLE_URL_URL . ", " 
				. BlockSetup::$TABLE_URL_DESCRIPTION 
				. " FROM " . $this->tablePrefix . BlockSetup::$TABLE_URLS . 
				" ORDER BY " . BlockSetup::$TABLE_URL_ID . " ASC");
		
		$urlRules = array();
		$x = 0;
		while ( $c = mysql_fetch_row($url_rules_query_result) )
		{
			$urlRules[$x++] = new VSFBlockURLRule($c[0], $c[1], $c[2], $c[3]);
		}
		
		$userQty = mysql_num_rows($user_rules_query_result);
		$urlQty = mysql_num_rows($url_rules_query_result);
		
		VSFBlockEssentials::log("Export user qty: " . $userQty);
		VSFBlockEssentials::log("Export user rules: " . $rules);
		VSFBlockEssentials::log("Export url qty: " . $urlQty);
		VSFBlockEssentials::log("Export url rules: " . $urlRules);
		$vsfSimpleBlockRules = new VSFSimpleBlockRules($userQty, $rules, $urlQty, $urlRules);
				
		$xml = new SimpleXMLElement("<" . self::ROOT_ELEMENT . "></" . self::ROOT_ELEMENT . ">");
		$vsfSimpleBlockRules->addRuleToXML($xml);
				
		header(XML_HEADER);
			
		$now = gmdate('Y-m-d H:i');
		header('Content-Disposition: attachment; filename="VSF block rules ' . $now . '.xml"');
		
		echo $xml->asXML();
		
		exit;
	}
}

?>