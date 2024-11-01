<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

	include_once('pagination.php');
	include_once('vsf_simple_block_essentials.php');
	include_once('vsf_simple_block_settings_admin.php');
	include_once('vsf_simple_block_spidertrap_admin.php');
	include_once('vsf_simple_block_user_admin.php');
	include_once('vsf_simple_block_url_admin.php');
	
	/* Sets up the tables and admin page */
	class BlockSetupAdmin
	{
		private static $VIEW_BLOCKS = "blocks";
		
		private static $VIEW_FILTER_RULES = "filterRules";
		private static $VIEW_FILTER_RULES_USER = "filterRules_user";
		private static $VIEW_FILTER_RULES_URL = "filterRuless_url";
		
		private static $VIEW_BLOCK_RULES = "blockRules";
		private static $VIEW_BLOCK_RULES_USER = "blockRules_user";
		private static $VIEW_BLOCK_RULES_URL = "blockRules_url";
		
		private static $VIEW_SPIDER_TRAP = "spiderTrap";
		
		private $filterRuleCurrentPage;
		private $filterUrlRuleCurrentPage;
		private $blockRuleCurrentPage;
		private $blockUrlRuleCurrentPage;
		private $blockCurrentPage;
		private $spiderTrapCurrentPage;
		
		private $selectedView;
		
		private $maxTableRows = 0;
		
		private $adminSettings = null;
		private $adminSpidertrap = null;
		private $adminUser = null;
		private $adminUrl = null;
		
		function __construct()
		{
			$this->adminSettings = new VSFBlockSetupAdminSettings();
			$this->adminSpidertrap = new VSFSimpleBlockSpidertrapAdmin();
			$this->adminUser = new VSFSimpleBlockUser();
			$this->adminUrl = new VSFBlockUrlAdmin();
		}
		
		public function adminOptionsPanel()
		{
			$resetAllPageNumbers = false;
			$this->blockRuleCurrentPage = $_GET[blockRule];
			$this->filterRuleCurrentPage = $_GET[filterRule];
			$this->blockCurrentPage = $_GET[block];
			
			if ( !VSFBlockEssentials::isEmpty($_POST['vsfBlockSelectedView']) )
			{
				$resetAllPageNumbers = true;
				
				$this->selectedView = $_POST['vsfBlockSelectedView'];
			}
			
			if ( VSFBlockEssentials::isEmpty($this->selectedView) )
			{
				$this->selectedView = self::$VIEW_BLOCKS;
			}
			
			$resetAllPageNumbersTemp = $this->adminSettings->handleOptions();
			if( $resetAllPageNumbersTemp )
			{
				$resetAllPageNumbers = true;
			}
		
			$this->maxTableRows = get_option("vsf_block_table_items_quantity");
			$this->maxTableRows = $this->maxTableRows == null ? self::$MAX_ITEMS : $this->maxTableRows;
			
			$resetAllPageNumbersTemp = $this->adminUser->handleOptions($this->maxTableRows);
			if( $resetAllPageNumbersTemp )
			{
				$resetAllPageNumbers = true;
			}
			
		
			$resetAllPageNumbersTemp = $this->adminUrl->handleOptions($this->maxTableRows);
			if( $resetAllPageNumbersTemp )
			{
				$resetAllPageNumbers = true;
			}
			
			if( isset($_POST['vsfBlockImportSettings']) )
			{
				$resetAllPageNumbers = true;
				
				$importFile = $_FILES["vsfBlockImportSettingsFile"];
				
				if ($importFile["error"] > 0)
				{
					VSFBlockEssentials::buildErrorDiv(__('Error: There was a problem uploading the filter ip file.  Please try again.', 'vsf-simple-block'));
				}
				else
				{
					if( $importFile["type"] != 'text/xml' )
					{
						VSFBlockEssentials::buildErrorDiv(__('File type is not correct!', 'vsf-simple-block'));
					}
					else if ( ($importFile["size"] / 1024) > 100 )
					{
						VSFBlockEssentials::buildErrorDiv(__('File size is too large.  File needs to be less than 100kb!', 'vsf-simple-block'));
					}
					else
					{
						$filterIpfileContent = file_get_contents($importFile["tmp_name"]);
						
						include('vsf_simple_block_import.php');
						
						$xmlReaderForSimpleBlock = new VSFSimpleBlockImport();
						$xmlReaderForSimpleBlock->importFileValues($filterIpfileContent);
					}
				}
			}
			
			if( $resetAllPageNumbers )
			{
				$this->filterRuleCurrentPage = NULL;
				$this->filterUrlRuleCurrentPage = NULL;
				$this->blockRuleCurrentPage = NULL;
				$this->blockUrlRuleCurrentPage = NULL;
				$this->blockCurrentPage = NULL;
				$this->spiderTrapCurrentPage = NULL;
			}
			
			?>
			<style type="text/css">
				.inside { margin:12px!important; }
			</style>
			<script>
				<?php $this->adminSpidertrap->getJavascript(); ?>

				function exportSettings()
				{
					document.getElementById('vsfBlockExportSettings').value = 'export';
					document.getElementById('vsfBlockForm').submit();
				}
				
				function resetButtons()
				{
					resetSpidertrap();
					document.getElementById('vsfBlockExportSettings').value = "";
				}
				
				<?php $this->adminUser->getJavascript(); ?>
				<?php $this->adminUrl->getJavascript(); ?>
				
				function vsfBlockChangeSelectedView(view)
				{
					resetButtons();
					document.getElementById('vsfBlockSelectedView').value = view;
					document.getElementById('vsfBlockForm').submit();
				}
			</script>
			<div class="wrap">
				<h2><?php _e('Options', 'vsf-simple-block'); ?></h2>
				<form id="vsfBlockForm" method="post" enctype="multipart/form-data">
					<?php $this->adminSpidertrap->getHiddenFields() ?>
					<?php $this->adminUser->getHiddenFields(); ?>
					<?php $this->adminUrl->getHiddenFields(); ?>
					<input id="vsfBlockSelectedView" type="hidden" name="vsfBlockSelectedView" value="<?php echo $this->selectedView; ?>">
					<input id="vsfBlockExportSettings" type="hidden" name="vsfBlockExportSettings" />
						
					<div id="poststuff" class="metabox-holder has-right-sidebar">
						<div id="side-info-column" class="inner-sidebar">
							<?php $this->buildSideBarContent(); ?>
						</div>
						
						<div id="post-body" class="has-sidebar">
							<div class="has-sidebar-content" id="post-body-content">
								<?php $this->buildMainContent(); ?>
							</div>
						</div>
					</div>
					
				</form>
			</div>
		<?php
		}
		
		private function buildSideBarContent()
		{
			$this->buildAboutPanel();
			$this->adminSettings->buildGeneralSettingsPanel();
			$this->buildImportExportPanel();
		}
		
		private function buildMainContent()
		{
			?><div id="vsfBlockView" class="postbox">
				<h3 class="hndle"><?php _e('View', 'vsf-simple-block'); ?></h3>
				<div class="inside">
					<ul class="subsubsub">
						<li><a <?php if( $this->selectedView == self::$VIEW_BLOCKS ) echo 'class="current" '; ?>onClick="vsfBlockChangeSelectedView('<?php echo self::$VIEW_BLOCKS ?>')"><?php _e('Block Records', 'vsf-simple-block'); ?></a> |</li>
						<li><a <?php if( $this->selectedView == self::$VIEW_FILTER_RULES || $this->selectedView == self::$VIEW_FILTER_RULES_USER || $this->selectedView == self::$VIEW_FILTER_RULES_URL ) echo 'class="current" '; ?>onClick="vsfBlockChangeSelectedView('<?php echo self::$VIEW_FILTER_RULES ?>')"><?php _e('Filter Rules', 'vsf-simple-block'); ?></a> |</li>
						<li><a <?php if( $this->selectedView == self::$VIEW_BLOCK_RULES || $this->selectedView == self::$VIEW_BLOCK_RULES_USER || $this->selectedView == self::$VIEW_BLOCK_RULES_URL ) echo 'class="current" '; ?>onClick="vsfBlockChangeSelectedView('<?php echo self::$VIEW_BLOCK_RULES ?>')"><?php _e('Block Rules', 'vsf-simple-block'); ?></a> |</li>
						<li><a <?php if( $this->selectedView == self::$VIEW_SPIDER_TRAP ) echo 'class="current" '; ?>onClick="vsfBlockChangeSelectedView('<?php echo self::$VIEW_SPIDER_TRAP ?>')"><?php _e('Spider Trap', 'vsf-simple-block'); ?></a></li>
					</ul>
					<br />
					<br />
					<br />
					<br />
					<ul class="subsubsub">
						<?php if ( $this->selectedView == self::$VIEW_FILTER_RULES || $this->selectedView == self::$VIEW_FILTER_RULES_USER || $this->selectedView == self::$VIEW_FILTER_RULES_URL ) { ?>
						<li><a <?php if( $this->selectedView == self::$VIEW_FILTER_RULES || $this->selectedView == self::$VIEW_FILTER_RULES_USER ) echo 'class="current" '; ?>onClick="vsfBlockChangeSelectedView('<?php echo self::$VIEW_FILTER_RULES_USER ?>')"><?php _e('Filter User Rules', 'vsf-simple-block'); ?></a> |</li>
						<li><a <?php if( $this->selectedView == self::$VIEW_FILTER_RULES_URL ) echo 'class="current" '; ?>onClick="vsfBlockChangeSelectedView('<?php echo self::$VIEW_FILTER_RULES_URL ?>')"><?php _e('Filter URL Rules', 'vsf-simple-block'); ?></a></li>
						
						<?php } else if ( $this->selectedView == self::$VIEW_BLOCK_RULES || $this->selectedView == self::$VIEW_BLOCK_RULES_USER || $this->selectedView == self::$VIEW_BLOCK_RULES_URL ) { ?>
						<li><a <?php if( $this->selectedView == self::$VIEW_BLOCK_RULES || $this->selectedView == self::$VIEW_BLOCK_RULES_USER ) echo 'class="current" '; ?>onClick="vsfBlockChangeSelectedView('<?php echo self::$VIEW_BLOCK_RULES_USER ?>')"><?php _e('Block User Rules', 'vsf-simple-block'); ?></a> |</li>
						<li><a <?php if( $this->selectedView == self::$VIEW_BLOCK_RULES_URL ) echo 'class="current" '; ?>onClick="vsfBlockChangeSelectedView('<?php echo self::$VIEW_BLOCK_RULES_URL ?>')"><?php _e('Block URL Rules', 'vsf-simple-block'); ?></a></li>
						
						<?php } ?>
					</ul>
				</div>
				<p>&#160;</p><p>&#160;</p>
			</div><?php
			
			if ( $this->selectedView == self::$VIEW_BLOCKS ) $this->adminUser->buildBlockedEntriesPanel();
			
			else if ( $this->selectedView == self::$VIEW_FILTER_RULES || $this->selectedView == self::$VIEW_FILTER_RULES_USER ) $this->adminUser->buildBlockFilterPanel();
			else if ( $this->selectedView == self::$VIEW_FILTER_RULES_URL ) $this->adminUrl->buildBlockUrlFilterPanel();
			
			else if ( $this->selectedView == self::$VIEW_BLOCK_RULES || $this->selectedView == self::$VIEW_BLOCK_RULES_USER ) $this->adminUser->buildBlockRulesPanel();
			else if ( $this->selectedView == self::$VIEW_BLOCK_RULES_URL ) $this->adminUrl->buildBlockUrlRulesPanel();
			
			else if ( $this->selectedView == self::$VIEW_SPIDER_TRAP ) $this->adminSpidertrap->buildSpiderTrapPanel();
		}
		
		private function buildAboutPanel()
		{
			?>
			<div id="vsfBlockAbout" class="postbox">
				<h3 class="hndle"><?php _e('About VSF Simple Block', 'vsf-simple-block'); ?></h3>
				<div class="inside">
					<ul>
						<li><a href="http://blog.v-s-f.co.uk/simple-block/?simple-block-admin" target="_blank">VSF Simple Block - Home Page</a></li>
						<li><a href="http://wordpress.org/extend/plugins/vsf-simple-block/" target="_blank">VSF Simple Block - Wordpress Page</a></li>
						<li><?php _e('Please rate whether this plug-in works on the WordPress website to help others make an informed decision', 'vsf-simple-block'); ?></li>
						<li><a href="http://www.amazon.co.uk/wishlist/2FRM957UJWLZ2" target="_blank">VSF Simple Block - Donate</a></li>
						<li>Other Plugins</li>
						<li><a href="http://blog.v-s-f.co.uk/simple-stats/?simple-block-admin" target="_blank">VSF Simple Stats - Home Page</a></li>
					</ul>
				</div>
			</div>
			<?php
		}
		
		private function buildImportExportPanel()
		{
			?>
			<div id="vsfBlockImportExport" class="postbox">
				<h3 class="hndle"><?php _e('Import and Export Settings', 'vsf-simple-block'); ?></h3>
				<div class="inside">
					<input type="button" onClick="exportSettings()" value="<?php _e('Export Settings', 'vsf-simple-block'); ?>" /><br />
					<br />
					<input type="file" name="vsfBlockImportSettingsFile" id="file" /><input type="submit" name="vsfBlockImportSettings" value="<?php _e('Import Settings', 'vsf-simple-block'); ?>" onclick="resetButtons();" />
				</div>
			</div>				
			<?php
		}
	}

?>