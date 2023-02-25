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
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['advertising_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) || isset ($_POST['cancel']) || isset ($_POST['delete']) || isset ($_POST['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'attachments.php');
		
		$advertising_class = new HOAM_advertising ('advertising');
		$attachment_class = new HOAM_attachments ('attachments');

		// If this is the second (or greater) time through the script (ie, there
		// were errors that had to be fixed before committing the changes) get
		// rid of the old cached information the user entered, we're going to
		// populate it with information just received from the form posting.
		if (isset ($_SESSION['advertising_information'])) {
			unset ($_SESSION['advertising_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the action, send them back to the main page
			header ('Location: /website/advertising/');
		} elseif (isset ($_POST['delete'])) {
			// NOTE! This will only delete the advertisement, not any associated
			// attachments.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['attachment_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				$trusted_id = (string) $advertising_class -> superClean ($_POST['advertising|id'], $advertising_class -> returnColumnSize ('id'));
				$url = $advertising_class -> returnURL ($$trusted_id);
				$result = $advertising_class -> deleteId ($trusted_id);
				if ($result) {
					// Send the user back to the attachment administration page.
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['advertisement']['deleted'], $url),
						HOAM_MESSAGE_NOTICE,
						array ('owner_id' => $trusted_id,
						'flags' => $_HOAM_setting ['log']['flags']['advertisement']));
					unset ($trusted_id);
					header ('Location: /website/advertising/');
				} else {
					// Error Removing Item
					header ('Location: /error/remove/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// If we're still here, then we must be adding or modifying an
			// existing advertisement.
			// Start populating the advertising_information array, and check for
			// errors.
			$_SESSION['advertising_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
			if (isset ($_POST['advertising|id'])) {
				$_SESSION['advertising_information']['trusted_advertising|id'] = (string) $advertising_class -> superClean ($_POST['advertising|id'], $advertising_class -> returnColumnSize ('id'));
				if ($advertising_class -> idExists ($_SESSION['advertising_information']['trusted_advertising|id'])) {
				} else {
					// The id is unknown.
					$_SESSION ['error_class_item'] -> addError ('advertising|id', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['advertising|description'])) {
				$_SESSION['advertising_information']['trusted_advertising|description'] = (string) $advertising_class -> superClean ($_POST['advertising|description'], $attachment_class -> returnColumnSize ('description'));
				// Make sure the description isn't too short.
				if (mb_strlen ($_SESSION['advertising_information']['trusted_advertising|description']) >= $_HOAM_setting ['attachment']['minimum_description_length']) {
				} else {
					// The description is too short.
					$_SESSION ['error_class_item'] -> addError ('advertising|description', sprintf ($_HOAM_language ['errors']['advertising']['description']['too-short'], $_HOAM_setting ['advertising']['minimum_description_length']));
				}
			}
			if (isset ($_POST['advertising|location'])) {
				$_SESSION['advertising_information']['trusted_advertising|location'] = (int) $advertising_class -> superClean ($_POST['advertising|location'], 1);
				// Make sure the location is valid.
				if (($_SESSION['advertising_information']['trusted_advertising|location'] > 0) AND ($_SESSION['advertising_information']['trusted_advertising|location'] <= 4)) {
					switch ($_SESSION['advertising_information']['trusted_advertising|location']) {
						case 1:
							$_SESSION['advertising_information']['trusted_advertising|flags'] = $_HOAM_setting ['advertising']['flags']['position1'];
							break;
						case 2:
							$_SESSION['advertising_information']['trusted_advertising|flags'] = $_HOAM_setting ['advertising']['flags']['position2'];
							break;
						case 3:
							$_SESSION['advertising_information']['trusted_advertising|flags'] = $_HOAM_setting ['advertising']['flags']['position3'];
							break;
						case 4:
							$_SESSION['advertising_information']['trusted_advertising|flags'] = $_HOAM_setting ['advertising']['flags']['position4'];
							break;
					}
				} else {
					// The location is invalid.
					$_SESSION ['error_class_item'] -> addError ('advertising|location', $_HOAM_language ['errors']['generic']['unknown-id']);
				}
			}
			if (isset ($_POST['advertising|postend'])) {
				$_SESSION['advertising_information']['trusted_advertising|postend'] = (string) $advertising_class -> superClean ($_POST['advertising|postend'], $advertising_class -> returnColumnSize ('datepostend'));
				if (!empty ($_SESSION['advertising_information']['trusted_advertising|postend'])) {
					// Make sure the date isn't too short.
					if (mb_strlen ($_SESSION['advertising_information']['trusted_advertising|postend']) < $advertising_class -> returnColumnSize ('datepostend')) {
						$_SESSION['error_class_item'] -> addError ('advertising|postend', $_HOAM_language ['errors']['generic']['date-too-short']);
					}
					// Make sure we have a valid date
					if (checkdate (date ('m', strtotime ($_SESSION['advertising_information']['trusted_advertising|postend'])), date ('d', strtotime ($_SESSION['advertising_information']['trusted_advertising|postend'])), date ('Y', strtotime ($_SESSION['advertising_information']['trusted_advertising|postend'])))) {
					} else {
						$_SESSION['error_class_item'] -> addError ('advertising|postend', $_HOAM_language ['errors']['generic']['invalid-date']);
					}
					// Make sure the date isn't in the past
					if (date ('m/d/Y') >= $_SESSION['advertising_information']['trusted_advertising|postend']) {
						$_SESSION['error_class_item'] -> addError ('advertising|postend', $_HOAM_language ['errors']['advertising']['postend_past']);
					}
				} else {
					$_SESSION['error_class_item'] -> addError ('advertising|postend', $_HOAM_language ['errors']['generic']['date-too-short']);
				}
			}
			if (isset ($_POST['advertising|poststart'])) {
				$_SESSION['advertising_information']['trusted_advertising|poststart'] = (string) $advertising_class -> superClean ($_POST['advertising|poststart'], $advertising_class -> returnColumnSize ('datepoststart'));
				if (!empty ($_SESSION['advertising_information']['trusted_advertising|poststart'])) {
					// Make sure the date isn't too short.
					if (mb_strlen ($_SESSION['advertising_information']['trusted_advertising|poststart']) < $advertising_class -> returnColumnSize ('datepoststart')) {
						$_SESSION['error_class_item'] -> addError ('advertising|poststart', $_HOAM_language ['errors']['generic']['date-too-short']);
					}
					// Make sure we have a valid date
					if (checkdate (date ('m', strtotime ($_SESSION['advertising_information']['trusted_advertising|poststart'])), date ('d', strtotime ($_SESSION['advertising_information']['trusted_advertising|poststart'])), date ('Y', strtotime ($_SESSION['advertising_information']['trusted_advertising|poststart'])))) {
					} else {
						$_SESSION['error_class_item'] -> addError ('advertising|poststart', $_HOAM_language ['errors']['generic']['invalid-date']);
					}
					// Make sure the date isn't in the past
					if (date ('m/d/Y') > $_SESSION['advertising_information']['trusted_advertising|poststart']) {
						$_SESSION['error_class_item'] -> addError ('advertising|poststart', $_HOAM_language ['errors']['advertising']['poststart_past']);
					}
				} else {
					$_SESSION['error_class_item'] -> addError ('advertising|poststart', $_HOAM_language ['errors']['generic']['date-too-short']);
				}
			}
			// OK, one final date check. Make sure the post end isn't before the
			// post start, or visa versa.
			if (strtotime ($_SESSION['advertising_information']['trusted_advertising|postend']) <= strtotime ($_SESSION['advertising_information']['trusted_advertising|poststart'])) {
				$_SESSION['error_class_item'] -> addError ('advertising|postend', $_HOAM_language ['errors']['advertising']['postend_before_start']);
			}
			if (isset ($_POST['advertising|url'])) {
			$_SESSION['advertising_information']['trusted_advertising|url'] = (string) $advertising_class -> superClean ($_POST['advertising|url'], $advertising_class -> returnColumnSize ('url'));
			// Make sure the url isn't too short.
				if (mb_strlen ($_SESSION['advertising_information']['trusted_advertising|url']) >= $_HOAM_setting ['advertising']['minimum_url_length']) {
					if (!filter_var ($_SESSION['advertising_information']['trusted_advertising|url'], FILTER_VALIDATE_URL)) {
						$_SESSION['error_class_item'] -> addError ('advertising|url', $_HOAM_language ['errors']['generic']['invalid-url']);
					}
				} else {
					// The url is too short.
					$_SESSION ['error_class_item'] -> addError ('advertising|url', sprintf ($_HOAM_language ['errors']['advertising']['url']['too-short'], $_HOAM_setting ['advertising']['minimum_url_length']));
				}
			}
// echo print_r ($_POST); print_r ($_SESSION['advertising_information']); $_SESSION['error_class_item'] -> errorCount; print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before
			// committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /website/advertising/add/');
				} else {
					header ('location: /website/advertising/edit/' . $_SESSION ['advertising_information']['trusted_advertising|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the
				// database.
				if (isset ($_POST['add'])) {
					$_SESSION['advertising_information']['trusted_advertising|id'] = $advertising_class -> add ($_SESSION['current_user']['id']);
					if ($_SESSION['advertising_information']['trusted_advertising|id'] == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['advertisement']['created'], $_SESSION['advertising_information']['trusted_advertising|url']),
							HOAM_MESSAGE_NOTICE,
							array ('owner_id' => $_SESSION['advertising_information']['trusted_advertising|id'],
							'flags' => $_HOAM_setting ['log']['flags']['advertisement']));
					}
				} else {
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['advertisement']['modified'], $_SESSION['advertising_information']['trusted_advertising|url']),
						HOAM_MESSAGE_NOTICE,
						array ('owner_id' => $_SESSION['advertising_information']['trusted_advertising|id'],
						'flags' => $_HOAM_setting ['log']['flags']['advertisement']));
				}
				// By now, we've already verified that all of our data is
				// correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$advertising_class -> changeDatePostEnd ($_SESSION['advertising_information']['trusted_advertising|id'], date ('Y-m-d', strtotime ($_SESSION['advertising_information']['trusted_advertising|postend'])));
				$advertising_class -> changeDatePostStart ($_SESSION['advertising_information']['trusted_advertising|id'], date ('Y-m-d', strtotime ($_SESSION['advertising_information']['trusted_advertising|poststart'])));
				$advertising_class -> changeDescription ($_SESSION['advertising_information']['trusted_advertising|id'], $_SESSION['advertising_information']['trusted_advertising|description']);
				$advertising_class -> changeFlags ($_SESSION['advertising_information']['trusted_advertising|id'], $_SESSION['advertising_information']['trusted_advertising|flags']);
				$advertising_class -> changeURL ($_SESSION['advertising_information']['trusted_advertising|id'], $_SESSION['advertising_information']['trusted_advertising|url']);
				// Process any attachments
				foreach ($_FILES as $file) {
					if ($file['name'] != NULL) {
						// Import the attachment into the database
						$id = HOAM_attachmentAdd ($file,
							$file,
							$_SESSION['current_user']['id'],
							$_HOAM_setting ['attachment']['flags']['advertising'],
							$_SESSION['advertising_information']['trusted_advertising|description'],
							$_SESSION['advertising_information']['trusted_advertising|id']);
						if (!$id) {
							echo $_HOAM_language ['errors']['attachment']['import'];
						}
					}
				}

				/**************************************************************/

				// Clear the error_class_item variable since everything worked
				// ok.
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}
				// Clear the advertising_information variable since everything
				// worked ok.
				if (isset ($_SESSION['advertising_information'])) {
					unset ($_SESSION['advertising_information']);
				}

				header ('Location: /website/advertising/list/');
			}
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're
		// being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

?>
