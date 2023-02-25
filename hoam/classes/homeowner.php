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

class HOAM_homeowner extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function changeAccessCode ($id, $value) {
		// This is most likely an int, but we'll save it as a string just in
		// case.
		return ($this -> changeString ($id, 'access_code', $value));
	}
	
	function changeAddress1 ($id, $value) {
		return ($this -> changeString ($id, 'address1', $value));
	}
	
	function changeAddress2 ($id, $value) {
		return ($this -> changeString ($id, 'address2', $value));
	}

	function changeAddress3 ($id, $value) {
		return ($this -> changeString ($id, 'address3', $value));
	}

	function changeCity ($id, $value) {
		return ($this -> changeString ($id, 'city', $value));
	}

	function changeComments ($id, $comments) {
		return ($this -> changeString ($id, 'comments', $comments));
	}

	function changeDateBankrupt ($id, $value) {
		return ($this -> changeString ($id, 'bankruptcy_date', $value));
	}

	function changeEmail ($id, $email) {
		return ($this -> changeString ($id, 'email', $email));
	}

	function changeLotId ($id, $value) {
		return ($this -> changeInt ($id, 'lot_id', $value));
	}

	function changeName ($id, $value) {
		return ($this -> changeString ($id, 'name', $value));
	}

	function changePaymentPlanDate ($id, $value) {
		return ($this -> changeString ($id, 'payment_plan_date', $value));
	}

	function changePaymentPlanDetails ($id, $value) {
		return ($this -> changeString ($id, 'payment_plan_details', $value));
	}

	function changePurchaseDate ($id, $value) {
		return ($this -> changeString ($id, 'purchasedate', $value));
	}

	function changeResidence_Mailing ($id, $value) {
		return ($this -> changeInt ($id, 'residence_mailing', $value));
	}

	function changeSaleDate ($id, $value) {
		return ($this -> changeString ($id, 'saledate', $value));
	}

	function changeState ($id, $value) {
		return ($this -> changeString ($id, 'state', $value));
	}

	function changeTelephoneFax ($id, $value) {
		return ($this -> changeString ($id, 'telephone_fax', $value));
	}
	
	function changeTelephoneHome ($id, $value) {
		return ($this -> changeString ($id, 'telephone_home', $value));
	}

	function changeTelephoneMobile ($id, $value) {
		return ($this -> changeString ($id, 'telephone_mobile', $value));
	}

	function changeTelephoneWork ($id, $value) {
		return ($this -> changeString ($id, 'telephone_work', $value));
	}

	function changeZipcode ($id, $value) {
		return ($this -> changeString ($id, 'zipcode', $value));
	}
	
	function isCurrentOwner ($homeowner_id) {
		// Is the specified homeowner the current owner of the property?
		$query = "SELECT * from $this->table
					WHERE id = '$homeowner_id' AND
					saledate='1970-01-01'";
		$result = $this -> itemQuery ($query);
		if ($result) {
			return (TRUE);
		} else {
			return (FALSE);
		}
	}
	
	function listByLotId ($lot_id, $number = 0, $offset = 0) {
		// Return an array with all homeowners for a specific lot
		$query = "SELECT id FROM $this->table
		            WHERE lot_id = '$lot_id'
					ORDER BY purchasedate DESC, saledate DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function listCurrentHomeowners ($number = 0, $offset = 0) {
		// Return an array with all of the current homeowners
		$query = "SELECT homeowners.id FROM $this->table, lots
					WHERE homeowners.saledate='1970-01-01' AND
					homeowners.lot_id = lots.id
					ORDER BY lots.street ASC, lots.address ASC, lots.building, lots.suite";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
		
	}

	function listResidenceHomeowners ($lot_id, $number = 0, $offset = 0) {
		// Return an array with all of the owners (current and prior) for a lot
		$query = "SELECT id FROM $this->table
					WHERE lot_id = '$lot_id'
					ORDER BY purchasedate DESC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function listCurrentOwnersWithBitSet ($bit) {
		$query = "SELECT h.id, h.flags FROM $this->table as h, lots
				WHERE h.lot_id = lots.id AND
				h.saledate = '1970-01-01'
				ORDER BY lots.street ASC, lots.address ASC, lots.building ASC, lots.suite ASC";
		$list = $this -> listQuery ($query);
		if (is_array ($list)) {
			$array = array ();
			foreach ($list as $item) {
				if ($item['flags'] & $bit) {
				} else {
					array_push ($array, $item['id']);
				}
			}
			return ($array);
		}
		return FALSE;
	}

	function returnAccessCode ($id) {
		return ($this -> returnItem ($id, 'access_code'));
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

	function returnCity ($id) {
		return ($this -> returnItem ($id, 'city'));
	}

	function returnComments ($id) {
		return ($this -> returnItem ($id, 'comments'));
	}

    function returnCurrentOwner ($lot_id) {
    	// Returns the id of the current homeowner.
    	// It gets this information by assuming that the current homeowner
    	// Will have a saledate of "1970-01-01"
	    $query = "SELECT id from $this->table
	              WHERE lot_id = '$lot_id' AND
	              (saledate='1970-01-01' OR saledate IS NULL)";
    	return ($this -> itemQuery ($query));
    }
    
	function returnDateBankrupt ($id) {
		return ($this -> returnItem ($id, 'bankruptcy_date'));
	}

	function returnEmail ($id) {
		return ($this -> returnItem ($id, 'email'));
	}

	function returnLotId ($id) {
		return ($this -> returnItem ($id, 'lot_id'));
	}

	function returnName ($id) {
		return ($this -> returnItem ($id, 'name'));
	}

	function returnOldestPurchase () {
		// Return the earliest known homeowner purchase.
	    $query = "SELECT purchasedate FROM $this->table 
	              ORDER BY purchasedate ASC
	              LIMIT 1";
		// Execute the query
		return ($this -> itemQuery ($query));
	}

	function returnOwner ($date, $lot_id) {
		// Return the homeowner on a particular date.
		// Formulate the SQL query
	    $query = "SELECT id from $this->table
	              WHERE (purchasedate <= '$date' AND (saledate = '1970-01-01' OR saledate >= '$date')) AND
	              lot_id = '$lot_id'";
		// Execute the query
		$temp = $this -> listSimpleQuery ($query);
		return ($temp[0]);
	}

	function returnPreviousOwnerId ($lot_id) {
		$query = "SELECT id FROM $this->table WHERE lot_id='$lot_id' ORDER BY purchasedate DESC LIMIT 1, 1";
		return ($this -> itemQuery ($query));
	}
	
	function returnPurchaseDate ($id) {
		return ($this -> returnItem ($id, 'purchasedate'));
	}

	function returnPaymentPlanDetails ($id) {
		return ($this -> returnItem ($id, 'payment_plan_details'));
	}

	function returnPaymentPlanDate ($id) {
		return ($this -> returnItem ($id, 'payment_plan_date'));
	}

	function returnResidence_Mailing ($id) {
		return ($this -> returnItem ($id, 'residence_mailing'));
	}

	function returnSaleDate ($id) {
		return ($this -> returnItem ($id, 'saledate'));
	}

	function returnState ($id) {
		return ($this -> returnItem ($id, 'state'));
	}

	function returnTelephoneFax ($id) {
		return ($this -> returnItem ($id, 'telephone_fax'));
	}

	function returnTelephoneHome ($id) {
		return ($this -> returnItem ($id, 'telephone_home'));
	}

	function returnTelephoneMobile ($id) {
		return ($this -> returnItem ($id, 'telephone_mobile'));
	}

	function returnTelephoneWork ($id) {
		return ($this -> returnItem ($id, 'telephone_work'));
	}

	function returnZipcode ($id) {
		return ($this -> returnItem ($id, 'zipcode'));
	}

}

?>