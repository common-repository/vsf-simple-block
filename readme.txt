=== Plugin Name ===
Contributors: Victoria Scales
Tags: traffic, visit, monitor, firewall, block, filter, reject, stop, limit, hits
Requires at least: 3.0
Tested up to: 3.3
Donate link: http://www.amazon.co.uk/wishlist/2FRM957UJWLZ2
Stable tag: trunk

VSF Simple Block plugin.  Acts as a sort of software firewall.  

== Description ==

** If you have an existing installation of this plugin, please make sure you do a database backup before upgrading - Previous versions are available on my blog **

Simple Block does what it says really.  It's effectively a software firewall of sorts.  Enter an IP Address (or an IP range) or a host or a browser summary into the block rules table and save it.  Then watch as visitors that match those entries are bounced and cannot access your site.

Block Rules:
IP address is an exact match.  
IP range is an exact match to the specified range.  
Host is a like match, so if you enter for example google, anything that has google in the host anywhere will be blocked.  
Browser summary works just like Host.  Add in a value like spider and any hit on your website that contains spider in the browser summary anywhere will be blocked.

In the settings page enter a bounce address of your chosing which will be used to "forward" the users on to if they match a record in the block table.

Filter Rules:
There is also a filter table which is read before the bounce address.  Values in this allow hits from users that match in exactly the same way as the block table.  Because this is queried before the block table any matches will be allowed through.

Block Records:
Is a list of all hits that have been bounced and also a single reason why.  for example if you have a block record for browser summary - bot - and the google bot arrives on your site, you will get a record that the google bot has been bounced. (I don't recommend blocking the google bot)

Also:
Auto block is not (coded) enabled yet.  Work in progress.

** Please note that this plugin has the ability to block you if misused!  Please be very careful when using this plugin.  This plugin requires database rights to create tables and also create and run a stored procedure.  Without those database rights this plugin will not be able to function.  **

== Installation ==

** If you have an existing installation of this plugin, please make sure you do a database backup before upgrading **

Install from fresh:
1. Download and extract it
2. Copy vsf-simple-block folder to the "/wp-content/plugins/" directory
3. Activate the plugin through the 'Plugins' menu in WordPress

Upgrade:
1. De-activate the plugin from the plugins page
2. Copy the new files to the "/wp-content/plugins/vsf-simple-block" directory
3. Re-activate the plugin from the plugins page

== Frequently Asked Questions ==

= I'm getting an error during export / import =
Go to the plugin website and post a comment on the simple block page with as much information as possible please.

= How do I block a single IP address, E.g. 192.168.0.1? =
Go to Settings -> VSF Simple Block.  Select Block Rules.  In the block rules page make sure "IP Address" is selected in the drop down.  In the text box labeled "Value" enter the IP address you wish to block and a description if wanted and then click the "Add new Block User Rule" button.

= How do I block an IP range, E.g. 192.168.0.1 through to 192.168.0.12? =
Go to Settings -> VSF Simple Block.  Select Block Rules.  In the block rules page make sure "IP Address" is selected in the drop down.  In the text box labeled "Value" enter the starting IP address and in the text box labeled "IP to (Range)", enter the ending IP address of the range you wish to block.  Add a description if wanted and then click the "Add new Block User Rule" button.

= I can't install the plugin because the stored procedure won't create/execute (permission problem) =
Each web host gives different features and permissions, so I cannot offer much help on this...
Please see the following sites:
http://dev.mysql.com/doc/refman/5.0/en/stored-routines-privileges.html
http://markcordell.blogspot.com/2008/11/mysql-stored-procedure-permissions-and.html
http://www.mickgenie.com/blog/mysql-stored-procedures-permission-problem/
Alternatively, try asking your web host to allow you the relevant permissions to create and execute stored procedures.

= I'm having trouble with the plugin =
Go to the plugin website and post a comment on the simple block page with as much information as possible please.

= I can't get anything to work =
Sorry, the plugin isn't for you then.  Try another plugin.

== Screenshots ==

1. Image of the admin page block records table
2. Image of the admin page filter user rules table
3. Image of the admin page filter url rules table
4. Image of the admin page block user rules table
5. Image of the admin page block user rules table - adding a single IP address block rule
6. Image of the admin page block url rules table

== Changelog ==

= 1.1 =
Bug fix to the stored procedure.  It wasn't logging IP blocks.  With thanks to Sylvain for finding the bug!  
Also made the block log insert into a single stored procedure rather than many identical lines of code around the place.

= 1.0 =
Added url blocking.

= 0.2.1 =
Remove references to regex

= 0.2 =
Added spider trap information and page to the project.

= 0.1 =
First and stable version.