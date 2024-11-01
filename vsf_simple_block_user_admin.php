<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

include_once('vsf_simple_block_essentials.php');

/** Simple rule object */
class VSFSimpleBlockUser
{
	private static $ipRegExp = "/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/";
	private static $REPLACE_COL = "[REPLACE_COL]";
	private static $REPLACE_VALUE = "[REPLACE_VALUE]";

	private $tablePrefix = "";
	private $maxTableRows;

	function __construct()
	{
		global $wpdb;
		$this->tablePrefix = $wpdb->prefix;
	}
	
	public function handleOptions($maxTableRows)
	{
		$this->maxTableRows = $maxTableRows;
		
		$resetAllPageNumbers = false;
		
		if ( !VSFBlockEssentials::isEmpty($_POST['ruleDelValue']) ) 
		{
			$resetAllPageNumbers = true;
			
			$ruleDelValue = $_POST['ruleDelValue'];
			//echo "ruleDelValue " . $ruleDelValue . "<br />";
			
			mysql_query("DELETE FROM " . $this->tablePrefix . BlockSetup::$TABLE_RULES . " WHERE id = $ruleDelValue");
			
			VSFBlockEssentials::buildUpdateDiv(__('Deleted Block Rule', 'vsf-simple-block'));
		}
			
		if ( isset($_POST['newBlockRuleSubmit']) )
		{
			$resetAllPageNumbers = true;
			
			$newRuleType = $_POST['newBlockRuleType'];
			$newRule1 = $_POST['newBlockRule1'];
			$newRule2 = $_POST['newBlockRule2'];
			$newRuleDescription = $_POST['newBlockRuleDescription'];
			
			$this->buildAndRunNewRuleInsertStatement(BlockSetup::$TABLE_RULES_RULE_BLOCK, $newRuleType, $newRule1, $newRule2, $newRuleDescription);
		}
		
		if ( isset($_POST['newFilterRuleSubmit']) )
		{
			$resetAllPageNumbers = true;
			
			$newRuleType = $_POST['newFilterRuleType'];
			$newRule1 = $_POST['newFilterRule1'];
			$newRule2 = $_POST['newFilterRule2'];
			$newRuleDescription = $_POST['newFilterRuleDescription'];
			
			$this->buildAndRunNewRuleInsertStatement(BlockSetup::$TABLE_RULES_RULE_FILTER, $newRuleType, $newRule1, $newRule2, $newRuleDescription);
		}
		
		if ( !VSFBlockEssentials::isEmpty($_POST['clearBlocks']) && $_POST['clearBlocks'] == "1" )
		{
			$resetAllPageNumbers = true;
			
			$result = mysql_query("TRUNCATE TABLE " . $this->tablePrefix . BlockSetup::$TABLE_BLOCK . "");
			if( $result == 1 ) VSFBlockEssentials::buildUpdateDiv(__('deleted All Block Records', 'vsf-simple-block'));
			else VSFBlockEssentials::buildErrorDiv(__('An error occured during truncating block table', 'vsf-simple-block'));
		}
		
		return $resetAllPageNumbers;
	}
	
	public function getJavascript()
	{
		?>
		
		function delRule(formObject, rule)
		{
			resetButtons();
			formObject.ruleDelValue.value = rule;
			formObject.submit();
		}

		function newBlockRuleComboChange(formObject)
		{
			resetButtons();
			//alert("changed " + formObject.newRuleType.value);
			formObject.newBlockRule2.disabled = !(formObject.newBlockRuleType.value == 1);
			formObject.newBlockRule2.style.backgroundColor = (formObject.newBlockRuleType.value == 1 ? "white" : "grey");
		}

		function newFilterRuleComboChange(formObject)
		{
			resetButtons();
			//alert("changed " + formObject.newFilterRuleType.value);
			formObject.newFilterRule2.disabled = !(formObject.newFilterRuleType.value == 1);
			formObject.newFilterRule2.style.backgroundColor = (formObject.newFilterRuleType.value == 1 ? "white" : "grey");
		}

		function clearBlockRecords(formObject)
		{
			resetButtons();
			var confirmBox = window.confirm("<?php _e("Are you sure you want to clear the block records?", 'vsf-simple-block'); ?>")
			if (confirmBox)
			{
				formObject.clearBlocks.value = 1;
				formObject.submit();
			}
		}
		<?php
	}
	
	public function getHiddenFields()
	{
		?><input type="hidden" name="ruleDelValue"><input type="hidden" name="clearBlocks"><?php
	}
	
	public function buildBlockFilterPanel()
	{
		?><div id="vsfBlockGeneralSettings" class="postbox">
			<h3 class="hndle"><?php _e('Filter Rules', 'vsf-simple-block'); ?></h3>
			<div class="inside">
				<p><?php _e('This table contains a list of filters that each hit on the website will be checked against.  If the hit is matched to a filter, then the hit will 
							be allowed through without any further checks.  If a match is not found it will be checked against the block user rules and if matched will be bounced.', 'vsf-simple-block'); ?></p>
				<p><?php _e('Values placed in this table are checked *before* the block user rules, so it\'s important to think about the ordering.  If there\'s a block rule like 
							browser summary containing "www" and a block filter containing "www.google.com" and the hit browser summary is "www.google.com" it will be filtered 
							and not blocked as the filter will be applied first.'); ?></p>
				<p></p>
				<table width="100%">
					<tr valign="top">
						<td><?php _e('Type', 'vsf-simple-block'); ?></td>
						<td><?php _e('Value', 'vsf-simple-block'); ?></td>
						<td><?php _e('IP to (Range)', 'vsf-simple-block'); ?></td>
						<td><?php _e('Description', 'vsf-simple-block'); ?></td>
						<td></td>
					</tr>
					<tr valign="top">
						<td><select id="newFilterRuleType" name="newFilterRuleType" onChange="newFilterRuleComboChange(this.form)"><option value="1" selected><?php _e('IP Address', 'vsf-simple-block'); ?></option><option value="2"><?php _e('Host Address', 'vsf-simple-block'); ?></option><option value="3"><?php _e('Browser Summary', 'vsf-simple-block'); ?></option></select></td>
						<td><input type="text" name="newFilterRule1"></td>
						<td><input id="newFilterRule2" type="text" name="newFilterRule2"></td>
						<td><input type="text" name="newFilterRuleDescription"></td>
						<td><input type="submit" name="newFilterRuleSubmit" value="<?php _e('Add New Filter User Rule', 'vsf-simple-block'); ?>" /></td>
					</tr>
				</table>
				<div class="tablenav">
					<div class='tablenav-pages'>
						<?php
							$fields = array("id", BlockSetup::$TABLE_RULES_TYPE, BlockSetup::$TABLE_RULES_HOST, "INET_NTOA(" . BlockSetup::$TABLE_RULES_IP1 . ")", "INET_NTOA(" . BlockSetup::$TABLE_RULES_IP2 . ")", BlockSetup::$TABLE_RULES_BROWSER_KEYWORD, description);
							$table = $this->tablePrefix . BlockSetup::$TABLE_RULES;
							$where = BlockSetup::$TABLE_RULES_RULE . " = " . BlockSetup::$TABLE_RULES_RULE_FILTER;
							$order = "id desc"; // Most recent first
							
							$selectQuery = VSFBlockEssentials::buildSelectQuery(false, $fields, $table, $where, $order);
							
							$countQuery = VSFBlockEssentials::buildSelectQuery(true, null, $table, $where, null);
							$items = mysql_fetch_row(mysql_query($countQuery)); // number of total rows in the database
							
							VSFBlockEssentials::log("number of items found " . $items[0] . "" . ($items[0] > 0));
							
							if( $items[0] > 0 )
							{
								$pagingForBotLookup = new pagination;
								$pagingForBotLookup->items($items[0]);
								$pagingForBotLookup->limit($this->maxTableRows); // Limit entries per page
								$pagingForBotLookup->target("?page=vsf_simple_block_setup.php");
								$pagingForBotLookup->parameterName("filterRule");
								$pagingForBotLookup->currentPage(($this->filterRuleCurrentPage > 0 ? $this->filterRuleCurrentPage : 1)); // Gets and validates the current page

								//Query for limit paging
								$lowerLimit = (($pagingForBotLookup->page - 1) * $pagingForBotLookup->limit);
								$selectQuery .= " LIMIT " . ($lowerLimit >= 0 ? $lowerLimit : 0) . ", " . $pagingForBotLookup->limit;
								VSFBlockEssentials::log("before show pagination");
								
								// Doesn't show anything if there is only 1 page... took me some time to work this out...
								echo $pagingForBotLookup->show();  // Echo out the list of paging.
								VSFBlockEssentials::log("after show pagination");
							}
						?>
					</div>
				</div>
			
				<table cellspacing="0" class="widefat fixed">
					<?php
						$columns = array(
							__('Remove', 'vsf-simple-block'), 
							__('Host', 'vsf-simple-block'),
							__('IP', 'vsf-simple-block'), 
							__('Browser Summary', 'vsf-simple-block'), 
							__('Description', 'vsf-simple-block')
						);
						VSFBlockEssentials::buildTableHeadAndFooter($columns);
					?>
					
					<tbody>
						<?php  
						
						$x = 0;
						VSFBlockEssentials::log($selectQuery);
						$botsLookupQueryResult = mysql_query($selectQuery);
						while ( $c = mysql_fetch_row($botsLookupQueryResult) )
						{
							?><tr class="<?php if( ($x % 2) == 0 ) echo "alternate"; ?>">
								<td><input type="button" value="<?php _e('Remove', 'vsf-simple-block'); ?>" onClick="delRule(this.form, '<?php echo $c[0]; ?>');"></td>
								<td><?php echo $c[2]; ?></td>
								<td><?php echo $c[3]; if( !VSFBlockEssentials::isEmpty($c[4]) ) echo " - " . $c[4]; ?></td>
								<td><?php echo $c[5]; ?></td>
								<td><?php echo $c[6]; ?></td>
							</tr><?php
							
							$x++;
						}
						
						?>
					</tbody>
				</table>
			</div>
		</div><?php
	}
	
	public function buildBlockRulesPanel()
	{
		?>
		<div id="vsfBlockBlockRules" class="postbox">
			<h3 class="hndle"><?php _e('Block Rules', 'vsf-simple-block'); ?></h3>
			<div class="inside">
				<p><?php _e('This table contains a list of user rules that each hit on the website will be checked against.  If the hit is matched to a user rule, then the hit will 
						be placed in the block table and the hit will be bounced to the address given in the general settings.', 'vsf-simple-block'); ?></p>
				<p><?php _e('User rules are added as either an IP based rule, Host based rule or Browser summary rule.  An IP rule will check hits against all IP\'s added to
						 this table.  IP\'s can be added as an individual IP or a range.  Host based rules look at the values in the table and basically does a contains match.  
						 So if your host is "www.007guard.com" and there is a host rule "www", the hit will be bounced as "www" is contained in the host.  Browser summary rules 
						 are very similar to Host rules.  Add in a keyword or pattern for example "mozilla" and all browser summaries that contain that string will be bounced.', 'vsf-simple-block'); ?></p>
				<p><?php _e('All rules will be overridden by values in the filter user rules table.  Also if you have VSF Simple Stats installed, values in the stats filter 
						 table will not be blocked.', 'vsf-simple-block'); ?></p>
				<p><i><?php _e('Be very careful when inserting a new value.  Think about your own IP, Host and Browser summary as if you add a value that matches yourself, 
						 the only way to get back in to your site is to manually remove entries from a few tables in the database!', 'vsf-simple-block'); ?></i></p>
				<p></p>
				<table width="100%">
					<tr valign="top"><td colspan="2"><b><?php _e('Your details', 'vsf-simple-block'); ?></b></td></tr>
					<tr valign="top"><td><b><?php _e('IP Address', 'vsf-simple-block'); ?></b></td><td><?php echo $_SERVER['REMOTE_ADDR']; ?></td></tr>
					<tr valign="top"><td><b><?php _e('Host Address', 'vsf-simple-block'); ?></b></td><td><?php echo gethostbyaddr($_SERVER['REMOTE_ADDR']); ?></td></tr>
					<tr valign="top"><td><b><?php _e('Browser Summary', 'vsf-simple-block'); ?></b></td><td><?php echo $_SERVER['HTTP_USER_AGENT']; ?></td></tr>
				</table>
				<p></p>
				<table width="100%">
					<tr valign="top">
						<td><?php _e('Type', 'vsf-simple-block'); ?></td>
						<td><?php _e('Value', 'vsf-simple-block'); ?></td>
						<td><?php _e('IP to (Range)', 'vsf-simple-block'); ?></td>
						<td><?php _e('Description', 'vsf-simple-block'); ?></td>
						<td></td>
					</tr>
					<tr valign="top">
						<td><select id="newBlockRuleType" name="newBlockRuleType" onChange="newBlockRuleComboChange(this.form)"><option value="1" selected><?php _e('IP Address', 'vsf-simple-block'); ?></option><option value="2"><?php _e('Host Address', 'vsf-simple-block'); ?></option><option value="3"><?php _e('Browser Summary', 'vsf-simple-block'); ?></option></select></td>
						<td><input type="text" name="newBlockRule1"></td>
						<td><input id="newBlockRule2" type="text" name="newBlockRule2"></td>
						<td><input type="text" name="newBlockRuleDescription"></td>
						<td><input type="submit" name="newBlockRuleSubmit" value="<?php _e('Add New Block User Rule', 'vsf-simple-block'); ?>" /></td>
					</tr>
				</table>
				<div class="tablenav">
					<div class='tablenav-pages'>
						<?php
							
							$fields = array("id", BlockSetup::$TABLE_RULES_TYPE, BlockSetup::$TABLE_RULES_HOST, "INET_NTOA(" . BlockSetup::$TABLE_RULES_IP1 . ")", "INET_NTOA(" . BlockSetup::$TABLE_RULES_IP2 . ")", BlockSetup::$TABLE_RULES_BROWSER_KEYWORD, description);
							$table = $this->tablePrefix . BlockSetup::$TABLE_RULES;
							$where = "(" . BlockSetup::$TABLE_RULES_RULE . " = " . BlockSetup::$TABLE_RULES_RULE_BLOCK . ") or (" . BlockSetup::$TABLE_RULES_RULE . " = " . BlockSetup::$TABLE_RULES_RULE_EXACT_BLOCK . ")";
							$order = "id desc"; // Most recent first
							
							$selectQuery = VSFBlockEssentials::buildSelectQuery(false, $fields, $table, $where, $order);
							
							$countQuery = VSFBlockEssentials::buildSelectQuery(true, null, $table, $where, null);
							$items = mysql_fetch_row(mysql_query($countQuery)); // number of total rows in the database
							VSFBlockEssentials::log("number of items found .." . $items[0] . ".." . ($items[0] > 0));
							
							if( $items[0] > 0 )
							{
								$pagingForBotLookup = new pagination;
								$pagingForBotLookup->items($items[0]);
								$pagingForBotLookup->limit($this->maxTableRows); // Limit entries per page
								$pagingForBotLookup->target("?page=vsf_simple_block_setup.php");
								$pagingForBotLookup->parameterName("blockRule");
								$pagingForBotLookup->currentPage(($this->blockRuleCurrentPage > 0 ? $this->blockRuleCurrentPage : 1)); // Gets and validates the current page

								//Query for limit paging
								$lowerLimit = (($pagingForBotLookup->page - 1) * $pagingForBotLookup->limit);
								$selectQuery .= " LIMIT " . ($lowerLimit >= 0 ? $lowerLimit : 0) . ", " . $pagingForBotLookup->limit;
								VSFBlockEssentials::log("before show pagination");
								
								// Doesn't show anything if there is only 1 page... took me some time to work this out...
								echo $pagingForBotLookup->show();  // Echo out the list of paging.
								VSFBlockEssentials::log("after show pagination");
							}
						?>
					</div>
				</div>
			
				<table cellspacing="0" class="widefat fixed">
					<?php
						$columns = array(
							__('Remove', 'vsf-simple-block'), 
							__('Host', 'vsf-simple-block'),
							__('IP', 'vsf-simple-block'), 
							__('Browser Summary', 'vsf-simple-block'), 
							__('Description', 'vsf-simple-block')
						);
						VSFBlockEssentials::buildTableHeadAndFooter($columns);
					?>
					
					<tbody>
						<?php  
						
						$x = 0;
						VSFBlockEssentials::log($selectQuery);
						$botsLookupQueryResult = mysql_query($selectQuery);
						while ( $c = mysql_fetch_row($botsLookupQueryResult) )
						{
							?><tr class="<?php if( ($x % 2) == 0 ) echo "alternate"; ?>">
								<td><input type="button" value="<?php _e('Remove', 'vsf-simple-block'); ?>" onClick="delRule(this.form, '<?php echo $c[0]; ?>');"></td>
								<td><?php echo $c[2]; ?></td>
								<td><?php echo $c[3]; if( !VSFBlockEssentials::isEmpty($c[4]) ) echo " - " . $c[4]; ?></td>
								<td><?php echo $c[5]; ?></td>
								<td><?php echo $c[6]; ?></td>
							</tr><?php
							
							$x++;
						}
						
						?>
					</tbody>
				</table>
				
			</div>
		</div>
		
		<?php
	}
	
	public function buildBlockedEntriesPanel()
	{
		?>
		
		<div id="vsfBlockEntries" class="postbox">
			<h3 class="hndle"><?php _e('Blocks', 'vsf-simple-block'); ?></h3>
			<div class="inside">
				<p><?php _e('This table contains a list of hits that have been blocked.  After 500 records, the last record will be deleted when a new record is added.', 'vsf-simple-block'); ?></p>
				<p><input type="button" name="clearAllBlockRecords" value="<?php _e('Clear All Block Records', 'vsf-simple-block'); ?>" onClick="clearBlockRecords(this.form)"></p>
				
				<div class="tablenav">
					<div class='tablenav-pages'>
						<?php
							$fields = array(BlockSetup::$TABLE_BLOCK_DATE_BLOCKED, "INET_NTOA(" . BlockSetup::$TABLE_BLOCK_IP . ")", BlockSetup::$TABLE_BLOCK_HOST, BlockSetup::$TABLE_BLOCK_BROWSER_SUMMARY, BlockSetup::$TABLE_BLOCK_DESCRIPTION);
							$table = $this->tablePrefix . BlockSetup::$TABLE_BLOCK;
							$order = BlockSetup::$TABLE_BLOCK_DATE_BLOCKED . " desc"; // most recent first
							
							$selectQuery = VSFBlockEssentials::buildSelectQuery(false, $fields, $table, null, $order);
							
							$countQuery = VSFBlockEssentials::buildSelectQuery(true, null, $table, $where, null);
							$items = mysql_fetch_row(mysql_query($countQuery)); // number of total rows in the database
							VSFBlockEssentials::log("number of items found " . $items[0] . "" . ($items[0] > 0));
							
							if( $items[0] > 0 )
							{
								$pagingForBlocks = new pagination;
								$pagingForBlocks->items($items[0]);
								$pagingForBlocks->limit($this->maxTableRows); // Limit entries per page
								$pagingForBlocks->target("?page=vsf_simple_block_setup.php");
								$pagingForBlocks->parameterName("block");
								$pagingForBlocks->currentPage(($this->blockCurrentPage > 0 ? $this->blockCurrentPage : 1)); // Gets and validates the current page

								//Query for limit paging
								$lowerLimit = (($pagingForBlocks->page - 1) * $pagingForBlocks->limit);
								$selectQuery .= " LIMIT " . ($lowerLimit >= 0 ? $lowerLimit : 0) . ", " . $pagingForBlocks->limit;
								VSFBlockEssentials::log("before show pagination");
								
								// Doesn't show anything if there is only 1 page... took me some time to work this out...
								echo $pagingForBlocks->show();  // Echo out the list of paging.
								VSFBlockEssentials::log("after show pagination");
							}
						?>
					</div>
				</div>
			
				<table cellspacing="0" class="widefat tag fixed">
					<?php
						$columns = array(
							__('Date Blocked', 'vsf-simple-block'), 
							__('IP Address', 'vsf-simple-block'),
							__('Host', 'vsf-simple-block'), 
							__('Browser Summary', 'vsf-simple-block'), 
							__('Description', 'vsf-simple-block')
						);
						VSFBlockEssentials::buildTableHeadAndFooter($columns);
					?>
					
					<tbody class="list:tag" id="the-list">
						<?php  
						
						$dateFormatForBlock = __('H:i d-m-Y', 'vsf-simple-block');
						
						$x = 0;
						VSFBlockEssentials::log($selectQuery);
						$blockQueryResult = mysql_query($selectQuery);
						while ( $c = mysql_fetch_row($blockQueryResult) )
						{
							?>
								<tr id="<?php echo $c[0]; ?>" class="<?php if( ($x % 2) == 0 ) {echo "alternate";} else {echo "";} ?>">
									<td class="name column-name"><?php echo date($dateFormatForBlock, $c[0]); ?></td>
									<td class="name column-name"><?php echo $c[1]; ?></td>
									<td class="name column-name"><?php echo $c[2]; ?></td>
									<td class="name column-name"><?php echo $c[3]; ?></td>
									<td class="name column-name"><?php echo $c[4]; ?></td>
								</tr>
							<?php
							
							$x++;
						}
						
						?>
					</tbody>
				</table>
				
			</div>
		</div>
		
		<?php
	}

	private function buildAndRunNewRuleInsertStatement($newRuleRule, $newRuleType, $newRule1, $newRule2, $newRuleDescription)
	{
		$newRuleDescription = VSFBlockEssentials::cleanUpString($newRuleDescription, BlockSetup::$TABLE_RULES_DESCRIPTION_MAX_LENGTH);
		
		if( !VSFBlockEssentials::isEmpty($newRuleType) && !VSFBlockEssentials::isEmpty($newRule1) )
		{
			$query = "INSERT INTO " . $this->tablePrefix . BlockSetup::$TABLE_RULES . " (" . BlockSetup::$TABLE_RULES_RULE . ", " . BlockSetup::$TABLE_RULES_TYPE . ", " . BlockSetup::$TABLE_RULES_DESCRIPTION . ", " . self::$REPLACE_COL . ") values ($newRuleRule, $newRuleType, " . (VSFBlockEssentials::isEmpty($newRuleDescription) ? 'null' : '\'' . $newRuleDescription . '\'') . ", " . self::$REPLACE_VALUE . ")";
			
			if( $newRuleType == 1 ) // IP
			{
				$newRule1 = trim($newRule1);
				$newRule2 = trim($newRule2);
				
				if ( !preg_match(self::$ipRegExp, $newRule1) ) VSFBlockEssentials::buildErrorDiv(__('Invalid IP!', 'vsf-simple-block'));
				else if( !VSFBlockEssentials::isEmpty($newRule2) && !preg_match(self::$ipRegExp, $newRule2) ) VSFBlockEssentials::buildErrorDiv(__('Invalid IP!', 'vsf-simple-block'));
				else
				{
					$query = str_replace(self::$REPLACE_COL, BlockSetup::$TABLE_RULES_IP1 . ", " . BlockSetup::$TABLE_RULES_IP2, $query);
					$replaceValue = "INET_ATON('$newRule1'), " . (!VSFBlockEssentials::isEmpty($newRule2) ? "INET_ATON('$newRule2')" : "null");
					$query = str_replace(self::$REPLACE_VALUE, $replaceValue, $query);
					
					VSFBlockEssentials::log("query is: " . $query);
					$result = mysql_query($query);
				
					if( $result == 1 ) VSFBlockEssentials::buildUpdateDiv(__('Added New Rule', 'vsf-simple-block'));
					else VSFBlockEssentials::buildErrorDiv(__('Error occured during browser summary rule insert: ', 'vsf-simple-block') . mysql_error());
				}
			}
			else if( ($newRuleType == 2) || ($newRuleType == 3) ) // Host or browser summary
			{
				$columnValue = "";
				if( $newRuleType == 2 ) // host
				{
					$newRule1 = VSFBlockEssentials::cleanUpString($newRule1, BlockSetup::$TABLE_RULES_HOST_MAX_LENGTH);
					$columnValue = BlockSetup::$TABLE_RULES_HOST;
				}
				else // browser
				{
					$newRule1 = VSFBlockEssentials::cleanUpString($newRule1, BlockSetup::$TABLE_RULES_BROWSER_KEYWORD_MAX_LENGTH);
					$columnValue = BlockSetup::$TABLE_RULES_BROWSER_KEYWORD;
				}
				
				$query = str_replace(self::$REPLACE_COL, $columnValue, $query);
				$query = str_replace(self::$REPLACE_VALUE, '\'' . $newRule1 . '\'', $query);
				
				VSFBlockEssentials::log("query is: " . $query);
				$result = mysql_query($query);
				
				if( $result == 1 ) VSFBlockEssentials::buildUpdateDiv(__('Added New Rule', 'vsf-simple-block'));
				else VSFBlockEssentials::buildErrorDiv(__('Error occured during browser summary rule insert: ', 'vsf-simple-block') . mysql_error());
			}
		}
	}
}

?>