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

class HOAM_violation extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function changeCategory ($id, $value) {
		return ($this -> changeString ($id, 'category', $value));
	}
	
	function changeDescription ($id, $value) {
		return ($this -> changeString ($id, 'description', $value));
	}
	
	function changeLotId ($id, $value) {
		return ($this -> changeInt ($id, 'lot_id', $value));
	}
	
	function changeOriginalLetter ($id, $value) {
		return ($this -> changeString ($id, 'original_letter', $value));
	}

	function changeResolution ($id, $value) {
		return ($this -> changeString ($id, 'resolution', $value));
	}
	
	function changeResolveByDate ($id, $value) {
		return ($this -> changeString ($id, 'dateresolveby', $value));
	}

	function changeResolutionDate ($id, $value) {
		return ($this -> changeString ($id, 'dateresolution', $value));
	}

	function changeSeverity ($id, $value) {
		return ($this -> changeString ($id, 'severity', $value));
	}
	
	function changeSpecific ($id, $value) {
		// MySQL reserved word 'specific', thus specifics
		return ($this -> changeString ($id, 'specifics', $value));
	}
	
	function changeViolationDate ($id, $value) {
		return ($this -> changeString ($id, 'dateviolation', $value));
	}

	function listCategory ($category, $number = 0, $offset = 0) {
		// Return details for all of the violations with a specific category
		// Does this even work since the categories are now serialized?
		$query = "SELECT * FROM $this->table
					WHERE category = '$category'
					ORDER BY datecreated DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function listLotId ($item = 0, $number = 0, $offset = 0) {
		// Return details for all of the violations for a particular lot
		if ($item == 0) {
			$query = "SELECT * FROM $this->table
					ORDER BY datecreated DESC, lot_id ASC";
		} else {
			$query = "SELECT id FROM $this->table
					WHERE lot_id = '$item'
					ORDER BY datecreated DESC";
		}
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}
	
	function listAfterDate ($lot_id, $date, $number = 0, $offset = 0) {
		// Return the IDs for all violations for a specified lot after the
		// specified date.
		$query = "SELECT id FROM $this->table
				WHERE lot_id = '$lot_id'
					AND dateviolation > '$date'
				ORDER BY datecreated DESC";
		// Are we retrieving a limited number of items?
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}
	
	function listBeforeDate ($lot_id, $date, $number = 0, $offset = 0) {
		// Return the IDs for all violations for a specified lot before the
		// specified date.
		$query = "SELECT id FROM $this->table
				WHERE lot_id = '$lot_id'
					AND dateviolation < '$date'
				ORDER BY datecreated DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}
	
	function listOpen ($number = 0, $offset = 0) {
		// Return the IDs for all violations that have not been resolved.
		$query = "SELECT id FROM $this->table
					WHERE (dateresolution IS NULL OR dateresolution = '1970-01-01')
					ORDER BY datecreated DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}
	
	function listOnViolationDate ($date, $number = 0, $offset = 0) {
		// Return the IDs for all violations on a specified date
		$query = "SELECT id FROM $this->table
					WHERE dateviolation = '$date'
					ORDER BY lot_id desc";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function listRangeLotId ($newest, $oldest, $lot_id, $number = 0, $offset = 0) {
		// Return the IDs created between the specified dates on a given lot
		$query = "SELECT id FROM $this->table
					WHERE (datecreated BETWEEN '$newest' AND '$oldest')
					AND lot_id = '$lot_id'
					ORDER BY dateviolation DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function listResolutionDates ($number = 0, $offset = 0) {
		// Return an array with the unique resolution dates for all violations.
		$query = "SELECT distinct dateresolution FROM $this->table
					ORDER BY dateresolution desc";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function listViolationDates ($number = 0, $offset = 0) {
		// Return an array with the unique violation dates for all violations.
		$query = "SELECT distinct dateviolation FROM $this->table
					ORDER BY dateviolation desc";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function listRecentViolation ($item) {
		// Find the most recent violation for a specified lot.
		$query = "SELECT id from $this->table
					WHERE lot_id = '$item'
					ORDER BY dateviolation DESC";
		return ($this -> listSimpleQuery ($query));
	}
	
	function returnCategory ($id) {
		return ($this -> returnItem ($id, 'category'));
	}
	
	function returnCategoryInUse ($id) {
		// This function returns whether the given category_id
		// is in use by any violations.
		$count = $this -> returnCountCategory ($id);
		if ($count > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function returnCountCategory ($item) {
		// Not sure if there's a way to optimize this short of maintaining
		// a running count for each category like with the messageboard.
		$violation_list = $this -> listAllId ();
		$category_count = 0;
		foreach ($violation_list as $violation) {
			$categories = unserialize ($this -> returnCategory ($violation));
			foreach ($categories as $catagory) {
				if ($item == $catagory) {
					$category_count++;
				}
			}
		}
		return $category_count;
	}

	function returnCountSeverity ($item) {
		return ($this -> returnCountQuery ("WHERE severity = '$item'"));
	}

	function returnCountLotId ($item) {
		return ($this -> returnCountQuery ("WHERE lot_id = '$item'"));
	}
	
	function returnCountOpen () {
		return ($this -> returnCountQuery ("WHERE resolved = 'NULL'"));
	}

	function returnDescription ($id) {
		return ($this -> returnItem ($id, 'description'));
	}
	
	function returnLotId ($id) {
		return ($this -> returnItem ($id, 'lot_id'));
	}
	
	function returnOriginalLetter ($id) {
		return ($this -> returnItem ($id, 'original_letter'));
	}
	
	function returnRecentViolation ($item) {
		$query = "SELECT id from $this->table
					WHERE lot_id = '$item'
					ORDER BY dateviolation DESC
					LIMIT 1;";
		return ($this -> itemQuery ($query));
	}

	function returnResolution ($id) {
		return ($this -> returnItem ($id, 'resolution'));
	}
	
	function returnResolutionDate ($id) {
		return ($this -> returnItem ($id, 'dateresolution'));
	}

	function returnResolveByDate ($id) {
		return ($this -> returnItem ($id, 'dateresolveby'));
	}

	function returnSeverity ($id) {
		return ($this -> returnItem ($id, 'severity'));
	}
	
	function returnSpecific ($id) {
		// MySQL reserved word 'specific', thus specifics
		return ($this -> returnItem ($id, 'specifics'));
	}
	
	function returnSeverityInUse ($id) {
		// This function returns whether the given severity_id
		// is in use by any violations.
		$count = $this -> returnCountSeverity ($id);
		if ($count > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function returnViolationDate ($id) {
		return ($this -> returnItem ($id, 'dateviolation'));
	}
}

?>
