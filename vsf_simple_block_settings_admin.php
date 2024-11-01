<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

include('vsf_simple_block_essentials.php');

class VSFBlockSetupAdminSettings
{
	private static $MAX_ITEMS = 100000;
	
	public function handleOptions()
	{
		$resetAllPageNumbers = false;
		
		if ( isset($_POST['updateGeneralSettingsSubmit']) )
		{
			$resetAllPageNumbers = true;
			
			$updatedSettings = false;
				
			if ( VSFBlockEssentials::isEmpty($_POST['bounceAddress']) )
			{
				VSFBlockEssentials::buildErrorDiv(__('Failed to update bounce address, cannot use blank address', 'vsf-simple-block'));
			}
			else
			{
				$bounceAddress = $_POST['bounceAddress'];
				// clean up the bounce address
				$bounceAddress = VSFBlockEssentials::cleanUpString($bounceAddress, 200);
				// Bounce address
				update_option("vsf_block_bounce_address", $bounceAddress);
				$updatedSettings = true;
			}
			
			if ( VSFBlockEssentials::isEmpty($_POST['tableItemsQuantity']) )
			{
				update_option("vsf_block_bounce_address", self::$MAX_ITEMS);
				$updatedSettings = true;
			}
			else
			{
				$tableItemsQuantity = $_POST['tableItemsQuantity'];
				if( $tableItemsQuantity >= 10 && $tableItemsQuantity <= self::$MAX_ITEMS )
				{
					update_option("vsf_block_table_items_quantity", $tableItemsQuantity);
					$updatedSettings = true;
				}
				else
				{
					VSFBlockEssentials::buildErrorDiv(__('Failed to update table row quantity as value was outside range 10 - 100000', 'vsf-simple-block'));
				}
			}
			
			if( $updatedSettings )
			{
				VSFBlockEssentials::buildUpdateDiv(__('Updated settings', 'vsf-simple-block'));
			}
		}
		
		
		return $resetAllPageNumbers;
	}
		
	public function buildGeneralSettingsPanel()
	{
		?>
		<div id="vsfBlockGeneralSettings" class="postbox">
			<h3 class="hndle"><?php _e('General Settings', 'vsf-simple-block'); ?></h3>
			<div class="inside">
				<?php _e('Bounce address'); ?><br />
				<?php _e('E.g. http://www.google.com'); ?><br />
				<input type="text" name="bounceAddress" value="<?php form_option('vsf_block_bounce_address'); ?>" size="35"><br />
				<p>&#160;</p>
				<?php _e('Show', 'vsf-simple-block'); ?> <input type="text" name="tableItemsQuantity" value="<?php form_option('vsf_block_table_items_quantity'); ?>" size="3"> <?php _e('items in table', 'vsf-simple-block'); ?>
				<p>&#160;</p>
				<input type="submit" name="updateGeneralSettingsSubmit" value="<?php _e('Update General Settings', 'vsf-simple-block'); ?>" />
			</div>
		</div>				
		<?php
	}
	
	private function autoDetect()
	{
		?><?php _e('Auto block Enabled:', 'vsf-simple-block'); ?> <input type="checkbox" disabled="disabled" /><br />
				<br /><?php _e('Max number of hits in', 'vsf-simple-block'); ?><br />
				<?php _e('1 minute', 'vsf-simple-block'); ?> <input type="text" name="oneMinute" value="<?php form_option('vsf_block_max_hits_in_one_minute'); ?>" size="3" disabled="disabled"><br />
				<?php _e('2 minutes', 'vsf-simple-block'); ?> <input type="text" name="twoMinute" value="<?php form_option('vsf_block_max_hits_in_two_minute'); ?>" size="3" disabled="disabled"><br />
				<?php _e('5 minutes', 'vsf-simple-block'); ?> <input type="text" name="fiveMinute" value="<?php form_option('vsf_block_max_hits_in_five_minute'); ?>" size="3" disabled="disabled">
				<p>&#160;</p><?php 
	}
}
?>