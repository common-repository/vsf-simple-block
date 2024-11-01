<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

include_once('vsf_simple_block_setup.php');
include_once('vsf_simple_block_url_rule.php');
include_once('vsf_simple_block_user_rule.php');

/** Simple filter object */
class VSFSimpleBlockRules
{
	public static $RULES_QUANTITY = "quantity";
	public static $RULES_RULES = "rules";
	public static $RULES_URL_QUANTITY = "urlQuantity";
	public static $RULES_URL_RULES = "urlRules";
	
	protected $quantity;
	protected $rules;
	protected $urlQuantity;
	protected $urlRules;
	
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
	function __construct4($quantityValue, $rulesValue, $urlQuantityValue, $urlRulesValue)
    {
        $this->quantity = isset($quantityValue) ? $quantityValue : 0;
		$this->rules = $rulesValue;
		$this->urlQuantity = isset($urlQuantityValue) ? $urlQuantityValue : 0;
		$this->urlRules = $urlRulesValue;
    }
	
	/** Getter */
	public function getRules() { return $this->rules; }
	/** Getter */
	public function getQuantity() { return $this->quantity; }
	/** Getter */
	public function getUrlRules() { return $this->urlRules; }
	/** Getter */
	public function getUrlQuantity() { return $this->urlQuantity; }
	
	public function addRuleToXML($xmlObject)
	{
		$xmlObject->addChild(self::$RULES_QUANTITY, $this->quantity);
		$rules = $xmlObject->addChild(self::$RULES_RULES);
		
		if( isset($this->rules) )
		{
			foreach($this->rules as $rule)
			{
				$rule->addRuleToXML($rules);
			}
		}
		
		
		$xmlObject->addChild(self::$RULES_URL_QUANTITY, $this->urlQuantity);
		$rules = $xmlObject->addChild(self::$RULES_URL_RULES);
		
		if( isset($this->urlRules) )
		{
			foreach($this->urlRules as $rule)
			{
				$rule->addRuleToXML($rules);
			}
		}
		
		return $xmlObject;
	}
	
	public function constructRulesFromXMLObject($xmlObject)
	{
		$rulesNodes = $xmlObject->children();
		
		// first node is quantity,
		// second is rules which is an array
		$x = 0;
		foreach($rulesNodes as $name => $value)
		{
			//echo "node name: " . $name . " node value: " . $value . "<br />";
			if( $x == 0 )
			{
				if( $name !== self::$RULES_QUANTITY ) die (__("The quantity node is not correct", VSF_BLOCK_DOMAIN));
				$this->quantity = $value;
			}
			else if( $x == 1 )
			{
				if( $name !== self::$RULES_RULES ) die (__("The rules node is not correct", VSF_BLOCK_DOMAIN));
				$tempRulesChildren = $value->children();
				
				$this->rules = array();
				
				foreach($tempRulesChildren as $ruleInstanceName => $ruleInstanceValue)
				{
					if( $ruleInstanceName !== VSFSimpleBlockUserRule::$RULE ) die (__("The rule instance node is not correct", VSF_BLOCK_DOMAIN));
					$ruleInstanceChildren = $ruleInstanceValue->children();
					
					$vsfSimpleBlockRule = new VSFSimpleBlockUserRule();
					$vsfSimpleBlockRule->constructRuleFromXMLObject($ruleInstanceChildren);
					VSFBlockEssentials::log("completed rule object: " . $vsfSimpleBlockRule);
					array_push($this->rules, $vsfSimpleBlockRule);
				}
			}
			else if( $x == 2 )
			{
				if( $name !== self::$RULES_URL_QUANTITY ) die (__("The url quantity node is not correct", VSF_BLOCK_DOMAIN));
				$this->urlQuantity = $value;
			}
			else if( $x == 3 )
			{
				if( $name !== self::$RULES_URL_RULES ) die (__("The url rules node is not correct", VSF_BLOCK_DOMAIN));
				$tempRulesChildren = $value->children();
				
				$this->urlRules = array();
				
				foreach($tempRulesChildren as $ruleInstanceName => $ruleInstanceValue)
				{
					if( $ruleInstanceName !== VSFBlockURLRule::$RULE ) die (__("The url rule instance node is not correct", VSF_BLOCK_DOMAIN));
					$ruleInstanceChildren = $ruleInstanceValue->children();
					
					$vsfSimpleBlockRule = new VSFBlockURLRule();
					$vsfSimpleBlockRule->constructRuleFromXMLObject($ruleInstanceChildren);
					VSFBlockEssentials::log("completed rule object: " . $vsfSimpleBlockRule);
					array_push($this->urlRules, $vsfSimpleBlockRule);
				}
			}
			
			$x++;
		}
	}
	
	/** Default toString */
	public function __toString() 
	{
		$output = 'Rules Object: [';
		
		
		$output = 'user quantity: ' . $this->quantity . ', user rules: ';
		foreach ( $this->rules as $ruleInstance )
		{
			$output .= $ruleInstance;
		}
		
		
		$output = 'url quantity: ' . $this->urlQuantity . ', url rules: ';
		foreach ( $this->urlRules as $ruleInstance )
		{
			$output .= $ruleInstance;
		}
		
		
		$output .= ']<br />';
		
		return $output;
	}
}

?>