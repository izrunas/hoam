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

class HOAM_lot extends HOAM_db {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function add ($user_id, $id = NULL) {
		global $_HOAM_language, $_HOAM_setting;
		// If this is called by a routine running automatically, record the
		// system account as the user.
		if (empty ($user_id)) {
			$user_id = $_HOAM_setting ['user']['ids']['system'];
		}
		
		if ($id == NULL) {
			// In general, it's preferable to use the county lot id# rather
			// than our own. But, if we don't have that, just generate a
			// random int.
			$id = mt_rand (100000, 999999);
		}

		$query = "INSERT INTO $this->table (id, datecreated, user_id)
					VALUES ('$id', now(), '$user_id')";

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
			return ($id);
		}
		// If the add failed, then we should have already died.  But, just
		// to cover ourselves, let's return a failure.
		return FALSE;
	}
	
	function changeAddress ($id, $item) {
		return ($this -> changeInt ($id, 'address', $item));
	}
	
	function changeBlock ($id, $item) {
		return ($this -> changeString ($id, 'block', $item));
	}
	
	function changeBuilding ($id, $item) {
		return ($this -> changeString ($id, 'building', $item));
	}
	
	function changeComments ($id, $item) {
		return ($this -> changeString ($id, 'comment', $item));
	}

	function changeLatitude ($id, $item) {
		return ($this -> changeFloat ($id, 'latitude', $item));
	}

	function changeLongitude ($id, $item) {
		return ($this -> changeFloat ($id, 'longitude', $item));
	}

	function changeLot ($id, $item) {
		return ($this -> changeInt ($id, 'lot', $item));
	}

	function changePlat ($id, $item) {
		return ($this -> changeInt ($id, 'plat', $item));
	}

	function changeSqft ($id, $item) {
		return ($this -> changeInt ($id, 'sqft', $item));
	}

	function changeStreet ($id, $item) {
		return ($this -> changeString ($id, 'street', $item));
	}
	
	function changeSuite ($id, $item) {
		return ($this -> changeString ($id, 'suite', $item));
	}
	
	function listAddresses ($street = NULL) {
		// List all of the distinct addresses if we're given a $street name,
		// then return the addresses that on that street.
		if (isset ($street)) {
			$query = "SELECT DISTINCT address, building, street FROM $this->table
						WHERE street = '$street'
						ORDER BY address ASC, building, suite";
		} else {
			$query = "SELECT DISTINCT address, building, suite FROM $this->table
						ORDER BY address ASC, building, suite";
		}
		return ($this -> listSimpleQuery ($query));
	}
	
	function listAllHomes ($number = 0, $offset = 0) {
		// Return a list of all homes in order
		$query = "SELECT id FROM $this->table
					ORDER BY street, address, building, suite";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function listByAddress ($address = null) {
		// Return details for all of the lots. If we're given an $id number,
		// then just return the info for that one lot.
		if (isset ($street)) {
			if ($this -> idExists ($id)) {
				$query = "SELECT id FROM $this->table
							WHERE address = '$address'";
			} else {
				return FALSE;
			}
		} else {
			$query = "SELECT id FROM $this->table
						ORDER BY address, street, building, suite";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function listByStreet ($street = null, $number = 0, $offset = 0) {
		if (isset ($street)) {
			$query = "SELECT id FROM $this->table
						WHERE street = '$street'
						ORDER BY address ASC, building, suite";
		} else {
			$query = "SELECT id FROM $this->table
						ORDER BY address ASC, building, suite";
		}
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}
	
	function listStreets ($address = NULL) {
		// List all of the distinct street names. If we're given an $address
		// number, then return the street names that include that address.
		if (isset ($address)) {
			$query = "SELECT DISTINCT street FROM $this->table
						HAVING address = '$address'
						ORDER BY street, address ASC";
		} else {
			$query = "SELECT DISTINCT street FROM $this->table
						ORDER BY street ASC";
		}
		return ($this -> listSimpleQuery ($query));
	}
	
	function listStreetAll ($street = NULL) {
		// Return information for every property on a street.
		if (isset ($street)) {
			$query = "SELECT * FROM $this->table
						WHERE street = '$street'
						ORDER BY address ASC, building, suite";
		} else {
			$query = "SELECT address, id FROM $this->table
						ORDER BY address ASC, building, suite";
		}
		return ($this -> listQuery ($query));
	}
	
	function returnAddress ($id) {
		return ($this -> returnItem ($id, 'address'));
	}

	function returnBlock ($id) {
		return ($this -> returnItem ($id, 'block'));
	}
	
	function returnBuilding ($id) {
		return ($this -> returnItem ($id, 'building'));
	}
	
	function returnCountAll () {
		return ($this -> returnCountQuery ());
	}

	function returnCountStreet ($item) {
		return ($this -> returnCountQuery ("WHERE street = '$item'"));
	}

	function returnComments ($id) {
		return ($this -> returnItem ($id, 'comment'));
	}

	function returnLatitude ($id) {
		return ($this -> returnItem ($id, 'latitude'));
	}
	
	function returnLongitude ($id) {
		return ($this -> returnItem ($id, 'longitude'));
	}
	
	function returnLot ($id) {
		return ($this -> returnItem ($id, 'lot'));
	}
	
	function returnPlat ($id) {
		return ($this -> returnItem ($id, 'plat'));
	}
	
	function returnSqft ($id) {
		return ($this -> returnItem ($id, 'sqft'));
	}
	
	function returnStreet ($id) {
		return ($this -> returnItem ($id, 'street'));
	}
	
	function returnSuite ($id) {
		return ($this -> returnItem ($id, 'suite'));
	}
	
	function returnId ($address, $street, $building = '', $suite = '') {
		$query = "SELECT id FROM $this->table
					WHERE address = '$address'
					AND street = '$street'";
		if (!empty ($building)) {
			$query .= " AND building = '$building'";
		}
		if (!empty ($suite)) {
			$query .= " AND suite = '$suite'";
		}
		$result = $this -> listSimpleQuery ($query);
		return ($result[0]);
	}

}

?>