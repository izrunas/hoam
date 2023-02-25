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

require_once ('../../hoam-startup.php');

session_start();

if (isset ($_POST['user|accountname']) AND isset ($_POST['user|hash'])) {
	$group_members_class = new HOAM_group_members ('group_members');
	$user_class = new HOAM_user ('users');
	$validation_class = new HOAM_user_validation ('user_validation');

	// If this is the second (or greater) time through the script (ie, there
	// were errors that had to be fixed before committing the changes) get rid
	// of the old cached information the user entered, we're going to populate
	// it with information just received from the form posting.
	if (isset ($_SESSION['user_information'])) {
		unset ($_SESSION['user_information']);
	}
		// Get rid of any old errors, and start over.
	if (isset ($_SESSION['error_class_item'])) {
		unset ($_SESSION['error_class_item']);
	}

	$_SESSION['user_information'] = array ();
	$_SESSION['error_class_item'] = new HOAM_errors ('errors');

	$trusted_accountname = $user_class -> superClean ($_POST['user|accountname'], $user_class -> returnColumnSize ('accountname'));
	$_SESSION['user_information']['trusted_user|hash'] = $validation_class -> superClean ($_POST['user|hash'], $validation_class -> returnColumnSize ('hash'));
	if ($user_class -> accountExists ($trusted_accountname)) {
		$validation_id = $validation_class -> returnIdWithHash ($_SESSION['user_information']['trusted_user|hash']);
		if ($validation_id) {
			$trusted_id = $user_class -> returnId ($trusted_accountname);
			if ($trusted_id == $validation_class -> returnUserId ($validation_id)) {
				if (isset ($_POST['user|password|new'])) {
					$_SESSION['user_information']['trusted_user|password|new'] = (string) $user_class -> superClean ($_POST['user|password|new'], $user_class -> returnColumnSize ('password'));
					if (mb_strlen ($_SESSION['user_information']['trusted_user|password|new']) > 0) {
						if (mb_strlen ($_SESSION['user_information']['trusted_user|password|new']) < $_HOAM_setting ['user']['minimum_password_length']) {
							$_SESSION['error_class_item'] -> addError ('user|password|new', sprintf ($_HOAM_language ['errors']['user']['password']['too-short'], $_HOAM_setting ['user']['minimum_password_length']));
						}
					} else {
						$_SESSION['error_class_item'] -> addError ('user|password|new', sprintf ($_HOAM_language ['errors']['user']['password']['too-short'], $_HOAM_setting ['user']['minimum_password_length']));
					}
				}
				if (isset ($_POST['user|password|verify'])) {
					$_SESSION['user_information']['trusted_user|password|verify'] = (string) $user_class -> superClean ($_POST['user|password|verify'], $user_class -> returnColumnSize ('password'));
					if (strcmp ($_SESSION['user_information']['trusted_user|password|new'], $_SESSION['user_information']['trusted_user|password|verify'])) {
						$_SESSION['error_class_item'] -> addError ('user|password|verify', $_HOAM_language ['errors']['user']['password']['not-match']);
					}
				}

// print_r ($_POST); print_r ($_SESSION['user_information']); $_SESSION['error_class_item'] -> errorCount; print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();	
				// Are there any errors that the current user needs to fix before
				// committing the changes?
				if ($_SESSION['error_class_item'] -> errorCount > 0) {
					// If we have an id, send the user back to that.
					header ('Location: /user/password_reset/' . $_SESSION['user_information']['trusted_user|hash'] . '/');
					exit ();
				} else {
					$user_class -> changePassword ($trusted_id, $_SESSION['user_information']['trusted_user|password|new']);
					// Set the password age to today
					$user_class -> changePasswordAge ($trusted_id, date ($_HOAM_country ['date']['format_mysql']));

					// Delete the validation entry from the database now that it
					// has been used.
					$validation_class -> deleteId ($validation_id);
					
					unset ($_SESSION['user_information']);
					unset ($_SESSION['error_class_item']);
					
					header ('Location: /user/validated/');
					exit ();
				}
			} else {
				header ('Location: /error/unknown_id/');
				exit ();
			}
		} else {
			header ('Location: /error/unknown_id/');
			exit ();
		}
	} else {
		header ('Location: /error/unknown_account/');
		exit ();
	}
}

exit ();

?>
