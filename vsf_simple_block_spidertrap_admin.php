<?php if (!defined('VSF_BLOCK_DIR')) die("Denied");

class VSFSimpleBlockSpidertrapAdmin
{
	public function getJavascript()
	{
		?>
		function downloadSpidertrap()
		{
			document.getElementById('vsfBlockDownloadSpidertrap').value = "download";
			document.getElementById('vsfBlockForm').submit();
		}

		function resetSpidertrap()
		{
			document.getElementById('vsfBlockDownloadSpidertrap').value = "";
		}
		<?php 
	}
	
	public function getHiddenFields() {	?><input id="vsfBlockDownloadSpidertrap" type="hidden" name="vsfBlockDownloadSpidertrap" /><?php }
	
	public function buildSpiderTrapPanel()
	{
		?>
		<div id="vsfSpiderTrapPanel" class="postbox">
			<h3 class="hndle"><?php _e('Spider Trap', 'vsf-simple-block'); ?></h3>
			<div class="inside">
				<h3><?php _e('What is a rougue spider?', 'vsf-simple-block'); ?></h3>
				<p><?php _e('If you\'re using any plugin that shows you exactly who is visiting your site (for example vsf-simpe-stats), you will have noticed the vast number of spiders that come to your site.  
							 Arguably the most famous of them all is the Google Bot.  A lot of them are there to provide a useful service - indexing your site to help others find it when 
							 making a search on a search engine.  But others are not.  Examples are ones that scrape your site of it\'s content, ones that use up bandwidth or ones 
							 that "search" your site at the speed of light (they should be fairly slow so as not to overwhelm your site).  Which spiders are legitimate is entirely
							 up to you, but my pet hates are (in no particular order):', 'vsf-simple-block'); ?></p>
				<p>wasalive<br />CCBot<br />Nutch<br />Deepnet Explorer<br />spreadia<br />Speedy Spider<br />(the list goes on - and on...)<br /></p>
				
				<h3><?php _e('So, how do I stop rogue spiders?', 'vsf-simple-block'); ?></h3>
				<p><?php _e('Well, since you\'ve downloaded this plugin, you\'re part way there already.  You could add keywords to the block table for every spider you don\'t like,
							but you can also do the following:', 'vsf-simple-block'); ?></p>
				
				
				<h4>1. <?php _e('Add a robots.txt file to your site', 'vsf-simple-block'); ?></h4>
				<p><?php _e('If you haven\'t already got a robots.txt file, create one!  Otherwise, edit your existing file to add a disallow path for /spidertrap.php and /spidertrap.html', 'vsf-simple-block'); ?><br />
				<?php _e('If you\'re creating a file for the first time, add the following into the file:', 'vsf-simple-block'); ?><br />
				<b># /robots.txt</b><br />
				<b>User-agent: *</b><br />
				<b>Disallow: /spidertrap.php</b><br />
				<b>Disallow: /spidertrap.html</b><br />
				<?php _e('The above tells all spiders with any user agent to not go to the file spidertrap.php.  Any spiders that disobey that command, I class as ones that are not 
						  wanted on my site.  Save the file and upload it to the root of your web space.', 'vsf-simple-block'); ?></p>
				<p><?php _e('I suggest you now wait an hour before proceeding with the next part.  That will give the spiders a chance to read the robots.txt file and process the command.', 'vsf-simple-block'); ?></p>
				
				
				<h4>2. <?php _e('Add a spider trap link to your template', 'vsf-simple-block'); ?></h4>
				<p><?php _e('Now you will need to place the below link somewhere on the main template page that will act as bait to rogue spiders.  The following link has javascript to 
						  prevent users from being able to click on the link, but not spiders.  Ideally this should be placed as high up the page as possible, but that might not be 
						  possible (like in my case, where it\'s in the footer of my site).', 'vsf-simple-block'); ?></p>
				<p>
					&lt;!-- spider trap --&gt;<br />
						&lt;a href="/spidertrap.php" onclick="return false;" onmouseover="window.status='Do not follow this link, or you will be blocked from this site. This is a spider trap.'; return true;"&gt;<br />
							&lt;font color="white" size="-2"&gt;.&lt;/font&gt;<br />
						&lt;/a&gt;<br />
					&lt;!-- end spider trap --&gt;
				</p>
				
				
				<h4>3. <?php _e('Add the spider trap page to your site', 'vsf-simple-block'); ?></h4>
				<p><?php _e('This is the last part, add a page which will auto block all users who found their way to spidertrap.php.', 'vsf-simple-block'); ?></p>
				<p><?php _e('You can either find the spider.php file inside the vsf-simple-block folder or click the button below to generate one.  If you use the one from the vsf-simple-block
						  folder, you will need to manually edit the database parameters - clicking the below button will do that for you.  Save the file and then upload it to the root of your
						  web space.  Now just wait.  Any spiders which find their way onto spidertrap.php will be blocked and a record will appear in the block records table.', 'vsf-simple-block'); ?></p>
				<p><input type="button" onClick="downloadSpidertrap()" value="Download spidertrap.php file" /></p>


				<p></p>
				<h3><?php _e('References', 'vsf-simple-block'); ?></h3>
				<p><a href="http://www.forumpostersunion.com/forumdisplay.php?s=71969227db92d485543185943f3a4b7a&f=167"><?php _e('Spiders, Crawlers and web robots forum', 'vsf-simple-block'); ?></a><br />
					<a href="http://www.leekillough.com/robots.html?vsf"><?php _e('How to Defeat Bad Web Robots With Apache', 'vsf-simple-block'); ?></a></p>
				
			</div>
		</div>
		
		<?php
	}
}

?>