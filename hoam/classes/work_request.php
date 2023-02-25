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

class HOAM_work_request extends HOAM_common {

	function __construct ($table) {
		parent::__construct ($table);
	}

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

	function changeDateCompleted ($id, $value = NULL) {
		if ($value === NULL) $value = date ('Y-m-d H:i:s');
		return ($this -> changeString ($id, 'datecompleted', $value));
	}
	
	function changeDateDue ($id, $value = NULL) {
		if ($value === NULL) $value = date ('Y-m-d H:i:s');
		return ($this -> changeString ($id, 'datedue', $value));
	}
	
	function changeDescription ($id, $value) {
		return ($this -> changeString ($id, 'description', $value));
	}
	
	function changeLotId ($id, $value) {
		return ($this -> changeString ($id, 'lot_id', $value));
	}
	
	function changeNotes ($id, $value) {
		return ($this -> changeString ($id, 'notes', $value));
	}
	
	function changePriority ($id, $value) {
		return ($this -> changeInt ($id, 'priority', (int) $value));
	}
	
	function changeParentId ($id, $value) {
		return ($this -> changeString ($id, 'parent_id', $value));
	}
	
	function changeRequesterId ($id, $value) {
		return ($this -> changeString ($id, 'requester_id', $value));
	}
	
	function changeStatus ($id, $value) {
		return ($this -> changeInt ($id, 'status', (int) $value));
	}
	
	function changeTitle ($id, $value) {
		return ($this -> changeString ($id, 'title', $value));
	}
	
	function changeUserId ($id, $value) {
		return ($this -> changeString ($id, 'user_id', $value));
	}
	
	function changeVendorId ($id, $value) {
		return ($this -> changeString ($id, 'vendor_id', $value));
	}
	
	function listDue ($number = 0, $offset = 0, $status = NULL, $priority = NULL) {
		global $_HOAM_setting;
		
		// return an array containing the id of all requests, grouped by parent/
		// child.
		$query_parents = "SELECT id FROM $this->table
			WHERE parent_id IS NULL";
		if ($status) {
			$query_parents .= " AND (status & $status)";
		}
		$query_parents .= " ORDER BY datedue ASC";
		if ($priority) {
			$query_parents .= ", priority = '$priority'";
		} else {
			$query_parents .= ", priority DESC";
		}
		$query_parents .= ", status ASC";
		// Execute the query
		$complete_list = array ();
		$parent_requests = $this -> listSimpleQuery ($query_parents);
		if (is_array ($parent_requests)) {
			foreach ($parent_requests as $parent) {
				// If this is a parent (or just doesn't have any children)
				// append a 0 to the array
				array_push ($complete_list, array ($parent, 0));
				$query_children = "SELECT id FROM $this->table
					WHERE parent_id = '$parent'
					ORDER BY datedue ASC, priority DESC, status ASC";
				$child_requests = $this -> listSimpleQuery ($query_children);
				if (is_array ($child_requests)) {
					foreach ($child_requests as $child) {
						// If this is a child, append a 1 to the array
						// This is used to later when building the category select list
						// among other things
						array_push ($complete_list, array ($child, 1));
					}
				}
			}
		}
		return ($complete_list);
	}

	function listLotByDate ($lot_id, $start_date, $end_date, $number = 0, $offset = 0, $status = NULL, $priority = NULL) {
		global $_HOAM_setting;
		
		// return an array containing the id of all requests, grouped by parent/
		// child.
		$query_parents = "SELECT id FROM $this->table
			WHERE lot_id = '$lot_id'
			AND parent_id IS NULL
			AND datecreated >= '$start_date'
			AND datecreated <= '$end_date'";
		if ($status) {
			$query_parents .= " AND (status & $status)";
		}
		$query_parents .= " ORDER BY datedue ASC";
		if ($priority) {
			$query_parents .= ", priority = '$priority'";
		} else {
			$query_parents .= ", priority DESC";
		}
		$query_parents .= ", status ASC";
		// Execute the query
		$complete_list = array ();
		$parent_requests = $this -> listSimpleQuery ($query_parents);
		if (is_array ($parent_requests)) {
			foreach ($parent_requests as $parent) {
				// If this is a parent (or just doesn't have any children)
				// append a 0 to the array
				array_push ($complete_list, array ($parent, 0));
				$query_children = "SELECT id FROM $this->table
					WHERE parent_id = '$parent'
					ORDER BY datedue ASC, priority DESC, status ASC";
				$child_requests = $this -> listSimpleQuery ($query_children);
				if (is_array ($child_requests)) {
					foreach ($child_requests as $child) {
						// If this is a child, append a 1 to the array
						// This is used to later when building the category select list
						// among other things
						array_push ($complete_list, array ($child, 1));
					}
				}
			}
		}
		return ($complete_list);
	}

	function listUserRequest ($user_id, $number = 0, $offset = 0, $status = NULL, $priority = NULL) {
		global $_HOAM_setting;
		
		// return an array containing the id of all requests, grouped by parent/
		// child.
		$query_parents = "SELECT id FROM $this->table
			WHERE user_id = '$user_id'
			AND parent_id IS NULL";
		if ($status) {
			$query_parents .= " AND (status & $status)";
		}
		$query_parents .= " ORDER BY datedue ASC";
		if ($priority) {
			$query_parents .= ", priority = '$priority'";
		} else {
			$query_parents .= ", priority DESC";
		}
		$query_parents .= ", status ASC";
		// Execute the query
		$complete_list = array ();
		$parent_requests = $this -> listSimpleQuery ($query_parents);
		if (is_array ($parent_requests)) {
			foreach ($parent_requests as $parent) {
				// If this is a parent (or just doesn't have any children)
				// append a 0 to the array
				array_push ($complete_list, array ($parent, 0));
				$query_children = "SELECT id FROM $this->table
					WHERE parent_id = '$parent'
					ORDER BY datedue ASC, priority DESC, status ASC";
				$child_requests = $this -> listSimpleQuery ($query_children);
				if (is_array ($child_requests)) {
					foreach ($child_requests as $child) {
						// If this is a child, append a 1 to the array
						// This is used to later when building the category select
						// list among other things
						array_push ($complete_list, array ($child, 1));
					}
				}
			}
		}
		return ($complete_list);
	}

	function listVendorRequest ($vendor_id, $number = 0, $offset = 0, $status = NULL, $priority = NULL) {
		global $_HOAM_setting;
		
		// return an array containing the id of all requests, grouped by parent/
		// child.
		$query_parents = "SELECT id FROM $this->table
			WHERE vendor_id = '$vendor_id'
			AND parent_id IS NULL";
		if ($status) {
			$query_parents .= " AND (status & $status)";
		}
		$query_parents .= " ORDER BY datedue ASC";
		if ($priority) {
			$query_parents .= ", priority = '$priority'";
		} else {
			$query_parents .= ", priority DESC";
		}
		$query_parents .= ", status ASC";
		// Execute the query
		$complete_list = array ();
		$parent_requests = $this -> listSimpleQuery ($query_parents);
		if (!empty ($parent_requests)) {
			foreach ($parent_requests as $parent) {
				// If this is a parent (or just doesn't have any children)
				// append a 0 to the array
				array_push ($complete_list, array ($parent, 0));
				$query_children = "SELECT id FROM $this->table
					WHERE parent_id = '$parent'
					ORDER BY datedue ASC, priority DESC, status ASC";
				$child_requests = $this -> listSimpleQuery ($query_children);
				if (!empty ($child_requests)) {
					foreach ($child_requests as $child) {
						// If this is a child, append a 1 to the array
						// This is used to later when building the category select list
						// among other things
						array_push ($complete_list, array ($child, 1));
					}
				}
			}
		}
		return ($complete_list);
	}
	
	function returnCountDueUser ($user_id) {
		global $_HOAM_setting;
		$query = "SELECT count(id) FROM $this->table
					WHERE user_id = '$user_id'
					AND ~status & " . $_HOAM_setting ['work_request']['flags']['complete'];
		// Execute the query
		return ($this -> itemQuery ($query));
	}
	
	function returnDateComplete ($id) {
		return ($this -> returnItem ($id, 'datecompleted'));
	}

	function returnDateDue ($id) {
		return ($this -> returnItem ($id, 'datedue'));
	}

	function returnDescription ($id) {
		return ($this -> returnItem ($id, 'description'));
	}

	function returnLotId ($id) {
		return ($this -> returnItem ($id, 'lot_id'));
	}

	function returnNotes ($id) {
		return ($this -> returnItem ($id, 'notes'));
	}

	function returnParentId ($id) {
		return ($this -> returnItem ($id, 'parent_id'));
	}

	function returnPriority ($id) {
		return ($this -> returnItem ($id, 'priority'));
	}

	function returnRequesterId ($id) {
		return ($this -> returnItem ($id, 'requester_id'));
	}

	function returnStatus ($id) {
		return ($this -> returnItem ($id, 'status'));
	}

	function returnTitle ($id) {
		return ($this -> returnItem ($id, 'title'));
	}

	function returnUserId ($id) {
		return ($this -> returnItem ($id, 'user_id'));
	}

	function returnVendorId ($id) {
		return ($this -> returnItem ($id, 'vendor_id'));
	}

}
?>
