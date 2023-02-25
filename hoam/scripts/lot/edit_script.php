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
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) || isset ($_POST['cancel']) || isset ($_POST['delete']) || isset ($_POST['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'attachments.php');
		
		$homeowner_class = new HOAM_homeowner ('homeowners');
		$lot_class = new HOAM_lot ('lots');
		
		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['lot_information'])) {
			unset ($_SESSION['lot_information']);
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
				$lot_id = (string) $lot_class -> superClean ($_POST['lot|id'], $lot_class -> returnColumnSize ('id'));
				if ($lot_class -> idExists ($lot_id)) {
					// Check that we aren't deleting a lot
					// that is in use.
					$homeowners = $homeowner_class -> listResidenceHomeowners ($lot_id);
					if (!empty ($homeowners)) {
						// Error Removing Item
						header ('Location: /error/inuse/');
					} else {
						$address = $lot_class -> returnAddress ($lot_id) . ' ' . $lot_class -> returnStreet ($lot_id);
						if ($lot_class -> deleteId ($lot_id)) {
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['lot']['deleted'], $address),
												HOAM_MESSAGE_NOTICE,
												array ('clean' => FALSE,
														'owner_id' => $lot_id,
														'flags' => $_HOAM_setting ['log']['flags']['lot']));
							header ('Location: /admin/lot/');
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
			$_SESSION['lot_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			if (isset ($_POST['lot|id'])) {
				$_SESSION['lot_information']['trusted_lot|id'] = (string) $lot_class -> superClean ($_POST['lot|id'], $lot_class -> returnColumnSize ('id'));
				if ($lot_class -> idExists ($_SESSION['lot_information']['trusted_lot|id'])) {
				} else {
					// The lot is unknown.
					$_SESSION ['error_class_item'] -> addError ('lot|id', $_HOAM_language ['errors']['generic_invalid_id']);
				}
			}
			if (isset ($_POST['lot|block'])) {
				$_SESSION['lot_information']['trusted_lot|block'] = $lot_class -> superClean ($_POST['lot|block'], $lot_class -> returnColumnSize ('block'));
			}
			if (isset ($_POST['lot|building'])) {
				$_SESSION['lot_information']['trusted_lot|building'] = $lot_class -> superClean ($_POST['lot|building'], $lot_class -> returnColumnSize ('building'));
			}
			if (isset ($_POST['lot|street'])) {
				$_SESSION['lot_information']['trusted_lot|street'] = ucwords (mb_strtolower ($lot_class -> superClean ($_POST['lot|street'], $lot_class -> returnColumnSize ('street'))));
				if ($lot_class -> valueExists ($_SESSION['lot_information']['trusted_lot|street'], 'street')) {
				} else {
					// The street is unknown.
					$_SESSION ['error_class_item'] -> addError ('lot|street', $_HOAM_language ['errors']['lot']['invalid_street']);
				}
			}
			if (isset ($_POST['lot|street-other_enable'])) {
				$_SESSION['lot_information']['trusted_lot|street-other_enable'] = (int) $lot_class -> superClean ($_POST['lot|street-other_enable'], 1);
				if ($_SESSION['lot_information']['trusted_lot|street-other_enable']) {
					$_SESSION['lot_information']['trusted_lot|street-other'] = ucwords (mb_strtolower ((string) $lot_class -> superClean ($_POST['lot|street-other'], $lot_class -> returnColumnSize ('street'))));
					// Make sure the street name isn't too short.
					if (mb_strlen ($_SESSION['lot_information']['trusted_lot|street-other']) >= $_HOAM_setting ['lot']['minimum_street_length']) {
					} else {
						// The street name is too short.
						$_SESSION ['error_class_item'] -> addError ('lot|street-other', sprintf ($_HOAM_language ['errors']['lot']['street_too_short'], $_HOAM_setting ['lot']['minimum_street_length']));
					}
					// Make sure the street name isn't a
					// duplicate of an existing street.
					if ($lot_class -> valueExists ($_SESSION['lot_information']['trusted_lot|street-other'], 'street')) {
						// The street is duplicate.
						$_SESSION ['error_class_item'] -> addError ('lot|street-other', $_HOAM_language ['errors']['lot']['street_duplicate']);
					} else {
					}
				}
			}
			if (isset ($_POST['lot|suite'])) {
				$_SESSION['lot_information']['trusted_lot|suite'] = $lot_class -> superClean ($_POST['lot|suite'], $lot_class -> returnColumnSize ('suite'));
			}
			if (isset ($_POST['lot|address'])) {
				$_SESSION['lot_information']['trusted_lot|address'] = $lot_class -> superClean ($_POST['lot|address'], $lot_class -> returnColumnSize ('address'));
				// Need to check that the address specified doesn't already
				// exist.
				if ($_SESSION['lot_information']['trusted_lot|street-other_enable']) {
					$lot = $lot_class -> returnId ($_SESSION['lot_information']['trusted_lot|address'], $_SESSION['lot_information']['trusted_lot|street-other'], $_SESSION['lot_information']['trusted_lot|building'], $_SESSION['lot_information']['trusted_lot|suite']);
				} else {
					$lot = $lot_class -> returnId ($_SESSION['lot_information']['trusted_lot|address'], $_SESSION['lot_information']['trusted_lot|street'], $_SESSION['lot_information']['trusted_lot|building'], $_SESSION['lot_information']['trusted_lot|suite']);
				}
				if ($lot AND ($lot == $_SESSION['lot_information']['trusted_lot|id'])) {
					// They match up, so we're editing an existing lot. This
					// will fail when editing a lot and changing it from a valid
					// existing street to a different street.
				} elseif ($lot AND $_SESSION['lot_information']['trusted_lot|id'] AND $_SESSION['lot_information']['trusted_lot|street-other_enable']) {
					// Now we catch if the street is being changed
				} elseif ($lot AND ($lot != $_SESSION['lot_information']['trusted_lot|id'])) {
					// The user is changing the address of the lot, but caused
					// a conflict.
					$_SESSION ['error_class_item'] -> addError ('lot|address', $_HOAM_language ['errors']['lot']['address_duplicate']);
				} elseif ($lot AND !isset ($_SESSION['lot_information']['trusted_lot|id'])) {
					// The user tried to add a lot which already exists.
					$_SESSION ['error_class_item'] -> addError ('lot|address', $_HOAM_language ['errors']['lot']['address_duplicate']);
				} else {
					// No errors, we're adding a new lot.
				}
			}
			if (isset ($_POST['lot|sqft'])) {
				$_SESSION['lot_information']['trusted_lot|sqft'] = (int) $lot_class -> superClean ($_POST['lot|sqft'], $lot_class -> returnColumnSize ('sqft'));
				if ($_SESSION['lot_information']['trusted_lot|sqft'] < $_HOAM_setting ['lot']['minimum_sqft_size']) {
					$_SESSION ['error_class_item'] -> addError ('lot|address', sprintf ($_HOAM_language ['errors']['lot']['sqft_too_small'], $_HOAM_setting ['lot']['minimum_sqft_size']));
				}
			}
			if (isset ($_POST['lot|lot'])) {
				$_SESSION['lot_information']['trusted_lot|lot'] = $lot_class -> superClean ($_POST['lot|lot'], $lot_class -> returnColumnSize ('lot'));
			}
			if (isset ($_POST['lot|plat'])) {
				$_SESSION['lot_information']['trusted_lot|plat'] = $lot_class -> superClean ($_POST['lot|plat'], $lot_class -> returnColumnSize ('plat'));
			}
			if (isset ($_POST['lot|latitude'])) {
				$_SESSION['lot_information']['trusted_lot|latitude'] = (float) $lot_class -> superClean ($_POST['lot|latitude'], $lot_class -> returnColumnSize ('latitude'));
				// Need to add a check that the latitude specified isn't out of bounds.
			}
			if (isset ($_POST['lot|longitude'])) {
				$_SESSION['lot_information']['trusted_lot|longitude'] = (float) $lot_class -> superClean ($_POST['lot|longitude'], $lot_class -> returnColumnSize ('longitude'));
				// Need to add a check that the longitude specified isn't out of bounds.
			}
			if (isset ($_POST['lot|comments'])) {
				$_SESSION['lot_information']['trusted_lot|comments'] = (string) $lot_class -> superClean ($_POST['lot|comments'], $lot_class -> returnColumnSize ('comment'));
			}

// print_r ($_POST); print_r ($_SESSION['lot_information']); print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();
			
			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/lot/add/');
				} else {
					header ('location: /admin/lot/edit/' . $_SESSION['lot_information']['trusted_lot|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				if (isset ($_POST['add'])) {
					$lot_id = $lot_class -> add ($_SESSION['current_user']['id']);
					if ($lot_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						// After adding a new lot, this var is checked.
						$_SESSION['lot_added'] = $lot_id;
						if ((isset ($_SESSION['lot_information']['trusted_lot|street-other_enable'])) AND $_SESSION['lot_information']['trusted_lot|street-other_enable']) {
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['lot']['created'], $_SESSION['lot_information']['trusted_lot|address'] . $_SESSION['lot_information']['trusted_lot|street-other']),
												HOAM_MESSAGE_NOTICE,
												array ('clean' => FALSE,
														'owner_id' => $lot_id,
														'flags' => $_HOAM_setting ['log']['flags']['lot']));
						} else {
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['lot']['created'], $_SESSION['lot_information']['trusted_lot|address'] . $_SESSION['lot_information']['trusted_lot|street']),
												HOAM_MESSAGE_NOTICE,
												array ('clean' => FALSE,
														'owner_id' => $lot_id,
														'flags' => $_HOAM_setting ['log']['flags']['lot']));
						}
					}
				} else {
					// Use the information gathered above.
					$lot_id = $_SESSION['lot_information']['trusted_lot|id'];
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$lot_class -> changeAddress ($lot_id, $_SESSION['lot_information']['trusted_lot|address']);
				$lot_class -> changeBlock ($lot_id, $_SESSION['lot_information']['trusted_lot|block']);
				$lot_class -> changeBuilding ($lot_id, $_SESSION['lot_information']['trusted_lot|building']);
				$lot_class -> changeComments ($lot_id, $_SESSION['lot_information']['trusted_lot|comment']);
				$lot_class -> changeLatitude ($lot_id, $_SESSION['lot_information']['trusted_lot|latitude']);
				$lot_class -> changeLongitude ($lot_id, $_SESSION['lot_information']['trusted_lot|longitude']);
				$lot_class -> changeLot ($lot_id, $_SESSION['lot_information']['trusted_lot|lot']);
				$lot_class -> changePlat ($lot_id, $_SESSION['lot_information']['trusted_lot|plat']);
				$lot_class -> changeSqft ($lot_id, $_SESSION['lot_information']['trusted_lot|sqft']);
				$lot_class -> changeSuite ($lot_id, $_SESSION['lot_information']['trusted_lot|suite']);
				if ((isset ($_SESSION['lot_information']['trusted_lot|street-other_enable'])) AND $_SESSION['lot_information']['trusted_lot|street-other_enable']) {
					$lot_class -> changeStreet ($lot_id, $_SESSION['lot_information']['trusted_lot|street-other']);
				} else {
					$lot_class -> changeStreet ($lot_id, $_SESSION['lot_information']['trusted_lot|street']);
				}

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
							$_HOAM_setting ['attachment']['flags']['lot'],
							$_SESSION['attachment_information']['trusted_attachment|description'],
							array ($lot_id));
						if (!$id) {
							echo $_HOAM_language ['errors']['attachment']['import'];
						}
					}
				}

				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION['lot_information'])) {
					unset ($_SESSION['lot_information']);
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
