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
if (isset ($_SESSION ['current_user'] ['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group'] ['ids'] ['budget_add'], $_SESSION ['current_user'] ['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group'] ['ids'] ['budget_delete'], $_SESSION ['current_user'] ['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group'] ['ids'] ['budget_edit'], $_SESSION ['current_user'] ['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group'] ['ids'] ['admin'], $_SESSION ['current_user'] ['id']) OR
	($_HOAM_setting ['users']['ids']['root'] === $_SESSION ['current_user'] ['id']))) {
	if (isset ($_POST ['add']) OR isset ($_POST ['cancel']) OR isset ($_POST ['delete']) OR isset ($_POST ['modify'])) {

		require_once (HOAM_PATH_FUNCTION . 'attachments.php');

		$budget_class = new HOAM_budget ('budget');
		$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
		$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');

		// If this is the second (or greater) time through the script (ie,
		// there were errors that had to be fixed before committing the changes)
		// get rid of the old cached information the user entered, we're going
		// to populate it with information just received from the form posting.
		if (isset ($_SESSION ['payment_information'])) {
			unset ($_SESSION ['payment_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION ['error_class_item'])) {
			unset ($_SESSION ['error_class_item']);
		}
		
		if (isset ($_POST ['cancel'])) {
			// Just send them back to the admin page
			header ('Location: /admin/financial/vendor/');
		} elseif (isset ($_POST['delete'])) {
			// Make sure the current user is the owner of the item, OR has
			// admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION ['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION ['current_user']['id']) OR 
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION ['current_user']['id'])) {
				// Make sure we found the invoice
				$invoice_id = (string) $budget_invoice_class -> superClean ($_POST ['budget|invoice|id'], $budget_invoice_class -> returnColumnSize ('id'));
				if ($budget_invoice_class -> idExists ($invoice_id)) {
					if ($budget_invoice_class -> deleteId ($invoice_id)) {
						header ('Location: /admin/financial/vendor/');
					} else {
						// Error Removing Item
						header ('Location: /error/remove/');
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
			$_SESSION ['payment_information'] = array ();
			$_SESSION ['error_class_item'] = new HOAM_errors ('errors');
			
			// Start populating the invoice_information array, and check for
			// errors.
			if (isset ($_POST['budget|invoice|id'])) {
				$_SESSION ['payment_information']['trusted_budget|invoice|id'] = (string) $budget_invoice_class -> superClean ($_POST['budget|invoice|id'], $budget_invoice_class -> returnColumnSize ('id'));
				if ($budget_invoice_class -> idExists ($_SESSION ['payment_information']['trusted_budget|invoice|id'])) {
				} else {
					// The invoice is unknown.
					$_SESSION ['error_class_item'] -> addError ('budget|invoice|id', $_HOAM_language ['errors']['budget']['invoice']['unknown-id']);
				}
			}
			if (isset ($_POST ['budget|payment|amount'])) {
				$_SESSION ['payment_information'] ['trusted_budget|payment|amount'] = floatval (preg_replace ('/,/', '', $budget_invoice_class -> superClean ($_POST['budget|payment|amount'], $budget_class -> returnColumnSize ('amount'))));
				// Make sure the amount isn't too little
				if ($_SESSION ['payment_information']['trusted_budget|payment|amount'] > $budget_invoice_class -> returnTotalAmountDue ($_SESSION['payment_information']['trusted_budget|invoice|id'])) {
					// The amount paid is greater than is owed.
					$_SESSION ['error_class_item'] -> addError ('budget|payment|amount', $_HOAM_language ['errors']['budget']['checknum_swapped']);
				} elseif ($_SESSION ['payment_information']['trusted_budget|payment|amount'] > 0) {
				} else {
					// The amount is too small.
					$_SESSION ['error_class_item'] -> addError ('budget|payment|amount', $_HOAM_language ['errors']['budget']['invoice_amount_negative']);
				}
			}
			if (empty ($_HOAM_setting ['budget']['ids']['organization'])) {
				$_SESSION['error_class_item'] -> addError ('budget|payment|amount', $_HOAM_language ['errors']['budget']['unknown_organization']);
			}
			if (isset ($_POST ['budget|payment|method'])) {
				$_SESSION ['payment_information'] ['trusted_budget|payment|method'] = (string) $budget_invoice_class -> superClean ($_POST ['budget|payment|method'], 16);
				switch ($_SESSION ['payment_information'] ['trusted_budget|payment|method']) {
					case 'autodraft':
						$_SESSION ['payment_information'] ['trusted_budget|payment|autodraft'] = (string) $budget_invoice_class -> superClean ($_POST ['budget|payment|autodraft'], $budget_class -> returnColumnSize ('checknum'));
						break;
					case 'check':
						$_SESSION ['payment_information'] ['trusted_budget|payment|checknum'] = (string) $budget_invoice_class -> superClean ($_POST ['budget|payment|checknum'], $budget_class -> returnColumnSize ('checknum'));
						break;
					case 'credit':
						$_SESSION ['payment_information'] ['trusted_budget|payment|credit'] = (string) $budget_invoice_class -> superClean ($_POST ['budget|payment|credit'], $budget_class -> returnColumnSize ('checknum'));
						break;
				}
			}
			if (isset ($_POST ['budget|invoice|datepaid'])) {
				$_SESSION ['payment_information'] ['trusted_budget|invoice|datepaid'] = (string) $budget_invoice_class -> superClean ($_POST ['budget|invoice|datepaid'], $budget_class -> returnColumnSize ('dateposted'));
				// Make sure we have a valid date
				$_SESSION['payment_information']['trusted_budget|invoice|datepaid'] = date ('m/d/Y', strtotime (preg_replace ('/[-.]/', '/', $_SESSION['payment_information']['trusted_budget|invoice|datepaid'])));
				$temp_date = explode ('/', $_SESSION['payment_information']['trusted_budget|invoice|datepaid']);
				if (count ($temp_date) == 3) {
					if (checkdate ($temp_date[0], $temp_date[1], $temp_date[2])) {
					} else {
						$_SESSION['error_class_item'] -> addError ('budget|invoice|datepaid', $_HOAM_language ['errors']['generic']['invalid_date']);
					}
					// Need to add a javascript popup that recognizes when the
					// date is in the past and asks if there are any late
					// penalties.
					if (date ('Y/m/d') >= ($temp_date[2] . '/' . $temp_date[1] . '/' . $temp_date[0])) {
						// This is being commented out temporarily while adding
						// in old data.
						// $_SESSION['error_class_item'] -> addError ('budget_invoice_date_due', $_HOAM_language ['errors']['generic']['date_past']);
					}
				} else {
					$_SESSION['error_class_item'] -> addError ('budget|invoice|datepaid', $_HOAM['errors']['generic']['invalid_date']);
				}
			}
			if (isset ($_POST ['budget|payment|memo'])) {
				$_SESSION ['payment_information'] ['trusted_budget|payment|memo'] = (string) $budget_invoice_class -> superClean ($_POST ['budget|payment|memo'], $budget_class -> returnColumnSize ('memo'));
				// Make sure the memo isn't too small.
				if (mb_strlen ($_SESSION ['payment_information'] ['trusted_budget|payment|memo']) >= $_HOAM_setting ['budget']['minimum_memo_length']) {
				} else {
					// The amount is too small.
					$_SESSION ['error_class_item'] -> addError ('budget|payment|memo', sprintf ($_HOAM_language ['errors']['budget']['memo_too_short'], $_HOAM_setting ['budget']['minimum_memo_length']));
				}
			}

//print_r ($_POST); print_r ($_SESSION['payment_information']); $_SESSION['error_class_item'] -> showErrors (); exit();
			// Are there any errors that the current user needs to fix before
			// committing the changes?
			if ($_SESSION ['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				header ('location: /admin/financial/vendor/invoice/payment/' . $_SESSION ['payment_information'] ['trusted_budget|invoice|id']);
			} else {
				// Finally! We have good data and can start modifying the
				// database.
				if (isset ($_POST ['add'])) {
					$payment_id = $budget_class -> add ($_SESSION ['current_user']['id']);
					if ($payment_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					}
				} else {
					// Use the information gathered above.
					// Needs to be fixed appropriately
					$payment_id = $_SESSION ['payment_information'] ['trusted_budget|invoice|id'];
				}
				$vendor_id = $budget_invoice_class -> returnVendorId ($_SESSION ['payment_information']['trusted_budget|invoice|id']);
				$budget_class -> changeAccountId ($payment_id, $_HOAM_setting ['budget']['account']['operating']);
				$budget_class -> changeAmount ($payment_id, -$_SESSION ['payment_information']['trusted_budget|payment|amount']);
				$budget_class -> changeCategoryId ($payment_id, $budget_vendor_class -> returnCategoryId ($vendor_id));
				switch ($_SESSION ['payment_information']['trusted_budget|payment|method']) {
					case 'autodraft':
						$budget_class -> changeCheckNumber ($payment_id, $_SESSION ['payment_information']['trusted_budget|payment|autodraft']);
						break;
					case 'check':
						$budget_class -> changeCheckNumber ($payment_id, $_SESSION ['payment_information']['trusted_budget|payment|checknum']);
						break;
					case 'credit':
						$budget_class -> changeCheckNumber ($payment_id, $_SESSION ['payment_information']['trusted_budget|payment|credit']);
						break;
				}
				$budget_class -> changeCustomerId ($payment_id, $_HOAM_setting ['budget']['ids']['organization']);
				$budget_class -> changeInvoiceId ($payment_id, $_SESSION ['payment_information']['trusted_budget|invoice|id']);
				$budget_class -> changeMemo ($payment_id, $_SESSION ['payment_information']['trusted_budget|payment|memo']);
				$budget_class -> changeVendorId ($payment_id, $vendor_id);
				$budget_class -> setBit ($payment_id, $_HOAM_setting['budget']['flags']['payment']);
				
				// Mark both the charge and payment as posted.
				$charge_list = $budget_class -> listChargesOnInvoice ($_SESSION ['payment_information']['trusted_budget|invoice|id']);
				// FIXME -- This is being done temporarily to resolve the issue,
				// but it is NOT at all a correct solution; it may even end up
				// causing problems.
				foreach ($charge_list as $charge) {
					$budget_class -> changeDatePosted ($charge, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION ['payment_information']['trusted_budget|invoice|datepaid'])));			
				}
				// This line needs to be fixed also.
				$budget_class -> changeParentEntryId ($payment_id, $charge_list[0]);
				$budget_class -> changeDateDue ($payment_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION ['payment_information']['trusted_budget|invoice|datepaid'])));
				$budget_class -> changeDatePosted ($payment_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION ['payment_information']['trusted_budget|invoice|datepaid'])));

				// Process any attachments
				foreach ($_FILES as $file) {
					if ($file['name'] != NULL) {
						// Import the attachment into the database
						$id = HOAM_attachmentAdd ($file,
							$file,
							$_SESSION['current_user']['id'],
							$_HOAM_setting ['attachment']['flags']['budget'],
							'',
							array ($payment_id));
						if (!$id) {
							echo $_HOAM_language ['errors']['attachment']['import'];
						}
					}
				}

				// OK, all done. Get rid of our data and finish up.
				unset ($_SESSION ['payment_information']);
				unset ($_SESSION ['error_class_item']);
				
				header ('Location: /admin/financial/vendor/');
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
