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
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		$violation_severity_class = new HOAM_violation_severity ('violation_severity');
		
		// If this is the second (or greater) time through the script (ie,
		// there were errors that had to be fixed before committing the
		// changes) get rid of the old cached information the user entered,
		// we're going to populate it with information just received from the
		// form posting.
		if (isset ($_SESSION['violation_severity_information'])) {
			unset ($_SESSION['violation_severity_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			// Just send them back to the admin page
				header ('Location: /admin/violation/severity/');
		} elseif (isset ($_POST['delete'])) {
			// Make sure the current user is the owner of the item, OR has
			// admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
			// Make sure we found the category
				$severity_id = (string) $violation_severity_class -> superClean ($_POST['violation|severity|id'], $violation_severity_class -> returnColumnSize ('id'));
				if ($violation_severity_class -> idExists ($severity_id)) {
					// Need to add a check that we aren't deleting a severity
					// that is in use by any violations.
					$violation_class = new HOAM_violation ('violations');
					if ($violation_class -> returnSeverityInUse ($severity_id)) {
						// Error Removing Item
						header ('Location: /error/inuse/');
					} else {
						if ($violation_severity_class -> deleteId ($severity_id)) {
							header ('Location: /admin/violation/severity/');
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
			// If we're still here, then we must be adding or modifying an
			// existing category.
			$_SESSION['violation_severity_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			if (isset ($_POST['violation|severity|id'])) {
				$_SESSION['violation_severity_information']['trusted_violation|severity|id'] = (string) $violation_severity_class -> superClean ($_POST['violation|severity|id'], $violation_severity_class -> returnColumnSize ('id'));
				if ($violation_severity_class -> idExists ($_SESSION['violation_severity_information']['trusted_violation|severity|id'])) {
				} else {
					// The severity is unknown.
					$_SESSION ['error_class_item'] -> addError ('violation|severity|id', $_HOAM_language ['errors']['violation']['severity']['unknown_id']);
				}
			}
			if (isset ($_POST['violation|severity|assume_resolved'])) {
				$_SESSION['violation_severity_information']['trusted_violation|severity|assume_resolved'] = (int) $_POST['violation|severity|assume_resolved'];
			} else {
				$_SESSION['violation_severity_information']['trusted_violation|severity|assume_resolved'] = (int) 0;
			}
			if (isset ($_POST['violation|severity|name'])) {
				$_SESSION['violation_severity_information']['trusted_violation|severity|name'] = (string) $violation_severity_class -> superClean ($_POST['violation|severity|name'], $violation_severity_class -> returnColumnSize ('severity'));
				// Make sure the severity name isn't too short.
				if (mb_strlen ($_SESSION['violation_severity_information']['trusted_violation|severity|name']) >= $_HOAM_setting ['violation']['severity']['minimum_name_length']) {
				} else {
					// The category is too short.
					$_SESSION ['error_class_item'] -> addError ('violation|severity|name', sprintf ($_HOAM_language ['errors']['violation']['severity']['name_too_short'], $_HOAM_setting ['violation']['severity']['minimum_name_length']));
				}
				// Make sure the severity name isn't a duplicate of an existing
				// severity.
				if ($violation_severity_class -> valueExists ($_SESSION['violation_severity_information']['trusted_violation|severity|name'], 'severity')) {
					// The severity is duplicate.
					// Are we modifying that "duplicate" category?
					if (isset ($_SESSION['violation_severity_information']['trusted_violation|severity|id']) AND
						($violation_severity_class -> returnSeverity ($_SESSION['violation_severity_information']['trusted_violation|severity|id']) == $_SESSION['violation_severity_information']['trusted_violation|severity|name'])) {
							// Yes, ignore the problem.
						} else {
							$_SESSION ['error_class_item'] -> addError ('violation|severity|name', $_HOAM_language ['errors']['violation']['severity']['exists']);
						}
				}
			}
			if (isset ($_POST['violation|severity|escalate'])) {
				$_SESSION['violation_severity_information']['trusted_violation|severity|escalate'] = (string) $violation_severity_class -> superClean ($_POST['violation|severity|escalate'], $violation_severity_class -> returnColumnSize ('escalate'));
				// Make sure the severity exists
				if ($violation_severity_class -> idExists ($_SESSION['violation_severity_information']['trusted_violation|severity|escalate'])) {
				} else {
					$_SESSION ['error_class_item'] -> addError ('violation|severity|escalate', $_HOAM_language ['errors']['violation']['severity']['unknown_id']);
				}
			}
			if (isset ($_POST['violation|severity|numdays'])) {
				$_SESSION['violation_severity_information']['trusted_violation|severity|numdays'] = (int) $violation_severity_class -> superClean ($_POST['violation|severity|numdays'], $violation_severity_class -> returnColumnSize ('numdays'));
				if ($_SESSION['violation_severity_information']['trusted_violation|severity|numdays'] < $_HOAM_setting ['violation']['severity']['minimum_days_resolution']) {
					$_SESSION ['error_class_item'] -> addError ('violation|severity|numdays', sprintf ($_HOAM_language ['errors']['violation']['severity']['numdays_too_small'], $_HOAM_setting ['violation']['severity']['minimum_days_resolution']));
				}
			} else {
				// If the amount hasn't been specified, assume 0.
				$_SESSION['violation_severity_information']['trusted_violation|severity|numdays'] = $_HOAM_setting ['violation']['severity']['minimum_days_resolution'];
			}
			if (isset ($_POST['violation|severity|fine_interest'])) {
				$_SESSION['violation_severity_information']['trusted_violation|severity|fine_interest'] = number_format ((float) $violation_severity_class -> superClean ($_POST['violation|severity|fine_interest'], $violation_severity_class -> returnColumnSize ('fine_interest')), 2);
				// We're not going to do any checks, just assume the value is
				// good. Eventually might need to do a check that the interest
				// rate isn't over the maximum legal amount.
			} else {
				// If the amount hasn't been specified, assume 0.
				$_SESSION['violation_severity_information']['trusted_violation|severity|fine_interest'] = 0;
			}
			if (isset ($_POST['violation|severity|fine_per_day'])) {
				$_SESSION['violation_severity_information']['trusted_violation|severity|fine_per_day'] = number_format ((float) $violation_severity_class -> superClean ($_POST['violation|severity|fine_per_day'], $violation_severity_class -> returnColumnSize ('fine_per_day')), 2);
				// We're not going to do any checks, just assume the value is
				// good.
			} else {
				// If the amount hasn't been specified, assume 0.
				$_SESSION['violation_severity_information']['trusted_violation|severity|fine_per_day'] = 0;
			}
			if (isset ($_POST['violation|severity|fine_per_notice'])) {
				$_SESSION['violation_severity_information']['trusted_violation|severity|fine_per_notice'] = number_format ((float) $violation_severity_class -> superClean ($_POST['violation|severity|fine_per_notice'], $violation_severity_class -> returnColumnSize ('fine_per_notice')), 2);
				// We're not going to do any checks, just assume the value is
				// good.
			} else {
				// If the amount hasn't been specified, assume 0.
				$_SESSION['violation_severity_information']['trusted_violation|severity|fine_per_notice'] = 0;
			}
			if (isset ($_POST['violation|severity|preamble'])) {
				$_SESSION['violation_severity_information']['trusted_violation|severity|preamble'] = (string) $violation_severity_class -> clean ($_POST['violation|severity|preamble'], $violation_severity_class -> returnColumnSize ('preamble'));
				// Make sure the severity preamble isn't too short.
				if (mb_strlen ($_SESSION['violation_severity_information']['trusted_violation|severity|preamble']) >= $_HOAM_setting ['violation']['severity']['minimum_preamble_length']) {
				} else {
					// The preamble is too short.
					$_SESSION ['error_class_item'] -> addError ('violation|severity|preamble', sprintf ($_HOAM_language ['errors']['violation']['severity']['preamble_too_short'], $_HOAM_setting ['violation']['severity']['minimum_preamble_length']));
				}
			}
			if (isset ($_POST['violation|severity|closing'])) {
				$_SESSION['violation_severity_information']['trusted_violation|severity|closing'] = (string) $violation_severity_class -> clean ($_POST['violation|severity|closing'], $violation_severity_class -> returnColumnSize ('closing'));
				// Make sure the severity closing isn't too short.
				if (mb_strlen ($_SESSION['violation_severity_information']['trusted_violation|severity|closing']) >= $_HOAM_setting ['violation']['severity']['minimum_closing_length']) {
				} else {
					// The closing is too short.
					$_SESSION ['error_class_item'] -> addError ('violation|severity|closing', sprintf ($_HOAM_language ['errors']['violation']['severity']['closing_too_short'], $_HOAM_setting ['violation']['severity']['minimum_closing_length']));
				}
			}

// print_r ($_POST); print_r ($_SESSION['violation_severity_information']); print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before
			// committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/violation/severity/add/');
				} else {
					header ('location: /admin/violation/severity/edit/' . $_SESSION['violation_severity_information']['trusted_violation|severity|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the
				// database.
				if (isset ($_POST['add'])) {
					$severity_id = $violation_severity_class -> add ($_SESSION['current_user']['id']);
					if ($severity_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					}
				} else {
					// Use the information gathered above.
					$severity_id = $_SESSION['violation_severity_information']['trusted_violation|severity|id'];
					$violation_severity_class -> changeDateModified ($severity_id);
				}
				// By now, we've already verified that all of our data is
				// correct and valid, therefore we can just update the db.
				$violation_severity_class -> changeSeverity ($severity_id, $_SESSION['violation_severity_information']['trusted_violation|severity|name']);
				$violation_severity_class -> changePreamble ($severity_id, $_SESSION['violation_severity_information']['trusted_violation|severity|preamble']);
				$violation_severity_class -> changeClosing ($severity_id, $_SESSION['violation_severity_information']['trusted_violation|severity|closing']);
				$violation_severity_class -> changeEscalate ($severity_id, $_SESSION['violation_severity_information']['trusted_violation|severity|escalate']);
				$violation_severity_class -> changeNumdays ($severity_id, $_SESSION['violation_severity_information']['trusted_violation|severity|numdays']);
				$violation_severity_class -> changeFineInterest ($severity_id, $_SESSION['violation_severity_information']['trusted_violation|severity|fine_interest']);
				$violation_severity_class -> changeFinePerDay ($severity_id, $_SESSION['violation_severity_information']['trusted_violation|severity|fine_per_day']);
				$violation_severity_class -> changeFinePerNotice ($severity_id, $_SESSION['violation_severity_information']['trusted_violation|severity|fine_per_notice']);
				if (is_true ($_SESSION['violation_severity_information']['trusted_violation|severity|assume_resolved'])) {
					$violation_severity_class -> setBit ($severity_id, $_HOAM_setting ['violation']['flags']['assume_resolved']);
				} else {
					$violation_severity_class -> clearBit ($severity_id, $_HOAM_setting ['violation']['flags']['assume_resolved']);
				}

				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION['violation_severity_information'])) {
					unset ($_SESSION['violation_severity_information']);
				}
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}

				header ('Location: /admin/violation/severity/list/');
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
