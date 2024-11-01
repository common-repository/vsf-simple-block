<?php 

	/* Sets up the tables and admin page */
	class BlockSetup
	{
		public static $TABLE_BLOCK = "vsf_block";
		public static $TABLE_HITS = "vsf_block_hits";
		public static $TABLE_RULES = "vsf_block_rules";
		public static $TABLE_URLS = "vsf_block_urls";
		
		public static $STORED_PROCEDURE_URL = "vsfBlockCheckURL";
		
		public static $INDEX_PREFIX = "idxVsfBlock";
		public static $INDEX_RULES_ALL = "RulesAll";
		public static $INDEX_BLOCK_IP = "IP";
		public static $INDEX_BLOCK_HOST = "Host";
		public static $INDEX_URLS_ALL = "URLsAll";
		
		public static $TABLE_BLOCK_ID = "id";
		public static $TABLE_BLOCK_IP = "ip";
		public static $TABLE_BLOCK_HOST = "host";
		public static $TABLE_BLOCK_HOST_MAX_LENGTH = 200;
		public static $TABLE_BLOCK_BROWSER_SUMMARY = "browserSummary";
		public static $TABLE_BLOCK_BROWSER_SUMMARY_MAX_LENGTH = 400;
		public static $TABLE_BLOCK_DESCRIPTION = "description";
		public static $TABLE_BLOCK_DESCRIPTION_MAX_LENGTH = 200;
		public static $TABLE_BLOCK_DATE_BLOCKED = "dateBlocked";
		
		public static $TABLE_FILTERS_BROWSER_KEYWORD = "browserKeyword";
		
		// rules
		
		public static $TABLE_RULES_ID = "id";
		public static $TABLE_RULES_BROWSER_KEYWORD = "browserKeyword";
		public static $TABLE_RULES_BROWSER_KEYWORD_MAX_LENGTH = 100;
		public static $TABLE_RULES_IP1 = "ip1";
		public static $TABLE_RULES_IP2 = "ip2";
		public static $TABLE_RULES_HOST = "host";
		public static $TABLE_RULES_HOST_MAX_LENGTH = 100;
		public static $TABLE_RULES_TYPE = "type";
		public static $TABLE_RULES_RULE = "rule";
		public static $TABLE_RULES_DESCRIPTION = "description";
		public static $TABLE_RULES_DESCRIPTION_MAX_LENGTH = 200;
		
		/** Record is a filter rule */
		public static $TABLE_RULES_RULE_FILTER = 1;
		/** Record is a block rule */
		public static $TABLE_RULES_RULE_BLOCK = 2;
		/** An exact block only occurs if the record matches the ip, host and browser in the rules table */
		public static $TABLE_RULES_RULE_EXACT_BLOCK = 3;
		
		/** Block a hit on all fields, ip, host and browser.  Must match all three to be blocked */
		public static $TABLE_RULES_TYPE_ALL = 0;
		public static $TABLE_RULES_TYPE_IP = 1;
		public static $TABLE_RULES_TYPE_HOST = 2;
		public static $TABLE_RULES_TYPE_BROWSER = 3;
		
		// urls
		
		public static $TABLE_URL_ID = "id";
		public static $TABLE_URL_TYPE = "type";
		public static $TABLE_URL_RULE = "rule";
		public static $TABLE_URL_URL = "url";
		public static $TABLE_URL_URL_MAX_LENGTH = 255;
		public static $TABLE_URL_DESCRIPTION = "description";
		public static $TABLE_URL_DESCRIPTION_MAX_LENGTH = 200;
		
		/** Record is a filter rule */
		public static $TABLE_URL_RULE_FILTER = 1;
		/** Record is a block rule */
		public static $TABLE_URL_RULE_BLOCK = 2;
		
		/** Determines where the url will be filtered, E.g. it must start with, end with or be anywhere in */
		public static $TABLE_URL_TYPE_START = 0;
		public static $TABLE_URL_TYPE_END = 1;
		public static $TABLE_URL_TYPE_ANYWHERE = 2;
		
		// misc
		
		private $tablePrefix = "";
		
		private static $CURRENT_VERSION = "1.1";
		
		public function activation()
		{
			global $wpdb;
			$this->tablePrefix = $wpdb->prefix;
		
			VSFBlockEssentials::log("get option for vsf_block_version = " . get_option('vsf_block_version'));
			
			if ( get_option('vsf_block_version') == '0.1' )
			{
				$this->upgradeToVersion1();
			}
			else if( get_option('vsf_block_version') == '0.2' )
			{
				$this->upgradeToVersion1();
			}
			else if( get_option('vsf_block_version') == '1.0' )
			{
				$this->upgradeToVersion101();
			}
			else if ( get_option('vsf_block_version') != self::$CURRENT_VERSION ) 
			{
				VSFBlockEssentials::log("about to create tables <br />");
				$this->createBlockTable();
				$this->createBlockHitsTable();
				$this->createBlockBrowserRulesTable();
				$this->createBlockURLTable();
			
				$this->createAllStoredProcedures();

				
				/* Now create the other option values */
				
				// Determines whether auto block is enabled
				update_option("vsf_block_auto_block", 0);
				
				// Current version of this stats plugin database
				update_option('vsf_block_version', self::$CURRENT_VERSION);
				
				// Bounce address
				update_option("vsf_block_bounce_address", 'http://www.google.com');
				
				update_option("vsf_block_max_hits_in_one_minute", 10);
				update_option("vsf_block_max_hits_in_two_minute", 15);
				update_option("vsf_block_max_hits_in_five_minute", 25);
				
				update_option("vsf_block_table_items_quantity", 30);
			}
		}
		
		private function upgradeToVersion1()
		{
			$this->createBlockURLTable();
			
			$this->createAllStoredProcedures();
				
			// Current version of this block plugin database
			update_option('vsf_block_version', self::$CURRENT_VERSION);
		}
		
		private function upgradeToVersion101(){
			$this->createAllStoredProcedures();
				
			// Current version of this block plugin database
			update_option('vsf_block_version', self::$CURRENT_VERSION);
		}
		
		private function createAllStoredProcedures()
		{
			VSFBlockEssentials::log("about to do stored procs for upgrade to 1");
			$this->createBlockHitStoredProcedure();
			$this->createBlockCheckHitStoredProcedure();
			$this->createStoredProcedureSpiderTrap();
			$this->createStoredProcedureURLCheck();
			$this->createLogBlockStoredProcedure();
		}
		
		/**
		 *	Create table WP_VSF_BLOCK
		 *	Separate id for the block record
		 *	IP address to be blocked
		 *	Host to be blocked as an alternative to ip
		 *	Description of the block
		 *	Date of the block
		 */
		protected function createBlockTable()
		{
			// only create if the version number is not 1
			if ( get_option('vsf_block_table_version') != '1' )
			{
				$vsfBlockTableResult = mysql_query
				(
					"CREATE TABLE `" . $this->tablePrefix . self::$TABLE_BLOCK . "` ("
					."`" . self::$TABLE_BLOCK_ID . "` int(100) NOT NULL auto_increment,"
					."`" . self::$TABLE_BLOCK_IP . "` int(16) UNSIGNED,"
					."`" . self::$TABLE_BLOCK_HOST . "` varchar(" . self::$TABLE_BLOCK_HOST_MAX_LENGTH . "),"
					."`" . self::$TABLE_BLOCK_BROWSER_SUMMARY . "` varchar(" . self::$TABLE_BLOCK_BROWSER_SUMMARY_MAX_LENGTH . "),"
					."`" . self::$TABLE_BLOCK_DESCRIPTION . "` varchar(" . self::$TABLE_BLOCK_DESCRIPTION_MAX_LENGTH . "),"
					."`" . self::$TABLE_BLOCK_DATE_BLOCKED . "` int(16) UNSIGNED,"
					."PRIMARY KEY  (`" . self::$TABLE_BLOCK_ID . "`)"
					.")"
				);

				if ( !$vsfBlockTableResult ) die ("Cannot create vsf block table.<br />" . mysql_error());
			}
			
			update_option("vsf_block_table_version", 1);
		}
		
		/**
		 *	Create table WP_VSF_BLOCK_HITS
		 *	Separate id for the block hit record
		 *	IP address to be monitored
		 *	date of the hit
		 */
		protected function createBlockHitsTable()
		{
			// only create if the version number is not 1
			if ( get_option('vsf_block_hits_table_version') != '1' )
			{
				// table to hold hits for the block filter
				$vsfBlockHitsTableResult = mysql_query
				(
					"CREATE TABLE `" . $this->tablePrefix . self::$TABLE_HITS . "` ("
					."`id` int(100) NOT NULL auto_increment," // separate id for the hit record
					."`ip` int UNSIGNED," // IP address being monitored
					."`dateOfHit` int UNSIGNED," // date of the hit
					."PRIMARY KEY  (`id`)"
					.")"
				);

				if ( !$vsfBlockHitsTableResult ) die ("Cannot create vsf block hits table.<br />" . mysql_error());
			}
			
			update_option("vsf_block_hits_table_version", 1);
		}
		
		/**
		 *	Create table wp_vsf_block_keywords
		 *	This table contains a list of things to match hits on.  The user can either enter an IP, HOST or Browser Summary keyword.
		 *	IP address is a straight match or a range span.
		 * 	Host is a contains match just like browser summary keywords.
		 *  Browser summary keywords are strings that bots might contain in the browser summary to search for when identifying bots
		 *	For example "bot", "spider" and if the summary contains any of those strings, block straight away.
		 *	Rules can either be filters or blocks depending on the rule column.
		 */
		protected function createBlockBrowserRulesTable()
		{
			if ( get_option('vsf_block_rules_table_version') != '1' )
			{
				// table to hold hits for the block filter
				$vsfBlockKeywordsTableResult = mysql_query
				(
					"CREATE TABLE `" . $this->tablePrefix . self::$TABLE_RULES . "` ("
					."`" . self::$TABLE_RULES_ID . "` int(100) NOT NULL auto_increment," // separate id for the record
					."`" . self::$TABLE_RULES_RULE . "` int(3) NOT NULL," // represents the rule type, 1 = filter, 2 = block
					."`" . self::$TABLE_RULES_TYPE . "` int(3) NOT NULL," // represents the type of entry, 1 = ip, 2 = host, 3 = browser keyword
					."`" . self::$TABLE_RULES_IP1 . "` int(16) UNSIGNED," // ip address 1
					."`" . self::$TABLE_RULES_IP2 . "` int(16) UNSIGNED," // ip address 2
					."`" . self::$TABLE_RULES_HOST . "` varchar(" . self::$TABLE_RULES_HOST_MAX_LENGTH . ")," // host
					."`" . self::$TABLE_RULES_BROWSER_KEYWORD . "` varchar(" . self::$TABLE_RULES_BROWSER_KEYWORD_MAX_LENGTH . ")," // keyword like "bot"
					."`" . self::$TABLE_RULES_DESCRIPTION . "` varchar(" . self::$TABLE_RULES_DESCRIPTION_MAX_LENGTH . ")," // description of the rule
					."PRIMARY KEY  (`" . self::$TABLE_RULES_ID . "`)"
					.")"
				);

				if ( !$vsfBlockKeywordsTableResult )
				{
					die ("Cannot create vsf block rules table.<br />" . mysql_error());
				}
				else
				{
					$Index = "CREATE index " . self::$INDEX_PREFIX . self::$INDEX_RULES_ALL;
					$Index .= " on " . $this->tablePrefix . self::$TABLE_RULES;
					$Index .= " (" . self::$TABLE_RULES_RULE . ", " . self::$TABLE_RULES_TYPE . ", " . self::$TABLE_RULES_IP1 . ", " . self::$TABLE_RULES_IP2 . ", " . self::$TABLE_RULES_HOST . ", " . self::$TABLE_RULES_BROWSER_KEYWORD . ")";
					
					$addIndexResult = mysql_query($Index);
					if ( !$addIndexResult ) die ("Cannot create vsf block index on rules table.<br />" . mysql_error());
				}
			}
			
			update_option("vsf_block_rules_table_version", 1);
		}
		
		/**
		 * This table holds all the url rule records, for example filter url or block url.
		 */
		protected function createBlockURLTable()
		{
			if ( get_option('vsf_block_url_table_version') != '1' )
			{
				// table to hold hits for the block filter
				$vsfBlockURLSTableResult = mysql_query
				(
					"CREATE TABLE " . $this->tablePrefix . self::$TABLE_URLS . " ("
					. self::$TABLE_URL_ID   . " int(100) NOT NULL auto_increment," // separate id for the record
					. self::$TABLE_URL_RULE . " int(3) NOT NULL," // represents the rule type, 1 = filter, 2 = block
					. self::$TABLE_URL_TYPE . " int(3) NOT NULL," // represents the type of entry, 1 = start, 2 = anywhere, 3 = end
					. self::$TABLE_URL_URL  . " varchar(" . self::$TABLE_URL_URL_MAX_LENGTH . ")," // E.g. phpmyadmin
					. self::$TABLE_URL_DESCRIPTION  . " varchar(" . self::$TABLE_URL_DESCRIPTION_MAX_LENGTH . "),"
					."PRIMARY KEY  (`" . self::$TABLE_URL_ID . "`)"
					.")"
				);

				if ( !$vsfBlockURLSTableResult )
				{
					die ("Cannot create vsf block urls table.<br />" . mysql_error());
				}
				else
				{
					$Index = "CREATE index " . self::$INDEX_PREFIX . self::$INDEX_URLS_ALL;
					$Index .= " on " . $this->tablePrefix . self::$TABLE_URLS;
					$Index .= " (" . self::$TABLE_URL_RULE . ", " . self::$TABLE_URL_TYPE . ", " . self::$TABLE_URL_URL .")";
					
					$addIndexResult = mysql_query($Index);
					if ( !$addIndexResult ) die ("Cannot create vsf block index on urls table.<br />" . mysql_error());
				}
			}
			
			update_option("vsf_block_url_table_version", 1);
		}
		
		protected function createBlockCheckHitStoredProcedure()
		{
			mysql_query("DROP FUNCTION IF EXISTS vsfBlockCheckHit;");
			$checkHitFunction = "
			CREATE FUNCTION vsfBlockCheckHit(ipAddress VARCHAR(15), hostAddress TEXT, browserSummary TEXT, simpleStats BOOLEAN)
			RETURNS INT(1)
			DETERMINISTIC
			BEGIN
				DECLARE FINE INT DEFAULT 0;
				DECLARE FILTERED INT DEFAULT 1;
				DECLARE BLOCK_BROWSER INT DEFAULT 2;
				DECLARE BLOCK_HOST INT DEFAULT 3;
				DECLARE BLOCK_IP INT DEFAULT 4;
				DECLARE EXACT_BLOCK INT DEFAULT 5;
				
				DECLARE simpleStatsValue INT DEFAULT 0;
				DECLARE hitFilterValue INT DEFAULT 0;
				DECLARE botSummaryHit INT DEFAULT 0;
				DECLARE blockHit INT DEFAULT 0;
				
				DECLARE keywordMatch VARCHAR(" . self::$TABLE_RULES_BROWSER_KEYWORD_MAX_LENGTH . ") DEFAULT '';
				
				DECLARE callValue INT DEFAULT 0;
				
				DECLARE summary VARCHAR(" . (self::$TABLE_RULES_BROWSER_KEYWORD_MAX_LENGTH + 70) . ") DEFAULT '';
				
				IF ( simpleStats ) THEN
					SELECT count(*) INTO simpleStatsValue 
					FROM " . $this->tablePrefix . "vsf_stats_filter 
					WHERE (INET_ATON(ipAddress) = ip1) 
						OR (INET_ATON(ipAddress) BETWEEN ip1 AND ip2);
						
					IF ( simpleStatsValue > 0 ) THEN
						RETURN FILTERED;
					END IF;
				END IF;
				
				SELECT count(*) INTO hitFilterValue 
				FROM " . $this->tablePrefix . self::$TABLE_RULES . " 
				WHERE rule = " . self::$TABLE_RULES_RULE_FILTER . "
					AND (
						((INET_ATON(ipAddress) = ip1) OR (INET_ATON(ipAddress) BETWEEN ip1 AND ip2)) 
						OR (browserSummary like concat('%', " . self::$TABLE_RULES_BROWSER_KEYWORD . ", '%'))
						OR (hostAddress like concat('%', " . self::$TABLE_RULES_HOST . ", '%'))
					);
					
				IF ( hitFilterValue > 0 ) THEN
					RETURN FILTERED;
				END IF;
				
				
				SELECT count(*) INTO hitFilterValue 
				FROM " . $this->tablePrefix . self::$TABLE_RULES . " 
				WHERE rule = " . self::$TABLE_RULES_RULE_EXACT_BLOCK . "
					AND (INET_ATON(ipAddress) = ip1)
					AND (browserSummary = " . self::$TABLE_RULES_BROWSER_KEYWORD . ")
					AND (hostAddress = " . self::$TABLE_RULES_HOST . ");
				
				IF ( hitFilterValue > 0 ) THEN
					SELECT " . self::$TABLE_RULES_DESCRIPTION . " INTO keywordMatch
					FROM " . $this->tablePrefix . self::$TABLE_RULES . " 
					WHERE rule = " . self::$TABLE_RULES_RULE_EXACT_BLOCK . "
						AND (INET_ATON(ipAddress) = ip1)
						AND (browserSummary = " . self::$TABLE_RULES_BROWSER_KEYWORD . ")
						AND (hostAddress = " . self::$TABLE_RULES_HOST . ");
						
					SET summary = concat('Exact block record: ', keywordMatch);
					SET blockHit = EXACT_BLOCK;
				END IF;
				
				
				IF (blockHit = 0) THEN
					SELECT count(*) INTO botSummaryHit
					FROM " . $this->tablePrefix . self::$TABLE_RULES . "
					WHERE rule = " . self::$TABLE_RULES_RULE_BLOCK . "
						AND type = " . self::$TABLE_RULES_TYPE_BROWSER . "
						AND browserSummary like concat('%', " . self::$TABLE_RULES_BROWSER_KEYWORD . ", '%');
					
					IF ( botSummaryHit > 0 ) THEN
						SELECT " . self::$TABLE_RULES_BROWSER_KEYWORD . " INTO keywordMatch
						FROM " . $this->tablePrefix . self::$TABLE_RULES . "
						WHERE rule = " . self::$TABLE_RULES_RULE_BLOCK . "
							AND type = " . self::$TABLE_RULES_TYPE_BROWSER . "
							AND browserSummary like concat('%', " . self::$TABLE_RULES_BROWSER_KEYWORD . ", '%')
						LIMIT 0, 1;
						
						SET summary = concat('Browser summary matched ', botSummaryHit, ' keyword values.  E.g. ', keywordMatch);
						SET blockHit = BLOCK_BROWSER;
					END IF;
				END IF;
				
				
				IF (blockHit = 0) THEN
					SELECT count(*) INTO botSummaryHit
					FROM " . $this->tablePrefix . self::$TABLE_RULES . "
					WHERE rule = " . self::$TABLE_RULES_RULE_BLOCK . "
						AND type = " . self::$TABLE_RULES_TYPE_HOST . "
						AND hostAddress like concat('%', " . self::$TABLE_RULES_HOST . ", '%');
					
					IF ( botSummaryHit > 0 ) THEN
						SELECT " . self::$TABLE_RULES_HOST . " INTO keywordMatch
						FROM " . $this->tablePrefix . self::$TABLE_RULES . "
						WHERE rule = " . self::$TABLE_RULES_RULE_BLOCK . "
							AND type = " . self::$TABLE_RULES_TYPE_HOST . " 
							AND hostAddress like concat('%', " . self::$TABLE_RULES_HOST . ", '%')
						LIMIT 0, 1;
						
						SET summary = concat('Host matched ', botSummaryHit, ' keyword values.  E.g. ', keywordMatch);
						SET blockHit = BLOCK_HOST;
					END IF;
				END IF;
				
				
				
				IF (blockHit = 0) THEN
					SELECT count(*) INTO botSummaryHit
					FROM " . $this->tablePrefix . self::$TABLE_RULES . "
					WHERE rule = " . self::$TABLE_RULES_RULE_BLOCK . "
						AND type = " . self::$TABLE_RULES_TYPE_IP . "
						AND ((INET_ATON(ipAddress) = ip1) OR (INET_ATON(ipAddress) BETWEEN ip1 AND ip2));
					
					IF ( botSummaryHit > 0 ) THEN
						SELECT INET_NTOA(" . self::$TABLE_RULES_IP1 . ") INTO keywordMatch
						FROM " . $this->tablePrefix . self::$TABLE_RULES . "
						WHERE rule = " . self::$TABLE_RULES_RULE_BLOCK . "
							AND type = " . self::$TABLE_RULES_TYPE_IP . " 
							AND ((INET_ATON(ipAddress) = ip1) OR (INET_ATON(ipAddress) BETWEEN ip1 AND ip2))
						LIMIT 0, 1;
						
						SET summary = concat('IP matched ', botSummaryHit, ' ip values.  E.g. ', keywordMatch);
						SET blockHit = BLOCK_IP;
					END IF;
				END IF;
				
				IF (blockHit > 0) THEN
					SELECT vsfBlockLog(ipAddress, hostAddress, browserSummary, summary) INTO callValue;
					RETURN blockHit;
				END IF;
				
				RETURN FINE;
				
			END ";
			
			$checkHitFunctionCreated = mysql_query($checkHitFunction);
			VSFBlockEssentials::log("checkHitFunctionCreated run " . $checkHitFunctionCreated . " <br />");
			if ( !$checkHitFunctionCreated ) die ("Cannot create vsf block stored procedure checkHitFunction.<br />" . mysql_error());
		}
		
		protected function createStoredProcedureSpiderTrap()
		{
			mysql_query("DROP FUNCTION IF EXISTS vsfBlockSpiderTrapHit;");
			$spidertrapHitFunction = "
			CREATE FUNCTION vsfBlockSpiderTrapHit(ipAddress VARCHAR(15), hostAddress TEXT, browserSummary TEXT)
			RETURNS INT(1)
			DETERMINISTIC
			BEGIN
				
				RETURN vsfBlockHit(ipAddress, hostAddress, browserSummary, 'Automatic ban for accessing spider trap page');
				
			END ";
			
			$spidertrapHitFunctionCreated = mysql_query($spidertrapHitFunction);
			VSFBlockEssentials::log("spidertrapHitFunctionCreated run " . $spidertrapHitFunctionCreated . " <br />");
			if ( !$spidertrapHitFunctionCreated ) die ("Cannot create vsf block stored procedure spidertrapHitFunction.<br />" . mysql_error());
		}
		
		protected function createStoredProcedureURLCheck()
		{
			mysql_query("DROP FUNCTION IF EXISTS " . self::$STORED_PROCEDURE_URL . ";");
			$checkHitFunction = "
			CREATE FUNCTION " . self::$STORED_PROCEDURE_URL . "(ipAddress VARCHAR(15), hostAddress TEXT, browserSummary TEXT, simpleStats BOOLEAN, urlAccessing TEXT)
			RETURNS INT(1)
			DETERMINISTIC
			BEGIN
				DECLARE FINE INT DEFAULT 0;
				DECLARE FILTERED INT DEFAULT 1;
				DECLARE BLOCKED INT DEFAULT 2;
				
				DECLARE hitFilterValue INT DEFAULT 0;
				DECLARE botSummaryHit INT DEFAULT 0;
				DECLARE blockHit INT DEFAULT 0;
				
				DECLARE callValue INT DEFAULT 0;
				
				DECLARE keywordMatch VARCHAR(" . self::$TABLE_RULES_BROWSER_KEYWORD_MAX_LENGTH . ") DEFAULT '';
				
				DECLARE summary VARCHAR(" . (self::$TABLE_RULES_BROWSER_KEYWORD_MAX_LENGTH + 70) . ") DEFAULT '';
				
				
				SELECT count(*) INTO hitFilterValue 
				FROM " . $this->tablePrefix . self::$TABLE_URLS . " 
				WHERE " . self::$TABLE_URL_RULE . " = " . self::$TABLE_URL_RULE_FILTER . "
					AND (
						((upper(urlAccessing) like upper(concat(" . self::$TABLE_URL_URL . ", '%'))) AND (" . self::$TABLE_URL_TYPE . " = " . self::$TABLE_URL_TYPE_START . "))
						OR ((upper(urlAccessing) like upper(concat('%', " . self::$TABLE_URL_URL . "))) AND (" . self::$TABLE_URL_TYPE . " = " . self::$TABLE_URL_TYPE_END . "))
						OR ((upper(urlAccessing) like upper(concat('%', " . self::$TABLE_URL_URL . ", '%'))) AND (" . self::$TABLE_URL_TYPE . " = " . self::$TABLE_URL_TYPE_ANYWHERE . "))
					);
					
				IF ( hitFilterValue > 0 ) THEN
					RETURN FILTERED;
				END IF;
				
				
				SELECT count(*) INTO hitFilterValue 
				FROM " . $this->tablePrefix . self::$TABLE_URLS . " 
				WHERE rule = " . self::$TABLE_URL_RULE_BLOCK . "
					AND (
						((upper(urlAccessing) like upper(concat(" . self::$TABLE_URL_URL . ", '%'))) AND (" . self::$TABLE_URL_TYPE . " = " . self::$TABLE_URL_TYPE_START . "))
						OR ((upper(urlAccessing) like upper(concat('%', " . self::$TABLE_URL_URL . "))) AND (" . self::$TABLE_URL_TYPE . " = " . self::$TABLE_URL_TYPE_END . "))
						OR ((upper(urlAccessing) like upper(concat('%', " . self::$TABLE_URL_URL . ", '%'))) AND (" . self::$TABLE_URL_TYPE . " = " . self::$TABLE_URL_TYPE_ANYWHERE . "))
					);
				
				IF ( hitFilterValue > 0 ) THEN
					SELECT " . self::$TABLE_URL_URL . " INTO keywordMatch
					FROM " . $this->tablePrefix . self::$TABLE_URLS . " 
					WHERE rule = " . self::$TABLE_URL_RULE_BLOCK . "
						AND (
							((upper(urlAccessing) like upper(concat(" . self::$TABLE_URL_URL . ", '%'))) AND (" . self::$TABLE_URL_TYPE . " = " . self::$TABLE_URL_TYPE_START . "))
							OR ((upper(urlAccessing) like upper(concat('%', " . self::$TABLE_URL_URL . "))) AND (" . self::$TABLE_URL_TYPE . " = " . self::$TABLE_URL_TYPE_END . "))
							OR ((upper(urlAccessing) like upper(concat('%', " . self::$TABLE_URL_URL . ", '%'))) AND (" . self::$TABLE_URL_TYPE . " = " . self::$TABLE_URL_TYPE_ANYWHERE . "))
						);
						
					SET summary = concat('Blocked for accessing blocked url: ', keywordMatch);
					SET blockHit = BLOCKED;
				END IF;
				
				
				
				IF (blockHit > 0) THEN
					SELECT vsfBlockLog(ipAddress, hostAddress, browserSummary, summary) INTO callValue;
					SELECT vsfBlockHit(ipAddress, hostAddress, browserSummary, summary) INTO callValue;
					
					RETURN blockHit;
					
				END IF;
				
				RETURN FINE;
				
			END ";
			
			$checkHitFunctionCreated = mysql_query($checkHitFunction);
			VSFBlockEssentials::log("checkHitFunctionCreated run " . $checkHitFunctionCreated . " <br />");
			if ( !$checkHitFunctionCreated ) die ("Cannot create vsf block stored procedure vsfBlockCheckURL.<br />" . mysql_error());
		}
		
		public function createBlockHitStoredProcedure()
		{
			mysql_query("DROP FUNCTION IF EXISTS vsfBlockHit;");
			$spidertrapHitFunction = "
			CREATE FUNCTION vsfBlockHit(ipAddress VARCHAR(15), hostAddress TEXT, browserSummary TEXT, description TEXT)
			RETURNS INT(1)
			DETERMINISTIC
			BEGIN
				
				DECLARE hitFilterValue INT DEFAULT 0;
				
				SELECT count(*) INTO hitFilterValue 
				FROM " . $this->tablePrefix . self::$TABLE_RULES . " 
				WHERE rule = " . self::$TABLE_RULES_RULE_EXACT_BLOCK . "
					AND (INET_ATON(ipAddress) = ip1)
					AND (browserSummary = " . self::$TABLE_RULES_BROWSER_KEYWORD . ")
					AND (hostAddress = " . self::$TABLE_RULES_HOST . ");
			
				IF (hitFilterValue = 0) THEN
					INSERT INTO " . $this->tablePrefix . self::$TABLE_RULES . 
						" (" . self::$TABLE_RULES_RULE . ", " . self::$TABLE_RULES_TYPE . ", " . self::$TABLE_RULES_DESCRIPTION . "," . self::$TABLE_RULES_IP1 . ", " . self::$TABLE_RULES_HOST . ", " . self::$TABLE_RULES_BROWSER_KEYWORD . ")" .
					"VALUES (" . self::$TABLE_RULES_RULE_EXACT_BLOCK . ", " . self::$TABLE_RULES_TYPE_ALL . ", description, INET_ATON(ipAddress), hostAddress, browserSummary);
				END IF;
				
				RETURN 1;
				
			END ";
			
			$spidertrapHitFunctionCreated = mysql_query($spidertrapHitFunction);
			VSFBlockEssentials::log("vsfBlockHit run " . $spidertrapHitFunctionCreated . " <br />");
			if ( !$spidertrapHitFunctionCreated ) die ("Cannot create vsf block stored procedure vsfBlockHit.<br />" . mysql_error());
		}
		
		public function createLogBlockStoredProcedure()
		{
			mysql_query("DROP FUNCTION IF EXISTS vsfBlockLog;");
			$spidertrapHitFunction = "
			CREATE FUNCTION vsfBlockLog(ipAddress VARCHAR(15), hostAddress TEXT, browserSummary TEXT, summary TEXT)
			RETURNS INT(1)
			DETERMINISTIC
			BEGIN
				
				INSERT INTO " . $this->tablePrefix . self::$TABLE_BLOCK . " 
						(ip, host, browserSummary, description, dateBlocked) VALUES 
						(inet_aton(ipAddress), hostAddress, browserSummary, summary, UNIX_TIMESTAMP());
				IF ( (SELECT count(*) FROM " . $this->tablePrefix . self::$TABLE_BLOCK . ") > 500 ) THEN
					DELETE FROM " . $this->tablePrefix . self::$TABLE_BLOCK . " WHERE id = (SELECT MIN(id) FROM " . $this->tablePrefix . self::$TABLE_BLOCK . ");
				END IF;
				
				RETURN 1;
				
			END ";
			
			$spidertrapHitFunctionCreated = mysql_query($spidertrapHitFunction);
			VSFBlockEssentials::log("vsfBlockLog run " . $spidertrapHitFunctionCreated . " <br />");
			if ( !$spidertrapHitFunctionCreated ) die ("Cannot create vsf block stored procedure vsfBlockLog.<br />" . mysql_error());
		}
		
		public function vsf_block_create_menu()
		{
			add_options_page(__('VSF Simple Block Options', 'vsf-simple-block'), __('VSF Simple Block', 'vsf-simple-block'), 1, basename(__FILE__), array('BlockSetup', 'adminOptionsPanel'));
		}
		
		public static function adminOptionsPanel()
		{
			include_once('vsf_simple_block_setup_admin.php');
			$blockSetupAdmin = new BlockSetupAdmin();
			$blockSetupAdmin->adminOptionsPanel();
		}
	}

?>