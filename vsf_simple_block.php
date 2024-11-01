<?php
	/*
		Plugin Name: VSF Simple Block
		Plugin URI: http://blog.v-s-f.co.uk/simple-block/
		Description: Allows you to select IP addresses, Hosts or Browser summary keywords via the simple block admin page to filter hits with.  Acts as a software firewall.  Please note that this plugin has the ability to block you if misused!  Please be very careful when using this plugin.  This plugin requires database rights to create tables and also create and run a stored procedure.  Without those database rights this plugin will not be able to function.
		Version: 1.1
		Author: Victoria Scales
		Author URI: http://www.v-s-f.co.uk
		Donation URI: http://www.amazon.co.uk/wishlist/2FRM957UJWLZ2
		Text Domain: vsf-simple-block
	*/
	
	/*
		Copyright 2012 Victoria Scales

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, see <http://www.gnu.org/licenses/>.
	*/
	
	
	define('VSF_BLOCK_DIR', dirname(__FILE__));
	define('VSF_BLOCK_DOMAIN', 'vsf-simple-block');
	define('VSF_BLOCK_DEBUG', false);
	
	include_once('vsf_simple_block_setup.php');
	include_once('vsf_simple_block_check.php');
	include_once('vsf_simple_block_spidertrap_download.php');
	
	include_once('vsf_simple_block_essentials.php');
	
	// Export the xml file of the database filter table
	if ( isset($_POST['vsfBlockExportSettings']) )
	{
		$stringToTest = $_POST['vsfBlockExportSettings'];
		if (!(is_null($stringToTest) || $stringToTest == ""))
		{
			include('vsf_simple_block_export.php');
			$vsfSimpleBlockExport = new VSFSimpleBlockExport();
			$vsfSimpleBlockExport->generateExport();
		}
	}
	
	// Download the spidertrap.php file
	if ( isset($_POST['vsfBlockDownloadSpidertrap']) )
	{
		$download = new VSFSimpleBlockSpidertrapDownload();
		$download->checkDownloadSpidertrapPost();
	}
	
	// create hook on the activation of the plugin so that the tables are created straight away!
	$obj = new BlockSetup();
	register_activation_hook(__FILE__, array($obj, 'activation'));
	
	add_action('admin_menu', array($obj, 'vsf_block_create_menu'));
	
	// will be run shortly after the plugin is loaded which is before most other wordpress features are done.
	$blockHit = new VSFBlockCheck();
	$blockHit->checkHit();

?>