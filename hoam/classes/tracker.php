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

class HOAM_tracker extends HOAM_db {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function add ($value, $column = 'text') {
		global $_HOAM_language;
		
		$id = md5 (uniqid (mt_rand (), TRUE));
		
		$query = "INSERT INTO $this->table (id, datecreated, $column)
					VALUES ('$id', now(), '$value')";

		// Execute the query
		$result = mysqli_query ($this -> connection, $query);
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
			$this -> changeDateCreated ($id);
			// Return the $id sequence we were given so that it can be used on
			// further queries.
			return $id;
		}
		// If the add failed, then we should have already died.  But, just
		// to cover ourselves, let's return a failure.
		return FALSE;
	}
	
	function changeCount ($id, $column) {
		return ($this -> changeInt ($id, 'count', $column));
	}
	
	function changeDateCreated ($id, $value = NULL) {
		if ($value == NULL) {
			$value = date ("Y-m-d H:i:s");
		}
		return ($this -> changeString ($id, 'datecreated', $value));
	}
	
	function changeDateModified ($id, $value = NULL) {
		if ($value == NULL) {
			$value = date ("Y-m-d H:i:s");
		}
		return ($this -> changeString ($id, 'datemodified', $value));
	}
	
	function changeText ($id, $value, $column = 'text') {
		return ($this -> changeString ($id, $column, $value));
	}
	
	function findText ($text, $where = NULL, $column = 'text') {
		// Formulate the SQL query
		$query = "SELECT id FROM $this->table
					WHERE $column LIKE '$text'";
		if ($where) {
			$query .= $where;
		}
		// Execute the query
		return ($this -> itemQuery ($query));
	}
	
	function listAll ($id = null, $sort = 'datecreated DESC') {
		// If we're given an $id number, then just return the info for that one
		// item.
		if (isset ($id)) {
			if ($this -> idExists ($id)) {
				// Formulate the SQL query
				$query = "SELECT * FROM $this->table
							WHERE id = '$id'";
			} else {
				return false;
			}
		} else {
			// Formulate the SQL query
			$query = "SELECT * FROM $this->tablename
						ORDER BY $sort";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}
	
	function listRange ($newest, $oldest, $number = 0, $offset = 0) {
		// Return details about the items created between the given dates.
		// Formulate the SQL query
		$query = "SELECT * FROM $this->table
					WHERE (datecreated BETWEEN '$newest' AND '$oldest')
					ORDER BY datecreated DESC";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}
	
	function listRecent ($number, $offset = 0) {
		// Return details for all of the 'x' recent
		// Formulate the SQL query
		$query = "SELECT * FROM $this->table
					ORDER BY datecreated DESC
					LIMIT $offset, $number";
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}
	
	function returnCount ($id) {
		return ($this -> returnItem ($id, 'count'));
	}

	function returnDateCreated ($id) {
		return ($this -> returnItem ($id, 'datecreated'));
	}

	function returnDateModified ($id) {
		return ($this -> returnItem ($id, 'datemodified'));
	}

	function returnText ($id, $column = 'text') {
		return ($this -> returnItem ($id, $column));
	}

}

?>
