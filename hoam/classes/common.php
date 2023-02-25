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

class HOAM_common extends HOAM_db {
	// We rely upon the calling code to clean all of the data and format we're
	// given. This class does not perform any cleansing of data!
	
	function add ($user_id, $id = NULL) {
		global $_HOAM_language, $_HOAM_setting;
		// Request a new item to be created
		// If this is called by a routine running automatically, record the
		// system account as the user.
		if (empty ($user_id)) {
			$user_id = $_HOAM_setting ['user']['ids']['system'];
		}

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
			// Return the $id sequence we were given so that it can be used on
			// further queries.
			return $id;
		}
		// If the add failed, then we should have already died.  But, just
		// to cover ourselves, let's return a failure.
		return FALSE;
	}
	
	function bitCompare ($bits, $bit_column, $id = NULL, $id_column = NULL) {
		// This function performs an AND bit comparison on a value in the
		// database. I would prefer to simply know whether ONLY the bits
		// compared are set, however it doesn't appear that MySQL supports that
		// - instead MySQL tells you if ANY of the bits compared are set. So,
		// we need to be very careful interpreting these results.
		//
		// This will work fine and only show if a single bit is set when
		// multipliers of 2 are used, otherwise it acts as described above.
		// 
		$query = "SELECT $bit_column & $bits FROM $this->table";
		// Adding a $id_column lets us specify a group_id or other value
		// to further narrow down the results.
		if ($id !== NULL) {
			$query .= " WHERE $id_column = '$id'";
		}
		return ($this -> listSimpleQuery ($query));
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

	function changeFlags ($id, $value, $column = "flags") {
		return ($this -> changeInt ($id, $column, $value));
	}

	function changeUserId ($id, $value) {
		return ($this -> changeString ($id, 'user_id', $value));
	}
	
	function clearBit ($id, $value, $column = "flags") {
		// IMHO, mysql handling of bit comparisons sucks. It may work fine in
		// some circumstances, but for what I want it's broken. So, we're just
		// going to do it in PHP.
		$bits = $this -> itemQuery ("SELECT $column FROM $this->table WHERE id='$id'");
		if ((int) $bits & (int) $value) {
			return ($this -> changeInt ($id, $column, (int) $bits - (int) $value));
		} else {
			// The flag was not set, nothing to change
			return (-1);
		}
	}

	function isBitSet ($id, $value, $column = "flags") {
		// IMHO, mysql handling of bit comparisons sucks. It may work fine in
		// some circumstances, but for what I want it's broken. So, we're just
		// going to do it in PHP.
		$bits = $this -> itemQuery ("SELECT $column FROM $this->table WHERE id='$id'");
		if ((int) $bits & (int) $value) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function listAuthor ($user_id, $number = 0, $offset = 0) {
		// Return details for all of the ids by a specific author
		// Formulate the SQL query
		$query = "SELECT * FROM $this->table
					WHERE user_id = '$user_id'
					ORDER BY datecreated DESC";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}
	
	function listAuthors ($number = 0, $offset = 0) {
		// Return an array containing a list of all id authors
		// Formulate the SQL query
		$query = "SELECT DISTINCT $this->table.user_id, users.accountname FROM $this->table, users
					ORDER BY users.accountname DESC";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}

	function listBitNotSet ($value, $column = "flags", $number = 0, $offset = 0, $sort = 'datecreated DESC') {
		// This function performs an AND bit comparison on a value in the
		// database. I would prefer to simply know whether ONLY the bits
		// compared are set, however it doesn't appear that MySQL supports that
		// - instead MySQL tells you if ANY of the bits compared are set. So,
		// we need to be very careful interpreting these results.
		//
		// This will work fine and only show if a single bit is set when
		// multipliers of 2 are used, otherwise it acts as described above.
		// 
		$query = "SELECT id, $column FROM $this->table ORDER BY $sort";
		// limiting the results here causes a problem with the final result;
		// ie, it causes the db to only return results inside the limits, NOT
		// returning the number from the resulting array.
		// If a proper method of evaluating bits in mysql can be found, this
		// problem goes away.
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		$list = $this -> listQuery ($query);
		if (is_array ($list)) {
			$array = array ();
			foreach ($list as $item) {
				if ($item[$column] & $value) {
				} else {
					array_push ($array, $item["id"]);
				}
			}
			return ($array);
		}
		return FALSE;
	}

	function listBitSet ($value, $column = "flags", $number = 0, $offset = 0, $sort = 'datecreated DESC') {
		// This function performs an AND bit comparison on a value in the
		// database. I would prefer to simply know whether ONLY the bits
		// compared are set, however it doesn't appear that MySQL supports that
		// - instead MySQL tells you if ANY of the bits compared are set. So,
		// we need to be very careful interpreting these results.
		//
		// This will work fine and only show if a single bit is set when
		// multipliers of 2 are used, otherwise it acts as described above.
		// 
		$query = "SELECT id, $column FROM $this->table ORDER BY $sort";
		// limiting the results here causes a problem with the final result;
		// ie, it causes the db to only return results inside the limits, NOT
		// returning the number from the resulting array.
		// If a proper method of evaluating bits in mysql can be found, this
		// problem goes away.
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		$list = $this -> listQuery ($query);
		if (is_array ($list)) {
			$array = array ();
			foreach ($list as $item) {
				if ($item[$column] & $value) {
					array_push ($array, $item["id"]);
				}
			}
			return ($array);
		}
		return FALSE;
	}

	function listRange ($oldest, $newest, $number = 0, $offset = 0) {
		// Return details about all of the items created between the given
		// dates
		$query = "SELECT id FROM $this->table
					WHERE (datecreated BETWEEN '$oldest' AND '$newest')
					ORDER BY datecreated DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}
	
	function listRecent ($number = 0, $offset = 0) {
		// Return details for all of the 'x' recent items
		// Formulate the SQL query
		$query = "SELECT * FROM $this->table
					ORDER BY datecreated DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}
	
	function listRecentModified ($number = 0, $offset = 0) {
		// Return details for all of the 'x' recent items
		// Formulate the SQL query
		$query = "SELECT * FROM $this->table
					ORDER BY datemodified DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}

	function listYears ($number = 0, $offset = 0) {
		// Return an array with all of the distinct years that items were
		// created in
		$query = "SELECT DISTINCT YEAR (datecreated) as years FROM $this->table
					 ORDER BY years DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}
	
	function returnCountAll () {
		return ($this -> returnCountQuery ());
	}
	
	function returnCountAuthor ($value) {
		return ($this -> returnCountQuery ("WHERE user_id = '$value'"));
	}
	
	function returnCountRange ($newest, $oldest) {
		return ($this -> returnCountQuery ("WHERE (datecreated BETWEEN '$newest' AND '$oldest')"));
	}
	
	function returnDateCreated ($id) {
		return ($this -> returnItem ($id, 'datecreated'));
	}
	
	function returnDateModified ($id) {
		return ($this -> returnItem ($id, 'datemodified'));
	}

	function returnFlags ($id, $column = 'flags') {
		return ($this -> returnItem ($id, $column));
	}

	function returnUserId ($id) {
		return ($this -> returnItem ($id, 'user_id'));
	}
	
	function setBit ($id, $value, $column = "flags") {
		// IMHO, mysql handling of bit comparisons sucks. It may work fine in
		// some circumstances, but for what I want it's broken. So, we're just
		// going to do it in PHP.
		$bits = $this -> itemQuery ("SELECT $column FROM $this->table WHERE id='$id'");
		if ((int) $bits & (int) $value) {
			// The flag is already set
			return (-1);
		} else {
			return ($this -> changeInt ($id, $column, (int) $bits + (int) $value));
		}
	}

}

?>
