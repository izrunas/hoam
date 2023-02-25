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

class HOAM_violation_severity extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function changeClosing ($id, $item) {
		return ($this -> changeString ($id, 'closing', $item));
	}
	
	function changeEscalate ($id, $item) {
		return ($this -> changeString ($id, 'escalate', $item));
	}

	function changeFineInterest ($id, $item) {
		return ($this -> changeFloat ($id, 'fine_interest', $item));
	}
	
	function changeFinePerDay ($id, $item) {
		return ($this -> changeFloat ($id, 'fine_per_day', $item));
	}

	function changeFinePerNotice ($id, $item) {
		return ($this -> changeFloat ($id, 'fine_per_notice', $item));
	}

	function changeNumdays ($id, $item) {
		return ($this -> changeInt ($id, 'numdays', $item));
	}
	
	function changePreamble ($id, $item) {
		return ($this -> changeString ($id, 'preamble', $item));
	}
	
	function changeSeverity ($id, $item) {
		return ($this -> changeInt ($id, 'severity', $item));
	}
	
	function ListSeverity ($number = 0, $offset = 0) {
		// Return an array containing a list of all severity levels
		$query = "SELECT id FROM $this->table
						ORDER BY severity ASC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function returnCountUserId ($item) {
		return ($this -> returnCountQuery ("WHERE user_id = '$item'"));
	}
	
	function returnCountRange ($newest, $oldest) {
		return ($this -> returnCountQuery ("WHERE (datecreated BETWEEN '$newest' AND '$oldest')"));
	}
	
	function returnClosing ($id) {
		return ($this -> returnItem ($id, 'closing'));
	}
	
	function returnEscalate ($id) {
		return ($this -> returnItem ($id, 'escalate'));
	}

	function returnFineInterest ($id) {
		return ($this -> returnItem ($id, 'fine_interest'));
	}

	function returnFinePerDay ($id) {
		return ($this -> returnItem ($id, 'fine_per_day'));
	}

	function returnFinePerNotice ($id) {
		return ($this -> returnItem ($id, 'fine_per_notice'));
	}

	function returnNumdays ($id) {
		return ($this -> returnItem ($id, 'numdays'));
	}
	
	function returnPreamble ($id) {
		return ($this -> returnItem ($id, 'preamble'));
	}
	
	function returnSeverity ($id) {
		return ($this -> returnItem ($id, 'severity'));
	}
}

?>
