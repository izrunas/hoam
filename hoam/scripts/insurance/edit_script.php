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
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		
		$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
		$insurance_class = new HOAM_insurance ('insurance');
		$lot_class = new HOAM_lot ('lots');

		require_once (HOAM_PATH_FUNCTION . 'attachments.php');

		// If this is the second (or greater) time through the script (ie,
		// there were errors that had to be fixed before committing the
		// changes) get rid of the old cached information the user entered,
		// we're going to populate it with information just received from the
		// form posting.
		if (isset ($_SESSION['insurance_information'])) {
			unset ($_SESSION['insurance_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			header ('Location: /admin/financial/insurance/');
			exit ();
		} elseif (isset ($_POST['delete'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				$policy_id = (string) $insurance_class -> superClean ($_POST['insurance|id'], $insurance_class -> returnColumnSize ('id'));
				if ($lot_class -> idExists ($_SESSION['insurance_information']['trusted_insurance|id'])) {
					$property = HOAM_returnAddress ($insurance_class -> returnLotId ($policy_id));
				} else {
					switch ($_SESSION['insurance_information']['trusted_insurance|address']) {
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
				$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['insurance']['deleted'], $property),
									HOAM_MESSAGE_NOTICE,
									array ('flags' => $_HOAM_setting ['log']['flags']['insurance'],
											'owner_id' => $policy_id));
				$result = $insurance_class -> deleteId ($policy_id);
				if ($result) {
					// Send the user back to the administration page.
					header ('Location: /admin/financial/insurance/');
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
			// existing policy. Start populating the insurance_information
			// array, and check for errors.
			$_SESSION['insurance_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');

			if (isset ($_POST['insurance|id'])) {
				$_SESSION['insurance_information']['trusted_insurance|id'] = (string) $insurance_class -> superClean ($_POST['insurance|id'], $insurance_class -> returnColumnSize ('id'));
				if ($insurance_class -> idExists ($_SESSION['insurance_information']['trusted_insurance|id'])) {
				} else {
					// The request is unknown.
					$_SESSION ['error_class_item'] -> addError ('insurance|id', $_HOAM_language ['errors']['generic']['unknown-id']);
				}
			}
			$_SESSION['insurance_information']['trusted_insurance|vendor'] = (string) $insurance_class -> superClean ($_POST['insurance|vendor'], $insurance_class -> returnColumnSize ('vendor_id'));
			// Make sure the vendor is valid.
			if (!$budget_vendor_class -> idExists ($_SESSION['insurance_information']['trusted_insurance|vendor'])) {
				// The vendor is invalid.
				$_SESSION ['error_class_item'] -> addError ('insurance|vendor', $_HOAM_language ['errors']['generic']['invalid-id']);
			}

			$_SESSION['insurance_information']['trusted_insurance|type'] = (int) $insurance_class -> superClean ($_POST['insurance|type'], $insurance_class -> returnColumnSize ('policy_type'));
			// Make sure the policy type is valid.
			if (($_SESSION['insurance_information']['trusted_insurance|type'] > 0) AND ($_SESSION['insurance_information']['trusted_insurance|type'] <= count ($_HOAM_language ['field_names']['insurance']['policy']))) {
			} else {
				// The policy type is invalid.
				$_SESSION ['error_class_item'] -> addError ('insurance|type', $_HOAM_language ['errors']['generic']['invalid-id']);
			}

			$_SESSION['insurance_information']['trusted_insurance|policy-number'] = (string) $insurance_class -> superClean ($_POST['insurance|policy-number'], $insurance_class -> returnColumnSize ('policy_num'));
			// Make sure the policy number isn't too short.
			if (mb_strlen ($_SESSION['insurance_information']['trusted_insurance|policy-number']) >= $_HOAM_setting ['budget']['insurance']['minimum_policy_length']) {
			} else {
				// The policy number is too short.
				$_SESSION ['error_class_item'] -> addError ('insurance|policy-number', sprintf ($_HOAM_language ['errors']['insurance']['policy-number-too-short'], $_HOAM_setting ['budget']['insurance']['minimum_policy_length']));
			}

			$_SESSION['insurance_information']['trusted_insurance|address'] = $insurance_class -> superClean ($_POST['insurance|address'], $insurance_class -> returnColumnSize ('lot_id'));
			// Make sure the address exists.
			if ($lot_class -> idExists ($_SESSION['insurance_information']['trusted_insurance|address'])) {
			} else {
				// Check to see if the address matches one of the common areas
				if (($_SESSION['insurance_information']['trusted_insurance|address'] == 'common_area') OR
					($_SESSION['insurance_information']['trusted_insurance|address'] == 'common_clubhouse') OR
					($_SESSION['insurance_information']['trusted_insurance|address'] == 'common_pool')) {
				} else { 
					// The address is unknown.
					$_SESSION ['error_class_item'] -> addError ('insurance|address', $_HOAM_language ['errors']['generic']['unknown-id']);
				}
			}

			$_SESSION['insurance_information']['trusted_insurance|renewal-date'] = (string) $insurance_class -> superClean ($_POST['insurance|renewal-date'], $insurance_class -> returnColumnSize ('daterenewal'));
			// Make sure we have a valid date
			if (checkdate (date ('m', strtotime ($_SESSION['insurance_information']['trusted_insurance|renewal-date'])), date ('d', strtotime ($_SESSION['insurance_information']['trusted_insurance|renewal-date'])), date ('Y', strtotime ($_SESSION['insurance_information']['trusted_insurance|renewal-date'])))) {
			} else {
				$_SESSION['error_class_item'] -> addError ('insurance|renewal-date', $_HOAM_language ['errors']['generic']['invalid-date']);
			}

			$_SESSION['insurance_information']['trusted_insurance|expiration-date'] = (string) $insurance_class -> superClean ($_POST['insurance|expiration-date'], $insurance_class -> returnColumnSize ('dateexpiration'));
			// Make sure we have a valid date
			if (checkdate (date ('m', strtotime ($_SESSION['insurance_information']['trusted_insurance|expiration-date'])), date ('d', strtotime ($_SESSION['insurance_information']['trusted_insurance|expiration-date'])), date ('Y', strtotime ($_SESSION['insurance_information']['trusted_insurance|expiration-date'])))) {
			} else {
				$_SESSION['error_class_item'] -> addError ('insurance|expiration-date', $_HOAM_language ['errors']['generic']['invalid-date']);
			}
			// Check to see if the policy is being replaced before checking the
			// expiration date
			if (isset ($_POST['insurance|replaced'])) {
				$_SESSION['insurance_information']['trusted_insurance|replaced'] = (int) $_POST['insurance|replaced'];
			} else {
				$_SESSION['insurance_information']['trusted_insurance|replaced'] = (int) 0;
			}
			// Make sure the date isn't in the past
			if (date ($_HOAM_country ['date']['format_mysql']) > date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['insurance_information']['trusted_insurance|expiration-date']))) {
				if (!isset ($_POST['insurance|expiration-date|ignore']) AND !$_SESSION['insurance_information']['trusted_insurance|replaced']) {
					$_SESSION['error_class_item'] -> addError ('insurance|expiration-date', $_HOAM_language ['errors']['generic']['date_past']);
				}
			}

			// We're not doing anything to comments other than cleaning them.			
			$_SESSION['insurance_information']['trusted_insurance|comments'] = (string) $insurance_class -> superClean ($_POST['insurance|comments'], $insurance_class -> returnColumnSize ('comments'));

// print_r ($_POST); print_r ($_SESSION['insurance_information']); print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before
			// committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/financial/insurance/add/');
					exit ();
				} else {
					header ('location: /admin/financial/insurance/edit/' . $_SESSION['insurance_information']['trusted_insurance|id'] . '/');
					exit ();
				}
			} else {
				// Finally! We have good data and can start modifying the
				// database.
				if (isset ($_POST['add'])) {
					$policy_id = $insurance_class -> add ($_SESSION['current_user']['id']);
					if ($policy_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
						exit ();
					} else {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['insurance']['created'], $_SESSION['insurance_information']['trusted_insurance|policy-number']),
											HOAM_MESSAGE_NOTICE,
											array ('flags' => $_HOAM_setting ['log']['flags']['insurance'],
													'owner_id' => $policy_id));
					}
				} else {
					// Use the information gathered above.
					$policy_id = $_SESSION['insurance_information']['trusted_insurance|id'];
				}

				$previous_settings = $insurance_class -> listAll ($policy_id);
				$previous_settings = $previous_settings[0];
				$policy_changes = '';
				
				if ($previous_settings['vendor_id'] != $_SESSION['insurance_information']['trusted_insurance|vendor']) {
					$policy_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['insurance']['vendor'] . '</td>';
					$policy_changes .= '<td class="center">' . $budget_vendor_class -> returnName ($_SESSION['insurance_information']['trusted_insurance|vendor']) . '</td>';
					$policy_changes .= '<td class="center">' . $budget_vendor_class -> returnName ($previous_settings['vendor_id']) . '</td></tr>';
				}
				$insurance_class -> changeVendorId ($policy_id, $_SESSION['insurance_information']['trusted_insurance|vendor']);
				
				if ($previous_settings['policy_type'] != $_SESSION['insurance_information']['trusted_insurance|type']) {
					$policy_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['insurance']['policy']['type'] . '</td>';
					$policy_changes .= '<td class="center">' . $_HOAM_language ['field_names']['insurance']['policy'][$_SESSION['insurance_information']['trusted_insurance|type']] . '</td>';
					$policy_changes .= '<td class="center">' . $_HOAM_language ['field_names']['insurance']['policy'][$previous_settings['policy_type']] . '</td></tr>';
				}
				$insurance_class -> changePolicyType ($policy_id, $_SESSION['insurance_information']['trusted_insurance|type']);

				if ($previous_settings['policy_num'] != $_SESSION['insurance_information']['trusted_insurance|policy-number']) {
					$policy_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['insurance']['policy']['number'] . '</td>';
					$policy_changes .= '<td class="center">' . $_SESSION['insurance_information']['trusted_insurance|policy-number'] . '</td>';
					$policy_changes .= '<td class="center">' . $previous_settings['policy_num'] . '</td></tr>';
				}
				$insurance_class -> changePolicyNum ($policy_id, $_SESSION['insurance_information']['trusted_insurance|policy-number']);

				if ($previous_settings['lot_id'] != $_SESSION['insurance_information']['trusted_insurance|address']) {
					$policy_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['insurance']['address'] . '</td>';
					if ($lot_class -> idExists ($_SESSION['insurance_information']['trusted_insurance|address'])) {
						$property = HOAM_returnAddress ($_SESSION['insurance_information']['trusted_insurance|address']);
					} else {
						switch ($_SESSION['insurance_information']['trusted_insurance|address']) {
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
					$policy_changes .= '<td class="center">' . $property . '</td>';
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
					$policy_changes .= '<td class="center">' . $property . '</td></tr>';
				}
				$insurance_class -> changeLotId ($policy_id, $_SESSION['insurance_information']['trusted_insurance|address']);
				if ($previous_settings['daterenewal'] != date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['insurance_information']['trusted_insurance|renewal-date']))) {
					$policy_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['insurance']['renewal-date'] . '</td>';
					$policy_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_short'], strtotime ($_SESSION['insurance_information']['trusted_insurance|renewal-date'], 1)) . '</td>';
					$policy_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_short'], strtotime ($previous_settings['daterenewal'])) . '</td>';
				}
				$insurance_class -> changeRenewalDate ($policy_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['insurance_information']['trusted_insurance|renewal-date'])));

				if ($previous_settings['dateexpiration'] != date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['insurance_information']['trusted_insurance|expiration-date']))) {
					$policy_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['insurance']['expiration-date'] . '</td>';
					$policy_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_short'], strtotime ($_SESSION['insurance_information']['trusted_insurance|expiration-date'], 1)) . '</td>';
					$policy_changes .= '<td class="center">' . date ($_HOAM_country ['date']['format_short'], strtotime ($previous_settings['dateexpiration'])) . '</td>';
				}
				$insurance_class -> changeExpirationDate ($policy_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['insurance_information']['trusted_insurance|expiration-date'])));
				if (is_true ($_SESSION['insurance_information']['trusted_insurance|replaced'])) {
					if ((int) $previous_settings ['flags'] & (int) $_HOAM_setting ['budget']['insurance']['flags']['replaced']) {
					} else {
						$policy_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['insurance']['flags']['replaced'] . '</td>';
						$policy_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td>';
						$policy_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td></tr>';
					}
					$insurance_class -> setBit ($policy_id, $_HOAM_setting ['budget']['insurance']['flags']['replaced']);
				} else {
					if ((int) $previous_settings ['flags'] & (int) $_HOAM_setting ['budget']['insurance']['flags']['replaced']) {
						$policy_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['insurance']['flags']['replaced'] . '</td>';
						$policy_changes .= '<td class="center">' . $_HOAM_language ['common']['no'] . '</td>';
						$policy_changes .= '<td class="center">' . $_HOAM_language ['common']['yes'] . '</td></tr>';
					}
					$insurance_class -> clearBit ($policy_id, $_HOAM_setting ['budget']['insurance']['flags']['replaced']);
				}

				if ($previous_settings['comments'] != $_SESSION['insurance_information']['trusted_insurance|comments']) {
					$policy_changes .= '<tr><td class="center">' . $_HOAM_language ['field_names']['insurance']['comments'] . '</td>';
					$policy_changes .= '<td class="center">' . $_SESSION['insurance_information']['trusted_insurance|comments'] . '</td>';
					$policy_changes .= '<td class="center">' . $previous_settings['comments'] . '</td></tr>';
				}
				$insurance_class -> changeComments ($policy_id, $_SESSION['insurance_information']['trusted_insurance|comments']);

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
							$_HOAM_setting ['attachment']['flags']['insurance'],
							'',
							$policy_id);
						if (!$id) {
							echo $_HOAM_language ['errors']['attachment']['import'];
						}
					}
				}

				$insurance_class -> changeDateModified ($policy_id);

				if (isset ($_POST['add'])) {
				} else {
					// If someone looked at a record, selected 'submit', but
					// didn't actually make any changes, this will be empty. No
					// need to update history.
					if (!empty ($policy_changes)) {
						$thead = sprintf ($_HOAM_language ['log']['generic']['table']['thead'], ucfirst ($_HOAM_language ['common']['setting']), ucfirst ($_HOAM_language ['common']['new-value']), ucfirst ($_HOAM_language ['common']['old-value']));
						$tbody = sprintf ($_HOAM_language ['log']['generic']['table']['tbody'], $policy_changes);
						$table = sprintf ($_HOAM_language ['log']['generic']['table']['default'], $_HOAM_language ['log']['old_new']['table']['colgroup'], $thead, $tbody);
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['insurance']['modified'], $table),
											HOAM_MESSAGE_NOTICE,
											array ('clean' => FALSE,
													'flags' => $_HOAM_setting ['log']['flags']['insurance'],
													'owner_id' => $policy_id));
					}
				}

				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION['error_class_item'])) {
					// Clear the error_class_item variable since everything
					// worked ok.
					unset ($_SESSION['error_class_item']);
				}
				if (isset ($_SESSION['insurance_information'])) {
					// Clear the homeowner_information variable since
					// everything worked ok.
					unset ($_SESSION['insurance_information']);
				}

				header ('Location: /admin/financial/insurance/');
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
