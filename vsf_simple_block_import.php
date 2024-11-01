<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

include_once('vsf_simple_block_setup.php');
include_once('vsf_simple_block_port_i.php');
include_once('vsf_simple_block_user_rule.php');
include_once('vsf_simple_block_url_rule.php');
include_once('vsf_simple_block_rules.php');

/**
 * Requires PHP 5+
 */
class VSFSimpleBlockImport implements IVSFSimpleBlockPort
{
	private $tablePrefix = "";
	
	private $alreadyInDB = 0;
	private $errorsFromInsert = 0;
	private $errorsThatOccured = array();
	private $totalRules = 0;
	
	public function importFileValues($rulesXMLFileContent)
	{
		global $wpdb;
		$this->tablePrefix = $wpdb->prefix;
		
		$xml = simplexml_load_string($rulesXMLFileContent);
		if( $xml->getName() !== self::ROOT_ELEMENT ) die (__("The root node is not correct", VSF_BLOCK_DOMAIN));
		
		$vsfSimpleBlockRules = new VSFSimpleBlockRules();
		$vsfSimpleBlockRules->constructRulesFromXMLObject($xml);
		
		VSFBlockEssentials::log("vsfSimpleBlockRules: " . $vsfSimpleBlockRules);
		
		VSFBlockEssentials::log("About to process user rules");
		$this->processRules($vsfSimpleBlockRules->getRules());
		VSFBlockEssentials::log("About to process url rules");
		$this->processRules($vsfSimpleBlockRules->getUrlRules());
		
		$this->printSummary();
	}
	
	/**
	 * Processes the list of rules, inserting them in to the database if they are not already present, otherwise ignoring them.
	 */
	private function processRules($rules)
	{
		foreach( $rules as $ruleInstance )
		{
			$this->totalRules++;
			$inDB = $this->checkForRuleInDB($ruleInstance);
			
			VSFBlockEssentials::log($inDB);
			
			if( $inDB )
			{
				$this->alreadyInDB++;
			}
			else
			{
				$error = $this->addRuleToDB($ruleInstance);
				VSFBlockEssentials::log($error);
				if( $error )
				{
					$this->errorsFromInsert++;
					$this->errorsThatOccured[count($this->errorsThatOccured)] = $ruleInstance;
				}
			}
		}
	}
	
	private function printSummary()
	{
		VSFBlockEssentials::log('errorsFromInsert: ' . $this->errorsFromInsert . ' alreadyInDB: ' . $this->alreadyInDB . ' inserted: ' . ($this->totalRules - $this->alreadyInDB - $this->errorsFromInsert));
		
		if( $errorsFromInsert != 0 )
		{
			if( $errorsFromInsert > 1 )
			{
				?><div class="error"><p><strong><?php printf(__('There were %d errors during import.', 'vsf-simple-block'), $this->errorsFromInsert); ?></strong></p><p><?php _e('Info:', 'vsf-simple-block'); echo '<br />'; $this->printOutRuleArray($this->errorsThatOccured); ?></p></div><?php
			}
			else
			{
				?><div class="error"><p><strong><?php _e('There was 1 error during import.', 'vsf-simple-block'); ?></strong></p><p><?php _e('Info:', 'vsf-simple-block'); echo '<br />'; $this->printOutRuleArray($this->errorsThatOccured); ?></p></div><?php
			}
		}
		else
		{
			if( ($this->totalRules - $this->alreadyInDB - $this->errorsFromInsert) > 1 )
			{
				?><div class="updated"><p><strong><?php printf(__('Successfully imported %d rules', 'vsf-simple-block'), ($this->totalRules - $this->alreadyInDB - $this->errorsFromInsert)); ?></strong></p></div><?php
			}
			else if ( ($this->totalRules - $this->alreadyInDB - $this->errorsFromInsert) == 0 )
			{
				?><div class="updated"><p><strong><?php _e('No rules to import.  Database already contains all rules in file.', 'vsf-simple-block'); ?></strong></p></div><?php
			}
			else if ( ($this->totalRules - $this->alreadyInDB - $this->errorsFromInsert) == 1 )
			{
				?><div class="updated"><p><strong><?php _e('Successfully imported 1 filter', 'vsf-simple-block'); ?></strong></p></div><?php
			}
		}
	}
	
	/** 
	 * Checks the current filter instance to see if it's already in the database. 
	 */
	private function checkForRuleInDB($rule)
	{
		global $wpdb;
		
		VSFBlockEssentials::log("select query: " . $rule->getSelectQuery());
		$preparedQuery = $wpdb->prepare($rule->getSelectQuery(), $rule->getValuesAsArray());
		
		if( VSF_BLOCK_DEBUG )
		{
			echo $preparedQuery . '<br />';
			echo "values that went into query: ";
			foreach ($rule->getValuesAsArray() as $valueInArray)
			{
				echo $valueInArray . "  ";
			}
			echo "<br />";
		}
		$ruleInTableResult = mysql_fetch_row(mysql_query($preparedQuery));
		
		VSFBlockEssentials::log('indb? : ' . $ruleInTableResult[0]);
		
		return ($ruleInTableResult[0] > 0);
	}
	
	/** 
	 * Adds the current rule instance to the database. 
	 */
	private function addRuleToDB($rule)
	{
		global $wpdb;
		
		VSFBlockEssentials::log("insert statement before: " . $rule->getInsertStatement());
		$preparedQuery = $wpdb->prepare($rule->getInsertStatement(), $rule->getValuesAsArray());
		VSFBlockEssentials::log($preparedQuery);
		$result = mysql_query($preparedQuery);
		VSFBlockEssentials::log('insert result: ' . $result);
		
		// if the result was anything but 1, then there was an error.
		return $result != 1;
	}
	
	/**
	 * Prints out each rule object
	 */
	private function printOutRuleArray($ruleArray)
	{
		foreach( $ruleArray as $t )
		{
			echo $t;
		}
	}
}

?>