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

class HOAM_budget_vendor extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function changeAddress1 ($id, $value) {
		return ($this -> changeString ($id, 'address1', $value));
	}
	
	function changeAddress2 ($id, $value) {
		return ($this -> changeString ($id, 'address2', $value));
	}
	
	function changeAddress3 ($id, $value) {
		return ($this -> changeString ($id, 'address3', $value));
	}
	
	function changeCategoryId ($id, $value) {
		return ($this -> changeString ($id, 'category_id', $value));
	}
	
	function changeCity ($id, $value) {
		return ($this -> changeString ($id, 'city', $value));
	}
	
	function changeContactId ($id, $value) {
		return ($this -> changeString ($id, 'contact_id', $value));
	}

	function changeEmail ($id, $value) {
		return ($this -> changeString ($id, 'email', $value));
	}

	function changeFederalId ($id, $value) {
		return ($this -> changeString ($id, 'federal_id', $value));
	}

	function changeName ($id, $item) {
		return ($this -> changeString ($id, 'name', $item));
	}
	
	function changePostalcode ($id, $item) {
		return ($this -> changestring ($id, 'postalcode', $item));
	}
	
	function changeState ($id, $value) {
		return ($this -> changeString ($id, 'state', $value));
	}

	function changeStateId ($id, $value) {
		return ($this -> changeString ($id, 'state_id', $value));
	}

	function changeTelephoneFax ($id, $value) {
		return ($this -> changeString ($id, 'telephone_fax', $value));
	}

	function changeTelephoneWork ($id, $value) {
		return ($this -> changeString ($id, 'telephone_work', $value));
	}

	function changeWebsite ($id, $value) {
		return ($this -> changeString ($id, 'website', $value));
	}

	function ListVendors ($number = 0, $offset = 0) {
		// Return an array listing all of the vendors
		$query = "SELECT id FROM $this->table
					ORDER BY name ASC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function returnAddress1 ($id) {
		return ($this -> returnItem ($id, 'address1'));
	}

	function returnAddress2 ($id) {
		return ($this -> returnItem ($id, 'address2'));
	}

	function returnAddress3 ($id) {
		return ($this -> returnItem ($id, 'address3'));
	}

	function returnCategoryId ($id) {
		return ($this -> returnItem ($id, 'category_id'));
	}

	function returnCity ($id) {
		return ($this -> returnItem ($id, 'city'));
	}

	function returnContactId ($id) {
		return ($this -> returnItem ($id, 'contact_id'));
	}

	function returnEmail ($id) {
		return ($this -> returnItem ($id, 'email'));
	}

	function returnFederalId ($id) {
		return ($this -> returnItem ($id, 'federal_id'));
	}

	function returnName ($id) {
		return ($this -> returnItem ($id, 'name'));
	}

	function returnPostalcode ($id) {
		return ($this -> returnItem ($id, 'postalcode'));
	}

	function returnState ($id) {
		return ($this -> returnItem ($id, 'state'));
	}

	function returnStateId ($id) {
		return ($this -> returnItem ($id, 'state_id'));
	}

	function returnTelephoneFax ($id) {
		return ($this -> returnItem ($id, 'telephone_fax'));
	}

	function returnTelephoneWork ($id) {
		return ($this -> returnItem ($id, 'telephone_work'));
	}

	function returnWebsite ($id) {
		return ($this -> returnItem ($id, 'website'));
	}
}

?>
