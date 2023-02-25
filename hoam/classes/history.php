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

class HOAM_history extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!

	function changeAction ($id, $value) {
		return ($this -> changeInt ($id, 'action', $value));
	}

	function changeDescription ($id, $value) {
		return ($this -> changeString ($id, 'description', $value));
	}

	function changeHomeownerId ($id, $value) {
		return ($this -> changeString ($id, 'homeowner_id', $value));
	}

	function changeOwnerId ($id, $value) {
		return ($this -> changeString ($id, 'owner_id', $value));
	}

	function listRevisions ($owner_id, $number = 0, $offset = 0) {
		$query = "SELECT id FROM $this->table
					WHERE owner_id = '$owner_id'
					ORDER BY revision DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function returnAction ($id) {
		return ($this -> returnItem ($id, 'action'));
	}

	function returnDescription ($id) {
		return ($this -> returnItem ($id, 'description'));
	}

	function returnHomeownerId ($id) {
		return ($this -> returnItem ($id, 'homeowner_id'));
	}

	function returnLotId ($id) {
		return ($this -> returnItem ($id, 'lot_id'));
	}

	function returnOwnerId ($id) {
		return ($this -> returnItem ($id, 'owner_id'));
	}

	function returnRevision ($id) {
		return ($this -> returnItem ($id, 'revision'));
	}

}

?>