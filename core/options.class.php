<?php 
/**
 * Option classes
 *
 * @copyright 	Keith Morrison, Infused Solutions	2001-2006
 * @author			Keith Morrison <keithm@infused-solutions.com>
 * @package 		options
 * @license http://opensource.org/licenses/gpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License contained in the file GNU.txt for
 * more details.
 */
 
 /*
 * $Id$
 */
	
	# Ensure this file is being included by a parent file
	defined( '_RGDS_VALID' ) or die( 'Direct access to this file is not allowed.' );
	
	/**
	* Options class.
	* When instantiated, this class loads configuration options from the database.
	* @package options
	* @subpackage classes
	*/
	class Options {
		
		/**
		* Array containing all options
		*/
		var $option_list;
		
		/**
		* Options class constructor
		* Loads options from the database and stuffs them into
		* class variables.  For example if an option has a key name of 
		* myoption and a value of 'dosomething' then a class variable is
		* created with a name of $this->myoption = 'dosomething'
		*/
		function Options() {
			$this->Initialize();
		}
		
		function Initialize() {
			global $db;
			$sql = 'SELECT * FROM '.TBL_OPTION;
			$rs = $db->Execute($sql);
			while ($row = $rs->FetchRow()) {
				$optkey = $row['opt_key'];
				$this->{$optkey} = $row['opt_val'];
				$this->option_list[$optkey] = $row['opt_val'];
			}
		}
		
		/**
		* GetOption
		* Returns a single option value.
		* This function returns null if the option is not found.
		* @param string $optkey
		* @return mixed
		*/
		function GetOption($optkey) {
			if (isset($this->{$optkey})) return $this->{$optkey};
			else return null;
		}
		
		/**
		* OptionUpdate
		* Updates the option parameters in the options table
		* @param string $opt_val
		* @param string $opt_key
		* @return boolean
		*/
		function OptionUpdate($key, $val) {
			global $db;
			
			# add the option to the table if not already there
			$sql = 'SELECT COUNT(*) FROM '.TBL_OPTION.' WHERE opt_key="'.$key.'"';
			if (!$db->GetOne($sql)) {
				$sql = 'INSERT INTO '.TBL_OPTION.' VALUES("", '.$db->qstr($key).', '.$db->qstr($val).')';
				$db->Execute($sql);
			}
			# or else update it if needed
			else {
				$sql = 'SELECT opt_val FROM '.TBL_OPTION.' WHERE opt_key="'.$key.'"';
				$old = $db->GetOne($sql);
				if ($val == $old) { 
					return false; 
				}
				else {
					$sql = 'UPDATE '.TBL_OPTION.' SET opt_val="'.$val.'" WHERE opt_key="'.$key.'"';
					$db->Execute($sql);
					return true;
				}
			}
		}
	}
?>