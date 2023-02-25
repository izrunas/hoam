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
		require_once (HOAM_PATH_FUNCTION . 'budget.php');

		$attachment_class = new HOAM_attachments ('attachments');		
		$budget_class = new HOAM_budget ('budget');
		$budget_category_class = new HOAM_budget_category ('budget_category');
		$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
		$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');

		// If this is the second (or greater) time through the script (ie,
		// there were errors that had to be fixed before committing the changes)
		// get rid of the old cached information the user entered, we're going
		// to populate it with information just received from the form posting.
		if (isset ($_SESSION ['invoice_information'])) {
			unset ($_SESSION ['invoice_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION ['error_class_item'])) {
			unset ($_SESSION ['error_class_item']);
		}
		
		if (isset ($_POST ['cancel'])) {
			// Just send them back to the admin page
			header ('Location: /admin/financial/vendor/');
		} elseif (isset ($_POST ['delete'])) {
			// Make sure the current user is the owner of the item, OR has
			// admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION ['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION ['current_user']['id']) OR 
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION ['current_user']['id'])) {
				// Make sure we found the invoice
				$invoice_id = (string) $budget_invoice_class -> superClean ($_POST ['budget|invoice|id'], $budget_invoice_class -> returnColumnSize ('id'));
				if ($budget_invoice_class -> idExists ($invoice_id)) {
					if ($budget_invoice_class -> deleteId ($invoice_id)) {
						$_HOAM_log -> add ($_HOAM_language ['log']['budget']['invoice']['deleted'],
											HOAM_MESSAGE_NOTICE,
											array ('owner_id' => $invoice_id,
													'flags' => $_HOAM_setting ['log']['flags']['budget_invoice']));
						$charge_list = $budget_class -> listChargesOnInvoice ($invoice_id);
						foreach ($charge_list as $charge) {
							$budget_class -> deleteId ($charge);
						}
						// Need to make sure we also go back and delete any
						// attachments for this invoice
						$attachment_list = $attachment_class -> listWithOwner ($invoice_id, $_HOAM_setting ['attachment']['flags']['budget']);
						foreach ($attachment_list as $attachment) {
							$attachment_class -> deleteId ($attachment);
						}
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
			$_SESSION ['invoice_information'] = array ();
			$_SESSION ['error_class_item'] = new HOAM_errors ('errors');
			
			// Start populating the invoice_information array, and check for
			// errors.
			if (isset ($_POST['budget|invoice|id'])) {
				$_SESSION ['invoice_information']['trusted_budget|invoice|id'] = (string) $budget_class -> superClean ($_POST['budget|invoice|id'], $budget_invoice_class -> returnColumnSize ('id'));
				if ($budget_invoice_class -> idExists ($_SESSION ['invoice_information']['trusted_budget|invoice|id'])) {
				} else {
					// The invoice is unknown.
					$_SESSION ['error_class_item'] -> addError ('budget|invoice|id', $_HOAM_language ['errors']['budget']['invoice']['unknown-id']);
				}
			}
			if (isset ($_POST['budget|invoice|vendor'])) {
				$_SESSION ['invoice_information']['trusted_budget|invoice|vendor'] = (string) $budget_vendor_class -> superClean ($_POST['budget|invoice|vendor'], $budget_vendor_class -> returnColumnSize ('id'));
				if ($budget_vendor_class -> idExists ($_SESSION ['invoice_information']['trusted_budget|invoice|vendor'])) {
				} else {
					// The vendor is unknown.
					$_SESSION ['error_class_item'] -> addError ('budget|invoice|vendor', $_HOAM_language ['errors']['budget']['vendor']['unknown-id']);
				}
			}
			if (isset ($_POST ['budget|invoice|amount'])) {
				$_SESSION ['invoice_information']['trusted_budget|invoice|amount'] = floatval (preg_replace ('/,/', '', $budget_class -> superClean ($_POST['budget|invoice|amount'], $budget_class -> returnColumnSize ('amount'))));
				// Make sure the amount isn't too little
				if ($_SESSION ['invoice_information']['trusted_budget|invoice|amount'] > 0 OR (isset ($_POST['budget|invoice|amount|ignore']))) {
				} else {
					// The amount is too small.
					$_SESSION ['error_class_item'] -> addError ('budget|invoice|amount', $_HOAM_language ['errors']['budget']['invoice_amount_negative']);
				}
				if (!$budget_vendor_class -> isBitSet ($_SESSION ['invoice_information']['trusted_budget|invoice|vendor'], $_HOAM_setting ['budget']['flags']['ignore_average'])) {
					// Perform a simple check to see whether we're way above or
					// below the average invoice amount
					$invoice_average = $budget_invoice_class -> returnAverageInvoice ($_SESSION ['invoice_information']['trusted_budget|invoice|vendor']);
					// However, this will fail if it's the first invoice entered
					// for this vendor, so we have to perform a special check
					// for that case as well.
					if (($invoice_average != 0) AND ($_HOAM_setting ['budget']['vendor']['percent_different'] != 0)) {
						if ((!isset ($_POST['budget|invoice|amount|ignore'])) AND $_SESSION ['invoice_information']['trusted_budget|invoice|amount'] > ($invoice_average * ((100 + $_HOAM_setting ['budget']['vendor']['percent_different']) / 100))) {
							$_SESSION ['error_class_item'] -> addError ('budget|invoice|amount', sprintf ($_HOAM_language ['errors']['budget']['invoice_amount_high'], HOAM_returnCurrency ($invoice_average)));
						} elseif ((!isset ($_POST['budget|invoice|amount|ignore'])) AND $_SESSION ['invoice_information']['trusted_budget|invoice|amount'] < ($invoice_average * ((100 - $_HOAM_setting ['budget']['vendor']['percent_different']) / 100))) {
							$_SESSION ['error_class_item'] -> addError ('budget|invoice|amount', sprintf ($_HOAM_language ['errors']['budget']['invoice_amount_low'], HOAM_returnCurrency ($invoice_average)));
						}
					} else {
						// This must be the first invoice entered for this vendor,
						// or we're ignoring the issue.
					}
				}
			}
			if (isset ($_POST ['budget|invoice|memo'])) {
				$_SESSION ['invoice_information']['trusted_budget|invoice|memo'] = (string) $budget_class -> superClean ($_POST ['budget|invoice|memo'], $budget_class -> returnColumnSize ('memo'));
				// Make sure the memo isn't too small.
				if (mb_strlen ($_SESSION ['invoice_information']['trusted_budget|invoice|memo']) >= $_HOAM_setting ['budget']['minimum_memo_length']) {
				} else {
					// The amount is too small.
					$_SESSION ['error_class_item'] -> addError ('budget|invoice|memo', sprintf ($_HOAM_language ['errors']['budget']['memo_too_short'], $_HOAM_setting ['budget']['minimum_memo_length']));
				}
			}
			if (isset ($_POST ['budget|invoice|description'])) {
				$_SESSION ['invoice_information']['trusted_budget|invoice|description'] = (string) $budget_class -> superClean ($_POST ['budget|invoice|description'], $budget_class -> returnColumnSize ('description'));
				// Make sure the description isn't too short.
				if (mb_strlen ($_SESSION ['invoice_information']['trusted_budget|invoice|description']) >= $_HOAM_setting ['budget']['minimum_description_length']) {
				} else {
					// The description is too short.
					$_SESSION ['error_class_item'] -> addError ('budget|invoice|description', sprintf ($_HOAM_language ['errors']['budget']['description_too_short'], $_HOAM_setting ['budget']['minimum_description_length']));
				}
			}
			if (isset ($_POST ['budget|invoice|number'])) {
				$_SESSION ['invoice_information']['trusted_budget|invoice|number'] = (string) $budget_class -> superClean ($_POST ['budget|invoice|number'], $budget_class -> returnColumnSize ('invoice_id'));
				if (mb_strlen ($_SESSION ['invoice_information']['trusted_budget|invoice|number']) == 0) {
					$_SESSION ['error_class_item'] -> addError ('budget|invoice|number', $_HOAM_language ['errors']['generic']['missing']);
				}
				// We also want to make sure we're not entering an invoice twice
				// (eg, duplicate). However, we need to make sure we can
				// override if necessary.
				$query = "SELECT id FROM budget_invoice WHERE vendor_id='" . $_SESSION ['invoice_information']['trusted_budget|invoice|vendor'] . "' AND number='" . $_SESSION['invoice_information']['trusted_budget|invoice|number'] . "'";
				$result = $budget_invoice_class -> itemQuery ($query);
				if (!empty ($result)) {
					// Yep, duplicate number.
					if (!isset ($_POST['budget|invoice|number|ignore'])) {
						$query = "SELECT datecreated FROM budget_invoice WHERE vendor_id='" . $_SESSION['invoice_information']['trusted_budget|invoice|vendor'] . "' AND number='" . $_SESSION['invoice_information']['trusted_budget|invoice|number'] . "'";
						$result = $budget_invoice_class -> itemQuery ($query);			
						$_SESSION['error_class_item'] -> addError ('budget|invoice|number', sprintf ($_HOAM_language ['errors']['budget']['invoice']['duplicate'], $_SESSION['invoice_information']['trusted_budget|invoice|number'], date ($_HOAM_country ['date']['format_reports'], strtotime ($result))));
					}
				}
				
			}
			if (isset ($_POST ['budget|invoice|category'])) {
				$_SESSION ['invoice_information']['trusted_budget|invoice|category'] = (string) $budget_category_class -> superClean ($_POST ['budget|invoice|category'], $budget_category_class -> returnColumnSize ('id'));
				// Make sure the category is valid.
				if (!$budget_category_class -> idExists ($_SESSION ['invoice_information']['trusted_budget|invoice|category'])) {
					$_SESSION ['error_class_item'] -> addError ('budget|invoice|category', $_HOAM_language ['errors']['budget']['category']['unknown-id']);
				}
			}
			if (isset ($_POST ['budget|invoice|datedue'])) {
				$_SESSION ['invoice_information']['trusted_budget|invoice|datedue'] = (string) $budget_class -> superClean ($_POST ['budget|invoice|datedue'], $budget_class -> returnColumnSize ('datedue'));
				// Make sure we have a valid date
				$_SESSION['invoice_information']['trusted_budget|invoice|datedue'] = date ('m/d/Y', strtotime (preg_replace ('/[-.]/', '/', $_SESSION['invoice_information']['trusted_budget|invoice|datedue'])));
				$temp_date = explode ('/', $_SESSION['invoice_information']['trusted_budget|invoice|datedue']);
				if (count ($temp_date) == 3) {
					if (checkdate ($temp_date[0], $temp_date[1], $temp_date[2])) {
					} else {
						$_SESSION['error_class_item'] -> addError ('budget|invoice|datedue', $_HOAM_language ['errors']['generic']['invalid_date']);
					}
					// Need to add a javascript popup that recognizes when the
					// date is in the past and asks if there are any late
					// penalties.
					if (date ('Y/m/d') > ($temp_date[2] . '/' . $temp_date[0] . '/' . $temp_date[1])) {
						// This is being commented out temporarily while adding
						// in old data.
						if (!isset ($_POST['budget|invoice|datedue|ignore'])) {
							$_SESSION['error_class_item'] -> addError ('budget|invoice|datedue', $_HOAM_language ['errors']['generic']['date_past']);
						}
					}
				} else {
					$_SESSION['error_class_item'] -> addError ('budget|invoice|datedue', $_HOAM['errors']['generic']['invalid_date']);
				}
			}
			if (isset ($_POST ['budget|payment|method'])) {
				$_SESSION ['invoice_information']['trusted_budget|payment|method'] = (string) $budget_class -> superClean ($_POST ['budget|payment|method'], 16);
				switch ($_SESSION ['invoice_information']['trusted_budget|payment|method']) {
					case 'autodraft':
						$_SESSION ['invoice_information']['trusted_budget|payment|autodraft'] = (string) $budget_class -> superClean ($_POST ['budget|payment|autodraft'], $budget_class -> returnColumnSize ('checknum'));
						break;
					case 'check':
						$_SESSION ['invoice_information']['trusted_budget|payment|checknum'] = (string) $budget_class -> superClean ($_POST ['budget|payment|checknum'], $budget_class -> returnColumnSize ('checknum'));
						// Going to check the checknum for consistency with
						// previous check numbers entered. Also, we're ordering
						// by datecreated rather than checknum in case the
						// Association ordered new checks that overlapped a
						// previously used range.
						$query = "SELECT checknum FROM budget WHERE customer_id = '" . $_HOAM_setting ['budget']['ids']['organization'] . "' AND account_id = '" . $_HOAM_setting ['budget']['account']['expense'] . "' ORDER BY datecreated DESC limit 1";
						$previous_checknum = $budget_class -> itemQuery ($query);
						// Ignore if this is the first check entered.
						if (($previous_checknum != FALSE) OR ($previous_checknum != NULL)) {
							if (!isset ($_POST['budget|payment|checknum|ignore'])) {
								if (($previous_checknum < ($_SESSION ['invoice_information']['trusted_budget|payment|checknum'] + 2)) OR
									($previous_checknum > ($_SESSION ['invoice_information']['trusted_budget|payment|checknum'] - 2))) {
										$_SESSION['error_class_item'] -> addError ('budget|payment|checknum', sprintf ($_HOAM_language ['errors']['budget']['checknum_expected_range'], $previous_checknum));
								}
							}
						}
						break;
					case 'credit':
						$_SESSION ['invoice_information']['trusted_budget|payment|credit'] = (string) $budget_class -> superClean ($_POST ['budget|payment|credit'], $budget_class -> returnColumnSize ('checknum'));
						break;
				}
			}
			if (isset ($_POST ['budget|invoice|datepaid'])) {
				$_SESSION ['invoice_information']['trusted_budget|invoice|datepaid'] = (string) $budget_class -> superClean ($_POST ['budget|invoice|datepaid'], $budget_class -> returnColumnSize ('dateposted'));
				// Make sure we have a valid date
				$_SESSION['invoice_information']['trusted_budget|invoice|datepaid'] = date ('m/d/Y', strtotime (preg_replace ('/[-.]/', '/', $_SESSION['invoice_information']['trusted_budget|invoice|datepaid'])));
				$temp_date = explode ('/', $_SESSION['invoice_information']['trusted_budget|invoice|datepaid']);
				if (count ($temp_date) == 3) {
					if (checkdate ($temp_date[0], $temp_date[1], $temp_date[2])) {
					} else {
						$_SESSION['error_class_item'] -> addError ('budget|invoice|datepaid', $_HOAM_language ['errors']['generic']['invalid_date']);
					}
					// Need to add a javascript popup that recognizes when the
					// date is in the past and asks if there are any late
					// penalties.
					if (date ('Y/m/d') > ($temp_date[2] . '/' . $temp_date[0] . '/' . $temp_date[1])) {
						// This is being commented out temporarily while adding
						// in old data.
						if (!isset ($_POST['budget|invoice|datepaid|ignore'])) {
							$_SESSION['error_class_item'] -> addError ('budget|invoice|datepaid', $_HOAM_language ['errors']['generic']['date_past']);
						}
					}
				} else {
					$_SESSION['error_class_item'] -> addError ('budget|invoice|datepaid', $_HOAM['errors']['generic']['invalid_date']);
				}
			}
			
// print_r ($_POST); print_r ($_SESSION['invoice_information']); $_SESSION['error_class_item'] -> showErrors (); exit();
			
			// Are there any errors that the current user needs to fix before
			// committing the changes?
			if ($_SESSION ['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST ['add'])) {
					header ('location: /admin/financial/vendor/invoice/add/');
					exit;
				} else {
					header ('location: /admin/financial/vendor/invoice/edit/' . $_SESSION ['invoice_information']['trusted_budget|invoice|id'] . '/');
					exit;
				}
			} else {
				// Finally! We have good data and can start modifying the
				// database.
				if (isset ($_POST ['add'])) {
					$invoice_id = $budget_invoice_class -> add ($_SESSION ['current_user']['id']);
					if ($invoice_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						$_HOAM_log -> add ($_HOAM_language ['log']['budget']['invoice']['created'],
											HOAM_MESSAGE_NOTICE,
											array ('owner_id' => $invoice_id,
													'flags' => $_HOAM_setting ['log']['flags']['budget_invoice']));
					}
				} else {
					// Use the information gathered above.
					$invoice_id = $_SESSION ['invoice_information']['trusted_budget|invoice|id'];
					$_HOAM_log -> add ($_HOAM_language ['log']['budget']['invoice']['modified'],
										HOAM_MESSAGE_INFORMATIONAL,
										array ('owner_id' => $invoice_id,
												'flags' => $_HOAM_setting ['log']['flags']['budget_invoice']));
				}
				$budget_invoice_class -> changeCustomerId ($invoice_id, $_HOAM_setting ['budget']['ids']['organization']);
				$budget_invoice_class -> changeInvoiceDate ($invoice_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION ['invoice_information']['trusted_budget|invoice|datedue'])));
				$budget_invoice_class -> changeNumber ($invoice_id, $_SESSION ['invoice_information']['trusted_budget|invoice|number']);
				$budget_invoice_class -> changeVendorId ($invoice_id, $_SESSION ['invoice_information']['trusted_budget|invoice|vendor']);

				// OK, the invoice has been created. Now add a corresponding
				// charge to the budget.
				if (isset ($_POST ['add'])) {
					$charge_id = HOAM_budgetAddCharge (NULL,
									$_SESSION ['invoice_information']['trusted_budget|invoice|amount'],
									$_SESSION ['invoice_information']['trusted_budget|invoice|category'],
									array ('account_id' => $_HOAM_setting ['budget']['account']['expense'],
										'category_id' => $_SESSION ['invoice_information']['trusted_budget|invoice|category'],
										'customer_id' => $_HOAM_setting ['budget']['ids']['organization'],
										'date_due' => date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION ['invoice_information']['trusted_budget|invoice|datedue'])),
										'invoice_id' => $invoice_id,
										'memo' => $_SESSION ['invoice_information']['trusted_budget|invoice|memo'],
										'vendor_id' => $_SESSION ['invoice_information']['trusted_budget|invoice|vendor']));
					if ($charge_id === FALSE) {
						header ('Location: /error/add/');
						exit ();
					}
				} else {
					// Use the information gathered above.
					// Needs to be fixed appropriately
					// $id = $_SESSION ['invoice_information']['trusted_budget|invoice|id'];
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

				// Finally, if we were provided payment information, create a
				// corresponding payment entry.
				if (($_SESSION ['invoice_information']['trusted_budget|payment|method'] == 'check' AND !empty($_SESSION ['invoice_information']['trusted_budget|payment|checknum'])) OR
					($_SESSION ['invoice_information']['trusted_budget|payment|method'] == 'autodraft') OR
					($_SESSION ['invoice_information']['trusted_budget|payment|method'] == 'credit')) {
					if (isset ($_POST ['add'])) {
						$payment_id = $budget_class -> add ($_SESSION ['current_user']['id']);
						if ($payment_id == FALSE) {
							// Error Adding Item
							header ('Location: /error/add/');
						}
					} else {
						// Use the information gathered above.
						// Needs to be fixed appropriately
						// $payment_id = $_SESSION ['invoice_information']['trusted_budget|invoice|id'];
					}

					$budget_class -> changeAccountId ($payment_id, $_HOAM_setting ['budget']['account']['operating']);
					$budget_class -> changeAmount ($payment_id, -$_SESSION ['invoice_information']['trusted_budget|invoice|amount']);
					$budget_class -> changeCategoryId ($payment_id, $_SESSION ['invoice_information']['trusted_budget|invoice|category']);
					switch ($_SESSION ['invoice_information']['trusted_budget|payment|method']) {
						case 'autodraft':
							$budget_class -> changeMemo ($payment_id, $_SESSION ['invoice_information']['trusted_budget|payment|autodraft']);
							break;
						case 'check':
							$budget_class -> changeCheckNumber ($payment_id, $_SESSION ['invoice_information']['trusted_budget|payment|checknum']);
							$budget_class -> changeMemo ($payment_id, $_HOAM_language ['field_names']['budget']['payment']['by_check'] . ' #' . $_SESSION ['invoice_information']['trusted_budget|payment|checknum']);
							break;
					}
					$budget_class -> changeCustomerId ($payment_id, $_HOAM_setting ['budget']['ids']['organization']);
					$budget_class -> changeInvoiceId ($payment_id, $invoice_id);
					$budget_class -> changeVendorId ($payment_id, $_SESSION ['invoice_information']['trusted_budget|invoice|vendor']);
					$budget_class -> setBit ($payment_id, $_HOAM_setting['budget']['flags']['payment']);
					// We currently only have one charge per invoice, so it's safe
					// to do it this way.
					$budget_class -> changeParentEntryId ($payment_id, $charge_id);
					
					// Mark both the charge and payment as posted.
					$budget_class -> changeDatePosted ($charge_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION ['invoice_information']['trusted_budget|invoice|datepaid'])));
					$budget_class -> changeDateDue ($payment_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION ['invoice_information']['trusted_budget|invoice|datepaid'])));
					$budget_class -> changeDatePosted ($payment_id, date ($_HOAM_country ['date']['format_mysql'], strtotime ($_SESSION ['invoice_information']['trusted_budget|invoice|datepaid'])));
//					$budget_class -> changeFlags ($payment_id, $_SESSION ['invoice_information']['trusted_budget|invoice|flags']);
				}

				// OK, all done. Get rid of our data and finish up.
				unset ($_SESSION ['invoice_information']);
				unset ($_SESSION ['error_class_item']);
				
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
	exit ();
}

?>
