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

// Is the current user allowed to add / edit articles?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) || isset ($_POST['cancel']) || isset ($_POST['delete']) || isset ($_POST['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'attachments.php');
		
		$attachment_class = new HOAM_attachments ('attachments');

		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['attachment_information'])) {
			unset ($_SESSION['attachment_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the action, send them back to the main page
			header ('Location: /website/attachment/');
		} elseif (isset ($_POST['delete'])) {
			// NOTE! This only deletes the database entry, it doesn't actually
			// delete the attached file from the file system.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_delete'], $_SESSION['current_user']['id']) OR
				$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
				$trusted_id = (string) $attachment_class -> superClean ($_POST['attachment|id'], $attachment_class -> returnColumnSize ('id'));
				$attachment = $attachment_class -> listAll ($trusted_id);
				if (unlink (HOAM_PATH . HOAM_attachmentReturnPath ($attachment[0]['id']))) {
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['file'], $attachment[0]['filename']),
										HOAM_MESSAGE_NOTICE,
										array ('flags' => $_HOAM_setting ['log']['flags']['attachment'],
												'owner_id' => $attachment[0]['id']));
					if ($_HOAM_setting ['attachment']['preview']['enable']) {
						$preview = HOAM_attachmentReturnPreviewPath ($attachment[0]['id']);
						if ($preview) {
							unlink (HOAM_PATH . $preview);
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['preview'], $attachment[0]['filename']),
												HOAM_MESSAGE_NOTICE,
												array ('flags' => $_HOAM_setting ['log']['flags']['attachment'],
														'owner_id' => $attachment[0]['id']));
						}
					}
				} else {
					// Error Removing Item
					header ('Location: /error/remove/');
					exit ();
				}
				if ($attachment_class -> deleteId ($attachment[0]['id'])) { 
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['database'], $attachment[0]['id']),
										HOAM_MESSAGE_NOTICE,
										array ('flags' => $_HOAM_setting ['log']['flags']['budget_vendor'],
												'owner_id' => $vendor_id));
					// Send the user back to the attachment administration page.
					header ('Location: /website/attachment/');
					exit ();
				} else {
					// Error Removing Item
					header ('Location: /error/remove/');
					exit ();
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
				exit ();
			}
		} else {
			// If we're still here, then we must be adding or modifying an existing article.
			// Start populating the news_information array, and check for errors.
			$_SESSION['attachment_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			if (isset ($_POST['attachment|id'])) {
				$_SESSION['attachment_information']['trusted_attachment|id'] = (string) $attachment_class -> superClean ($_POST['attachment|id'], $attachment_class -> returnColumnSize ('id'));
				if ($attachment_class -> idExists ($_SESSION['attachment_information']['trusted_attachment|id'])) {
				} else {
					// The id is unknown.
					$_SESSION ['error_class_item'] -> addError ('attachment|id', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['attachment|description'])) {
				$_SESSION['attachment_information']['trusted_attachment|description'] = (string) $attachment_class -> superClean ($_POST['attachment|description'], $attachment_class -> returnColumnSize ('description'));
				// Make sure the description isn't too short.
				if (mb_strlen ($_SESSION['attachment_information']['trusted_attachment|description']) >= $_HOAM_setting ['attachment']['minimum_description_length']) {
				} else {
					// The description is too short.
					$_SESSION ['error_class_item'] -> addError ('attachment|description', sprintf ($_HOAM_language ['errors']['attachment']['description']['too-short'], $_HOAM_setting ['attachment']['minimum_description_length']));
				}
			}

// echo print_r ($_POST); print_r ($_SESSION['attachment_information']); $_SESSION['error_class_item'] -> errorCount; print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /website/attachment/add/');
				} else {
					header ('location: /website/attachment/edit/' . $_SESSION ['attachment_information']['trusted_attachment|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST['add'])) {
					$id = $attachment_class -> add ($_SESSION['current_user']['id']);
					if ($id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						$_HOAM_log -> add ($_HOAM_language ['log']['attachment']['created'],
											HOAM_MESSAGE_INFORMATIONAL,
											array ('flags' => $_HOAM_setting ['log']['flags']['attachment'],
													'owner_id' => $id));
					}
				} else {
					$id = $_SESSION['attachment_information']['trusted_attachment|id'];
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['modified'], $attachment_class -> returnFilename ($id)),
										HOAM_MESSAGE_INFORMATIONAL,
										array ('flags' => $_HOAM_setting ['log']['flags']['attachment'],
												'owner_id' => $id));
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				if (is_uploaded_file ($_FILES['attachment|file']['tmp_name'])) {
					$year = date ('Y');
					$file_path = HOAM_PATH_ATTACHMENT . $year . '/' . date ($_HOAM_country ['date']['format_mysql']);
					if (! file_exists ($file_path)) {
						mkdir ($file_path, 0700, TRUE);
					}
					$new_name = HOAM_attachmentGenerateName ($_FILES['attachment|file']['name']);
					move_uploaded_file ($_FILES['attachment|file']['tmp_name'], $file_path . '/' . $id . '_' . $new_name);
					chmod ($file_path . '/' . $id . '_' . $new_name, 0600);
					$attachment_class -> changeFileName ($id, $_FILES['attachment|file']['name']);
					$attachment_class -> changeMime ($id, $_FILES['attachment|file']['type']);
					if (mb_substr ($_FILES['attachment|file']['type'], 0, 5) == 'image') {
						$img_info = getimagesize ($file_path . '/' . $id . '_' . $new_name);
						$attachment_class -> changeHeight ($id, $img_info[1] );
						$attachment_class -> changeWidth ($id, $img_info[0] ); 
					}
					$attachment_class -> changeDescription ($id, $_SESSION['attachment_information']['trusted_attachment|description']);
					$attachment_class -> changeSize ($id, $_FILES['attachment|file']['size']);
				} elseif (isset ($_POST['modify'])) {
					$attachment_class -> changeDescription ($id, $_SESSION['attachment_information']['trusted_attachment|description']);
				}
				
				/*************************************************************************/

				// Clear the error_class_item variable since everything worked ok.
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}
				// Clear the news_information variable since everything worked ok.
				if (isset ($_SESSION['attachment_information'])) {
					unset ($_SESSION['attachment_information']);
				}

				header ('Location: /website/attachment/list/');
			}
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're being asked to perform
		header ('Location: /error/unknown_action/');
		exit ();
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

?>
