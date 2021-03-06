<?php
/**
 * Genealogy classes
 *
 * This file contains classes for genealogy objects such as people, 
 * events, and families (marriages).  All of these classes query the 
 * database for information, so a database connection needs to be 
 * established prior to instantiating any of these classes.
 *
 * @copyright 	Keith Morrison, Infused Solutions	2001-2006
 * @author			Keith Morrison <keithm@infused-solutions.com>
 * @package 		genealogy
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
 
 /**
 * $Id$
 */

	# Ensure this file is being included by a parent file
	defined( '_RGDS_VALID' ) or die( 'Direct access to this file is not allowed.' );

/**
 * Defines a person.
 * Some of the properties of this class are other class objects.
 * The example below instantiates a new person object populated with 
 * all of the information available.  If $p_vitals_only is set to true, 
 * then the marriages, parents, children, and notes variables will not be populated.
 * Example:
 * <code>
 * $person = new Person('I100')
 * </code>
 * This class requires a valid database connection before instantiating it
 * @author			Keith Morrison <keithm@infused-solutions.com>
 * @package	genealogy
 */
class Person {
	
	/**
	* Person Constructor
	* 
	* The $p_level parameter specifies that amount of information that is populated.
	* Levels:<br />
	* 0: All data<br />
	* 1: Only vital statistics, parents, and sources<br />
	* 2: Parents only (populates only $father_indkey and $mother_indkey)<br /> 
	* 3: Vitals only - No Parents, No Sources
	* 4: Custom setting currently only used for the Ahnentafel report
	* @param string $p_id indkey
	* @param integer $p_level populate all class properties or only vital stats?
	* @param integer $p_ns_number anhnentafel number
	*/
	function Person($indkey, $level = 0, $ns_number = null) {
		$this->indkey = $indkey;
		$this->ns_number = $ns_number;
		$this->sources = array();
		
		# 0: All data
		if ($level == 0 OR $level >= 5) {
			$this->_get_name();
			$this->_get_events();
			if ($GLOBALS['options']->sort_events) $this->_sort_events();
			$this->_get_parents();
			$this->marriages = array();
			$this->children = array();
			$this->_get_marriages();
			if ($GLOBALS['options']->sort_marriages) $this->_sort_marriages();
			$this->_get_notes();
		}
		# 1: Vitals only
		elseif($level == 1) {
			$this->_get_name();
			$this->_get_events();
			$this->_get_parents();
		}
		# 2: Parents only
		elseif($level == 2) {
			$this->_get_parents();
		}
		# 3: Vitals (No Parents, No Sources)
		elseif($level == 3) {
			$this->_get_name();
			$this->_get_events(false);
		}
		# 4: Custom (used for Ahnentafel report)
		elseif($level == 4) {
			$this->_get_name();
			$this->_get_events(false);
			$this->_get_parents();
			$this->marriages = array();
			$this->_get_marriages(false);
			if ($GLOBALS['options']->sort_marriages) $this->_sort_marriages();
		}
	}
	
	function first_name() {
	  $names = explode(' ', $this->gname);
	  return $names[0]; 
	}
	
	function surname() {
	  return $this->sname;
	}
	
	function full_name() {
	  $name = trim($this->gname.' '.$this->sname);
		if (strlen($name) < 1) return "(--?--)";
		else return $name;
	}
	
	function gender() {
	  if ($this->sex == 'M') return 'Male'; 
		elseif ($this->sex == 'F') return 'Female'; 
		else return 'Unknown';
	}
	
	/**
	* Gets name information from database
	*/
	function _get_name() {
		global $db;
		$sql = 'SELECT * FROM '.TBL_INDIV.' WHERE indkey='.$db->qstr($this->indkey);
		$row = $db->GetRow($sql);
		$this->prefix = $row['prefix'];
		$this->suffix = $row['suffix'];
		$this->gname = $row['givenname'];
		$this->sname = $row['surname'];
		$this->aka = $row['aka'];
		$this->refn = $row['refn'];
		$this->notekey = $row['notekey'];
		$this->sex = $row['sex'];
	}

	/**
	* Gets events from database
	* @param boolean $p_fetch_sources
	*/
	function _get_events($p_fetch_sources = true) {
		global $db;
		$this->events = array();
		$sql =  'SELECT * FROM '.TBL_FACT.' WHERE indfamkey='.$db->qstr($this->indkey);
		$rs = $db->Execute($sql);
		while ($row = $rs->FetchRow()) {
			$event =& new event($row, $p_fetch_sources);
			if (strcasecmp($event->type, 'birth') == 0) $this->birth = $event;
			elseif (strcasecmp($event->type, 'death') == 0) $this->death = $event;
			else $this->events[] = $event;
		}
		$this->event_count = count($this->events);
	}
	
	/**
	* Sort events by date
	*/
	function _sort_events() {
		// declare internal compare function
		if (!function_exists('datecmp_evt')) {
			function datecmp_evt($arr1, $arr2) {
				return strcmp($arr1->sort_date, $arr2->sort_date);
			}		
		}
		if ($this->event_count > 0) {
			usort($this->events, 'datecmp_evt');
		}
	}

	/**
	* Gets parents from database
	*/	
	function _get_parents() {
		global $db;
		$sql  = 'SELECT spouse1, spouse2 FROM '.TBL_FAMILY.' ';
		$sql .= 'INNER JOIN '.TBL_CHILD.' ';
		$sql .= 'ON '.TBL_FAMILY.'.famkey = '.TBL_CHILD.'.famkey ';
		$sql .= 'WHERE '.TBL_CHILD.'.indkey = '.$db->qstr($this->indkey);
		if ($row = $db->GetRow($sql)) {
			$this->father_indkey = $row['spouse1'];
			$this->mother_indkey = $row['spouse2'];
		}
	}

	/**
	* Gets marriages/family units from database
	* @param boolean $fetch_sources
	*/	
	function _get_marriages($fetch_sources = true) {
		global $db;
		if ($this->sex == 'M') { 
			$p_col = 'spouse1'; 
			$s_col = 'spouse2'; 
		}
		else { 
			$p_col = 'spouse2'; 
			$s_col = 'spouse1'; 
		}
		$sql = 'SELECT * FROM '.TBL_FAMILY.' WHERE '.$p_col.'='.$db->qstr($this->indkey);
		$rs = $db->Execute($sql);
		while ($row = $rs->FetchRow()) {
			$famkey =& $row['famkey'];
			$beginstatus =& $row['beginstatus'];
			$endstatus =& $row['endstatus'];
			$notekey =& $row['notekey'];
			$spouse =& $row[$s_col];
			$marriage =& new marriage($famkey, $spouse, $beginstatus, $endstatus, $notekey, $fetch_sources);
			$this->marriages[] = $marriage;
		}
		$this->marriage_count = count($this->marriages);
	}
	
	function _sort_marriages() {
		// declare internal compare function
		if (!function_exists('datecmp_marr')) {
			function datecmp_marr($arr1, $arr2) {
				return strcmp($arr1->sort_date, $arr2->sort_date);
			}
		}
		if ($this->marriage_count > 0) {
			usort($this->marriages, 'datecmp_marr');
		}
	}

	/**
	* Gets notes from database
	*/	
	function _get_notes() {
		global $db;
		$query = 'SELECT text FROM '.TBL_NOTE.' WHERE notekey="'.$this->notekey.'"';
		$this->notes = nl2br($db->GetOne($query));
	}
}

/**
 * Defines an event
 * @package	genealogy
 */
class Event {
	/**
	* Event Type
	* @var string
	*/
	var $type;

	/**
	*	Event Date
	* This date string has been formated according to the
	* date format chosen in the administration module
	* @var string
	*/
	var $date;
	
	/**
	* Raw date data
	* This is an array information returned from the date
	* parser. Indexes are date_mod, date1, and date2
	* @var array
	*/
	var $raw;

	/**
	* Sort Date
	* @var string
	*/
	var $sort_date;
	
	/**
	* Event Place
	* @var string	
	*/
	var $place;
	
	/**
	* Event Comment
	* @var string
	*/
	var $comment;
	
	/**
	* Event Factkey
	* @var string
	*/
	var $factkey;

	/**
	* Array of Sources
	* @var array
	*/
	var $sources;

	/**
	* Count of Sources
	* @var integer
	*/
	var $source_count;
	
	/**
	* Array of notes
	* @var array
	*/
	var $notes;
	
	/**
	* Event Constructor
	* @param string $p_type The type of event
	* @param string $p_date When the event occured
	* @param string $p_place Where the event occured
	* @param string $p_factkey 
	*/
	function Event($event_data, $p_fetch_sources = true) {
		$this->type = ucwords(strtolower($event_data['type']));
		$this->place = $event_data['place'];
		$this->comment = $event_data['comment'];
		$this->factkey = $event_data['factkey'];
		$this->sort_date = $event_data['date1'];
		$this->notekey = $event_data['notekey'];
		$dp =& new DateParser();
		$this->date = $dp->FormatDateStr($event_data);
		$this->raw['mod'] = $event_data['date_mod'];
		$this->raw['date1'] = $event_data['date1'];
		$this->raw['date2'] = $event_data['date2'];
		if ($p_fetch_sources === true) {
		  $this->_get_sources();
		  $this->_get_notes();
		}
	}
	
	/** 
	* Gets sources
	* @access private
	*/
	function _get_sources() {
		global $db;
		$sources = array();
		$sql  = 'SELECT '.TBL_CITATION.'.source, '.TBL_SOURCE.'.text '; 
		$sql .= 'FROM '.TBL_CITATION.' INNER JOIN '.TBL_SOURCE.' ';
		$sql .= 'ON '.TBL_CITATION.'.srckey='.TBL_SOURCE.'.srckey ';
		$sql .= 'WHERE '.TBL_CITATION.'.factkey='.$db->qstr($this->factkey);
		$rs = $db->Execute($sql);
		while ($row = $rs->FetchRow()) {
			$srccitation = $row['source'];
			$msrc = $row['text'];
			$source = $msrc."\n".$srccitation;
			$sources[] = $source;
		}
		$this->sources = $sources;
		$this->source_count = count($this->sources);
	}
	
	function _get_notes() {
		global $db;
		$query = 'SELECT text FROM '.TBL_NOTE.' WHERE notekey="'.$this->notekey.'"';
		$this->notes = nl2br($db->GetOne($query));
	}
	
}


/**
 * Defines a marriage or family unit
 * @author			Keith Morrison <keithm@infused-solutions.com>
 * @package	genealogy
 */
class Marriage {
	
	/**
	* Famkey.
	* This is the key used to look up the family from the database.
	* @var string
	*/
	var $famkey;

	/**
	* Marriage/Union/Family type.
	* Some values are, but are not limited to:
	* "married", "unmarried", "friends", "partners", "single"
	* @var string
	*/
	var $type;
	
	/**
	* Indkey of spouse or partner
	* @var string
	*/
	var $spouse;

	/** 
	* Date of marriage
	* @var string
	*/
	var $date;
	
	/**
	* Sort date
	* @var string
	*/
	var $sort_date;
	
	/**
	* Place of marriage
	* @var string
	*/
	var $place;
	
	/** 
	* Marriage begin status
	* 
	* ie. married, single, partners, etc....
	* @var string
	*/
	var $beginstatus;
	
	/**
	* Begin status event object
	*/
	var $begin_event;
	
	/**
	* Begin status factkey
	* @var string
	*/
	var $beginstatus_factkey;
	
	/**
	* Marriage end status
	* ie. divorced, annulled, etc.
	* @var string
	*/
	var $endstatus;
	
	/**
	* End status event object
	*/
	var $end_event;
	
	/**
	* End status factkey
	* @var string
	*/
	var $endstatus_factkey;
	
	/**
	* End date
	* @var string
	*/
	var $enddate;
	
	/**
	* End place
	* @var string
	*/
	var $endplace;
	
	/**
	* An array of Event objects
	* @see Event
	* @var array
	*/
	var $events;
	
	/**
	* Number of events contained in $events
	* @var integer
	*/
	var $event_count;
	
	/**
	* Marriage notes
	* @var string
	*/
	var $notes;
	
	/**
	* Array of child indkeys
	* @var array
	*/
	var $children;
	
	/**
	* Number/Count of children
	* @var integer
	*/
	var $child_count;

	/** 
	* Array of begin status sources
	* @var array
	*/
	var $sources;

	/**
	* Number/Count of begin status sources
	* @var integer
	*/
	var $source_count;
	
	/**
	* Array of end status sources
	* @var array
	*/
	var $end_sources;

	/** Number/Count of end status sources
	* @var integer
	*/
	var $end_sources_count;
	
	# private properties
	
	/**
	* @access private
	* @var string
	*/
	var $notekey;
	
	/**
	* Marriage Constructor
	* @param string $p__famkey
	* @param string $p_spouse
	* @param string $p_beginstatus
	* @param string $p_endstatus
	* @param string $p_notekey
	* @param boolean $fetch_sources
	*/
	function Marriage($p_famkey, $p_spouse, $p_beginstatus, $p_endstatus, $p_notekey, $fetch_sources = true) {
		$this->famkey = $p_famkey;
		$this->spouse = $p_spouse;

		# work around wording discrepency
		if (strcasecmp($p_beginstatus, 'married') == 0) $this->beginstatus = 'Marriage';
		else $this->beginstatus = (!empty($p_beginstatus)) ? $p_beginstatus : 'Relationship';

		$this->endstatus = $p_endstatus;
		$this->notekey = $p_notekey;
		$this->children = array();
		$this->_get_children();
		if ($GLOBALS['options']->sort_children) $this->_sort_children();
		$this->_get_notes();
		$this->_get_events();
	}
		
	/**
	* Get Children
	*/	
	function _get_children() {	
		global $db;
		$sql = 'SELECT indkey FROM '.TBL_CHILD.' WHERE famkey='.$db->qstr($this->famkey);
		$this->children = $db->GetCol($sql);
		$this->child_count = count($this->children);
	}
	
	/** 
	* Sort children by date
	*/
	function _sort_children() {
		# declare internal compare function
		if (!function_exists('datecmp_chi')) {	
			function datecmp_chi($arr1, $arr2) {
				return strcmp($arr1->birth->sort_date, $arr2->birth->sort_date);
			}
		}
		if ($this->child_count > 0) {
			$tmp_arr = array();
			foreach ($this->children as $indkey) {
				$c =& new Person($indkey, 3);
				$tmp_arr[] = $c;
			}
			usort($tmp_arr, 'datecmp_chi');
			foreach ($tmp_arr as $child) {
				$children[] = $child->indkey;
			}
			$this->children = $children;
		}
	}
	
	/**
	* Get Notes
	*/
	function _get_notes() {
		global $db;
		$query = 'SELECT text FROM '.TBL_NOTE.' WHERE notekey="'.$this->notekey.'"';
		$this->notes = $db->GetAll($query);
	}
	
	/**
	* Gets events from database
	*/
	function _get_events($p_fetch_sources = true) {
		global $db;
		$this->events = array();
		$sql =  'SELECT * FROM '.TBL_FACT.' WHERE indfamkey='.$db->qstr($this->famkey);
		$rs = $db->Execute($sql);
		while ($row = $rs->FetchRow()) {
			$event =& new event($row, $p_fetch_sources);
			if ($event->type == $this->beginstatus) {
				$this->beginstatus_factkey = $row['factkey'];
				$dp = new DateParser();
				$this->date = $dp->FormatDateStr($row);
				$this->sort_date = $row['date1'];
				$this->place = $row['place'];
				$this->begin_event = $event;
			} 
			elseif ($event->type == $this->endstatus) {
				$this->endstatus_factkey = $row['factkey'];
				$dp =& new DateParser();
				$this->enddate = $dp->FormatDateStr($row);
				$this->endplace = $row['place'];
				$this->end_event = $event;
			}
			else array_push($this->events, $event);
		}
		$this->event_count = count($this->events);
	}
}
?>