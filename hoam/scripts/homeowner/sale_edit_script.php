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
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) || isset ($_POST['cancel']) || isset ($_POST['delete']) || isset ($_POST['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'attachments.php');

		$attachment_class = new HOAM_attachments ('attachments');
		$homeowner_class = new HOAM_homeowner ('homeowners');
		$homeowner_sale = new HOAM_homeowner_sale ('homeowner_sale');
		$lot_class = new HOAM_lot ('lots');
		
		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['sale_information'])) {
			unset ($_SESSION['sale_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the action, send them back to the main page
			header ('Location: /admin/homeowner/sale/');
		} elseif (isset ($_POST['delete'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
				$trusted_id = $homeowner_sale -> superClean ($_POST['homeowner|sale|id'], $homeowner_sale -> returnColumnSize ('id'));
				$lot = $homeowner_sale -> returnLotId ($trusted_id); 
				$attachment_list = $attachment_class -> listWithOwner ($trusted_id);
				if (is_array ($attachment_list)) {
					foreach ($attachment_list as $attachment) {
						$attachment = $attachment_class -> listAll ($attachment);
						if (unlink (HOAM_PATH . HOAM_attachmentReturnPath ($attachment[0]['id']))) {
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['file'], $attachment[0]['filename']),
												HOAM_MESSAGE_NOTICE,
												array ('flags' => $_HOAM_setting ['log']['flags']['homeowner_sale'],
														'owner_id' => $trusted_id));
						}
						if ($_HOAM_setting ['attachment']['preview']['enable']) {
							$preview = HOAM_attachmentReturnPreviewPath ($attachment[0]['id']);
							if ($preview) {
								unlink (HOAM_PATH . $preview);
								$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['preview'], $attachment[0]['filename']),
													HOAM_MESSAGE_NOTICE,
													array ('flags' => $_HOAM_setting ['log']['flags']['homeowner_sale'],
															'owner_id' => $trusted_id));
							}
						}
						if ($attachment_class -> deleteId ($attachment[0]['id'])) { 
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['database'], $attachment[0]['id']),
												HOAM_MESSAGE_NOTICE,
												array ('flags' => $_HOAM_setting ['log']['flags']['homeowner_sale'],
														'owner_id' => $trusted_id));
						}
					}
				}
				if ($homeowner_sale -> deleteId ($trusted_id)) {
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['homeowner']['sale']['deleted'], HOAM_returnAddress ($lot)),
										HOAM_MESSAGE_NOTICE,
										array ('flags' => $_HOAM_setting ['log']['flags']['homeowner_sale'],
												'owner_id' => $trusted_id));
					// Send the user back to the administration page.
					header ('Location: /admin/homeowner/sale/');
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
			// existing entry. Start populating the sale_information array, and
			// check for errors.
			$_SESSION['sale_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			if (isset ($_POST['homeowner|sale|id'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|id'] = (string) $homeowner_sale -> superClean ($_POST['homeowner|sale|id'], $homeowner_sale -> returnColumnSize ('id'));
				if ($homeowner_sale -> idExists ($_SESSION['sale_information']['trusted_homeowner|sale|id'])) {
				} else {
					// The homeowner is unknown.
					$_SESSION ['error_class_item'] -> addError ('homeowner|sale|id', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['homeowner|sale|address'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|address'] = (string) $homeowner_sale -> superClean ($_POST['homeowner|sale|address'], $lot_class -> returnColumnSize ('id'));
				// Make sure the address is valid short.
				if ($lot_class -> idExists ($_SESSION['sale_information']['trusted_homeowner|sale|address'])) {
				} else {
					// The address is invalid.
					$_SESSION ['error_class_item'] -> addError ('homeowner|sale|address', $_HOAM_language ['errors']['lot']['unknown_id']);
				}
			}
			if (isset ($_POST['homeowner|sale|comments'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|comments'] = (string) $homeowner_sale -> superClean ($_POST['homeowner|sale|comments'], $homeowner_sale -> returnColumnSize ('comments'));
				// Make sure the comments aren't too short.
				if (mb_strlen ($_SESSION['sale_information']['trusted_homeowner|sale|comments']) >= $_HOAM_setting ['homeowner']['minimum_comments_length']) {
				} else {
					// The comments are too short.
					$_SESSION ['error_class_item'] -> addError ('homeowner|sale|comments', sprintf ($_HOAM_language ['errors']['generic']['value-too-small'], $_HOAM_setting ['homeowner']['minimum_comments_length']));
				}
			}
			if (isset ($_POST['homeowner|sale|name'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|name'] = (string) $homeowner_sale -> superClean ($_POST['homeowner|sale|name'], $homeowner_sale -> returnColumnSize ('name'));
/*				// Make sure the name isn't too short.
				if (mb_strlen ($_SESSION['sale_information']['trusted_homeowner|sale|name']) >= $_HOAM_setting ['homeowner']['minimum_name_length']) {
				} else {
					// The name is too short.
					$_SESSION ['error_class_item'] -> addError ('homeowner|sale|name', sprintf ($_HOAM_language ['errors']['generic']['value-too-small'], $_HOAM_setting ['news']['minimum_name_length']));
				}
*/			}
			if (isset ($_POST['homeowner|sale|saledate'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|saledate'] = (string) $homeowner_sale -> superClean ($_POST['homeowner|sale|saledate'], $homeowner_sale -> returnColumnSize ('saledate'));
				if (mb_strlen ($_SESSION['sale_information']['trusted_homeowner|sale|saledate']) < $homeowner_sale -> returnColumnSize ('saledate')) {
					$_SESSION['error_class_item'] -> addError ('homeowner|sale|saledate', $_HOAM_language ['errors']['generic']['date-too-short']);
				}
				// Make sure we have a valid date
				if (checkdate (date ('m', strtotime ($_SESSION['sale_information']['trusted_homeowner|sale|saledate'])), date ('d', strtotime ($_SESSION['sale_information']['trusted_homeowner|sale|saledate'])), date ('Y', strtotime ($_SESSION['sale_information']['trusted_homeowner|sale|saledate'])))) {
				} else {
					$_SESSION['error_class_item'] -> addError ('homeowner|sale|saledate', $_HOAM_language ['errors']['generic']['invalid-date']);
				}
				// This test is currently excluded because it causes problems
				// When editing existing entries that may have already passed
				// the sale date; for instance, marking a property sold after
				// the projected sale date originally entered.
/*				
				// Make sure the date isn't in the past
				if (date ($_HOAM_country ['date']['format_mysql']) >= date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['sale_information']['trusted_homeowner|sale|saledate']))) {
					$_SESSION['error_class_item'] -> addError ('homeowner|sale|saledate', $_HOAM_language ['errors']['generic']['date_past']);
				}
*/			}
			if (isset ($_POST['homeowner|sale|title'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|title'] = (string) $homeowner_sale -> superClean ($_POST['homeowner|sale|title'], $homeowner_sale -> returnColumnSize ('title_company'));
/*				// Make sure the name isn't too short.
				if (mb_strlen ($_SESSION['sale_information']['trusted_homeowner|sale|title']) >= $_HOAM_setting ['homeowner']['minimum_name_length']) {
				} else {
					// The name is too short.
					$_SESSION ['error_class_item'] -> addError ('homeowner|sale|title', sprintf ($_HOAM_language ['errors']['generic']['value-too-small'], $_HOAM_setting ['news']['minimum_name_length']));
				}
*/			}

			$_SESSION['sale_information']['trusted_homeowner|sale|flags'] = 0;
			if (isset ($_POST['homeowner|sale|flag|packet_delivered'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|flags'] += $_HOAM_setting ['homeowner']['flags']['packet_delivered'];
			}
			if (isset ($_POST['homeowner|sale|flag|packet_ready'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|flags'] += $_HOAM_setting ['homeowner']['flags']['packet_ready'];
			}
			if (isset ($_POST['homeowner|sale|flag|payment_received'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|flags'] += $_HOAM_setting ['homeowner']['flags']['payment_received'];
			}
			if (isset ($_POST['homeowner|sale|flag|residence_sold'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|flags'] += $_HOAM_setting ['homeowner']['flags']['residence_sold'];
			}
			if (isset ($_POST['homeowner|sale|flag|residence_off_market'])) {
				$_SESSION['sale_information']['trusted_homeowner|sale|flags'] += $_HOAM_setting ['homeowner']['flags']['residence_off_market'];
			}
			
// echo print_r ($_POST); print_r ($_FILES); print_r ($_SESSION['sale_information']); $_SESSION['error_class_item'] -> errorCount; print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/homeowner/sale/add/');
				} else {
					header ('location: /admin/homeowner/sale/edit/' . $_SESSION ['sale_information']['trusted_homeowner|sale|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST['add'])) {
					$sale_id = $homeowner_sale -> add ($_SESSION['current_user']['id']);
					if ($sale_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						// When adding a new entry, set the modified date the
						// same as the created.
						$homeowner_sale -> changeDateModified ($sale_id, $homeowner_sale -> returnDateCreated ($sale_id));
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['homeowner']['sale']['created'], HOAM_returnAddress ($_SESSION['sale_information']['trusted_homeowner|sale|address'])),
										HOAM_MESSAGE_NOTICE,
										array ('flags' => $_HOAM_setting ['log']['flags']['homeowner_sale'],
												'owner_id' => $sale_id));
					}
				} else {
					$sale_id = $_SESSION['sale_information']['trusted_homeowner|sale|id'];
					$homeowner_sale -> changeDateModified ($sale_id);
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['homeowner']['sale']['modified'], HOAM_returnAddress ($_SESSION['sale_information']['trusted_homeowner|sale|address'])),
										HOAM_MESSAGE_NOTICE,
										array ('flags' => $_HOAM_setting ['log']['flags']['homeowner_sale'],
												'owner_id' => $sale_id));
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$homeowner_sale -> changeComments ($sale_id, $_SESSION['sale_information']['trusted_homeowner|sale|comments']);
				$homeowner_sale -> changeLotId ($sale_id, $_SESSION['sale_information']['trusted_homeowner|sale|address']);
				$homeowner_sale -> changeName ($sale_id, $_SESSION['sale_information']['trusted_homeowner|sale|name']);
				$homeowner_sale -> changeSaleDate ($sale_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['sale_information']['trusted_homeowner|sale|saledate'])));
				$homeowner_sale -> changeTitleCompany ($sale_id, $_SESSION['sale_information']['trusted_homeowner|sale|title']);
				$homeowner_sale -> changeFlags ($sale_id, $_SESSION['sale_information']['trusted_homeowner|sale|flags']);
				// Process any attachments
				// There must be a better way of doing this, however I'm not sure what it is.
				// I've tried several different ways, but none of them seem to handle the $_POST
				// array correctly. Is it special?
				//
				// Go through every item in the array
				foreach ($_FILES as $file) {
					if ($file['name'] != NULL) {
						// Import the attachment into the database
						$id = HOAM_attachmentAdd ($file,
							$file,
							$_SESSION['current_user']['id'],
							$_HOAM_setting ['attachment']['flags']['homeowner_sale'],
							$_SESSION['attachment_information']['trusted_attachment|description'],
							array ($sale_id));
					}
				}
				
				/*************************************************************************/

				// Clear the error_class_item variable since everything worked ok.
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}
				// Clear the sale_information variable since everything worked ok.
				if (isset ($_SESSION['sale_information'])) {
					unset ($_SESSION['sale_information']);
				}

				header ('Location: /admin/homeowner/sale/');
			}
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

?>
