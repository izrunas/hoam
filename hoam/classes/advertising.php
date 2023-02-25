<?php
/*
 * This file is part of HOAM, copyright (C) 2002-2020 Robert Butler
 *
 * HOAM is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option)
 * any later version.
 *
 * HOAM is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with HOAM; if not, see http://www.gnu.org/licenses or write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301
 *
 * Questions specific to HOAM should be directed to ARP Realty. Please see
 * the HOAM web site at http://hoam.arprs.com/
 *
 * Some portions of HOAM incorporate ideas and/or code from other sources, and
 * those portions are explicitly mentioned and attributed in the relevant
 * section of HOAM source code. Questions about that code should be directed to
 * the original authors.
 *
 */

// NOTE! Unlike the group* classes, this does handle un/serializing of data as
// necessary.

class HOAM_advertising extends HOAM_db {
	
	function add ($user_id, $id = NULL) {
		global $_HOAM_language, $_HOAM_setting;
		
		// By default we want to generate a random MD5 for the id.
		// If we're passed an ID to use, however, use that.
		if ($id === NULL) {
			$id = md5 (uniqid (mt_rand (), TRUE));
		}
		
		$query = "INSERT INTO $this->table (id, datecreated, user_id)
					VALUES ('$id', now(), '$user_id')";
		
		// Execute the query
		$result = mysqli_query ($this -> connection, $query);
		// Always check that $result is not an error
		if (!$result) {
			echo $_HOAM_language ['errors']['hoam']['database']['problem'];
			if (HOAM_DEBUG) {
				printf ($_HOAM_language ['errors']['hoam']['database']['error'], mysqli_error ($this -> connection));
				printf ($_HOAM_language ['errors']['hoam']['database']['query'], $query);
				$backtrace = debug_backtrace();
				printf ('Backtrace Functions :: %s &rArr; %s &rArr; %s<br />', $backtrace[2]['function'], $backtrace[1]['function'], $backtrace[0]['function']);
				printf ('Backtrace File :: %s<br />', $backtrace[0]['file']);
				printf ('Backtrace Line :: %d<br />', $backtrace[0]['line']);
			} else {
				echo $_HOAM_language ['errors']['hoam']['debugging'];
			}
			echo $_HOAM_language ['errors']['hoam']['contact-support'];
			exit ();
		} else {
			// Return the $id sequence we were given so that it can be used on further queries.
			return $id;
		}
		// If the add failed, then we should have already died.  But, just
		// to cover ourselves, let's return a failure.
		return FALSE;
	}

	function changeDatePostEnd ($id, $value) {
		return ($this -> changeString ($id, 'datepostend', $value));
	}
	
	function changeDatePostStart ($id, $value) {
		return ($this -> changeString ($id, 'datepoststart', $value));
	}
	
	function changeDescription ($id, $value) {
		return ($this -> changeString ($id, 'description', $value));
	}

	function changeFlags ($id, $value, $column = "flags") {
		return ($this -> changeInt ($id, $column, $value));
	}

	function changeURL ($id, $value) {
		return ($this -> changeString ($id, 'url', $value));
	}

	function ListCurrentForPosition ($section, $number = 0, $offset = 0) {
		global $_HOAM_country;
		// Return an array containing a list of all attachments
		// that have the specified owner_id as one of the owners.
		// Formulate the SQL query
		$query = "SELECT id FROM $this->table ";
		$query .= "WHERE flags & $section AND ";
		$query .= "datepoststart <= '" . date ($_HOAM_country ['date']['format_mysql']) . "' ";
		$query .= "AND datepostend >= '" . date ($_HOAM_country ['date']['format_mysql']) . "' "; 
		$query .= "ORDER BY datecreated ASC";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listQuery ($query));
	}

	function recordClick ($id) {
		global $_HOAM_log;
		$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['advertisement']['click'], $this -> returnURL ($id)),
							HOAM_MESSAGE_INFORMATIONAL,
							array ('owner_id' => $id,
							'flags' => $_HOAM_setting ['log']['flags']['advertisement']));
		$query = "UPDATE $this->table SET clicks = clicks + 1 WHERE id = '$id'";
		return ($this -> itemQuery ($query));
	}
	
	function recordImpression ($id) {
		$query = "UPDATE $this->table SET impressions = impressions + 1 WHERE id = '$id'";
		return ($this -> itemQuery ($query));
	}
	
	function returnClicks ($id) {
		return ($this -> returnItem ($id, 'clicks'));
	}
	
	function returnDatePostEnd ($id) {
		return ($this -> returnItem ($id, 'datepostend'));
	}
	
	function returnDatePostStart ($id) {
		return ($this -> returnItem ($id, 'datepoststart'));
	}
	
	function returnDescription ($id) {
		return ($this -> returnItem ($id, 'description'));
	}

	function returnFlags ($id, $column = 'flags') {
		return ($this -> returnItem ($id, $column));
	}

	function returnImpressions ($id) {
		return ($this -> returnItem ($id, 'impressions'));
	}
	
	function returnURL ($id) {
		return ($this -> returnItem ($id, 'url'));
	}
	
}

?>
