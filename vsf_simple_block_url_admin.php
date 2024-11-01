<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

include_once('vsf_simple_block_essentials.php');
include_once('vsf_simple_block_setup.php');

class VSFBlockUrlAdmin
{
	private static $URL_BOX_SIZE = 50;
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
		
		if ( !VSFBlockEssentials::isEmpty($_POST['urlRuleDelValue']) ) 
		{
			$resetAllPageNumbers = true;
			
			$ruleDelValue = $_POST['urlRuleDelValue'];
			//echo "ruleDelValue " . $ruleDelValue . "<br />";
			
			mysql_query("DELETE FROM " . $this->tablePrefix . BlockSetup::$TABLE_URLS . " WHERE id = $ruleDelValue");
			
			VSFBlockEssentials::buildUpdateDiv(__('Deleted Block Rule', 'vsf-simple-block'));
		}

		VSFBlockEssentials::log("new block url: " . $_POST['newBlockUrlRuleSubmit']);
		if ( isset($_POST['newBlockUrlRuleSubmit']) )
		{
			$resetAllPageNumbers = true;
			
			$newRuleType = $_POST['newBlockUrlType'];
			$newUrl = $_POST['newBlockUrlUrl'];
			$newRuleDescription = $_POST['newBlockUrlDescription'];
			
			$this->buildAndRunNewRuleInsertStatement(BlockSetup::$TABLE_URL_RULE_BLOCK, $newRuleType, $newUrl, $newRuleDescription);
		}

		VSFBlockEssentials::log("new filter url: " . $_POST['newFilterUrlRuleSubmit']);
		if ( isset($_POST['newFilterUrlRuleSubmit']) )
		{
			$resetAllPageNumbers = true;
			
			$newRuleType = $_POST['newFilterUrlType'];
			$newUrl = $_POST['newFilterUrlUrl'];
			$newRuleDescription = $_POST['newFilterUrlDescription'];
			
			$this->buildAndRunNewRuleInsertStatement(BlockSetup::$TABLE_URL_RULE_FILTER, $newRuleType, $newUrl, $newRuleDescription);
		}
		
		return $resetAllPageNumbers;
	}
	
	public function getJavascript()
	{
		?>
		function delUrlRule(formObject, rule)
		{
			resetButtons();
			formObject.urlRuleDelValue.value = rule;
			formObject.submit();
		}
		<?php
	}
	
	public function getHiddenFields() {	?><input type="hidden" name="urlRuleDelValue"><?php }
	
	public function buildBlockUrlFilterPanel()
	{
		?><div id="vsfBlockUrlGeneralSettings" class="postbox">
			<h3 class="hndle"><?php _e('Filter URL Rules', 'vsf-simple-block'); ?></h3>
			<div class="inside">
				<p><?php _e('This table contains a list of filters that each hit on the website will be checked against.  Note that this is done AFTER the user rules.  
							User rules are checked first, then the URL block rules.  If the hit is matched to a filter, then the hit will allowed through without any further checks.  
							If the hit is not found it will be checked against the URL block rules and if matched will be bounced.', 'vsf-simple-block'); ?></p>
				<p><?php _e('Values placed in this table are checked *before* the URL block rules, but *after* the user rules, so it\'s important to think about the ordering.', 'vsf-simple-block'); ?></p>
				<p></p>
				<table width="100%">
					<tr valign="top">
						<td><?php _e('Type', 'vsf-simple-block'); ?></td>
						<td><?php _e('URL', 'vsf-simple-block'); ?></td>
						<td><?php _e('Description', 'vsf-simple-block'); ?></td>
						<td></td>
					</tr>
					<tr valign="top">
						<td>
							<select id="newFilterUrlType" name="newFilterUrlType">
								<option value="<?php echo BlockSetup::$TABLE_URL_TYPE_START; ?>" selected><?php _e('Start', 'vsf-simple-block'); ?></option>
								<option value="<?php echo BlockSetup::$TABLE_URL_TYPE_END; ?>"><?php _e('End', 'vsf-simple-block'); ?></option>
								<option value="<?php echo BlockSetup::$TABLE_URL_TYPE_ANYWHERE; ?>"><?php _e('Anywhere', 'vsf-simple-block'); ?></option>
							</select>
						</td>
						<td><input type="text" name="newFilterUrlUrl" size="<?php echo self::$URL_BOX_SIZE; ?>"></td>
						<td><input type="text" name="newFilterUrlDescription"></td>
						<td><input type="submit" name="newFilterUrlRuleSubmit" value="<?php _e('Add New Filter URL Rule', 'vsf-simple-block'); ?>" /></td>
					</tr>
				</table>
				<div class="tablenav">
					<div class='tablenav-pages'>
						<?php
							$fields = $this->getColumns();
							$table = $this->tablePrefix . BlockSetup::$TABLE_URLS;
							$where = BlockSetup::$TABLE_URL_RULE . " = " . BlockSetup::$TABLE_URL_RULE_FILTER;
							$order = BlockSetup::$TABLE_URL_ID . " desc"; // Most recent first
							
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
								$pagingForBotLookup->parameterName("filterUrlRule");
								$pagingForBotLookup->currentPage(($this->filterUrlRuleCurrentPage > 0 ? $this->filterUrlRuleCurrentPage : 1)); // Gets and validates the current page

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
			
				<?php $this->createTableWithQuery($selectQuery); ?>
				
			</div>
		</div><?php
	}
	
	public function buildBlockUrlRulesPanel()
	{
		?>
		<div id="vsfBlockBlockRules" class="postbox">
			<h3 class="hndle"><?php _e('Block Rules', 'vsf-simple-block'); ?></h3>
			<div class="inside">
				<p><?php _e('This table contains a list of URL rules that each hit on the website will be checked against.  If the hit is matched to a rule, then the hit will be placed in the block table and the hit will be bounced to the address given in the general settings.', 'vsf-simple-block'); ?></p>
				<p><?php _e('When checking the entered URL rules below, the URL will either be matched to the start, end or anywhere in the URL.', 'vsf-simple-block'); ?></p>
				<p><?php _e('All rules will be overridden by values in the User filter, User block rules and URL filter table.  Also if you have VSF Simple Stats installed, values in the stats filter table 
							will not be blocked.', 'vsf-simple-block'); ?></p>
				<p><i><?php _e('Be very careful when inserting a new URL value.  Think about the URL\'s you visit on this site! If you add a value that matches somewhere you visit on your
								site, you will be blocked and the only way to get back in to your site is to manually remove entries from a few tables in the database!', 'vsf-simple-block'); ?></i></p>
				<p></p>
				<table width="100%">
					<tr valign="top">
						<td><?php _e('Type', 'vsf-simple-block'); ?></td>
						<td><?php _e('URL', 'vsf-simple-block'); ?></td>
						<td><?php _e('Description', 'vsf-simple-block'); ?></td>
						<td></td>
					</tr>
					<tr valign="top">
						<td>
							<select id="newBlockUrlType" name="newBlockUrlType">
								<option value="<?php echo BlockSetup::$TABLE_URL_TYPE_START; ?>" selected><?php _e('Start', 'vsf-simple-block'); ?></option>
								<option value="<?php echo BlockSetup::$TABLE_URL_TYPE_END; ?>"><?php _e('End', 'vsf-simple-block'); ?></option>
								<option value="<?php echo BlockSetup::$TABLE_URL_TYPE_ANYWHERE; ?>"><?php _e('Anywhere', 'vsf-simple-block'); ?></option>
							</select>
						</td>
						<td><input type="text" name="newBlockUrlUrl" size="<?php echo self::$URL_BOX_SIZE; ?>"></td>
						<td><input type="text" name="newBlockUrlDescription"></td>
						<td><input type="submit" name="newBlockUrlRuleSubmit" value="<?php _e('Add New Block URL Rule', 'vsf-simple-block'); ?>" /></td>
					</tr>
				</table>
				<div class="tablenav">
					<div class='tablenav-pages'>
						<?php
							
							$fields = $this->getColumns();
							$table = $this->tablePrefix . BlockSetup::$TABLE_URLS;
							$where = BlockSetup::$TABLE_URL_RULE . " = " . BlockSetup::$TABLE_URL_RULE_BLOCK;
							$order = BlockSetup::$TABLE_URL_ID . " desc"; // Most recent first
							
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
								$pagingForBotLookup->parameterName("blockUrlRule");
								$pagingForBotLookup->currentPage(($this->blockUrlRuleCurrentPage > 0 ? $this->blockUrlRuleCurrentPage : 1)); // Gets and validates the current page

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
			
				<?php $this->createTableWithQuery($selectQuery); ?>
				
			</div>
		</div>
		
		<?php
	}

	private function getColumns()
	{
		$fields = array(
			BlockSetup::$TABLE_URL_ID, 
			BlockSetup::$TABLE_URL_TYPE, 
			BlockSetup::$TABLE_URL_RULE, 
			BlockSetup::$TABLE_URL_URL, 
			BlockSetup::$TABLE_URL_DESCRIPTION
		);

		return $fields;
	}
	
	private function createTableWithQuery($selectQuery)
	{
		?>
		<table cellspacing="0" class="widefat">
			<?php
				$columns = array(
					__('Remove', 'vsf-simple-block'), 
					__('Type', 'vsf-simple-block'),
					__('URL', 'vsf-simple-block'),
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
						<td><input type="button" value="<?php _e('Remove', 'vsf-simple-block'); ?>" onClick="delUrlRule(this.form, '<?php echo $c[0]; ?>');"></td>
						<?php $urlType = ($c[1] == BlockSetup::$TABLE_URL_TYPE_START ? 
														__("Start", 'vsf-simple-block') : 
																($c[1] == BlockSetup::$TABLE_URL_TYPE_END ? __("End", 'vsf-simple-block') : __("Anywhere", 'vsf-simple-block'))); ?>
						<td><?php echo $urlType; ?></td>
						<td><?php echo $c[3]; ?></td>
						<td><?php echo $c[4]; ?></td>
					</tr><?php
					$x++;
				}
				
				?>
			</tbody>
		</table>
		<?php
	}
	
	private function buildAndRunNewRuleInsertStatement($newRuleRule, $newRuleType, $newRuleURL, $newRuleDescription)
	{
		$newRuleDescription = VSFBlockEssentials::cleanUpString($newRuleDescription, BlockSetup::$TABLE_RULES_DESCRIPTION_MAX_LENGTH);
		VSFBlockEssentials::log("newRuleURL: " . $newRuleURL);
		if( !VSFBlockEssentials::isEmpty($newRuleURL) )
		{
			$newRuleURL = VSFBlockEssentials::cleanUpString($newRuleURL, BlockSetup::$TABLE_URL_DESCRIPTION_MAX_LENGTH);
			
			$query = "INSERT INTO " . $this->tablePrefix . BlockSetup::$TABLE_URLS . 
					" (" . BlockSetup::$TABLE_URL_RULE . ", " . BlockSetup::$TABLE_URL_TYPE . ", " . BlockSetup::$TABLE_URL_DESCRIPTION . ", " . BlockSetup::$TABLE_URL_URL . ") " .
					"values ($newRuleRule, $newRuleType, " . 
							(VSFBlockEssentials::isEmpty($newRuleDescription) ? "null" : "'$newRuleDescription'") . 
							", " . (VSFBlockEssentials::isEmpty($newRuleURL) ? "null" : "'$newRuleURL'") . ")";

			VSFBlockEssentials::log("query is: " . $query);
			$result = mysql_query($query);
		
			if( $result == 1 ) VSFBlockEssentials::buildUpdateDiv(__('Added New URL Rule', 'vsf-simple-block'));
			else VSFBlockEssentials::buildErrorDiv(__('Error occured during URL rule insert: ', 'vsf-simple-block') . mysql_error());
		}
	}
}

?>