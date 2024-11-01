<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

include_once('vsf_simple_block.php');
include_once('vsf_simple_block_essentials.php');
include_once('vsf_simple_block_setup.php');

class VSFBlockURLRule
{
	public static $RULE = "urlRuleInstance";
	
	protected $rule;
	protected $type;
	protected $url;
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
	function __construct4($ruleValue, $typeValue, $urlValue, $descriptionValue)
	{
		VSFBlockEssentials::log("$ruleValue, $typeValue, $urlValue, $descriptionValue");
		
		$this->rule = VSFBlockEssentials::isEmpty($ruleValue) ? null : (int) $ruleValue;
		$this->type = VSFBlockEssentials::isEmpty($typeValue) ? null : (int) $typeValue;
		$this->url = VSFBlockEssentials::isEmpty($urlValue) ? null : (string) $urlValue;
		$this->description = VSFBlockEssentials::isEmpty($descriptionValue) ? null : (string) $descriptionValue;
	}
	
	/** Add the rule object to the xml object */
	public function addRuleToXML($xmlObject)
	{
		$rule = $xmlObject->addChild(self::$RULE);
		$rule->addChild(BlockSetup::$TABLE_URL_RULE, $this->rule);
		$rule->addChild(BlockSetup::$TABLE_URL_TYPE, $this->type);
		$rule->addChild(BlockSetup::$TABLE_URL_URL, $this->url);
		$rule->addChild(BlockSetup::$TABLE_URL_DESCRIPTION, $this->description);
		
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
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_URL_RULE ) die (__("The url rule instance rule is not correct", VSF_BLOCK_DOMAIN));
					$this->rule = (int) $ruleInstanceNodeValue;
				}
				else if( $x == 1 )
				{
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_URL_TYPE ) die (__("The url rule instance type is not correct", VSF_BLOCK_DOMAIN));
					$this->type = (int) $ruleInstanceNodeValue;
				}
				else if( $x == 2 )
				{
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_URL_URL ) die (__("The url rule instance ip1 is not correct", VSF_BLOCK_DOMAIN));
					$this->url = (string) $ruleInstanceNodeValue;
				}
				else if( $x == 3 )
				{
					if( $ruleInstanceNodeName !== BlockSetup::$TABLE_URL_DESCRIPTION ) die (__("The url rule instance description is not correct", VSF_BLOCK_DOMAIN));
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
	
		$insertQuery = "SELECT count(*) FROM " . $tablePrefix . BlockSetup::$TABLE_URLS . " WHERE ";
		
		$this->queryValues = array();
		
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_URL_RULE, $this->rule);
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_URL_TYPE, $this->type);
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_URL_URL, $this->url);
		$insertQuery .= $this->buildWhereClauseSection(BlockSetup::$TABLE_URL_DESCRIPTION, $this->description);
		
		return $insertQuery;
	}
	
	/** Builds up a where clause for the select count query */
	private function buildWhereClauseSection($tableColumn, $tableColumnValue)
	{
		VSFBlockEssentials::log("column value: $tableColumnValue is blank? " . (VSFBlockEssentials::isEmpty($tableColumnValue)));
		$insertQuery = (BlockSetup::$TABLE_URL_RULE == $tableColumn ? "" : " and ") 
				. "(" . $tableColumn . (VSFBlockEssentials::isEmpty($tableColumnValue) ? " is null" : " = %" . (in_array($tableColumn, array(BlockSetup::$TABLE_URL_URL, BlockSetup::$TABLE_URL_DESCRIPTION)) ? "s" : "d")) . ")";
		
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
	
		$insertQuery = "INSERT INTO " . $tablePrefix . BlockSetup::$TABLE_URLS . 
				" (" .
					BlockSetup::$TABLE_URL_RULE . ", " .
					BlockSetup::$TABLE_URL_TYPE . ", " .
					BlockSetup::$TABLE_URL_URL . ", " .
					BlockSetup::$TABLE_URL_DESCRIPTION .
				") values (" . $this->buildValuesSection() . ")";
					
		VSFBlockEssentials::log("insert query: " + $insertQuery);
		
		return $insertQuery;
	}
	
	/** Builds up the insert values */
	private function buildValuesSection()
	{
		$values = "";
		
		$colsAndAssociatedValue = array(
			array($this->rule, "%d"), 
			array($this->type, "%d"), 
			array($this->url, "%s"), 
			array($this->description, "%s"));
		
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
	public function __toString() { return ' Url Rule Object: [rule: ' . $this->rule . ', type: ' . $this->type . ', url: ' . $this->url . ', description: ' . $this->description . ']<br />'; }
}

?>