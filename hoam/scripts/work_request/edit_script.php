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

// Does the current user have the necessary permissions to access this form?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['work_request_add'], $_SESSION['current_user']['id']) OR
    $group_members_class -> isMember ($_HOAM_setting ['group']['ids']['work_request_delete'], $_SESSION['current_user']['id']) OR
    $group_members_class -> isMember ($_HOAM_setting ['group']['ids']['work_request_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {

		$attachment_class = new HOAM_attachments ('attachments');		
		$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
		$lot_class = new HOAM_lot ('lots');
		$work_request_class = new HOAM_work_request ('work_requests');

		require_once (HOAM_PATH_FUNCTION . 'attachments.php');

		// If this is the second (or greater) time through the script (ie,
		// there were errors that had to be fixed before committing the
		// changes) get rid of the old cached information the user entered,
		// we're going to populate it with information just received from the
		// form posting.
		if (isset ($_SESSION['request_information'])) {
			unset ($_SESSION['request_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			header ('Location: /admin/work_request/');
			exit ();
		} elseif (isset ($_POST['delete'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
                $group_members_class -> isMember ($_HOAM_setting ['group']['ids']['work_request_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				$request_id = (string) $work_request_class -> superClean ($_POST['work_request|id'], $work_request_class -> returnColumnSize ('id'));
				if ($lot_class -> idExists ($_SESSION['request_information']['trusted_work_request|address'])) {
					$property = HOAM_returnAddress ($work_request_class -> returnLotId ($request_id));
				} else {
					switch ($_SESSION['request_information']['trusted_work_request|address']) {
						case 'common_area' :
							$property = $_HOAM_language ['field_names']['lot']['common']['area'];
							break;
						case 'common_clubhouse' :
							$property = $_HOAM_language ['field_names']['lot']['common']['clubhouse'];
							break;
						case 'common_pool' :
							$property = $_HOAM_language ['field_names']['lot']['common']['pool'];
							break;
					}
				}
				$attachment_list = $attachment_class -> listWithOwner ($request_id);
				if (is_array ($attachment_list)) {
					foreach ($attachment_list as $attachment) {
						$attachment = $attachment_class -> listAll ($attachment);
						if (unlink (HOAM_PATH . HOAM_attachmentReturnPath ($attachment[0]['id']))) {
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['file'], $attachment[0]['filename']),
												HOAM_MESSAGE_NOTICE,
												array ('flags' => $_HOAM_setting ['log']['flags']['work_request'],
														'owner_id' => $request_id));
						}
						if ($_HOAM_setting ['attachment']['preview']['enable']) {
							$preview = HOAM_attachmentReturnPreviewPath ($attachment[0]['id']);
							if ($preview) {
								unlink (HOAM_PATH . $preview);
								$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['preview'], $attachment[0]['filename']),
													HOAM_MESSAGE_NOTICE,
													array ('flags' => $_HOAM_setting ['log']['flags']['work_request'],
															'owner_id' => $request_id));
							}
						}
						if ($attachment_class -> deleteId ($attachment[0]['id'])) { 
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['database'], $attachment[0]['id']),
												HOAM_MESSAGE_NOTICE,
												array ('flags' => $_HOAM_setting ['log']['flags']['work_request'],
														'owner_id' => $request_id));
						}
					}
				}
				$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['work_request']['deleted'], $property),
									HOAM_MESSAGE_NOTICE,
									array ('flags' => $_HOAM_setting ['log']['flags']['work_request'],
											'owner_id' => $request_id));
				$result = $work_request_class -> deleteId ($request_id);
				if ($result) {
					// Send the user back to the administration page.
					header ('Location: /admin/work_request/');
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
			// If we're still here, then we must be adding or modifying an
			// existing request. Start populating the request_information
			// array, and check for errors.
			$_SESSION['request_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');

			if (isset ($_POST['work_request|id'])) {
				$_SESSION['request_information']['trusted_work_request|id'] = (string) $work_request_class -> superClean ($_POST['work_request|id'], $work_request_class -> returnColumnSize ('id'));
				if ($work_request_class -> idExists ($_SESSION['request_information']['trusted_work_request|id'])) {
				} else {
					// The request is unknown.
					$_SESSION ['error_class_item'] -> addError ('work_request|id', $_HOAM_language ['errors']['generic']['unknown-id']);
				}
			}
			$_SESSION['request_information']['trusted_work_request|address'] = $work_request_class -> superClean ($_POST['work_request|address'], $work_request_class -> returnColumnSize ('lot_id'));
			// Make sure the address exists.
			if ($lot_class -> idExists ($_SESSION['request_information']['trusted_work_request|address'])) {
			} else {
				// Check to see if the address matches one of the common areas
				if (($_SESSION['request_information']['trusted_work_request|address'] == 'common_area') OR
					($_SESSION['request_information']['trusted_work_request|address'] == 'common_clubhouse') OR
					($_SESSION['request_information']['trusted_work_request|address'] == 'common_pool')) {
				} else { 
					// The address is unknown.
					$_SESSION ['error_class_item'] -> addError ('work_request|address', $_HOAM_language ['errors']['generic']['unknown-id']);
				}
			}

			if (isset ($_POST['work_request|status'])) {
				$_SESSION['request_information']['trusted_work_request|status'] = (int) $work_request_class -> superClean ($_POST['work_request|status'], $work_request_class -> returnColumnSize ('status'));
				// Make sure the status is valid.
				$valid = FALSE;
				foreach ($_HOAM_language ['status'] as $status) {
					if ($_SESSION['request_information']['trusted_work_request|status'] == $_HOAM_setting ['work_request']['flags'][$status['value']]) {
						$valid = TRUE;
						break;
					}
				}
				if (!$valid) {
					// The status is invalid.
					$_SESSION ['error_class_item'] -> addError ('work_request|status', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
				// Make sure all child requests have also been completed.
				if ($_SESSION['request_information']['trusted_work_request|status'] == $_HOAM_setting ['work_request']['flags']['complete']) {
					
				}
			}

			$_SESSION['request_information']['trusted_work_request|date|due'] = (string) $work_request_class -> superClean ($_POST['work_request|date|due'], $work_request_class -> returnColumnSize ('datedue'));
			// Make sure we have a valid date
			if (checkdate (date ('m', strtotime ($_SESSION['request_information']['trusted_work_request|date|due'])), date ('d', strtotime ($_SESSION['request_information']['trusted_work_request|date|due'])), date ('Y', strtotime ($_SESSION['request_information']['trusted_work_request|date|due'])))) {
			} else {
				$_SESSION['error_class_item'] -> addError ('work_request|date|due', $_HOAM_language ['errors']['generic']['invalid-date']);
			}
			// If the task is complete, let's not worry about the due date.
			if ($_SESSION['request_information']['trusted_work_request|status'] == $_HOAM_setting ['work_request']['flags']['complete']) {
			} else {
				if (mb_strlen ($_SESSION['request_information']['trusted_work_request|date|due']) < $work_request_class -> returnColumnSize ('datedue')) {
					$_SESSION['error_class_item'] -> addError ('work_request|date|due', $_HOAM_language ['errors']['generic']['date-too-short']);
				}
				// Make sure the date isn't in the past
				if (date ($_HOAM_country ['date']['format_mysql']) > date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['request_information']['trusted_work_request|date|due']))) {
					if (!isset ($_POST['work_request|date|due|ignore'])) {
						$_SESSION['error_class_item'] -> addError ('work_request|date|due', $_HOAM_language ['errors']['generic']['date_past']);
					}
				}
			}
			
			$_SESSION['request_information']['trusted_work_request|description'] = (string) $work_request_class -> superClean ($_POST['work_request|description'], $work_request_class -> returnColumnSize ('description'));
			// Make sure the description isn't too short.
			if (mb_strlen ($_SESSION['request_information']['trusted_work_request|description']) >= $_HOAM_setting ['work_request']['minimum_description_length']) {
			} else {
				// The description is too short.
				$_SESSION ['error_class_item'] -> addError ('work_request|description', sprintf ($_HOAM_language ['errors']['work_request']['description_too_short'], $_HOAM_setting ['work_request']['minimum_description_length']));
			}

			if (isset ($_POST['work_request|notes'])) {
				$_SESSION['request_information']['trusted_work_request|notes'] = (string) $work_request_class -> superClean ($_POST['work_request|notes'], $work_request_class -> returnColumnSize ('notes'));
				// Make sure the notes aren't too short.
				if (mb_strlen ($_SESSION['request_information']['trusted_work_request|notes']) >= $_HOAM_setting ['work_request']['minimum_notes_length']) {
				} else {
					// The notes are too short.
					$_SESSION ['error_class_item'] -> addError ('work_request|notes', sprintf ($_HOAM_language ['errors']['work_request']['notes_too_short'], $_HOAM_setting ['work_request']['minimum_notes_length']));
				}
			}

			if (isset ($_POST['work_request|child']) AND isset ($_POST['work_request|parent'])) {
				$_SESSION['request_information']['trusted_work_request|parent'] = (string) $work_request_class -> superClean ($_POST['work_request|parent'], $work_request_class -> returnColumnSize ('parent_id'));
				// Make sure we're not a child of ourselves.
				if (($_SESSION['request_information']['trusted_work_request|parent'] != $_SESSION['request_information']['trusted_work_request|id']) AND
					$work_request_class -> idExists ($_SESSION['request_information']['trusted_work_request|parent'])) {
				} else {
					$_SESSION ['error_class_item'] -> addError ('work_request|parent', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}

			$_SESSION['request_information']['trusted_work_request|priority'] = (int) $work_request_class -> superClean ($_POST['work_request|priority'], $work_request_class -> returnColumnSize ('priority'));
			// Make sure the priority is valid.
			$valid = FALSE;
			foreach ($_HOAM_language ['priority'] as $priority) {
				if ($_SESSION['request_information']['trusted_work_request|priority'] == $_HOAM_setting ['work_request']['flags'][$priority['value']]) {
					$valid = TRUE;
					break;
				}
			}
			if (!$valid) {
				// The priority is invalid.
				$_SESSION ['error_class_item'] -> addError ('work_request|priority', $_HOAM_language ['errors']['generic']['invalid-id']);
			}
			if ($_HOAM_setting ['work_request']['require_approval']) {
				$_SESSION['request_information']['trusted_work_request|approval'] = (int) $_POST['work_request|approval'];
				// Make sure the approval status is valid.
				$valid = FALSE;
				foreach ($_HOAM_language ['approval'] as $approval) {
					if ($_SESSION['request_information']['trusted_work_request|approval'] == $_HOAM_setting ['work_request']['flags'][$approval['value']]) {
						$valid = TRUE;
						break;
					}
				}
				if (!$valid) {
					// The approval status is invalid.
					$_SESSION ['error_class_item'] -> addError ('work_request|approval', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			$_SESSION['request_information']['trusted_work_request|title'] = (string) $work_request_class -> superClean ($_POST['work_request|title'], $work_request_class -> returnColumnSize ('title'));
			// Make sure the title isn't too short.
			if (mb_strlen ($_SESSION['request_information']['trusted_work_request|title']) >= $_HOAM_setting ['work_request']['minimum_title_length']) {
			} else {
				// The title is too short.
				$_SESSION ['error_class_item'] -> addError ('work_request|title', sprintf ($_HOAM_language ['errors']['work_request']['title_too_short'], $_HOAM_setting ['work_request']['minimum_title_length']));
			}

			if (isset ($_POST['work_request|vendor'])) {
				$_SESSION['request_information']['trusted_work_request|vendor'] = (string) $work_request_class -> superClean ($_POST['work_request|vendor'], $work_request_class -> returnColumnSize ('vendor_id'));
				// Make sure the vendor is valid.
				if (($_SESSION['request_information']['trusted_work_request|vendor'] == '') AND
					($_SESSION['request_information']['trusted_work_request|status'] != $_HOAM_setting ['work_request']['flags']['new'])) {
					// Cannot be both assigned and not have a vendor.
					$_SESSION ['error_class_item'] -> addError ('work_request|vendor', $_HOAM_language ['errors']['work_request']['vendor_required']);
				} elseif (($_SESSION['request_information']['trusted_work_request|vendor'] != '') AND
							!$budget_vendor_class -> idExists ($_SESSION['request_information']['trusted_work_request|vendor'])) {
					// The vendor is invalid.
					$_SESSION ['error_class_item'] -> addError ('work_request|vendor', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			
// print_r ($_POST); print_r ($_SESSION['request_information']); print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before
			// committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/work_request/add/');
					exit ();
				} else {
					header ('location: /admin/work_request/edit/' . $_SESSION['request_information']['trusted_work_request|id'] . '/');
					exit ();
				}
			} else {
				// Finally! We have good data and can start modifying the
				// database.
				if (isset ($_POST['add'])) {
					$request_id = $work_request_class -> add ($_SESSION['current_user']['id']);
					if ($request_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
						exit ();
					} else {
						if ($lot_class -> idExists ($_SESSION['request_information']['trusted_work_request|address'])) {
							$property = HOAM_returnAddress ($_SESSION['request_information']['trusted_work_request|address']);
						} else {
							switch ($_SESSION['request_information']['trusted_work_request|address']) {
								case 'common_area' :
									$property = $_HOAM_language ['field_names']['lot']['common']['area'];
									break;
								case 'common_clubhouse' :
									$property = $_HOAM_language ['field_names']['lot']['common']['clubhouse'];
									break;
								case 'common_pool' :
									$property = $_HOAM_language ['field_names']['lot']['common']['pool'];
									break;
							}
						}
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['work_request']['created'], $property),
											HOAM_MESSAGE_NOTICE,
											array ('flags' => $_HOAM_setting ['log']['flags']['work_request'],
													'owner_id' => $request_id));
						$work_request_class -> changeRequesterId ($request_id, $_SESSION['current_user']['id']);
						if (isset ($_SESSION['request_information']['trusted_work_request|vendor'])) {
							$work_request_class -> changeStatus ($request_id, $_HOAM_setting ['work_request']['flags']['assigned']);
						} else {
							$work_request_class -> changeStatus ($request_id, $_HOAM_setting ['work_request']['flags']['new']);
						}
					}
				} else {
					// Use the information gathered above.
					$request_id = $_SESSION['request_information']['trusted_work_request|id'];
				}

				$previous_settings = $work_request_class -> listAll ($request_id);
				$previous_settings = $previous_settings[0];
				
				if ($previous_settings['datedue'] != date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['request_information']['trusted_work_request|date|due']))) {
					$request_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['work_request']['date']['due'] . '</td>';
					$request_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($_SESSION['request_information']['trusted_work_request|date|due'])) . '</td>';
					$request_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($previous_settings['datedue'])) . '</td></tr>';
				}
				$work_request_class -> changeDateDue ($request_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['request_information']['trusted_work_request|date|due'])));
				
				if ($previous_settings['description'] != $_SESSION['request_information']['trusted_work_request|description']) {
					$request_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['work_request']['description'] . '</td>';
					$desc = HOAM_diff ($previous_settings['description'], $_SESSION['request_information']['trusted_work_request|description']);
					$request_changes .= '<td class="center">' . $desc['new'] . '</td>';
					$request_changes .= '<td class="center">' . $desc['old'] . '</td></tr>';
					unset ($desc);
				}
				$work_request_class -> changeDescription ($request_id, $_SESSION['request_information']['trusted_work_request|description']);

				if ($previous_settings['lot_id'] != $_SESSION['request_information']['trusted_work_request|address']) {
					$request_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['work_request']['address'] . '</td>';
					if ($lot_class -> idExists ($_SESSION['request_information']['trusted_work_request|address'])) {
						$property = HOAM_returnAddress ($_SESSION['request_information']['trusted_work_request|address']);
					} else {
						switch ($_SESSION['request_information']['trusted_work_request|address']) {
							case 'common_area' :
								$property = $_HOAM_language ['field_names']['lot']['common']['area'];
								break;
							case 'common_clubhouse' :
								$property = $_HOAM_language ['field_names']['lot']['common']['clubhouse'];
								break;
							case 'common_pool' :
								$property = $_HOAM_language ['field_names']['lot']['common']['pool'];
								break;
						}
					}						
					$request_changes .= '<td class="center">' . $property . '</td>';
					if ($lot_class -> idExists ($previous_settings['lot_id'])) {
						$property = HOAM_returnAddress ($previous_settings['lot_id']);
					} else {
						switch ($previous_settings['lot_id']) {
							case 'common_area' :
								$property = $_HOAM_language ['field_names']['lot']['common']['area'];
								break;
							case 'common_clubhouse' :
								$property = $_HOAM_language ['field_names']['lot']['common']['clubhouse'];
								break;
							case 'common_pool' :
								$property = $_HOAM_language ['field_names']['lot']['common']['pool'];
								break;
						}
					}						
					$request_changes .= '<td class="center">' . $property . '</td></tr>';
				}
				$work_request_class -> changeLotId ($request_id, $_SESSION['request_information']['trusted_work_request|address']);

				if ($previous_settings['notes'] != $_SESSION['request_information']['trusted_work_request|notes']) {
					$request_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['work_request']['notes'] . '</td>';
					$notes = HOAM_diff ($previous_settings['notes'], $_SESSION['request_information']['trusted_work_request|notes']);
					$request_changes .= '<td class="center">' . $notes['new'] . '</td>';
					$request_changes .= '<td class="center">' . $notes['old'] . '</td></tr>';
					unset ($notes);
				}
				$work_request_class -> changeNotes ($request_id, $_SESSION['request_information']['trusted_work_request|notes']);

				if ($previous_settings['priority'] != $_SESSION['request_information']['trusted_work_request|priority']) {
					$request_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['work_request']['priority'] . '</td>';
					foreach ($_HOAM_language ['priority'] as $priority) {
						if ($_HOAM_setting ['work_request']['flags'][$priority['value']] == $_SESSION['request_information']['trusted_work_request|priority']) {
							$request_changes .= '<td class="center">' . $priority['name'] . '</td>';
						}
					}
					foreach ($_HOAM_language ['priority'] as $priority) {
						if ($_HOAM_setting ['work_request']['flags'][$priority['value']] == $previous_settings['priority']) {
							$request_changes .= '<td class="center">' . $priority['name'] . '</td>';
						}
					}
				}
				$work_request_class -> changePriority ($request_id, $_SESSION['request_information']['trusted_work_request|priority']);
				
				if (isset ($_POST['work_request|child']) AND
					isset ($_SESSION['request_information']['trusted_work_request|parent'])) {
					$work_request_class -> changeParentId ($request_id, $_SESSION['request_information']['trusted_work_request|parent']);
				} else {
					$work_request_class -> changeParentId ($request_id, NULL);
				}
				
				if ($previous_settings['flags'] != $_SESSION['request_information']['trusted_work_request|approval']) {
					$request_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['work_request']['approval'] . '</td>';
					foreach ($_HOAM_language ['approval'] as $approval) {
						if ($_HOAM_setting ['work_request']['flags'][$approval['value']] == $_SESSION['request_information']['trusted_work_request|approval']) {
							$request_changes .= '<td class="center">' . $approval['name'] . '</td>';
						}
					}
					foreach ($_HOAM_language ['priority'] as $approval) {
						if ($_HOAM_setting ['work_request']['flags'][$approval['value']] == $previous_settings['flags']) {
							$request_changes .= '<td class="center">' . $approval['name'] . '</td>';
						}
					}
					// This is really ugly, need a more elegant solution.
					$work_request_class -> clearBit ($request_id, $_HOAM_setting ['work_request']['flags']['needs_approval']);
					$work_request_class -> clearBit ($request_id, $_HOAM_setting ['work_request']['flags']['more_information']);
					$work_request_class -> clearBit ($request_id, $_HOAM_setting ['work_request']['flags']['approved']);
					$work_request_class -> clearBit ($request_id, $_HOAM_setting ['work_request']['flags']['not_approved']);
				}
				$work_request_class -> setBit ($request_id, $_SESSION['request_information']['trusted_work_request|approval']);
				
				if ($previous_settings['status'] != $_SESSION['request_information']['trusted_work_request|status']) {
					if (($previous_settings['status'] != $_HOAM_setting ['work_request']['flags']['complete']) AND
						($_SESSION['request_information']['trusted_work_request|status'] == $_HOAM_setting ['work_request']['flags']['complete'])) {
						$work_request_class -> changeDateCompleted ($request_id);
					}
					$request_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['work_request']['status'] . '</td>';
					foreach ($_HOAM_language ['status'] as $status) {
						if ($_HOAM_setting ['work_request']['flags'][$status['value']] == $_SESSION['request_information']['trusted_work_request|status']) {
							$request_changes .= '<td class="center">' . $status['name'] . '</td>';
						}
					}
					foreach ($_HOAM_language ['status'] as $status) {
						if ($_HOAM_setting ['work_request']['flags'][$status['value']] == $previous_settings['status']) {
							$request_changes .= '<td class="center">' . $status['name'] . '</td>';
						}
					}
				}
				$work_request_class -> changeStatus ($request_id, $_SESSION['request_information']['trusted_work_request|status']);
				
				if ($previous_settings['title'] != $_SESSION['request_information']['trusted_work_request|title']) {
					$request_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['work_request']['title'] . '</td>';
					$request_changes .= '<td class="center">' . $_SESSION['request_information']['trusted_work_request|title'] . '</td>';
					$request_changes .= '<td class="center">' . $previous_settings['title'] . '</td></tr>';
				}
				$work_request_class -> changeTitle ($request_id, $_SESSION['request_information']['trusted_work_request|title']);
				
				if ($previous_settings['vendor_id'] != $_SESSION['request_information']['trusted_work_request|vendor']) {
					$request_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['work_request']['vendor'] . '</td>';
					$request_changes .= '<td class="center">' . $budget_vendor_class -> returnName ($_SESSION['request_information']['trusted_work_request|vendor']) . '</td>';
					$request_changes .= '<td class="center">' . $budget_vendor_class -> returnName ($previous_settings['vendor_id']) . '</td></tr>';
				}
				$work_request_class -> changeVendorId ($request_id, $_SESSION['request_information']['trusted_work_request|vendor']);

				// Process any attachments
				foreach ($_FILES as $file) {
					if ($file['name'] != NULL) {
						// Import the attachment into the database
						$id = HOAM_attachmentAdd ($file,
							$file,
							$_SESSION['current_user']['id'],
							$_HOAM_setting ['attachment']['flags']['work_request'],
							'',
							$request_id);
						if (!$id) {
							echo $_HOAM_language ['errors']['attachment']['import'];
						}
					}
				}

				$work_request_class -> changeDateModified ($request_id);

				if (isset ($_POST['add'])) {
				} else {
					// If someone looked at a record, selected 'submit', but
					// didn't actually make any changes, this will be empty. No
					// need to update history.
					if ($request_changes) {
						$thead = sprintf ($_HOAM_language ['log']['generic']['table']['thead'], ucfirst ($_HOAM_language ['common']['setting']), ucfirst ($_HOAM_language ['common']['new-value']), ucfirst ($_HOAM_language ['common']['old-value']));
						$tbody = sprintf ($_HOAM_language ['log']['generic']['table']['tbody'], $request_changes);
						$table = sprintf ($_HOAM_language ['log']['generic']['table']['default'], $_HOAM_language ['log']['old_new']['table']['colgroup'], $thead, $tbody);
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['work_request']['modified'], $table),
											HOAM_MESSAGE_NOTICE,
											array ('clean' => FALSE,
													'flags' => $_HOAM_setting ['log']['flags']['work_request'],
													'owner_id' => $request_id));
					}
				}

				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION['error_class_item'])) {
					// Clear the error_class_item variable since everything
					// worked ok.
					unset ($_SESSION['error_class_item']);
				}
				if (isset ($_SESSION['request_information'])) {
					// Clear the homeowner_information variable since
					// everything worked ok.
					unset ($_SESSION['request_information']);
				}

				header ('Location: /admin/work_request/');
				exit ();
			}
			// If we're still here, something's gone wrong.
			header ('Location: /error/');
			exit ();
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're
		// being asked to perform
		header ('Location: /error/unknown_action/');
		exit ();
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

// If we're still here, something's gone wrong.
header ('Location: /error/');
exit ();

?>
