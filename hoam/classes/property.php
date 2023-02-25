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

class HOAM_property extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function changeComments ($id, $value) {
		return ($this -> changeString ($id, 'comments', $value));
	}
	
	function changeDescription ($id, $value) {
		return ($this -> changeString ($id, 'description', $value));
	}

	function changeDepositAmount ($id, $value) {
		return ($this -> changeFloat ($id, 'deposit_amount', $value));
	}

	function changeFeeAmount ($id, $value) {
		return ($this -> changeFloat ($id, 'fee_amount', $value));
	}

	function changeRentalAmount ($id, $value) {
		return ($this -> changeFloat ($id, 'rental_amount', $value));
	}

	function changeOwnerId ($id, $value) {
		return ($this -> changeString ($id, 'owner_id', $value));
	}

	function changeVendorId ($id, $value) {
		return ($this -> changeString ($id, 'vendor_id', $value));
	}

	function returnComments ($id) {
		return ($this -> returnItem ($id, 'comments'));
	}

	function returnDescription ($id) {
		return ($this -> returnItem ($id, 'description'));
	}

	function returnDepositAmount ($id) {
		return ($this -> returnItem ($id, 'deposit_amount'));
	}

	function returnFeeAmount ($id) {
		return ($this -> returnItem ($id, 'fee_amount'));
	}

	function returnRentalAmount ($id) {
		return ($this -> returnItem ($id, 'rental_amount'));
	}

	function returnOwnerId ($id) {
		return ($this -> returnItem ($id, 'owner_id'));
	}

	function returnVendorId ($id) {
		return ($this -> returnItem ($id, 'vendor_id'));
	}

}

?>