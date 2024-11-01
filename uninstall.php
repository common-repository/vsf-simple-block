<?php

require_once('vsf_simple_block_setup.php');

if (!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')) die();

	global $wpdb;
	$tablePrefix = $wpdb->prefix;
				
	// delete data from wp_options
	delete_option("vsf_block_version");
	delete_option("vsf_block_bounce_address");
	delete_option("vsf_block_max_hits_in_one_minute");
	delete_option("vsf_block_max_hits_in_two_minute");
	delete_option("vsf_block_max_hits_in_five_minute");
	delete_option("vsf_block_init_hook_added");
	delete_option("vsf_block_admin_panel_hook_added");
	delete_option("vsf_block_auto_block");
	delete_option("vsf_block_table_items_quantity");
	
	delete_option("vsf_block_hits_table_version");
	delete_option("vsf_block_table_version");
	delete_option("vsf_block_rules_table_version");
	delete_option("vsf_block_url_table_version");
	
	delete_option("vsf_block_cookie_rules");
	delete_option("vsf_block_cookie_urls");
	delete_option("vsf_block_cookie_expire_time");
	
	mysql_query("DROP INDEX " . BlockSetup::$INDEX_PREFIX . BlockSetup::$INDEX_RULES_ALL);
	mysql_query("DROP INDEX " . BlockSetup::$INDEX_PREFIX . BlockSetup::$INDEX_BLOCK_IP);
	mysql_query("DROP INDEX " . BlockSetup::$INDEX_PREFIX . BlockSetup::$INDEX_BLOCK_HOST);
	mysql_query("DROP INDEX " . BlockSetup::$INDEX_PREFIX . BlockSetup::$INDEX_URLS_ALL);
	
	mysql_query("DROP TABLE " . $tablePrefix . BlockSetup::$TABLE_BLOCK);
	mysql_query("DROP TABLE " . $tablePrefix . BlockSetup::$TABLE_HITS);
	mysql_query("DROP TABLE " . $tablePrefix . BlockSetup::$TABLE_RULES);
	mysql_query("DROP TABLE " . $tablePrefix . BlockSetup::$TABLE_URLS);
	
	mysql_query("DROP FUNCTION vsfBlockCheckHit");
	mysql_query("DROP FUNCTION vsfBlockSpiderTrapHit");
	mysql_query("DROP FUNCTION vsfBlockCheckURL");
	mysql_query("DROP FUNCTION vsfBlockHit");
?>