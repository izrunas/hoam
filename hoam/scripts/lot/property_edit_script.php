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

// Is the current user allowed to add / edit lots?
if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {

		$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');		
		$homeowner_class = new HOAM_homeowner ('homeowners');
		$lot_class = new HOAM_lot ('lots');
		$property_class = new HOAM_property ('misc_property');
		
		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['property_information'])) {
			unset ($_SESSION['property_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			// Just send them back to the admin page
			header ('Location: /admin/');
		} elseif (isset ($_POST['delete'])) {
			// Make sure the current user is the owner of the item, OR has admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				// Make sure we found the lot
				$trusted_id = (string) $property_class -> superClean ($_POST['property|id'], $property_class -> returnColumnSize ('id'));
				if ($property_class -> idExists ($trusted_id)) {
					// Check that we aren't deleting a property item in use.
					if ($property_class -> isBitSet ($trusted_id, $_HOAM_setting ['property']['flags']['rented'])) {
						header ('Location: /error/inuse/');
					} else {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['property']['deleted'], $property_class -> returnDescription ($trusted_id),
									HOAM_MESSAGE_NOTICE,
									array ('owner_id' => $trusted_id,
											'flags' => $_HOAM_setting ['log']['flags']['property'])));
						if ($property_class -> deleteId ($trusted_id)) {
							header ('Location: /admin/lot/property/');
						} else {
							// Error Removing Item
							header ('Location: /error/remove/');
						}
					}
				} else {
					// The lot couldn't be found
					header ('Location: /error/unknown_id/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// If we're still here, then we must be adding or
			// modifying an existing lot. Start populating the
			// lot_information array, and check for errors.
			$_SESSION['property_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			if (isset ($_POST['property|id'])) {
				$_SESSION['property_information']['trusted_property|id'] = (string) $property_class -> superClean ($_POST['property|id'], $property_class -> returnColumnSize ('id'));
				if ($property_class -> idExists ($_SESSION['property_information']['trusted_property|id'])) {
				} else {
					// The property is unknown.
					$_SESSION ['error_class_item'] -> addError ('property|id', $_HOAM_language ['errors']['generic_invalid_id']);
				}
			}
			if (isset ($_POST['property|description'])) {
				$_SESSION['property_information']['trusted_property|description'] = (string) $property_class -> superClean ($_POST['property|description'], $property_class -> returnColumnSize ('description'));
			}
			if (isset ($_POST['property|deposit_amount'])) {
				$_SESSION['property_information']['trusted_property|deposit_amount'] = (float) $property_class -> superClean ($_POST['property|deposit_amount'], $property_class -> returnColumnSize ('deposit_amount'));
				if ($_SESSION['property_information']['trusted_property|deposit_amount'] < 0) {
					$_SESSION ['error_class_item'] -> addError ('property|deposit_amount', sprintf ($_HOAM_language ['errors']['generic']['value-too-small'], 0));
				}
			}
			if (isset ($_POST['property|fee_amount'])) {
				$_SESSION['property_information']['trusted_property|fee_amount'] = (float) $property_class -> superClean ($_POST['property|fee_amount'], $property_class -> returnColumnSize ('fee_amount'));
				if ($_SESSION['property_information']['trusted_property|fee_amount'] < 0) {
					$_SESSION ['error_class_item'] -> addError ('property|fee_amount', sprintf ($_HOAM_language ['errors']['generic']['value-too-small'], 1));
				}
			}
			if (isset ($_POST['property|rental_amount'])) {
				$_SESSION['property_information']['trusted_property|rental_amount'] = (float) $property_class -> superClean ($_POST['property|rental_amount'], $property_class -> returnColumnSize ('rental_amount'));
				if ($_SESSION['property_information']['trusted_property|rental_amount'] < 1) {
					$_SESSION ['error_class_item'] -> addError ('property|rental_amount', sprintf ($_HOAM_language ['errors']['generic']['value-too-small'], 1));
				}
			}
			if (isset ($_POST['property|frequency'])) {
				$_SESSION['property_information']['trusted_property|frequency'] = (int) $property_class -> superClean ($_POST['property|frequency'], 20);
			}
			if (isset ($_POST['property|renew'])) {
				$_SESSION['property_information']['trusted_property|renew'] = (int) $_POST['property|renew'];
			} else {
				$_SESSION['property_information']['trusted_property|renew'] = (int) 0;
			}
			if (isset ($_POST['property|rented'])) {
				$_SESSION['property_information']['trusted_property|rented'] = (int) $_POST['property|rented'];
			} else {
				$_SESSION['property_information']['trusted_property|rented'] = (int) 0;
			}
			if (isset ($_POST['property|owner'])) {
				$_SESSION['property_information']['trusted_property|owner'] = (string) $property_class -> superClean ($_POST['property|owner'], $property_class -> returnColumnSize ('owner_id'));
				if ($homeowner_class -> idExists ($_SESSION['property_information']['trusted_property|owner'])) {
				} else {
					$_SESSION ['error_class_item'] -> addError ('property|owner', $_HOAM_language ['errors']['homeowner']['unknown']);
				}
			}
			if (isset ($_POST['property|vendor'])) {
				$_SESSION['property_information']['trusted_property|vendor'] = (string) $property_class -> superClean ($_POST['property|vendor'], $property_class -> returnColumnSize ('vendor_id'));
				if ($budget_vendor_class -> idExists ($_SESSION['property_information']['trusted_property|vendor'])) {
				} else {
					$_SESSION ['error_class_item'] -> addError ('property|vendor', $_HOAM_language ['errors']['generic']['unknown-id']);
				}
			} else {
				$_SESSION ['error_class_item'] -> addError ('property|vendor', $_HOAM_language ['errors']['generic']['invalid-id']);
			}
			if (isset ($_POST['property|comments'])) {
				$_SESSION['property_information']['trusted_property|comments'] = (string) $property_class -> superClean ($_POST['property|comments'], $property_class -> returnColumnSize ('comments'));
			}

// print_r ($_POST); print_r ($_SESSION['property_information']); print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();
			
			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/lot/property/add/');
				} else {
					header ('location: /admin/lot/property/edit/' . $_SESSION['property_information']['trusted_property|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST['add'])) {
					$property_id = $property_class -> add ($_SESSION['current_user']['id']);
					if ($property_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['property']['created'], $_SESSION['property_information']['trusted_property|description']),
											HOAM_MESSAGE_NOTICE,
											array ('owner_id' => $property_id,
													'flags' => $_HOAM_setting ['log']['flags']['property']));
					}
				} else {
					// Use the information gathered above.
					$property_id = $_SESSION['property_information']['trusted_property|id'];
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['property']['modified'], $_SESSION['property_information']['trusted_property|description']),
										HOAM_MESSAGE_NOTICE,
										array ('owner_id' => $property_id,
												'flags' => $_HOAM_setting ['log']['flags']['property']));
				}

				$property_class -> changeDescription ($property_id, $_SESSION['property_information']['trusted_property|description']);
				$property_class -> changeDepositAmount ($property_id, $_SESSION['property_information']['trusted_property|deposit_amount']);
				$property_class -> changeFeeAmount ($property_id, $_SESSION['property_information']['trusted_property|fee_amount']);
				$property_class -> changeRentalAmount ($property_id, $_SESSION['property_information']['trusted_property|rental_amount']);
				$property_class -> changeOwnerId ($property_id, $_SESSION['property_information']['trusted_property|owner']);
				$property_class -> changeVendorId ($property_id, $_SESSION['property_information']['trusted_property|vendor']);
				$property_class -> changeComments ($property_id, $_SESSION['property_information']['trusted_property|comments']);
				if (is_true ($_SESSION['property_information']['trusted_property|renew'])) {
					$property_class -> setBit ($property_id, $_HOAM_setting ['property']['flags']['renew']);
				} else {
					$property_class -> clearBit ($property_id, $_HOAM_setting ['property']['flags']['renew']);
				}
				if (is_true ($_SESSION['property_information']['trusted_property|rented'])) {
					$property_class -> setBit ($property_id, $_HOAM_setting ['property']['flags']['rented']);
				} else {
					$property_class -> clearBit ($property_id, $_HOAM_setting ['property']['flags']['rented']);
				}
				$property_class -> clearBit ($property_id, $_HOAM_setting ['property']['flags']['annual']);
				$property_class -> clearBit ($property_id, $_HOAM_setting ['property']['flags']['monthly']);
				$property_class -> clearBit ($property_id, $_HOAM_setting ['property']['flags']['weekly']);
				$property_class -> clearBit ($property_id, $_HOAM_setting ['property']['flags']['daily']);
				$property_class -> setBit ($property_id, $_SESSION['property_information']['trusted_property|frequency']);
				$property_class -> changeDateModified ($property_id);
				
				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION['property_information'])) {
					unset ($_SESSION['property_information']);
				}
				if (isset ($_SESSION['error_class_item'])) {
					// Clear the error_class_item variable since everything worked ok.
					unset ($_SESSION['error_class_item']);
				}

				header ('Location: /admin/lot/');
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
