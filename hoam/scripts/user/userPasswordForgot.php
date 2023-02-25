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

if (isset ($_POST['user|accountname'])) {
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

	$user_class = new HOAM_user ('users');

	$_SESSION['user_information']['trusted_user|accountname'] = $user_class -> superClean ($_POST['user|accountname'], $user_class -> returnColumnSize ('accountname'));
	if ($user_class -> accountExists ($_SESSION['user_information']['trusted_user|accountname'])) {
		$trusted_id = $user_class -> returnId ($_SESSION['user_information']['trusted_user|accountname']);
		// Need to add a check here that even if email validation isn't enabled
		// that we check to see whether the user had provided an email address
		// and if so, send the reset email to that address.
		if (is_true ($_HOAM_setting ['user']['email_validation'])) {
			// If configured, now send an email with a link to reset their
			// password.
			$validation_class = new HOAM_user_validation ('user_validation');
			
			// Delete any prior reset requests for the user so we don't have
			// duplicates
			$existing_list = $validation_class -> listAuthor ($trusted_id);
			if (is_array ($existing_list)) {
				foreach ($existing_list as $existing_requst) {
					$validation_class -> deleteId ($existing_request);
				}
			}
			unset ($existing_list);
			
			$validation_id = $validation_class -> add ($trusted_id);
			$validation_hash = md5 (mt_rand());
			$validation_class -> changeHash ($validation_id, $validation_hash);
				
				require_once (HOAM_PATH . '3rdparty/phpmailer/class.phpmailer.php');
				
				$wiki = new HOAM_wiki_parser ();
				$mail = new PHPMailer ();
							
				$mail -> IsSMTP (); // telling the class to use SMTP
				$mail -> SMTPDebug = HOAM_DEBUG; // Use the same setting as
											// HOAM
       				            	        // 1 = errors and messages
	                			            // 2 = messages only
			if ($_HOAM_setting ['email']['auth']) { // enable SMTP
													// authentication
				$mail -> SMTPAuth = TRUE;
				$mail -> Username = $wiki -> parse ($_HOAM_setting ['email']['username']);
				$mail -> Password = $_HOAM_setting ['email']['password'];
				// If we're authenticating, then we know we have a username.
				$mail -> SetFrom ($wiki -> parse ($_HOAM_setting ['email']['username']));
			} else {
				// Since we don't know that we have a username configured
				// (one would only be there if authentication was previously
				// configured and then disabled), use the address of the
				// management company.
				$mail -> SetFrom ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
			}
			$mail -> Host = $_HOAM_setting ['email']['server'];
			$mail -> Port = $_HOAM_setting ['email']['port'];
			$mail -> AddReplyTo ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
			$mail -> AddAddress ($user_class -> returnEmail ($trusted_id));
			$mail -> Subject = HOAM_returnWebsiteTitle () . ' :: ' . $_HOAM_language ['field_names']['user']['password']['reset'];

			$body = sprintf ($_HOAM_language ['help_pages']['email']['password_forgot'], $_SESSION['user_information']['trusted_user|accountname'], $validation_hash, $validation_hash, $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_ADDR']);
			if ($_HOAM_setting ['email']['do_not_reply']) {
				$body .= $_HOAM_language ['help_pages']['generic']['do_not_reply'];
			}
			$mail -> MsgHTML ($wiki -> parse ($body));
			$mail -> AltBody = $_HOAM_language ['field_names']['email']['text_only'];

			if (!$mail -> Send ()) {
				$_HOAM_log -> add ('PHPMailer Error: ' . $mail -> ErrorInfo, HOAM_MESSAGE_ERROR);
			} else {
				$_HOAM_log -> add ('User: email sent with password reset instructions to "' . $_SESSION['user_information']['trusted_user|accountname'] . '".', HOAM_MESSAGE_INFORMATIONAL);
				$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['user']['password']['reset']['email'], $_SESSION['user_information']['trusted_user|accountname']),
									HOAM_MESSAGE_NOTICE,
									array ('owner_id' => $trusted_id,
											'flags' => $_HOAM_setting ['log']['flags']['user']));
			}

			unset ($_SESSION['user_information']);
			unset ($_SESSION['error_class_item']);

			header ('Location: /user/password_reset_sent/');
			exit;
		} else {
			header ('Location: /user/password_reset_question/');
			exit;
		}
	} else {
		header ('Location: /error/unknown_account/');
		exit ();
	}
}

exit ();

?>
