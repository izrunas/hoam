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

// Is the current user allowed to add / edit violation categories?
if (isset ($_SESSION ['current_user']['id']) and
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION ['current_user']['id']) OR
	($_HOAM_setting ['users']['ids']['root'] === $_SESSION ['current_user']['id']))) {
	if (isset ($_POST ['add']) OR isset ($_POST ['cancel']) OR isset ($_POST ['delete']) OR isset ($_POST ['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'attachments.php');

		$attachment_class = new HOAM_attachments ('attachments');
		$budget_class = new HOAM_budget ('budget');
		$budget_category_class = new HOAM_budget_category ('budget_category');
		$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');

		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION ['vendor_information'])) {
			unset ($_SESSION ['vendor_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION ['error_class_item'])) {
			unset ($_SESSION ['error_class_item']);
		}
		
		if (isset ($_POST ['cancel'])) {
			// Just send them back to the admin page
			header ('Location: /admin/financial/vendor/');
		} elseif (isset ($_POST ['delete'])) {
			// Make sure the current user is the owner of the item, OR has admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION ['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION ['current_user']['id']) OR 
			$_HOAM_setting ['user']['ids']['root'] == $_SESSION ['current_user']['id']) {
				// Make sure we found the category
				$vendor_id = (string) $budget_vendor_class -> superClean ($_POST ['vendor|id'], $budget_vendor_class -> returnColumnSize ('id'));
				if ($budget_vendor_class -> idExists ($vendor_id)) {
					// Check whether the vendor account is used elsewhere in the
					// system configuration.
					if (($vendor_id == $_HOAM_setting ['budget']['ids']['management']) OR
					 	($vendor_id == $_HOAM_setting ['budget']['ids']['organization'])) {
						// Error Removing Item
						// This needs to be moved to a more specific erorr, as in 'The item is still in use, can't be deleted'.
						header ('Location: /error/inuse/');
					} else {
						$attachment_list = $attachment_class -> listWithOwner ($vendor_id);
						if (is_array ($attachment_list)) {
							foreach ($attachment_list as $attachment) {
								$attachment = $attachment_class -> listAll ($attachment);
								if (unlink (HOAM_PATH . HOAM_attachmentReturnPath ($attachment[0]['id']))) {
									$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['file'], $attachment[0]['filename']),
														HOAM_MESSAGE_NOTICE,
														array ('flags' => $_HOAM_setting ['log']['flags']['budget_vendor'],
																'owner_id' => $vendor_id));
								}
								if ($_HOAM_setting ['attachment']['preview']['enable']) {
									$preview = HOAM_attachmentReturnPreviewPath ($attachment[0]['id']);
									if ($preview) {
										unlink (HOAM_PATH . $preview);
										$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['preview'], $attachment[0]['filename']),
															HOAM_MESSAGE_NOTICE,
															array ('flags' => $_HOAM_setting ['log']['flags']['budget_vendor'],
																	'owner_id' => $vendor_id));
									}
								}
								if ($attachment_class -> deleteId ($attachment[0]['id'])) { 
									$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['database'], $attachment[0]['id']),
														HOAM_MESSAGE_NOTICE,
														array ('flags' => $_HOAM_setting ['log']['flags']['budget_vendor'],
																'owner_id' => $vendor_id));
								}
							}
						}
						if ($budget_vendor_class -> deleteId ($vendor_id)) {
							$vendor_name = $budget_vendor_class -> returnName ($vendor_id);
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['budget_vendor']['deleted'], $vendor_name),
														HOAM_MESSAGE_NOTICE,
														array ('flags' => $_HOAM_setting ['log']['flags']['budget_vendor'],
																'owner_id' => $vendor_id));
							
							header ('Location: /admin/financial/vendor/');
						} else {
							// Error Removing Item
							header ('Location: /error/remove/');
						}
					}
				} else {
					// The article couldn't be found
					header ('Location: /error/unknown_id/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// If we're still here, then we must be adding or modifying an existing category.
			$_SESSION ['vendor_information'] = array ();
			$_SESSION ['error_class_item'] = new HOAM_errors ('errors');
			
			// Start populating the vendor_information array, and check for errors.
			if (isset ($_POST ['vendor|id'])) {
				$_SESSION ['vendor_information']['trusted_vendor|id'] = (string) $budget_vendor_class -> superClean ($_POST ['vendor|id'], $budget_vendor_class -> returnColumnSize ('id'));
				if ($budget_vendor_class -> idExists ($_SESSION ['vendor_information']['trusted_vendor|id'])) {
				} else {
					// The vendor is unknown.
					$_SESSION ['error_class_item'] -> addError ('vendor|id', $_HOAM_language ['errors']['budget']['vendor']['unknown-id']);
				}
			}
			if (isset ($_POST ['budget|vendor|name'])) {
				$_SESSION ['vendor_information']['trusted_budget|vendor|name'] = (string) $budget_vendor_class -> superClean ($_POST ['budget|vendor|name'], $budget_vendor_class -> returnColumnSize ('name'));
				// Make sure the name isn't too short.
				if (mb_strlen ($_SESSION ['vendor_information']['trusted_budget|vendor|name']) >= $_HOAM_setting ['budget']['minimum_name_length']) {
				} else {
					// The category name is too short.
					$_SESSION ['error_class_item'] -> addError ('budget|vendor|name', $_HOAM_language ['errors']['budget']['vendor']['name-too-short'], $_HOAM_setting ['budget']['minimum_name_length']);
				}
				// Need to add a check that the vendor specified doesn't already exist.
			}
			if (isset ($_POST ['budget|vendor|category'])) {
				$_SESSION ['vendor_information']['trusted_budget|vendor|category'] = (string) $budget_vendor_class -> superClean ($_POST ['budget|vendor|category'], $budget_vendor_class -> returnColumnSize ('category_id'));
				// Make sure the category is valid.
				if (!$budget_category_class -> idExists ($_SESSION ['vendor_information']['trusted_budget|vendor|category'])) {
					$_SESSION ['error_class_item'] -> addError ('budget|vendor|category', $_HOAM_language ['errors']['budget']['category']['unknown-id']);
				}
			}
			if (isset ($_POST ['budget|vendor|address1'])) {
				$_SESSION ['vendor_information']['trusted_budget|vendor|address1'] = (string) $budget_vendor_class -> superClean ($_POST ['budget|vendor|address1'], $budget_vendor_class -> returnColumnSize ('address1'));
			}
			if (isset ($_POST ['budget|vendor|address2'])) {
				$_SESSION ['vendor_information']['trusted_budget|vendor|address2'] = (string) $budget_vendor_class -> superClean ($_POST ['budget|vendor|address2'], $budget_vendor_class -> returnColumnSize ('address2'));
			}
			if (isset ($_POST ['budget|vendor|address3'])) {
				$_SESSION ['vendor_information']['trusted_budget|vendor|address3'] = (string) $budget_vendor_class -> superClean ($_POST ['budget|vendor|address3'], $budget_vendor_class -> returnColumnSize ('address3'));
			}
			if (isset ($_POST ['budget|vendor|city'])) {
				$_SESSION ['vendor_information']['trusted_budget|vendor|city'] = (string) $budget_vendor_class -> superClean ($_POST ['budget|vendor|city'], $budget_vendor_class -> returnColumnSize ('city'));
			}
			if (isset ($_POST['budget|vendor|state'])) {
				$_SESSION['vendor_information']['trusted_budget|vendor|state'] = (string) $budget_vendor_class -> superClean ($_POST['budget|vendor|state'], $budget_vendor_class -> returnColumnSize ('state'));
				// Make sure the state isn't too short.
				if (mb_strlen ($_SESSION['vendor_information']['trusted_budget|vendor|state']) >= $_HOAM_setting ['budget']['vendor']['minimum_state_length']) {
				} else {
					// The state is too short.
					$_SESSION ['error_class_item'] -> addError ('budget|vendor|state', sprintf ($_HOAM_language ['errors']['budget']['vendor']['state_too_short'], $_HOAM_setting ['budget']['vendor']['minimum_state_length']));
				}
				// !!! NOTE
				//
				// Also need to try and verify this list against the list of
				// valid states for the HOA's country. Could catch some
				// bugs, but cause problems for people who live in a diff.
				// country (eg, own property in US, live in Canada, etc.)
			}
			if (isset ($_POST['budget|vendor|postalcode'])) {
				$_SESSION['vendor_information']['trusted_budget|vendor|postalcode'] = (string) $budget_vendor_class -> superClean ($_POST['budget|vendor|postalcode'], $budget_vendor_class -> returnColumnSize ('postalcode'));
				// Make sure the postalcode isn't too short.
				if (mb_strlen ($_SESSION['vendor_information']['trusted_budget|vendor|postalcode']) > 0) {
					if (preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $_SESSION ['vendor_information']['trusted_budget|vendor|postalcode'], 1)) {
					} else {
						// The postalcode is invalid.
						$_SESSION ['error_class_item'] -> addError ('budget|vendor|postalcode', $_HOAM_language ['errors']['generic']['invalid-postalcode']);
					}
				}
				// !!! NOTE
				//
				// Also need to figure out a way to verify that the
				// postalcode is valid.
			}
			// We don't require a telephone number, but if one is entered make
			// sure it is valid.
			if (isset ($_POST['budget|vendor|telephone|fax'])) {
				$_SESSION['vendor_information']['trusted_budget|vendor|telephone|fax'] = (string) $budget_vendor_class -> superClean ($_POST['budget|vendor|telephone|fax'], $budget_vendor_class -> returnColumnSize ('telephone_fax'));
				if (mb_strlen ($_SESSION['vendor_information']['trusted_budget|vendor|telephone|fax']) > 0) {
					if (preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION ['vendor_information']['trusted_budget|vendor|telephone|fax'], 1)) {
					} else {
						// The telephone # is invalid.
						$_SESSION ['error_class_item'] -> addError ('budget|vendor|telephone|fax', $_HOAM_language ['errors']['generic']['invalid-telephone']);
					}
				}
			}
			if (isset ($_POST['budget|vendor|telephone|work'])) {
				$_SESSION['vendor_information']['trusted_budget|vendor|telephone|work'] = (string) $budget_vendor_class -> superClean ($_POST['budget|vendor|telephone|work'], $budget_vendor_class -> returnColumnSize ('telephone_work'));
				if (mb_strlen ($_SESSION['vendor_information']['trusted_budget|vendor|telephone|work']) > 0) {
					if (preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION ['vendor_information']['trusted_budget|vendor|telephone|work'], 1)) {
					} else {
						// The telephone # is invalid.
						$_SESSION ['error_class_item'] -> addError ('budget|vendor|telephone|work', $_HOAM_language ['errors']['generic']['invalid-telephone']);
					}
				}
			}
			if (isset ($_POST['budget|vendor|email'])) {
				$_SESSION['vendor_information']['trusted_budget|vendor|email'] = (string) $budget_vendor_class -> superClean ($_POST['budget|vendor|email'], $budget_vendor_class -> returnColumnSize ('email'));
				// We don't require the user to provide an email address, so we only check it if it's provided.
				if (mb_strlen ($_SESSION['vendor_information']['trusted_budget|vendor|email']) > 0) {
					if (!filter_var ($_SESSION['vendor_information']['trusted_budget|vendor|email'], FILTER_VALIDATE_EMAIL)) {
						$_SESSION['error_class_item'] -> addError ('budget|vendor|email', $_HOAM_language ['errors']['generic']['invalid-email']);
					}
				}
			}
			if (isset ($_POST['budget|vendor|federal_id'])) {
				$_SESSION['vendor_information']['trusted_budget|vendor|federal_id'] = (string) $budget_vendor_class -> superClean ($_POST['budget|vendor|federal_id'], $budget_vendor_class -> returnColumnSize ('federal_id'));
				// Make sure the federal id isn't too short (but, we can leave
				// it blank).
				if (mb_strlen ($_SESSION['vendor_information']['trusted_budget|vendor|federal_id']) > 0) {
					if (mb_strlen ($_SESSION['vendor_information']['trusted_budget|vendor|federal_id']) < $_HOAM_country ['vendor']['federal_id']['minlength']) {
						// The id is too short.
						$_SESSION ['error_class_item'] -> addError ('budget|vendor|federal_id', $_HOAM_language ['errors']['generic']['invalid-id']);
					}
					if (preg_replace ($_HOAM_country ['vendor']['federal_id']['match'], $_HOAM_country ['vendor']['federal_id']['format'], $_SESSION ['vendor_information']['trusted_budget|vendor|federal_id'], 1)) {
					} else {
						// The id is invalid.
						$_SESSION ['error_class_item'] -> addError ('budget|vendor|federal_id', $_HOAM_language ['errors']['generic']['invalid-id']);
					}
					// !!! NOTE
					//
					// Also need to figure out a way to verify that the
					// id is valid (can we query the irs?)
				}
			}
			if (isset ($_POST['budget|vendor|state_id'])) {
				$_SESSION['vendor_information']['trusted_budget|vendor|state_id'] = (string) $budget_vendor_class -> superClean ($_POST['budget|vendor|state_id'], $budget_vendor_class -> returnColumnSize ('state_id'));
				// Make sure the state id isn't too short (but, we can leave it
				// blank).
				if (mb_strlen ($_SESSION['vendor_information']['trusted_budget|vendor|state_id']) > 0) {
					if (mb_strlen ($_SESSION['vendor_information']['trusted_budget|vendor|state_id']) < $_HOAM_country ['vendor']['state_id']['minlength']) {
						// The id is too short.
						$_SESSION ['error_class_item'] -> addError ('budget|vendor|state_id', $_HOAM_language ['errors']['generic']['invalid-id']);
					}
					if (preg_replace ($_HOAM_country ['vendor']['state_id']['match'], $_HOAM_country ['vendor']['state_id']['format'], $_SESSION ['vendor_information']['trusted_budget|vendor|state_id'], 1)) {
					} else {
						// The if is invalid.
						$_SESSION ['error_class_item'] -> addError ('budget|vendor|state_id', $_HOAM_language ['errors']['generic']['invalid-id']);
					}
					// !!! NOTE
					//
					// Also need to figure out a way to verify that the
					// id is valid.
				}
			}
			if (isset ($_POST['budget|vendor|website'])) {
				$_SESSION['vendor_information']['trusted_budget|vendor|website'] = (string) $budget_vendor_class -> superClean ($_POST['budget|vendor|website'], $budget_vendor_class -> returnColumnSize ('website'));
				// We don't require the user to provide an URL, so we only check it if it's provided.
				if (mb_strlen ($_SESSION['vendor_information']['trusted_budget|vendor|website']) > 0) {
					if (!filter_var ($_SESSION['vendor_information']['trusted_budget|vendor|website'], FILTER_VALIDATE_URL)) {
						$_SESSION['error_class_item'] -> addError ('budget|vendor|website', $_HOAM_language ['errors']['generic']['invalid-url']);
					}
				}
			}

			$_SESSION ['vendor_information']['trusted_budget|vendor|flags'] = 0;
			
			$_HOAM_term_list = &$_HOAM_language ['budget']['term'];
			
			if (isset ($_POST ['budget|vendor|term'])) {
				$_SESSION ['vendor_information']['trusted_budget|vendor|term'] = (int) $budget_vendor_class -> superClean ($_POST['budget|vendor|term'], 20);
				$_SESSION ['vendor_information']['trusted_budget|vendor|flags'] += $_SESSION['vendor_information']['trusted_budget|vendor|term'];
			}
			$_SESSION['vendor_information']['trusted_budget|vendor|ignore_budget'] = (int) $_POST ['budget|vendor|ignore_average'];
			if ($_SESSION['vendor_information']['trusted_budget|vendor|ignore_budget']) {
				$_SESSION ['vendor_information']['trusted_budget|vendor|flags'] += $_HOAM_setting ['budget']['flags']['ignore_average'];
			}
			
// print_r ($_POST); print_r ($_SESSION['vendor_information']); $_SESSION['error_class_item'] -> showErrors (); exit();
			
			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION ['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST ['add'])) {
					header ('location: /admin/financial/vendor/add/');
				} else {
					header ('location: /admin/financial/vendor/edit/' . $_SESSION ['vendor_information']['trusted_vendor|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST ['add'])) {
					$vendor_id = $budget_vendor_class -> add ($_SESSION ['current_user']['id']);
					if ($vendor_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					}
				} else {
					// Use the information gathered above.
					$vendor_id = $_SESSION ['vendor_information']['trusted_vendor|id'];
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$budget_vendor_class -> changeName ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|name']);
				$budget_vendor_class -> changeCategoryId ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|category']);
				$budget_vendor_class -> changeAddress1 ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|address1']);
				$budget_vendor_class -> changeAddress2 ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|address2']);
				$budget_vendor_class -> changeAddress3 ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|address3']);
				$budget_vendor_class -> changeCity ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|city']);
				$budget_vendor_class -> changeState ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|state']);
				$budget_vendor_class -> changePostalcode ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|postalcode']);
				$budget_vendor_class -> changeTelephoneFax ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|telephone|fax']);
				$budget_vendor_class -> changeTelephoneWork ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|telephone|work']);
				$budget_vendor_class -> changeEmail ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|email']);
				$budget_vendor_class -> changeFederalId ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|federal_id']);
				$budget_vendor_class -> changeStateId ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|state_id']);
				$budget_vendor_class -> changeWebsite ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|website']);
				$budget_vendor_class -> changeFlags ($vendor_id, $_SESSION ['vendor_information']['trusted_budget|vendor|flags']);
				$budget_vendor_class -> changeDateModified ($vendor_id);

				// Process any attachments
				foreach ($_FILES as $file) {
					if ($file['name'] != NULL) {
						// Import the attachment into the database
						$id = HOAM_attachmentAdd ($file,
							$file,
							$_SESSION['current_user']['id'],
							$_HOAM_setting ['attachment']['flags']['budget_vendor'],
							'',
							$vendor_id);
						if (!$id) {
							echo $_HOAM_language ['errors']['attachment']['import'];
						}
					}
				}
				
				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION ['vendor_information'])) {
					unset ($_SESSION ['vendor_information']);
				}
				if (isset ($_SESSION ['error_class_item'])) {
					unset ($_SESSION ['error_class_item']);
				}
				
				header ('Location: /admin/financial/vendor/list/');
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
