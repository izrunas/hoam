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

// Is the current user allowed to add / edit violations?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_approve'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['users']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'attachments.php');
		require_once (HOAM_PATH_FUNCTION . 'image.php');

		$attachment_class = new HOAM_attachments ('attachments');
		$lot_class = new HOAM_lot ('lots');
		$violation_class = new HOAM_violation ('violations');
		$violation_category_class = new HOAM_violation_category ('violation_category');
		$violation_severity_class = new HOAM_violation_severity ('violation_severity');
		
		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['violation_information'])) {
			unset ($_SESSION['violation_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			header ('Location: /admin/violation/');
		} elseif (isset ($_POST['delete'])) {
			// Make sure the current user is the owner of the item, OR has admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['users']['ids']['root'] === $_SESSION['current_user']['id'])) {
				$violation_id = (string) $violation_class -> superClean ($_POST['violation|id'], $violation_class -> returnColumnSize ('id'));
				if ($violation_class -> idExists ($violation_id)) {
					$address = $violation_class -> returnLotId ($violation_id);
					if ($violation_class -> deleteId ($violation_id)) {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['violation']['deleted'], HOAM_returnAddress ($address)),
											HOAM_MESSAGE_NOTICE,
											array ('flags' => $_HOAM_setting ['log']['flags']['violation'],
													'owner_id' => $violation_id));
						header ('Location: /admin/violation/');
					} else {
						// Error Removing Item
						header ('Location: /error/remove/');
					}
				} else {
					// The violation couldn't be found
					header ('Location: /error/unknown_id/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// If we're still here, then we must be adding or modifying an existing violation.
			// Start populating the violation_information array, and check for errors.
			$_SESSION['violation_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			if (isset ($_POST['violation|id'])) {
				$_SESSION['violation_information']['trusted_violation|id'] = (string) $violation_class -> superClean ($_POST['violation|id'], $violation_class -> returnColumnSize ('id'));
				if ($violation_class -> idExists ($_SESSION['violation_information']['trusted_violation|id'])) {
				} else {
					// The violation is unknown.
					$_SESSION ['error_class_item'] -> addError ('violation|id', $_HOAM_language ['errors']['violation']['unknown_id']);
				}
			}
			if (isset ($_POST['violation|address'])) {
				$_SESSION['violation_information']['trusted_violation|address'] = (int) $violation_class -> superClean ($_POST['violation|address'], $violation_class -> returnColumnSize ('lot_id'));
				if ($lot_class -> idExists ($_SESSION['violation_information']['trusted_violation|address'])) {
				} else {
					// The violation is unknown.
					$_SESSION ['error_class_item'] -> addError ('violation|address', $_HOAM_language ['errors']['lot']['unknown_id']);
				}
			}
			// Update the violation category list.
			if (isset ($_POST['violation|assume_resolved'])) {
				$_SESSION['violation_information']['trusted_violation|assume_resolved'] = (int) $_POST['violation|assume_resolved'];
			} else {
				$_SESSION['violation_information']['trusted_violation|assume_resolved'] = 0;
			}
			if (isset ($_POST['violation|categories'])) {
				$_SESSION['violation_information']['trusted_violation|categories'] = array ();
				foreach ($_POST['violation|categories'] as $category) {
					// First, find out if the category id is valid
					$clean_category = $violation_category_class -> superClean ($category, $violation_category_class -> returnColumnSize ('id'));
					if ($violation_category_class -> idExists ($clean_category)) {
						array_push ($_SESSION['violation_information']['trusted_violation|categories'], $clean_category);
					} else {
						// The violation is unknown.
						$_SESSION ['error_class_item'] -> addError ('violation|categories', $_HOAM_language ['errors']['violation']['category']['unknown_id']);
					}
				}
				if (empty ($_SESSION['violation_information']['trusted_violation|categories'])) {
					$_SESSION ['error_class_item'] -> addError ('violation|categories', $_HOAM_language ['errors']['violation']['category']['none-selected']);
				}
			} else {
				$_SESSION ['error_class_item'] -> addError ('violation|categories', $_HOAM_language ['errors']['violation']['category']['none-selected']);
			}
			if (isset ($_POST['violation|severity|current'])) {
				$_SESSION['violation_information']['trusted_violation|severity|current'] = $violation_severity_class -> superClean ($_POST['violation|severity|current'], $violation_severity_class -> returnColumnSize ('id'));
				if ($violation_severity_class -> idExists ($_SESSION['violation_information']['trusted_violation|severity|current'])) {
				} else {
					// The severity is unknown.
					$_SESSION ['error_class_item'] -> addError ('violation|severity|current', $_HOAM_language ['errors']['violation']['severity']['unknown_id']);
				}
			}
			if (isset ($_POST['violation|original_letter'])) {
				// NOTE! This should normally never be allowed, because (in theory) once a violation has been
				// entered and the violation letter has been created, there should be assurances that the
				// letter has not been modified after it has been created and sent to the homeowner.
				//
				// It's in here right now primarily because debugging of the violation letter process is still
				// ongoing, and problems need to be fixed.
				$_SESSION['violation_information']['trusted_violation|original_letter'] = (string) $violation_class -> clean ($_POST['violation|original_letter'], $violation_class -> returnColumnSize ('original_letter'));
			}
			// Perform checks against the violation date
			if (isset ($_POST['violation|date|violation'])) {
				$_SESSION['violation_information']['trusted_violation|date|violation'] = (string) $violation_class -> superClean ($_POST['violation|date|violation'], $violation_class -> returnColumnSize ('dateviolation'));
				if (mb_strlen ($_SESSION['violation_information']['trusted_violation|date|violation']) < $violation_class -> returnColumnSize ('dateviolation')) {
					$_SESSION['error_class_item'] -> addError ('violation|date|violation', $_HOAM_language ['errors']['generic']['date-too-short']);
				}
				// Make sure we have a valid date
				if (checkdate (date ('m', strtotime ($_SESSION['violation_information']['trusted_violation|date|violation'])), date ('d', strtotime ($_SESSION['violation_information']['trusted_violation|date|violation'])), date ('Y', strtotime ($_SESSION['violation_information']['trusted_violation|date|violation'])))) {
				} else {
					$_SESSION['error_class_item'] -> addError ('violation|date|violation', $_HOAM_language ['errors']['generic']['invalid-date']);
				}
				// Make sure the date isn't in the future
				if (date ($_HOAM_country ['date']['format_mysql']) < date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['violation_information']['trusted_violation|date|violation']))) {
					$_SESSION['error_class_item'] -> addError ('violation|date|violation', $_HOAM_language ['errors']['generic']['date_future']);
				}
			}
			if (isset ($_POST['violation|date|resolution'])) {
				$_SESSION['violation_information']['trusted_violation|date|resolution'] = (string) $violation_class -> superClean ($_POST['violation|date|resolution'], $violation_class -> returnColumnSize ('dateresolution'));
				if (mb_strlen ($_SESSION['violation_information']['trusted_violation|date|resolution']) < $violation_class -> returnColumnSize ('dateresolution')) {
					$_SESSION['error_class_item'] -> addError ('violation|date|resolution', $_HOAM_language ['errors']['generic']['date-too-short']);
				}
				// Make sure we have a valid date
				if (checkdate (date ('m', strtotime ($_SESSION['violation_information']['trusted_violation|date|resolution'])), date ('d', strtotime ($_SESSION['violation_information']['trusted_violation|date|resolution'])), date ('Y', strtotime ($_SESSION['violation_information']['trusted_violation|date|resolution'])))) {
				} else {
					$_SESSION['error_class_item'] -> addError ('violation|date|resolution', $_HOAM_language ['errors']['generic']['invalid-date']);
				}
				// Make sure the date isn't in the future
				if (date ($_HOAM_country ['date']['format_mysql']) < date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['violation_information']['trusted_violation|date|resolution']))) {
					$_SESSION['error_class_item'] -> addError ('violation|date|resolution', $_HOAM_language ['errors']['generic']['date_future']);
				}
			}
			if (isset ($_POST['violation|date|resolveby'])) {
				$_SESSION['violation_information']['trusted_violation|date|resolveby'] = (string) $violation_class -> superClean ($_POST['violation|date|resolveby'], $violation_class -> returnColumnSize ('dateresolveby'));
				if (mb_strlen ($_SESSION['violation_information']['trusted_violation|date|resolveby']) < $violation_class -> returnColumnSize ('dateresolveby')) {
					$_SESSION['error_class_item'] -> addError ('violation|date|resolveby', $_HOAM_language ['errors']['generic']['date-too-short']);
				}
				// Make sure we have a valid date
				if (checkdate (date ('m', strtotime ($_SESSION['violation_information']['trusted_violation|date|resolveby'])), date ('d', strtotime ($_SESSION['violation_information']['trusted_violation|date|resolveby'])), date ('Y', strtotime ($_SESSION['violation_information']['trusted_violation|date|resolveby'])))) {
				} else {
					$_SESSION['error_class_item'] -> addError ('violation|date|resolveby', $_HOAM_language ['errors']['generic']['invalid-date']);
				}
				// Make sure the date isn't in the past
				if (date ($_HOAM_country ['date']['format_mysql']) >= date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['violation_information']['trusted_violation|date|resolveby']))) {
					$_SESSION['error_class_item'] -> addError ('violation|date|resolveby', $_HOAM_language ['errors']['generic']['date_past']);
				}
			}
			if ($_HOAM_setting ['violation']['require_approval']) {
				$_SESSION['violation_information']['trusted_violation|approval'] = (int) $_POST['violation|approval'];
				// Make sure the approval status is valid.
				$valid = FALSE;
				foreach ($_HOAM_language ['approval'] as $approval) {
					if ($_SESSION['violation_information']['trusted_violation|approval'] == $_HOAM_setting ['violation']['flags'][$approval['value']]) {
						$valid = TRUE;
						break;
					}
				}
				if (!$valid) {
					// The approval status is invalid.
					$_SESSION ['error_class_item'] -> addError ('violation|approval', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}

			// Are there any comments on the resolution of this violation?
			if (isset ($_POST['violation|resolution']) ) {
				$_SESSION['violation_information']['trusted_violation|resolution'] = (string) $violation_class -> superClean ($_POST['violation|resolution'], $violation_class -> returnColumnSize ('resolution'));
			}

			// Are there any specific notes for this violation?
			if (isset ($_POST['violation|specific']) ) {
				$_SESSION['violation_information']['trusted_violation|specific'] = (string) $violation_class -> superClean ($_POST['violation|specific'], 255);
			}

// print_r ($_POST); print_r ($_SESSION['violation_information']); print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/violation/add/');
				} else {
					header ('location: /admin/violation/edit/' . $_SESSION['violation_information']['trusted_violation|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST['add'])) {
					$violation_id = $violation_class -> add ($_SESSION['current_user']['id']);
					if ($violation_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['violation']['created'], HOAM_returnAddress ($_SESSION['violation_information']['trusted_violation|address'])),
											HOAM_MESSAGE_NOTICE,
											array ('flags' => $_HOAM_setting ['log']['flags']['violation'],
													'owner_id' => $violation_id));
					}
				} else {
					// Use the information gathered above.
					$violation_id = $_SESSION['violation_information']['trusted_violation|id'];
					$violation_class -> changeDateModified ($violation_id);
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$violation_class -> changeCategory ($violation_id, serialize ($_SESSION['violation_information']['trusted_violation|categories']));
				$violation_class -> changeSeverity ($violation_id, $_SESSION['violation_information']['trusted_violation|severity|current']);
				$violation_class -> changeLotId ($violation_id, $_SESSION['violation_information']['trusted_violation|address']);
				$violation_class -> changeResolveByDate ($violation_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['violation_information']['trusted_violation|date|resolveby'])));
				// This is really ugly, need a more elegant solution.
				$violation_class -> clearBit ($violation_id, $_HOAM_setting ['violation']['flags']['needs_approval']);
				$violation_class -> clearBit ($violation_id, $_HOAM_setting ['violation']['flags']['more_information']);
				$violation_class -> clearBit ($violation_id, $_HOAM_setting ['violation']['flags']['approved']);
				$violation_class -> clearBit ($violation_id, $_HOAM_setting ['violation']['flags']['not_approved']);
				$violation_class -> setBit ($violation_id, $_SESSION['violation_information']['trusted_violation|approval']);
				
				$violation_class -> changeFlags ($violation_id, $_SESSION['violation_information']['trusted_violation|approval']);
				$violation_class -> changeViolationDate ($violation_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['violation_information']['trusted_violation|date|violation'])));
				if (isset ($_SESSION['violation_information']['trusted_violation|resolution'])) {
					$violation_class -> changeResolution ($violation_id, $_SESSION['violation_information']['trusted_violation|resolution']);
				}
				if (is_true ($_SESSION['violation_information']['trusted_violation|assume_resolved'])) {
					$result = $violation_class -> setBit ($violation_id, $_HOAM_setting ['violation']['flags']['assume_resolved']);
				} else {
					$violation_class -> clearBit ($violation_id, $_HOAM_setting ['violation']['flags']['assume_resolved']);
				}
				// Process any attachments
				foreach ($_FILES as $file) {
					if ($file['name'] != NULL) {
						// Import the attachment into the database
						$id = HOAM_attachmentAdd ($file,
							$file,
							$_SESSION['current_user']['id'],
							$_HOAM_setting ['attachment']['flags']['violation'],
							$_SESSION['attachment_information']['trusted_attachment|description'],
							array ($violation_id));
						if (!$id) {
							echo $_HOAM_language ['errors']['attachment']['import'];
						}
					}
				}
				
				$letter_attachments = '';
				$attachment_list = $attachment_class -> listWithOwner ($violation_id, $_HOAM_setting ['attachment']['flags']['violation']);
				if (is_array ($attachment_list)) {
					$letter_attachments .= '<center>';
					foreach ($attachment_list as $attachment) {
						if (HOAM_is_image ($attachment_class -> returnMime ($attachment))) {
							$letter_attachments .= '<img style="margin: 1em; height: 2in; width: 3in;" src="/hoam/scripts/attachment/view.php?id=' . $attachment . '" alt="' . $attachment_class -> returnFilename ($violation_id) . '" />';
						}
					}
					$letter_attachments .= '</center>\n';
				}
				$new_letter  = '<div class="violation_letter">';
				$new_letter .= '<div class="violation_letter_head">' . $_HOAM_setting ['organization']['letter']['head'] . '</div>';
				$new_letter .= '<br /><br />' . date ($_HOAM_country ['date']['format_long']) . '<br /><br />';
				$new_letter .= '<div class="violation_letter_salutation">' . $violation_class -> clean ($_POST['violation|letter|salutation'], 512) . '</div>';
				$new_letter  .= '<div class="violation_letter_preamble">' . $violation_class -> clean ($_POST['violation|letter|preamble'], $violation_severity_class -> returnColumnSize ('preamble')) . '</div>';
				$new_letter  .= '<div class="violation_letter_description">' . $violation_class -> clean ($_POST['violation|letter|description'], $violation_category_class -> returnColumnSize ('description')) . '</div>';
				if (!empty ($_SESSION['violation_information']['trusted_violation|specific'])) {
					$new_letter  .= '<div class="violation_letter_specific">' . $_HOAM_language ['common']['specifically'] . ': ' . $_SESSION['violation_information']['trusted_violation|specific'] . '</div>';
				} else {
					$new_letter  .= '<div class="violation_letter_specific"></div>';
				}
				$new_letter  .= '<div class="violation_letter_attachment">' . $letter_attachments . '</div>';
				$new_letter  .= '<div class="violation_letter_detail">' . $violation_class -> clean ($_POST['violation|letter|detail'], $violation_category_class -> returnColumnSize ('detail')) . '</div>';
				$new_letter  .= '<div class="violation_letter_closing">' . $violation_class -> clean ($_POST['violation|letter|closing'], $violation_severity_class -> returnColumnSize ('closing')) . '</div>';
				$new_letter  .= '<div class="violation_letter_signature">' . $_HOAM_setting ['organization']['letter']['signature'] . '</div>';

				if (!isset ($_POST['add'])) {
					// If someone looked at a record, selected 'submit', but
					// didn't actually make any changes, this will be empty. No
					// need to update history.
					$original_letter = $violation_class -> returnOriginalLetter ($violation_id);
					if ($original_letter != $new_letter) {
						$changes = HOAM_diff ($original_letter, $new_letter);
						$changes = sprintf ($_HOAM_language ['tables']['generic']['history'], ucwords ($_HOAM_language ['common']['old-value']), ucwords ($_HOAM_language ['common']['new-value']), $changes['old'], $changes['new']);
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['violation']['modified'], HOAM_returnAddress ($_SESSION['violation_information']['trusted_violation|address']), $changes),
													HOAM_MESSAGE_NOTICE,
													array ('clean' => FALSE,
															'flags' => $_HOAM_setting ['log']['flags']['violation'],
															'owner_id' => $violation_id));
					}
				}
				
				// Add letter to the violation record
				$violation_class -> changeOriginalLetter ($violation_id, $new_letter);
				$violation_class -> changeSpecific ($violation_id, $_SESSION['violation_information']['trusted_violation|specific']);

				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION['error_class_item'])) {
					// Clear the error_class_item variable since everything worked ok.
					unset ($_SESSION['error_class_item']);
				}
				if (isset ($_SESSION['violation_information'])) {
					// Clear the violation_information variable since everything worked ok.
					unset ($_SESSION['violation_information']);
				}
				header ('Location: /admin/violation/');
				exit ();
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

exit ();
?>
