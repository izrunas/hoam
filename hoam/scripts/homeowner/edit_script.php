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
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'attachments.php');

		$attachment_class = new HOAM_attachments ('attachments');		
		$budget_class = new HOAM_budget ('budget');
		$homeowner_class = new HOAM_homeowner ('homeowners');
		$lot_class = new HOAM_lot ('lots');

		// If this is the second (or greater) time through the script (ie,
		// there were errors that had to be fixed before committing the
		// changes) get rid of the old cached information the user entered,
		// we're going to populate it with information just received from the
		// form posting.
		if (isset ($_SESSION['homeowner_information'])) {
			unset ($_SESSION['homeowner_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			header ('Location: /admin/homeowner/');
			exit ();
		} elseif (isset ($_POST['delete'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
				$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
				$homeowner_id = (string) $homeowner_class -> superClean ($_POST['homeowner|id'], $homeowner_class -> returnColumnSize ('id'));
				$attachment_list = $attachment_class -> listWithOwner ($homeowner_id);
				if (is_array ($attachment_list)) {
					foreach ($attachment_list as $attachment) {
						$attachment = $attachment_class -> listAll ($attachment);
						if (unlink (HOAM_PATH . HOAM_attachmentReturnPath ($attachment[0]['id']))) {
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['file'], $attachment[0]['filename']),
												HOAM_MESSAGE_NOTICE,
												array ('flags' => $_HOAM_setting ['log']['flags']['homeowner'],
														'owner_id' => $homeowner_id));
						}
						if ($_HOAM_setting ['attachment']['preview']['enable']) {
							$preview = HOAM_attachmentReturnPreviewPath ($attachment[0]['id']);
							if ($preview) {
								unlink (HOAM_PATH . $preview);
								$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['preview'], $attachment[0]['filename']),
													HOAM_MESSAGE_NOTICE,
													array ('flags' => $_HOAM_setting ['log']['flags']['homeowner'],
															'owner_id' => $homeowner_id));
							}
						}
						if ($attachment_class -> deleteId ($attachment[0]['id'])) { 
							$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['attachment']['deleted']['database'], $attachment[0]['id']),
												HOAM_MESSAGE_NOTICE,
												array ('flags' => $_HOAM_setting ['log']['flags']['homeowner'],
														'owner_id' => $homeowner_id));
						}
					}
				}
				if ($homeowner_class -> deleteId ($homeowner_id)) {
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['homeowner']['deleted'], $homeowner_class -> returnName ($homeowner_id), HOAM_returnAddress ($homeowner_class -> returnLotId ($homeowner_id))),
										HOAM_MESSAGE_NOTICE,
										array ('owner_id' => $homeowner_id,
												'flags' => $_HOAM_setting ['log']['flags']['homeowner']));
					// Send the user back to the homeowner administration page.
					header ('Location: /admin/homeowner/');
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
			// existing homeowner. Start populating the homeowner_information
			// array, and check for errors.
			$_SESSION['homeowner_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');

			if (isset ($_POST['homeowner|id'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|id'] = (string) $homeowner_class -> superClean ($_POST['homeowner|id'], $homeowner_class -> returnColumnSize ('id'));
				if ($homeowner_class -> idExists ($_SESSION['homeowner_information']['trusted_homeowner|id'])) {
				} else {
					// The homeowner is unknown.
					$_SESSION ['error_class_item'] -> addError ('homeowner|id', $_HOAM_language ['errors']['homeowner']['unknown_id']);
				}
			}
			// We MUST have a valid purchase date. This is how we know who the
			// current homeowner is, and all of the report generation is based
			// off of it.
			$_SESSION['homeowner_information']['trusted_homeowner|purchasedate'] = (string) $homeowner_class -> superClean ($_POST['homeowner|purchasedate'], $homeowner_class -> returnColumnSize ('purchasedate'));
			if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate']) < $homeowner_class -> returnColumnSize ('purchasedate')) {
				$_SESSION['error_class_item'] -> addError ('homeowner|purchasedate', $_HOAM_language ['errors']['generic']['date-too-short']);
			}
			// Make sure we have a valid date
			if (checkdate (date ('m', strtotime ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate'])), date ('d', strtotime ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate'])), date ('Y', strtotime ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate'])))) {
			} else {
				$_SESSION['error_class_item'] -> addError ('homeowner|purchasedate', $_HOAM_language ['errors']['generic']['invalid-date']);
			}
			// Make sure the date isn't in the future
			if (date ($_HOAM_country ['date']['format_mysql']) < date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate']))) {
				$_SESSION['error_class_item'] -> addError ('homeowner|purchasedate', $_HOAM_language ['errors']['generic']['date_future']);
			}

			// We MUST have a valid sale date. This is how we know who the
			// current homeowner is, and all of the report generation is based
			// off of it.
			//
			// The current homeowner will NOT have a sale date (obviously).
			$_SESSION['homeowner_information']['trusted_homeowner|saledate'] = (string) $homeowner_class -> superClean ($_POST['homeowner|saledate'], $homeowner_class -> returnColumnSize ('saledate'));
			if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|saledate']) > 0) {
				if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|saledate']) != $homeowner_class -> returnColumnSize ('saledate')) {
					$_SESSION['error_class_item'] -> addError ('homeowner|saledate', $_HOAM_language ['errors']['generic']['date-too-short']);
				}
				// Make sure we have a valid date
				if (checkdate (date ('m', strtotime ($_SESSION['homeowner_information']['trusted_homeowner|saledate'])), date ('d', strtotime ($_SESSION['homeowner_information']['trusted_homeowner|saledate'])), date ('Y', strtotime ($_SESSION['homeowner_information']['trusted_homeowner|saledate'])))) {
				} else {
					$_SESSION['error_class_item'] -> addError ('homeowner|saledate', $_HOAM_language ['errors']['generic']['invalid-date']);
				}
				// Make sure the date isn't in the future
				if (date ($_HOAM_country ['date']['format_mysql']) < date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|saledate']))) {
					$_SESSION['error_class_item'] -> addError ('homeowner|saledate', $_HOAM_language ['errors']['generic']['date_future']);
				}
			}
			
			$_SESSION['homeowner_information']['trusted_homeowner|address'] = $homeowner_class -> superClean ($_POST['homeowner|address'], $homeowner_class -> returnColumnSize ('lot_id'));
			// Make sure the address exists.
			if ($lot_class -> idExists ($_SESSION['homeowner_information']['trusted_homeowner|address'])) {
				// We need to make sure that there isn't already an
				// existing 'current' homeowner at this address.
				if (isset ($_SESSION['homeowner_information']['trusted_homeowner|id'])) {
					if ($_SESSION['homeowner_information']['trusted_homeowner|id'] != $homeowner_class -> returnCurrentOwner ($_SESSION['homeowner_information']['trusted_homeowner|address'])) {
						// Are we trying to edit an old homeowner account, who
						// has already sold the property?
						if (isset ($_SESSION['homeowner_information']['trusted_homeowner|saledate']) AND
							($_SESSION['homeowner_information']['trusted_homeowner|address'] == $homeowner_class -> returnLotId ($_SESSION['homeowner_information']['trusted_homeowner|id'])) AND
							($_SESSION['error_class_item'] -> showCategory ('homeowner|saledate') === NULL)) {
							// OK, we must be editing an older homeowner file.
						} else {
							// If the trusted_id doesn't match the result from
							// the current homeowner lookup, then the edit is
							// trying to modify a homeowner that doesn't live at
							// this address. Throw an error.
							$_SESSION ['error_class_item'] -> addError ('homeowner|address', $_HOAM_language ['errors']['homeowner']['current_exists']);
						}
					}
				} elseif ($homeowner_class -> returnCurrentOwner ($_SESSION['homeowner_information']['trusted_homeowner|address'])) {
					// Similar test to above; if we get any result (ie, not
					// FALSE) then a 'current' homeowner already exists at
					// this address. This will happen when we're adding a
					// new homeowner to an address that already has an
					// owner.
					if (isset ($_POST['homeowner|previous|close'])) {
						// We were told to close out the current homeowner,
						// so let's do that.
						$previous_homeowner = $homeowner_class -> returnCurrentOwner ($_SESSION['homeowner_information']['trusted_homeowner|address']);
						$account_changes = '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['saledate'] . '</td>';
						$account_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_short'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate'])) . '</td>';
						$account_changes .= '<td>&nbsp;</td>';
						$account_changes .= '</tr>';
						$thead = sprintf ($_HOAM_language ['log']['generic']['table']['thead'], $_HOAM_language ['common']['setting'], ucfirst ($_HOAM_language ['common']['new-value']), ucfirst ($_HOAM_language ['common']['old-value']));
						$tbody = sprintf ($_HOAM_language ['log']['generic']['table']['tbody'], $account_changes);
						$table = sprintf ($_HOAM_language ['log']['generic']['table']['default'], $_HOAM_language ['log']['old_new']['table']['colgroup'], $thead, $tbody);
						$_HOAM_log -> add ($table,
											HOAM_MESSAGE_INFORMATIONAL,
											array ('clean' => FALSE,
													'flags' => $_HOAM_setting ['log']['flags']['homeowner'],
													'owner_id' => $previous_homeowner));
						$homeowner_class -> changeSaleDate ($previous_homeowner, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate'])));
					} else {
						$_SESSION ['error_class_item'] -> addError ('homeowner|address', $_HOAM_language ['errors']['homeowner']['current_exists']);
					}
				}
			} else {
				// The address is unknown.
				$_SESSION ['error_class_item'] -> addError ('homeowner|address', $_HOAM_language ['errors']['homeowner']['unknown_address']);
			}

			$_SESSION['homeowner_information']['trusted_homeowner|name'] = (string) $homeowner_class -> superClean ($_POST['homeowner|name'], $homeowner_class -> returnColumnSize ('name'));
			if (isset ($_POST['add'])) {
				// Only when we're adding a new homeowner, force the name into
				// ucwords format. This is primarily because when adding new
				// owners the name is often copied and pasted from the county
				// source that is in all uppercase. When we're later editing
				// the name, assume that it's being done correctly and leave the
				// case alone.
				$_SESSION['homeowner_information']['trusted_homeowner|name'] = ucwords (mb_strtolower ($_SESSION['homeowner_information']['trusted_homeowner|name']));
			}
			// Make sure the homeowner's name isn't too short.
			if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|name']) >= $_HOAM_setting ['homeowner']['minimum_name_length']) {
			} else {
				// The name is too short.
				$_SESSION ['error_class_item'] -> addError ('homeowner|name', sprintf ($_HOAM_language ['errors']['homeowner']['name_too_short'], $_HOAM_setting ['homeowner']['minimum_name_length']));
			}

			$_SESSION['homeowner_information']['trusted_homeowner|residence_mailing'] = (int) $homeowner_class -> superClean ($_POST['homeowner|residence_mailing'], 1);
			// There is no test, just getting the information.
			if (is_true ($_SESSION['homeowner_information']['trusted_homeowner|residence_mailing'])) {
				// If the homeowner receives mail at the residence, then
				// there's no need to gather this information. We'll overwrite
				// it during processing.
			} else {
				// The homeowner does NOT receive mail at their residence. Use
				// the entered information.
				if (isset ($_POST['homeowner|resident'])) {
					$_SESSION['homeowner_information']['trusted_homeowner|resident'] = (int) $homeowner_class -> superClean ($_POST['homeowner|resident'], 1);
					// There is no test, just getting the information.
				} else {
					$_SESSION['homeowner_information']['trusted_homeowner|resident'] = 0;
				}
				$_SESSION['homeowner_information']['trusted_homeowner|address1'] = ucwords (mb_strtolower ((string) $homeowner_class -> superClean ($_POST['homeowner|address1'], $homeowner_class -> returnColumnSize ('address1'))));
				// Make sure the address1 isn't too short.
				if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|address1']) >= $_HOAM_setting ['homeowner']['minimum_address1_length']) {
				} else {
					// The address1 is too short.
					$_SESSION ['error_class_item'] -> addError ('homeowner|address1', sprintf ($_HOAM_language ['errors']['homeowner']['address1_too_short'], $_HOAM_setting ['homeowner']['minimum_address1_length']));
				}
				if (isset ($_POST['homeowner|address2'])) {
					$_SESSION['homeowner_information']['trusted_homeowner|address2'] = ucwords (mb_strtolower ((string) $homeowner_class -> superClean ($_POST['homeowner|address2'], $homeowner_class -> returnColumnSize ('address2'))));
					// Make sure the address2 isn't too short.
					if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|address2']) >= $_HOAM_setting ['homeowner']['minimum_address2_length']) {
					} else {
						// The address2 is too short.
						$_SESSION ['error_class_item'] -> addError ('homeowner|address2', sprintf ($_HOAM_language ['errors']['homeowner']['address2_too_short'], $_HOAM_setting ['homeowner']['minimum_address2_length']));
					}
				}
				$_SESSION['homeowner_information']['trusted_homeowner|address3'] = ucwords (mb_strtolower ((string) $homeowner_class -> superClean ($_POST['homeowner|address3'], $homeowner_class -> returnColumnSize ('address3'))));
				// Make sure the address3 isn't too short.
				if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|address3']) >= $_HOAM_setting ['homeowner']['minimum_address3_length']) {
				} else {
					// The address3 is too short.
					$_SESSION ['error_class_item'] -> addError ('homeowner|address3', sprintf ($_HOAM_language ['errors']['homeowner']['address3_too_short'], $_HOAM_setting ['homeowner']['minimum_address3_length']));
				}
				$_SESSION['homeowner_information']['trusted_homeowner|city'] = ucwords (mb_strtolower ((string) $homeowner_class -> superClean ($_POST['homeowner|city'], $homeowner_class -> returnColumnSize ('city'))));
				// Make sure the city isn't too short.
				if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|city']) >= $_HOAM_setting ['homeowner']['minimum_city_length']) {
				} else {
					// The city is too short.
					$_SESSION ['error_class_item'] -> addError ('homeowner|city', sprintf ($_HOAM_language ['errors']['homeowner']['city_too_short'], $_HOAM_setting ['homeowner']['minimum_city_length']));
				}
				// Is there a way to validate the city? Does the USPS have
				// an API that can be used?
				$_SESSION['homeowner_information']['trusted_homeowner|state'] = (string) $homeowner_class -> superClean ($_POST['homeowner|state'], $homeowner_class -> returnColumnSize ('state'));
				// Make sure the state isn't too short.
				if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|state']) >= $_HOAM_setting ['homeowner']['minimum_state_length']) {
				} else {
					// The state is too short.
					$_SESSION ['error_class_item'] -> addError ('homeowner|state', sprintf ($_HOAM_language ['errors']['homeowner']['state_too_short'], $_HOAM_setting ['homeowner']['minimum_state_length']));
				}
				// !!! NOTE
				//
				// Also need to try and verify this list against the list of
				// valid states for the HOA's country. Could catch some
				// bugs, but cause problems for people who live in a diff.
				// country (eg, own property in US, live in Canada, etc.)
				$_SESSION['homeowner_information']['trusted_homeowner|postalcode'] = (string) $homeowner_class -> superClean ($_POST['homeowner|postalcode'], $homeowner_class -> returnColumnSize ('zipcode'));
				// Make sure the postalcode isn't too short.
				if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|postalcode']) < $_HOAM_country ['postalcode']['minlength']) {
					// The postalcode is too short.
					$_SESSION ['error_class_item'] -> addError ('homeowner|postalcode', sprintf ($_HOAM_language ['errors']['generic']['postalcode-too-short'], $_HOAM_country ['postalcode']['minlength'], $_HOAM_country ['postalcode']['length']));
				}
				if (preg_replace ($_HOAM_country ["postalcode"]['match'], $_HOAM_country ["postalcode"]['format'], $_SESSION ['homeowner_information']['trusted_homeowner|postalcode'], 1)) {
				} else {
					// The postalcode is invalid.
					$_SESSION ['error_class_item'] -> addError ('homeowner|postalcode', $_HOAM_language ['errors']['generic']['invalid-postalcode']);
				}
				// !!! NOTE
				//
				// Also need to figure out a way to verify that the
				// postalcode is valid.
			}
			// We don't require a telephone number, but if one is entered make
			// sure it is valid.
			$_SESSION['homeowner_information']['trusted_homeowner|telephone|home'] = (string) $homeowner_class -> superClean ($_POST['homeowner|telephone|home'], $homeowner_class -> returnColumnSize ('telephone_home'));
			if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|telephone|home']) > 0) {
				if (preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION ['homeowner_information']['trusted_homeowner|telephone|home'], 1)) {
				} else {
					// The telephone # is invalid.
					$_SESSION ['error_class_item'] -> addError ('homeowner|telephone|home', $_HOAM_language ['errors']['generic']['invalid-telephone']);
				}
			}
			// We don't require a telephone number, but if one is entered make
			// sure it is valid.
			$_SESSION['homeowner_information']['trusted_homeowner|telephone|fax'] = (string) $homeowner_class -> superClean ($_POST['homeowner|telephone|fax'], $homeowner_class -> returnColumnSize ('telephone_fax'));
			if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|telephone|fax']) > 0) {
				if (preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION ['homeowner_information']['trusted_homeowner|telephone|fax'], 1)) {
				} else {
					// The telephone # is invalid.
					$_SESSION ['error_class_item'] -> addError ('homeowner|telephone|fax', $_HOAM_language ['errors']['generic']['invalid-telephone']);
				}
			}
			// We don't require a telephone number, but if one is entered make
			// sure it is valid.
			$_SESSION['homeowner_information']['trusted_homeowner|telephone|mobile'] = (string) $homeowner_class -> superClean ($_POST['homeowner|telephone|mobile'], $homeowner_class -> returnColumnSize ('telephone_mobile'));
			if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|telephone|mobile']) > 0) {
				if (preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION ['homeowner_information']['trusted_homeowner|telephone|mobile'], 1)) {
				} else {
					// The telephone # is invalid.
					$_SESSION ['error_class_item'] -> addError ('homeowner|telephone|mobile', $_HOAM_language ['errors']['generic']['invalid-telephone']);
				}
			}
			// We don't require a telephone number, but if one is entered make
			// sure it is valid.
			$_SESSION['homeowner_information']['trusted_homeowner|telephone|work'] = (string) $homeowner_class -> superClean ($_POST['homeowner|telephone|work'], $homeowner_class -> returnColumnSize ('telephone_work'));
			if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|telephone|work']) > 0) {
				if (preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION ['homeowner_information']['trusted_homeowner|telephone|work'], 1)) {
				} else {
					// The telephone # is invalid.
					$_SESSION ['error_class_item'] -> addError ('homeowner|telephone|work', $_HOAM_language ['errors']['generic']['invalid-telephone']);
				}
			}
			$_SESSION['homeowner_information']['trusted_homeowner|access_code'] = (string) $homeowner_class -> superClean ($_POST['homeowner|access_code'], $homeowner_class -> returnColumnSize ('access_code'));
			// We don't require an email address, but if one is entered make
			// sure it is valid.
			$_SESSION['homeowner_information']['trusted_homeowner|email'] = (string) $homeowner_class -> superClean ($_POST['homeowner|email'], $homeowner_class -> returnColumnSize ('email'));
			// We don't require the user to provide an email address, so we only check it if it's provided.
			if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|email']) > 0) {
				if (!filter_var ($_SESSION['homeowner_information']['trusted_homeowner|email'], FILTER_VALIDATE_EMAIL)) {
					$_SESSION['error_class_item'] -> addError ('homeowner|email', $_HOAM_language ['errors']['generic']['invalid-email']);
				}
			}
			// All the stuff for validating attachments needs to be added
			if (isset ($_FILES)) {
			}

			if (isset ($_POST['homeowner|ignore_violations'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|ignore_violations'] = (int) $homeowner_class -> superClean ($_POST['homeowner|ignore_violations'], 1);
			}
			if (isset ($_POST['homeowner|assessment|exempt'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|assessment|exempt'] = (int) $homeowner_class -> superClean ($_POST['homeowner|assessment|exempt'], 1);
			}
			if (isset ($_POST['homeowner|flags|no_fees'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|no_fees'] = (int) $homeowner_class -> superClean ($_POST['homeowner|flags|no_fees'], 1);
			}
			if (isset ($_POST['homeowner|assessment|payment_plan'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan'] = (int) $homeowner_class -> superClean ($_POST['homeowner|assessment|payment_plan'], 1);
			}
			if (isset ($_POST['homeowner|assessment|payment_plan_default'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan_default'] = (int) $homeowner_class -> superClean ($_POST['homeowner|assessment|payment_plan_default'], 1);
			}
			if (isset ($_POST['homeowner|assessment|payment_plan_details'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan_details'] = $homeowner_class -> superClean ($_POST['homeowner|assessment|payment_plan_details'], $homeowner_class -> returnColumnSize ('payment_plan_details'));
			}
			if (isset ($_POST['homeowner|comments'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|comments'] = (string) $homeowner_class -> superClean ($_POST['homeowner|comments'], $homeowner_class -> returnColumnSize ('comments'));
			}

			if (isset ($_POST['homeowner|flags|bankrupt'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|bankrupt'] = (int) $homeowner_class -> superClean ($_POST['homeowner|flags|bankrupt'], 1);
				$_SESSION['homeowner_information']['trusted_homeowner|assessment|bankrupt_date'] = (string) $homeowner_class -> superClean ($_POST['homeowner|assessment|bankrupt_date'], $homeowner_class -> returnColumnSize ('bankruptcy_date'));
				if (mb_strlen ($_SESSION['homeowner_information']['trusted_homeowner|assessment|bankrupt_date']) < $homeowner_class -> returnColumnSize ('bankruptcy_date')) {
					$_SESSION['error_class_item'] -> addError ('homeowner|assessment|bankrupt_date', $_HOAM_language ['errors']['generic']['date-too-short']);
				}
				// Make sure we have a valid date
				if (checkdate (date ('m', strtotime ($_SESSION['homeowner_information']['trusted_homeowner|assessment|bankrupt_date'])), date ('d', strtotime ($_SESSION['homeowner_information']['trusted_homeowner|assessment|bankrupt_date'])), date ('Y', strtotime ($_SESSION['homeowner_information']['trusted_homeowner|assessment|bankrupt_date'])))) {
				} else {
					$_SESSION['error_class_item'] -> addError ('homeowner|assessment|bankrupt_date', $_HOAM_language ['errors']['generic']['invalid-date']);
				}
				// Make sure the date isn't in the future
				if (date ($_HOAM_country ['date']['format_mysql']) < date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|assessment|bankrupt_date']))) {
					$_SESSION['error_class_item'] -> addError ('homeowner|assessment|bankrupt_date', $_HOAM_language ['errors']['generic']['date_future']);
				}
			}
			if (isset ($_POST['homeowner|flags|service_member'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|service_member'] = (int) $homeowner_class -> superClean ($_POST['homeowner|flags|service_member'], 1);
			}
			
			if (isset ($_POST['homeowner|flags|late_notice'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|late_notice'] = (int) $homeowner_class -> superClean ($_POST['homeowner|flags|late_notice'], 1);
			}
			if (isset ($_POST['homeowner|flags|late_delinquent'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|late_delinquent'] = (int) $homeowner_class -> superClean ($_POST['homeowner|flags|late_delinquent'], 1);
			}
			if (isset ($_POST['homeowner|flags|late_default'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|late_default'] = (int) $homeowner_class -> superClean ($_POST['homeowner|flags|late_default'], 1);
			}
			if (isset ($_POST['homeowner|flags|late_lien'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|late_lien'] = (int) $homeowner_class -> superClean ($_POST['homeowner|flags|late_lien'], 1);
			}
			if (isset ($_POST['homeowner|flags|late_attorney'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|late_attorney'] = (int) $homeowner_class -> superClean ($_POST['homeowner|flags|late_attorney'], 1);
			}
			if (isset ($_POST['homeowner|flags|late_attorney_demand'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|late_attorney_demand'] = (int) $homeowner_class -> superClean ($_POST['homeowner|flags|late_attorney_demand'], 1);
			}
			if (isset ($_POST['homeowner|flags|late_attorney_foreclosure'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|late_attorney_foreclosure'] = (int) $homeowner_class -> superClean ($_POST['homeowner|flags|late_attorney_foreclosure'], 1);
			}
			if (isset ($_POST['homeowner|flags|late_attorney_eviction'])) {
				$_SESSION['homeowner_information']['trusted_homeowner|flags|late_attorney_eviction']+= (int) $homeowner_class -> superClean ($_POST['homeowner|flags|late_attorney_eviction'], 1);
			}
			
// print_r ($_POST); print_r ($_FILES); print_r ($_SESSION['homeowner_information']); print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before
			// committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/homeowner/add/');
					exit ();
				} else {
					header ('location: /admin/homeowner/edit/' . $_SESSION['homeowner_information']['trusted_homeowner|id'] . '/');
					exit ();
				}
			} else {
				// Finally! We have good data and can start modifying the
				// database.
				if (isset ($_POST['add'])) {
					$homeowner_id = $homeowner_class -> add ($_SESSION['current_user']['id']);
					if ($homeowner_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
						exit ();
					} else {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['homeowner']['created'], $_SESSION['homeowner_information']['trusted_homeowner|name'], HOAM_returnAddress ($_SESSION['homeowner_information']['trusted_homeowner|address'])),
											HOAM_MESSAGE_NOTICE,
											array ('owner_id' => $homeowner_id,
													'flags' => $_HOAM_setting ['log']['flags']['homeowner']));
						// Were we told to migrate any budget charges from the
						// previous homeowner?
						if (isset ($_POST['homeowner|previous|charges'])) {
							// $previous homeowner was set above when we closed
							// out the old account.
							$charge_list = $budget_class -> listCurrentChargesForCustomer ($previous_homeowner);
							foreach ($charge_list as $charge) {
								$budget_class -> changeCustomerId ($charge, $homeowner_id);
							}
							$_HOAM_log -> add ($_HOAM_language ['log']['homeowner']['transfer_charges'],
												HOAM_MESSAGE_NOTICE,
												array ('owner_id' => $homeowner_id,
														'flags' => $_HOAM_setting ['log']['flags']['homeowner']));
							// $previous_homeowner is reused from earlier.
							$_HOAM_log -> add ($_HOAM_language ['log']['homeowner']['transfer_charges'],
												HOAM_MESSAGE_NOTICE,
												array ('owner_id' => $previous_homeowner,
														'flags' => $_HOAM_setting ['log']['flags']['homeowner']));
						}
						// Were we told to close the existing sale entry?
						if (isset ($_POST['homeowner|previous|sale'])) {
							$homeowner_sale_class = new HOAM_homeowner_sale ('homeowner_sale');
							$sale_id = $homeowner_sale_class -> listSimpleQuery ("SELECT id FROM homeowner_sale WHERE lot_id='" . $_SESSION['homeowner_information']['trusted_homeowner|address'] . "' AND ~flags & " . $_HOAM_setting ['homeowner']['flags']['residence_sold']);
							$homeowner_sale_class -> changeName ($sale_id[0], $_SESSION['homeowner_information']['trusted_homeowner|name']);
							$homeowner_sale_class -> changeSaleDate ($sale_id[0], date ($_HOAM_country['date']['format_mysql'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate'])));
							$homeowner_sale_class -> setBit ($sale_id[0], $_HOAM_setting ['homeowner']['flags']['residence_sold']);
							$_HOAM_log -> add ($_HOAM_language ['log']['homeowner']['transfer_sale'],
												HOAM_MESSAGE_NOTICE,
												array ('owner_id' => $homeowner_id,
														'flags' => $_HOAM_setting ['log']['flags']['homeowner']));
							// $previous_homeowner is reused from earlier.
							$_HOAM_log -> add ($_HOAM_language ['log']['homeowner']['transfer_sale'],
												HOAM_MESSAGE_NOTICE,
												array ('owner_id' => $previous_homeowner,
														'flags' => $_HOAM_setting ['log']['flags']['homeowner']));
						}
					}
				} else {
					// Use the information gathered above.
					$homeowner_id = $_SESSION['homeowner_information']['trusted_homeowner|id'];
				}
				// Gather the previous settings so we can make a report on any
				// changes
				$account_previous_settings = $homeowner_class -> listAll ($homeowner_id);
				$account_previous_settings = $account_previous_settings[0];
				$account_changes = (string) NULL;
				// By now, we've already verified that all of our data is
				// correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)

				if ($account_previous_settings['access_code'] != $_SESSION['homeowner_information']['trusted_homeowner|access_code']) {
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['access_code'] . '</td>';
					$account_changes .= '<td class="center">' . $_SESSION['homeowner_information']['trusted_homeowner|access_code'] . '</td>';
					$account_changes .= '<td class="center">' . $account_previous_settings['access_code'] . '</td></tr>';
				}
				$homeowner_class -> changeAccessCode ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|access_code']);
				
				$homeowner_class -> changeLotId ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|address']);
				if ($account_previous_settings['name'] != $_SESSION['homeowner_information']['trusted_homeowner|name']) {
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['name'] . '</td>';
					$account_changes .= '<td class="center">' . $_SESSION['homeowner_information']['trusted_homeowner|name'] . '</td>';
					$account_changes .= '<td class="center">' . $account_previous_settings['name'] . '</td></tr>';
				}
				$homeowner_class -> changeName ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|name']);

				// Does the homeowner receive mail at the residence?
				if (isset ($_SESSION['homeowner_information']['trusted_homeowner|residence_mailing']) AND is_true ($_SESSION['homeowner_information']['trusted_homeowner|residence_mailing'])) {
					if ((int) $account_previous_settings['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['residence_mailing']) {
					} else {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['mailing_address'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td></tr>';
					}
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['residence_mailing']);
					// We're going to assume that if the homeowner's mailing
					// address is in the neighborhood, then they are also a
					// resident.
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['resident']);
					// Therefore, their mailing address is the same as the
					// physical residence.
					$homeowner_class -> changeAddress1 ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|name']);
					if ($lot_class -> returnBuilding ($_SESSION['homeowner_information']['trusted_homeowner|address'])) {
						$bldg = 'Bldg. ' . $lot_class -> returnBuilding ($_SESSION['homeowner_information']['trusted_homeowner|address']);
					} else {
						$bldg = '';
					}
					if ($lot_class -> returnSuite ($_SESSION['homeowner_information']['trusted_homeowner|address'])) {
						$suite = 'Suite ' . $lot_class -> returnSuite ($_SESSION['homeowner_information']['trusted_homeowner|address']);
					} else {
						$suite = '';
					}
					if ($bldg AND $suite) {
						$_SESSION['homeowner_information']['trusted_homeowner|address2'] .= $bldg . ' ' . $suite;
					} else {
						if ($bldg || $suite) {
							$_SESSION['homeowner_information']['trusted_homeowner|address2'] .= $bldg . $suite;
						}
					}
					
					$homeowner_class -> changeAddress2 ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|address2']);
					$homeowner_class -> changeAddress3 ($homeowner_id, $lot_class -> returnAddress ($_SESSION['homeowner_information']['trusted_homeowner|address']) . ' ' . $lot_class -> returnStreet ($_SESSION['homeowner_information']['trusted_homeowner|address']));
					$homeowner_class -> changeCity ($homeowner_id, $_HOAM_setting ['homeowner']['default']['city']);
					$homeowner_class -> changeState ($homeowner_id, $_HOAM_setting ['homeowner']['default']['state']);
					$homeowner_class -> changeZipcode ($homeowner_id, $_HOAM_setting ['homeowner']['default']['postalcode']);
				} else {
					if ((int) $account_previous_settings['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['residence_mailing']) {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['mailing_address'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '<br />';
						$account_changes .= $_SESSION['homeowner_information']['trusted_homeowner|address1'] . '<br />';
						if ($_SESSION['homeowner_information']['trusted_homeowner|address2']) {
							$account_changes .= $_SESSION['homeowner_information']['trusted_homeowner|address2'] . '<br />';
						}
						$account_changes .= $_SESSION['homeowner_information']['trusted_homeowner|address3'] . '<br />';
						$account_changes .= $_SESSION['homeowner_information']['trusted_homeowner|city'] . ', ' . $_SESSION['homeowner_information']['trusted_homeowner|state'] . ' ';
						$account_changes .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $_SESSION['homeowner_information']['trusted_homeowner|postalcode'], 1);
						$account_changes .= '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td></tr>';
					}
					// The homeowner does NOT receive mail at their residence.
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['residence_mailing']);
					// Even though they may not receive mail at the residence,
					// are they still a resident?
					if (is_true ($_SESSION['homeowner_information']['trusted_homeowner|resident'])) {
						if ((int) $account_previous_settings['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['resident']) {
						} else {
							$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['resident'] . '</td>';
							$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td>';
							$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td></tr>';
						}
						$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['resident']);
					} else {
						if ((int) $account_previous_settings['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['resident']) {
							$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['resident'] . '</td>';
							$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td>';
							$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td></tr>';
						}
						$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['resident']);
					}
					// Use the provided mailing address information.
					$homeowner_class -> changeAddress1 ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|address1']);
					$homeowner_class -> changeAddress2 ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|address2']);
					$homeowner_class -> changeAddress3 ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|address3']);
					$homeowner_class -> changeCity ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|city']);
					$homeowner_class -> changeState ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|state']);
					$homeowner_class -> changeZipcode ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|postalcode']);
				}
				if ($account_previous_settings['email'] != $_SESSION['homeowner_information']['trusted_homeowner|email']) {
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['email'] . '</td>';
					$account_changes .= '<td class="center">' . $_SESSION['homeowner_information']['trusted_homeowner|email'] . '</td>';
					$account_changes .= '<td class="center">' . $account_previous_settings['email'] . '</td></tr>';
				}
				$homeowner_class -> changeEmail ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|email']);
				
				if ($account_previous_settings['telephone_home'] != $_SESSION['homeowner_information']['trusted_homeowner|telephone|home']) {
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . '</td>';
					$account_changes .= '<td class="center">' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|home'], 1) . '</td>';
					$account_changes .= '<td class="center">' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $account_previous_settings['telephone_home'], 1) . '</td></tr>';
				}
				$homeowner_class -> changeTelephoneHome ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|telephone|home']);
				if ($account_previous_settings['telephone_fax'] != $_SESSION['homeowner_information']['trusted_homeowner|telephone|fax']) {
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['telephone']['fax'] . '</td>';
					$account_changes .= '<td class="center">' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|fax'], 1) . '</td>';
					$account_changes .= '<td class="center">' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $account_previous_settings['telephone_fax'], 1) . '</td></tr>';
				}
				$homeowner_class -> changeTelephoneFax ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|telephone|fax']);
				if ($account_previous_settings['telephone_mobile'] != $_SESSION['homeowner_information']['trusted_homeowner|telephone|mobile']) {
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . '</td>';
					$account_changes .= '<td class="center">' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|mobile'], 1) . '</td>';
					$account_changes .= '<td class="center">' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $account_previous_settings['telephone_mobile'], 1) . '</td></tr>';
				}
				$homeowner_class -> changeTelephoneMobile ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|telephone|mobile']);
				if ($account_previous_settings['telephone_work'] != $_SESSION['homeowner_information']['trusted_homeowner|telephone|work']) {
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . '</td>';
					$account_changes .= '<td class="center">' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|work'], 1) . '</td>';
					$account_changes .= '<td class="center">' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $account_previous_settings['telephone_work'], 1) . '</td></tr>';
				}
				$homeowner_class -> changeTelephoneWork ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|telephone|work']);

				if ($account_previous_settings['purchasedate'] != date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate']))) {
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['purchasedate'] . '</td>';
					$account_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_short'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate'], 1)) . '</td>';
					$account_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_short'], strtotime ($account_previous_settings['purchasedate'])) . '</td>';
				}
				$homeowner_class -> changePurchaseDate ($homeowner_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|purchasedate'])));

				// Check for empty values so we don't end up showing this as a
				// change every time...
				if ($_SESSION['homeowner_information']['trusted_homeowner|saledate'] == '') {
					$homeowner_class -> changeSaleDate ($homeowner_id, '1970-01-01');
				} else {
					if ($account_previous_settings['saledate'] != $_SESSION['homeowner_information']['trusted_homeowner|saledate']) {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['saledate'] . '</td>';
						$account_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_short'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|saledate'])) . '</td>';
						if ($account_previous_settings['saledate'] == '1970-01-01') {
							$account_changes .= '<td>&nbsp;</td>';
						} else {
							$account_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_short'], strtotime ($account_previous_settings['saledate'])) . '</td>';
						}
					}
					$homeowner_class -> changeSaleDate ($homeowner_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|saledate'])));
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
							$_HOAM_setting ['attachment']['flags']['homeowner'],
							$_SESSION['attachment_information']['trusted_attachment|description'],
							array ($_SESSION['homeowner_information']['trusted_homeowner|id']));
						if ($id) {
							$account_changes .= '<tr><td>' . $_HOAM_language ['field_names']['homeowner']['attachments'] . '</td>';
							$account_changes .= '<td colspan=2>' . HOAM_attachmentReturnHTML ($id, TRUE) . '</td>';
						} else {
							echo $_HOAM_language ['errors']['attachment']['import'];
						}
					}
				}
				// Are we going to ignore deed restriction violations for this
				// homeowner?
				if (is_true ($_SESSION['homeowner_information']['trusted_homeowner|ignore_violations'])) {
					if ((int) $account_previous_settings['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['ignore_violations']) {
					} else {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['ignore_violations'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td></tr>';
					}
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['ignore_violations']);
				} else {
					if ((int) $account_previous_settings['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['ignore_violations']) {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['ignore_violations'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td></tr>';
					}
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['ignore_violations']);
				}
				// Not tracking the change history on late notice flags for now.
				if ($_SESSION['homeowner_information']['trusted_homeowner|flags|late_notice']) {
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_notice'], 'budget_flags');
				} else {
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_notice'], 'budget_flags');
				}
				if ($_SESSION['homeowner_information']['trusted_homeowner|flags|late_delinquent']) {
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_delinquent'], 'budget_flags');
				} else {
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_delinquent'], 'budget_flags');
				}
				if ($_SESSION['homeowner_information']['trusted_homeowner|flags|late_default']) {
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_default'], 'budget_flags');
				} else {
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_default'], 'budget_flags');
				}
				if ($_SESSION['homeowner_information']['trusted_homeowner|flags|late_lien']) {
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_lien'], 'budget_flags');
				} else {
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_lien'], 'budget_flags');
				}
				if ($_SESSION['homeowner_information']['trusted_homeowner|flags|late_attorney']) {
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_attorney'], 'budget_flags');
				} else {
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_attorney'], 'budget_flags');
				}
				if ($_SESSION['homeowner_information']['trusted_homeowner|flags|late_attorney_demand']) {
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_attorney_demand'], 'budget_flags');
				} else {
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_attorney_demand'], 'budget_flags');
				}
				if ($_SESSION['homeowner_information']['trusted_homeowner|flags|late_attorney_foreclosure']) {
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_attorney_foreclosure'], 'budget_flags');
				} else {
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_attorney_foreclosure'], 'budget_flags');
				}
				if ($_SESSION['homeowner_information']['trusted_homeowner|flags|late_attorney_eviction']) {
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_attorney_eviction'], 'budget_flags');
				} else {
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_attorney_eviction'], 'budget_flags');
				}
				
				if (is_true ($_SESSION['homeowner_information']['trusted_homeowner|assessment|exempt'])) {
					if ((int) $account_previous_settings['budget_flags'] & (int) $_HOAM_setting ['homeowner']['flags']['ignore_budget']) {
					} else {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['assessment']['exempt'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td></tr>';
					}
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['ignore_budget'], 'budget_flags');
				} else {
					if ((int) $account_previous_settings['budget_flags'] & (int) $_HOAM_setting ['homeowner']['flags']['ignore_budget']) {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['assessment']['exempt'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td></tr>';
					}
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['ignore_budget'], 'budget_flags');
				}
				if (is_true ($_SESSION['homeowner_information']['trusted_homeowner|flags|no_fees'])) {
					if ((int) $account_previous_settings['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['no_fees']) {
					} else {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['flags']['no_fees'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td></tr>';
					}
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['no_fees']);
				} else {
					if ((int) $account_previous_settings['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['no_fees']) {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['flags']['no_fees'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td></tr>';
					}
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['no_fees']);
				}

				if (is_true ($_SESSION['homeowner_information']['trusted_homeowner|flags|bankrupt'])) {
					if ((int) $account_previous_settings['budget_flags'] & (int) $_HOAM_setting ['homeowner']['flags']['bankrupt']) {
					} else {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['budget']['flags']['bankrupt'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td></tr>';
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['assessment']['bankrupt_date'] . '</td>';
						$account_changes .= '<td class="center">' . date ($_HOAM_country['date']['format_reports'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|assessment|bankrupt_date'])) . '</td>';
						$account_changes .= '<td class="center">&nbsp;</td></tr>';
					}
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['bankrupt'], 'budget_flags');
					$homeowner_class -> changeDateBankrupt ($homeowner_id, date ($_HOAM_country['date']['format_mysql'], strtotime ($_SESSION['homeowner_information']['trusted_homeowner|assessment|bankrupt_date'])));
				} else {
					if ((int) $account_previous_settings['budget_flags'] & (int) $_HOAM_setting ['homeowner']['flags']['bankrupt']) {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['budget']['flags']['bankrupt'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td></tr>';
					}
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['bankrupt'], 'budget_flags');
					$homeowner_class -> changeDateBankrupt ($homeowner_id, '1970-01-01');
				}
				
				if (is_true ($_SESSION['homeowner_information']['trusted_homeowner|flags|service_member'])) {
					if ((int) $account_previous_settings['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['service_member']) {
					} else {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['flags']['service_member'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td></tr>';
					}
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['service_member']);
				} else {
					if ((int) $account_previous_settings['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['service_member']) {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['flags']['service_member'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td>';
						$account_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td></tr>';
					}
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['homeowner']['flags']['service_member']);
				}
				
				if (is_true ($_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan'])) {
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_payment_plan'], 'budget_flags');
					$homeowner_class -> changePaymentPlanDate ($homeowner_id, date ($_HOAM_country ['date']['format_mysql']));
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['assessment']['payment_plan_details'] . '</td>';
					$account_changes .= '<td class="center">' . $_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan_details'] . '</td>';
					$account_changes .= '<td class="center">' . ucfirst ($_HOAM_language ['common']['no']) . '</td>';
					$homeowner_class -> changePaymentPlanDetails ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan_details']);
				} elseif (is_true ($_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan_default'])) {
					$homeowner_class -> clearBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['late_payment_plan'], 'budget_flags');
					$homeowner_class -> setBit ($homeowner_id, $_HOAM_setting ['budget']['flags']['payment_plan_default'], 'budget_flags');
					$homeowner_class -> changePaymentPlanDate ($homeowner_id, date ($_HOAM_country ['date']['format_mysql']));
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['assessment']['payment_plan'] . '</td>';
					$account_changes .= '<td class="center">' . ucfirst ($_HOAM_language ['common']['no']) . '</td>';
					$account_changes .= '<td class="center">' . ucfirst ($_HOAM_language ['common']['yes']) . '</td></tr>';
				} else {
					if ($account_previous_settings['payment_plan_details'] != $_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan_details']) {
						$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['assessment']['payment_plan_details'] . '</td>';
						$diff = HOAM_Diff ($account_previous_settings['payment_plan_details'], $_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan_details']);
						$account_changes .= '<td class="center">' . $diff['new'] . '</td>';
						$account_changes .= '<td class="center">' . $diff['old'] . '</td>';
						$homeowner_class -> changePaymentPlanDetails ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan_details']);
					}
				}

				if ($account_previous_settings['comments'] != $_SESSION['homeowner_information']['trusted_homeowner|comments']) {
					$account_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['homeowner']['comments'] . '</td>';
					$diff = HOAM_diff ($account_previous_settings['comments'], $_SESSION['homeowner_information']['trusted_homeowner|comments']);
					$account_changes .= '<td>' . $diff['new'] . '</td>';
					$account_changes .= '<td>' . $diff['old'] . '</td>';
				}
				$homeowner_class -> changeComments ($homeowner_id, $_SESSION['homeowner_information']['trusted_homeowner|comments']);
				$homeowner_class -> changeDateModified ($homeowner_id);

				if (isset ($_POST['add'])) {
				} else {
					// If someone looked at a record, selected 'submit', but
					// didn't actually make any changes, this will be empty. No
					// need to update history.
					if ($account_changes) {
						$thead = sprintf ($_HOAM_language ['log']['generic']['table']['thead'], ucfirst ($_HOAM_language ['common']['setting']), ucfirst ($_HOAM_language ['common']['new-value']), ucfirst ($_HOAM_language ['common']['old-value']));
						$tbody = sprintf ($_HOAM_language ['log']['generic']['table']['tbody'], $account_changes);
						$table = sprintf ($_HOAM_language ['log']['generic']['table']['default'], $_HOAM_language ['log']['old_new']['table']['colgroup'], $thead, $tbody);
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['homeowner']['modified'], $_SESSION['homeowner_information']['trusted_homeowner|name'], $table),
											HOAM_MESSAGE_INFORMATIONAL,
											array ('clean' => FALSE,
													'flags' => $_HOAM_setting ['log']['flags']['homeowner'],
													'owner_id' => $homeowner_id));
					}
				}

				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION['error_class_item'])) {
					// Clear the error_class_item variable since everything
					// worked ok.
					unset ($_SESSION['error_class_item']);
				}
				if (isset ($_SESSION['homeowner_information'])) {
					// Clear the homeowner_information variable since
					// everything worked ok.
					unset ($_SESSION['homeowner_information']);
				}

				header ('Location: /admin/homeowner/');
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
