<?php 
/**
 * Language Functions
 * @copyright 	Keith Morrison, Infused Solutions	2001-2004
 * @author			Keith Morrison <keithm@infused-solutions.com>
 * @package 		language
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
	
	/**
	* Gets Birth Sentence
	* 
	* Given a Person object, this function returns a sentence describing the birth of 
	* the individual in the currently selected language.  This function has the capability 
	* to return a different sentence structure based on the individual's gender.
	* @access public
	* @param Person $p Individual 
	* @return string
	*/
	function get_birth_sentence($p) {
		$dp = new DateParser();
		$s 					= '';
		$date 			= $p->birth->date;
		$place 			= $p->birth->place;
		$rdate_mod 	= $p->birth->raw['mod'];
		$rdate1 		= $p->birth->raw['date1'];
		$rdate2 		= $p->birth->raw['date2']; 
		
		# populate keyword array
		keyword_push($p->name);
		if (!empty($p->birth->place)) { keyword_push($p->birth->place); }
		# for males
		if ($p->sex == 'M') { 
			if ($rdate_mod == '00') {
				if ($date AND $place) $s = sprintf(gtc("He was born on %s in %s."), $date, $place);
				elseif ($date) 				$s = sprintf(gtc("He was born on %s."), $date);
				elseif ($place) 			$s = sprintf(gtc("He was born in %s."), $place);
			}
			else {
				if ($date AND $place) $s = sprintf(gtc("He was born %s in %s."), $date, $place);
				elseif ($date) 				$s = sprintf(gtc("He was born %s."), $date);
				elseif ($place) 			$s = sprintf(gtc("He was born in %s."), $place);
			}
		}
		# for females
		if ($p->sex == 'F') { 
			if ($rdate_mod == '00') {
				if ($date AND $place) $s = sprintf(gtc("She was born on %s in %s."), $date, $place);
				elseif ($date) 				$s = sprintf(gtc("She was born on %s."), $date);
				elseif ($place) 			$s = sprintf(gtc("She was born in %s."), $place);
			}
			else {
				if ($date AND $place) $s = sprintf(gtc("She was born %s in %s."), $date, $place);
				elseif ($date) 				$s = sprintf(gtc("She was born %s."), $date);
				elseif ($place) 			$s = sprintf(gtc("She was born in %s."), $place);
			}
		}
		return $s.' ';
	}
	
	/**
	* Gets Death Sentence
	* 
	* Given a Person object, this function returns a sentence describing the death of 
	* the individual in the currently selected language.  This function has the capability 
	* to return a different sentence structure based on the individual's gender.
	* @access public
	* @param Person $p Individual
	* @return string
	*/
	function get_death_sentence($p) {
		$s = null;
		# populate keyword array
		if (!empty($p->death->place)) { keyword_push($p->death->place); }
		
		# for males
		if ($p->sex == 'M') { 
			if ($p->death->date and $p->death->place) {
				$s = sprintf(gtc("He died on %s in %s."), $p->death->date, $p->death->place).' ';		
			}
			elseif ($p->death->date) {
				$s = sprintf(gtc("He died on %s."), $p->death->date).' ';
			}
			elseif ($p->death->place) {
				$s = sprintf(gtc("He died in %s."), $p->death->place).' ';
			}
		}
		# for females
		if ($p->sex == 'F') { 
			if ($p->death->date and $p->death->place) {
				$s = sprintf(gtc("She died on %s in %s."), $p->death->date, $p->death->place).' ';		
			}
			elseif ($p->death->date) {
				$s = sprintf(gtc("She died on %s."), $p->death->date).' ';
			}
			elseif ($p->death->place) {
				$s = sprintf(gtc("She died in %s."), $p->death->place).' ';
			}
		}
		return $s;
	}
	
	/**
	* Gets Parent Sentence
	* 
	* Given a Person object, this function returns a sentence describing the parents of 
	* the individual in the currently selected language.  This function has the capability 
	* to return a different sentence structure based on the individual's gender.
	* @access public
	* @param Person $p Individual
	* @param Person $p_father Father
	* @param Person $p_mother Mother
	* @return string
	*/
	function get_parents_sentence($p, $p_father, $p_mother) {
		# populate keyword array
		keyword_push($p_father->name);
		keyword_push($p_mother->name);
		
		$mother_link = '<a class="secondary" href="'.$_SERVER['PHP_SELF'].'?option=family&amp;id='.$p_mother->indkey.'">'.$p_mother->name.'</a>';
		$father_link = '<a class="secondary" href="'.$_SERVER['PHP_SELF'].'?option=family&amp;id='.$p_father->indkey.'">'.$p_father->name.'</a>';
		if ($p->father_indkey || $p->mother_indkey) {
			if ($p->sex == 'M') { 
				# structure for son of father and mother
				if ($p->father_indkey and $p->mother_indkey) {
					return sprintf(gtc(", son of %s and %s."), $father_link, $mother_link).' ';
				}
				# structure for son of father
				elseif ($p->father_indkey) {
					return sprintf(gtc(", son of %s."), $father_link).' ';
				}
				# structure for son of mother
				elseif ($p->mother_indkey) {
					return sprintf(gtc(", son of %s."), $mother_link).' ';
				}
			}
			if ($p->sex == 'F') { 
				# structure for daughter of father and mother
				if ($p->father_indkey and $p->mother_indkey) {
					return sprintf(gtc(", daughter of %s and %s."), $father_link, $mother_link).' ';
				}
				# structure for daugher of father
				elseif ($p->father_indkey) {
					return sprintf(gtc(", daughter of %s."), $father_link).' ';
				}
				# structure for daugher of mother
				elseif ($p->mother_indkey) {
					return sprintf(gtc(", daughter of %s."), $mother_link).' ';
				}
			}
		}
	}
	
	/**
	* Gets Marriage Sentence
	* @access public
	* @param Person $p Individual
	* @return string
	*/
	function get_marriage_sentences($p) {
		global $g_family_page;
		$s = ' ';
		for ($i = 0; $i < $p->marriage_count; $i++) {
			$marriage =& $p->marriages[$i];
			if ($marriage->spouse) {
				$spouse = new Person($marriage->spouse, 3);
				$spouse_link = '<a class="secondary" href="'.$_SERVER['PHP_SELF'].'?option=family&amp;id='.$spouse->indkey.'">'.$spouse->name.'</a>';
				# populate keyword array
				keyword_push($spouse->name);
				
				# fix some problems
				if (!$spouse->name) { $spouse->name =  gtc("Unknown"); }
				if ($spouse->name == 'Unknown Unknown') { $spouse->name = gtc("Unknown"); }
				
				if ($marriage->beginstatus == 'Marriage') {
					if ($p->sex == 'M') {
						# structure for male married with date and place
						if ($marriage->date and $marriage->place) {
							$s .= sprintf(gtc("male %s married %s on %s in %s."), $p->fname, $spouse_link, $marriage->date, $marriage->place);
						}
						# structure for male married with date only
						elseif ($marriage->date) {
							$s .= sprintf(gtc("male %s married %s on %s."), $p->fname, $spouse_link, $marriage->date);
						}
						# structure for male married with place only
						elseif ($marriage->place) {
							$s .= sprintf(gtc("male %s married %s in %s."), $p->fname, $spouse_link, $marriage->place);
						}
						# structure for male married with no date or place
						else {
							$s .= sprintf(gtc("male %s married %s."), $p->fname, $spouse_link);
						}
						if ($marriage->endstatus) { 
							$s .= ' ';
							$s .= sprintf(gtc("This marriage ended in %s."), strtolower(gtc($marriage->endstatus))); 
						}
					}
					elseif ($p->sex == 'F') {
						# structure for female married with date and place
						if ($marriage->date and $marriage->place) {
							$s .= sprintf(gtc("female %s married %s on %s in %s."), $p->fname, $spouse_link, $marriage->date, $marriage->place);
						}
						# structure for female married with date only
						elseif ($marriage->date) {
							$s .= sprintf(gtc("female %s married %s on %s."), $p->fname, $spouse_link, $marriage->date);
						}
						# structure for female married with place only
						elseif ($marriage->place) {
							$s .= sprintf(gtc("female %s married %s in %s."), $p->fname, $spouse_link, $marriage->place);
						}
						# structure for female married with no date or place
						else {
							$s .= sprintf(gtc("female %s married %s."), $p->fname, $spouse_link);
						}
						if ($marriage->endstatus) { 
							$s .= ' ';
							$s .= sprintf(gtc("This marriage ended in %s."), strtolower(gtc($marriage->endstatus))); 
						}
					}
				}
				else {
					if ($p->sex == 'M') {
						# structure for male relationship with date and place
						if ($marriage->date and $marriage->place) {
							$s .= sprintf(gtc("male %s had a relationship with %s on %s in %s."), $p->fname, $spouse_link, $marriage->date, $marriage->place);
						}
						# structure for male relationship with date only
						elseif ($marriage->date) {
							$s .= sprintf(gtc("male %s had a relationship with %s on %s."), $p->fname, $spouse_link, $marriage->date);
						}
						# structure for male relationship with place only
						elseif ($marriage->place) {
							$s .= sprintf(gtc("male %s had a relationship with %s in %s."), $p->fname, $spouse_link, $marriage->place);
						}
						# structure for male relationship with no date or place
						else {
							$s .= sprintf(gtc("male %s had a relationship with %s."), $p->fname, $spouse_link);
						}
						if ($marriage->endstatus) { 
							$s .= ' ';
							$s .= sprintf(gtc("This marriage ended in %s."), gtc($marriage->endstatus)); 
						}
					}
					elseif ($p->sex == 'F') {
						# structure for female relationship with date and place
						if ($marriage->date and $marriage->place) {
							$s .= sprintf(gtc("female %s had a relationship with %s on %s in %s."), $p->fname, $spouse_link, $marriage->date, $marriage->place);
						}
						# structure for female relationship with date only
						elseif ($marriage->date) {
							$s .= sprintf(gtc("female %s had a relationship with %s on %s."), $p->fname, $spouse_link, $marriage->date);
						}
						# structure for female relationship with place only
						elseif ($marriage->place) {
							$s .= sprintf(gtc("female %s had a relationship with %s in %s."), $p->fname, $spouse_link, $marriage->place);
						}
						# structure for female relationship with no date or place
						else {
							$s .= sprintf(gtc("female %s had a relationship with %s."), $p->fname, $spouse_link);
						}
						if ($marriage->endstatus) { 
							$s .= ' ';
							$s .= sprintf(gtc("This relationship ended in %s."), gtc($marriage->endstatus));
						}
					}			
				}
			}
			if ($s) { $s .= ' '; }
		}
		return $s;
	}
	
	/**
	* Gets 'children of' sentence
	* @access public
	* @param Person $p Individual
	* @param Person $ps Spouse
	*/
	function get_children_of_sentence($p, $ps) {
		$s = '';
		if ($p->name and $ps->name) {
			$s .= sprintf(gtc("Children of %s and %s"), $p->name, $ps->name);
		}
		else {
			$s .= sprintf(gtc("Children of %s"), $p->name);
		}
		return $s;
	}
?>