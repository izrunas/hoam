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
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['users']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'attachments.php');
		require_once (HOAM_PATH_FUNCTION . 'budget.php');
		
		$budget_class = new HOAM_budget ('budget');
		$homeowner_class = new HOAM_homeowner ('homeowners');
		
		// If this is the second (or greater) time through the script (ie,
		// there were errors that had to be fixed before committing the
		// changes) get rid of the old cached information the user entered,
		// we're going to populate it with information just received from the
		// form posting.
		if (isset ($_SESSION['payment_information'])) {
			unset ($_SESSION['payment_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the action, send them back to the main page
			header ('Location: /admin/financial/');
		} elseif (isset ($_POST['delete'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
				$result = $budget_class -> deleteId ((string) $budget_class -> superClean ($_POST['id'], $budget_class -> returnColumnSize ('id')));
				if ($result) {
					// Send the user back to the budget administration page.
					header ('Location: /admin/financial/');
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
			// existing invoice. Start populating the payment_information
			// array, and check for errors.
			$_SESSION['payment_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');

			if (isset ($_POST['budget|payment|customer'])) {
				$_SESSION['payment_information']['trusted_budget|payment|customer'] = (string) $budget_class -> superClean ($_POST['budget|payment|customer'], $budget_class -> returnColumnSize ('customer_id'));
				if ($homeowner_class -> idExists ($_SESSION['payment_information']['trusted_budget|payment|customer'])) {
				} else {
					$_SESSION['error_class_item'] -> addError ('budget|payment|customer', $_HOAM_language ['errors']['budget']['unknown_customer']);
				}
			}
			if (isset ($_POST['budget|payment|customer|show-all'])) {
				$_SESSION['payment_information']['trusted_budget|payment|customer|show-all'] = (int) $_POST['budget|payment|customer|show-all'];
			}
			if (isset ($_POST['budget|payment|amount'])) {
				$_SESSION['payment_information']['trusted_budget|payment|amount'] = $budget_class -> superClean ($_POST['budget|payment|amount'], $budget_class -> returnColumnSize ('amount'));
				// Appears that casting the currency string to a float broke...
				// dunno why... stripping out the comma fixes it.
				$_SESSION['payment_information']['trusted_budget|payment|amount'] = (float) round (preg_replace ('/,/', '', $_SESSION['payment_information']['trusted_budget|payment|amount']), 2);
				// Make sure the amount is positive.
				if ($_SESSION['payment_information']['trusted_budget|payment|amount'] <= 0) {
					$_SESSION['error_class_item'] -> addError ('budget|payment|amount', $_HOAM_language ['errors']['budget']['invoice_amount_negative']);
				} else {
					// Let's do a real quick check to see whether the user may
					// have inadvertently swapped the check number and payment
					// amount.
					$total_amount_due = $budget_class -> returnTotalAmountDue ($_SESSION['payment_information']['trusted_budget|payment|customer']);
					if ($_SESSION['payment_information']['trusted_budget|payment|amount'] > ($total_amount_due * 1.5)) {
						if (!isset ($_POST['budget|payment|amount|ignore'])) {
							$_SESSION['error_class_item'] -> addError ('budget|payment|amount', $_HOAM_language ['errors']['budget']['checknum_swapped']);
						}
					}
					unset ($total_amount_due);
				}
			}
			if (empty ($_HOAM_setting ['budget']['ids']['organization'])) {
				$_SESSION['error_class_item'] -> addError ('budget|payment|amount', $_HOAM_language ['errors']['budget']['unknown_organization']);
				$_HOAM_log -> add ($_HOAM_language ['errors']['budget']['unknown_organization'], HOAM_MESSAGE_ERROR);
			}
			if (isset ($_POST['budget|payment|method'])) {
				$_SESSION['payment_information']['trusted_budget|payment|method'] = $budget_class -> superClean ($_POST['budget|payment|method'], 32);
				switch ($_SESSION['payment_information']['trusted_budget|payment|method']) {
					case 'check':
						if (isset ($_POST['budget|payment|checknum'])) {
							// There's really not much to check here, since check numbers
							// can be pretty much any format or characters.
							$_SESSION['payment_information']['trusted_budget|payment|checknum'] = $budget_class -> superClean ($_POST['budget|payment|checknum'], $budget_class -> returnColumnSize ('checknum'));
							// However, we will check to make sure we're not reentering a
							// check that was already entered previously.
							$query = "SELECT id FROM budget WHERE checknum='" . $_SESSION['payment_information']['trusted_budget|payment|checknum'] . "' AND customer_id='" . $_SESSION['payment_information']['trusted_budget|payment|customer'] . "'";
							$result = $budget_class -> itemQuery ($query);
							if (!empty ($result)) {
								// Yep, duplicate check.
								if (!isset ($_POST['budget|payment|checknum|ignore'])) {
									$query = "SELECT dateposted FROM budget WHERE checknum='" . $_SESSION['payment_information']['trusted_budget|payment|checknum'] . "' AND customer_id='" . $_SESSION['payment_information']['trusted_budget|payment|customer'] . "'";
									$result = $budget_class -> itemQuery ($query);			
									$_SESSION['error_class_item'] -> addError ('budget|payment|checknum', sprintf ($_HOAM_language ['errors']['budget']['duplicate_check'], $_SESSION['payment_information']['trusted_budget|payment|checknum'], date ($_HOAM_country ['date']['format_reports'], strtotime ($result))));
								}
							}
						}
					break;
					case 'paypal':
						// OK, now we need to get at the correct amount for the
						// payment. If the payment was via PayPal, we need to
						// calculate what the amount really was.
						if ($_SESSION['payment_information']['trusted_budget|payment|amount'] > 0) {
							$surcharge_amount = (int) $_HOAM_setting ['budget']['paypal']['surcharge']['amount'];
							$surcharge_percent = (int) $_HOAM_setting ['budget']['paypal']['surcharge']['percent'];
							$_SESSION['payment_information']['trusted_budget|payment|amount'] = $_SESSION['payment_information']['trusted_budget|payment|amount'] - $surcharge_amount;
							$_SESSION['payment_information']['trusted_budget|payment|amount'] = $_SESSION['payment_information']['trusted_budget|payment|amount'] / ((100 + $surcharge_percent) / 100);
							$_SESSION['payment_information']['trusted_budget|payment|amount'] = number_format ($_SESSION['payment_information']['trusted_budget|payment|amount'], 2);
							$_SESSION['payment_information']['trusted_budget|payment|paypalid'] = $budget_class -> superClean ($_POST['budget|payment|paypalid'], $budget_class -> returnColumnSize ('checknum'));
						}					
						break;
					case 'writeoff':
						break;
					default:
						$_SESSION['error_class_item'] -> addError ('budget|payment|method', $_HOAM_language ['errors']['budget']['unknown_method']);
						break;
				}
			}
			if (isset ($_POST['budget|payment|datereceived'])) {
				$_SESSION['payment_information']['trusted_budget|payment|datereceived'] = (string) $budget_class -> superClean ($_POST['budget|payment|datereceived'], $budget_class -> returnColumnSize ('dateposted'));
				// Make sure we have a valid date
				if (mb_strlen ($_SESSION['payment_information']['trusted_budget|payment|datereceived']) < 5) {
					$_SESSION['error_class_item'] -> addError ('budget|payment|datereceived', $_HOAM_setting ['errors']['generic']['invalid_date']);
				} else {
					$_SESSION['payment_information']['trusted_budget|payment|datereceived'] = date ('m/d/Y', strtotime (preg_replace ('/[-.]/', '/', $_SESSION['payment_information']['trusted_budget|payment|datereceived'])));
					$temp_date = explode ('/', $_SESSION['payment_information']['trusted_budget|payment|datereceived']);
					if (count ($temp_date) == 3) {
						if (checkdate ($temp_date[0], $temp_date[1], $temp_date[2])) {
						} else {
							$_SESSION['error_class_item'] -> addError ('budget|payment|datereceived', $_HOAM_language ['errors']['generic']['invalid_date']);
						}
						// Make sure the date isn't in the past
						if (date ('Y/m/d') < ($temp_date[2] . '/' . $temp_date[0] . '/' . $temp_date[1])) {
							$_SESSION['error_class_item'] -> addError ('budget|payment|datereceived', $_HOAM_language ['errors']['generic']['date_past']);
						}
					} else {
						$_SESSION['error_class_item'] -> addError ('budget|payment|datereceived', $_HOAM_language ['errors']['generic']['invalid_date']);
					}
				}
			}
			if (isset ($_POST['budget|payment|memo'])) {
				// Nothing to check here other than for malicious code.
				$_SESSION['payment_information']['trusted_budget|payment|memo'] = (string) $budget_class -> superClean ($_POST['budget|payment|memo'], $budget_class -> returnColumnSize ('memo'));
				if (mb_strlen ($_SESSION['payment_information']['trusted_budget|payment|memo']) <= 0) {
					$_SESSION['error_class_item'] -> addError ('budget|payment|memo', $_HOAM_language ['errors']['budget']['no_memo']);
				}
			}
			if (isset ($_POST['budget|payment|description'])) {
				// Nothing to check here other than for malicious code.
				$_SESSION['payment_information']['trusted_budget|payment|description'] = (string) $budget_class -> superClean ($_POST['budget|payment|description'], $budget_class -> returnColumnSize ('description'));
			}

// print_r ($_POST); print_r ($_SESSION['payment_information']); $_SESSION['error_class_item'] -> showErrors (); exit();

			// Are there any errors that the current user needs to fix before
			// committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/financial/payment/');
				} else {
					header ('location: /admin/financial/payment/');
				}
			} else {
				if ($_SESSION['payment_information']['trusted_budget|payment|method'] == 'paypal') {
					HOAM_budgetAddPayment ($_SESSION['payment_information']['trusted_budget|payment|customer'],
						$_SESSION['payment_information']['trusted_budget|payment|amount'],
						array ('checknum' => $_SESSION['payment_information']['trusted_budget|payment|paypalid'],
							'date_due' => date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['payment_information']['trusted_budget|payment|datereceived'])),
							'description' => $_SESSION['payment_information']['trusted_budget|payment|description'],
							'memo' => $_SESSION['payment_information']['trusted_budget|payment|memo'],
							'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']
							));
				} else {
					HOAM_budgetAddPayment ($_SESSION['payment_information']['trusted_budget|payment|customer'],
						$_SESSION['payment_information']['trusted_budget|payment|amount'],
						array ('checknum' => $_SESSION['payment_information']['trusted_budget|payment|checknum'],
							'date_due' => date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION['payment_information']['trusted_budget|payment|datereceived'])),
							'description' => $_SESSION['payment_information']['trusted_budget|payment|description'],
							'memo' => $_SESSION['payment_information']['trusted_budget|payment|memo'],
							'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']
							));
				}				

				// Process any attachments
				foreach ($_FILES as $file) {
					if ($file['name'] != NULL) {
						// Import the attachment into the database
						$id = HOAM_attachmentAdd ($file,
							$file,
							$_SESSION['current_user']['id'],
							$_HOAM_setting ['attachment']['flags']['homeowner'],
							'',
							$_SESSION['payment_information']['trusted_budget|payment|customer']);
						if (!$id) {
							echo $_HOAM_language ['errors']['attachment']['import'];
						}
					}
				}

				// Clear the error_class_item variable
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}
				// Clear the payment_information variable
				if (isset ($_SESSION['payment_information'])) {
					unset ($_SESSION['payment_information']);
				}

				header ('Location: /admin/financial/homeowner/invoice/' . (string) $budget_class -> superClean ($_POST['budget|payment|customer'], $budget_class -> returnColumnSize ('customer_id')) . '/');
			}
		}
	} else {
		// We don't know what action we're being asked to perform.
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

?>
