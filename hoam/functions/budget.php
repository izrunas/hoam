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

function HOAM_budgetAccountHistory ($customer_id) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;

	$budget_class = new HOAM_budget ('budget');
	
	$HTML = '<h2>' . $_HOAM_language ['field_names']['budget']['payment']['history'] . '</h2>';
	$colgroup = '<colgroup>';
	$colgroup .= '<col width="5%" />';
	if (HOAM_DEBUG) {
		$colgroup .= '<col width="15%" />';	
		$colgroup .= '<col span="2" width="10%" />';	
	} else {
		$colgroup .= '<col span="2" width="10%" />';	
	}
	$colgroup .= '<col width="35%" />';
	$colgroup .= '<col span="3" width="10%" char="." />';
	$colgroup .= '<col width="10%" />';
	$colgroup .= '</colgroup>';
	$thead = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['item'] . '</th>';
	if (HOAM_DEBUG) {
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['id'] . '</th>';
	}
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['date'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['check'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['memo'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['assessments'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['payments'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['balance'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['other'] . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$tfoot = preg_replace ('/thead/', 'tfoot', $thead);
	$tbody = '<tbody id="account_review_body" class="budget_body">';
	$charge_list = $budget_class -> listAllChargesForCustomer ($customer_id);
	$balance = 0;
	$item = 1;
	if (is_array ($charge_list)) {
		foreach ($charge_list as $charge) {
			$charge_info = $budget_class -> listAll ($charge);
			$charge_info = $charge_info [0];
			$tbody .= '<tr>';
			$tbody .= '<td class="center">' . $item ++ . '</td>';
			if (HOAM_DEBUG) {
				$tbody .= '<td class="center"><small>' . $charge_info ['id'] . '</small></td>';
			}
			$tbody .= '<td class="budget_date">' . date ($_HOAM_country ['date'] ['format_reports'], strtotime ($charge_info ['datecreated'])) . '</td>';
			$tbody .= '<td class="budget_check">' . $charge_info ['checknum'] . '</td>';
			$tbody .= '<td class="budget_memo">' . html_encode (stripslashes ($charge_info ['memo']));
			if ($charge_info ['parent_entry_id'] != NULL) {
				$tbody .= '<br /><span class="less-opacity small">' . $_HOAM_language ['field_names']['budget']['applied_to'] . ': ' . html_encode (stripslashes ($budget_class -> returnMemo ($charge_info ['parent_entry_id']))) . '</span>';
				if (HOAM_DEBUG) {
					$tbody .= '&nbsp; <small>(' . $charge_info ['parent_entry_id'] . ')</small>';
				}
			}
			$tbody .= '</td>';
			if ($charge_info ['amount'] >= 0) {
				$tbody .= '<td class="budget_assessment currency">' . HOAM_returnCurrency ($charge_info ['amount']) . '</td>';
				$tbody .= '<td>&nbsp;</td>';
			} else {
				$tbody .= '<td>&nbsp;</td>';
				$tbody .= '<td class="budget_payment currency">' . HOAM_returnCurrency ($charge_info ['amount']) . '</td>';
			}
			$tbody .= '<td class="budget_balance currency">' . HOAM_returnCurrency ($balance += $charge_info ['amount']) . '</td>';
			$tbody .= '<td class="budget_detail">';
			// Are there any attachments?
//			if (! empty ($charge_info ['description'])) {
//				$tbody .= '<img src="/hoam/images/icons/attach.png" title="' . $_HOAM_language ['field_names']['budget']['entry']['attachments'] . '" class="cursor_help" />';
//			}
			// Are there any notes or memos?
			if (! empty ($charge_info ['description'])) {
				$tbody .= '<img src="/hoam/images/icons/note.png" title="' . $_HOAM_language ['field_names']['budget']['entry']['notes'] . '" class="cursor_help" />';
			}
//			// Is this a scheduled transaction?
//			if (! empty ($charge_info ['description'])) {
//				$tbody .= '<img src="/hoam/images/icons/date.png" title="' . $_HOAM_language ['field_names']['budget']['entry']['scheduled'] . '" class="cursor_help" />';
//			}
			$tbody .= '</td>';
			$tbody .= '</tr>';
		}
	}
	$tbody .= '</tbody>';
	$HTML .= '<table>' . $colgroup . $thead . $tfoot . $tbody . '</table>';
	
	return ($HTML);
}

function HOAM_budgetAccountHistory2 ($customer_id) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;

	$budget_class = new HOAM_budget ('budget');
	$budget_category_class = new HOAM_budget_category ('budget_category');
	
	$HTML = '<h2>' . $_HOAM_language ['field_names']['budget']['payment']['history'] . '</h2>';
	$HTML .= '<select id="review_selected_action">';
	$HTML .= '<option selected="selected" value="0">With Selected</option>';
	$HTML .= '<option value="delete">Delete</option>';
//	$HTML .= '<option value="edit">Edit</option>';
	$HTML .= '<option value="insert">Insert Before</option>';
	$HTML .= '</select>';
	$HTML .= '<input disabled="disabled" type="button" id="perform_selected_action" value="Act" />';
	$colgroup = '<colgroup>';
	$colgroup .= '<col width="5%" />';
	$colgroup .= '<col width="10%" />';	
	$colgroup .= '<col width="45%" />';
	$colgroup .= '<col width="30%" />';
	$colgroup .= '<col width="10%" />';
	$colgroup .= '</colgroup>';
	$thead = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th><input type="checkbox" id="review_select_all" /></th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['date'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['memo'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['category'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['payments'] . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$tfoot = '<tfoot>';
	$tfoot .= '<tr>';
	$tfoot .= '<th>&nbsp;</th>';
	$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['generic']['date'] . '</th>';
	$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['budget']['memo'] . '</th>';
	$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['budget']['category'] . '</th>';
	$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['budget']['payments'] . '</th>';
	$tfoot .= '</tr>';
	$tfoot .= '</tfoot>';
	$tbody = '<tbody id="account_review_tbody" class="budget_body">';
	$charge_list = $budget_class -> listAllChargesForCustomer ($customer_id);
	$balance = 0;
	$item = 1;
	foreach ($charge_list as $charge) {
		$charge_info = $budget_class -> listAll ($charge);
		$charge_info = $charge_info [0];
		$tbody .= '<tr>';
		// Display either a checkbox or item number depending on whether the
		// page is being viewed in a browser or printed.
		$tbody .= '<td class="center">';
		$tbody .= '<span class="donotprint"><input type="checkbox" id="chk_' . $charge_info ['id'] . '" /></span>';
		$tbody .= '<span class="onlyprint">' . $item ++ . '</span>';
		$tbody .= '</td>';
		$tbody .= '<td class="budget_date">' . date ($_HOAM_country ['date'] ['format_reports'], strtotime ($charge_info ['datecreated'])) . '</td>';
		$tbody .= '<td class="budget_memo">';
		$tbody .= html_encode (stripslashes ($charge_info ['memo']));
		$tbody .= '&nbsp; <img class="donotprint less-opacity" id="img_' . $charge_info ['id'] . '" src="/hoam/images/icons/magnifier.png" alt="More Information" />';		
		$tbody .= '</td>';
		$tbody .= '<td class="center">' . html_encode (stripslashes ($budget_category_class -> returnCategory ($charge_info ['category_id']))) . '</td>';
		if ($charge_info ['amount'] >= 0) {
			$tbody .= '<td class="currency">' . HOAM_returnCurrency ($charge_info ['amount']) . '</td>';
		} else {
			$tbody .= '<td class="currency_neg">(' . HOAM_returnCurrency ($charge_info ['amount']) . ')</td>';
		}
		$tbody .= '</tr>';
	}
	$tbody .= '</tbody>';
	$HTML .= '<table>' . $colgroup . $thead . $tfoot . $tbody . '</table>';
	
	return ($HTML);
}

function HOAM_budgetAddCharge ($parent, $amount, $category, $options = NULL) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$budget_class = new HOAM_budget ('budget');
	$budget_category_class = new HOAM_budget_category ('budget_category');
	$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');

	// Don't try to do anything if the organization hasn't been set.
	if (empty ($_HOAM_setting ['budget']['ids']['organization'])) {
		$_HOAM_log -> add ($_HOAM_language ['errors']['budget']['unknown_organization'], HOAM_MESSAGE_ERROR);
		return FALSE;
	}

	// Parse any options received, and use default values otherwise.
	if (!isset ($options['account_id'])) {
		$options['account_id'] = NULL;
	}
	if (!isset ($options['customer_id'])) {
		$options['customer_id'] = $budget_class -> returnCustomerId ($parent);
	}
	if (!isset ($options['date_due'])) {
		// use today's date
		$options['date_due'] = date ($_HOAM_country ['date']['format_mysql']);
	}
	if (!isset ($options['date_late'])) {
		// use today's date
		$options['date_late'] = date ($_HOAM_country ['date']['format_mysql']);
	}
	if (!isset ($options['flags'])) {
		// Use the default flags of the category
		$options['flags'] = $budget_category_class -> returnFlags ($category);
	}
	if (!isset ($options['memo'])) {
		// Use the default name of the category
		$options['memo'] = $budget_category_class -> returnName ($category);
	}
	if (!isset ($options['vendor_id'])) {
		$options['vendor_id'] = NULL;
	}
	if (isset ($options['invoice_id'])) {
		// If the invoice_id provided doesn't exist, create a new invoice and
		// fill in the details.
		// NOTE!! This routine assumes that it's only creating invoices for
		// properties, with the Association as the vendor. This behavior should
		// not be relied on; it will likely change in the future.
		if ($budget_invoice_class -> idExists ($options['invoice_id'])) {
		} else {
			$_HOAM_log -> add ('Existing invoice with id: ' . $options['invoice_id'] . ' not found, creating.', HOAM_MESSAGE_DEBUG);
			$budget_invoice_class -> add ($_HOAM_setting ['user']['ids']['system'], $options['invoice_id']);
			$budget_invoice_class -> changeCustomerId ($options['invoice_id'], $options['customer_id']);
			$budget_invoice_class -> changeInvoiceDate ($options['invoice_id'], date ($_HOAM_country ['date']['format_mysql']));
			if (!$options['vendor_id']) {
				$options['vendor_id'] = $_HOAM_setting ['budget']['ids']['organization'];
			}
			$budget_invoice_class -> changeVendorId ($options['invoice_id'], $options['vendor_id']);
			$_HOAM_log -> add ('New budget invoice added, id: ' . $options['invoice_id'], HOAM_MESSAGE_DEBUG);
		}
	} else {
		$options['invoice_id'] = NULL;
	}
	$new_entry = $budget_class -> add ($_HOAM_setting ['user']['ids']['system']);
	if ($new_entry) {
		$budget_class -> changeAccountId ($new_entry, $options['account_id']);
		$budget_class -> changeAmount ($new_entry, $amount);
		$budget_class -> changeCategoryId ($new_entry, $category);
		$budget_class -> changeCustomerId ($new_entry, $options['customer_id']);
		$budget_class -> changeDateDue ($new_entry, $options['date_due']);
		$budget_class -> changeDateLate ($new_entry, $options['date_late']);
		$budget_class -> changeFlags ($new_entry, $options['flags']);
		$budget_class -> changeInvoiceId ($new_entry, $options['invoice_id']);
		$budget_class -> changeMemo ($new_entry, $options['memo']);
		$budget_class -> changeParentEntryId ($new_entry, $parent);
		$budget_class -> changeVendorId ($new_entry, $options['vendor_id']);
		$_HOAM_log -> add ('New budget entry added, id: ' . $new_entry, HOAM_MESSAGE_DEBUG);
		$_HOAM_log -> add ('New budget entry customer: ' . $options['customer_id'], HOAM_MESSAGE_DEBUG);
		$_HOAM_log -> add ('New budget entry date due: ' . $options['date_due'], HOAM_MESSAGE_DEBUG);
		$_HOAM_log -> add ('New budget entry date late: ' . $options['date_late'], HOAM_MESSAGE_DEBUG);
		$_HOAM_log -> add ('New budget entry invoice: ' . $options['invoice_id'], HOAM_MESSAGE_DEBUG);
		$_HOAM_log -> add ('New budget entry amount: ' . $amount, HOAM_MESSAGE_DEBUG);
		$_HOAM_log -> add ('New budget entry memo: "' . $options['memo'] . '"', HOAM_MESSAGE_DEBUG);
		return ($new_entry);
	} else {
		$_HOAM_log -> add ('New budget entry add failed.', HOAM_MESSAGE_ERROR);
		return FALSE;
	}
}

function HOAM_budgetAddPayment ($customer, $payment_amount, $options = array ()) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	$budget_class = new HOAM_budget ('budget');
	$homeowner_class = new HOAM_homeowner ('homeowners');

	// Parse any options received, and use default values otherwise.
	if (!isset ($options['account_id'])) {
		$options['account_id'] = $_HOAM_setting ['budget']['account']['expense'];
	}
	if (!isset ($options['checknum'])) {
		$options['checknum'] = NULL;
	}
	if (!isset ($options['date_due'])) {
		// use today's date
		$options['date_due'] = date ($_HOAM_country ['date']['format_mysql']);
	}
	if (!isset ($options['date_late'])) {
		// use today's date
		$options['date_late'] = date ($_HOAM_country ['date']['format_mysql']);
	}
	if (!isset ($options['description'])) {
		$options['description'] = NULL;
	}
	if (!isset ($options['flags'])) {
		// Use the default flags for a payment
		$options['flags'] = $_HOAM_setting ['budget']['flags']['payment'];
	}
	if (!isset ($options['memo'])) {
		$options['memo'] = NULL;
	}
	if (!isset ($options['vendor_id'])) {
		$options['vendor_id'] = $customer;
	}

	$total_payment_amount = $payment_amount;
// echo $total_payment_amount . '<br />';

	// The order of assessment types is set as is because of the association
	// collection policy in place when the code was written. Should it be made
	// configurable?
	$_HOAM_assessment_list = &$_HOAM_language ['budget']['assessment'];

	/*
	1) Find the oldest unpaid invoice (NOT DONE YET.... is it necessary?)
	2) Make a list of all assessment types / flags
	3) Make a list of all categories with each assessment type/flag
	4) Apply payments to all unpaid entries of each category
	5) Apply payments to any other missed entries not caught above.
	6) Apply credits for any leftover amount.				
	*/
	foreach ($_HOAM_assessment_list as $assessment_type) {
//		print_r ($assessment_type);
		if ($total_payment_amount > 0) {
			// If there's still money remaining in the payment
			$unpaid_list = $budget_class -> listCurrentChargesForCustomerWithFlags ($customer, $_HOAM_setting ['budget']['flags'][$assessment_type['value']]);
			foreach ($unpaid_list as $unpaid_charge) {
				if ($total_payment_amount > 0) {
					// If there's still money remaining in the payment. We do
					// this check twice because of the additional foreach loop
					// Is/are there payments already recorded for this entry?
					$unpaid_charge_amount = round ($budget_class -> returnAmount ($unpaid_charge), 2);
					$unpaid_charge_payment_list = $budget_class -> listAllPaymentsForCharge ($unpaid_charge);
					foreach ($unpaid_charge_payment_list as $payment) {
						// Subtract the amount of each payment from the total
						// due on the assessment.
						$unpaid_charge_amount += round ($budget_class -> returnAmount ($payment), 2);
					}
// echo $total_payment_amount . '<br />';
					$payment_entry = $budget_class -> add ($_SESSION['current_user']['id']);
					if ($payment_entry) {
						$budget_class -> changeCheckNumber ($payment_entry, $options['checknum']);
						$budget_class -> changeCustomerId ($payment_entry, $customer);
						$budget_class -> changeDateDue ($payment_entry, $options['date_due']);
						$budget_class -> changeDatePosted ($payment_entry);
						$budget_class -> changeDescription ($payment_entry, $options['description']);
						$budget_class -> changeInvoiceId ($payment_entry, $budget_class -> returnInvoiceId ($charge));
						$budget_class -> changeMemo ($payment_entry, $options['memo']);
						$budget_class -> changeDateModified ($payment_entry);
						$budget_class -> changeParentEntryId ($payment_entry, $unpaid_charge);
						$budget_class -> changeVendorId ($payment_entry, $options['vendor_id']);
						$budget_class -> setBit ($payment_entry, $options['flags']);
						if (round ($unpaid_charge_amount, 2) <= round ($total_payment_amount, 2)) {
							$budget_class -> changeAmount ($payment_entry, -round ($unpaid_charge_amount, 2));
							$budget_class -> changeDatePosted ($unpaid_charge);
							// Enough money is left to completely pay off this
							// entry mark the $unpaid_entry as paid. Remove
							// entry_amount from the amount of funds remaining.
// echo round ($unpaid_charge_amount, 2) . '<br />';
							$total_payment_amount -= round ($unpaid_charge_amount, 2);
// echo $unpaid_charge_amount . '<br />';
// echo $total_payment_amount . '<br />';
						} else {
							// Apply partial payment to charge. The only
							// differences to the above is the amount paid, and
							// leaving the unpaid_entry dateposted null (that
							// way it will be seen and inspected the next time
							// through).
							$budget_class -> changeAmount ($payment_entry, -round ($total_payment_amount, 2));
							// The entire payment amount has been spent.
							$total_payment_amount = 0;
						}
					} else {
						// Need to add something here dealing with the error.
					}
				}
			}
		}
	}
// echo $total_payment_amount . '<br />';

	// The remaining funds check needs to be rounded to cents, because php's
	// flaky math (which I suspect has to do with using floats instead of ints
	// for the amount) leaves miniscule exponents trailing
	if (round ($total_payment_amount, 2) > 0) {
		// If there is still money remaining, then the customer must have
		// overpaid. Add a new budget entry showing a credit on their account.
		$credit_entry = $budget_class -> add ($_SESSION['current_user']['id']);
		if ($credit_entry) {
			$budget_class -> changeAccountId ($credit_entry, $_HOAM_setting ['budget']['account']['expense']);
			$budget_class -> changeAmount ($credit_entry, -round ($total_payment_amount, 2));
			$budget_class -> changeCategoryId ($credit_entry, $_HOAM_setting ['budget']['category']['credit']);
			$budget_class -> changeCheckNumber ($credit_entry, $options['checknum']);
			$budget_class -> changeCustomerId ($credit_entry, $customer);
			$budget_class -> changeDateDue ($credit_entry);
			$budget_class -> changeMemo ($credit_entry, 'Credit of ' . HOAM_returnCurrency ($total_payment_amount) . ' applied for overpayment of assessments.');
			$budget_class -> changeDateModified ($credit_entry);
			$budget_class -> setBit ($credit_entry, $_HOAM_setting['budget']['flags']['credit']);
		} else {
			// Need to add something here.
		}
	}

	$total_amount_due = $budget_class -> returnTotalAmountDue ($customer);
	if ($total_amount_due == 0) {	
		// If the owner/customer has a payment plan, is their account now
		// current? There needs to be accomodation for handling funds under a
		// payment plan, and still having payments not covered. For instance, if
		// a homeowner was delinquent with their previous assessments and signed
		// a payment plan for them, then only those specified funds should be
		// considered to be under a payment plan; any new charges would not be
		// covered under the payment plan. Would this scheme be applied to funds
		// under bankrupty also?
		if ($homeowner_class -> isBitSet ($customer, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags')) {
			// Go ahead and clear the payment plan bit, and make a/ history note
			// of that fact.
			$homeowner_class -> ClearBit ($customer, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags');
			$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['homeowner']['assessment']['payment_plan']['complete'], $options['memo']),
								HOAM_MESSAGE_INFORMATIONAL,
								array ('flags' => $_HOAM_setting ['log']['flags']['homeowner'],
										'owner_id' => $customer));
		}

		// Go ahead and clear all budget flags for the homeowner. We already
		// cleared the payment plan above, but really, anything that's left is
		// unnecessary (bankruptcy, notice level, etc.)  
		$homeowner_class -> changeFlags ($customer, 0, 'budget_flags');
		
		// An additional check needs to be added such that if the total amount
		// owed is <= 0, then mark all of the line items (except credits, of
		// course) as posted.
	} elseif ($total_amount_due > 0) {
	    HOAM_budgetApplyCredits ($customer, $options);
    } else {
        // 
    }
// exit;
}

function HOAM_budgetApplyAdministrativeFee ($customer, $invoice_id = NULL) {
	global $_HOAM_log, $_HOAM_setting;

	// Don't try to do anything if the organization hasn't been set.
	if (empty ($_HOAM_setting ['budget']['ids']['organization'])) {
		$HTML = $_HOAM_language ['errors']['budget']['unknown_organization'];
		$_HOAM_log -> add ($_HOAM_language ['errors']['budget']['unknown_organization'], HOAM_MESSAGE_ERROR);
		return ($HTML);
	} else {
		$HTML = '';
	}

	$current_entry = HOAM_budgetFeeExists (NULL, $_HOAM_setting ['budget']['category']['administrative'], array ('customer_id' => $customer, 'parent_entry_id' => 'AND parent_entry_id IS NULL'));
	switch ($current_entry) {
		case 0:
			// Add a new charge for this month.
			$_HOAM_log -> add ('Budget maintenance: Applying collection / administrative fee.', HOAM_MESSAGE_INFORMATIONAL);
			$HTML .= '<li>Applying collection / administrative fee.</li>';
			HOAM_budgetAddCharge (NULL, $_HOAM_setting ['budget']['fee']['collection'], $_HOAM_setting ['budget']['category']['administrative'], array ('customer_id' => $customer, 'invoice_id' => $invoice_id, 'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']));
			// Now that we're done creating the entry for the homeowner's
			// account, we need to create our matching entry in the budget for
			// the HOA's income account.
			HOAM_budgetAddCharge (NULL,
								$_HOAM_setting ['budget']['fee']['collection'],
								$_HOAM_setting ['budget']['category']['administrative'],
								array ('account_id' => $_HOAM_setting ['budget']['account']['income'],
									'customer_id' => $_HOAM_setting ['budget']['ids']['organization'],
									'invoice_id' => $invoice_id,
									'vendor_id' => $customer));
			break;
		case 1:
			$_HOAM_log -> add ('Budget maintenance: Collection / administrative fee already applied previously.', HOAM_MESSAGE_DEBUG);
			break;
		case 3:
		case 5:
			// A payment was made, but there are still funds owed on the 
			// original charge. Add a new charge for this month.
			$_HOAM_log -> add ('Budget maintenance: Applying collection / administrative fee.', HOAM_MESSAGE_INFORMATIONAL);
			$HTML .= '<li>Applying collection / administrative fee.</li>';
			HOAM_budgetAddCharge (NULL, $_HOAM_setting ['budget']['fee']['collection'], $_HOAM_setting ['budget']['category']['administrative'], array ('customer_id' => $customer, 'invoice_id' => $invoice_id, 'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']));
			// Now that we're done creating the entry for the homeowner's
			// account, we need to create our matching entry in the budget for
			// the HOA's income account.
			HOAM_budgetAddCharge (NULL,
								$_HOAM_setting ['budget']['fee']['collection'],
								$_HOAM_setting ['budget']['category']['administrative'],
								array ('account_id' => $_HOAM_setting ['budget']['account']['income'],
									'customer_id' => $_HOAM_setting ['budget']['ids']['organization'],
									'invoice_id' => $invoice_id,
									'vendor_id' => $customer));
			break;
		default:
			break;
	}
	return ($HTML);
}

function HOAM_budgetApplyAssessmentsToday () {
	// Check the current date (ie, today) and determine whether assessments
	// should be assessed today.
	global $_HOAM_country, $_HOAM_log, $_HOAM_setting;
	
	$apply_assessments = FALSE;
	switch ($_HOAM_setting ['budget']['assessment']['frequency']) {
		case 'annual' :
			if ((date ($_HOAM_country ['date']['format_mysql']) == date ($_HOAM_country ['date']['format_mysql'], strtotime ($_HOAM_setting ['budget']['assessment']['date']))) OR
				$_HOAM_setting ['hoam']['last_maintenance_run'] < date ($_HOAM_country ['date']['format_mysql'], strtotime ($_HOAM_setting ['budget']['assessment']['date']))) {
				$_HOAM_log -> add ('Budget maintenance: annual assessments should be applied.', HOAM_MESSAGE_INFORMATIONAL);
				$apply_assessments = TRUE;
			}
			break;
		case 'biannual' :
			if (((date ('n') == 1) OR (date ('n') == 7)) AND
				((date ('j') == 1) OR (date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) > $_HOAM_setting ['hoam']['last_maintenance_run']))) {
				$_HOAM_log -> add ('Budget maintenance: biannual assessments should be applied.', HOAM_MESSAGE_INFORMATIONAL);
				$apply_assessments = TRUE;
			}
			break;
		case 'quarterly' :
			if ((((date ('n') == 1) OR (date ('n') == 4) OR (date ('n') == 7) OR (date ('n') == 10))) AND
				((date ('j') == 1) OR (date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) > $_HOAM_setting ['hoam']['last_maintenance_run']))) {
				$_HOAM_log -> add ('Budget maintenance: quarterly assessments should be applied.', HOAM_MESSAGE_INFORMATIONAL);
				$apply_assessments = TRUE;
			}
			break;
		case 'monthly' :
			if ((date ('j') == 1) OR
				(date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) > $_HOAM_setting ['hoam']['last_maintenance_run'])) {
				$_HOAM_log -> add ('Budget maintenance: monthly assessments should be applied.', HOAM_MESSAGE_INFORMATIONAL);
				$apply_assessments = TRUE;
			}
			break;
		default :
			break;
	}
	return ($apply_assessments);
}

function HOAM_budgetApplyCredits ($customer, $options = array ()) {
    // Was originally part of budgetAddPayment, but broken out to separate the
    // application of payments and the application of existing credits.
    global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

    $budget_class = new HOAM_budget ('budget');
    $homeowner_class = new HOAM_homeowner ('homeowners');

    // Parse any options received, and use default values otherwise.
    if (!isset ($options['account_id'])) {
        $options['account_id'] = $_HOAM_setting ['budget']['account']['expense'];
    }
    if (!isset ($options['checknum'])) {
        $options['checknum'] = NULL;
    }
    if (!isset ($options['date_due'])) {
        // use today's date
        $options['date_due'] = date ($_HOAM_country ['date']['format_mysql']);
    }
    if (!isset ($options['date_late'])) {
        // use today's date
        $options['date_late'] = date ($_HOAM_country ['date']['format_mysql']);
    }
    if (!isset ($options['description'])) {
        $options['description'] = NULL;
    }
    if (!isset ($options['flags'])) {
        // Use the default flags for a payment
        $options['flags'] = $_HOAM_setting ['budget']['flags']['payment'];
    }
    if (!isset ($options['memo'])) {
        $options['memo'] = NULL;
    }
    if (!isset ($options['vendor_id'])) {
        $options['vendor_id'] = $customer;
    }

    $total_payment_amount = 0;
// echo $total_payment_amount . '<br />';
    // Retrieve a list of any credits on the account and add to the payment
    // total. Usually should be just one.
    $existing_credit_list = $budget_class -> listAvailableCreditsForCustomer ($customer);
    foreach ($existing_credit_list as $credit) {
// echo $credit . '<br />';
        $total_payment_amount += -$budget_class -> returnAmount ($credit);
        // Mark the credit(s) as having been used. If the total_payment_amount
        // ends up being more than the amount owed than a new credit will be
        // created. Yes, this whole process could be a little cleaner by only
        // marking credits as used if they're needed, but I don't think it's
        // worth the extra hassle.
        $budget_class -> changeDatePosted ($credit);
        // If we don't have a checknum already, use the most recent check number
        // (which would have caused the credit to be applied) as the checknum. 
        if ($options['checknum'] == NULL) {
            $options['checknum'] = $budget_class -> returnCheckNumber ($credit);
        }
        if ($options['memo'] == NULL) {
            $options['memo'] = 'Applying credit from payment #' . $options['checknum'] . ' on ' . $budget_class -> returnDateDue ($credit) .'.';
        }
    }
// echo $total_payment_amount . '<br />';

    // The order of assessment types is set as is because of the association
    // collection policy in place when the code was written. Should it be made
    // configurable?
    $_HOAM_assessment_list = &$_HOAM_language ['budget']['assessment'];

    /*
    1) Find the oldest unpaid invoice (NOT DONE YET.... is it necessary?)
    2) Make a list of all assessment types / flags
    3) Make a list of all categories with each assessment type/flag
    4) Apply payments to all unpaid entries of each category
    5) Apply payments to any other missed entries not caught above.
    6) Apply credits for any leftover amount.               
    */
    foreach ($_HOAM_assessment_list as $assessment_type) {
//      print_r ($assessment_type);
        if ($total_payment_amount > 0) {
            // If there's still money remaining in the payment
            $unpaid_list = $budget_class -> listCurrentChargesForCustomerWithFlags ($customer, $_HOAM_setting ['budget']['flags'][$assessment_type['value']]);
            foreach ($unpaid_list as $unpaid_charge) {
                if ($total_payment_amount > 0) {
                    // If there's still money remaining in the payment. We do
                    // this check twice because of the additional foreach loop
                    // Is/are there payments already recorded for this entry?
                    $unpaid_charge_amount = round ($budget_class -> returnAmount ($unpaid_charge), 2);
                    $unpaid_charge_payment_list = $budget_class -> listAllPaymentsForCharge ($unpaid_charge);
                    foreach ($unpaid_charge_payment_list as $payment) {
                        // Subtract the amount of each payment from the total
                        // due on the assessment.
                        $unpaid_charge_amount += round ($budget_class -> returnAmount ($payment), 2);
                    }
// echo $total_payment_amount . '<br />';
                    $payment_entry = $budget_class -> add ($_SESSION['current_user']['id']);
                    if ($payment_entry) {
                        $budget_class -> changeCheckNumber ($payment_entry, $options['checknum']);
                        $budget_class -> changeCustomerId ($payment_entry, $customer);
                        $budget_class -> changeDateDue ($payment_entry, $options['date_due']);
                        $budget_class -> changeDatePosted ($payment_entry);
                        $budget_class -> changeDescription ($payment_entry, $options['description']);
                        $budget_class -> changeInvoiceId ($payment_entry, $budget_class -> returnInvoiceId ($charge));
                        $budget_class -> changeMemo ($payment_entry, $options['memo']);
                        $budget_class -> changeDateModified ($payment_entry);
                        $budget_class -> changeParentEntryId ($payment_entry, $unpaid_charge);
                        $budget_class -> changeVendorId ($payment_entry, $options['vendor_id']);
                        $budget_class -> setBit ($payment_entry, $options['flags']);
                        if (round ($unpaid_charge_amount, 2) <= round ($total_payment_amount, 2)) {
                            $budget_class -> changeAmount ($payment_entry, -round ($unpaid_charge_amount, 2));
                            $budget_class -> changeDatePosted ($unpaid_charge);
                            // Enough money is left to completely pay off this
                            // entry mark the $unpaid_entry as paid. Remove
                            // entry_amount from the amount of funds remaining.
// echo round ($unpaid_charge_amount, 2) . '<br />';
                            $total_payment_amount -= round ($unpaid_charge_amount, 2);
// echo $unpaid_charge_amount . '<br />';
// echo $total_payment_amount . '<br />';
                        } else {
                            // Apply partial payment to charge. The only
                            // differences to the above is the amount paid, and
                            // leaving the unpaid_entry dateposted null (that
                            // way it will be seen and inspected the next time
                            // through).
                            $budget_class -> changeAmount ($payment_entry, -round ($total_payment_amount, 2));
                            // The entire payment amount has been spent.
                            $total_payment_amount = 0;
                        }
                    } else {
                        // Need to add something here dealing with the error.
                    }
                }
            }
        }
    }
// echo $total_payment_amount . '<br />';

    // The remaining funds check needs to be rounded to cents, because php's
    // flaky math (which I suspect has to do with using floats instead of ints
    // for the amount) leaves miniscule exponents trailing
    if (round ($total_payment_amount, 2) > 0) {
        // If there is still money remaining, then the customer must have
        // overpaid. Add a new budget entry showing a credit on their account.
        $credit_entry = $budget_class -> add ($_SESSION['current_user']['id']);
        if ($credit_entry) {
            $budget_class -> changeAccountId ($credit_entry, $_HOAM_setting ['budget']['account']['expense']);
            $budget_class -> changeAmount ($credit_entry, -round ($total_payment_amount, 2));
            $budget_class -> changeCategoryId ($credit_entry, $_HOAM_setting ['budget']['category']['credit']);
            $budget_class -> changeCheckNumber ($credit_entry, $options['checknum']);
            $budget_class -> changeCustomerId ($credit_entry, $customer);
            $budget_class -> changeDateDue ($credit_entry);
            $budget_class -> changeMemo ($credit_entry, 'Credit of ' . HOAM_returnCurrency ($total_payment_amount) . ' applied for overpayment of assessments.');
            $budget_class -> changeDateModified ($credit_entry);
            $budget_class -> setBit ($credit_entry, $_HOAM_setting['budget']['flags']['credit']);
        } else {
            // Need to add something here.
        }
    }

    // OK, here's where we do scary bad things. So that returnTotalAmountDue
    // sums the amounts correctly, we need to zero out the amounts for the
    // credits that were just applied. Also, we're going to update the memo for
    // the credit to note that it was used on today's date.
    foreach ($existing_credit_list as $credit) {
        $budget_class -> changeAmount ($credit, 0);
        $budget_class -> changeMemo ($credit, $budget_class -> returnMemo ($credit) . ' Applied on ' . date ($_HOAM_country ['date']['format_short']) . '.');
    }

    $total_amount_due = $budget_class -> returnTotalAmountDue ($customer);
    if ($total_amount_due <= 0) {   
        // If the owner/customer has a payment plan, is their account now
        // current? There needs to be accomodation for handling funds under a
        // payment plan, and still having payments not covered. For instance, if
        // a homeowner was delinquent with their previous assessments and signed
        // a payment plan for them, then only those specified funds should be
        // considered to be under a payment plan; any new charges would not be
        // covered under the payment plan. Would this scheme be applied to funds
        // under bankrupty also?
        if ($homeowner_class -> isBitSet ($customer, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags')) {
            // Go ahead and clear the payment plan bit, and make a/ history note
            // of that fact.
            $homeowner_class -> ClearBit ($customer, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags');
            $_HOAM_log -> add (sprintf ($_HOAM_language ['log']['homeowner']['assessment']['payment_plan']['complete'], $options['memo']),
                                HOAM_MESSAGE_INFORMATIONAL,
                                array ('flags' => $_HOAM_setting ['log']['flags']['homeowner'],
                                        'owner_id' => $customer));
        }

        // Go ahead and clear all budget flags for the homeowner. We already
        // cleared the payment plan above, but really, anything that's left is
        // unnecessary (bankruptcy, notice level, etc.)  
        $homeowner_class -> changeFlags ($customer, 0, 'budget_flags');
        
        // An additional check needs to be added such that if the total amount
        // owed is <= 0, then mark all of the line items (except credits, of
        // course) as posted.
    }
// exit;
}

function HOAM_budgetApplyInterest ($entry, $current_customer, $invoice_id = NULL) {
	// With interest, we have the following goals:
	// 1) Apply it from the first day applicable (eg, the day an item was due
	//    through today)
	// 2) Ideally, only have one interest entry per month.
	// 3) On HOA dues only, don't apply interest until after the payment is
	//    "late".
	
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	
	$budget_class = new HOAM_budget ('budget');

	// Don't try to do anything if the organization hasn't been set.
	if (empty ($_HOAM_setting ['budget']['ids']['organization'])) {
		$HTML = $_HOAM_language ['errors']['budget']['unknown_organization'];
		$_HOAM_log -> add ($_HOAM_language ['errors']['budget']['unknown_organization'], HOAM_MESSAGE_ERROR);
		return ($HTML);
	} else {
		$HTML = '';
	}

	// Currently using the default interest rate, although the system is capable
	// of storing and using different interest rates on individual charges. Just
	// haven't really spent the time (or had a need) to support anything other
	// than the default yet.
	$interest_rate = (float) $_HOAM_setting ['budget']['interest_rate'] / 100;
	$total_payment = 0;
	$payment_list = $budget_class -> listAllPaymentsForCharge ($entry);
	if (is_array ($payment_list)) {
		foreach ($payment_list as $payment) {
			$total_payment += $budget_class -> returnAmount ($payment);
		}
	}
	$remaining_charge = $budget_class -> returnAmount ($entry) + $total_payment;
	$days_delinquent = (int) ((time () - strtotime ($budget_class -> returnDateDue ($entry))) / 86400);
	$_HOAM_log -> add ('Budget maintenance: Entry is ' . $days_delinquent . ' days past due.', HOAM_MESSAGE_DEBUG);
	// Find out if there's an existing interest entry yet
	$interest_entry = HOAM_budgetFeeExists ($entry, $_HOAM_setting ['budget']['category']['interest'], array ('return_id' => TRUE));
	$_HOAM_log -> add ('Budget maintenance: HOAM_budgetFeeExists return value: ' . $interest_entry[0] . '.', HOAM_MESSAGE_DEBUG);
	if ($interest_entry [1]) {
		$_HOAM_log -> add ('Budget maintenance: Existing interest entry exists: ' . $budget_class -> returnMemo ($interest_entry[1]) . '.', HOAM_MESSAGE_DEBUG);
	}
	switch ($interest_entry[0]) {
		case 0:
			// No previous interest entry. Create one.
			//
			// Calculate the interest payment. Use the default interest rate,
			// how long has it been since the due date, and multiply the amount
			// of the original entry.
			// We're going to go ahead and format the amount now, rather than
			// keeping all the fractions of a penny. Technically we might lose
			// out on a penny here and there, but when you add up the invoice
			// items manually, it comes out the same.
			//
			// Check whether it's a leap year.
			if (date ('L')) {
				$interest_amount = number_format ($remaining_charge * $interest_rate * ($days_delinquent / 366), 2);
			} else {
				$interest_amount = number_format ($remaining_charge * $interest_rate * ($days_delinquent / 365), 2);
			}
			$_HOAM_log -> add ('Budget maintenance: Applying interest: ' . $interest_amount . '.', HOAM_MESSAGE_INFORMATIONAL);
			$HTML .= '<li>Applying new interest fee: ' . $interest_amount . '</li>';
			HOAM_budgetAddCharge ($entry, $interest_amount, $_HOAM_setting ['budget']['category']['interest'], $options = array ('date_late' => date ('Y-m-t'), 'invoice_id' => $invoice_id, 'memo' => 'Accumulated interest from ' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_class -> returnDateDue ($entry))) . ' through ' . date ($_HOAM_country ['date']['format_short']) . '.', 'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']));
			// Now that we're done creating the entry for the homeowner's
			// account, we need to create our matching entry in the budget for
			// the HOA's income account.
			HOAM_budgetAddCharge (NULL,
								$interest_amount,
								$_HOAM_setting ['budget']['category']['interest'],
								array ('account_id' => $_HOAM_setting ['budget']['account']['income'],
									'customer_id' => $_HOAM_setting ['budget']['ids']['organization'],
									'invoice_id' => $invoice_id,
									'memo' => 'Accumulated interest from ' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_class -> returnDateDue ($entry))) . ' through ' . date ($_HOAM_country ['date']['format_short']) . '.', 
									'vendor_id' => $current_customer));
			break;
		case 1:
			// Update the existing entry
			$interest_entry = $interest_entry[1];
			$new_memo = 'Accumulated interest from ' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_class -> returnDateDue ($entry))) . ' through ' . date ($_HOAM_country ['date']['format_short']) . '.'; 
			$old_amount = $budget_class -> returnAmount ($interest_entry);
			$interest_amount = number_format ($remaining_charge * $interest_rate * ($days_delinquent / 365), 2) + $old_amount;
			$_HOAM_log -> add ('Budget maintenance: Updating existing interest entry.', HOAM_MESSAGE_INFORMATIONAL);
			$HTML .= '<li>Updating existing interest entry.';
			$HTML .= '<ul style="list-style: none">';
			$HTML .= '<li><small><q>' . $new_memo . '</q></small></li>';
			$HTML .= '<li><small>Old amount: ' . HOAM_returnCurrency ($old_amount) . ' New amount: ' . HOAM_returnCurrency ($interest_amount) . '</small></li>';
			$HTML .= '</ul>';
			$HTML .= '</li>';
			$budget_class -> changeMemo ($interest_entry, $new_memo);
			// Bit of a pickle. If the customer paid their total amount owed on
			// the charge, but not enough to cover the interest, then this will
			// end up overwriting the $interest_amount to $0 since the
			// $remaining_charge amount above will be 0.
			$budget_class -> changeAmount ($interest_entry, $interest_amount);
			$budget_class -> changeDateDue ($interest_entry);
			$budget_class -> changeDateLate ($interest_entry, date ('Y-m-t'));
			$budget_class -> changeDateModified ($interest_entry);
			break;
		case 2:
			// Last month's entry was paid, but there's still remaining funds
			// owed on the original charge. Add a new entry for the remaining
			// amount.
			$days_delinquent = (int) (time () - strtotime ($budget_class -> returnDatePosted ($interest_entry[1]))) / 86400;
			$interest_amount = number_format ($remaining_charge * $interest_rate * ($days_delinquent / 365), 2);
			$_HOAM_log -> add ('Budget maintenance: Last month\'s entry was paid, but there\'s still remaining funds owed on the original charge. Adding a new entry for the remaining amount.', HOAM_MESSAGE_INFORMATIONAL);
			$HTML .= '<li>Last month\'s entry was paid, but there\'s still remaining funds owed on the original charge. Adding a new entry for the remaining amount.</li>';
			HOAM_budgetAddCharge ($entry, $interest_amount, $_HOAM_setting ['budget']['category']['interest'], $options = array ('date_late' => date ('Y-m-t'), 'invoice_id' => $invoice_id, 'memo' => 'Accumulated interest from ' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_class -> returnDatePosted ($interest_entry[1]))) . ' through ' . date ($_HOAM_country ['date']['format_short']) . '.', 'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']));
			// Now that we're done creating the entry for the homeowner's
			// account, we need to create our matching entry in the budget for
			// the HOA's income account.
			HOAM_budgetAddCharge (NULL,
								$interest_amount,
								$_HOAM_setting ['budget']['category']['interest'],
								array ('account_id' => $_HOAM_setting ['budget']['account']['income'],
									'customer_id' => $_HOAM_setting ['budget']['ids']['organization'],
									'invoice_id' => $invoice_id,
									'memo' => 'Accumulated interest from ' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_class -> returnDatePosted ($interest_entry[1]))) . ' through ' . date ($_HOAM_country ['date']['format_short']) . '.', 
									'vendor_id' => $current_customer));
			break;
		case 3:
			// Last month's interest wasn't paid. Create a new entry for this
			// month.
			$_HOAM_log -> add ('Budget maintenance: Last month\'s interest not paid yet, adding new.', HOAM_MESSAGE_INFORMATIONAL);
			$HTML .= '<li>Last month\'s interest not paid yet, adding new.</li>';
			$days_delinquent = (int) (time () - strtotime ($budget_class -> returnDateDue ($interest_entry[1]))) / 86400;
			$interest_amount = number_format ($remaining_charge * $interest_rate * ($days_delinquent / 365), 2);
			HOAM_budgetAddCharge ($entry, $interest_amount, $_HOAM_setting ['budget']['category']['interest'], $options = array ('date_late' => date ('Y-m-t'), 'invoice_id' => $invoice_id, 'memo' => 'Accumulated interest from ' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_class -> returnDateDue ($interest_entry[1]))) . ' through ' . date ($_HOAM_country ['date']['format_short']) . '.', 'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']));
			// Now that we're done creating the entry for the homeowner's
			// account, we need to create our matching entry in the budget for
			// the HOA's income account.
			HOAM_budgetAddCharge (NULL,
								$interest_amount,
								$_HOAM_setting ['budget']['category']['interest'],
								array ('account_id' => $_HOAM_setting ['budget']['account']['income'],
									'customer_id' => $_HOAM_setting ['budget']['ids']['organization'],
									'invoice_id' => $invoice_id,
									'memo' => 'Accumulated interest from ' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_class -> returnDateDue ($interest_entry[1]))) . ' through ' . date ($_HOAM_country ['date']['format_short']) . '.', 
									'vendor_id' => $current_customer));
			break;
		case 4:
			// If it's not due or late yet, leave the existing entry alone
			break;
		case 5:
			// Last month's entry was paid, but there's still remaining funds
			// owed on the original charge. Add a new entry for the remaining
			// amount.
			$_HOAM_log -> add ('Budget maintenance: Last month\'s entry was paid, but there\'s still remaining funds owed on the original charge. Adding new entry for the remaining amount.', HOAM_MESSAGE_INFORMATIONAL);
			$HTML .= '<li>Last month\'s entry was paid, but there\'s still remaining funds owed on the original charge. Adding new entry for the remaining amount.</li>';
			$days_delinquent = (int) (time () - strtotime ($budget_class -> returnDatePosted ($interest_entry[1]))) / 86400;
			$interest_amount = number_format ($remaining_charge * $interest_rate * ($days_delinquent / 365), 2);
			HOAM_budgetAddCharge ($entry, $interest_amount, $_HOAM_setting ['budget']['category']['interest'], $options = array ('date_late' => date ('Y-m-t'), 'invoice_id' => $invoice_id, 'memo' => 'Accumulated interest from ' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_class -> returnDatePosted ($interest_entry[1]))) . ' through ' . date ($_HOAM_country ['date']['format_short']) . '.', 'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']));
			// Now that we're done creating the entry for the homeowner's
			// account, we need to create our matching entry in the budget for
			// the HOA's income account.
			HOAM_budgetAddCharge (NULL,
								$interest_amount,
								$_HOAM_setting ['budget']['category']['interest'],
								array ('account_id' => $_HOAM_setting ['budget']['account']['income'],
									'customer_id' => $_HOAM_setting ['budget']['ids']['organization'],
									'invoice_id' => $invoice_id,
									'memo' => 'Accumulated interest from ' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_class -> returnDatePosted ($interest_entry[1]))) . ' through ' . date ($_HOAM_country ['date']['format_short']) . '.', 
									'vendor_id' => $current_customer));
			break;
		case 6:
			// If it's not due or late yet, leave the existing entry alone
			break;
	}
	return ($HTML);
}	

function HOAM_budgetApplyLateFee ($current_charge, $invoice_id = NULL) {
	global $_HOAM_log, $_HOAM_setting;
	
	$budget_class = new HOAM_budget ('budget');
	
	// Don't try to do anything if the organization hasn't been set.
	if (empty ($_HOAM_setting ['budget']['ids']['organization'])) {
		$HTML = $_HOAM_language ['errors']['budget']['unknown_organization'];
		$_HOAM_log -> add ($_HOAM_language ['errors']['budget']['unknown_organization'], HOAM_MESSAGE_ERROR);
		return ($HTML);
	} else {
		$HTML = '';
	}
	
	// Has a late fee already been applied?
	$current_entry = HOAM_budgetFeeExists ($current_charge, $_HOAM_setting ['budget']['category']['late']);
	switch ($current_entry) {
		case 0:
			$_HOAM_log -> add ('Budget maintenance: Applying Late Fee.', HOAM_MESSAGE_INFORMATIONAL);
			$HTML .= '<li>Applying late fee.</li>';
			HOAM_budgetAddCharge ($current_charge, $_HOAM_setting ['budget']['fee']['late'], $_HOAM_setting ['budget']['category']['late'], $options = array ('invoice_id' => $invoice_id, 'vendor_id' => $_HOAM_setting ['budget']['ids']['organization']));
			// Now that we're done creating the entry for the homeowner's
			// account, we need to create our matching entry in the budget for
			// the HOA's income account.
			HOAM_budgetAddCharge (NULL,
								$_HOAM_setting ['budget']['fee']['late'],
								$_HOAM_setting ['budget']['category']['late'],
								array ('account_id' => $_HOAM_setting ['budget']['account']['income'],
									'customer_id' => $_HOAM_setting ['budget']['ids']['organization'],
									'invoice_id' => $invoice_id,
									'vendor_id' => $budget_class -> returnCustomerId ($current_charge)));
			break;
		case 1:
			$_HOAM_log -> add ('Budget maintenance: Late fee already applied previously.', HOAM_MESSAGE_DEBUG);
			break;
		default:
			break;
	}
	return ($HTML);
}

function HOAM_budgetEscalateAccounts () {
	// Determine whether the customer is late and if we need to move the
	// escalation up a notch. However, we only want to do this on the first of
	// the month or if the last maintenance run was before the beginning of the
	// month.
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
			$HTML = '<ul>';
			foreach ($customer_list as $current_customer) {
				$_HOAM_log -> add ('Budget maintenance: processing homeowner "' . stripslashes (html_encode ($homeowner_class -> returnName ($current_customer))) . '".', HOAM_MESSAGE_INFORMATIONAL);
				$HTML .= '<li>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($current_customer)) . ' "' . stripslashes (html_encode ($homeowner_class -> returnName ($current_customer))) . '" has open (unpaid or credit) charges.<ul>';
				if ($budget_class -> returnTotalAmountDue ($current_customer) > 0) {
					// NOTE! This check works because right now we're always adding
					// a charge every month for late fees, interest, etc. If that's
					// not done, if the script is run more than once per day it will
					// quickly ramp up the notice level.
					$query = "SELECT datelate FROM budget WHERE customer_id='$current_customer' AND dateposted IS NULL ORDER BY datelate ASC LIMIT 1";
					$datelate = $budget_class -> itemQuery ($query);
					$_HOAM_log -> add ('Budget maintenance: checking for notice escalation.', HOAM_MESSAGE_DEBUG);
					if (!empty ($datelate) AND ($datelate < date ($_HOAM_country ['date']['format_mysql']))) {
						$_HOAM_log -> add ('Budget maintenance: at least one payment is late.', HOAM_MESSAGE_DEBUG);
						$budget_flags = $homeowner_class -> returnFlags ($current_customer, 'budget_flags');
						// Yes, we could get by without a switch here, but it was
						// done this way in case we decide to add other checks like
						// in the case of bankruptcy, we don't want to escalate on
						// older amounts that are covered under the bankruptcy.
						switch (TRUE) {
							// Is the owner under a payment plan?
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan']) :
									// If we haven't received a payment from the owner
								// in at least 2 months, set them defaulted.
								$query = "SELECT dateposted FROM budget WHERE customer_id='$current_customer' AND
										flags & " . $_HOAM_setting['budget']['flags']['payment'] . "
										ORDER BY dateposted DESC LIMIT 1";
								$lastpaid = $budget_class -> itemQuery ($query);
								$date = new DateTime ();
								$date -> modify ('-2 month');
								if ($lastpaid < $date -> format ($_HOAM_country ['date']['format_mysql'])) {
									$_HOAM_log -> add ('Budget maintenance: it\'s been at least two months since the last payment, marking payment plan in default.', HOAM_MESSAGE_INFORMATIONAL);
									$HTML .= '<li>' . $_HOAM_language ['field_names']['budget']['flags']['payment_plan_default'] . '.</li>';
									$homeowner_class -> clearBit ($current_customer, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags');
									$homeowner_class -> setBit ($current_customer, $_HOAM_setting ['budget']['flags']['payment_plan_default'], 'budget_flags');
									$homeowner_class -> changePaymentPlanDate ($current_customer, date ($_HOAM_country ['date']['format_mysql']));
									$homeowner_class -> changeDateModified ($current_customer, date ($_HOAM_country ['date']['format_mysql']));
								} else {
									$_HOAM_log -> add ('Budget maintenance: it hasn\'t yet been two months since the last payment, leaving payment plan in effect.', HOAM_MESSAGE_INFORMATIONAL);
								}
								break;
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan_default']) :
								// If the payment plan defaulted over 30 days ago,
								// the move to the next appropriate collection level
								// (eg, don't send to the attorney if they never
								// were 'delinquent')
								break;
						}
						// Only try to escalate if a payment plan is not in effect.
						switch (!((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan'])) {
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney']) :
								$_HOAM_log -> add ('Budget maintenance: owner already with attorney, cannot escalate further.', HOAM_MESSAGE_DEBUG);
								$HTML .= '<li>Cannot escalate past ' . $_HOAM_language ['field_names']['budget']['flags']['late_attorney'] . '.</li>';
								// Can't escalate past here.
								break;
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_default']) :
								$_HOAM_log -> add ('Budget maintenance: escalating account to 4th notice (attorney).', HOAM_MESSAGE_INFORMATIONAL);
								$HTML .= '<li>Escalating to ' . $_HOAM_language ['field_names']['budget']['flags']['late_attorney'] . '.</li>';
								$homeowner_class -> setBit ($current_customer, $_HOAM_setting ['budget']['flags']['late_attorney'], 'budget_flags');
								$homeowner_class -> changeDateModified ($current_customer, date ($_HOAM_country ['date']['format_mysql']));
								break;
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_delinquent']) :
								$_HOAM_log -> add ('Budget maintenance: escalating account to 3rd notice (default).', HOAM_MESSAGE_INFORMATIONAL);
								$HTML .= '<li>Escalating to ' . $_HOAM_language ['field_names']['budget']['flags']['late_default'] . '.</li>';
								$homeowner_class -> setBit ($current_customer, $_HOAM_setting ['budget']['flags']['late_default'], 'budget_flags');
								$homeowner_class -> changeDateModified ($current_customer, date ($_HOAM_country ['date']['format_mysql']));
								break;
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_notice']) :
								$_HOAM_log -> add ('Budget maintenance: escalating account to 2nd notice (delinquent).', HOAM_MESSAGE_INFORMATIONAL);
								$HTML .= '<li>Escalating to ' . $_HOAM_language ['field_names']['budget']['flags']['late_delinquent'] . '.</li>';
								$homeowner_class -> setBit ($current_customer, $_HOAM_setting ['budget']['flags']['late_delinquent'], 'budget_flags');
								$homeowner_class -> changeDateModified ($current_customer, date ($_HOAM_country ['date']['format_mysql']));
								break;
							default:
								$_HOAM_log -> add ('Budget maintenance: escalating account to 1st notice.', HOAM_MESSAGE_INFORMATIONAL);
								$HTML .= '<li>Escalating to ' . $_HOAM_language ['field_names']['budget']['flags']['late_notice'] . '.</li>';
								$homeowner_class -> setBit ($current_customer, $_HOAM_setting ['budget']['flags']['late_notice'], 'budget_flags');
								$homeowner_class -> changeDateModified ($current_customer, date ($_HOAM_country ['date']['format_mysql']));
								break;
						}
					}
				} else {
					$_HOAM_log -> add ('Budget maintenance: owner doesn\'t owe anything (open charges are credits on the account).', HOAM_MESSAGE_INFORMATIONAL);
					$HTML .= '<li>Owner doesn\'t owe anything.</li>';
				}
				$HTML .= '</ul></li>';
			}
			$HTML .= '</ul>';
			return ($HTML);
		}
	}
}

function HOAM_budgetFeeExists ($entry, $category, $options = NULL) {
	global $_HOAM_country, $_HOAM_language;
	
	$budget_class = new HOAM_budget ('budget');
	
	$HTML = '';
	if (!isset ($options['customer_id'])) {
		$options['customer_id'] = $budget_class -> returnCustomerId ($entry);
	}
	if (!isset ($options['return_id'])) {
		$options['return_id'] = FALSE;
	}
	if (!isset ($options['parent_entry_id'])) {
		$options['parent_entry_id'] = "AND parent_entry_id='$entry'";
	}
	$query = "SELECT id FROM budget WHERE customer_id='" . $options['customer_id'] . "' AND
				category_id='$category' " . $options['parent_entry_id'] . " ORDER BY datecreated DESC";
	$last_fee = $budget_class -> itemQuery ($query);
	if ($last_fee) {
		if (HOAM_DEBUG) {
			$HTML .= '<br />Previous fee entry ID: ' . $last_fee;
		}
		$posted = $budget_class -> returnDatePosted ($last_fee);
		$created = $budget_class -> returnDateCreated ($last_fee);
		$day_of_month = date ('j');
		$days_since_last_charge = round ((time () - strtotime ($created)) / 86400);
		$duedate = $budget_class -> returnDateDue ($last_fee);
		$latedate = $budget_class -> returnDateLate ($last_fee);
		if ($latedate > date ($_HOAM_country ['date']['format_mysql'])) {
			// Entry isn't late yet
			if (HOAM_DEBUG) {
				$HTML .= '<br />Entry is not late yet (Late: ' . $latedate . ', Today: ' . date ($_HOAM_country ['date']['format_mysql']) . ').';
			}
			$return_value = 6;
		} elseif ($duedate > date ($_HOAM_country ['date']['format_mysql'])) {
			// Entry isn't due yet
			if (HOAM_DEBUG) {
				$HTML .= '<br />Entry is not due yet (Due : ' . $duedate . ', Today: ' . date ($_HOAM_country ['date']['format_mysql']) . ').';
			}
			$return_value = 4;
		} elseif ($days_since_last_charge <= $day_of_month) {
			// Was this charge created during this month?
			$return_value = 1;
		} elseif ($posted) {
			if ($posted < date ($_HOAM_country ['date']['format_mysql'])) {
				$HTML .= '<li>The previous entry has not been paid off completely.</li>';
				$return_value = 5;
			} else {
				// It's already been paid
				$HTML .= '<li>The previous entry has been paid off.</li>';
				$return_value = 2;
			}
		} else {
			// There's a current entry, that has not been paid yet
			$return_value = 3;
		}
	} else {
		// No previous entry exists
		$HTML .= '<li>No previous fee exists.</li>';
		$return_value = 0;
	}
	if ($options['return_id']) {
		if (isset ($last_fee)) {
			return (array ($return_value, $last_fee));
		} else {
			return (array ($return_value, -1));
		}
	} else {
		return $return_value;
	}
	return ($HTML);
}

function HOAM_budgetFindCurrentBudget ($date = NULL) {
	// OK, we want to somehow programmatically locate the current budget for
	// this fiscal year (except HOAM currently doesn't have any concept of
	// fiscal years, it just uses calendar). We're going to do this by finding:
	// 
	// #1 The most recent budget that the provided date falls within, and
	// #2 Contains the word 'annual' or the numeric year.
	// 
	// If we don't find a budget meeting both conditions, return false.
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	
	$budget_class = new HOAM_budget_budget ('budget_budget');

	// Force a recognizable date	
	if ($date === NULL) {
		$date = date ($_HOAM_country ['date']['format_mysql']);
	} else {
		$date = date ($_HOAM_country ['date']['format_mysql'], strtotime ($date));
	}
	
	$budget_list = $budget_class -> listAll ();
	if (is_array ($budget_list)) {
		foreach ($budget_list as $budget) {
			if (($date >= $budget['datestart']) AND ($date <= $budget['dateend'])) {
				if (mb_stristr ($budget['name'], $_HOAM_language ['common']['annual']) OR mb_stristr ($budget['name'], date ('Y', strtotime ($date)))) {
					$_HOAM_log -> add (__FUNCTION__ . ' Found budget.', HOAM_MESSAGE_DEBUG);
					return ($budget['id']);
				}
			} 
		}
	} else {
		$_HOAM_log -> add (__FUNCTION__ . ' No budgets found in system.', HOAM_MESSAGE_DEBUG);
		return FALSE;
	}
	return FALSE;
}

function HOAM_budgetIncomeStatement ($budget_id, $date_start, $date_end) {
	// See https://en.wikipedia.org/wiki/Income_statement
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;
	require_once (HOAM_PATH_FUNCTION . 'image.php');
	
	$budget_class = new HOAM_budget ('budget');
	$budget_budget_class = new HOAM_budget_budget ('budget_budget');
	$budget_category_class = new HOAM_budget_category ('budget_category');
	$budget_track_class = new HOAM_budget_track ('budget_track');

	$table = '<table id="budget_detail" style="page-break-inside: avoid; text-align: right">';

	if ((date ($_HOAM_country ['date']['format_mysql'], strtotime ($date_start)) >= $budget_budget_class -> returnDateStart ($budget_id)) AND
		(date ($_HOAM_country ['date']['format_mysql'], strtotime ($date_end)) <= $budget_budget_class -> returnDateEnd ($budget_id))) {

		$caption = '<div style="text-align: center">Income Statement for ' . $_HOAM_setting ['organization']['name'] . '<br />';
		$caption .= date ($_HOAM_country ['date']['format_reports'], strtotime ($date_start)) . ' through ' . date ($_HOAM_country ['date']['format_reports'], strtotime ($date_end)) . '</div>';
	
		$colgroup = '<colgroup>';
		$colgroup .= '<col />';
		$colgroup .= '<col span="2" width="15%" />';
		$colgroup .= '</colgroup>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<td>' . '</td>';
		$thead .= '<td>' . $_HOAM_language ['tables']['column']['budget']['debit'] . '</td>';
		$thead .= '<td>' . $_HOAM_language ['tables']['column']['budget']['credit'] . '</td>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$tbody = '<tbody>';
		$tbody .= '<tr>';
		$tbody .= '<td style="font-weight: bold; text-align: left">' . $_HOAM_language ['tables']['column']['budget']['revenues'] . '</td>';
		$tbody .= '<td colspan="2">&nbsp;</td>';
		$tbody .= '</tr>';
		
		$category_parent_child_list = $budget_category_class -> listCategoryAll ();
		$current_period_expense_amount = 0;
		$current_period_income_amount = 0;
		
		$income_data = '';
		$expense_data = '';
		foreach ($category_parent_child_list as $category) {
			// Is this a parent category?
			if ((int) $budget_category_class -> returnFlags ($category[0]) & (int) $_HOAM_setting ['budget']['flags']['category_income']) {
				if ($category[1] == 0) {
					$display_parent = FALSE;
					$parent_category = $budget_category_class -> returnName ($category[0]);
				} else {
					$id = $budget_track_class -> returnIdForCategory ($category[0], $budget_id);
					$query = "SELECT SUM(amount) FROM budget WHERE customer_id='" . $_HOAM_setting ['budget']['ids']['organization'] . "' AND category_id = '" . $category [0] . "' AND amount > 0 AND datedue >= '" . $date_start . "' AND datedue <= '" . $date_end . "'";
					$income_amount = $budget_class -> itemQuery ($query);
					if ($income_amount > 0) {
						if ($display_parent) {
						} else {
							$display_parent = TRUE;
							$income_data .= '<tr><td colspan="3" style="padding-left: 2em; text-align: left">' . $parent_category . '</td></tr>';
						}
						$current_period_income_amount += $income_amount;
						$income_data .= '<tr>';
						$income_data .= '<td style="padding-left: 5em; text-align: left">' . $budget_category_class -> returnName ($category[0]) . '</td>';
						$income_data .= '<td>&nbsp;</td>';
						$income_data .= '<td>' . number_format ($income_amount, 2) . '</td>';
						$income_data .= '</tr>';
					}
				}
			} else {
				if ($category[1] == 0) {
					$display_parent = FALSE;
					$parent_category = $budget_category_class -> returnName ($category[0]);
				} else {
					$id = $budget_track_class -> returnIdForCategory ($category[0], $budget_id);
					$query = "SELECT SUM(amount) FROM budget WHERE customer_id='" . $_HOAM_setting ['budget']['ids']['organization'] . "' AND category_id = '" . $category [0] . "' AND amount > 0 AND datedue >= '" . $date_start . "' AND datedue <= '" . $date_end . "'";
					$expense_amount = $budget_class -> itemQuery ($query);
					if ($expense_amount > 0) {
						if ($display_parent) {
						} else {
							$display_parent = TRUE;
							$expense_data .= '<tr><td colspan="3" style="padding-left: 2em; text-align: left">' . $parent_category . '</td></tr>';
						}
						$current_period_expense_amount += $expense_amount;
						$expense_data .= '<tr>';
						$expense_data .= '<td style="padding-left: 5em; text-align: left">' . $budget_category_class -> returnName ($category[0]) . '</td>';
						$expense_data .= '<td>' . number_format ($expense_amount, 2) . '</td>';
						$expense_data .= '<td>&nbsp;</td>';
						$expense_data .= '</tr>';
					}
				}
			}
		}
		$tbody .= $income_data;
		$tbody .= '</tbody>';
		$tbody .= '<tbody>';
		$tbody .= '<tr>';
		$tbody .= '<td style="font-weight: bold; text-align: left">' . $_HOAM_language ['tables']['column']['budget']['expenses'] . '</td>';
		$tbody .= '<td colspan="2">&nbsp;</td>';
		$tbody .= '</tr>';
		$tbody .= $expense_data;
		$tbody .= '</tbody>';
		$tbody .= '<tbody>';
		$tbody .= '<tr>';
		$tbody .= '<td style="font-weight: bold; text-align: left">' . $_HOAM_language ['tables']['column']['budget']['total_expenses'] . '</td>';
		$tbody .= '<td>' . number_format ($current_period_expense_amount, 2) . '</td>';
		$tbody .= '<td>&nbsp;</td>';
		$tbody .= '</tr>';
		$tbody .= '</tbody>';
		$tbody .= '<tbody>';
		$tbody .= '<tr>';
		$tbody .= '<td style="font-weight: bold; text-align: left">' . $_HOAM_language ['tables']['column']['budget']['net_income'] . '</td>';
		$tbody .= '<td>&nbsp;</td>';
		$tbody .= '<td>' . number_format ($current_period_income_amount - $current_period_expense_amount, 2) . '</td>';
		$tbody .= '</tr>';
		$tbody .= '</tbody>';

		$income_percent = round ((($current_period_income_amount / ($current_period_income_amount + $current_period_expense_amount)) * 100), 2);
		$expense_percent = 100 - $income_percent;
		$piechart = '<br /><center>' . HOAM_piechart (array ('Revenue ' . $income_percent . '%' => $income_percent, 'Expenses ' . $expense_percent . '%' => $expense_percent), 140, 140, 130, array ('colors' => array ('green', 'red'), 'fontsize' => 15));

		$table .= $piechart . $caption . $colgroup . $thead . $tbody;
	} else {
		$table .= '<tbody><tr><td colspan="6">' . $_HOAM_language ['errors']['generic']['date_out_of_range'] . '</td></tr></tbody>';
	}
	$table .= '</table>';
	
	return ($table); 
}

function HOAM_budgetInvoiceCharges ($cust_id, $charge, $padding, &$item_number, &$total_amount) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$budget_class = new HOAM_budget ('budget');
	
	// Display the current charge
	// $padding is the current css em padding # we indent the memo by. This is
	// to show a parentage to the charges on the invoice (just easier to read /
	// decipher).
	$line_item_amount = number_format ($budget_class -> returnAmount ($charge), 2);
	$total_amount += $line_item_amount;
	$amount_due_parent = $budget_class -> returnAmountDueParent ($charge);

	// Display Line Item
	if ($budget_class -> returnDatePosted ($charge) != NULL) {
		$opacity = ' class="less-opacity"';
	} else {
		$opacity = NULL;
	}
	$line_item = '<tr' . $opacity . '><td class="budget_invoice_item_number">' . $item_number++ . '</td>';
	
	// Display Memo
	$line_item .= '<td style="padding-left: ' . $padding . 'em;">';
	$memo = $budget_class -> returnMemo ($charge);
	if ($memo) {
		$line_item .= $memo;
	} else {
		// This is meant as a placeholder. Eventually, if there isn't a memo
		// specified, we either add the description recorded, or we add detail
		// programmatically given the flags.
	}
	if ($budget_class -> isBitSet ($charge, $_HOAM_setting ['budget']['flags']['payment'])) {
		$line_item .= '<br /><span class="highlight">Thank you for your payment !</span>';
	}
	$line_item .= '</td>';

	// Display Date Due / Posted
	if ($budget_class -> isBitSet ($charge, $_HOAM_setting ['budget']['flags']['payment']) OR
		$budget_class -> isBitSet ($charge, $_HOAM_setting ['budget']['flags']['credit'])) {
		$line_item .= '<td class="budget_invoice_item_date">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateDue ($charge))) . '</td>';
	} elseif (date ($_HOAM_country ['date']['format_mysql']) >= $budget_class -> returnDateDue ($charge)) {
		$line_item .= '<td class="budget_invoice_item_date error">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateDue ($charge))) . '</td>';
	} else {
		$line_item .= '<td class="budget_invoice_item_date">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateDue ($charge))) . '</td>';
	}
	
	// Display Amount
	$line_item .= '<td class="currency">' . $line_item_amount . '</td>';
	$line_item .= '</tr>';
	
	// If there are any children, display those charges as well
	$new_charge_list = $budget_class -> listCurrentChargesForCustomer ($cust_id, $charge);
	foreach ($new_charge_list as $new_charge) {
		$line_item .= HOAM_budgetInvoiceCharges ($cust_id, $new_charge, $padding + 2, $item_number, $total_amount);
	}
	return $line_item;
}

function HOAM_budgetInvoiceGenerate ($customer_id, $save_internal = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting, $wiki;
	require_once (HOAM_PATH_FUNCTION . 'budget.php');
	require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
	// For some reason, when the envelope and letter includes are done as
	// require_once, it only loads the first time through the script; any
	// subsequent calls leave it empty.
	require (HOAM_PATH_LETTER . 'hoam-envelope.php');
	require (HOAM_PATH_LETTER . 'hoam-paper.php');
	
	$budget_class = new HOAM_budget ('budget');
	$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
	$homeowner_class = new HOAM_homeowner ('homeowners');
					
	$trusted_customer_id = $budget_class -> superClean ($customer_id, $budget_class -> returnColumnSize ('customer_id'));
	
	$HTML = '<table>';
	$HTML .= '<colgroup>';
	$HTML .= '<col width="50% />';
	$HTML .= '<col width="50% />';
	$HTML .= '</colgroup>';
	$HTML .= '<tbody class="nostripe">';
	$HTML .= '<tr>';
	$HTML .= '<td>' . HOAM_organizationReturnMailingAddress () . '<br/>{{ORG_PHONE}}<br /></td>';
	$HTML .= '<td style="text-align: center" rowspan="2"><h2>' . mb_strtoupper ($_HOAM_language ['common']['invoice']) . '</h2></td>';
	$HTML .= '</tr>';
	$HTML .= '<tr>';
	$HTML .= '<td colspan="2">' . $_HOAM_language ['help_pages']['budget']['invoice']['property'] . ': ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_customer_id)) . '<br />';
	$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['date'] . ': ' . date ($_HOAM_country ['date']['format_reports']) . '</td>';
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
	
	// If there are no charges at all for a customer, then there are no
	// invoices. By checking for existing charges first, we avoid a
	// potential db error when there are no invoices.
	if ($budget_class -> returnNewestCharge ($trusted_customer_id)) {
		$invoice_list = $budget_invoice_class -> listInvoicesForCustomer ($trusted_customer_id);
	} else {
		$invoice_list = NULL;
	}
	
	if (is_array ($invoice_list)) {
		$total_last_invoice = $budget_class -> returnInvoiceChargeAmount ($invoice_list[count ($invoice_list) - 1], $trusted_customer_id);
		$previous_balance = $budget_class -> returnTotalAmountDue ($trusted_customer_id);
		// Now remove the charges and payments from the current invoice.
		$previous_balance -= $total_last_invoice;
		$last_month = date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') - 1, 1, date ('Y')));
		$payment_list = $budget_class -> listAllPaymentsForCustomer ($trusted_customer_id, $last_month);
		$recent_payments = 0;
		if (is_array ($payment_list)) {
			foreach ($payment_list as $payment) {
				$recent_payments += $budget_class -> returnAmount ($payment);
			}
		}
	} else {
		$previous_balance = 0;	
		$recent_payments = 0;
		$total_last_invoice = 0;
	}
	$total_amount_due = $budget_class -> returnTotalAmountDue ($trusted_customer_id);
	$HTML .= '<br />';
	$HTML .= '<table style="text-align: center; width: 100%">';
	$thead = '<thead>';
	$thead .= '<tr>';
    $thead .= '<th style="background-color: silver; font-weight: bold">' . $_HOAM_language ['tables']['column']['budget']['balance_previous'] . '</th>';
    $thead .= '<th style="background-color: silver; font-weight: bold">' . $_HOAM_language ['tables']['column']['budget']['payments_credits'] . '</th>';
    $thead .= '<th style="background-color: silver; font-weight: bold">' . $_HOAM_language ['tables']['column']['budget']['balance_forward'] . '</th>';
    $thead .= '<th style="background-color: silver; font-weight: bold">' . $_HOAM_language ['tables']['column']['budget']['total_current_charges'] . '</th>';
    $thead .= '<th style="background-color: silver; font-weight: bold">' . $_HOAM_language ['tables']['column']['budget']['total_amount_due'] . '</th>';
    $thead .= '<th style="background-color: silver; font-weight: bold">' . mb_strtoupper ($_HOAM_language ['tables']['column']['budget']['past_due_after']) . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$HTML .= $thead;
	$HTML .= '<tbody>';
	$HTML .= '<tr>';
	$HTML .= '<td>' . HOAM_returnCurrency ($previous_balance + -$recent_payments) . '</td>';
	$HTML .= '<td>' . HOAM_returnCurrency (-$recent_payments) . '</td>';
	$HTML .= '<td>' . HOAM_returnCurrency ($previous_balance) . '</td>';
	$HTML .= '<td>' . HOAM_returnCurrency ($total_last_invoice) . '</td>';
	$total_due = HOAM_returnCurrency ($previous_balance + $total_last_invoice);
	$HTML .= '<td>' . $total_due . '</td>';
	if ($total_amount_due > 0) {
		$open_charge_list = $budget_class -> listCurrentChargesForCustomer ($trusted_customer_id);
		$due_date = strtotime ($budget_class -> returnDateDue ($open_charge_list[0]));
		$days30 = 60 * 60 * 24 * 30;
		// If the due date is more than 30 days in the future, use it.
		if (($due_date - $days30) >= time ()) {
			$due_date = date ($_HOAM_country ['date']['format_reports'], $due_date);
		} else {
			$due_date = date ($_HOAM_country ['date']['format_endofmonth']);
		}
	} else {
		$due_date = $_HOAM_language ['common']['na'];
	}
	if (((int) date ('j') >= 10) AND ($due_date != $_HOAM_language ['common']['na'])) {
		$HTML .= '<td style="color: red">' . $due_date . '</td>';
	} else {
		$HTML .= '<td>' . $due_date . '</td>';
	}
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
	
	$HTML .= '<p>' . $_HOAM_language ['help_pages']['budget']['invoice']['new_charges'] . ':</p>';
	$HTML .= '<ol>';
	if (is_array ($invoice_list)) {
		$charge_list = $budget_class -> listChargesOnInvoice ($invoice_list[count ($invoice_list) - 1]);
		foreach ($charge_list as $charge) {
			if ($budget_class -> returnVendorId ($charge) != $trusted_customer_id) {
				$HTML .= '<li>' . $budget_class -> returnMemo ($charge) . ' ' . HOAM_returnCurrency ($budget_class -> returnAmount ($charge));
				if ($budget_class -> returnParentEntryId ($charge)) {
					$HTML .= '<br /><span style="font-size: 75%">' . $_HOAM_language ['field_names']['budget']['applied_to'] . ': ' . $budget_class -> returnMemo ($budget_class -> returnParentEntryId ($charge)) . '</span>';
				}
			}
			$HTML .= '</li>';
		}
	}
	$HTML .= '</ul>';
	$HTML .= '<br />';
	
	$budget_flags = $homeowner_class -> returnFlags ($trusted_customer_id, 'budget_flags');
	switch (TRUE) {
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan']) :
			$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['payment_plan_default'];
			break;
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan_default']) :
			// If the date the payment plan was defaulted on was at least one
			// month ago, then we know we've sent the owner at least one
			// notice about it. In that case, let's skip notifying them about
			// the default, and check the remaining flags.
			if ($homeowner_class -> returnPaymentPlanDate ($trusted_customer_id) <= date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date (1) - 2, 1, date ('Y')))) {
			} else {
				$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['payment_plan_default']; 
				break;				
			}
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney']) :
			$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['attorney'];
			break;
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_default']) :
			$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['default'];
			break;
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_delinquent']) :
			$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['delinquent'];
			break;
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_notice']) :
			$HTML .= sprintf ($_HOAM_language ['help_pages']['budget']['invoice']['notice'], HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_customer_id)));
			break;
		default :
			break;
	}
	
    $HTML .= '<div style="color: grey; font-size: 60%; text-align: center">' . $_HOAM_language ['help_pages']['budget']['invoice']['detach'] . '</div>';

	$HTML .= '<table cellpadding="0" cellspacing="0" style="width: 100%">';
	$HTML .= '<tbody class="nostripe">';
	$HTML .= '<tr>';
	$HTML .= '<td style="width: 55%">' . HOAM_organizationReturnMailingAddress () . '<br />{{ORG_PHONE}}<br /></td>';
	$HTML .= '<td>' . $_HOAM_language ['help_pages']['budget']['invoice']['payable_to'] . ': <em>' . $_HOAM_setting['organization']['name'] . '</em></td>';
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
	$HTML .= '<table cellpadding="0" cellspacing="0" style="width: 100%">';
	$HTML .= '<tbody class="nostripe">';
	$HTML .= '<tr>';
	$HTML .= '<td style="width: 55%">' . $_HOAM_language ['help_pages']['budget']['invoice']['property'] . ': ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_customer_id)) . '<br />';
	$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['date'] . ': ' . date ($_HOAM_country ['date']['format_reports']) . '<br /></td>';
	$online_amount = ($total_amount_due * ($_HOAM_setting ['budget']['paypal']['surcharge']['percent'] / 100)) + $_HOAM_setting ['budget']['paypal']['surcharge']['amount'];
	$HTML .= '<td><span style="font-weight: bold">' . $_HOAM_language ['help_pages']['budget']['invoice']['amount_due'] . ': ' . HOAM_returnCurrency ($total_amount_due) . '</span><br />';
	if ($_HOAM_setting ['budget']['paypal']['enable']) {
		$HTML .= '<em>' . sprintf ($_HOAM_language ['help_pages']['budget']['invoice']['add_for_online'], HOAM_returnCurrency ($online_amount)) . '</em><br />';
	}
	if ($due_date != $_HOAM_language ['common']['na']) {
        $HTML .= mb_strtoupper (sprintf ($_HOAM_language ['help_pages']['budget']['invoice']['pay_on_or_before'] , date ('F jS', mktime(0, 0, 0, date ('m', strtotime ($due_date)), 20, date ('Y'))))) . '</td>';
	} else {
		$HTML .= '</td>';
	}
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';

	$HTML .= '<table cellpadding="0" cellspacing="0" style="width: 100%">';
	$HTML .= '<tbody class="nostripe">';
	$HTML .= '<tr>';
	$HTML .= '<td style="width: ' . $_HOAM_envelope[$_HOAM_setting ['letter']['envelope']]['indent'] . 'mm">&nbsp;</td>';
	$HTML .= '<td style="width: 50%">' . HOAM_homeownerReturnMailingAddress ($trusted_customer_id) . '</td>';
    $HTML .= '<td style="width: 40%">[ ]' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_change'] . '<br />&nbsp; &nbsp; <span style="font-size: 75%">' . $_HOAM_language ['help_pages']['budget']['invoice']['fill_back'] . '</span></td>';
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
	$HTML .= '<div style="color: grey; font-size: 75%; page-break-before: always; text-align: justify">';
	$HTML .= $_HOAM_setting ['budget']['invoice']['fineprint'];
	$HTML .= '</div>';

	$HTML .= '<h4>' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_change'] . '</h4>';
    $HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['fill_below'];
    $HTML .= '<br /><br />';
    $HTML .= '<table border="1" cellpadding="1" style="color: grey; font-size: 65%">';
    $HTML .= '<tr>';
    $HTML .= '<td colspan="2">' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_name'] . '<br /><br /></td>';
    $HTML .= '<td>' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_company'] . '<br /><br /></td>';
    $HTML .= '</tr>';
    $HTML .= '<tr>';
    $HTML .= '<td colspan="2">' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_address'] . '<br /><br /></td>';
    $HTML .= '<td >' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_suite'] . '<br /><br /></td>';
    $HTML .= '</tr>';
	$HTML .= '<tr>';
	$HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['city'] . '<br /><br /></td>';
	$HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['state'] . '<br /><br /></td>';
	$HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['postalcode'] . '<br /><br /></td>';
	$HTML .= '</tr>';
	$HTML .= '<tr>';
	$HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . '<br /><br /></td>';
	$HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . '<br /><br /></td>';
	$HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . '<br /><br /></td>';
	$HTML .= '</tr>';
	$HTML .= '<tr>';
	$HTML .= '<td colspan="3">' . $_HOAM_language ['field_names']['homeowner']['email'] . '<br /><br /></td>';
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
	
	return ($HTML);
}

function HOAM_budgetInvoiceGeneratePDF ($customer_id, $save_internal = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	require_once (HOAM_PATH_FUNCTION . 'budget.php');
	require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
	// For some reason, when the envelope and letter includes are done as
	// require_once, it only loads the first time through the script; any
	// subsequent calls leave it empty.
	require (HOAM_PATH_LETTER . 'hoam-envelope.php');
	require (HOAM_PATH_LETTER . 'hoam-paper.php');
	
	$budget_class = new HOAM_budget ('budget');
	$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	// We have to create this, because although there is a global $wiki we could
	// use, it doesn't exist when running via the cli maintenance script.
	$wiki = new HOAM_wiki_parser ();
					
	$trusted_customer_id = $budget_class -> superClean ($customer_id, $budget_class -> returnColumnSize ('customer_id'));
	
	// create new PDF document
	$pdf = new HOAM_InvoicePDF (PDF_PAGE_ORIENTATION, 'mm', 'LETTER', TRUE, 'UTF-8', FALSE, TRUE);
	$pdf -> SetCreator (PDF_CREATOR);
	$pdf -> SetAuthor ('HOAM v' . HOAM_VERSION . ' ' . HOAM_RELEASE . ' :: ' . HOAM_URL);
	$pdf -> SetTitle (date ($_HOAM_country ['date']['format_mysql']) . ' Invoice for ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($customer_id)) . ' (' . $homeowner_class -> returnName ($customer_id) . ')');
	$pdf -> SetSubject (date ($_HOAM_country ['date']['format_mysql']) . ' Invoice for ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($customer_id)) . ' (' . $homeowner_class -> returnName ($customer_id) . ')');
	$pdf -> SetKeywords ('HOAM, invoice, ');
	$pdf -> SetMargins ($_HOAM_paper[$_HOAM_setting ['letter']['paper']]['margin']['left'],
						$_HOAM_paper[$_HOAM_setting ['letter']['paper']]['margin']['top'],
						$_HOAM_paper[$_HOAM_setting ['letter']['paper']]['margin']['right']);
	//set auto page breaks
	$pdf -> SetAutoPageBreak (FALSE, $_HOAM_paper[$_HOAM_setting ['letter']['paper']]['margin']['bottom']);

	// remove default header/footer
	$pdf -> setPrintHeader (FALSE);
	$pdf -> setPrintFooter (FALSE);
	
	// set the default font
	$pdf -> SetFont ('helvetica', '', 10, '', TRUE);
	
	//set image scale factor
	$pdf -> setImageScale (PDF_IMAGE_SCALE_RATIO);
	
	// Add the first page of data.
	$pdf -> AddPage ();

	$HTML = '<table>';
	$HTML .= '<colgroup>';
	$HTML .= '<col width="50% />';
	$HTML .= '<col width="50% />';
	$HTML .= '</colgroup>';
	$HTML .= '<tbody class="nostripe">';
	$HTML .= '<tr>';
	$HTML .= '<td>' . HOAM_organizationReturnMailingAddress () . '<br/>{{ORG_PHONE}}<br /></td>';
	$HTML .= '<td style="text-align: center" rowspan="2"><h2>' . mb_strtoupper ($_HOAM_language ['common']['invoice']) . '</h2></td>';
	$HTML .= '</tr>';
	$HTML .= '<tr>';
	$HTML .= '<td colspan="2">' . $_HOAM_language ['help_pages']['budget']['invoice']['property'] . ': ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_customer_id)) . '<br />';
	$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['date'] . ': ' . date ($_HOAM_country ['date']['format_reports']) . '</td>';
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
					
	$pdf -> writeHTML ($wiki -> parse ($HTML), TRUE, FALSE, TRUE, FALSE, '');

	// If there are no charges at all for a customer, then there are no
	// invoices. By checking for existing charges first, we avoid a
	// potential db error when there are no invoices.
	if ($budget_class -> returnNewestCharge ($trusted_customer_id)) {
		$invoice_list = $budget_invoice_class -> listInvoicesForCustomer ($trusted_customer_id);
	} else {
		$invoice_list = NULL;
	}
	
	if (is_array ($invoice_list)) {
		$total_last_invoice = $budget_class -> returnInvoiceChargeAmount ($invoice_list[count ($invoice_list) - 1], $trusted_customer_id);
		$previous_balance = $budget_class -> returnTotalAmountDue ($trusted_customer_id);
		// Now remove the charges and payments from the current invoice.
		$previous_balance -= $total_last_invoice;
		$last_month = date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') - 1, 1, date ('Y')));
		$payment_list = $budget_class -> listAllPaymentsForCustomer ($trusted_customer_id, $last_month);
		$recent_payments = 0;
		if (is_array ($payment_list)) {
			foreach ($payment_list as $payment) {
				$recent_payments += $budget_class -> returnAmount ($payment);
			}
		}
	} else {
		$previous_balance = 0;
		$recent_payments = 0;	
		$total_last_invoice = 0;
	}
	$total_amount_due = $budget_class -> returnTotalAmountDue ($trusted_customer_id);
	$HTML = '<br />';
	$HTML .= '<table style="text-align: center; width: 100%">';
	$thead = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th style="background-color: silver; font-weight: bold">' . $_HOAM_language ['tables']['column']['budget']['balance_previous'] . '</th>';
	$thead .= '<th style="background-color: silver; font-weight: bold">' . $_HOAM_language ['tables']['column']['budget']['payments_credits'] . '</th>';
	$thead .= '<th style="background-color: silver; font-weight: bold">' . $_HOAM_language ['tables']['column']['budget']['balance_forward'] . '</th>';
	$thead .= '<th style="background-color: silver; font-weight: bold">' . $_HOAM_language ['tables']['column']['budget']['total_current_charges'] . '</th>';
	$thead .= '<th style="background-color: silver; font-weight: bold">' . $_HOAM_language ['tables']['column']['budget']['total_amount_due'] . '</th>';
	$thead .= '<th style="background-color: silver; font-weight: bold">' . mb_strtoupper ($_HOAM_language ['tables']['column']['budget']['past_due_after']) . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$HTML .= $thead;
	$HTML .= '<tbody>';
	$HTML .= '<tr>';
	$HTML .= '<td>' . HOAM_returnCurrency ($previous_balance + -$recent_payments) . '</td>';
	$HTML .= '<td>' . HOAM_returnCurrency (-$recent_payments) . '</td>';
	$HTML .= '<td>' . HOAM_returnCurrency ($previous_balance) . '</td>';
	$HTML .= '<td>' . HOAM_returnCurrency ($total_last_invoice) . '</td>';
	$total_due = HOAM_returnCurrency ($previous_balance + $total_last_invoice);
	$HTML .= '<td>' . $total_due . '</td>';
	if ($total_amount_due > 0) {
		$open_charge_list = $budget_class -> listCurrentChargesForCustomer ($trusted_customer_id);
		$due_date = strtotime ($budget_class -> returnDateDue ($open_charge_list[0]));
		$days30 = 60 * 60 * 24 * 30;
		// If the due date is more than 30 days in the future, use it.
		if (($due_date - $days30) >= time ()) {
			$due_date = date ($_HOAM_country ['date']['format_reports'], $due_date);
		} else {
			$due_date = date ($_HOAM_country ['date']['format_endofmonth']);
		}
	} else {
		$due_date = $_HOAM_language ['common']['na'];
	}
	if (((int) date ('j') >= 10) AND ($due_date != $_HOAM_language ['common']['na'])) {
		$HTML .= '<td style="color: red">' . $due_date . '</td>';
	} else {
		$HTML .= '<td>' . $due_date . '</td>';
	}
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
	
	$pdf -> writeHTML ($wiki -> parse ($HTML), TRUE, FALSE, TRUE, FALSE, '');
													
    $HTML = '<p>' . $_HOAM_language ['help_pages']['budget']['invoice']['new_charges'] . ':</p>';
	$HTML .= '<ol>';
	$charge_list = $budget_class -> listChargesOnInvoice ($invoice_list[count ($invoice_list) - 1]);
	if (is_array ($charge_list)) {
		foreach ($charge_list as $charge) {
			if ($budget_class -> returnVendorId ($charge) != $trusted_customer_id) {
				$HTML .= '<li>' . $budget_class -> returnMemo ($charge) . ' ' . HOAM_returnCurrency ($budget_class -> returnAmount ($charge));
				if ($budget_class -> returnParentEntryId ($charge)) {
					$HTML .= '<br /><span style="font-size: 75%">' . $_HOAM_language ['field_names']['budget']['applied_to'] . ': ' . $budget_class -> returnMemo ($budget_class -> returnParentEntryId ($charge)) . '</span>';
				}
			}
			$HTML .= '</li>';
		}
	}
	$HTML .= '</ul>';
	$HTML .= '<br />';
	
	$budget_flags = $homeowner_class -> returnFlags ($trusted_customer_id, 'budget_flags');
	switch (TRUE) {
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan']) :
			$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['payment_plan_default'];
			break;
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan_default']) :
			// If the date the payment plan was defaulted on was at least one
			// month ago, then we know we've sent the owner at least one
			// notice about it. In that case, let's skip notifying them about
			// the default, and check the remaining flags.
			if ($homeowner_class -> returnPaymentPlanDate ($trusted_customer_id) <= date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date (1) - 2, 1, date ('Y')))) {
			} else {
				$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['payment_plan_default']; 
				break;				
			}
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney']) :
			$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['attorney'];
			break;
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_default']) :
			$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['default'];
			break;
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_delinquent']) :
			$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['delinquent'];
			break;
		case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_notice']) :
			$HTML .= sprintf ($_HOAM_language ['help_pages']['budget']['invoice']['notice'], HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_customer_id)));
			break;
		default :
			break;
	}
	
	$pdf -> writeHTML ($wiki -> parse ($HTML), TRUE, FALSE, TRUE, FALSE, '');

	// Output the detach notice.
	// Move just above the perf before we print the detach notice.
	$pdf -> setY ($_HOAM_envelope[$_HOAM_setting ['letter']['envelope']]['perforation'] - 6.3);
	
	$HTML = '<div style="color: grey; font-size: 60%; text-align: center">' . $_HOAM_language ['help_pages']['budget']['invoice']['detach'] . '</div>';
	$pdf -> writeHTML ($wiki -> parse ($HTML), TRUE, FALSE, TRUE, FALSE, '');
	
	// Move the pointer down so we can print the return coupon correctly.
	$pdf -> setY ($_HOAM_envelope[$_HOAM_setting ['letter']['envelope']]['address']['association']);
	$HTML = '<table cellpadding="0" cellspacing="0" style="width: 100%">';
	$HTML .= '<tbody>';
	$HTML .= '<tr>';
	$HTML .= '<td style="width: 55%">' . HOAM_organizationReturnMailingAddress () . '<br />{{ORG_PHONE}}<br /></td>';
    $HTML .= '<td>' . $_HOAM_language ['help_pages']['budget']['invoice']['payable_to'] . ': <em>' . $_HOAM_setting['organization']['name'] . '</em></td>';
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
	$pdf -> writeHTML ($wiki -> parse ($HTML), TRUE, FALSE, TRUE, FALSE, '');

	$pdf -> setY ($_HOAM_envelope[$_HOAM_setting ['letter']['envelope']]['info']);
	$HTML = '<table cellpadding="0" cellspacing="0" style="width: 100%">';
	$HTML .= '<tbody>';
	$HTML .= '<tr>';
	$HTML .= '<td style="width: 55%">' . $_HOAM_language ['help_pages']['budget']['invoice']['property'] . ': ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_customer_id)) . '<br />';
	$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['date'] . ': ' . date ($_HOAM_country ['date']['format_reports']) . '<br /></td>';
	$online_amount = ($total_amount_due * ($_HOAM_setting ['budget']['paypal']['surcharge']['percent'] / 100)) + $_HOAM_setting ['budget']['paypal']['surcharge']['amount'];
    $HTML .= '<td><span style="font-weight: bold">' . $_HOAM_language ['help_pages']['budget']['invoice']['amount_due'] . ': ' . HOAM_returnCurrency ($total_amount_due) . '</span><br />';
    if ($_HOAM_setting ['budget']['paypal']['enable']) {
    	$HTML .= '<em>' . sprintf ($_HOAM_language ['help_pages']['budget']['invoice']['add_for_online'], HOAM_returnCurrency ($online_amount)) . '</em><br />';
    }
	if ($due_date != $_HOAM_language ['common']['na']) {
        $HTML .= mb_strtoupper (sprintf ($_HOAM_language ['help_pages']['budget']['invoice']['pay_on_or_before'] , date ('F jS', mktime(0, 0, 0, date ('m', strtotime ($due_date)), 20, date ('Y'))))) . '</td>';
	} else {
		$HTML .= '</td>';
	}
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
	$pdf -> writeHTML ($wiki -> parse ($HTML), TRUE, FALSE, TRUE, FALSE, '');

	$pdf -> setY ($_HOAM_envelope[$_HOAM_setting ['letter']['envelope']]['address']['owner']);
	$HTML = '<table cellpadding="0" cellspacing="0" style="width: 100%">';
	$HTML .= '<tbody>';
	$HTML .= '<tr>';
	$HTML .= '<td style="width: ' . $_HOAM_envelope[$_HOAM_setting ['letter']['envelope']]['indent'] . 'mm">&nbsp;</td>';
	$HTML .= '<td style="width: 50%">' . HOAM_homeownerReturnMailingAddress ($trusted_customer_id) . '</td>';
	$HTML .= '<td style="width: 40%">[ ]' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_change'] . '<br />&nbsp; &nbsp; <span style="font-size: 75%">' . $_HOAM_language ['help_pages']['budget']['invoice']['fill_back'] . '</span></td>';
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
	$pdf -> writeHTML ($wiki -> parse ($HTML), TRUE, FALSE, TRUE, FALSE, '');

	$pdf -> addPage ();
	$HTML = '<div style="color: grey; font-size: 75%; text-align: justify">';
	$HTML .= $_HOAM_setting ['budget']['invoice']['fineprint'];
	$HTML .= '</div>';
	$pdf -> writeHTML ($wiki -> parse ($HTML), TRUE, FALSE, TRUE, FALSE, '');

	// Output the change of contact information.
	// Move just above the perf before we print the detach notice.
	$pdf -> setY ($_HOAM_envelope[$_HOAM_setting ['letter']['envelope']]['perforation'] + 4.2);
	$HTML = '<h4>' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_change'] . '</h4>';
	$HTML .= $_HOAM_language ['help_pages']['budget']['invoice']['fill_below'];
	$HTML .= '<br /><br />';
	$HTML .= '<table border="1" cellpadding="1" style="color: grey; font-size: 65%">';
	$HTML .= '<tr>';
	$HTML .= '<td colspan="2">' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_name'] . '<br /><br /></td>';
	$HTML .= '<td>' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_company'] . '<br /><br /></td>';
	$HTML .= '</tr>';
	$HTML .= '<tr>';
	$HTML .= '<td colspan="2">' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_address'] . '<br /><br /></td>';
	$HTML .= '<td >' . $_HOAM_language ['help_pages']['budget']['invoice']['contact_suite'] . '<br /><br /></td>';
	$HTML .= '</tr>';
	$HTML .= '<tr>';
    $HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['city'] . '<br /><br /></td>';
    $HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['state'] . '<br /><br /></td>';
    $HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['postalcode'] . '<br /><br /></td>';
    $HTML .= '</tr>';
    $HTML .= '<tr>';
    $HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . '<br /><br /></td>';
    $HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . '<br /><br /></td>';
    $HTML .= '<td>' . $_HOAM_language ['field_names']['homeowner']['telephone']['work'] . '<br /><br /></td>';
    $HTML .= '</tr>';
    $HTML .= '<tr>';
    $HTML .= '<td colspan="3">' . $_HOAM_language ['field_names']['homeowner']['email'] . '<br /><br /></td>';
	$HTML .= '</tr>';
	$HTML .= '</table>';
	$pdf -> writeHTML ($wiki -> parse ($HTML), TRUE, FALSE, TRUE, FALSE, '');
	
	// reset pointer to the last page
	$pdf -> lastPage ();
	if ($save_internal) {
		// return an array with the filename and PDF data in a string. 
		return (array ('filename' => date ('Ymd') . ' ' . $_HOAM_setting ['website']['title'] . ' ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($customer_id)) . ' Invoice.pdf',
						'data' => $pdf -> Output (date ('Ymd') . ' ' . $_HOAM_setting ['website']['title'] . ' ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($customer_id)) . ' Invoice.pdf', 'S')));
	} else {
		// Output a file to the client browser and exit.
		$pdf -> Output (date ('Ymd') . ' ' . $_HOAM_setting ['website']['title'] . ' ' . HOAM_returnAddress ($homeowner_class -> returnLotId ($customer_id)) . ' Invoice.pdf', 'D');
		exit ();
	}
}

function HOAM_budgetStatementAttorney ($customer_id) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$budget_class = new HOAM_budget ('budget');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	
	$HTML .= '<table>';
	$HTML .= '<thead>';
	$HTML .= '<tr><th>Address</th><th>Name</th><th>Last Payment Date</th></tr>';
	$HTML .= '<tr><th>Amount Owed</th><th>Payment Plan</th><th></th></tr>';
	$HTML .= '</thead>';
	$HTML .= '<tbody>';
	$HTML .= '<tr>';
	$HTML .= '<td class="center">' . HOAM_returnAddress ($homeowner_class -> returnLotId ($customer_id)) . '</td>';
	$HTML .= '<td class="center">' . html_encode (stripslashes ($homeowner_class -> returnName ($customer_id))) . '</td>';
	$HTML .= '<td class="center">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDatePosted ($budget_class -> returnLastPayment ($customer_id)))) . '</td>';
	$HTML .= '</tr>';
	$HTML .= '<tr>';
	$HTML .= '<td class="center">' . HOAM_returnCurrency ($budget_class -> returnTotalAmountDue ($customer_id)) . '</td>';
	$HTML .= '<td colspan="2" class="center">' . '</td>';
	$HTML .= '</tr>';
	$HTML .= '</tbody>';
	$HTML .= '</table>';
	
	return ($HTML);
}

function HOAM_budgetStatementCategories ($customer_id) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$budget_class = new HOAM_budget ('budget');
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	
	$HTML = '<h2>' . $_HOAM_language ['field_names']['budget']['assessment']['due'] . '</h2>';
	$HTML .= '<table>';
	$HTML .= '<thead>';
	$HTML .= '<tr><th>Assessment Type</th><th>Total $ for Type</th><th>$ Amount per Vendor</th></tr>';
	$HTML .= '</thead>';
	$HTML .= '<tbody>';
	$total_assessment_amount = 0;
	$total_assessment_owed = 0;
	$total_vendor_amount = 0;
	$total_vendor_owed = 0;
	$_HOAM_assessment_list = &$_HOAM_language ['budget']['assessment'];
	foreach ($_HOAM_assessment_list as $assessment_type) {
		$assessment_charges = $budget_class -> listCurrentChargesForCustomerWithFlags ($customer_id, $_HOAM_setting['budget']['flags'][$assessment_type['value']]);
		$assessment_type['amount'] = 0;
		$assessment_type['owed'] = 0;
		$row_assessment_amount = 0;
		$vendor_charges_total = array ();
		$vendor_charges_owed = array ();
		$vendor_list = array ();
		$row = '<tr>';
		if (is_array ($assessment_charges)) {
			foreach ($assessment_charges as $charge) {
				$charge_amount = $budget_class -> returnAmount ($charge);
				$assessment_type['amount'] += $charge_amount;
				$row_assessment_amount += $charge_amount;
				$vendor_id = $budget_class -> returnVendorId ($charge);
				if (!in_array ($vendor_id, $vendor_list)) {
					array_push ($vendor_list, $vendor_id);
				}
				$vendor_charges_total [$vendor_id] += $charge_amount;
				$charge_payments = $budget_class -> listAllPaymentsForCharge ($charge);
				if (is_array ($charge_payments)) {
					foreach ($charge_payments as $payment) {
						$charge_amount += $budget_class -> returnAmount ($payment);
					}
				}
				$assessment_type['owed'] += $charge_amount;
				$total_assessment_owed += $charge_amount;
				$vendor_charges_owed [$vendor_id] += $charge_amount;
			}
		}
		$row .= '<td class="center">' . $assessment_type['name'] . '</td>';
		$row .= '<td class="center">' . HOAM_returnCurrency ($assessment_type['amount']) . ' (' . HOAM_returnCurrency ($assessment_type['owed']) . ' remaining)</td>';
		$row .= '<td>';
		$row .= '<ul style="list-style: none">';
		foreach ($vendor_list as $vendor) {
			$total_vendor_amount += $vendor_charges_total[$vendor];
			$total_vendor_owed += $vendor_charges_owed[$vendor];
			$row .= '<li>';
			if ($budget_vendor_class -> returnName ($vendor)) {
				$row .= $budget_vendor_class -> returnName ($vendor);
			} else {
				$row .= '<span class="less-opacity">' . $_HOAM_language ['field_names']['budget']['vendor']['name'] . ' ' . $_HOAM_language ['common']['not-specified'] . '</span>';
			}
			$row .= ': ' . HOAM_returnCurrency ($vendor_charges_total[$vendor]) . ' (' . HOAM_returnCurrency ($vendor_charges_owed[$vendor]) . ' remaining)</li>';
		}
		$row .= '</ul></td>';
		$row .= '</tr>';
		if ($row_assessment_amount > 0) {
			$HTML .= $row;
			$total_assessment_amount += $row_assessment_amount;
		}
	}
	if ($total_assessment_amount <= 0) {
		$HTML .= '<tr><td colspan="3" class="center">' . $_HOAM_language ['field_names']['budget']['assessment']['none_due'] . '</td></tr>';
	}
	$HTML .= '</tbody>';
	$HTML .= '<tfoot>';
	$HTML .= '<tr>';
	$HTML .= '<th>&nbsp;</th>';
	$HTML .= '<th>&nbsp;</th>';
	$HTML .= '<th>' . number_format ($total_vendor_amount, 2) . ' (' . HOAM_returnCurrency ($total_vendor_owed) . ' remaining)</th>';
	$HTML .= '</tr>';
	$HTML .= '</tfoot>';
	$HTML .= '</table>';

	return ($HTML);
}

function HOAM_budgetStatementHeader ($customer_id) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
	
	$budget_class = new HOAM_budget ('budget');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	
	$HTML = '<div class="RHS"><em><strong>' . $_HOAM_language ['field_names']['budget']['statement'] . '</strong></em><br />' . date ($_HOAM_country ['date']['format_long']) . '</div>';
	$HTML .= '<em><strong>' . $_HOAM_language ['field_names']['homeowner']['address'] . '</strong></em><br />' . HOAM_returnAddress ($homeowner_class -> returnLotId ($customer_id));
	$HTML .= '<br /><br /><em><strong>' . $_HOAM_language ['field_names']['homeowner']['mailing'] . '</strong></em><br />' . HOAM_homeownerReturnMailingAddress ($customer_id);
	$HTML .= '<br /><br /><em><strong>' . $_HOAM_language ['field_names']['homeowner']['purchasedate'] . '</strong></em><br />' . date ($_HOAM_country ['date']['format_long'], strtotime ($homeowner_class -> returnPurchaseDate ($customer_id)));
	$account_balance = $budget_class -> returnAccountBalance ($customer_id);
	$HTML .= '<br /><br /><em><strong>' . $_HOAM_language ['field_names']['budget']['balance'] . '</strong></em><br />';
	$HTML .= HOAM_returnCurrency ($account_balance);
	if ($account_balance > 0) {
		$oldest_charge = $budget_class -> returnOldestCharge ($customer_id);
		$last_payment = $budget_class -> returnLastPayment ($customer_id);
		$HTML .= ' (' . round ((time () - strtotime ($budget_class -> returnDateDue ($oldest_charge))) / 86400) . $_HOAM_language ['field_names']['budget']['last_payment'];
		if ($last_payment) {
			$HTML .= HOAM_date ($_HOAM_country ['date']['format_long'], strtotime ($budget_class -> returnDatePosted ($last_payment)));
		} else {
			$HTML .= $_HOAM_language ['common']['never'];
		}
		$HTML .= ')';
	}
	$HTML .= '<br /><br /><em><strong>' . $_HOAM_language ['field_names']['homeowner']['assessment']['payment_plan'] . '</strong></em><br />';
	if ($homeowner_class -> isBitSet ($customer_id, $_HOAM_setting ['budget']['flags']['late_payment_plan'], 'budget_flags')) {
		$HTML .= $_HOAM_language ['common']['yes'];
		$HTML .= ', <q>' . stripslashes (nl2br (html_encode (str_replace ('\r\n', "\r\n", $homeowner_class -> returnPaymentPlanDetails ($customer_id))))) . '</q>';
	} else {
		$HTML .= $_HOAM_language ['common']['no'];
	}

	return ($HTML);
}

function HOAM_budgetUnpaidAmount ($include_actions = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$budget_class = new HOAM_budget ('budget');
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');

	$colgroup = '<colgroup>';
	$colgroup .= '<col width="20%" />';
	$colgroup .= '<col width="25%" />';
	if ($include_actions) {
		$colgroup .= '<col width="15%" class="donotprint" />';
	}
	$colgroup .= '<col width="10%" />';
	$colgroup .= '<col width="9%" char="." />';
	$colgroup .= '<col width="5%" />';
	$colgroup .= '</colgroup>';
	$thead = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['address'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['name'] . '</th>';
	if ($include_actions) {
		$thead .= '<th class="donotprint">' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
	}
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['last_payment'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['balance'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['flags'] . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$days_30 = 86400 * 30;
	$total_amount_prepaid = 0;
	$total_amount_due = 0;
	$total_count_prepaid = 0;
	$total_count_due = 0;
	if (!empty ($lot_class -> listStreets ())) {
		// We're performing this check in case no streets have been configured.
		// This is extremely unlikely, however it will cause a MySQL error if
		// the maintenance script is set to run but no lots have been entered
		// yet (fresh install).
		$open_charge_list = $budget_class -> listCustomersWithOpenCharges ();
		if (is_array ($open_charge_list)) {
			$open_charge_count = count ($open_charge_list);
			$prepaid_tbody = '';
			$unpaid_tbody = '';
			foreach ($open_charge_list as $current_homeowner) {
				$current_amount = $budget_class -> returnTotalAmountDue ($current_homeowner);
				$tbody  = '<tr>';
				$tbody .= '<td>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($current_homeowner)) . '</td>';
				$tbody .= '<td>' . html_encode (stripslashes ($homeowner_class -> returnName ($current_homeowner))) . '</td>';
				if ($include_actions) {
					$tbody .= '<td class="donotprint">';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
						$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
						$tbody .= '<small><a href="/admin/homeowner/edit/' . $current_homeowner . '/">' . $_HOAM_language ['action']['homeowner']['edit'] . '</a></small>';
						$tbody .= ' &nbsp; ';
					}
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
						$_HOAM_setting ['user']['ids']['root'] == $_SESSION['current_user']['id']) {
						$tbody .= '<small><a href="/admin/financial/homeowner/review/' . $current_homeowner . '/">' . $_HOAM_language ['action']['budget']['review'] . '</a></small>';
						$tbody .= ' &nbsp; ';
						$tbody .= '<small><a href="/admin/financial/homeowner/invoice/' . $current_homeowner . '/">' . $_HOAM_language ['action']['budget']['invoice'] . '</a></small>';
						$tbody .= ' &nbsp; ';
						$tbody .= '<small><a href="/admin/financial/homeowner/invoice_pdf/' . $current_homeowner . '/">PDF</a></small>';
					}
					$tbody .= '</td>';
				}
				$last_payment = $budget_class -> returnLastPayment ($current_homeowner);
				$tbody .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDatePosted ($last_payment))) . '</td>';
				$amount_due = $budget_class -> returnTotalAmountDue ($current_homeowner);
				if ($amount_due > 0) {
					$oldest_charge = $budget_class -> returnOldestCharge ($current_homeowner);
					if (date ($_HOAM_country ['date']['format_mysql']) <= $budget_class -> returnDateLate ($oldest_charge)) {
						$tbody .= '<td class="caution currency">' . HOAM_returnCurrency ($amount_due) . '</td>';
					} else {
						$tbody .= '<td class="error currency">' . HOAM_returnCurrency ($amount_due) . '</td>';
					}
				} else {
					$tbody .= '<td class="currency">' . HOAM_returnCurrency ($amount_due) . '</td>';
				}
				$tbody .= '<td>';
				$homeowner_flags = $homeowner_class -> returnFlags ($current_homeowner);
				if (!(int) $homeowner_flags & (int) $_HOAM_setting ['homeowner']['flags']['resident']) {
					$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['abbr']['lr']) . '">R</span> ';
				}
				if ((int) $homeowner_flags & (int) $_HOAM_setting ['homeowner']['flags']['no_fees']) {
					$tbody .= '<span class="cursor_help caution" title="' . strip_tags ($_HOAM_language ['abbr']['nf']) . '">&curren;</span> ';
				}
				$budget_flags = $homeowner_class -> returnFlags ($current_homeowner, 'budget_flags');
				if ((int) $budget_flags & (int) $_HOAM_setting ['homeowner']['flags']['ignore_budget']) {
					$tbody .= '<span class="caution cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['homeowner']['flags']['ignore_budget']) . '">E</span> ';
				}
				if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan']) {
					$tbody .= '<span class="complete cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['payment_plan_description']) . '">P</span> ';
				}
				if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan_default']) {
					$tbody .= '<span class="complete cursor_help warning" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['payment_plan_default_description']) . '">P</span> ';
				}
				if ((int) $budget_flags & (int) $_HOAM_setting ['homeowner']['flags']['bankrupt']) {
					$tbody .= '<span class="caution cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['bankrupt']) . '">B</span> ';
				}
				switch (TRUE) {
					case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney']) :
						$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_description']) . '">A</span>';
						switch (TRUE) {
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_eviction']) :
								$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_eviction_description']) . '">(E)</span>';
								break;
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_foreclosure']) :
								$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_foreclosure_description']) . '">(F)</span>';
								break;
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_demand']) :
								$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_demand_description']) . '">(D)</span>';
								break;
						}
						$tbody .= ' ';
						break;
					case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_default']) :
						$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_default_description']) . '">3</span> ';
						break;
					case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_delinquent']) :
						$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_delinquent_description']) . '">2</span> ';
						break;
					case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_notice']) :
						$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_notice_description']) . '">1</span> ';
						break;
					default:
						break;
				}
				if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_lien']) {
					$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_lien_description']) . '">L</span> ';
				}
				$tbody .= '</td>';
				$tbody .= '</tr>';
				// Compile two separate sums of delinquencies and credits.
				if ($current_amount > 0) {
					$total_count_due++;
					$total_amount_due += $current_amount;
					$unpaid_tbody .= $tbody;
				} else {
					$total_count_prepaid++;
					$total_amount_prepaid += $current_amount;
					$prepaid_tbody .= $tbody;
				}
			// Calculate the averages, min, median, max.
			}
		} else {
			$unpaid_tbody = '<tr><td colspan="8">' . $_HOAM_language ['errors']['budget']['no_entries'] . '</td></tr>';
			$prepaid_tbody = '<tr><td colspan="8">' . $_HOAM_language ['errors']['budget']['no_entries'] . '</td></tr>';
		}
	} else {
		$unpaid_tbody = '<tr><td colspan="8">' . $_HOAM_language ['errors']['budget']['no_entries'] . '</td></tr>';
		$prepaid_tbody = '<tr><td colspan="8">' . $_HOAM_language ['errors']['budget']['no_entries'] . '</td></tr>';
	}
	$HTML = '<table class="center" style="page-break-inside: avoid">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>' . $unpaid_tbody . '</tbody></table>';
	$HTML .= sprintf ($_HOAM_language ['field_names']['reports']['total_due'], HOAM_returnCurrency ($total_amount_due), $total_count_due);
	$HTML .= '<p>';
	$HTML .= 'Average: ' . HOAM_returnCurrency ($total_amount_due / $open_charge_count); 
	$HTML .= ' Min: ' . HOAM_returnCurrency ($budget_class -> returnTotalAmountDue ($open_charge_list[$open_charge_count - 1]));
	if ($open_charge_count % 2) {
		$amount = $budget_class -> returnTotalAmountDue ($open_charge_list[($open_charge_count / 2) - 1]);
		$amount += $budget_class -> returnTotalAmountDue ($open_charge_list[($open_charge_count / 2) + 1]);
		$HTML .= ' Median: '. HOAM_returnCurrency ($amount / 2);
		unset ($amount);
	} else {
		$HTML .= ' Median: ' . HOAM_returnCurrency ($budget_class -> returnTotalAmountDue ($open_charge_list[$open_charge_count / 2]));
	}
	$HTML .= ' Max: ' . HOAM_returnCurrency ($budget_class -> returnTotalAmountDue ($open_charge_list[0]));
	$HTML .= '</p>';
	if ($total_amount_prepaid < 0) {
		$HTML .= '<table class="center" style="page-break-inside: avoid">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>' . $prepaid_tbody . '</tbody></table>';
		$HTML .= sprintf ($_HOAM_language ['field_names']['reports']['total_prepaid'], HOAM_returnCurrency (-$total_amount_prepaid), $total_count_prepaid);
	} else {
		$HTML .= $_HOAM_language ['errors']['budget']['no_prepaid'];
	}
	return ($HTML);	
}

function HOAM_budgetUnpaidStreet () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$budget_class = new HOAM_budget ('budget');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	
	$colgroup = '<colgroup>';
	$colgroup .= '<col span="2" />';
	$colgroup .= '<col align="char" char="." />';
	$colgroup .= '<col />';
	$colgroup .= '<col align="char" char="." />';
	$colgroup .= '</colgroup>';
	$thead = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['lot']['street'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['prepaid_homes'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['prepaid_amount'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['delinquent_homes'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['delinquent_percent'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['delinquent_amount'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['flags'] . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$street_list = $lot_class -> ListStreets ();
	$total_due_amount = 0;
	$total_due_count = 0;
	$total_prepaid_amount = 0;
	$total_prepaid_count = 0;
	$tbody = '';
	if (!empty ($street_list)) {
		// We're performing this check in case no streets have been configured.
		// This is extremely unlikely, however it will cause a MySQL error if
		// the maintenance script is set to run but no lots have been entered
		// yet (fresh install).
		foreach ($street_list as $street) {
			$lot_list = $lot_class -> ListByStreet ($street);
			$lot_count = count ($lot_list);
			$total_street_due_amount = 0;
			$total_street_due_count = 0;
			$total_street_prepaid_amount = 0;
			$total_street_prepaid_count = 0;
			$street_flags = 0;
			foreach ($lot_list as $lot) {
				$homeowner_list = $homeowner_class -> listByLotId ($lot);
				if (is_array ($homeowner_list)) {
					foreach ($homeowner_list as $homeowner_id) {
						$street_flags = $street_flags | (int) $homeowner_class -> returnFlags ($homeowner_id, 'budget_flags');
						$amount_due = $budget_class -> returnTotalAmountDue ($homeowner_id);
						if ($amount_due > 0) {
							$total_due_amount += $amount_due;
							$total_due_count++;
							$total_street_due_amount += $amount_due;
							$total_street_due_count++;
						} elseif ($amount_due < 0) {
							$total_prepaid_amount += $amount_due;
							$total_prepaid_count++;
							$total_street_prepaid_amount += $amount_due;
							$total_street_prepaid_count++;
						}
					}
				}
			}
			$tbody .= '<tr><td>' . $street. '</td>';
			$tbody .= '<td>' . $total_street_prepaid_count . '</td>';
			$tbody .= '<td class="currency">' . HOAM_returnCurrency (-$total_street_prepaid_amount) . '</td>';
			if ($total_street_due_count == 0) {
				$tbody .= '<td class="complete">' . $total_street_due_count . '</td>';
				$tbody .= '<td class="currency complete">' . number_format (($total_street_due_count / $lot_count) * 100, 1) . '%</td>';
				$tbody .= '<td class="currency complete">' . HOAM_returnCurrency (0) . '</td>';
			} else {
				$tbody .= '<td>' . $total_street_due_count . '</td>';
				$tbody .= '<td class="currency">' . number_format (($total_street_due_count / $lot_count) * 100, 1) . '%</td>';
				$tbody .= '<td class="currency">' . HOAM_returnCurrency ($total_street_due_amount) . '</td>';
			}
			$tbody .= '<td>';
			if ((int) $street_flags & (int) $_HOAM_setting ['homeowner']['flags']['ignore_budget']) {
				$tbody .= '<span class="caution cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['homeowner']['flags']['ignore_budget']) . '">E</span> ';
			}
			if ((int) $street_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan']) {
				$tbody .= '<span class="complete cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['payment_plan_description']) . '">P</span> ';
			}
			if ((int) $street_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan_default']) {
				$tbody .= '<span class="complete cursor_help warning" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['payment_plan_default_description']) . '">P</span> ';
			}
			if ((int) $street_flags & (int) $_HOAM_setting ['homeowner']['flags']['bankrupt']) {
				$tbody .= '<span class="caution cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['bankrupt']) . '">B</span> ';
			}
			switch (TRUE) {
				case ((int) $street_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney']) :
					$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_description']) . '">A</span>';
					switch (TRUE) {
						case ((int) $street_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_eviction']) :
							$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_eviction_description']) . '">(E)</span>';
							break;
						case ((int) $street_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_foreclosure']) :
							$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_foreclosure_description']) . '">(F)</span>';
							break;
						case ((int) $street_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_demand']) :
							$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_demand_description']) . '">(D)</span>';
							break;
					}
					$tbody .= ' ';
					break;
				case ((int) $street_flags & (int) $_HOAM_setting ['budget']['flags']['late_default']) :
					$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_default_description']) . '">3</span> ';
					break;
				case ((int) $street_flags & (int) $_HOAM_setting ['budget']['flags']['late_delinquent']) :
					$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_delinquent_description']) . '">2</span> ';
					break;
				case ((int) $street_flags & (int) $_HOAM_setting ['budget']['flags']['late_notice']) :
					$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_notice_description']) . '">1</span> ';
					break;
				default:
					break;
			}
			if ((int) $street_flags & (int) $_HOAM_setting ['budget']['flags']['late_lien']) {
				$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_lien_description']) . '">L</span> ';
		}
			$tbody .= '</td>';
			$tbody .= '</tr>';
		}
	}
	$HTML = '<table class="center" style="page-break-inside: avoid">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>' . $tbody . '</tbody></table>';
	
	$HTML .= sprintf ($_HOAM_language ['field_names']['reports']['total_due'], HOAM_returnCurrency ($total_due_amount), $total_due_count);
	if (!empty ($lot_class -> listStreets ())) {
		// We're performing this check in case no streets have been configured.
		// This is extremely unlikely, however it will cause a MySQL error if
		// the maintenance script is set to run but no lots have been entered
		// yet (fresh install).
		if ($budget_class -> returnCountAll () != 0) {
			$open_charge_list = $budget_class -> listCustomersWithOpenCharges ();
			if (is_array ($open_charge_list)) {
				$open_charge_count = count ($open_charge_list);
				$HTML .= '<p>';
				$HTML .= ucfirst ($_HOAM_language ['common']['average']) . ': ' . HOAM_returnCurrency ($total_due_amount / $open_charge_count); 
				$HTML .= ' Min: ' . HOAM_returnCurrency ($budget_class -> returnTotalAmountDue ($open_charge_list[$open_charge_count - 1]));
				if ($open_charge_count % 2) {
					$amount = $budget_class -> returnTotalAmountDue ($open_charge_list[($open_charge_count / 2) - 1]);
					$amount += $budget_class -> returnTotalAmountDue ($open_charge_list[($open_charge_count / 2) + 1]);
					$HTML .= ' ' . ucfirst ($_HOAM_language ['common']['median']) . ': '. HOAM_returnCurrency ($amount / 2);
					unset ($amount);
				} else {
					$HTML .= ' ' . ucfirst ($_HOAM_language ['common']['median']) . ': ' . HOAM_returnCurrency ($budget_class -> returnTotalAmountDue ($open_charge_list[$open_charge_count / 2]));
				}
				$HTML .= ' Max: ' . HOAM_returnCurrency ($budget_class -> returnTotalAmountDue ($open_charge_list[0]));
				$HTML .= '</p>';
			}
			$HTML .= sprintf ($_HOAM_language ['field_names']['reports']['total_prepaid'], HOAM_returnCurrency (-$total_prepaid_amount), $total_prepaid_count);
		}
	}
	return ($HTML);
}

function HOAM_budgetView ($trusted_budget_id, $trusted_date_start, $trusted_date_end) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$budget_class = new HOAM_budget ('budget');
	$budget_budget_class = new HOAM_budget_budget ('budget_budget');
	$budget_category_class = new HOAM_budget_category ('budget_category');
	$budget_tracking_class = new HOAM_budget_track ('budget_track');
		
	$HTML = '<table id="budget_detail" style="page-break-inside: avoid; text-align: right">';

	if ((date ($_HOAM_country ['date']['format_mysql'], strtotime ($trusted_date_start)) >= $budget_budget_class -> returnDateStart ($trusted_budget_id)) AND
		(date ($_HOAM_country ['date']['format_mysql'], strtotime ($trusted_date_end)) <= $budget_budget_class -> returnDateEnd ($trusted_budget_id))) {

		$colgroup = '<colgroup>';
		$colgroup .= '<col width="24" />';
		$colgroup .= '<col width="33" span="6" />';
		$colgroup .= '</colgroup>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th rowspan="2">' . $_HOAM_language ['tables']['column']['budget']['category'] . '</th>';
		$thead .= '<th colspan="3">' . $_HOAM_language ['tables']['column']['budget']['budget']['current_period'];
		$thead .= ' (';
		$thead .= date ($_HOAM_country ['date']['format_reports'], strtotime ($trusted_date_start));
		$thead .= ' to ';
		$thead .= date ($_HOAM_country ['date']['format_reports'], strtotime ($trusted_date_end));
		$thead .= ')';
		$thead .= '</th>';
		$thead .= '<th colspan="3">' . $_HOAM_language ['tables']['column']['budget']['budget']['budget_to_date'];
		$thead .= ' (';
		$thead .= date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_budget_class -> returnDateStart ($trusted_budget_id)));
		$thead .= ' to ';
		$thead .= date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_budget_class -> returnDateEnd ($trusted_budget_id)));
		$thead .= ')';
		$thead .= '</th>';
		$thead .= '</tr>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['budget']['actual'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['budget']['budget'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['budget']['variance'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['budget']['actual'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['budget']['budget'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['budget']['variance'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$expense_tbody = '<tbody id="budget|view|expense">';
		$expense_tbody .= '<tr><td style="font-weight: bold; text-align: left">' . $_HOAM_language ['field_names']['budget']['category']['expense'] . '</td><td colspan="6">&nbsp;</td></tr>';

		$income_tbody = '<tbody id="budget|view|income">';
		$income_tbody .= '<tr><td style="font-weight: bold; text-align: left">' . $_HOAM_language ['field_names']['budget']['category']['income'] . '</td><td colspan="6">&nbsp;</td></tr>';

		$number_of_days = (strtotime ($trusted_date_end) - strtotime ($trusted_date_start)) / 86400;
		$category_list = $budget_tracking_class -> listCategories ($trusted_budget_id);
		$category_parent_child_list = $budget_category_class -> listCategoryAll ();

		$current_period_expense_amount = 0;
		$current_period_income_amount = 0;
		$year_amount = 0;
		$year_budget_amount = 0;
		$year_budget_total_expense_amount = 0;
		$year_budget_total_income_amount = 0;
		$year_total_expense_amount = 0;
		$year_total_income_amount = 0;
		
		foreach ($category_parent_child_list as $category) {
			$current_period_amount = 0;
			// Is this a parent category?
			if ($category[1] == 0) {
				$tbody = '<tr><td colspan="7" align="left">' . $budget_category_class -> returnName ($category[0]) . '</td></tr>';
			} else {
				$id = $budget_tracking_class -> returnIdForCategory ($category[0], $trusted_budget_id);
				if ($id) {
					$year_budget_amount = $budget_tracking_class -> returnAmount ($id);
					$tbody = '<tr>';
					$tbody .= '<td style="padding-left: 3em; text-align: left">' . $budget_category_class -> returnName ($category[0]) . '</td>';
					$query = "SELECT SUM(amount) FROM budget WHERE customer_id='" . $_HOAM_setting ['budget']['ids']['organization'] . "' AND category_id='" . $category [0] . "' AND amount > 0 AND datedue >= '" . $trusted_date_start . "' AND datedue <= '" . $trusted_date_end . "'";
					$current_period_amount = $budget_class -> itemQuery ($query);
					$tbody .= '<td>' . number_format ($current_period_amount, 2) . '</td>';
					$tbody .= '<td>' . number_format ($year_budget_amount * ($number_of_days / 365), 2) . '</td>';
					$variance = $current_period_amount - ($year_budget_amount * ($number_of_days / 365));
					if ((int) $budget_category_class -> returnFlags ($category[0]) & (int) $_HOAM_setting ['budget']['flags']['category_income']) {
						if ($variance > 0) {
							$tbody .= '<td class="complete">' . number_format ($variance, 2) . '</td>';
						} elseif ($variance < 0) {
							$tbody .= '<td class="warning">(' . number_format (-$variance, 2) . ')</td>';
						} else {
							$tbody .= '<td>' . number_format ($variance, 2) . '</td>';
						}
					} else {
						if ($variance > 0) {
							$tbody .= '<td class="warning">(' . number_format ($variance, 2) . ')</td>';
						} elseif ($variance < 0) {
							$tbody .= '<td class="complete">' . number_format (-$variance, 2) . '</td>';
						} else {
							$tbody .= '<td>' . number_format ($variance, 2) . '</td>';
						}
					}
//					$year_amount = 0;
					$start_date = $budget_budget_class -> returnDateStart ($trusted_budget_id);
					$end_date = $budget_budget_class -> returnDateEnd ($trusted_budget_id);
					$query = "SELECT SUM(amount) FROM budget WHERE customer_id='" . $_HOAM_setting ['budget']['ids']['organization'] . "' AND category_id = '" . $category [0] . "' AND amount > 0 AND datedue >= '" . $budget_budget_class -> returnDateStart ($trusted_budget_id) . "' AND datedue <= '" . $budget_budget_class -> returnDateEnd ($trusted_budget_id) . "'";
					$year_amount = $budget_class -> itemQuery ($query);
					$tbody .= '<td>' . number_format ($year_amount, 2) . '</td>';
					$tbody .= '<td>' . number_format ($year_budget_amount, 2) . '</td>';
					$variance = $year_amount - $year_budget_amount;
					if ((int) $budget_category_class -> returnFlags ($category[0]) & (int) $_HOAM_setting ['budget']['flags']['category_income']) {
						if ($variance > 0) {
							$tbody .= '<td class="complete">' . number_format ($variance, 2) . '</td>';
						} elseif ($variance < 0) {
							$tbody .= '<td class="warning">(' . number_format (-$variance, 2) . ')</td>';
						} else {
							$tbody .= '<td>' . number_format ($variance, 2) . '</td>';
						}
					} else {
						if ($variance > 0) {
							$tbody .= '<td class="warning">(' . number_format ($variance, 2) . ')</td>';
						} elseif ($variance < 0) {
							$tbody .= '<td class="complete">' . number_format (-$variance, 2) . '</td>';
						} else {
							$tbody .= '<td>' . number_format ($variance, 2) . '</td>';
						}
					}
					$tbody .= '</tr>';
				}
			}
			// Don't display a budget line if the amounts are zero, unless it's
			// a parent category.
			if (!(($category[1] != 0) AND ($year_budget_amount == 0) AND ($year_amount == 0)) OR $_HOAM_setting ['budget']['zero_amount']['enable']) {
				// Make separate lists for Income and Expense
				if ((int) $budget_category_class -> returnFlags ($category[0]) & (int) $_HOAM_setting ['budget']['flags']['category_income']) {
					$income_tbody .= $tbody;
					$current_period_income_amount += $current_period_amount;
					$year_total_income_amount += $year_amount;
					$year_budget_total_income_amount += $year_budget_amount;
				} else {
					$expense_tbody .= $tbody;
					$current_period_expense_amount += $current_period_amount;
					$year_total_expense_amount += $year_amount;
					$year_budget_total_expense_amount += $year_budget_amount;
				}
			}
		}

		$expense_tbody .= '<tr><td style="border-top: 1px solid black">&nbsp;</td>';
		$expense_tbody .= '<td style="border-top: 1px solid black">' . HOAM_returnCurrency ($current_period_expense_amount) . '</td>';
		$expense_tbody .= '<td style="border-top: 1px solid black">' . HOAM_returnCurrency ($year_budget_total_expense_amount * ($number_of_days / 365)) . '</td>';
		$current_expense_variance = $current_period_expense_amount - ($year_budget_total_expense_amount * ($number_of_days / 365));
		$expense_tbody .= '<td style="border-top: 1px solid black"';
		if ($current_expense_variance > 0) {
			$expense_tbody .= ' class="warning">(' . HOAM_returnCurrency ($current_expense_variance) . ')</td>';
		} elseif ($current_expense_variance < 0) {
			$expense_tbody .= ' class="complete">' . HOAM_returnCurrency (-$current_expense_variance) . '</td>';
		} else {
			$expense_tbody .= '>' . HOAM_returnCurrency ($current_expense_variance) . '</td>';
		}
		$expense_tbody .= '<td style="border-top: 1px solid black">' . HOAM_returnCurrency ($year_total_expense_amount) . '</td>';
		$expense_tbody .= '<td style="border-top: 1px solid black">' . HOAM_returnCurrency ($year_budget_total_expense_amount) . '</td>';
		$year_expense_variance = $year_total_expense_amount - $year_budget_total_expense_amount;
		$expense_tbody .= '<td style="border-top: 1px solid black"';
		if ($year_expense_variance > 0) {
			$expense_tbody .= ' class="warning">(' . HOAM_returnCurrency ($year_expense_variance) . ')</td>';
		} elseif ($year_expense_variance < 0) {
			$expense_tbody .= ' class="complete">' . HOAM_returnCurrency (-$year_expense_variance) . '</td>';
		} else {
			$expense_tbody .= '>' . HOAM_returnCurrency ($year_expense_variance) . '</td>';
		}
		$expense_tbody .= '</tbody>';

		$income_tbody .= '<tr><td style="border-top: 1px solid black">&nbsp;</td>';
		$income_tbody .= '<td style="border-top: 1px solid black">' . HOAM_returnCurrency ($current_period_income_amount) . '</td>';
		$income_tbody .= '<td style="border-top: 1px solid black">' . HOAM_returnCurrency ($year_budget_total_income_amount * ($number_of_days / 365)) . '</td>';
		$current_income_variance = $current_period_income_amount - ($year_budget_total_income_amount * ($number_of_days / 365));
		$income_tbody .= '<td style="border-top: 1px solid black"';
		if ($current_income_variance > 0) {
			$income_tbody .= ' class="complete">' . HOAM_returnCurrency ($current_income_variance) . '</td>';
		} elseif ($current_income_variance < 0) {
			$income_tbody .= ' class="warning">(' . HOAM_returnCurrency (-$current_income_variance) . ')</td>';
		} else {
			$income_tbody .= '>' . HOAM_returnCurrency ($current_income_variance) . '</td>';
		}
		$income_tbody .= '<td style="border-top: 1px solid black">' . HOAM_returnCurrency ($year_total_income_amount) . '</td>';
		$income_tbody .= '<td style="border-top: 1px solid black">' . HOAM_returnCurrency ($year_budget_total_income_amount) . '</td>';
		$year_income_variance = $year_total_income_amount - $year_budget_total_income_amount;
		$income_tbody .= '<td style="border-top: 1px solid black"';
		if ($year_income_variance > 0) {
			$income_tbody .= ' class="complete">' . HOAM_returnCurrency ($year_income_variance) . '</td>';
		} elseif ($year_income_variance < 0) {
			$income_tbody .= ' class="warning">(' . HOAM_returnCurrency (-$year_income_variance) . ')</td>';
		} else {
			$income_tbody .= '>' . HOAM_returnCurrency ($year_income_variance) . '</td>';
		}
		$income_tbody .= '</tbody>';

		$comparison_tbody = '<tbody id="budget|view|comparison">';
		$comparison_tbody .= '<tr><td style="font-weight: bold; text-align: left">' . $_HOAM_language ['field_names']['budget']['budget']['net'] . '</td><td colspan="6">&nbsp;</td></tr>';
		$comparison_tbody .= '<tr>';
		$comparison_tbody .= '<td style="border-top: 1px solid black">&nbsp;</td>';
		$net_current_actual = $current_period_expense_amount - $current_period_income_amount;
		$comparison_tbody .= '<td style="border-top: 1px solid black"';
		if ($net_current_actual > 0) {
			$comparison_tbody .= ' class="warning">(' . HOAM_returnCurrency ($net_current_actual) . ')</td>';
		} elseif ($net_current_actual < 0) {
			$comparison_tbody .= ' class="complete">' . HOAM_returnCurrency (-$net_current_actual) . '</td>';
		} else {
			$comparison_tbody .= '>' . HOAM_returnCurrency ($net_current_actual) . '</td>';
		}
		$net_current_budget = ($year_budget_total_expense_amount * ($number_of_days / 365)) - ($year_budget_total_income_amount * ($number_of_days / 365));
		$comparison_tbody .= '<td style="border-top: 1px solid black"';
		if ($net_current_budget > 0) {
			$comparison_tbody .= ' class="warning">(' . HOAM_returnCurrency ($net_current_budget) . ')</td>';
		} elseif ($net_current_budget < 0) {
			$comparison_tbody .= ' class="complete">' . HOAM_returnCurrency (-$net_current_budget) . '</td>';
		} else {
			$comparison_tbody .= '>' . HOAM_returnCurrency ($net_current_budget) . '</td>';
		}
		$net_current_variance = $current_income_variance - $current_expense_variance;
		$comparison_tbody .= '<td style="border-top: 1px solid black"';
		if ($net_current_variance > 0) {
			$comparison_tbody .= ' class="warning">(' . HOAM_returnCurrency ($net_current_variance) . ')</td>';
		} elseif ($net_current_variance < 0) {
			$comparison_tbody .= ' class="complete">' . HOAM_returnCurrency (-$net_current_variance) . '</td>';
		} else {
			$comparison_tbody .= '>' . HOAM_returnCurrency ($net_current_variance) . '</td>';
		}
		$net_year_actual = $year_total_expense_amount - $year_total_income_amount;
		$comparison_tbody .= '<td style="border-top: 1px solid black"';
		if ($net_year_actual > 0) {
			$comparison_tbody .= ' class="warning">(' . HOAM_returnCurrency ($net_year_actual) . ')</td>';
		} elseif ($net_year_actual < 0) {
			$comparison_tbody .= ' class="complete">' . HOAM_returnCurrency (-$net_year_actual) . '</td>';
		} else {
			$comparison_tbody .= '>' . HOAM_returnCurrency ($net_year_actual) . '</td>';
		}
		$net_year_budget = $year_budget_total_expense_amount - $year_budget_total_income_amount;
		$comparison_tbody .= '<td style="border-top: 1px solid black"';
		if ($net_year_budget > 0) {
			$comparison_tbody .= ' class="warning">(' . HOAM_returnCurrency ($net_year_budget) . ')</td>';
		} elseif ($net_year_budget < 0) {
			$comparison_tbody .= ' class="complete">' . HOAM_returnCurrency (-$net_year_budget) . '</td>';
		} else {
			$comparison_tbody .= '>' . HOAM_returnCurrency ($net_year_budget) . '</td>';
		}
		$net_year_variance = $year_income_variance - $year_expense_variance;
		$comparison_tbody .= '<td style="border-top: 1px solid black"';
		if ($net_year_variance > 0) {
			$comparison_tbody .= ' class="complete">' . HOAM_returnCurrency ($net_year_variance) . '</td>';
		} elseif ($net_year_variance < 0) {
			$comparison_tbody .= ' class="warning">(' . HOAM_returnCurrency (-$net_year_variance) . ')</td>';
		} else {
			$comparison_tbody .= '>' . HOAM_returnCurrency ($net_year_variance) . '</td>';
		}
		$comparison_tbody .= '</tr>';
		$comparison_tbody .= '</tbody>';

		$HTML .= $colgroup . $thead . $income_tbody . $expense_tbody . $comparison_tbody;
	} else {
		$HTML .= $colgroup . $thead . '<tbody><tr><td colspan="6">' . $_HOAM_language ['errors']['generic']['date_out_of_range'] . '</td></tr></tbody>';
	}
	$HTML .= '</table>';
	
	return $HTML;
}

?>