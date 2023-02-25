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

function HOAM_reportMonthly () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting;

	require (HOAM_PATH_FUNCTION . 'image.php');

	$monthly_report = '';
	$monthly_report_details = array ();
	$_HOAM_log -> add ('Creating monthly report.', HOAM_MESSAGE_NOTICE);
	$_HOAM_log -> add ('Reporting monthly vendor invoices.', HOAM_MESSAGE_NOTICE);
	$HTML = '<h2>Creating monthly report</h2>';

	// Gather budget information and report
	require_once (HOAM_PATH_FUNCTION . 'budget.php');
	$budget_class = new HOAM_budget ('budget');
	$budget_category_class = new HOAM_budget_category ('budget_category');
	$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$insurance_class = new HOAM_insurance ('insurance');
	$lot_class = new HOAM_lot ('lots');

	// Need to look at why having these query the income account rather than
	// vendor ID gives an incorrect result.
	$monthly_report_details['amount_assessed'] = $budget_class -> itemQuery ("SELECT SUM(amount) FROM budget WHERE amount > 0 AND vendor_id='" . $_HOAM_setting ['budget']['ids']['organization'] . "' AND datecreated >='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') - 1, 1, date ('Y'))) . "' AND datecreated <= '" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) . "'");
	$monthly_report_details['amount_collected'] = $budget_class -> itemQuery ("SELECT SUM(amount) FROM budget WHERE amount > 0 AND vendor_id='" . $_HOAM_setting ['budget']['ids']['organization'] . "' AND datecreated >='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') - 1, 1, date ('Y'))) . "' AND datecreated <= '" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) . "' AND dateposted IS NOT NULL");
//	$monthly_report_details['amount_spent'] = $budget_class -> itemQuery ("SELECT SUM(amount) FROM budget WHERE account_id='" . $_HOAM_setting ['budget']['account']['expense'] . "' AND datecreated >='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') - 1, 1, date ('Y'))) . "' AND datecreated < '" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) . "'");
//	$monthly_report_details['amount_spent_real'] = $budget_class -> itemQuery ("SELECT SUM(amount) FROM budget WHERE account_id='" . $_HOAM_setting ['budget']['account']['expense'] . "' AND datecreated >='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') - 1, 1, date ('Y'))) . "' AND datecreated < '" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) . "' AND dateposted IS NOT NULL");

	$total_receivables = 0;
	$total_prepay = 0;
	
	if (!empty ($lot_class -> listStreets ())) {
		// We're performing this check in case no streets have been configured.
		// This is extremely unlikely, however it will cause a MySQL error if
		// the maintenance script is set to run but no lots have been entered
		// yet (fresh install).
		$open_customer_list = $budget_class -> listCustomersWithOpenCharges ();
		if (is_array ($open_customer_list)) {
			$open_charge_count = count ($open_customer_list);
			foreach ($open_customer_list as $customer) {
				$amount = $budget_class -> returnTotalAmountDue ($customer);
				if ($amount >= 0) {
					$total_receivables += $amount;
				} else {
					$total_prepay += -$amount;
				}
			} 
		}
	}
	
#	$paid_invoice_list = $budget_invoice_class -> listSimpleQuery ("SELECT id FROM budget_invoice WHERE customer_id='" . $_HOAM_setting['budget']['ids']['organization'] . "' AND datecreated>='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') -1, 1, date ('Y'))) . "' AND datecreated<='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) . "' ORDER BY dateinvoice ASC");
	$invoice_list = $budget_invoice_class -> listSimpleQuery ("SELECT id FROM budget_invoice WHERE customer_id='" . $_HOAM_setting['budget']['ids']['organization'] . "' AND datecreated>='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') -1, 1, date ('Y'))) . "' AND datecreated<='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) . "' ORDER BY dateinvoice ASC");
	$paid_invoice_list = $unpaid_invoice_list = array ();
	if (is_array ($invoice_list)) {
		$monthly_report_details['amount_spent'] = 0;
		foreach ($invoice_list as $invoice) {
			$invoice_amount = $budget_invoice_class -> returnTotalAmount ($invoice);
			$invoice_amount_due = $budget_invoice_class -> returnTotalAmountDue ($invoice);
			$monthly_report_details['amount_spent'] += $invoice_amount;
			if ($invoice_amount_due <= 0) {
				array_push ($paid_invoice_list, $invoice);
				$monthly_report_details['amount_spent_real'] += $invoice_amount - $invoice_amount_due;
			} else {
				array_push ($unpaid_invoice_list, $invoice);
			}
		}
	}
	if (is_array ($paid_invoice_list)) {
		$monthly_report_details['paid_invoices_amount'] = 0;
		$monthly_report_details['paid_invoices'] = '<table class="center">';
		$monthly_report_details['paid_invoices'] .= '<caption>' . count ($paid_invoice_list) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</caption>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['date'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['invoice']['number'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['vendor']['name'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['check'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['amount'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$monthly_report_details['paid_invoices'] .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['paid_invoices'] .= '<tbody>';
		foreach ($paid_invoice_list as $invoice) {
			$monthly_report_details['paid_invoices'] .= '<tr>';
			$monthly_report_details['paid_invoices'] .= '<td>' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_invoice_class -> returnInvoiceDate ($invoice))) . '</td>';
			$monthly_report_details['paid_invoices'] .= '<td>' . $budget_invoice_class -> returnNumber ($invoice) . '</td>';
			$monthly_report_details['paid_invoices'] .= '<td>' . stripslashes (html_encode ($budget_vendor_class -> returnName ($budget_invoice_class -> returnVendorId ($invoice)))) . '</td>';
			$invoice_charges = $budget_class -> listChargesOnInvoice ($invoice);
			$invoice_amount = 0;
				foreach ($invoice_charges as $charge) {
				$charge_amount = $budget_class -> returnAmount ($charge);
				if ($charge_amount > 0) {
					$invoice_amount += $charge_amount;
				}
			}
			$checknum = $budget_class -> returnCheckNumber ($charge);
			if ($checknum != NULL) { 
				$monthly_report_details['paid_invoices'] .= '<td>' . stripslashes (html_encode ($checknum)) . '</td>';
			} else {
				$monthly_report_details['paid_invoices'] .= '<td>' . stripslashes (html_encode ($budget_class -> returnMemo ($charge))) . '</td>';
			}
			$monthly_report_details['paid_invoices'] .= '<td class="currency">' . HOAM_returnCurrency ($invoice_amount) . '</td>';
			$monthly_report_details['paid_invoices_amount'] += $invoice_amount;
			$monthly_report_details['paid_invoices'] .= '</tr>';
		}
		$monthly_report_details['paid_invoices'] .= '</tbody>';
		$monthly_report_details['paid_invoices'] .= '</table>';
	} else {
		$monthly_report_details['paid_invoices'] .= ucfirst ($_HOAM_language ['common']['none']) . '.';
	}

	$unpaid_invoice_list = $budget_invoice_class -> listSimpleQuery ("SELECT DISTINCT(budget_invoice.id) FROM budget, budget_invoice WHERE budget_invoice.id = budget.invoice_id AND budget.dateposted IS NULL AND ~budget.flags & 256 AND budget_invoice.customer_id='" . $_HOAM_setting ['budget']['ids']['organization'] . "'");
	if (is_array ($unpaid_invoice_list)) {
		$monthly_report_details['unpaid_invoices_amount'] = 0;
		$monthly_report_details['unpaid_invoices'] = '<table class="center">';
		$monthly_report_details['unpaid_invoices'] .= '<caption>' . count ($unpaid_invoice_list) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</caption>';
		$monthly_report_details['unpaid_invoices'] .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['unpaid_invoices'] .= '<tbody>';
		foreach ($unpaid_invoice_list as $invoice) {
			$monthly_report_details['unpaid_invoices'] .= '<tr>';
			$monthly_report_details['unpaid_invoices'] .= '<td>' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_invoice_class -> returnInvoiceDate ($invoice))) . '</td>';
			$monthly_report_details['unpaid_invoices'] .= '<td>' . $budget_invoice_class -> returnNumber ($invoice) . '</td>';
			$monthly_report_details['unpaid_invoices'] .= '<td>' . stripslashes (html_encode ($budget_vendor_class -> returnName ($budget_invoice_class -> returnVendorId ($invoice)))) . '</td>';
			$monthly_report_details['unpaid_invoices'] .= '<td>' . $_HOAM_language ['common']['na'] . '</td>';
			$invoice_charges = $budget_class -> listChargesOnInvoice ($invoice);
			$invoice_amount = 0;
			foreach ($invoice_charges as $charge) {
				$charge_amount = $budget_class -> returnAmount ($charge);
				if ($charge_amount > 0) {
					$invoice_amount += $charge_amount;
				}
			}
			$monthly_report_details['unpaid_invoices'] .= '<td class="currency">' . HOAM_returnCurrency ($invoice_amount) . '</td>';
			$monthly_report_details['unpaid_invoices_amount'] += $invoice_amount;
			$monthly_report_details['unpaid_invoices'] .= '</tr>';
		}
		$monthly_report_details['unpaid_invoices'] .= '</tbody>';
		$monthly_report_details['unpaid_invoices'] .= '</table>';
	} else {
		$monthly_report_details['unpaid_invoices'] = ucfirst ($_HOAM_language ['common']['none']) . '.';
	}

	// We're reusing the $paid_invoice_list generated earlier to extract the
	// categories and amounts paid.
	$category_list = array ();
	foreach ($paid_invoice_list as $invoice) {
		$charge_list = $budget_class -> listChargesOnInvoice ($invoice);
		foreach ($charge_list as $charge) {
			$category = $budget_class -> returnCategoryId ($charge);
			if ($budget_class -> returnAmount ($charge) > 0) {
				$category_list [$category] += $budget_class -> returnAmount ($charge);
			}
		}
	}
	if (!empty ($category_list)) {
		$monthly_report_details['category_expenses_chart'] = array ();
		arsort ($category_list, SORT_NUMERIC);
		$monthly_report_details['category_expenses'] .= '<table class="center">';
		$monthly_report_details['category_expenses'] .= '<caption>' . count ($category_list) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</caption>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['category'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['amount'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['percent'] = 'Percent &plusmn;' . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$monthly_report_details['category_expenses'] .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['category_expenses'] .= '<tbody>';
		$categories = array_keys ($category_list);
		foreach ($categories as $category) {
			$monthly_report_details['category_expenses'] .= '<tr>';
			$monthly_report_details['category_expenses'] .= '<td>' . $budget_category_class -> returnName ($category) . '</td>';
			$monthly_report_details['category_expenses'] .= '<td class="currency">' . HOAM_returnCurrency ($category_list[$category]) . '</td>';
			$monthly_report_details['category_expenses'] .= '<td>' . number_format (($category_list[$category] / $monthly_report_details['paid_invoices_amount']) * 100, 1) . '%</td>';
			$monthly_report_details['category_expenses'] .= '</tr>';
			$monthly_report_details['category_expenses_chart'] += array ($budget_category_class -> returnName ($category) => number_format (($category_list[$category] / $monthly_report_details['paid_invoices_amount']) * 100, 1));
		}
		$monthly_report_details['category_expenses'] .= '</tbody>';
		$monthly_report_details['category_expenses'] .= '</table>';
		$monthly_report_details['category_expenses'] = '<br /><center>' . HOAM_piechart ($monthly_report_details['category_expenses_chart'], 140, 140, 130, array ('fontsize' => 15)) . '<br /></center>' . $monthly_report_details['category_expenses']; 
	} else {
		$monthly_report_details['category_expenses'] = ucfirst ($_HOAM_language ['common']['none']) . '.';
	}

	// We're going to create the same type of category list for
	// assessments as we did for expenses.
	$category_list = array ();
	$assessment_list = $budget_class -> listSimpleQuery ("SELECT * FROM budget WHERE amount > 0 AND vendor_id='" . $_HOAM_setting ['budget']['ids']['organization'] . "' AND datecreated>='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') -1, 1, date ('Y'))) . "' AND datecreated<'" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) . "' ORDER BY datecreated ASC, customer_id");
	if (is_array ($assessment_list)) {
		$monthly_report_details['assessments_charged'] = '<table class="center">';
		$monthly_report_details['assessments_charged'] .= '<caption>' . count ($assessment_list) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</caption>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['date'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['lot']['residence'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['name'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['memo'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['amount'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$monthly_report_details['assessments_charged'] .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['assessments_charged'] .= '<tbody>';
		foreach ($assessment_list as $assessment) {
			$category = $budget_class -> returnCategoryId ($assessment);
			if ($budget_class -> returnAmount ($assessment) > 0) {
				$category_list [$category] += $budget_class -> returnAmount ($assessment);
			}
			$monthly_report_details['assessments_charged'] .= '<tr>';
			$monthly_report_details['assessments_charged'] .= '<td>' . date ($_HOAM_country ['date']['format_short'], strtotime ($budget_class -> returnDateCreated ($assessment))) . '</td>';
			$monthly_report_details['assessments_charged'] .= '<td>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($budget_class -> returnCustomerId ($assessment))) . '</td>';
			$monthly_report_details['assessments_charged'] .= '<td>' . stripslashes (html_encode ($homeowner_class -> returnName ($budget_class -> returnCustomerId ($assessment)))) . '</td>';
			$monthly_report_details['assessments_charged'] .= '<td>' . $budget_class -> returnMemo ($assessment) . '</td>';
			$monthly_report_details['assessments_charged'] .= '<td class="currency">' . HOAM_returnCurrency ($budget_class -> returnAmount ($assessment)) . '</td>';
			$monthly_report_details['assessments_charged'] .= '</tr>';
		}
		$monthly_report_details['assessments_charged'] .= '</tbody>';
		$monthly_report_details['assessments_charged'] .= '</table>';
	} else {
		$monthly_report_details['assessments_charged'] = ucfirst ($_HOAM_language ['common']['none']) . '.';
	}

	if (!empty ($category_list)) {
		$monthly_report_details['category_assessments_chart'] = array ();
		arsort ($category_list, SORT_NUMERIC);
		$monthly_report_details['category_assessments'] = '<table class="center">';
		$monthly_report_details['category_assessments'] .= '<caption>' . count ($category_list) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</caption>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['category'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['amount'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['percent'] = 'Percent &plusmn;' . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$monthly_report_details['category_assessments'] .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['category_assessments'] .= '<tbody>';
		$categories = array_keys ($category_list);
		foreach ($categories as $category) {
			$monthly_report_details['category_assessments'] .= '<tr>';
			$monthly_report_details['category_assessments'] .= '<td>' . $budget_category_class -> returnName ($category) . '</td>';
			$monthly_report_details['category_assessments'] .= '<td class="currency">' . HOAM_returnCurrency ($category_list[$category]) . '</td>';
			$monthly_report_details['category_assessments'] .= '<td>' . number_format (($category_list[$category] / $monthly_report_details['amount_assessed']) * 100, 1) . '%</td>';
			$monthly_report_details['category_assessments'] .= '</tr>';
			$monthly_report_details['category_assessments_chart'] += array ($budget_category_class -> returnName ($category) => number_format ((($category_list[$category] / $monthly_report_details['amount_assessed']) * 100), 1));
		}
		$monthly_report_details['category_assessments'] .= '</tbody>';
		$monthly_report_details['category_assessments'] .= '</table>';
		$monthly_report_details['category_assessments'] = '<br /><center>' . HOAM_piechart ($monthly_report_details['category_assessments_chart'], 140, 140, 130, array ('fontsize' => 15)) . '<br /></center>' . $monthly_report_details['category_assessments']; 
	} else {
		$monthly_report_details['category_assessments'] = ucfirst ($_HOAM_language ['common']['none']) . '.';
	}

	// Report information for the prior month's budget
	$current_budget = HOAM_budgetFindCurrentBudget (date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') -1, 1, date ('Y'))));
	if ($current_budget) {
		$monthly_report_details['income_statement'] = HOAM_budgetIncomeStatement ($current_budget, date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') -1, 1, date ('Y'))), date ($_HOAM_country ['date']['format_mysql'], mktime (23, 59, 59, date ('m'), 0, date ('Y'))));
		$monthly_report_details['budget_statement'] = HOAM_budgetView ($current_budget, date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') -1, 1, date ('Y'))), date ($_HOAM_country ['date']['format_mysql'], mktime (23, 59, 59, date ('m'), 0, date ('Y'))));
	} else {
		$monthly_report_details['income_statement'] = $_HOAM_language ['errors']['report']['could_not_determine'];
		$monthly_report_details['budget_statement'] = $_HOAM_language ['errors']['report']['could_not_determine'];
	}

	// This section is dependent on whether there are any owners at their 3rd
	// notice.
	$query = 'SELECT * FROM homeowners WHERE budget_flags & ' . $_HOAM_setting ['budget']['flags']['late_default'];
	$default_list = $homeowner_class -> listQuery ($query);
    if (is_array ($default_list)) {
		$monthly_report_details['are_owners_to_be_sent'] = '<li>\'\'\'Delinquent Accounts for Attorney\'\'\'</li>';
		
		$monthly_report_details['owners_for_attorney'] = '<h3>Delinquent Accounts for Attorney</h3>';
		$monthly_report_details['owners_for_attorney'] .= $_HOAM_language ['field_names']['reports']['send_attorney'];
    	$monthly_report_details['owners_for_attorney'] .= '<table class="center">';
		$monthly_report_details['owners_for_attorney'] .= '<colgroup>';
		$monthly_report_details['owners_for_attorney'] .= '<col width="35%" />';
		$monthly_report_details['owners_for_attorney'] .= '<col width="35%" />';
		$monthly_report_details['owners_for_attorney'] .= '<col width="15%" />';
		$monthly_report_details['owners_for_attorney'] .= '<col width="15%" />';
		$monthly_report_details['owners_for_attorney'] .= '</colgroup>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['lot']['residence'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['name'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['amount'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['last_payment'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$monthly_report_details['owners_for_attorney'] .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['owners_for_attorney'] .= '<tbody class="default_list">';
		foreach ($default_list as $owner) {
			if ((int) $owner['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['late_attorney']) {
				$monthly_report_details['owners_for_attorney'] .= '<tr style="font-weight: bold">';
			} else {
				$monthly_report_details['owners_for_attorney'] .= '<tr>';
			}
			//$monthly_report_details['owners_for_attorney'] .= '<td>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($owner['id'])) . '</td>';
			$monthly_report_details['owners_for_attorney'] .= '<td>' . HOAM_returnAddress ($owner['lot_id']) . '</td>';
			$monthly_report_details['owners_for_attorney'] .= '<td>' . stripslashes (htmlentities ($owner['name'])) . '</td>';
			$monthly_report_details['owners_for_attorney'] .= '<td>' . HOAM_returnCurrency ($budget_class -> returnTotalAmountDue ($owner['id'])) . '</td>';
			$last_payment = $budget_class -> returnLastPayment ($owner['id']);
			$monthly_report_details['owners_for_attorney'] .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDatePosted ($last_payment))) . '</td>';
			$monthly_report_details['owners_for_attorney'] .= '</tr>';
		}
		$monthly_report_details['owners_for_attorney'] .= '</tbody>';
		$monthly_report_details['owners_for_attorney'] .= '</table>';
	} else {
		$monthly_report_details['are_owners_to_be_sent'] = '';
    	$monthly_report_details['owners_for_attorney'] = '';
	}

	// This section is dependent on whether insurance policies tracking is
	// enabled, and whether any are expiring
	if ($_HOAM_setting ['budget']['insurance']['enable']) {
		require_once (HOAM_PATH_FUNCTION . 'insurance.php');

		$monthly_report_details['is_insurance_tracked'] = '<li>' . $_HOAM_language ['field_names']['reports']['insurance']['policies'];
		$monthly_report_details['is_insurance_tracked'] .= '<ol><li>' . $_HOAM_language ['field_names']['reports']['insurance']['current'] . '</li>';

		$monthly_report_details['insurance_expiring'] = '<h2 style="page-break-before: always">' . $_HOAM_language ['field_names']['reports']['insurance']['policies'] . '</h2>';
		$monthly_report_details['insurance_expiring'] .= '<h3>' . $_HOAM_language ['field_names']['reports']['insurance']['current'] . '</h3>';
		$monthly_report_details['insurance_expiring'] .= HOAM_insuranceList ();
		$days_expiring = new DateTime ();
		$days_expiring -> modify ('+' . $_HOAM_setting ['budget']['insurance']['days'] . ' day');
		$policy_list = $insurance_class -> listExpiringId ($days_expiring -> format ($_HOAM_country ['date']['format_mysql']));
		if (is_array ($policy_list)) {
			$monthly_report_details['is_insurance_tracked'] .= '<li>\'\'\'' . $_HOAM_language ['field_names']['reports']['insurance']['expiring'] . '\'\'\'</li>';
			$monthly_report_details['insurance_expiring'] .= '<h3>' . $_HOAM_language ['field_names']['reports']['insurance']['expiring'] . '</h3>';
			$monthly_report_details['insurance_expiring'] .= HOAM_insuranceExpiring ();
		}
		$monthly_report_details['is_insurance_tracked'] .= '</ol></li>';
	} else {
		$monthly_report_details['is_insurance_tracked'] = '';	
		$monthly_report_details['insurance_expiring'] = '';
	}
	
	// Gather recent violation information and report
	$violation_class = new HOAM_violation ('violations');
	$violation_category_class = new HOAM_violation_category ('violation_category');
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');
	$violation_list = $violation_class -> listQuery ("SELECT * from violations WHERE datecreated>='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') -1, 1, date ('Y'))) . "' AND datecreated<='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) . "' ORDER BY dateviolation ASC");
	if (is_array ($violation_list)) {
		$monthly_report_details['violation_list'] = '<table class="center">';
		$monthly_report_details['violation_list'] .= '<caption>' . count ($violation_list) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</caption>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['date'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['lot']['residence'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['homeowner'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['severity'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['category']['name'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$monthly_report_details['violation_list'] .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['violation_list'] .= '<tbody>';
		// Store the violation categories so we can later report that back.
		$violation_category_list = array ();
		foreach ($violation_list as $violation) {
			$monthly_report_details['violation_list'] .= '<tr>';
			$monthly_report_details['violation_list'] .= '<td>' . date ($_HOAM_country ['date']['format_short'], strtotime ($violation ['dateviolation'])) . '</td>';
			$monthly_report_details['violation_list'] .= '<td>' . HOAM_returnAddress ($violation ['lot_id']) . '</td>';
			$monthly_report_details['violation_list'] .= '<td>' . stripslashes (html_encode ($homeowner_class -> returnName ($homeowner_class -> returnOwner ($violation ['dateviolation'], $violation['lot_id'])))) . '</td>';
			$monthly_report_details['violation_list'] .= '<td>' . $violation_severity_class -> returnSeverity ($violation['severity']) . '</td>';
			$monthly_report_details['violation_list'] .= '<td>';
			$category_list = unserialize ($violation['category']);
			foreach ($category_list as $category) {
				$monthly_report_details['violation_list'] .= $violation_category_class -> returnCategory ($category) . '<br />';
				$violation_category_list [$category] += 1;
			}
			$monthly_report_details['violation_list'] .= '</td>';
			$monthly_report_details['violation_list'] .= '</tr>';
		}
		$monthly_report_details['violation_list'] .= '</tbody>';
		$monthly_report_details['violation_list'] .= '</table>';
	} else {
		$monthly_report_details['violation_list'] = ucfirst ($_HOAM_language ['common']['none']) . '.';
	}

	// Gather unresolved violation information and report
	$violation_list = $violation_class -> listQuery ("SELECT * from violations WHERE datecreated<'" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') -1, 1, date ('Y'))) . "' AND (dateresolution IS NULL OR dateresolution = '1970-01-01') ORDER BY dateviolation ASC");
	if (is_array ($violation_list)) {
		$monthly_report_details['violation_unresolved'] = '<table class="center">';
		$monthly_report_details['violation_unresolved'] .= '<caption>' . count ($violation_list) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</caption>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['date'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['lot']['residence'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['homeowner'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['severity'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['category']['name'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$monthly_report_details['violation_unresolved'] .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['violation_unresolved'] .= '<tbody>';
		// Store the violation categories so we can later report that back.
		foreach ($violation_list as $violation) {
			$monthly_report_details['violation_unresolved'] .= '<tr>';
			$monthly_report_details['violation_unresolved'] .= '<td>' . date ($_HOAM_country ['date']['format_short'], strtotime ($violation ['dateviolation'])) . '</td>';
			$monthly_report_details['violation_unresolved'] .= '<td>' . HOAM_returnAddress ($violation ['lot_id']) . '</td>';
			$monthly_report_details['violation_unresolved'] .= '<td>' . stripslashes (html_encode ($homeowner_class -> returnName ($homeowner_class -> returnOwner ($violation ['dateviolation'], $violation['lot_id'])))) . '</td>';
			$monthly_report_details['violation_unresolved'] .= '<td>' . $violation_severity_class -> returnSeverity ($violation['severity']) . '</td>';
			$monthly_report_details['violation_unresolved'] .= '<td>';
			$category_list = unserialize ($violation['category']);
			$monthly_report_details['violation_unresolved'] .= '<ul>';
			foreach ($category_list as $category) {
				$monthly_report_details['violation_unresolved'] .= '<li>' . $violation_category_class -> returnCategory ($category) . '</li>';
			}
			$monthly_report_details['violation_unresolved'] .= '</ul>';
			$monthly_report_details['violation_unresolved'] .= '</td>';
			$monthly_report_details['violation_unresolved'] .= '</tr>';
		}
		$monthly_report_details['violation_unresolved'] .= '</tbody>';
		$monthly_report_details['violation_unresolved'] .= '</table>';
	} else {
		$monthly_report_details['violation_unresolved'] = ucfirst ($_HOAM_language ['common']['none']) . '.';
	}

	if (!empty ($violation_category_list)) {
		$monthly_report_details['violation_category_chart'] = array ();
		arsort ($violation_category_list, SORT_NUMERIC);
		$monthly_report_details['violation_category'] = '<table class="center">';
		$monthly_report_details['violation_category'] .= '<caption>' . count ($violation_category_list) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</caption>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['category'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['amount'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['percent'] = 'Percent &plusmn;' . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$monthly_report_details['violation_category'] .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['violation_category'] .= '<tbody>';
		$categories = array_keys ($violation_category_list);
		foreach ($categories as $category) {
			$monthly_report_details['violation_category'] .= '<tr>';
			$monthly_report_details['violation_category'] .= '<td>' . $violation_category_class -> returnCategory ($category) . '</td>';
			$monthly_report_details['violation_category'] .= '<td>' . $violation_category_list[$category] . '</td>';
			$monthly_report_details['violation_category'] .= '<td>' . number_format (($violation_category_list[$category] / count ($violation_list)) * 100, 1) . '%</td>';
			$monthly_report_details['violation_category'] .= '</tr>';
			$monthly_report_details['violation_category_chart'] += array ($violation_category_class -> returnCategory ($category) => number_format (($violation_category_list[$category] / count ($violation_list)) * 100, 1));
		}
		$monthly_report_details['violation_category'] .= '</tbody>';
		$monthly_report_details['violation_category'] .= '</table>';
		$monthly_report_details['violation_category'] .= 'Please note that the total percentage may be greater than 100%% because more than one violation category may be assigned for each violation notice.';
		$monthly_report_details['violation_category'] = '<br /><center>' . HOAM_piechart ($monthly_report_details['violation_category_chart'], 140, 140, 130, array ('fontsize' => 15)) . '<br /></center>' . $monthly_report_details['violation_category']; 
	} else {
		$monthly_report_details['violation_category'] = ucfirst ($_HOAM_language ['common']['none']) . '.';
	}
		
	// Gather homeowner information and report
	require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
	$homeowner_list = $homeowner_class -> listQuery ("SELECT * FROM homeowners WHERE datecreated>='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') -1, 1, date ('Y'))) . "' AND datecreated<='" . date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))) . "' ORDER BY purchasedate ASC");
	if (is_array ($homeowner_list)) {
		$monthly_report_details['homeowner_list'] = '<table class="center">';
		$monthly_report_details['homeowner_list'] .= '<caption>' . count ($homeowner_list) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</caption>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['date'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['lot']['residence'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['name'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['date']['purchased'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$monthly_report_details['homeowner_list'] .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['homeowner_list'] .= '<tbody>';
		foreach ($homeowner_list as $homeowner) {
			$monthly_report_details['homeowner_list'] .= '<tr>';
			$monthly_report_details['homeowner_list'] .= '<td>' . date ($_HOAM_country ['date']['format_short'], strtotime ($homeowner ['datecreated'])) . '</td>';
			$monthly_report_details['homeowner_list'] .= '<td>' . HOAM_returnAddress ($homeowner ['lot_id']) . '</td>';
			$monthly_report_details['homeowner_list'] .= '<td>' . stripslashes (html_encode ($homeowner['name'])) . '</td>';
			$monthly_report_details['homeowner_list'] .= '<td>' . date ($_HOAM_country ['date']['format_short'], strtotime ($homeowner ['purchasedate'])) . '</td>';
			$monthly_report_details['violation_list'] .= '</tr>';
		}
		$monthly_report_details['homeowner_list'] .= '</tbody>';
		$monthly_report_details['homeowner_list'] .= '</table>';
	} else {
		$monthly_report_details['homeowner_list'] = ucfirst ($_HOAM_language ['common']['none']) . '.';
	}
	
	// Current work requests
	$work_request_class = new HOAM_work_request ('work_requests');
	$user_class = new HOAM_user ('users');
	$request_list = $work_request_class -> listDue (0, 0, $_HOAM_setting ['work_request']['flags']['new'] + $_HOAM_setting ['work_request']['flags']['assigned'] + $_HOAM_setting ['work_request']['flags']['in_progress'] + $_HOAM_setting ['work_request']['flags']['on_hold']);
	if (is_array ($request_list) AND (count ($request_list) > 0)) {
		$monthly_report_details['work_requests'] = '<table class="center">';
		$monthly_report_details['work_requests'] .= '<caption>' . count ($request_list) . ' ' . ucfirst ($_HOAM_language ['common']['entries']) . '</caption>';
		$colgroup = '<colgroup>';
		$colgroup .= '</colgroup>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['created'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['priority'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['status'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['date']['due'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['lot'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['title'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['requester'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$monthly_report_details['work_requests'] .=  $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$monthly_report_details['work_requests'] .= '<tbody>';
		foreach ($request_list as $request) {
			$request_info = $work_request_class -> ListAll ($request[0]);
			// We reassign it to itself, because the ListAll() function assumes you're retrieving multiple rows.
			$request_info = $request_info[0];
			$monthly_report_details['work_requests'] .= '<tr>';
			$monthly_report_details['work_requests'] .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($request_info['datecreated'])) . '</td>';
			switch (TRUE) {
				case ((int) $_HOAM_setting ['work_request']['flags']['urgent'] == (int) $request_info['priority']) :
					$monthly_report_details['work_requests'] .= '<td class="error">';
					break;
				case ((int) $_HOAM_setting ['work_request']['flags']['high'] == (int) $request_info['priority']) :
					$monthly_report_details['work_requests'] .= '<td class="warning">';
					break;
				default:
					$monthly_report_details['work_requests'] .= '<td>';
					break;
			}
			foreach ($_HOAM_language ['priority'] as $priority) {
				if ((int) $_HOAM_setting ['work_request']['flags'][$priority['value']] == (int) $request_info['priority']) {
					$monthly_report_details['work_requests'] .= $priority['name'];
					break;  
				}
			}
			$monthly_report_details['work_requests'] .= '</td>';
			$monthly_report_details['work_requests'] .= '<td>';
			foreach ($_HOAM_language ['status'] as $status) {
				if ((int) $_HOAM_setting ['work_request']['flags'][$status['value']] == (int) $request_info['status']) {
					$monthly_report_details['work_requests'] .= $status['name'];
					break;  
				}
			}
			$monthly_report_details['work_requests'] .= '</td>';
			$monthly_report_details['work_requests'] .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($request_info['datedue'])) . '</td>';
			switch ($request_info['lot_id']) {
				case 'common_area' :
					$monthly_report_details['work_requests'] .= '<td>' . $_HOAM_language ['field_names']['lot']['common']['area'] . '</td>';
					break;
				case 'common_clubhouse' :
					$monthly_report_details['work_requests'] .= '<td>' . $_HOAM_language ['field_names']['lot']['common']['clubhouse'] . '</td>';
					break;
				case 'common_pool' :
					$monthly_report_details['work_requests'] .= '<td>' . $_HOAM_language ['field_names']['lot']['common']['pool'] . '</td>';
					break;
				default:
					$monthly_report_details['work_requests'] .= '<td>' . HOAM_returnAddress ($request_info['lot_id']) . '</td>';
					break;
			}
			$monthly_report_details['work_requests'] .= '<td style="text-align: left">';
			if ($request_info['parent_id']) {
				$monthly_report_details['work_requests'] .= '&nbsp; &#9492;&#9472; &nbsp;';
			}
			$monthly_report_details['work_requests'] .= '' . HOAM_returnTruncatedString ($request_info['title'], 40) . '</td>';
			$monthly_report_details['work_requests'] .= '<td>' . $user_class -> returnAccountName ($request_info['user_id']) . '</td>';
			$monthly_report_details['work_requests'] .= '</tr>';
		}
		$monthly_report_details['work_requests'] .= '</tbody>';
		$monthly_report_details['work_requests'] .= '</table>';
	} else {
		$monthly_report_details['work_requests'] = ucfirst ($_HOAM_language ['common']['none']) . '.';
	}

	// Attachments in the last month
	$attachment_class = new HOAM_attachments ('attachments');
	$attachment_list = $attachment_class -> listByDateRange (date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m') - 1, 1, date ('Y'))), date ($_HOAM_country ['date']['format_mysql'], mktime (0, 0, 0, date ('m'), 1, date ('Y'))));
	if (is_array ($attachment_list) AND (count ($attachment_list) > 0)) {
		$monthly_report_details['attachments'] = '';
		foreach ($attachment_list as $attachment) {
			$monthly_report_details['attachments'] .= HOAM_attachmentReturnHTML ($attachment);
		}
	} else {
		$monthly_report_details['attachments'] = ucfirst ($_HOAM_language ['common']['none']) . '.';
	}

	// All of the information gathered, Fill in all of the report details.
	$monthly_report = sprintf ($_HOAM_language ['field_names']['reports']['monthly_narrative'],
	date ($_HOAM_country ['date']['format_long']),
	'{{ORG_NAME}}<br />' . date ('F Y', mktime (0, 0, 0, date ('m') - 1, 1, date ('Y'))),
	date ('F Y', mktime (0, 0, 0, date ('m') - 1, 1, date ('Y'))),
	$monthly_report_details['are_owners_to_be_sent'],
	$monthly_report_details['is_insurance_tracked'],
	HOAM_returnCurrency ($monthly_report_details['amount_spent']),
	HOAM_returnCurrency ($monthly_report_details['amount_spent_real']),
	HOAM_returnCurrency ($monthly_report_details['amount_assessed']),
	HOAM_returnCurrency ($monthly_report_details['amount_collected']),
	HOAM_returnCurrency ($total_receivables),
	HOAM_returnCurrency ($total_prepay),
	HOAM_returnCurrency ($total_receivables - $total_prepay),
	HOAM_returnCurrency ($monthly_report_details['paid_invoices_amount']),
	$monthly_report_details['paid_invoices'],
	$monthly_report_details['category_expenses'],
	HOAM_returnCurrency ($monthly_report_details['unpaid_invoices_amount']),
	$monthly_report_details['unpaid_invoices'],
	HOAM_returnCurrency ($monthly_report_details['amount_assessed']),
	$monthly_report_details['assessments_charged'],
	$monthly_report_details['category_assessments'],
	HOAM_budgetUnpaidAmount (),
	HOAM_budgetUnpaidStreet (),
	$monthly_report_details['income_statement'],
	$monthly_report_details['budget_statement'],
	$monthly_report_details['owners_for_attorney'],
	$monthly_report_details['insurance_expiring'],
	$monthly_report_details['violation_list'],
	$monthly_report_details['violation_unresolved'],
	$monthly_report_details['violation_category'],
	$monthly_report_details['homeowner_list'],
	HOAM_homeownerRentalStreet (),
	$monthly_report_details['work_requests'],
	$monthly_report_details['attachments']);

	return ($GLOBALS['wiki'] -> parse ($monthly_report));
}

?>
