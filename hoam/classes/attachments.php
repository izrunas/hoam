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

class HOAM_attachments extends HOAM_common {
	
	function changeDescription ($id, $value) {
		return ($this -> changeString ($id, 'description', $value));
	}

	function changeFilename ($id, $value) {
		return ($this -> changeString ($id, 'filename', $value));
	}

	function changeHeight ($id, $value) {
		return ($this -> changeInt ($id, 'height', $value));
	}
	
	function changeMime ($id, $value) {
		return ($this -> changeString ($id, 'mimetype', $value));
	}
	
	function changeOwners ($id, $value) {
		return ($this -> changeString ($id, 'owners', serialize ($value)));
	}
	
	function changeSize ($id, $value) {
		return ($this -> changeInt ($id, 'size', $value));
	}
	
	function changeWidth ($id, $value) {
		return ($this -> changeInt ($id, 'width', $value));
	}

	function listWithOwner ($owner_id, $flags = 0, $number = 0, $offset = 0) {
		// Return an array containing a list of all attachments
		// that have the specified owner_id as one of the owners.
		// Formulate the SQL query
		$query = "SELECT id, owners FROM $this->table ";
		// Are any flags set?
		if ($flags) {
			$query .= "WHERE flags & $flags ";
		}
		$query .= "ORDER BY datecreated DESC";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		$attachment_list = $this -> listQuery ($query);
		$owner_list = array ();
		if (is_array ($attachment_list)) {
			foreach ($attachment_list as $attachment) {
				$owners = unserialize ($attachment['owners']);
				if (is_array ($owners)) {
					if (in_array ($owner_id, $owners)) {
						array_push ($owner_list, $attachment['id']);
					}
				} elseif ($owners == $owner_id) {
					array_push ($owner_list, $attachment['id']);
				}
			}
		}
		unset ($attachment_list);
		return ($owner_list);
	}

	function listByDate ($id = NULL, $num = 20) {
		// Return an array containing a list of attachments in order of date
		// starting at the specified ID.
		if ($id == NULL) {
			$id = '';
		}
		$result = $this -> runQuery ('call HOAM_attachmentList ("' . $id . '", ' . $num . ')');

		$count = mysqli_affected_rows ($this -> connection);
		if ($count > 0) {
			$array = array ();
			for ($row = 0; $row < $count; $row ++) {
				$array[$row] = mysqli_fetch_assoc ($result);
			}
			// OK, complete and utter PItA. The mysqli_next_result needs to be
			// there otherwise we get the error "Commands out of sync;  You
			// can't run this command now"; using mysqli_free_result doesn't
			// work either.
			mysqli_next_result ($this -> connection);
			return $array;
		} else {
			return FALSE;
		}	
	}

	function listByDateRange ($startdate, $enddate, $order = "datecreated DESC", $number = 0, $offset = 0) {
		// Return all IDs
		$query = "SELECT id FROM $this->table
						WHERE datecreated>='$startdate' AND datecreated <='$enddate'
						ORDER BY $order";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query) );
	}
	
	function returnDescription ($id) {
		return ($this -> returnItem ($id, 'description'));
	}

	function returnFilename ($id) {
		return ($this -> returnItem ($id, 'filename'));
	}

	function returnHeight ($id) {
		return ($this -> returnItem ($id, 'height'));
	}

	function returnMime ($id) {
		return ($this -> returnItem ($id, 'mimetype'));
	}

	function returnOwners ($id) {
		return (unserialize ($this -> returnItem ($id, 'owners')));
	}

	function returnSize ($id) {
		return ($this -> returnItem ($id, 'size'));
	}

	function returnWidth ($id) {
		return ($this -> returnItem ($id, 'width'));
	}

}

?>
