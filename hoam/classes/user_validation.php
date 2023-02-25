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

class HOAM_user_validation extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	// Note, we do something a little different with the user_id column in this
	// class than we normally do. In this case, we're using it to determine the
	// user_id of the user with the request, rather than the user that created
	// the database entry.
	// Overall it ends up the same thing, but we're providing the user_id when
	// calling -> Add()
	
	function changeHash ($id, $value) {
		return ($this -> changeString ($id, 'hash', $value));
	}

	function returnHash ($id) {
		return ($this -> returnItem ($id, 'hash'));
	}

	function returnIdWithHash ($value) {
		// Does the username given exist in the database?
		$query = "SELECT id FROM $this->table
					WHERE hash='$value'";
		$result = mysqli_query ($this -> connection, $query);
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
			// If we receive any result other than 1, the hash does not exist,
			// or there is a problem with the database.
			if (mysqli_affected_rows ($this -> connection) == 1) {
				$row = mysqli_fetch_array ($result, MYSQLI_ASSOC);
				return $row['id'];
			} else {
				return FALSE;
			}
		}
	}
	
}
?>