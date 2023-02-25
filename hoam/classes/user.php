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

class HOAM_user extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function accountExists ($accountname, $return_id = FALSE) {
		global $_HOAM_language;
		// Does the username given exist in the database?
		$query = "SELECT id FROM $this->table
					WHERE accountname LIKE '$accountname'";
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
			// If we receive any result other than 1, the user does not exist,
			// or there is a problem with the database.
			if (mysqli_affected_rows ($this -> connection) == 1) {
				if ($return_id == FALSE) {
					return TRUE;
				} else {
					$row = mysqli_fetch_array ($result, MYSQLI_ASSOC);
					return $row['id'];
				}
			} else {
				return FALSE;
			}
		}
	}
	
	function authenticateUser ($accountname, $password) {
		global $_HOAM_log;
		global $_HOAM_setting;
		$id = $this -> accountExists ($accountname, TRUE);
		if ($id) {
			// Determine the function used to encrypt the user's password.
			if ($this -> isBitSet ($id, $_HOAM_setting ['user']['flags']['password_old'])) {
				// Use the older method.
				$crypted_password = $this -> encryptPasswordOld ($accountname, $password);
			} else {
				// Use the newer method.
				$crypted_password = $this -> encryptPassword ($accountname, $password);
			}
			$query = "SELECT * FROM $this->table
					WHERE password = '$crypted_password'
					HAVING accountname = '$accountname'";
			
			$result = mysqli_query ($this -> connection, $query);
			if (HOAM_DEBUG) {
				$_HOAM_log -> add ('Could not authenticate user, query was "' . $query . '"', HOAM_MESSAGE_WARNING);
			}
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
				// Did we receive one result back? We should always only have
				// one result unless there's database corruption or some type
				// of hack attempt.
				if (mysqli_affected_rows ($this -> connection) == 1) {
					$this -> id = $id;
					// If the user is using the old method of encryption, force
					// a reencrypt to the new method.
					if ($this -> isBitSet ($id, $_HOAM_setting ['user']['flags']['password_old'])) {
						$this -> changePassword ($id, $password);
					}
					return TRUE;
				} else {
					// We didn't receive any rows...
					return FALSE;
				}
			}
		} else {
			return FALSE;
		}
	}
	
	function changeAccountname ($id, $value) {
		return ($this -> changeString ($id, 'accountname', $value));
	}
	
	function changeComments ($id, $value) {
		return ($this -> changeString ($id, 'comments', $value));
	}

	function changeEmail ($id, $value) {
		return ($this -> changeString ($id, 'email', $value));
	}
	
	function changeFirstname ($id, $value) {
		return ($this -> changeString ($id, 'firstname', $value));
	}
	
	function changeHomeownerId ($id, $homeowner) {
		return ($this -> changeString ($id, 'homeowner_id', $homeowner));
	}
	
	function changeLastlogin ($id, $value) {
		return ($this -> changeString ($id, 'datelastlogin', $value));
	}
	
	function changeLastname ($id, $value) {
		return ($this -> changeString ($id, 'lastname', $value));
	}
	
	function changePassword ($id, $password){
		global $_HOAM_setting;
		
		$crypted_password = $this -> encryptPassword ($this -> returnAccountname ($id), $password);
		// Since we're resetting the password, we want to make sure we update
		// the user account in case it was using an older password method.
		$this -> clearBit ($id, $_HOAM_setting ['user']['flags']['password_old']);
		return ($this -> changeString ($id, 'password', $crypted_password));
	}
	
	function changePasswordAge ($id, $value) {
		return ($this -> changeString ($id, 'password_age', $value));
	}
	
	function changePasswordHint ($id, $value) {
		return ($this -> changeString ($id, 'password_hint', $value));
	}

	function changePasswordHintName ($id, $value) {
		return ($this -> changeString ($id, 'password_hint_name', $value));
	}
	
	function changePreference ($id, $value) {
		return ($this -> changeInt ($id, 'preference', $value));
	}

	function changePreferenceEditCol ($id, $value) {
		return ($this -> changeInt ($id, 'preference_edit_col', $value));
	}

	function changePreferenceEditRow ($id, $value) {
		return ($this -> changeInt ($id, 'preference_edit_row', $value));
	}

	function changePreferenceItems ($id, $value) {
		return ($this -> changeInt ($id, 'preference_items', $value));
	}

	function encryptPassword ($accountname, $password) {
		// Get the salt from the user's password
		$salt = mb_substr ($password, (mb_strlen ($password) / 2 + 1), 3);
		
		// Encrypt the password we were given and send it back
		return (crypt ($password, $salt));
	}
	
	function encryptPasswordOld ($accountname, $password) {
		global $_HOAM_log;
		$backtrace = debug_backtrace();
		$message = PHP_EOL . "Backtrace Function :: " . $backtrace[2]['function'];
		$message .= PHP_EOL . "Backtrace File :: " . $backtrace[0]['file'];
		$message .= PHP_EOL . "Backtrace Line :: " . $backtrace[0]['line'];
		$_HOAM_log -> add ('DEPRECATED call to ' . __CLASS__ . ' -> ' . __FUNCTION__ . $message, HOAM_MESSAGE_WARNING);
		unset ($backtrace);
		unset ($message);
		
		// Get the two character salt from the username given
		// NOTE! In order to make sure that we always get the same salt even
		// when the user may enter their username in different upper / lower
		// cases, we force the lowercase version.
		$salt = mb_substr (mb_strtolower ($accountname), 0, 2);
		
		// Encrypt the password we were given and send it back
		return (crypt ($password, $salt));
	}
	
	function ListPasswordHintNames ($number = 0, $offset = 0) {
		$query = "SELECT DISTINCT password_hint_name FROM $this->table";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function returnAccountName ($id) {
		return ($this -> returnItem ($id, 'accountname'));
	}
	
	function returnComments ($id) {
		return ($this -> returnItem ($id, 'comments'));
	}
	
	function returnEmail ($id) {
		return ($this -> returnItem ($id, 'email'));
	}
	
	function returnId ($accountname = NULL) {
		if ($accountname == NULL) {
			return $this -> id;
		} else {
			return ($this -> accountExists ($accountname, TRUE));
		}
	}
	
	function returnFirstname ($id) {
		return ($this -> returnItem ($id, 'firstname'));
	}
	
	function returnHomeownerId ($id) {
		return ($this -> returnItem ($id, 'homeowner_id'));
	}

	function returnLastLogin ($id) {
		return ($this -> returnItem ($id, 'datelastlogin'));
	}

	function returnLastname ($id) {
		return ($this -> returnItem ($id, 'lastname'));
	}
	
	function returnMaiden ($id) {
		return ($this -> returnItem ($id, 'maiden'));
	}
	
	function returnPassword ($id) {
		return ($this -> returnItem ($id, 'password'));
	}

	function returnPasswordAge ($id) {
		return ($this -> returnItem ($id, 'password_age'));
	}

	function returnPasswordHint ($id) {
		return ($this -> returnItem ($id, 'password_hint'));
	}

	function returnPasswordHintName ($id) {
		return ($this -> returnItem ($id, 'password_hint_name'));
	}
	
	function returnPreference ($id) {
		return ($this -> returnItem ($id, 'preference'));
	}

	function returnPreferenceEditCol ($id) {
		return ($this -> returnItem ($id, 'preference_edit_col'));
	}
	
	function returnPreferenceEditRow ($id) {
		return ($this -> returnItem ($id, 'preference_edit_row'));
	}
	
	function returnPreferenceItems ($id) {
		return ($this -> returnItem ($id, 'preference_items'));
	}
	
	function returnPreferenceAcronyms ($id) {
		global $_HOAM_setting;
		$acronyms = $this -> bitCompare ($_HOAM_setting ['user']['flags']['acronyms'], 'preference', $id, 'id');
		if ($acronyms[0] == $_HOAM_setting ['user']['flags']['acronyms']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function returnPreferenceCensor ($id) {
		global $_HOAM_setting;
		$censor = $this -> bitCompare ($_HOAM_setting ['user']['flags']['censor'], 'preference', $id, 'id');
		if ($censor[0] == $_HOAM_setting ['user']['flags']['censor']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function returnPreferenceHolidays ($id) {
		global $_HOAM_setting;
		$holidays = $this -> bitCompare ($_HOAM_setting ['user']['flags']['holidays'], 'preference', $id, 'id');
		if ($holidays[0] == $_HOAM_setting ['user']['flags']['holidays']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function returnPreferenceLinks ($id) {
		global $_HOAM_setting;
		$links = $this -> bitCompare ($_HOAM_setting ['user']['flags']['convert_links'], 'preference', $id, 'id');
		if ($links[0] == $_HOAM_setting ['user']['flags']['convert_links']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function returnPreferenceSmileys ($id) {
		global $_HOAM_setting;
		$smiley = $this -> bitCompare ($_HOAM_setting ['user']['flags']['smileys'], 'preference', $id, 'id');
		if ($smiley[0] == $_HOAM_setting ['user']['flags']['smileys']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	}

?>