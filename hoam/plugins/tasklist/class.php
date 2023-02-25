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

class tasklist extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're
	// given. This class does not perform any cleansing of data!
	
	function changeAssignedGroup ($id, $item) {
		return ($this -> changeString ($id, 'assigned_group', $item));
	}
	
	function changeAssignedUser ($id, $item) {
		return ($this -> changeString ($id, 'assigned_user', $item));
	}
	
	function changeDateDue ($id, $value = NULL) {
		if ($value == NULL) {
			$value = date ("Y-m-d H:i:s");
		}
		return ($this -> changeString ($id, 'datedue', $value));
	}
	
	function changePriority ($id, $item) {
		return ($this -> changeInt ($id, 'priority', $item));
	}

	function changeStatus ($id, $item) {
		return ($this -> changeInt ($id, 'status', $item));
	}

	function changeTask ($id, $item) {
		return ($this -> changeString ($id, 'item', $item));
	}
	
	function listAuthorComplete ($user_id, $number = 0, $offset = 0) {
		// Return details for all of the articles by a specific author
		// Formulate the SQL query
		$query = "SELECT id FROM $this->table
					WHERE user_id = '$user_id' AND status = '128'
					ORDER BY datedue ASC";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}

	function listAuthorDue ($user_id, $number = 0, $offset = 0) {
		// Return details for all of the articles by a specific author
		// Formulate the SQL query
		$query = "SELECT id FROM $this->table
					WHERE user_id = '$user_id' AND status != '128'
					ORDER BY datedue ASC";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}

	function listDue ($user_id, $number = 0, $offset = 0, $status = NULL, $priority = NULL) {
		global $_HOAM_setting;
		// Return details for all of the articles by a specific author
		// Formulate the SQL query
		$query = "SELECT id FROM $this->table
					WHERE assigned_user = '$user_id'";
		if ($status) {
			$query .= " AND (status & '$status') = '$status'";
		} else {
			$query .= " AND (status & '" . (int) $_HOAM_setting ['plugin']['tasklist']['flag']['status_complete'] . "') != '" . (int) $_HOAM_setting ['plugin']['tasklist']['flag']['status_complete'] . "'";
		}
		$query .= " ORDER BY datedue ASC";
		if ($priority) {
			$query .= " , priority = '$priority'";
		} else {
			$query .= " , priority ASC";
		}
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}

	function returnAssignedGroup ($id) {
		return ($this -> returnItem ($id, 'assigned_group'));
	}

	function returnAssignedUser ($id) {
		return ($this -> returnItem ($id, 'assigned_user'));
	}

	function returnComplete ($id) {
		// Return completion status for the task specified.
		global $_HOAM_setting;

		// Right now this is a crappy hack just to fix code that's broken and
		// dependent on this function.
		if ($this-> isBitSet ($id, $_HOAM_setting ['plugin']['tasklist']['flag']['complete'], 'status')) {
			return '100%';
		} else {
			return '0%';
		}
	}
	
	function returnCountDueUser ($user_id) {
		global $_HOAM_setting;
		
		$query = "SELECT count(*) FROM $this->table
					WHERE assigned_user = '$user_id'
					AND (status & '" . $_HOAM_setting ['plugin']['tasklist']['flag']['status_complete'] . "') != '" . $_HOAM_setting ['plugin']['tasklist']['flag']['status_complete'] . "'";
		// Execute the query
		return ($this -> itemQuery ($query));
	}
	
	function returnDateDue ($id) {
		return ($this -> returnItem ($id, 'datedue'));
	}

	function returnPriority ($id) {
		return ($this -> returnItem ($id, 'priority'));
	}

	function returnStatus ($id) {
		return ($this -> returnItem ($id, 'status'));
	}

	function returnTask ($id) {
		return ($this -> returnItem ($id, 'item'));
	}
}

?>
