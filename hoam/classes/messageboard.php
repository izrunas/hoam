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
 * Questions specific to HOAM should be directed to ARP Realty. Please see the
 * HOAM web site at http://hoam.arprs.com/
 *
 * Some portions of HOAM incorporate ideas and/or code from other sources, and
 * those portions are explicitly mentioned and attributed in the relevant
 * section of HOAM source code. Questions about that code should be directed to
 * the original authors.
 *
 */

class HOAM_messageboard extends HOAM_db {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!

	function add ($user_id, $id = NULL) {
		global $_HOAM_setting;
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
	
	function changeArticle ($id, $item) {
		return ($this -> changeString ($id, 'article', $item));
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

	function changeReplies ($id, $item) {
		return ($this -> changeInt ($id, 'replies', $item));
	}

	function changeRootId ($id, $item) {
		return ($this -> changeString ($id, 'root_id', $item));
	}
	
	function changeUserId ($id, $item) {
		return ($this -> changeString ($id, 'user_id', $item));
	}
	
	function changeSubject ($id, $item) {
		return ($this -> changeString ($id, 'subject', $item));
	}

	function changeTreeId ($id, $item) {
		return ($this -> changeString ($id, 'tree_id', $item));
	}

	function clearBit ($id, $value, $column = "flags") {
		// IMHO, mysql handling of bit comparisons sucks. It may work fine in
		// some circumstances, but for what I want it's broken. So, we're just
		// going to do it in PHP.
		$bits = $this -> itemQuery ("SELECT $column FROM $this->table WHERE id='$id'");
		if ((int) $bits & (int) $value) {
			return ($this -> changeInt ($id, $column, $bits - $value));
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

	function listAuthor ($user_id, $root_id = NULL) {
		if (!isset ($root_id)) {
			$query = "SELECT * FROM $this->table
					WHERE user_id = '$user_id'
					ORDER BY datecreated DESC"; 
		} else {
			$query = "SELECT * FROM $this->table
					WHERE user_id = '$user_id', root_id = '$root_id'
					ORDER BY datecreated DESC"; 
		}
		return ($this -> listQuery ($query));
	}
	
	function listRange ($newest, $oldest, $root_id = NULL) {
		if (!isset ($root_id)) {
			$query = "SELECT * FROM $this->messageboard_table
					WHERE (datecreated BETWEEN '$newest' AND '$oldest')
					ORDER BY datecreated DESC"; 
		} else {
			$query = "SELECT * FROM $this->messageboard_table
					WHERE (datecreated BETWEEN '$newest' AND '$oldest'),
					root_id = 'root_id'
					ORDER BY datecreated DESC"; 
		}
		return ($this -> listQuery ($query));
	}
	
	function listRecent ($number, $tree_id = NULL)	{
		if (!isset ($tree_id)) {
			$query = "SELECT * FROM $this->table
					ORDER BY datecreated DESC
					LIMIT $number";
		} else {
			$query = "SELECT * FROM $this->table
					WHERE tree_id = '$tree_id'
					ORDER BY datecreated DESC
					LIMIT $number";
		}
		return ($this -> listQuery ($query));
	}
	
	function listRootId ($root_id, $number = 0, $offset = 0) {
		global $_HOAM_setting;
		
		$query = "SELECT * FROM $this->table
					WHERE root_id = '$root_id'
					ORDER BY (flags & " . $_HOAM_setting ['messageboard']['flags']['display_top'] . ") = 0, datecreated DESC, subject ASC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}
	
	function returnArticle ($id) {
		return ($this -> returnItem ($id, 'article'));
	}

	function returnCountAuthor ($value) {
		return ($this -> returnCountQuery ("WHERE user_id = '$value'"));
	}

	function returnCountReplies ($id) {
		return ($this -> returnCountQuery ("WHERE root_id = '$value'"));
	}

	function returnCountTopic ($value) {
		return ($this -> returnCountQuery ("WHERE root_id = '$value'"));
	}

	function returnDateCreated ($id) {
		return ($this -> returnItem ($id, 'datecreated'));
	}

	function returnDateModified ($id) {
		return ($this -> returnItem ($id, 'datemodified'));
	}

	function returnReplies ($id) {
		return ($this -> returnItem ($id, 'replies'));
	}

	function returnRootId ($id) {
		return ($this -> returnItem ($id, 'root_id'));
	}

	function returnSubject ($id) {
		return ($this -> returnItem ($id, 'subject'));
	}

	function returnUserId ($id) {
		return ($this -> returnItem ($id, 'user_id'));
	}
	
	function returnTreeId ($id) {
		return ($this -> returnItem ($id, 'tree_id'));
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
			return ($this -> changeInt ($id, $column, $bits + $value));
		}
	}

}

?>
