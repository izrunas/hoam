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
 * Questions specific to HOAM should be directed to ARP Realty. Please see the
 * HOAM web site at http://hoam.arprs.com/
 *
 * Some portions of HOAM incorporate ideas and/or code from other sources, and
 * those portions are explicitly mentioned and attributed in the relevant
 * section of HOAM source code. Questions about that code should be directed to
 * the original authors.
 *
 */

function HOAM_maintenanceBudget () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	require_once (HOAM_PATH_FUNCTION . 'budget.php');

	$HTML = '<h3 style="text-decoration: underline">Budget Maintenance</h3>';
	$_HOAM_log -> add ('Beginning budget maintenance.', HOAM_MESSAGE_NOTICE);

	$HTML .= '<h4>Applying Association Assessments</h4>';
	$_HOAM_log -> add ('Budget maintenance: checking whether to apply association assessments.', HOAM_MESSAGE_NOTICE);
	if (HOAM_budgetApplyAssessmentsToday ()) {
		$HTML .= '<h5>Assessments can be applied today.</h5>';
		$HTML .= '<ul>';
		$_HOAM_log -> add ('Budget maintenance: assessments can be applied today.', HOAM_MESSAGE_INFORMATIONAL);
		$HTML .= HOAM_maintenanceBudgetApplyAssessments ();
		$HTML .= '</ul>';
	} else {
		$HTML .= '<p>' . $_HOAM_language ['errors']['generic']['no-action-necessary'] . '</p>';
		$_HOAM_log -> add ('Budget maintenance: assessments should not be applied today.', HOAM_MESSAGE_INFORMATIONAL);
	}
	
	$HTML .= '<h4>Checking for Credits to Apply</h4>';
	$_HOAM_log -> add ('Budget maintenance: checking for credits to apply.', HOAM_MESSAGE_NOTICE);
	$HTML .= HOAM_maintenanceBudgetApplyCredits ();

	$HTML .= '<h4>Checking Payment Status / Escalation</h4>';
	// Not sure if we need to spend time modifying this for handling bankrupt
	// homeowners; usually anyone who declares bankruptcy has already been
	// delinquent for a while and will have gone through all of the escalations
	// available. Also, we need to still handle the bankrupt charges separately
	// from any new charges, and those new charges still need to go through a
	// separate escalation as they aren't covered by the bankruptcy plan.
	$_HOAM_log -> add ('Budget maintenance: checking payment status / escalation.', HOAM_MESSAGE_NOTICE);
	if ((date ('j') == 1) OR
		(date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) > $_HOAM_setting ['hoam']['last_maintenance_run'])) {
		$_HOAM_log -> add ('Budget maintenance: accounts can be automatically escalated if necessary.', HOAM_MESSAGE_DEBUG);
		$HTML .= HOAM_budgetEscalateAccounts ();
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-action-necessary'] . '</li></ul>';
	}

	$HTML .= '<h4>Applying Late and Interest Fees</h4>';
	$_HOAM_log -> add ('Budget maintenance: applying late and interest fees.', HOAM_MESSAGE_NOTICE);
	$HTML .= HOAM_maintenanceBudgetApplyInterestLate ();

	$HTML .= '<h4>Creating Owner Invoices</h4>';
	$_HOAM_log -> add ('Budget maintenance: creating owner invoices.', HOAM_MESSAGE_NOTICE);
	$HTML .= HOAM_maintenanceBudgetOwnerInvoices ();
	
	$HTML .= '<h4>Checking Vendor Invoices</h4>';
	$_HOAM_log -> add ('Budget maintenance: checking vendor invoices.', HOAM_MESSAGE_NOTICE);
	$HTML .= HOAM_maintenanceBudgetVendorInvoice ();
	
	$HTML .= '<h4>Emailing Notices to Owners</h4>';
	// Only send an email if the feature is enabled.
	if ($_HOAM_setting ['email']['automated']) {
		// Do this on the 20th of each month, or up to the 25th of each month if
		// the maintenance script hasn't been run yet during this time.
		if ((date ('j') == '20') OR
			// Between the 20th and 25th.
			(((date ('j') > 20) AND (date ('j') < 26)) AND
			// The maintenance routine hasn't run yet.
			$_HOAM_setting ['hoam']['last_maintenance_run'] < date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('n'), 20, date ('Y'))))) {
			$_HOAM_log -> add ('Budget maintenance: It is the ' . date ('jS') . ' of the month and email notices have not yet been sent.', HOAM_MESSAGE_NOTICE);
			$_HOAM_log -> add ('Budget maintenance: emailing notices to owners.', HOAM_MESSAGE_NOTICE);
			$HTML .= HOAM_maintenanceBudgetEmailDue ();
		} else {
			$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-action-necessary'] . '</li></ul>';
		}
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['website']['feature_disabled'] . '</li></ul>';
	}
	
	$_HOAM_log -> add ('Budget maintenance complete.', HOAM_MESSAGE_NOTICE);
	return ($HTML);
}

function HOAM_maintenanceBudgetApplyAssessments () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	
	$budget_class = new HOAM_budget ('budget');
	$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$property_class = new HOAM_property ('misc_property');

	// Don't try to do anything if the organization hasn't been set.
	if (empty ($_HOAM_setting ['budget']['ids']['organization'])) {
		$HTML = $_HOAM_language ['errors']['budget']['unknown_organization'];
		$_HOAM_log -> add ($_HOAM_language ['errors']['budget']['unknown_organization'], HOAM_MESSAGE_ERROR);
		return ($HTML);
	} else {
		$HTML = '';
	}
	$customer_list = $homeowner_class -> listCurrentHomeowners ();
	foreach ($customer_list as $current_customer) {
		$HTML .= '<li>Processing ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($current_customer)) . ' "' . stripslashes (html_encode ($homeowner_class -> returnName ($current_customer))) . '".';
		$_HOAM_log -> add ('Budget maintenance: processing ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($current_customer)) . ' "' . stripslashes (html_encode ($homeowner_class -> returnName ($current_customer))) . '".', HOAM_MESSAGE_INFORMATIONAL);
		// Need to add one other check here to make sure that we don't add
		// the assessments more than once, which would happen if the script
		// was run more than once a day. Also, this helps us recover in case
		// the script is aborted partway through execution.
		// $_HOAM_log -> add ('Budget maintenance: checking for prior application of assessments today.', HOAM_MESSAGE_DEBUG);
		$query = "SELECT datecreated FROM budget WHERE customer_id='$current_customer' AND flags & " . $_HOAM_setting ['budget']['flags']['assessment'] . " ORDER BY datecreated DESC LIMIT 1";
		$datecreated = $budget_class -> itemQuery ($query);
		if (date ($_HOAM_country ['date']['format_mysql'], strtotime ($datecreated)) == date ($_HOAM_country ['date']['format_mysql'])) {
			// Dues have already been applied for this owner
			$_HOAM_log -> add ('Budget maintenance: assessment already applied today.', HOAM_MESSAGE_DEBUG);
			$HTML .= '<ul><li>Assessment already applied today.</li></ul>';
		} else {
			$_HOAM_log -> add ('Budget maintenance: applying assessment.', HOAM_MESSAGE_DEBUG);
			$invoice_list = $budget_invoice_class -> listInvoicesForCustomer ($current_customer, TRUE);
			if (is_array ($invoice_list)) {
				if (date ($_HOAM_country ['date']['format_mysql']) == $budget_invoice_class -> returnInvoiceDate ($invoice_list[0])) {
					$invoice_id = $invoice_list[0];
					$_HOAM_log -> add ('Budget maintenance: Appending any new charges to existing invoice already created today: ' . $invoice_id, HOAM_MESSAGE_DEBUG);
				} else {
					// Generate a new invoice ID we can use for any charges.
					$invoice_id = md5 (mt_rand());
				}
			} else {
				// Generate a new invoice ID we can use for any charges.
				$invoice_id = md5 (mt_rand());
			}
			$days_due = new DateTime ();
			$days_late = new DateTime ();
			
			if ($_HOAM_setting ['budget']['assessment']['time'] == 'days') {
				$days_due -> modify ('+' . $_HOAM_setting ['budget']['assessment']['days_due'] . ' day');
				$days_late -> modify ('+' . $_HOAM_setting ['budget']['assessment']['days_late'] . ' day');
			} else {
				// We subtract one day from the month because
				// we want to make sure we calculate based
				// on the end of the month
				$days_due -> modify ('+' . $_HOAM_setting ['budget']['assessment']['months_due'] . ' month');
				$days_late -> modify ('+' . $_HOAM_setting ['budget']['assessment']['months_late'] . ' month');
			}
			// However, we always want due/late date to be the end of the month,
			// essentially date ('t'). Check if the maintenance script was last
			// run prior to the beginning of the current month
			if ($_HOAM_setting ['hoam']['last_maintenance_run'] < date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('n'), 1, date ('Y')))) {
				// If so, roll $days_due and $days_late back to the end of the
				// month they're currently on. Eg, running the script on 7/6
				// gives us 8/6 by calculation, so roll it back to 7/31
				while ($days_due -> format ('j') != 1) {
					$days_due -> modify ('-1 day');
					$days_late -> modify ('-1 day');
				}
				// OK, now we know we're at the 1st. Now, drop one more day back
				// so we're at the end of the month.
				$days_due -> modify ('-1 day');
				$days_late -> modify ('-1 day');
			}
			switch ($_HOAM_setting ['budget']['assessment']['unit']) {
				case 'single' :
					$_HOAM_log -> add ('Budget maintenance: applying single rate for all units.', HOAM_MESSAGE_DEBUG);
					// Going to run through this again so we can calculate the
					// dues rate correctly.
					switch ($_HOAM_setting ['budget']['assessment']['frequency']) {
						case 'annual' :
							$_HOAM_log -> add ('Budget maintenance: annual assessment.', HOAM_MESSAGE_DEBUG);
							$assessment_amount = $_HOAM_setting ['budget']['assessment']['amount'];
							$memo = 'Annual HOA Membership Dues for ' . date ('Y', mktime (0, 0, 0, 1, 1, date ('Y') + 1));
							break;
						case 'biannual' :
							$_HOAM_log -> add ('Budget maintenance: biannual assessment.', HOAM_MESSAGE_DEBUG);
							$assessment_amount = $_HOAM_setting ['budget']['assessment']['amount'];
							$memo = 'Biannual HOA Membership Dues for ' . date ('F', mktime (0, 0, 0, date ('n'), 1, date ('Y')));
							break;
						case 'quarterly' :
							$_HOAM_log -> add ('Budget maintenance: quarterly assessment.', HOAM_MESSAGE_DEBUG);
							$assessment_amount = $_HOAM_setting ['budget']['assessment']['amount'];
							$memo = 'Quarterly HOA Membership Dues for ' . date ('F', mktime (0, 0, 0, date ('n'), 1, date ('Y')));
							break;
						case 'monthly' :
							$_HOAM_log -> add ('Budget maintenance: monthly assessment.', HOAM_MESSAGE_DEBUG);
							$assessment_amount = $_HOAM_setting ['budget']['assessment']['amount'];
							$memo = 'Monthly HOA Membership Dues for ' . date ('F', mktime (0, 0, 0, date ('n'), 1, date ('Y')));
							break;
						default :
							// We shouldn't ever hit this.
							$_HOAM_log -> add ('Budget maintenance: assessment frequency setting "' . $_HOAM_setting ['budget']['assessment']['freqency'] . '" is unknown.', HOAM_MESSAGE_ERROR);
							break;
					}
					break;
				case 'sqft' :
					$_HOAM_log -> add ('Budget maintenance: applying variable rate based on square footage.', HOAM_MESSAGE_DEBUG);
					switch ($_HOAM_setting ['budget']['assessment']['frequency']) {
						case 'annual' :
							$_HOAM_log -> add ('Budget maintenance: annual assessment.', HOAM_MESSAGE_DEBUG);
							$assessment_amount = $_HOAM_setting ['budget']['assessment']['amount'] * $lot_class -> returnSqft ($homeowner_class -> returnLotId ($current_customer));
							$memo = 'Annual HOA Membership Dues for ' . date ('Y', mktime (0, 0, 0, 1, 1, date ('Y') + 1));
							break;
						case 'biannual' :
							$_HOAM_log -> add ('Budget maintenance: biannual assessment.', HOAM_MESSAGE_DEBUG);
							$assessment_amount = $_HOAM_setting ['budget']['assessment']['amount'] * $lot_class -> returnSqft ($homeowner_class -> returnLotId ($current_customer));
							$memo = 'Biannual HOA Membership Dues for ' . date ('F Y', mktime (0, 0, 0, date ('n'), 1, date ('Y')));
							break;
						case 'quarterly' :
							$_HOAM_log -> add ('Budget maintenance: quarterly assessment.', HOAM_MESSAGE_DEBUG);
							$assessment_amount = $_HOAM_setting ['budget']['assessment']['amount'] * $lot_class -> returnSqft ($homeowner_class -> returnLotId ($current_customer));
							$memo = 'Quarterly HOA Membership Dues for ' . date ('F Y', mktime (0, 0, 0, date ('n'), 1, date ('Y')));
							break;
						case 'monthly' :
							$_HOAM_log -> add ('Budget maintenance: monthly assessment.', HOAM_MESSAGE_DEBUG);
							$assessment_amount = $_HOAM_setting ['budget']['assessment']['amount'] * $lot_class -> returnSqft ($homeowner_class -> returnLotId ($current_customer));
							$memo = 'Monthly HOA Membership Dues for ' . date ('F Y', mktime (0, 0, 0, date ('n'), 1, date ('Y')));
							break;
						default :
						// We shouldn't ever hit this.
						$_HOAM_log -> add ('Budget maintenance: assessment frequency setting "' . $_HOAM_setting ['budget']['assessment']['freqency'] . '" is unknown.', HOAM_MESSAGE_ALERT);
						break;
					}
					break;
				default :
					// We shouldn't ever hit this.
					$_HOAM_log -> add ('Budget maintenance: assessment unit setting "' . $_HOAM_setting ['budget']['assessment']['unit'] . '" is unknown.', HOAM_MESSAGE_ALERT);
					break;
			}
			$new_entry = HOAM_budgetAddCharge (NULL,
						$assessment_amount,
						$_HOAM_setting ['budget']['category']['dues'],
						$options = array (
						'customer_id' => $current_customer,
						'date_due' => $days_due -> format ($_HOAM_country ['date']['format_mysql']),
						'date_late' => $days_late -> format ($_HOAM_country ['date']['format_mysql']),
						'invoice_id' => $invoice_id,
						'memo' => $memo,
						'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']));
			$_HOAM_log -> add ('Budget maintenance: assessment created for "' . stripslashes (html_encode ($homeowner_class -> returnName ($current_customer))) . '".', HOAM_MESSAGE_INFORMATIONAL);
			// Create the matching entry for the income account.
			HOAM_budgetAddCharge (NULL,
						$assessment_amount,
						$_HOAM_setting ['budget']['category']['dues'],
						$options = array (
						'customer_id' => $_HOAM_setting ['budget']['ids']['organization'],
						'date_due' => $days_due -> format ($_HOAM_country ['date']['format_mysql']),
						'date_late' => $days_late -> format ($_HOAM_country ['date']['format_mysql']),
						'invoice_id' => $invoice_id,
						'memo' => $memo,
						'vendor_id' => $current_customer));
			$_HOAM_log -> add ('Budget maintenance: income account entry created for "' . stripslashes (html_encode ($homeowner_class -> returnName ($current_customer))) . '".', HOAM_MESSAGE_INFORMATIONAL);
			$HTML .= '<ul><li>Assessment applied.</li></ul>';
		}
		$HTML .= '</li>';
	}

/*
 * This works, but only if the property is being rented by the month; it needs
 * to be updated to handle other time periods.
	if ($_HOAM_setting ['lot']['property']) {
		$HTML .= '<h4>Applying Rental Property Assessments</h4>';
		$_HOAM_log -> add ('Budget maintenance: checking whether there are any rental property assessments.', HOAM_MESSAGE_NOTICE);
		$homeowner_list = $homeowner_class -> listCurrentHomeowners ();		
		$property_list = $property_class -> listAll ();
		foreach ($homeowner_list as $homeowner) {
			foreach ($property_list as $property) {
				if ((int) $property ['flags'] & (int) $_HOAM_setting ['property']['flags']['rented']) {
					if ($property ['owner_id'] == $homeowner) {
						HOAM_budgetAddCharge (NULL,
											$property ['rental_amount'],
											$_HOAM_setting ['budget']['category']['property'],
											$options = array ('date_late' => date ('Y-m-t'),
											'customer_id' => $homeowner,
											'date_due' => $days_due -> format ($_HOAM_country ['date']['format_mysql']),
											'date_late' => $days_late -> format ($_HOAM_country ['date']['format_mysql']),
											'invoice_id' => $invoice_id,
											'memo' => 'Rental Fee for ' . $property ['description'],
											'vendor_id' => $property ['vendor)id']));
						$_HOAM_log -> add ('Budget maintenance: rental charge created for "' . stripslashes (html_encode ($homeowner_class -> returnName ($homeowner))) . '" for "' . $property ['description'] . '".', HOAM_MESSAGE_INFORMATIONAL);
					}
				}
			}
		}	
	}
 */
	return ($HTML);
}

function HOAM_maintenanceBudgetApplyCredits () {
	// Generate a list of owners with credits; see if they also have unpaid
	// assessments. If so, apply the credit to the assessment.
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$budget_class = new HOAM_budget ('budget');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	
	if (!empty ($lot_class -> listStreets ())) {
		// We're performing this check in case no streets have been configured.
		// This is extremely unlikely, however it will cause a MySQL error if
		// the maintenance script is set to run but no lots have been entered
		// yet (fresh install).
		$customer_list = $budget_class -> listCustomersWithOpenCharges ();
		if (is_array ($customer_list)) {
			$changes = '';
			foreach ($customer_list as $current_customer) {
				$credit_list = $budget_class -> listAvailableCreditsForCustomer ($current_customer);
				if (is_array ($credit_list)) {
					$_HOAM_log -> add ('Budget maintenance: "' . stripslashes (html_encode ($homeowner_class -> returnName ($current_customer))) . '" has a credit.', HOAM_MESSAGE_DEBUG);
					$changes .= '<li>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($current_customer)) . ' "' . stripslashes (html_encode ($homeowner_class -> returnName ($current_customer))) . '" has a credit.';
					// Are there any charges that need to be paid?
					$new_charges = $budget_class -> listCurrentChargesForCustomerWithoutFlags ($current_customer, $_HOAM_setting ['budget']['flags']['credit']);
					if (is_array ($new_charges)) {
						HOAM_budgetApplyCredits ($current_customer);
						$changes .= '<ul><li>Applied credit to assessments on account.</li></ul>';
						$_HOAM_log -> add ('Budget maintenance: applied credit to assessments on account.', HOAM_MESSAGE_DEBUG);
					} else {
						$changes .= '<ul><li>No assessments due, leaving credit in place.</li></ul>';
						$_HOAM_log -> add ('Budget maintenance: No assessments due, leaving credit in place.', HOAM_MESSAGE_DEBUG);
					}
					$changes .= '</li>';
				}
			}
		}
	}
	if (isset ($changes) AND $changes) {
		return ('<ul>' . $changes . '</ul>');
	} else {
		return ('<ul><li>' . $_HOAM_language ['errors']['generic']['no-action-necessary'] . '</li></ul>');
	}
}

function HOAM_maintenanceBudgetApplyInterestLate () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$budget_class = new HOAM_budget ('budget');
	$budget_category_class = new HOAM_budget_category ('budget_category');
	$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	
	$date = new DateTime ();
	$_HOAM_log -> add ('Budget maintenance: applying late and interest fees.', HOAM_MESSAGE_NOTICE);
	if (!empty ($lot_class -> listStreets ())) {
		// We're performing this check in case no streets have been configured.
		// This is extremely unlikely, however it will cause a MySQL error if
		// the maintenance script is set to run but no lots have been entered
		// yet (fresh install).
		$customer_list = $budget_class -> listCustomersWithOpenCharges ();
		foreach ($customer_list as $current_customer) {
			$address = HOAM_returnAddress ($homeowner_class -> returnLotId ($current_customer));
			$changes = '';
			$homeowner_name = stripslashes (html_encode ($homeowner_class -> returnName ($current_customer)));
			$open_charges = $budget_class -> listCurrentChargesForCustomer ($current_customer);
			$_HOAM_log -> add ('Budget maintenance: processing owner "' . $homeowner_name . '" ' . $address . '.', HOAM_MESSAGE_INFORMATIONAL);
			$_HOAM_log -> add ('Budget maintenance: owner has ' . count ($open_charges) . ' open (unpaid or credit) charges.', HOAM_MESSAGE_DEBUG);
			// Has the homeowner been exempted from any new charges?
			if ($homeowner_class -> isBitSet ($current_customer, $_HOAM_setting ['homeowner']['flags']['ignore_budget'])) {
				$_HOAM_log -> add ('Budget maintenance: Homeowner account is exempted from any new charges.', HOAM_MESSAGE_INFORMATIONAL);
			} elseif ($homeowner_class -> isBitSet ($current_customer, $_HOAM_setting ['homeowner']['flags']['no_fees'])) {
				$_HOAM_log -> add ('Budget maintenance: Homeowner account is set to ignore fees.', HOAM_MESSAGE_INFORMATIONAL);
			} elseif ($budget_class -> returnTotalAmountDue ($current_customer) <= 0) {
				$_HOAM_log -> add ('Budget maintenance: Homeowner doesn\'t owe anything.', HOAM_MESSAGE_INFORMATIONAL);
			} else {
				$invoice_list = $budget_invoice_class -> listInvoicesForCustomer ($current_customer, TRUE);
				if (is_array ($invoice_list)) {
					if (date ($_HOAM_country ['date']['format_mysql']) == $budget_invoice_class -> returnInvoiceDate ($invoice_list[0])) {
						$invoice_id = $invoice_list[0];
						$_HOAM_log -> add ('Budget maintenance: Appending any new charges to existing invoice already created today: ' . $invoice_id, HOAM_MESSAGE_DEBUG);		
					} else {
						// Generate a new invoice ID we can use for any charges.
						$invoice_id = md5 (mt_rand ());
						$_HOAM_log -> add ('Budget maintenance: Generated invoice ID for use if needed: ' . $invoice_id, HOAM_MESSAGE_DEBUG);		
					}
				} else {
					// Generate a new invoice ID we can use for any charges.
					$invoice_id = md5 (mt_rand ());
					$_HOAM_log -> add ('Budget maintenance: Generated invoice ID for use if needed: ' . $invoice_id, HOAM_MESSAGE_DEBUG);
				}
				if ($homeowner_class -> isBitSet ($current_customer, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags')) {
					// Don't add fees/fines if there's a payment plan
					$_HOAM_log -> add ('Budget maintenance: Homeowner has payment plan, no late or administrative fees may be applied.', HOAM_MESSAGE_INFORMATIONAL);
				} else {
					if ($budget_class -> returnTotalAmountDue ($current_customer) > 0) {
						foreach ($open_charges as $current_charge) {
							$_HOAM_log -> add ('Budget maintenance: Current entry ID: ' . $current_charge . ', Memo "' . $budget_class -> returnMemo ($current_charge) . '"', HOAM_MESSAGE_DEBUG);
							if ($budget_class -> isBitSet ($current_charge, (int) $_HOAM_setting ['budget']['flags']['credit'])) {
								$_HOAM_log -> add ('Budget maintenance: This is a credit entry, skipping.', HOAM_MESSAGE_DEBUG);
								// Skip to the next entry
							} else {
								if ($budget_class -> isBitSet ($current_charge, (int) $_HOAM_setting ['budget']['flags']['apply_fee_late'])) {
									// OK, we are allowed to apply late fees to this
									// charge.
									$_HOAM_log -> add ('Budget maintenance: Late fee may be applied, checking...', HOAM_MESSAGE_DEBUG);
									// Is it late?
									if ($budget_class -> returnDateLate ($current_charge) < date ($_HOAM_country ['date']['format_mysql'])) {
										// If the customer declared bankruptcy,
										// don't apply late fees to charges incurred
										// before the bankruptcy date.
										if ($homeowner_class -> isBitSet ($current_customer, $_HOAM_setting ['homeowner']['flags']['bankrupt'])) {
											if ($budget_class -> returnDateLate ($current_charge) > $homeowner_class -> returnDateBankrupt ($current_customer)) {
												// Check whether we should apply a
												// late fee.
												$changes .= HOAM_budgetApplyLateFee ($current_charge, $invoice_id);
											}
										} else {
											// Check whether we should apply a late fee.
											$changes .= HOAM_budgetApplyLateFee ($current_charge, $invoice_id);
										}
									} else {
										$_HOAM_log -> add ('Budget maintenance: Assessment is not late yet (Late: ' . $budget_class -> returnDateLate ($current_charge) . ', Today: ' . date ($_HOAM_country ['date']['format_mysql']) . ')', HOAM_MESSAGE_DEBUG);
									}
								}
								// Same basic check as the late fee. We only want
								// to apply the administrative fee if the assessment
								// is late.
								if ($budget_category_class -> isBitSet ($budget_class -> returnCategoryId ($current_charge), (int) $_HOAM_setting ['budget']['flags']['assessment'])) {
									// Yes, it's a dues charge.
									$_HOAM_log -> add ('Budget maintenance: This assessment is for association dues.', HOAM_MESSAGE_DEBUG);
									// Is it late?
									if ($budget_class -> returnDateLate ($current_charge) < date ($_HOAM_country ['date']['format_mysql'])) {
										// Make sure we only apply the admin fee to
										// the root, not sub-items of dues
										// assessments.
										if ($homeowner_class -> isBitSet ($current_customer, $_HOAM_setting ['homeowner']['flags']['bankrupt'])) {
											if ($budget_class -> returnDateLate ($current_charge) > $homeowner_class -> returnDateBankrupt ($current_customer)) {
												if (! $budget_class -> returnParentEntryId ($current_charge)) {
													$changes .= HOAM_budgetApplyAdministrativeFee ($current_customer, $invoice_id);
												}
											}
										} else {
											if (! $budget_class -> returnParentEntryId ($current_charge)) {
												$changes .= HOAM_budgetApplyAdministrativeFee ($current_customer, $invoice_id);
											}
										}
									} else {
										$_HOAM_log -> add ('Budget maintenance: Assessment is not late yet (Late: ' . $budget_class -> returnDateLate ($current_charge) . ', Today: ' . date ($_HOAM_country ['date']['format_mysql']) . ').', HOAM_MESSAGE_DEBUG);
									}
								} elseif ($budget_class -> returnDateLate ($current_charge) < date ($_HOAM_country ['date']['format_mysql'])) {
									$_HOAM_log -> add ('Budget maintenance: Charge is late, applying collection fee.', HOAM_MESSAGE_DEBUG);
									// We only want to apply the administration fee
									// as a top level item, not underneath a child.
									if (! $budget_class -> returnParentEntryId ($current_charge)) {
										$changes .= HOAM_budgetApplyAdministrativeFee ($current_customer, $invoice_id);
									}
								}
							}
						}
					}
				}
				// Now check for interest or other fees to be applied.
				$_HOAM_log -> add ('Budget maintenance: Checking for applicable interest fees.', HOAM_MESSAGE_DEBUG);
				foreach ($open_charges as $current_charge) {
					$_HOAM_log -> add ('Budget maintenance: Current entry ID: ' . $current_charge . ', Memo "' . $budget_class -> returnMemo ($current_charge) . '"', HOAM_MESSAGE_DEBUG);
					if ($budget_class -> isBitSet ($current_charge, (int) $_HOAM_setting ['budget']['flags']['credit'])) {
						$_HOAM_log -> add ('Budget maintenance: This is a credit entry, skipping.', HOAM_MESSAGE_DEBUG);
						// Skip to the next entry
					} elseif ($budget_class -> isBitSet ($current_charge, $_HOAM_setting ['budget']['flags']['account_frozen'])) {
						$_HOAM_log -> add ('Budget maintenance: Entry is frozen, skipping.', HOAM_MESSAGE_DEBUG);
						// Skip to the next entry
					} elseif ($budget_class -> returnDateLate ($current_charge) < date ($_HOAM_country ['date']['format_mysql'])) {
						$_HOAM_log -> add ('Budget maintenance: Entry late, checking (Due: ' . $budget_class -> returnDateDue ($current_charge) . ').', HOAM_MESSAGE_DEBUG);
						if ($budget_class -> isBitSet ($current_charge, $_HOAM_setting ['budget']['flags']['apply_interest'])) {
							$_HOAM_log -> add ('Budget maintenance: Interest may be applied, checking.', HOAM_MESSAGE_DEBUG);
							if ($homeowner_class -> isBitSet ($current_customer, $_HOAM_setting ['homeowner']['flags']['bankrupt'])) {
								if ($budget_class -> returnDateLate ($current_charge) > $homeowner_class -> returnDateBankrupt ($current_customer)) {
									if ($homeowner_class -> isBitSet ($current_customer, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags')) {
										// Check whether applying interest to owners
										// with a payment plan is enabled.
										if ($_HOAM_setting ['budget']['payment_plan']['interest']) {
											$changes .= HOAM_budgetApplyInterest ($current_charge, $current_customer, $invoice_id);
										} else {
											$_HOAM_log -> add ('Budget maintenance: Application of interest to owners with payment plans is disabled.', HOAM_MESSAGE_INFORMATIONAL);
										}
									} else {
										$changes .= HOAM_budgetApplyInterest ($current_charge, $current_customer, $invoice_id);
									}
								}
							} else {
								if ($homeowner_class -> isBitSet ($current_customer, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags')) {
									// Check whether applying interest to owners
									// with a payment plan is enabled.
									if ($_HOAM_setting ['budget']['payment_plan']['interest']) {
										$changes .= HOAM_budgetApplyInterest ($current_charge, $current_customer, $invoice_id);
									} else {
										$_HOAM_log -> add ('Budget maintenance: Application of interest to owners with payment plans is disabled.', HOAM_MESSAGE_INFORMATIONAL);
									}
								} else {
									$changes .= HOAM_budgetApplyInterest ($current_charge, $current_customer, $invoice_id);
								}
							}
						} else {
							$_HOAM_log -> add ('Budget maintenance: Interest may not be applied.', HOAM_MESSAGE_DEBUG);
						}
					} else {
						$_HOAM_log -> add ('Budget maintenance: Entry not late yet (Due: ' . $budget_class -> returnDateLate ($current_charge) . ', Today: ' . date ($_HOAM_country ['date']['format_mysql']) . ').', HOAM_MESSAGE_DEBUG);
					}
				}
			}
			$_HOAM_log -> add ('Budget maintenance: processing of homeowner "' . $homeowner_name . '" ' . $address . ' complete.', HOAM_MESSAGE_INFORMATIONAL);
			if ($changes) {
				$HTML = '<ul>';
				$HTML .= '<li>' . $address . ' "' . $homeowner_name . '" ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($current_customer)) . ' has open (unpaid or credit) charges.';
				$HTML .= '<ul>' . $changes . '</ul>';
				$HTML .= '</li>';
				$HTML .= '</ul>';
			} else {
				$HTML = '<ul><li>' . $_HOAM_language ['errors']['generic']['no-action-necessary'] . '</li></ul>';
			}
		}
	} else {
		$_HOAM_log -> add ('Budget maintenance: no action to take (applying late and interest fees.)', HOAM_MESSAGE_NOTICE);
		$HTML = '<ul><li>' . $_HOAM_language ['errors']['generic']['no-action-necessary'] . '</li></ul>';
	}
	return ($HTML);
}

function HOAM_maintenanceBudgetOwnerInvoices () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	
	$budget_class = new HOAM_budget ('budget');
	$homeowner_class = new HOAM_homeowner ('homeowners');

	require_once (HOAM_PATH_FUNCTION . 'attachments.php');
	require_once (HOAM_PATH_FUNCTION . 'budget.php');
	
	$_HOAM_log -> add ('Budget maintenance: creating owner invoices.', HOAM_MESSAGE_NOTICE);
	if ($_HOAM_setting ['hoam']['last_maintenance_run'] == date ($_HOAM_country ['date']['format_mysql'])) {
		$_HOAM_log -> add ('Budget maintenance: maintenance script has already run for today. Skipping invoice creation', HOAM_MESSAGE_NOTICE);
		$HTML = '<p>Maintenance script has already completed for today.</p>';
	} elseif (date ('j') != '1') {
		$_HOAM_log -> add ('Budget maintenance: it\'s not the first of the month. Skipping invoice creation', HOAM_MESSAGE_NOTICE);
		$HTML = '<p>Invoices are only created automatically on the first of the month.</p><p>If you need to re-send a previous invoice, please download it from the homeowner\'s account. If you need to generate a new invoice with new charges, please generate and download it from the homeowner\'s account.</p>';
	} else {
		$customer_list = $budget_class -> listCustomersWithOpenCharges ();
		if (is_array ($customer_list)) {
			$HTML = '<ul>';
			$changes = '';
			foreach ($customer_list as $current_customer) {
				// Only automatically create a new invoice if there was a new charge
				// on the homeowner's account, or it's the first of the month
				$charge = $budget_class -> returnNewestCharge ($current_customer);
				$changes .= '<li>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($current_customer)) . ' "' . stripslashes (html_encode ($homeowner_class -> returnName ($current_customer))) . '" has open (unpaid or credit) charges.';
				// Don't create new invoices when there is no balance or a
				// credit.
				if ($budget_class -> returnTotalAmountDue ($current_customer) > 0) {
					// Don't create a new invoice if one was already created
					// that has all of the current charges listed on it.
					if (TRUE) {
					}
					$changes .= '<ul>';
					$_HOAM_log -> add ('Budget maintenance: generating new invoice for ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($current_customer)) . '"' . $homeowner_class -> returnName ($current_customer) . '".', HOAM_MESSAGE_INFORMATIONAL);
					//
 					// 1) Generate the PDF
	 				// 2) Save it as an attachment for the homeowner
					// 3) Display a list of all PDF files created. 
				 	//
					// Make sure we clear these before each generation to
					// avoid problems.
					$invoice_data = NULL;
					$attachment_id = FALSE;
					$invoice_data = HOAM_budgetInvoiceGeneratePDF ($current_customer, TRUE);
					$attachment_id = HOAM_attachmentAddInternal (
						$invoice_data['filename'],
						$invoice_data['data'],
						'application/pdf',
						$_SESSION['current_user']['id'],
						$_HOAM_setting ['attachment']['flags']['homeowner'],
						'Automatically generated invoice ' . date ($_HOAM_country ['date']['format_short']),
						array ($current_customer));
					if ($attachment_id) {
						$changes .= '<li>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($current_customer)) . ' "' . stripslashes (html_encode ($homeowner_class -> returnName ($current_customer))) . '" <a href="{{WEBSITE_URL}}/hoam/scripts/attachment/view.php?id=' . $attachment_id . '">New invoice generated</a>.</li>';
					} else {
						$changes .= '<li style="color:red">Failed to generate new invoice.</li>';
						$_HOAM_log -> add ('Budget maintenance: failed to generate new invoice.', HOAM_MESSAGE_WARNING);
					}
					$changes .= '</ul>';
					$changes .= '</li>';
				}
			}
			$HTML .= $changes;
			$HTML .= '</ul>';
		} else {
			$HTML = '<ul><li>' . $_HOAM_language ['errors']['generic']['no-action-necessary'] . '</li></ul>';
		}
	}
	return ($HTML);
}

function HOAM_maintenanceBudgetVendorInvoice () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	
	$invoice_list = $budget_invoice_class -> listSimpleQuery ("SELECT id FROM budget_invoice WHERE customer_id='" . $_HOAM_setting['budget']['ids']['organization'] . "' AND datecreated>='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), date ('d') - 1, date ('Y'))) . "'");
	if (is_array ($invoice_list)) {
		$HTML = '<p>The following new invoices were entered yesterday:</p>';
		$HTML .= '<ul>';
		foreach ($invoice_list as $invoice) {
			$HTML .= '<li>Invoice #' . $budget_invoice_class -> returnNumber ($invoice) . ' from ' . stripslashes (html_encode ($budget_vendor_class -> returnName ($budget_invoice_class -> returnVendorId ($invoice)))) . '</li>';
		}
		$HTML .= '</ul>';
	} else {
		$HTML = '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}
	return ($HTML);
}

function HOAM_maintenanceBudgetEmailDue () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$budget_class = new HOAM_budget ('budget');
	$homeowner_class = new HOAM_homeowner ('homeowners');

	$owner_list = $budget_class -> listCustomersWithBalanceDue ();
	if (is_array ($owner_list)) {
		require_once (HOAM_PATH . '3rdparty/phpmailer/class.phpmailer.php');
		require_once (HOAM_PATH_FUNCTION . 'letter.php');
		$wiki = new HOAM_wiki_parser ();
		$HTML = '<ul>';
		foreach ($owner_list as $owner) {
			// We need to recreate the $mail object every time otherwise
			// addaddress appends instead of replaces, meaning that everyone on
			// $owner_list gets a copy. Found this out the hard way :(
			$mail = new PHPMailer ();
			$_HOAM_log -> add ('Budget maintenance: ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($owner)) . ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($owner))) . ') still owes.', HOAM_MESSAGE_INFORMATIONAL);
			$HTML .= '<li>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($owner)) . ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($owner))) . ')';
			if ($homeowner_class -> returnEmail ($owner)) {
				// If the owner currently has a payment plan, send them a
				// slightly different email.
				if ($homeowner_class -> isBitSet ($owner, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags')) {
					// Was the last payment received this month?
					if ($budget_class -> returnDateCreated ($budget_class -> returnLastPayment ($owner)) < date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('n'), 1, date ('Y')))) {
						$body = sprintf ($_HOAM_language ['help_pages']['email']['balance_payment_plan'],
										HOAM_returnAddress ($homeowner_class -> returnLotId ($owner)),
										html_encode (stripslashes ($homeowner_class -> returnName ($owner))),
										HOAM_returnCurrency ($budget_class -> returnTotalAmountDue ($owner)),
										'{{ORG_ADDRESS}}<br /><br />{{ORG_PHONE}}<br />{{ORG_PHONE_FAX}} ' . ucfirst ($_HOAM_language ['common']['fax']),
                                        $_HOAM_setting ['organization']['email']['management'],
                                        $_HOAM_setting ['organization']['email']['management']);
					}
				} else {
					$body = sprintf ($_HOAM_language ['help_pages']['email']['balance_due'],
									HOAM_returnAddress ($homeowner_class -> returnLotId ($owner)),
									html_encode (stripslashes ($homeowner_class -> returnName ($owner))),
									HOAM_returnCurrency ($budget_class -> returnTotalAmountDue ($owner)),
									'{{ORG_ADDRESS}}<br /><br />{{ORG_PHONE}}<br />{{ORG_PHONE_FAX}} ' . ucfirst ($_HOAM_language ['common']['fax']),
									HOAM_returnCurrency (HOAM_letterAssessmentAmount ($homeowner_class -> returnLotId ($owner))),
									strtolower ($_HOAM_language ['field_names']['budget']['assessment']['frequency_' . $_HOAM_setting['budget']['assessment']['frequency']]),
                                    $_HOAM_setting ['organization']['email']['management'],
                                    $_HOAM_setting ['organization']['email']['management']);
				}
				$mail -> IsSMTP (); // telling the class to use SMTP
				$mail -> SMTPDebug = HOAM_DEBUG; // Use the same setting as HOAM
       					            	        // 1 = errors and messages
	        	        			            // 2 = messages only
				if ($_HOAM_setting ['email']['auth']) { // enable SMTP authentication
					$mail -> SMTPAuth = TRUE;
					$mail -> Username = $wiki -> parse ($_HOAM_setting ['email']['username']);
					$mail -> Password = $_HOAM_setting ['email']['password'];
					// If we're authenticating, then we know we have a username.
					$mail -> SetFrom ($wiki -> parse ($_HOAM_setting ['email']['username']));
				} else {
					// Since we don't know that we have a username
					// configured (one would only be there if authentication
					// was previously configured and then disabled), use the
					// address of the management company.
					$mail -> SetFrom ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
				}
				$mail -> Host = $_HOAM_setting ['email']['server'];
				$mail -> Port = $_HOAM_setting ['email']['port'];
				$mail -> AddReplyTo ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
				$mail -> AddAddress ($homeowner_class -> returnEmail ($owner));
				$mail -> Subject = HOAM_returnWebsiteTitle () . ' :: ' . $_HOAM_language ['field_names']['reports']['automated_balance'];

				if ($_HOAM_setting ['email']['do_not_reply']) {
					$body .= $_HOAM_language ['help_pages']['generic']['do_not_reply'];
				}
				$mail -> MsgHTML ($wiki -> parse ($body));
				$mail -> AltBody = $_HOAM_language ['field_names']['email']['text_only'];

				if (!$mail -> Send ()) {
  					$_HOAM_log -> add ('PHPMailer Error: ' . $mail -> ErrorInfo, HOAM_MESSAGE_ERROR);
				} else {
					$_HOAM_log -> add ('Budget maintenance: email notice sent.', HOAM_MESSAGE_INFORMATIONAL);
				}
			} else {
				$_HOAM_log -> add ('Budget maintenance: no email address on file for ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($owner)) . ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($owner))) . ').', HOAM_MESSAGE_NOTICE);
				$HTML .= '<br /><span style="color: red">No Email Address on File!</span>';
			}
			$HTML .= '</li>';
		}
		$HTML .= '</ul>';
	}
	return ($HTML);
}

function HOAM_maintenanceHomeowners () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$HTML = '';
	$_HOAM_log -> add ('Beginning homeowner maintenance.', HOAM_MESSAGE_NOTICE);
	$HTML .= '<h3 style="text-decoration: underline">Homeowner Maintenance</h3>';
	$homeowner_class = new HOAM_homeowner ('homeowners');

	$HTML .= '<h4>Homeowner Changes in the Last Day</h4>';
	$homeowner_list = $homeowner_class -> listSimpleQuery ("SELECT id FROM homeowners WHERE datemodified >'" . date ($_HOAM_country ['date']['format_mysql'] . ' ' . $_HOAM_country ['time']['format_mysql'], mktime (0, 0, 0, date ('m'), date ('d') - 1, date ('Y'))) . "' ORDER BY name ASC");
	if (is_array ($homeowner_list)) {
		$HTML .= '<ul>';
		foreach ($homeowner_list as $homeowner) {
			$HTML .= '<li>';
			$HTML .= HOAM_returnAddress ($homeowner_class -> returnLotId ($homeowner)) . ' "' . stripslashes (html_encode ($homeowner_class -> returnName ($homeowner))) . '".';
			$HTML .= '</li>';
		}
		$HTML .= '</ul>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}
	
	$_HOAM_log -> add ('Homeowner maintenance complete.', HOAM_MESSAGE_NOTICE);
	return ($HTML);
}

function HOAM_maintenanceInsurance () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	require_once (HOAM_PATH . '3rdparty/phpmailer/class.phpmailer.php');
	require_once (HOAM_PATH_FUNCTION . 'insurance.php');

	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$insurance_class = new HOAM_insurance ('insurance');
	$wiki = new HOAM_wiki_parser ();
	
	$HTML = '<h3 style="text-decoration: underline">Insurance Maintenance</h3>';
	
	$HTML .= '<h4>New Insurance Policies in the Last Day</h4>';
	
	$thead = '<thead style="background: silver"><tr style="font-weight: bold">';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['insurance']['policy']['type'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['lot']['address'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['tables']['column']['budget']['vendor']['name'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['insurance']['policy']['number'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['insurance']['expiration-date'] . '</th>';
	$thead .= '</tr></thead>';
	
	$policy_list = $insurance_class -> listSimpleQuery ("SELECT id FROM insurance WHERE datecreated >'" . date ($_HOAM_country ['date']['format_mysql'] . ' ' . $_HOAM_country ['time']['format_mysql'], mktime (0, 0, 0, date ('m'), date ('d') - 1, date ('Y'))) . "' ORDER BY policy_type ASC");
	if (is_array ($policy_list)) {
		$new_table = '<table style="text-align: center; width: 100%">';
		$new_table .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$new_table .= '<tbody>';
		foreach ($policy_list as $policy) {
			$new_table .= '<tr style="color: red">';
			$new_table .= '<td>' . $_HOAM_language ['field_names']['insurance']['policy'][$insurance_class -> returnPolicyType ($policy)] . '</td>';
			$new_table .= '<td>';
			switch ($insurance_class -> returnLotId ($policy)) {
				case 'common_area':
					$new_table .= $_HOAM_language ['field_names']['lot']['common']['area'];
					break;
				case 'common_clubhouse':
					$new_table .= $_HOAM_language ['field_names']['lot']['common']['clubhouse'];
					break;
				case 'common_pool':
					$new_table .= $_HOAM_language ['field_names']['lot']['common']['pool'];
					break;
				default:
					$new_table .= HOAM_returnAddress ($insurance_class -> returnLotId ($policy));
			}
			$new_table .= '</td>';
			$new_table .= '<td>' . $budget_vendor_class -> returnName ($insurance_class -> returnVendorId ($policy)) . '</td>';
			$new_table .= '<td>' . $insurance_class -> returnPolicyNum ($policy) . '</td>';
			$new_table .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($insurance_class -> returnExpirationDate ($policy))) . '</td>';
			$new_table .= '</tr>';
		}
		$new_table .= '</tbody></table>';
		$HTML .= $new_table;
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}

	$HTML .= '<h4>The following insurance policies are expiring soon:</h4>';
	$days_expiring = new DateTime ();
	$days_expiring -> modify ('+' . $_HOAM_setting ['budget']['insurance']['days'] . ' day');
	$policy_list = $insurance_class -> listExpiringId ($days_expiring -> format ($_HOAM_country ['date']['format_mysql']));
	if (is_array ($policy_list)) {
		$HTML .= HOAM_insuranceExpiring ();
		
		// Only send an email if the feature is enabled.
		if ($_HOAM_setting ['email']['automated']) {
			$mail = new PHPMailer ();

			$body = sprintf ($_HOAM_language ['help_pages']['email']['policy_expiring'], HOAM_insuranceExpiring ());
			$mail -> IsSMTP (); // telling the class to use SMTP
			$mail -> SMTPDebug = HOAM_DEBUG; // Use the same setting as HOAM
   					            	        // 1 = errors and messages
        	        			            // 2 = messages only
			if ($_HOAM_setting ['email']['auth']) { // enable SMTP authentication
				$mail -> SMTPAuth = TRUE;
				$mail -> Username = $wiki -> parse ($_HOAM_setting ['email']['username']);
				$mail -> Password = $_HOAM_setting ['email']['password'];
				// If we're authenticating, then we know we have a username.
				$mail -> SetFrom ($wiki -> parse ($_HOAM_setting ['email']['username']));
			} else {
				// Since we don't know that we have a username
				// configured (one would only be there if authentication
				// was previously configured and then disabled), use the
				// address of the management company.
				$mail -> SetFrom ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
			}
			$mail -> Host = $_HOAM_setting ['email']['server'];
			$mail -> Port = $_HOAM_setting ['email']['port'];
			$mail -> AddReplyTo ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
			$mail -> AddAddress ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
			$mail -> Subject = HOAM_returnWebsiteTitle () . ' :: ' . $_HOAM_language ['field_names']['reports']['insurance']['expiring'];

			if ($_HOAM_setting ['email']['do_not_reply']) {
				$body .= $_HOAM_language ['help_pages']['generic']['do_not_reply'];
			}
			$mail -> MsgHTML ($wiki -> parse ($body));
			$mail -> AltBody = $_HOAM_language ['field_names']['email']['text_only'];

			if (!$mail -> Send ()) {
				$_HOAM_log -> add ('PHPMailer Error: ' . $mail -> ErrorInfo, HOAM_MESSAGE_ERROR);
			} else {
				$_HOAM_log -> add ('Budget maintenance: insurance expiring email notice sent.', HOAM_MESSAGE_INFORMATIONAL);
				$HTML .= '<ul><li>' . $_HOAM_language ['field_names']['reports']['insurance']['emailed'] . '</li></ul>';
			}
		} else {
			$HTML .= $_HOAM_language ['errors']['website']['feature_disabled'];
		}
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}

	return ($HTML);
}

function HOAM_maintenanceNews () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$news_class = new HOAM_news ('news');

	$HTML = '';
	$_HOAM_log -> add ('Beginning news maintenance.', HOAM_MESSAGE_NOTICE);
	$HTML .= '<h3 style="text-decoration: underline">News Maintenance</h3>';

	$HTML .= '<h4>News Articles in the Last Day</h4>';
	$news_list = $news_class -> listSimpleQuery ("SELECT id FROM news WHERE datecreated >'" . date ($_HOAM_country ['date']['format_mysql'] . ' ' . $_HOAM_country ['time']['format_mysql'], mktime (0, 0, 0, date ('m'), date ('d') - 1, date ('Y'))) . "'");
	if (is_array ($news_list)) {
		$HTML .= '<ul>';
		foreach ($news_list as $news) {
			$HTML .= '<li>';
			$HTML .= strip_tags ($news_class -> returnTitle ($news));
			$HTML .= ' @ ' . date ($_HOAM_country ['time']['format_short'], strtotime ($news_class -> returnDateCreated ($news)));
			$HTML .= '</li>';
		}
		$HTML .= '</ul>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}
	
	$HTML .= '<h4>Modified News Articles in the Last Day</h4>';
	$news_list = $news_class -> listSimpleQuery ("SELECT id FROM news WHERE datecreated != datemodified AND datemodified >'" . date ($_HOAM_country ['date']['format_mysql'] . ' ' . $_HOAM_country ['time']['format_mysql'], mktime (0, 0, 0, date ('m'), date ('d') - 1, date ('Y'))) . "'");
	if (is_array ($news_list)) {
		$HTML .= '<ul>';
		foreach ($news_list as $news) {
			$HTML .= '<li>';
			$HTML .= strip_tags ($news_class -> returnTitle ($news));
			$HTML .= ' @ ' . date ($_HOAM_country ['time']['format_short'], strtotime ($news_class -> returnDateModified ($news)));
			$HTML .= '</li>';
		}
		$HTML .= '</ul>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}
	
	$_HOAM_log -> add ('News maintenance complete.', HOAM_MESSAGE_NOTICE);
	return ($HTML);
}

function HOAM_maintenanceUsers () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$HTML = '';
	$_HOAM_log -> add ('Beginning user maintenance.', HOAM_MESSAGE_NOTICE);
	$HTML .= '<h3 style="text-decoration: underline">User Maintenance</h3>';
	$user_class = new HOAM_user ('users');
	$group_member_class = new HOAM_group_members ('group_members');

	$HTML .= '<h4>Users Logged in During the Last Day</h4>';
	$user_list = $user_class -> listSimpleQuery ("SELECT id FROM users WHERE datelastlogin >'" . date ($_HOAM_country ['date']['format_mysql'] . ' ' . $_HOAM_country ['time']['format_mysql'], mktime (0, 0, 0, date ('m'), date ('d') - 1, date ('Y'))) . "'");
	if (is_array ($user_list)) {
		$HTML .= '<ul>';
		foreach ($user_list as $user) {
			$HTML .= '<li>';
			$HTML .= $user_class -> returnAccountName ($user);
			$HTML .= ' @ ' . date ($_HOAM_country ['time']['format_short'], strtotime ($user_class -> returnLastLogin ($user)));
			$HTML .= '</li>';
		}
		$HTML .= '</ul>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}

	$HTML .= '<h4>New User Accounts in the Last Day</h4>';
	$user_list = $user_class -> listSimpleQuery ("SELECT id FROM users WHERE datecreated >'" . date ($_HOAM_country ['date']['format_mysql'] . ' ' . $_HOAM_country ['time']['format_mysql'], mktime (0, 0, 0, date ('m'), date ('d') - 1, date ('Y'))) . "'");
	if (is_array ($user_list)) {
		$HTML .= '<ul>';
		foreach ($user_list as $user) {
			$HTML .= '<li>';
			$HTML .= $user_class -> returnAccountName ($user);
			$HTML .= ' @ ' . date ($_HOAM_country ['time']['format_short'], strtotime ($user_class -> returnDateCreated ($user)));
			$HTML .= '</li>';
		}
		$HTML .= '</ul>';
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}

	$HTML .= '<h4>Users Requesting Validation</h4>';
	$user_list = $user_class -> listAll (NULL, 'accountname ASC');
	if (is_array ($user_list)) {
		$changes = '';
		foreach ($user_list as $user) {
			if ((int) $user['flags'] & (int) $_HOAM_setting ['user']['flags']['disabled']) {
				// Don't bother to report on accounts that have been disabled.
			} else {
				if ($user['homeowner_id']) {
					if ($group_member_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $user['id'])) {
					} else {
						// The user has a homeowner_id, but has not been added to either the resident or homeowner group yet.
						$changes .= '<li>' . $user['accountname'] . '</li>';
					}
				}
			}
		}
		if (isset ($changes)) {
			$HTML .= '<ul>' . $changes . '</ul>';
		} else {
			$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
		}
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}
	
	$HTML .= '<h4>Checking for Inactive Accounts to Disable</h4>';
	$user_list = $user_class -> listAll (NULL, 'accountname ASC');
	if (is_array ($user_list)) {
		$count = 0;
		foreach ($user_list as $user) {
			// Check to make sure the disabled setting is valid, and also make
			// sure the user we're checking hasn't already been disabled.
			if (($_HOAM_setting ['user']['flags']['disabled'] >= 14) AND
				((int) $user['flags'] & (int) $_HOAM_setting ['user']['flags']['disabled'])) {
				// Don't bother to report on accounts that have been disabled.
			} else {
				// Check if the user's account should be disabled.
				$date = new DateTime ();
				$date -> modify ('-' . $_HOAM_setting ['user']['days_before_disable'] . ' day');
				if ($user['datelastlogin'] < $date -> format ($_HOAM_country ['date']['format_mysql'])) {
					// Has the user simply created the account but never logged
					// in?
					if (($user['datelastlogin'] != NULL) AND
						($user['datecreated'] < $date -> format ($_HOAM_country ['date']['format_mysql']))) {
						$user_class -> setBit ($user['id'], $_HOAM_setting ['user']['flags']['disabled']);
						$_HOAM_log -> add ('User account disabled for inactivity: "' . $user['accountname'] . '".', HOAM_MESSAGE_NOTICE); 
						$list .= '<li>Disabled "' . $user['accountname'] . '".</li>';
						$count++;
					}
				}
			}
		}
		if ($count) {
			$HTML .= '<ul>' . $list . '</ul>';
			unset ($list);
		} else {
			$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
		}
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}
	
	$HTML .= '<h4>Checking for Inactive Accounts to Delete</h4>';
	$user_list = $user_class -> listAll (NULL, 'accountname ASC');
	if (is_array ($user_list)) {
		$count = 0;
		foreach ($user_list as $user) {
			// Only try to delete accounts that have been disabled.
			if (((int) $user['flags'] & (int) $_HOAM_setting ['user']['flags']['disabled']) AND
				// Don't delete the root or system accounts!
				($user['id'] != $_HOAM_setting ['user']['ids']['root']) AND
				($user['id'] != $_HOAM_setting ['user']['ids']['system']) AND
				($_HOAM_setting ['user']['days_before_delete'] >= 90)) {
				// We're only going to check accounts that have previously been
				// disabled.
				$date = new DateTime ();
				$date -> modify ('-' . $_HOAM_setting ['user']['days_before_delete'] . ' day');
				if ($user['datelastlogin'] < $date -> format ($_HOAM_country ['date']['format_mysql'])) {
					$user_class -> deleteId ($user['id']);
					$_HOAM_log -> add ('User account deleted for inactivity: "' . $user['accountname'] . '".', HOAM_MESSAGE_NOTICE); 
					$list .= '<li>Deleted "' . $user['accountname'] . '".</li>';
					$count++;
				}
			}
		}
		if ($count) {
			$HTML .= '<ul>' . $list . '</ul>';
			unset ($list);
		} else {
			$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
		}
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}
	
	$HTML .= '<h4>Checking for Accounts no Longer Having Current Homeowner</h4>';
	$user_list = $user_class -> listAll (NULL, 'accountname ASC');
	if (is_array ($user_list)) {
		$group_class = new HOAM_group ('group_list');
		$homeowner_class = new HOAM_homeowner ('homeowners');
		$count = 0;
		foreach ($user_list as $user) {
			if ($group_member_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $user['id'])) {
				$lot_id = $homeowner_class -> returnLotId ($user['homeowner_id']);
				$current_homeowner = $homeowner_class -> returnCurrentOwner ($lot_id);
				if ($user['homeowner_id'] != $current_homeowner) {
					$group_member_class -> removeMember ($_HOAM_setting ['group']['ids']['homeowner'], $user['id']);
					$_HOAM_log -> add ('User account "' . $user['accountname'] . '" group membership removed: ' . $group_class -> returnName ($_HOAM_setting ['group']['ids']['homeowner']), HOAM_MESSAGE_NOTICE); 
					$list .= '<li>Homeowner group membership removed: "' . $user['accountname'] . '".</li>';
					if ($group_member_class -> isMember ($_HOAM_setting ['group']['ids']['resident'], $user['id'])) {
						$group_member_class -> removeMember ($_HOAM_setting ['group']['ids']['resident'], $user['id']);
						$_HOAM_log -> add ('User account "' . $user['accountname'] . '" group membership removed: ' . $group_class -> returnName ($_HOAM_setting ['group']['ids']['resident']), HOAM_MESSAGE_NOTICE);
						$list .= '<li>Resident group membership removed: "' . $user['accountname'] . '".</li>';
					}
					$count++;
				}
			}
		}
		if ($count) {
			$HTML .= '<ul>' . $list . '</ul>';
			unset ($list);
		} else {
			$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
		}
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}
		
	$_HOAM_log -> add ('User maintenance complete.', HOAM_MESSAGE_NOTICE);
	return ($HTML);
}

function HOAM_maintenanceViolations () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$violation_class = new HOAM_violation ('violations');
	
	require (HOAM_PATH_FUNCTION . 'violations.php');

	$_HOAM_log -> add ('Beginning violation maintenance.', HOAM_MESSAGE_NOTICE);
	
	$HTML = '<h3 style="text-decoration: underline">Violation Maintenance</h3>';
	$HTML .= '<h4>New Violations Yesterday</h4>';
	$violation_list = $violation_class -> listOnViolationDate (date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), date ('d') - 1, date ('Y'))));
	if (is_array ($violation_list)) {
		$HTML .= '<ul>';
		foreach ($violation_list as $violation) {
			$HTML .= '<li>' . HOAM_returnAddress ($violation_class -> returnLotId ($violation)) . ' <a href="/admin/violation/letter/' . $violation . '/">Letter</a></li>';
		}
		$HTML .= '</ul>';		
	} else {
		$HTML .= '<ul><li>' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</li></ul>';
	}

	$HTML .= '<h4>Violations to Follow Up On</h4>';
	$HTML .= HOAM_violationShowUnresolved ();
	
	$_HOAM_log -> add ('Violation maintenance complete.', HOAM_MESSAGE_NOTICE);
	return ($HTML);
}

?>
