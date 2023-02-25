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

// Is the current user allowed to add / edit invoices?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['messageboard_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['messageboard_post'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['edit']) OR isset ($_POST['reply'])) {
		require_once (HOAM_PATH_FUNCTION . 'messageboard.php');

		$messageboard_class = new HOAM_messageboard ('messageboard');
	
		// If this is the second (or greater) time through the script (ie, there
		// were errors that had to be fixed before committing the changes) get
		// rid of the old cached information the user entered, we're going to
		// populate it with information just received from the form posting.
		if (isset ($_SESSION['message_information'])) {
			unset ($_SESSION['message_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			// If we have an message id, send the user back to that.
			if (isset ($_POST['messageboard|'])) {
				header ('Location: /apps/messageboard/view/' . (string) $messageboard_class -> superClean ($_POST['messageboard|id'], $messageboard_class -> returnColumnSize ('id')) . '/');
				exit ();
			} else {
				// Just send them back to the root
				header ('Location: /apps/messageboard/view/' . (string) $messageboard_class -> superClean ($_POST['messageboard|root'], $messageboard_class -> returnColumnSize ('id')) . '/');
				exit ();
			}
		} elseif (isset ($_POST['delete'])) {
			if (isset ($_SESSION['current_user']['id'])) {
				// Make sure the current user is the owner of the item, OR has
				// admin-level privileges.
				if (($messageboard_class -> returnUserId ((string) $messageboard_class -> superClean ($_POST['message_id'], $messageboard_class -> returnColumnSize ('id'))) === $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['messageboard_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					// Make sure we found the message
					$message_id = (string) $messageboard_class -> superClean ($_POST['messageboard|id'], $messageboard_class -> returnColumnSize ('id'));
					if ($messageboard_class -> isBitSet ($message_id, $_HOAM_setting ['messageboard']['flags']['do_not_delete'])) {
						header ('Location: /error/inuse/');
						exit ();
					} else {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['messageboard']['delete'], $messageboard_class -> returnSubject ($message_id)),
											HOAM_MESSAGE_NOTICE,
											array ('owner_id' => $message_id,
													'flags' => $_HOAM_setting ['log']['flags']['messageboard']));
						if (HOAM_messageboardDelete ($message_id, (string) $messageboard_class -> superClean ($_POST['messageboard|tree'], $messageboard_class -> returnColumnSize ('id')))) {
							// Send the user back to the message's root id.
							header ('Location: /apps/messageboard/view/' . (string) $messageboard_class -> superClean ($_POST['messageboard|tree'], $messageboard_class -> returnColumnSize ('id')) . '/');
							exit ();
						} else {
							// Error Removing Item
							header ('Location: /error/remove/');
							exit ();
						}
					}
				} else {
					// User does not have the necessary permissions.
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				// User is not logged in / does not have the necessary
				// permissions.
				header ('Location: /error/403/');
				exit ();
			}
		} else {
			// If we're still here, then we must be adding or modifying an existing message.
			// Start populating the message_information array, and check for errors.
			$_SESSION['message_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');

			// Get information about the message
			if (isset ($_POST['messageboard|root'])) {
				$temp_root_id = (string) $messageboard_class -> superClean ($_POST['messageboard|root'], $messageboard_class -> returnColumnSize ('id'));
				if ($messageboard_class -> idExists ($temp_root_id)) {
					$_SESSION['message_information']['trusted_messageboard|root'] = $temp_root_id;
					unset ($temp_root_id);
				} else {
					// There is a problem with the message root sent.
					header ('Location: /error/unknown_id/');
					exit ();
				}
			}
			if (isset ($_POST['messageboard|tree'])) {
				$temp_tree_id = (string) $messageboard_class -> superClean ($_POST['messageboard|tree'], $messageboard_class -> returnColumnSize ('id'));
				if ($messageboard_class -> idExists ($temp_tree_id)) {
					$_SESSION['message_information']['trusted_messageboard|tree'] = $temp_tree_id;
					unset ($temp_tree_id);
				} else {
					// There is a problem with the message tree sent.
					header ('Location: /error/unknown_id/');
					exit ();
				}
			}
			if (isset ($_POST['messageboard|id'])) {
				$_SESSION['message_information']['trusted_messageboard|id'] = (string) $messageboard_class -> superClean ($_POST['messageboard|id'], $messageboard_class -> returnColumnSize ('id'));
				if ($messageboard_class -> idExists ($_SESSION['message_information']['trusted_messageboard|id'])) {
				} else {
					// The homeowner is unknown.
					$_SESSION ['error_class_item'] -> addError ('messageboard|id', $_HOAM_language ['errors']['messageboard']['not-exist']);
				}
			}
			// Start populating the message_information array, and check for errors.
			if (isset ($_POST['messageboard|subject'])) {
				$_SESSION['message_information']['trusted_messageboard|subject'] = (string) $messageboard_class -> superClean ($_POST['messageboard|subject'], $messageboard_class -> returnColumnSize ('subject'));
				// Make sure the message subject isn't too short.
				if (mb_strlen ($_SESSION['message_information']['trusted_messageboard|subject']) >= $_HOAM_setting ['messageboard']['minimum_subject_length']) {
				} else {
					// The subject is too short.
					$_SESSION ['error_class_item'] -> addError ('messageboard|subject', $_HOAM_language ['errors']['messageboard']['subject']['too-short'], $_HOAM_setting ['messageboard']['minimum_subject_length']);
				}
			}
			if (isset ($_POST['messageboard|message'])) {
				$_SESSION['message_information']['trusted_messageboard|message'] = (string) $messageboard_class -> superClean ($_POST['messageboard|message'], $messageboard_class -> returnColumnSize ('article'));
				// Make sure the message content isn't too short.
				if (mb_strlen ($_SESSION['message_information']['trusted_messageboard|message']) >= $_HOAM_setting ['messageboard']['minimum_message_length']) {
				} else {
					// The message is too short.
					$_SESSION ['error_class_item'] -> addError ('messageboard|message', $_HOAM_language ['errors']['messageboard']['message']['too-short'], $_HOAM_setting ['messageboard']['minimum_message_length']);
				}
			}

			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if (isset ($_POST['messageboard|flags|display_top'])) {
					$_SESSION['message_information']['trusted_messageboard|flags|display_top'] = (int) $_POST['messageboard|flags|display_top'];
				} else {
					$_SESSION['message_information']['trusted_messageboard|flags|display_top'] = (int) 0;
				}
				if (isset ($_POST['messageboard|flags|do_not_delete'])) {
					$_SESSION['message_information']['trusted_messageboard|flags|do_not_delete'] = (int) $_POST['messageboard|flags|do_not_delete'];
				} else {
					$_SESSION['message_information']['trusted_messageboard|flags|do_not_delete'] = (int) 0;
				}
				if (isset ($_POST['messageboard|flags|no_more_comments'])) {
					$_SESSION['message_information']['trusted_messageboard|flags|no_more_comments'] = (int) $_POST['messageboard|flags|no_more_comments'];
				} else {
					$_SESSION['message_information']['trusted_messageboard|flags|no_more_comments'] = (int) 0;
				}
			}
			// Are there any errors that the current user needs to fix before
			// committing the changes?

// echo $_SESSION['error_class_item'] -> errorCount; print_r ($_POST); print_r ($_SESSION['message_information']); exit();

			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /apps/messageboard/post/' . HOAM_messageboardRoot ($_SESSION['message_information']['trusted_messageboard|root']) . '/');
				} elseif (isset ($_POST['reply'])) {
					header ('location: /apps/messageboard/reply/' . $_SESSION['message_information']['trusted_messageboard|root'] . '/');
				} else { // (isset ($_POST['edit'])) {
					header ('location: /apps/messageboard/edit/' . $_SESSION['message_information']['trusted_messageboard|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the
				// database.
				if (isset ($_POST['add']) OR isset ($_POST['reply'])) {
					$message_id = $messageboard_class -> add ($_SESSION['current_user']['id']);
					if ($message_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['messageboard']['created'], $_SESSION['message_information']['trusted_messageboard|subject']),
											HOAM_MESSAGE_NOTICE,
											array ('owner_id' => $message_id,
													'flags' => $_HOAM_setting ['log']['flags']['messageboard']));
						// Increase the parent's reply count by one.
						$messageboard_class -> changeReplies ($_SESSION['message_information']['trusted_messageboard|root'], $messageboard_class -> returnReplies ($_SESSION['message_information']['trusted_messageboard|root']) + 1);
						// Increase the tree_id reply count by one, but only if we
						// didn't just do so above (ie, we aren't working one level
						// below the root already)
						if ($_SESSION['message_information']['trusted_messageboard|root'] != $_SESSION['message_information']['trusted_messageboard|tree']) {
							$messageboard_class -> changeReplies ($_SESSION['message_information']['trusted_messageboard|tree'], $messageboard_class -> returnReplies ($_SESSION['message_information']['trusted_messageboard|tree']) + 1);
						}
					}
				} else {
					$message_id = $_SESSION['message_information']['trusted_messageboard|id'];
				}
				// Make sure that the current user is authorized to edit this
				// message.
				if (isset ($_POST['modify'])) {
					if (($messageboard_class -> returnUserId ($message_id) === $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
					} else {
						// User does not have the necessary permissions.
						header ('Location: /error/403/');
						exit ();
					}
				}
				if (isset ($_POST['edit'])) {
					$changes = HOAM_diff ($messageboard_class -> returnArticle ($message_id), $_SESSION['message_information']['trusted_messageboard|message']);
					$changes = sprintf ($_HOAM_language ['tables']['generic']['history'], ucfirst ($_HOAM_language ['common']['old-value']), ucfirst ($_HOAM_language ['common']['new-value']), $changes['old'], $changes['new']);
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['messageboard']['modified'], $changes),
										HOAM_MESSAGE_NOTICE,
										array ('clean' => FALSE,
												'owner_id' => $message_id,
												'flags' => $_HOAM_setting ['log']['flags']['messageboard']));
				}

				// Make sure that someone isn't trying to maliciously insert
				// dummy messages that could clog up the system.
				$messageboard_class -> changeRootId ($message_id, $_SESSION['message_information']['trusted_messageboard|root']);
				$messageboard_class -> changeTreeId ($message_id, $_SESSION['message_information']['trusted_messageboard|tree']);
				$messageboard_class -> changeArticle ($message_id, $_SESSION['message_information']['trusted_messageboard|message']);
				$messageboard_class -> changeSubject ($message_id, $_SESSION['message_information']['trusted_messageboard|subject']);
				
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					if (is_true ($_SESSION['message_information']['trusted_messageboard|flags|display_top'])) {
						$messageboard_class -> setBit ($message_id, $_HOAM_setting ['messageboard']['flags']['display_top']);
					} else {
						$messageboard_class -> clearBit ($message_id, $_HOAM_setting ['messageboard']['flags']['display_top']);
					}
					if (is_true ($_SESSION['message_information']['trusted_messageboard|flags|do_not_delete'])) {
						$messageboard_class -> setBit ($message_id, $_HOAM_setting ['messageboard']['flags']['do_not_delete']);
					} else {
						$messageboard_class -> clearBit ($message_id, $_HOAM_setting ['messageboard']['flags']['do_not_delete']);
					}
					if (is_true ($_SESSION['message_information']['trusted_messageboard|flags|no_more_comments'])) {
						$messageboard_class -> setBit ($message_id, $_HOAM_setting ['messageboard']['flags']['no_more_comments']);
					} else {
						$messageboard_class -> clearBit ($message_id, $_HOAM_setting ['messageboard']['flags']['no_more_comments']);
					}
				}
				$messageboard_class -> changeDateModified ($message_id);
				// Let's make sure we also update the modify date for the root
				// article.
				$messageboard_class -> changeDateModified ($_SESSION['message_information']['trusted_messageboard|tree']);

				// Clear the variables since everything worked ok.
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}
				if (isset ($_SESSION['message_information'])) {
					unset ($_SESSION['message_information']);
				}

				header ('Location: /apps/messageboard/view/' . $message_id . '/');
			}
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

exit ();

?>
