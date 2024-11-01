<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

include_once('vsf_simple_block.php');
include_once('vsf_simple_block_essentials.php');
include_once('vsf_simple_block_setup.php');

/** Simple rule object */
class VSFSimpleBlockUserRule
{
	public static $RULE = "ruleInstance";
	
	protected $rule;
	protected $type;
	protected $ip1;
	protected $ip2;
	protected $host;
	protected $browserKeyword;
	protected $description;
	
	protected $queryValues;
	
	/** Null constructor */
	function __construct()
    {
        $a = func_get_args();
        $i = func_num_args();
        if ( method_exists($this, $f = '__construct' . $i) )
		{
            call_user_func_array( array($this, $f), $a );
        }
    }
	
	/** Full constructor */
	function __construct7($ruleValue, $typeValue, $ip1Value, $ip2Value, $hostValue, $browserKeywordValue, $descriptionValue)
	{
		VSFBlockEssentials::log("user constructor: $ruleValue, $typeValue, $ip1Value, $ip2Value, $hostValue, $browserKeywordValue, $descriptionValue");
		
		$this->rule = VSFBlockEssentials::isEmpty($ruleValue) ? null : (int) $ruleValue;
		$this->type = VSFBlockEssentials::isEmpty($typeValue) ? null : (int) $typeValue;
		$this->ip1 = VSFBlockEssentials::isEmpty($ip1Value) ? null : (double) $ip1Value;
		$this->ip2 = VSFBlockEssentials::isEmpty($ip2Value) ? null : (double) $ip2Value;
		$this->host = VSFBlockEssentials::isEmpty($hostValue) ? null : (string) $hostValue;
		$this->browserKeyword = VSFBlockEssentials::isEmpty($browserKeywordValue) ? null : (string) $browserKeywordValue;
		$this->description = VSFBlockEssentials::isEmpty($descriptionValue) ? null : (string) $descriptionValue;
	}
	
	/** Add the rule object to the xml object */
	public function addRuleToXML($xmlObject)
	{
		$rule = $xmlObject->addChild(self::$RULE);
		$rule->addChild(BlockSetup::$TABLE_RULES_RULE, $this->rule);
		$rule->addChild(BlockSetup::$TABLE_RULES_TYPE, $this->type);
		$rule->addChild(BlockSetup::$TABLE_RULES_IP1, $this->ip1);
		$rule->addChild(BlockSetup::$TABLE_RULES_IP2, $this->ip2);
		$rule->addChild(BlockSetup::$TABLE_RULES_HOST, $this->host);
		$rule->addChild(BlockSetup::$TABLE_RULES_BROWSER_KEYWORD, $this->browserKeyword);
		$rule->addChild(BlockSetup::$TABLE_RULES_DESCRIPTION, $this->description);
		
		return $xmlObject;
	}
	
	/** Removes values from the xmlChild object and fill in this rule object */
	public function constructRuleFromXMLObject($ruleInstanceChildren)
	{
		$x = 0;
		
		foreach($ruleInstanceChildren as $ruleInstanceNodeName => $ruleInstanceNodeValue)
		{
			if( !VSFBlockEssentials::isEmpty($ruleInstanceNodeValue) )
			{
				VSFBlockEssentials::log("nodeName: $ruleInstanceNodeName - nodeValue: $ruleInstanceNodeValue");
				
				if( $x == 0 )
				{
					VSFBlockEssentials::log("ruleInstanceNodeName: " . $ruleInstanceNodeName);
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_RULES_RULE ) die (__("The rule instance rule is not correct", VSF_BLOCK_DOMAIN));
					$this->rule = (int) $ruleInstanceNodeValue;
				}
				else if( $x == 1 )
				{
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_RULES_TYPE ) die (__("The rule instance type is not correct", VSF_BLOCK_DOMAIN));
					$this->type = (int) $ruleInstanceNodeValue;
				}
				else if( $x == 2 )
				{
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_RULES_IP1 ) die (__("The rule instance ip1 is not correct", VSF_BLOCK_DOMAIN));
					$this->ip1 = (double) $ruleInstanceNodeValue;
				}
				else if( $x == 3 )
				{
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_RULES_IP2 ) die (__("The rule instance ip2 is not correct", VSF_BLOCK_DOMAIN));
					$this->ip2 = (double) $ruleInstanceNodeValue;
				}
				else if( $x == 4 )
				{
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_RULES_HOST ) die (__("The rule instance host is not correct", VSF_BLOCK_DOMAIN));
					$this->host = (string) $ruleInstanceNodeValue;
				}
				else if( $x == 5 )
				{
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_RULES_BROWSER_KEYWORD ) die (__("The rule instance browser keyword is not correct", VSF_BLOCK_DOMAIN));
					$this->browserKeyword = (string) $ruleInstanceNodeValue;
				}
				else if( $x == 6 )
				{
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_RULES_DESCRIPTION ) die (__("The rule instance description is not correct", VSF_BLOCK_DOMAIN));
					$this->description = (string) $ruleInstanceNodeValue;
				}
			}
			
			$x++;
		}
	}
	
	/** Builds up the select count query */
	public function getSelectQuery()
	{
		global $wpdb;
		$tablePrefix = $wpdb->prefix;
	
		$insertQuery = "SELECT count(*) FROM " . $tablePrefix . BlockSetup::$TABLE_RULES . " WHERE ";
		
		$this->queryValues = array();
		
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_RULES_RULE, $this->rule);
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_RULES_TYPE, $this->type);
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_RULES_IP1, $this->ip1);
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_RULES_IP2, $this->ip2);
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_RULES_HOST, $this->host);
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_RULES_BROWSER_KEYWORD, $this->browserKeyword);
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_RULES_DESCRIPTION, $this->description);
		
		return $insertQuery;
	}
	
	/** Builds up a where clause for the select count query */
	private function buildWhereClauseSection($tableColumn, $tableColumnValue)
	{
		$insertQuery = (BlockSetup::$TABLE_RULES_RULE == $tableColumn ? "" : " and ") . "(" . $tableColumn . (VSFBlockEssentials::isEmpty($tableColumnValue) ? " is null" : " = %" . (in_array($tableColumn, array(BlockSetup::$TABLE_RULES_HOST, BlockSetup::$TABLE_RULES_BROWSER_KEYWORD, BlockSetup::$TABLE_RULES_DESCRIPTION)) ? "s" : "d")) . ")";
		
		if( !VSFBlockEssentials::isEmpty($tableColumnValue) )
		{
			array_push($this->queryValues, $tableColumnValue);
		}
		
		return $insertQuery;
	}
	
	/** Builds up the insert rule query */
	public function getInsertStatement()
	{
		global $wpdb;
		$tablePrefix = $wpdb->prefix;
		
		$this->insertQueryValues = array();
	
		$insertQuery = "INSERT INTO " . $tablePrefix . BlockSetup::$TABLE_RULES . 
				" (" .
					BlockSetup::$TABLE_RULES_RULE . ", " .
					BlockSetup::$TABLE_RULES_TYPE . ", " .
					BlockSetup::$TABLE_RULES_IP1 . ", " .
					BlockSetup::$TABLE_RULES_IP2 . ", " .
					BlockSetup::$TABLE_RULES_HOST . ", " .
					BlockSetup::$TABLE_RULES_BROWSER_KEYWORD . ", " .
					BlockSetup::$TABLE_RULES_DESCRIPTION .
				") values (" . $this->buildValuesSection() . ")";
		
		return $insertQuery;
	}
	
	/** Builds up the insert values */
	private function buildValuesSection()
	{
		$values = "";
		
		$colsAndAssociatedValue = array(array($this->rule, "%d"), array($this->type, "%d"), array($this->ip1, "%f"), array($this->ip2, "%f"), array($this->host, "%s"), array($this->browserKeyword, "%s"), array($this->description, "%s"));
		
		$x = 0;
		foreach($colsAndAssociatedValue as $k)
		{
			$values .= ($x++ > 0 ? ", " : "" ) . (VSFBlockEssentials::isEmpty($k[0]) ? "null" : $k[1]);
		}
		
		return $values;
	}
	
	/** Getter for queryValues */
	public function getValuesAsArray() { return $this->queryValues; }
	
	/** Default toString */
	public function __toString() { return ' Rule Object: [rule: ' . $this->rule . ', type: ' . $this->type . ', ip1: ' . $this->ip1 . ', ip2: ' . $this->ip2 . ', host: ' . $this->host . ', browserKeyword: ' . $this->browserKeyword . ', description: ' . $this->description . ']<br />'; }
}

?>