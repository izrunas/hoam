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

class HOAM_insurance extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function changeComments ($id, $item) {
		return ($this -> changeString ($id, 'comments', $item));
	}

	function changeExpirationDate ($id, $item) {
		return ($this -> changeString ($id, 'dateexpiration', $item));
	}

	function changeLotId ($id, $value) {
		return ($this -> changeString ($id, 'lot_id', $value));
	}
	
	function changePolicyNum ($id, $value) {
		return ($this -> changeString ($id, 'policy_num', $value));
	}
	
	function changePolicyType ($id, $value) {
		return ($this -> changeInt ($id, 'policy_type', $value));
	}

	function changeRenewalDate ($id, $item) {
		return ($this -> changeString ($id, 'daterenewal', $item));
	}

	function changeVendorId ($id, $item) {
		return ($this -> changeString ($id, 'vendor_id', $item));
	}
	
	function returnComments ($id) {
		return ($this -> returnItem ($id, 'comments'));
	}

	function listCurrentId () {
		global $_HOAM_setting;
		
		return ($this -> listSimpleQuery ("SELECT id FROM insurance WHERE ~flags & " . $_HOAM_setting ['budget']['insurance']['flags']['replaced'] . " ORDER BY policy_type ASC"));
	}
	
	function listExpiredId () {
		global $_HOAM_country, $_HOAM_setting;
		
		return ($this -> listSimpleQuery ("SELECT id FROM insurance WHERE dateexpiration <'" . date ($_HOAM_country ['date']['format_mysql']) . "' AND flags & " . $_HOAM_setting ['budget']['insurance']['flags']['replaced'] . " ORDER BY policy_type ASC"));
	}
	
	function listExpiringId ($date) {
		global $_HOAM_country, $_HOAM_setting;
		
		return ($this -> listSimpleQuery ("SELECT id FROM insurance WHERE dateexpiration <'" . date ($_HOAM_country ['date']['format_mysql'], strtotime ($date)) . "' AND ~flags & " . $_HOAM_setting ['budget']['insurance']['flags']['replaced'] . " ORDER BY policy_type ASC"));
	}
	
	function returnExpirationDate ($id) {
		return ($this -> returnItem ($id, 'dateexpiration'));
	}

	function returnLotId ($id) {
		return ($this -> returnItem ($id, 'lot_id'));
	}

	function returnPolicyNum ($id) {
		return ($this -> returnItem ($id, 'policy_num'));
	}

	function returnPolicyType ($id) {
		return ($this -> returnItem ($id, 'policy_type'));
	}

	function returnRenewalDate ($id) {
		return ($this -> returnItem ($id, 'daterenewal'));
	}

	function returnVendorId ($id) {
		return ($this -> returnItem ($id, 'vendor_id'));
	}
}

?>
