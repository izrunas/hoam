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

if (isset ($_POST['user|accountname']) AND isset ($_POST['user|hash']) AND isset ($_POST['user|hint'])) {
	$group_members_class = new HOAM_group_members ('group_members');
	$user_class = new HOAM_user ('users');
	$validation_class = new HOAM_user_validation ('user_validation');

	// Get rid of any old errors, and start over.
	if (isset ($_SESSION['error_class_item'])) {
		unset ($_SESSION['error_class_item']);
	}

	$trusted_accountname = $user_class -> superClean ($_POST['user|accountname'], $user_class -> returnColumnSize ('accountname'));
	$trusted_answer = $user_class -> superClean ($_POST['user|hint'], $user_class -> returnColumnSize ('password_hint'));
	$trusted_hash = $validation_class -> superClean ($_POST['user|hash'], $validation_class -> returnColumnSize ('hash'));
	if ($user_class -> accountExists ($trusted_accountname)) {
		$validation_id = $validation_class -> returnIdWithHash ($trusted_hash);
		if ($validation_id) {
			$trusted_id = $user_class -> returnId ($trusted_accountname);
			if ($trusted_id == $validation_class -> returnUserId ($validation_id)) {
				if (mb_strtolower ($trusted_answer) === mb_strtolower ($user_class -> returnPasswordHint ($trusted_id))) {
					// OK, at this point we've verified that the account exists,
					// that the hash in question is for the user requesting the
					// change, and security answer is correct.  

					$_HOAM_log -> add ('User "' . $trusted_accountname . '" validated their email address.', HOAM_MESSAGE_NOTICE);
					// Add user to Registered Users group if not already a
					// member
					if (!$group_members_class -> isMember ($trusted_id, $_HOAM_setting ['group']['ids']['registered'])) {
						$group_members_class -> addUserToGroup ($trusted_id, $_HOAM_setting ['group']['ids']['registered']);
					}
					// Mark the user's account as having validated their email
					$user_class -> setBit ($trusted_id, $_HOAM_setting ['user']['flags']['email_validated']);
					// Delete the validation entry from the database now that it
					// has been used.
					$validation_class -> deleteId ($validation_id);
					header ('Location: /user/validated/');
					exit ();
				} else {
					$_SESSION['error_class_item'] = new HOAM_errors ('errors');

					$_SESSION['error_class_item'] -> addError ('user|hint', $_HOAM_language ['errors']['user']['password']['hint']['incorrect']);
					header ('Location: /user/validate/' . $trusted_hash);
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
