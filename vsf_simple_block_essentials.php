<?php
/**
 * Utility class that contains functions that will be needed across the whole project.
 * All functions are static.
 */
if( !class_exists("VSFBlockEssentials") )
{
	class VSFBlockEssentials
	{
		/**
		 * Determines whether a string is null or blank
		 * @param string $stringToTest
		 * @return boolean
		 */
		public static function isEmpty($stringToTest)
		{
			return is_null($stringToTest) || (strlen($stringToTest) == 0);
		}
		
		/**
		 * Logs out if debug is set to true
		 * @param value to log out $log
		 */
		public static function log($log)
		{
			if ( VSF_BLOCK_DEBUG ) echo $log . "<br />";
		}
		
		/**
		 *	Builds a div for showing update messages
		 */
		public static function buildUpdateDiv($message) { self::buildMessageDiv(false, $message); }
		
		/**
		 *	Builds a div for showing warning messages
		 */
		public static function buildErrorDiv($message) { self::buildMessageDiv(true, $message); }
		
		public static function buildMessageDiv($error, $message) { ?><div class="<?php if( $error ) echo 'error'; else echo 'updated'; ?>"><p><strong><?php echo $message; ?></strong></p></div><?php }
		
		/**
		 *	Builds up a select query using the passed in parameters.
		 */
		public static function buildSelectQuery($count, $columns, $table, $where, $order)
		{
			$query = "SELECT ";
			
			if( $count )
			{
				$query .= "count(*) ";
			}
			else
			{
				$x = 0;
				foreach ( $columns as $column )
				{
					$query .= ($x == 0 ? "" : ", ") . $column;
					$x++;
				}
				
				$query .= " ";
			}
			
			$query .= "FROM " . $table . " ";
			
			if( !self::isEmpty($where) ) $query .= "WHERE " . $where . " ";
			
			if( !$count && !self::isEmpty($order) ) $query .= "ORDER BY " . $order;
			
			return $query;
		}

		/**
		 * Builds up the table header and footer for tables.
		 */
		function buildTableHeadAndFooter($columns)
		{
			?>
			<thead><tr><?php foreach( $columns as $column ) { self::buildTableHeadingColum($column); } ?></tr></thead>
			<tfoot><tr><?php foreach( $columns as $column ) { self::buildTableHeadingColum($column); } ?></tr></tfoot>
			<?php
		}

		/**
		 * Builds up a column heading/footing for a table.
		 */
		function buildTableHeadingColum($columnTitle)
		{
			?><th class="manage-column" scope="col"><?php echo $columnTitle; ?></th><?php
		}

		/**
		 * Escapes strings and removes angle brackets.
		 * @param string $stringToClean
		 * @param int $maxLength
		 * @return cleaned string
		 */
		public static function cleanUpString($stringToClean, $maxLength)
		{
			if( $stringToClean != null && stringToClean != "" )
			{
				$stringToClean = (strlen($stringToClean) <= $maxLength ? $stringToClean : substr($stringToClean, 0, $maxLength));
				$stringToClean = (phpversion() >= '4.3.0' ? mysql_real_escape_string($stringToClean) : mysql_escape_string($stringToClean));
				$stringToClean = str_replace(array('<', '>'), '', $stringToClean);
			}
			
			return $stringToClean;
		}
	}
}