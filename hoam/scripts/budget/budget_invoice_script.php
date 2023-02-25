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
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'attachments.php');
		require_once (HOAM_PATH_FUNCTION . 'budget.php');
		
		$budget_class = new HOAM_budget ('budget');
		$budget_category_class = new HOAM_budget_category ('budget_category');
		$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
		$homeowner_class = new HOAM_homeowner ('homeowners');

		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['invoice_information'])) {
			unset ($_SESSION['invoice_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the change, send them back to the main page
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
			// existing invoice.
			$_SESSION['invoice_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
			
			if (isset ($_POST['budget|invoice|customer'])) {
				// Check for multiple customers.
				$_SESSION['invoice_information']['trusted_budget|invoice|customer'] = array ();
				foreach ($_POST['budget|invoice|customer'] as $customer) {
					// First, find out if the customer id is valid
					$customer = (string) $homeowner_class -> superClean ($customer, $homeowner_class -> returnColumnSize ('id'));
					if ($homeowner_class -> idExists ($customer)) {
						array_push ($_SESSION['invoice_information']['trusted_budget|invoice|customer'], $customer);
					} else {
						$_SESSION['error_class_item'] -> addError ('budget|invoice|customer', $_HOAM_language ['errors']['budget']['unknown_customer']);
					}
				}
			} else {
				// No customers were selected.
				$_SESSION['error_class_item'] -> addError ('budget|invoice|customer', $_HOAM_language ['errors']['budget']['customer_none_selected']);
			}
			if (empty ($_HOAM_setting ['budget']['ids']['organization'])) {
				$_SESSION['error_class_item'] -> addError ('budget|invoice|customer', $_HOAM_language ['errors']['budget']['unknown_organization']);
			}
			$_SESSION['invoice_information']['trusted_budget|invoice|charge_count'] = (int) $budget_class -> superClean ($_POST['charge_count'], 5);
			$charge_list = array ();
			for ($i = 0; $i < $_SESSION['invoice_information']['trusted_budget|invoice|charge_count']; $i++) {
				$charge_list [$i]['amount'] = (float) preg_replace ('/,/', '', $budget_class -> superClean ($_POST['amount' . $i], $budget_class -> returnColumnSize ('amount')));
				// Make sure the amount is positive.
				if ($charge_list [$i]['amount'] < 0) {
					// Put some type of error message here.
				}
				$charge_list [$i]['datedue'] = (string) $budget_class -> superClean ($_POST['datedue' . $i], $budget_class -> returnColumnSize ('datedue'));
				$charge_list [$i]['datedue'] = date ('m/d/Y', strtotime (preg_replace ('/[-.]/', '/', $charge_list [$i]['datedue'])));
				$temp_date = explode ('/', $charge_list [$i]['datedue']);
				if (count ($temp_date) == 3) {
					if (checkdate ($temp_date[0], $temp_date[1], $temp_date[2])) {
					} else {
						$_SESSION['error_class_item'] -> addError ('datedue' . $i, $_HOAM_language ['errors']['generic']['invalid_date']);
					}
					// Make sure the date isn't in the past
					if (date ('Y/m/d') > ($temp_date[2] . '/' . $temp_date[0] . '/' . $temp_date[1])) {
						// This is being commented out temporarily while adding in old data.
						$_SESSION['error_class_item'] -> addError ('datedue', $_HOAM_language ['errors']['generic']['date_past']);
					}
				} else {
					$_SESSION['error_class_item'] -> addError ('datedue' . $i, $_HOAM['errors']['generic']['invalid_date']);
				}
				$charge_list [$i]['datelate'] = (string) $budget_class -> superClean ($_POST['datelate' . $i], $budget_class -> returnColumnSize ('datelate'));
				$charge_list [$i]['datelate'] = date ('m/d/Y', strtotime (preg_replace ('/[-.]/', '/', $charge_list [$i]['datelate'])));
				$temp_date = explode ('/', $charge_list [$i]['datelate']);
				if (count ($temp_date) == 3) {
					if (checkdate ($temp_date[0], $temp_date[1], $temp_date[2])) {
					} else {
						$_SESSION['error_class_item'] -> addError ('datelate' . $i, $_HOAM_language ['errors']['generic']['invalid_date']);
					}
					// Make sure the date isn't in the past
					if (date ('Y/m/d') > ($temp_date[2] . '/' . $temp_date[0] . '/' . $temp_date[1])) {
						// This is being commented out temporarily while adding in old data.
						$_SESSION['error_class_item'] -> addError ('datelate', $_HOAM_language ['errors']['generic']['date_past']);
					}
				} else {
					$_SESSION['error_class_item'] -> addError ('datelate' . $i, $_HOAM['errors']['generic']['invalid_date']);
				}
				$charge_list [$i]['memo'] = (string) $budget_class -> superClean ($_POST['memo' . $i], $budget_class -> returnColumnSize ('memo'));
				$charge_list [$i]['category'] = (string) $budget_category_class -> superClean ($_POST['category' . $i], $budget_category_class -> returnColumnSize ('name'));
				$charge_list [$i]['category'] = preg_replace ('/^&nbsp; └─ &nbsp;/', '', $charge_list [$i]['category']);
				$charge_list [$i]['category'] = $budget_category_class -> returnId ($charge_list [$i]['category']);
			}
			if (isset ($_POST['budget|invoice|recent'])) {
				$_SESSION['invoice_information']['trusted_budget|invoice|recent'] = (int) $_POST['budget|invoice|recent'];
				// TOOD. Check for previous invoices and alert if there aren't
				// any, and also warn (with override) if the most recent invoice
				// was over # days (or one month) previous.
			}

// print_r ($charge_list); exit;
// print_r ($_POST); print_r ($_SESSION['invoice_information']); $_SESSION['error_class_item'] -> showErrors (); exit();

			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/financial/invoice/');
				} else {
					header ('location: /admin/financial/invoice/');
				}
			} else {
				foreach ($_SESSION['invoice_information']['trusted_budget|invoice|customer'] as $customer) {
					if ($_SESSION['invoice_information']['trusted_budget|invoice|recent'] === 1) {
						$invoice_list = $budget_invoice_class -> listInvoicesForCustomer ($customer, TRUE);
						if (is_array ($invoice_list)) {
							$invoice_id = $invoice_list [count ($invoice_list) - 1];
						} else {
							// If there aren't any previous invoices, just
							// generate a new id.
							$invoice_id = $budget_invoice_class -> add ($_SESSION['current_user']['id']);
						}
					} else {
						$invoice_id = $budget_invoice_class -> add ($_SESSION['current_user']['id']);
					}
					$budget_invoice_class -> changeInvoiceDate ($invoice_id, date ($_HOAM_country ['date']['format_mysql']));
					$budget_invoice_class -> changeCustomerId ($invoice_id, $customer);
					$budget_invoice_class -> changeVendorId ($invoice_id, $_HOAM_setting ['budget']['ids']['organization']);
					foreach ($charge_list as $charge) { 
						$charge_id = HOAM_budgetAddCharge (NULL,
									$charge ['amount'],
									$charge ['category'],
									array ('customer_id' => $customer,
											'date_due' => date ($_HOAM_country ['date']['format_mysql'], strtotime ($charge ['datedue'])),
											'date_late' => date ($_HOAM_country ['date']['format_mysql'], strtotime ($charge ['datelate'])),
											'flags' => $budget_category_class -> returnFlags ($charge ['category']),
											'invoice_id' => $invoice_id,
											'memo' => $charge ['memo'],
											'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']));
						if ($charge_id == FALSE) {
							// Error Adding Item
							header ('Location: /error/add/');
							exit ();
						}
						// Now that we're done creating the entry for the
						// homeowner's account, we need to create our matching
						// entry in the budget for the HOA's income account.
						$charge_id = HOAM_budgetAddCharge (NULL,
									$charge ['amount'],
									$charge ['category'],
									array ('account_id' => $_HOAM_setting ['budget']['account']['income'],
											'customer_id' => $_HOAM_setting ['budget']['ids']['organization'],
											'date_due' => date ($_HOAM_country ['date']['format_mysql'], strtotime ($charge ['datedue'])),
											'date_late' => date ($_HOAM_country ['date']['format_mysql'], strtotime ($charge ['datelate'])),
											'flags' => $budget_category_class -> returnFlags ($charge ['category']),
											'invoice_id' => $invoice_id,
											'memo' => $charge ['memo'],
											'vendor_id' => $customer));
					}
					// Process any attachments
					foreach ($_FILES as $file) {
						if ($file['name'] != NULL) {
							// Import the attachment into the database
							$id = HOAM_attachmentAdd ($file,
								$file,
								$_SESSION['current_user']['id'],
								$_HOAM_setting ['attachment']['flags']['budget'],
								'',
								$invoice_id);
							if (!$id) {
								echo $_HOAM_language ['errors']['attachment']['import'];
							}
						}
					}
				}

				/*************************************************************************/

				// Clear the error_class_item variable since everything worked ok.
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}
				// Clear the invoice_information variable since everything worked ok.
				if (isset ($_SESSION['invoice_information'])) {
					unset ($_SESSION['invoice_information']);
				}

				header ('Location: /admin/financial/');
			}
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
}

exit ();

?>
