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

session_start ();

$group_members_class = new HOAM_group_members ('group_members');

if (isset ($_SESSION['current_user']['id']) AND
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['registered'], $_SESSION['current_user']['id'])) {
		
	if (isset ($_POST['cancel']) OR isset ($_POST['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'forms.php');
		require_once (HOAM_PATH_FUNCTION . 'user.php');
	
		$user_class = new HOAM_user ('users');

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

		if (isset ($_POST['cancel'])) {
			$log_id = $_HOAM_log -> add (sprintf ($_HOAM_language ['log']['user']['password']['cancelled'], $user_class -> returnAccountName ($_SESSION['current_user']['id'])),
										HOAM_MESSAGE_WARNING,
										array ('owner_id' => $_SESSION['current_user']['id'],
												'flags' => $_HOAM_setting ['log']['flags']['user']));
			header ('Location: /');
			exit ();
		} else {
			// If we're still here, then we must be adding or modifying an existing
			// user. Start populating the user_information array, and check for
			// errors.
			$_SESSION['user_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');

			// Verify that the old password is correct.
			if (isset ($_POST['user|password|old'])) {
				$_SESSION['user_information']['trusted_user|password|old'] = (string) $user_class -> superClean ($_POST['user|password|old'], $user_class -> returnColumnSize ('password'));
				if (!$user_class -> authenticateUser ($user_class -> returnAccountName ($_SESSION['current_user']['id']), $_SESSION['user_information']['trusted_user|password|old'])) {
					// The password given was incorrect.
					$_SESSION['error_class_item'] -> addError ('user|password|old', $_HOAM_language ['errors']['user']['password']['incorrect']);
				}
			}
			if (isset ($_POST['user|password|new'])) {
				$_SESSION['user_information']['trusted_user|password|new'] = (string) $user_class -> superClean ($_POST['user|password|new'], $user_class -> returnColumnSize ('password'));
				if (mb_strlen ($_SESSION['user_information']['trusted_user|password|new']) < $_HOAM_setting ['user']['minimum_password_length']) {
					$_SESSION['error_class_item'] -> addError ('user|password|new', sprintf ($_HOAM_language ['errors']['user']['password']['too-short'], $_HOAM_setting ['user']['minimum_password_length']));
				}
			}
			if (isset ($_POST['user|password|verify'])) {
				$_SESSION['user_information']['trusted_user|password|verify'] = (string) $user_class -> superClean ($_POST['user|password|verify'], $user_class -> returnColumnSize ('password'));
				if (strcmp ($_SESSION['user_information']['trusted_user|password|new'], $_SESSION['user_information']['trusted_user|password|verify'])) {
					$_SESSION['error_class_item'] -> addError ('user|password|verify', $_HOAM_language ['errors']['user']['password']['not-match']);
				}
			}
			if (isset ($_POST['user|password|hint-name'])) {
				$_SESSION['user_information']['trusted_user|password|hint-name'] = (string) $user_class -> superClean ($_POST['user|password|hint-name'], $user_class -> returnColumnSize ('password_hint_name'));
			}
			if (isset ($_POST['user|password|hint'])) {
				$_SESSION['user_information']['trusted_user|password|hint'] = (string) $user_class -> superClean ($_POST['user|password|hint'], $user_class -> returnColumnSize ('password_hint'));
				if (mb_strlen ($_SESSION['user_information']['trusted_user|password|hint']) < $_HOAM_setting ['user']['minimum_password_hint_length']) {
					$_SESSION['error_class_item'] -> addError ('user|password|hint', sprintf ($_HOAM_language ['errors']['user']['password']['hint']['too-short'], $_HOAM_setting ['user']['minimum_password_hint_length']));
				}

			}
		}

// print_r ($_POST); print_r ($_SESSION['user_information']); $_SESSION['error_class_item'] -> errorCount; print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();	

		// Are there any errors that the current user needs to fix before
		// committing the changes?
		if ($_SESSION['error_class_item'] -> errorCount > 0) {
			header ('Location: /user/password_age/');
		} else {
			// Finally! We have good data and can start modifying the database.
			$user_id = $_SESSION['current_user']['id'];

			$user_class -> changePassword ($user_id, $_SESSION['user_information']['trusted_user|password|new']);
			// Set the password age to today
			$user_class -> changePasswordAge ($user_id, date ($_HOAM_country ['date']['format_mysql']));
			$user_class -> changePasswordHintName ($user_id, $_SESSION['user_information']['trusted_user|password|hint-name']);
			$user_class -> changePasswordHint ($user_id, $_SESSION['user_information']['trusted_user|password|hint']);
			$user_class -> changeDateModified ($user_id);

			// Clear the error_class_item and user_information variables
			// since everything worked ok.
			unset ($_SESSION['user_information']);
			unset ($_SESSION['error_class_item']);
			header ('Location: /');
		}
	}
} else {
	// We don't know what action (add / cancel / delete / modify) we're being
	// asked to perform
	header ('Location: /error/unknown_action/');
}

exit ();

?>
