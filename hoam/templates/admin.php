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

function HOAM_adminFinancial ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;
	
	$budget_class = new HOAM_budget ('budget');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$group_members_class = new HOAM_group_members ('group_members');
	$lot_class = new HOAM_lot ('lots');

	switch ($GLOBALS['global_url_array'][2]) {
		case ('account') :
			$_HOAM_log -> add ('Loading /admin/financial/account/ page.', HOAM_MESSAGE_DEBUG);
			$HTML = HOAM_adminBudgetAccount (TRUE);
			break;
		case ('budget') :
			$_HOAM_log -> add ('Loading /admin/financial/budget/ page.', HOAM_MESSAGE_DEBUG);
			$HTML = HOAM_adminBudgetBudget (TRUE);
			break;
		case ('category') :
			$_HOAM_log -> add ('Loading /admin/financial/category/ page.', HOAM_MESSAGE_DEBUG);
			$HTML = HOAM_adminBudgetCategory (TRUE);
			break;
		case ('homeowner') :
			$_HOAM_log -> add ('Loading /admin/financial/homeowner/ page.', HOAM_MESSAGE_DEBUG);
			$HTML = HOAM_adminBudgetHomeowner (TRUE);
			break;
		case ('insurance') :
			$_HOAM_log -> add ('Loading /admin/financial/insurance/ page.', HOAM_MESSAGE_DEBUG);
			$HTML = HOAM_adminInsurance (TRUE);
			break;
		case ('invoice') :
			$_HOAM_log -> add ('Loading /admin/financial/invoice/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['invoice']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetInvoiceJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['invoice']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
			
				// OK, we're checking to see that the invoice we're trying to edit is the one that we
				// have information on in the invoice_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				// Also, trusted_id will /only/ be set if an invoice was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['invoice_information']['trusted_id']))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['invoice_information'])) {
						unset ($_SESSION['invoice_information']);
					}
				}

				$HTML .= '<form id="new_invoice" action="/hoam/scripts/budget/budget_invoice_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" id="add" name="add">' . $_HOAM_language ['form_buttons']['budget']['invoice']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';
			
				$HTML .= '<div class="tabbertab" id="add_invoice" title="' . $_HOAM_language ['form_tabs']['budget']['invoice']['add'] . '">';
				$HTML .= '<div class="mandatory">';
  				$HTML .= '<table class="options" style="background: transparent !important">';
  				$HTML .= '<tbody class="nostripe">';
    			$HTML .= '<tr>';
      			$HTML .= '<td>';
				$HTML .= HOAM_formSelectMultiple ('budget|invoice|customer', $_HOAM_language ['field_names']['budget']['invoice']['customer'], 10);
				if (isset ($_SESSION['invoice_information']['trusted_budget|invoice|customer'])) {
					$default_customers = $_SESSION['invoice_information']['trusted_budget|invoice|customer'];
				} else {
					$default_customers = array ();
				}
				foreach ($default_customers as $customer) {
					$lot_id = $homeowner_class -> returnLotId ($customer);
					$lot = $lot_class -> listAll ($lot_id);
					$HTML .= '<option value="' . $customer . '">' . HOAM_returnAddress ($lot_id); 
					$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($customer))) . ')</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</td><td>';
				$HTML .= '<button type="button" id="to_all_button">&gt;&gt;</button><br />';
				$HTML .= '<button type="button" id="to_customer_button">&lt;&lt;</button>';
				$HTML .= '</td><td>';
				$HTML .= '<label for="budget|invoice|customer-all">' . $_HOAM_language ['field_names']['budget']['invoice']['customer-all'] . '</label>';
				$HTML .= '<select multiple="multiple" id="budget|invoice|customer-all" size="10">';
				$customer_list = $homeowner_class -> listCurrentHomeowners ();
				foreach ($customer_list as $customer) {
					if (!in_array ($customer, $default_customers)) {
						$lot_id = $homeowner_class -> returnLotId ($customer);
						$lot = $lot_class -> listAll ($lot_id);
						$lot = $lot[0];
						$HTML .= '<option value="' . $customer . '">' . HOAM_returnAddress ($lot_id);
						$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($customer))) . ')</option>';
					}
				}
				$HTML .= '</select>';
				$HTML .= '</td>';
				$HTML .= '</tr>';
				$HTML .= '</tbody>';
				$HTML .= '</table>';

				$HTML .= '<label for="budget|invoice|charges">' . $_HOAM_language ['field_names']['budget']['invoice']['charges'];
				$HTML .= HOAM_formDisplayHelp ('budget|invoice|charges');
				$HTML .= HOAM_formDisplayError ('budget|invoice|charges');
				$HTML .= '</label>';
				$HTML .= '<fieldset id="budget|invoice|charges">';
				$HTML .= '<input type="button" id="budget|invoice|charge|add" value="' . $_HOAM_language ['field_names']['budget']['invoice']['charge']['add'] . '" />';
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				
				$HTML .= '<div class="optional">';
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formCheckbox ('budget|invoice|recent', $_HOAM_language ['field_names']['budget']['invoice']['recent'], 1, $_SESSION ['invoice_information']['trusted_budget|invoice|recent'], 0);
				$HTML .= '</fieldset>';
				$HTML .= '<label for="budget|invoice|attachments">' . $_HOAM_language ['field_names']['budget']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('budget|invoice|attachments');
				$HTML .= HOAM_formDisplayError ('budget|invoice|attachments');
				$HTML .= '</label>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Invoice Add

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('payment') :
			$_HOAM_log -> add ('Loading /admin/financial/payment/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['payment']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetPaymentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');

				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['payment']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
			
				// OK, we're checking to see that the invoice we're trying to edit is the one that we
				// have information on in the invoice_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				// Also, trusted_id will /only/ be set if an invoice was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['payment_information']['trusted_id']))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['payment_information'])) {
						unset ($_SESSION['payment_information']);
					}
				}

				$HTML .= '<form id="new_payment" action="/hoam/scripts/budget/budget_payment_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['budget']['payment']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';
			
				$HTML .= '<div class="tabbertab" id="add_payment" title="' . $_HOAM_language ['form_tabs']['budget']['payment']['add'] . '">';

				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelect ('budget|payment|customer', $_HOAM_language ['field_names']['budget']['payment']['customer']);
				if (isset ($_SESSION['payment_information']['trusted_budget|payment|customer'])) {
					$default_customer = $_SESSION['payment_information']['trusted_budget|payment|customer'];
				} else {
					$default_customer = NULL;
				}
				// We're performing this check in case no streets have been configured.
				// This is extremely unlikely, however it will cause a MySQL error if
				// no lots have been entered yet (fresh install).
				if ($homeowner_class -> returnCountAll ()) {
					$customer_list = $budget_class -> listCustomers ();
					foreach ($customer_list as $customer) {
						$HTML .= '<option ';
						if ($customer == $default_customer) {
							$HTML .= 'selected="selected" ';
						}
						$total_amount_due = $budget_class -> returnTotalAmountDue ($customer);
						$HTML .= 'amount_due="' . $total_amount_due . '" ';
						if ($total_amount_due <= 0) {
							$HTML .= 'field="hide" ';
						}
						$HTML .= 'value="' . $customer . '">' . HOAM_returnAddress ($homeowner_class -> returnLotId ($customer));
						$HTML .= '&nbsp;' . HOAM_returnCurrency ($total_amount_due) .'&nbsp;(' . html_encode (stripslashes ($homeowner_class -> returnName ($customer))) . ')</option>';
					}
				}
				$HTML .= '</select>';
				$HTML .= '<input ';
				if ($_SESSION['payment_information']['trusted_budget|payment|customer|show-all'] == '1') {
					$HTML .= 'checked="checked" ';
				} 
				$HTML .= 'type="checkbox" id="budget|payment|customer|show-all" name="budget|payment|customer|show-all" value="1" />';
				$HTML .= '<label for="budget|payment|customer|show-all" style="display: inline">Show All Homeowners</label>';
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formField ('budget|payment|amount', $_HOAM_language ['field_names']['budget']['payment']['amount'], number_format ($_SESSION['payment_information']['trusted_budget|payment|amount'], 2), '', 10, 1);
				// Real hacky way to do this, should really be done through a
				// realtime Javascript popup, or at least cleaner integration.
				if ((isset ($_SESSION['error_class_item'])) AND ($_SESSION['error_class_item'] -> ShowCategory ('budget|payment|amount') != '')) {
					$HTML .= HOAM_formCheckbox ('budget|payment|amount|ignore', $_HOAM_language ['errors']['generic']['ignore'], 1, 0, 0);
				}
				$HTML .= HOAM_formSelect ('budget|payment|method', $_HOAM_language ['field_names']['budget']['payment']['method']);
				switch ($_SESSION['payment_information']['budget|payment|method']) {
					case 'check':
						$HTML .= '<option selected="selected" value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
						if ($_HOAM_setting ['budget']['paypal']['enable']) {
							$HTML .= '<option value="paypal">' . $_HOAM_language ['field_names']['budget']['payment']['paypal'] . '</option>';
						}
						$HTML .= '<option value="writeoff">' . $_HOAM_language ['field_names']['budget']['payment']['writeoff'] . '</option>';
						break;
					case 'paypal':
						$HTML .= '<option value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
						$HTML .= '<option selected="selected" value="paypal">' . $_HOAM_language ['field_names']['budget']['payment']['paypal'] . '</option>';
						$HTML .= '<option value="writeoff">' . $_HOAM_language ['field_names']['budget']['payment']['writeoff'] . '</option>';
						break;
					case 'writeoff':
						$HTML .= '<option value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
						if ($_HOAM_setting ['budget']['paypal']['enable']) {
							$HTML .= '<option value="paypal">' . $_HOAM_language ['field_names']['budget']['payment']['paypal'] . '</option>';
						}
						$HTML .= '<option selected="selected" value="writeoff">' . $_HOAM_language ['field_names']['budget']['payment']['writeoff'] . '</option>';
					break;
					default: 
						$HTML .= '<option selected="selected" value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
						if ($_HOAM_setting ['budget']['paypal']['enable']) {
							$HTML .= '<option value="paypal">' . $_HOAM_language ['field_names']['budget']['payment']['paypal'] . '</option>';
						}
						$HTML .= '<option value="writeoff">' . $_HOAM_language ['field_names']['budget']['payment']['writeoff'] . '</option>';
						break;
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formField ('budget|payment|checknum', $_HOAM_language ['field_names']['budget']['payment']['checknum'], $_SESSION['payment_information']['trusted_budget|payment|checknum'], '', $budget_class -> returnColumnSize ('checknum'), 1);
				if ((isset ($_SESSION['error_class_item'])) AND ($_SESSION['error_class_item'] -> ShowCategory ('budget|payment|checknum') != '')) {
					$HTML .= HOAM_formCheckbox ('budget|payment|checknum|ignore', $_HOAM_language ['errors']['generic']['ignore'], 1, 0, 0);
				}
				$HTML .= HOAM_formFieldHidden ('budget|payment|paypalid', $_HOAM_language ['field_names']['budget']['payment']['paypalid'], $_SESSION['payment_information']['trusted_budget|payment|checknum'], '', $budget_class -> returnColumnSize ('checknum'), 1);
				$HTML .= HOAM_formSelectHidden ('budget|payment|writeoff', $_HOAM_language ['field_names']['budget']['payment']['writeoff']); 
				switch ($_SESSION['payment_information']['budget|payment|writeoff']) {
					case 'attorney':
						$HTML .= '<option selected="selected" value="attorney">' . $_HOAM_language ['field_names']['budget']['writeoff']['attorney'] . '</option>';
						$HTML .= '<option value="board">' . $_HOAM_language ['field_names']['budget']['writeoff']['board'] . '</option>';
						$HTML .= '<option value="foreclosed">' . $_HOAM_language ['field_names']['budget']['writeoff']['foreclosed'] . '</option>';
						$HTML .= '<option value="information">' . $_HOAM_language ['field_names']['budget']['writeoff']['information'] . '</option>';
						$HTML .= '<option value="ownership">' . $_HOAM_language ['field_names']['budget']['writeoff']['ownership'] . '</option>';
						$HTML .= '<option value="paypal">' . $_HOAM_language ['field_names']['budget']['writeoff']['paypal'] . '</option>';
						$HTML .= '<option value="other">' . $_HOAM_language ['field_names']['budget']['writeoff']['other'] . '</option>';
						break;
					case 'board':
						$HTML .= '<option value="attorney">' . $_HOAM_language ['field_names']['budget']['writeoff']['attorney'] . '</option>';
						$HTML .= '<option selected="selected" value="board">' . $_HOAM_language ['field_names']['budget']['writeoff']['board'] . '</option>';
						$HTML .= '<option value="foreclosed">' . $_HOAM_language ['field_names']['budget']['writeoff']['foreclosed'] . '</option>';
						$HTML .= '<option value="information">' . $_HOAM_language ['field_names']['budget']['writeoff']['information'] . '</option>';
						$HTML .= '<option value="ownership">' . $_HOAM_language ['field_names']['budget']['writeoff']['ownership'] . '</option>';
						$HTML .= '<option value="paypal">' . $_HOAM_language ['field_names']['budget']['writeoff']['paypal'] . '</option>';
						$HTML .= '<option value="other">' . $_HOAM_language ['field_names']['budget']['writeoff']['other'] . '</option>';
						break;
					case 'foreclosed':
						$HTML .= '<option value="attorney">' . $_HOAM_language ['field_names']['budget']['writeoff']['attorney'] . '</option>';
						$HTML .= '<option value="board">' . $_HOAM_language ['field_names']['budget']['writeoff']['board'] . '</option>';
						$HTML .= '<option selected="selected" value="foreclosed">' . $_HOAM_language ['field_names']['budget']['writeoff']['foreclosed'] . '</option>';
						$HTML .= '<option value="information">' . $_HOAM_language ['field_names']['budget']['writeoff']['information'] . '</option>';
						$HTML .= '<option value="ownership">' . $_HOAM_language ['field_names']['budget']['writeoff']['ownership'] . '</option>';
						$HTML .= '<option value="paypal">' . $_HOAM_language ['field_names']['budget']['writeoff']['paypal'] . '</option>';
						$HTML .= '<option value="other">' . $_HOAM_language ['field_names']['budget']['writeoff']['other'] . '</option>';
						break;
					case 'information':
						$HTML .= '<option value="attorney">' . $_HOAM_language ['field_names']['budget']['writeoff']['attorney'] . '</option>';
						$HTML .= '<option value="board">' . $_HOAM_language ['field_names']['budget']['writeoff']['board'] . '</option>';
						$HTML .= '<option value="foreclosed">' . $_HOAM_language ['field_names']['budget']['writeoff']['foreclosed'] . '</option>';
						$HTML .= '<option selected="selected" value="information">' . $_HOAM_language ['field_names']['budget']['writeoff']['information'] . '</option>';
						$HTML .= '<option value="ownership">' . $_HOAM_language ['field_names']['budget']['writeoff']['ownership'] . '</option>';
						$HTML .= '<option value="paypal">' . $_HOAM_language ['field_names']['budget']['writeoff']['paypal'] . '</option>';
						$HTML .= '<option value="other">' . $_HOAM_language ['field_names']['budget']['writeoff']['other'] . '</option>';
						break;
					case 'ownership':
						$HTML .= '<option value="attorney">' . $_HOAM_language ['field_names']['budget']['writeoff']['attorney'] . '</option>';
						$HTML .= '<option value="board">' . $_HOAM_language ['field_names']['budget']['writeoff']['board'] . '</option>';
						$HTML .= '<option value="foreclosed">' . $_HOAM_language ['field_names']['budget']['writeoff']['foreclosed'] . '</option>';
						$HTML .= '<option value="information">' . $_HOAM_language ['field_names']['budget']['writeoff']['information'] . '</option>';
						$HTML .= '<option selected="selected" value="ownership">' . $_HOAM_language ['field_names']['budget']['writeoff']['ownership'] . '</option>';
						$HTML .= '<option value="paypal">' . $_HOAM_language ['field_names']['budget']['writeoff']['paypal'] . '</option>';
						$HTML .= '<option value="other">' . $_HOAM_language ['field_names']['budget']['writeoff']['other'] . '</option>';
						break;
					case 'other':
						$HTML .= '<option value="attorney">' . $_HOAM_language ['field_names']['budget']['writeoff']['attorney'] . '</option>';
						$HTML .= '<option value="board">' . $_HOAM_language ['field_names']['budget']['writeoff']['board'] . '</option>';
						$HTML .= '<option value="foreclosed">' . $_HOAM_language ['field_names']['budget']['writeoff']['foreclosed'] . '</option>';
						$HTML .= '<option value="information">' . $_HOAM_language ['field_names']['budget']['writeoff']['information'] . '</option>';
						$HTML .= '<option value="ownership">' . $_HOAM_language ['field_names']['budget']['writeoff']['ownership'] . '</option>';
						$HTML .= '<option value="paypal">' . $_HOAM_language ['field_names']['budget']['writeoff']['paypal'] . '</option>';
						$HTML .= '<option selected="selected" value="other">' . $_HOAM_language ['field_names']['budget']['writeoff']['other'] . '</option>';
						break;
					case 'paypal':
						$HTML .= '<option value="attorney">' . $_HOAM_language ['field_names']['budget']['writeoff']['attorney'] . '</option>';
						$HTML .= '<option value="board">' . $_HOAM_language ['field_names']['budget']['writeoff']['board'] . '</option>';
						$HTML .= '<option value="foreclosed">' . $_HOAM_language ['field_names']['budget']['writeoff']['foreclosed'] . '</option>';
						$HTML .= '<option value="information">' . $_HOAM_language ['field_names']['budget']['writeoff']['information'] . '</option>';
						$HTML .= '<option value="ownership">' . $_HOAM_language ['field_names']['budget']['writeoff']['ownership'] . '</option>';
						$HTML .= '<option selected="selected" value="paypal">' . $_HOAM_language ['field_names']['budget']['writeoff']['paypal'] . '</option>';
						$HTML .= '<option value="other">' . $_HOAM_language ['field_names']['budget']['writeoff']['other'] . '</option>';
						break;
					default:
						$HTML .= '<option value="attorney">' . $_HOAM_language ['field_names']['budget']['writeoff']['attorney'] . '</option>';
						$HTML .= '<option value="board">' . $_HOAM_language ['field_names']['budget']['writeoff']['board'] . '</option>';
						$HTML .= '<option value="foreclosed">' . $_HOAM_language ['field_names']['budget']['writeoff']['foreclosed'] . '</option>';
						$HTML .= '<option value="information">' . $_HOAM_language ['field_names']['budget']['writeoff']['information'] . '</option>';
						$HTML .= '<option value="ownership">' . $_HOAM_language ['field_names']['budget']['writeoff']['ownership'] . '</option>';
						$HTML .= '<option value="paypal">' . $_HOAM_language ['field_names']['budget']['writeoff']['paypal'] . '</option>';
						$HTML .= '<option value="other">' . $_HOAM_language ['field_names']['budget']['writeoff']['other'] . '</option>';
						break;
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formDate ('budget|payment|datereceived', $_HOAM_language ['field_names']['budget']['payment']['date']['received'], $_SESSION['payment_information']['trusted_budget|payment|datereceived'], HOAM_date ($_HOAM_country ['date']['format_reports']));
				$HTML .= HOAM_formText ('budget|payment|memo', $_HOAM_language ['field_names']['budget']['payment']['memo'], $_SESSION['payment_information']['trusted_budget|payment|memo'], '', $budget_class -> returnColumnSize ('memo'), 1);
				$HTML .= '</div>';
				
				$HTML .= '<div class="optional">';
				$HTML .= '<label for="budget|invoice|attachments">' . $_HOAM_language ['field_names']['budget']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('budget|invoice|attachments');
				$HTML .= HOAM_formDisplayError ('budget|invoice|attachments');
				$HTML .= '</label>';
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= '</div>';

				$HTML .= '</div>'; // End Invoice Add

				$HTML .= '<div class="tabbertab" id="advanced" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formTextarea ('budget|payment|description', $_HOAM_language ['field_names']['budget']['payment']['description'], $_SESSION['payment_information']['trusted_budget|payment|description'], '', $_SESSION['current_user']['edit']['row'], $budget_class -> returnColumnSize ('description'));
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Advanced Options
				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('vendor') :
			$_HOAM_log -> add ('Loading /admin/financial/vendor/ page.', HOAM_MESSAGE_DEBUG);
			$HTML = HOAM_adminBudgetVendor (TRUE);
			break;
		case ('view'):
			$_HOAM_log -> add ('Loading /admin/financial/view/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					
					$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
					$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
					$_HOAM_term_list = &$_HOAM_language ['budget']['term'];
					require_once (HOAM_PATH_FUNCTION . 'attachments.php');
					
					$trusted_invoice_id = $budget_vendor_class -> superClean ($GLOBALS['global_url_array'][3], $budget_vendor_class -> returnColumnSize ('id'));
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['invoice']['view'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['invoice']['view'] . '</h1>';
					if ($budget_invoice_class -> idExists ($trusted_invoice_id)) {
						$trusted_vendor_id = $budget_invoice_class -> returnVendorId ($trusted_invoice_id);
						$invoice_amount = 0;
						$item = 1;
						$HTML .= '<h2>' . html_encode (stripslashes ($budget_vendor_class -> returnName ($trusted_vendor_id))) . '</h2>';
						$HTML .= '<h3>' . 'Contact Information' . '</h3>';
						if ($budget_vendor_class -> returnAddress3 ($trusted_vendor_id)) {
							if ($budget_vendor_class -> returnAddress1 ($trusted_vendor_id)) {
								$HTML .= html_encode (stripslashes ($budget_vendor_class -> returnAddress1 ($trusted_vendor_id))) . '<br />';
							}
							if ($budget_vendor_class -> returnAddress2 ($trusted_vendor_id)) {
								$HTML .= html_encode (stripslashes ($budget_vendor_class -> returnAddress2 ($trusted_vendor_id))) . '<br />';
							}
							if ($budget_vendor_class -> returnAddress3 ($trusted_vendor_id)) {
								$HTML .= html_encode (stripslashes ($budget_vendor_class -> returnAddress3 ($trusted_vendor_id))) . '<br />';
							}
							$HTML .= $budget_vendor_class -> returnCity ($trusted_vendor_id) . ', ';
							$HTML .= $budget_vendor_class -> returnState ($trusted_vendor_id) . ' ';
							$HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $budget_vendor_class -> returnPostalcode ($trusted_vendor_id), 1);
							$HTML .= '<br />';
							if ($budget_vendor_class -> returnTelephoneWork ($trusted_vendor_id)) {
								$HTML .= '<br />' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $budget_vendor_class -> returnTelephoneWork ($trusted_vendor_id), 1) . ' ' . ucfirst ($_HOAM_language ['common']['work']);
							}
							if ($budget_vendor_class -> returnTelephoneFax ($trusted_vendor_id)) {
								$HTML .= '<br />' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $budget_vendor_class -> returnTelephoneFax ($trusted_vendor_id), 1) . ' ' . ucfirst ($_HOAM_language ['common']['fax']);
							}
							$HTML .= '<br />';
							if ($budget_vendor_class -> returnEmail ($trusted_vendor_id)) {
								$HTML .= '<br /><a href="mailto:' . $budget_vendor_class -> returnEmail ($trusted_vendor_id) . '">' . $budget_vendor_class -> returnEmail ($trusted_vendor_id) . '</a>';
							}
							if ($budget_vendor_class -> returnWebsite ($trusted_vendor_id)) {
								$HTML .= '<br /><a href="' . $budget_vendor_class -> returnWebsite ($trusted_vendor_id) . '">' . $budget_vendor_class -> returnWebsite ($trusted_vendor_id) . '</a>';
							}
						} else {
							$HTML .= '<span style="color: grey; font-style: italic">' . $_HOAM_language ['errors']['generic']['none_entered'] . '</span>';							
						}
						$HTML .= '<h3>' . $_HOAM_language ['field_names']['budget']['invoice']['number'] . '</h3>' . $budget_invoice_class -> returnNumber ($trusted_invoice_id);
						
						$HTML .= '<h3>' . $_HOAM_language ['field_names']['budget']['attachments'] . '</h3>';
						$charge_list = $budget_class -> listChargesOnInvoice ($trusted_invoice_id);
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_invoice_id, $_HOAM_setting ['attachment']['flags']['budget']);
						if (is_array ($attachment_list)) {
							foreach ($attachment_list as $attachment) {
								$attachment_html .= HOAM_attachmentReturnHTML ($attachment);
							}
						}
						if (isset ($attachment_html)) {
							$HTML .= $attachment_html;
							$HTML .= '<br class="clear" />';
							unset ($attachment_html);
						} else {
							$HTML .= '<span style="color: grey; font-style: italic">' . $_HOAM_language ['errors']['generic']['none_entered'] . '</span>';
						}
						$HTML .= '<h3>' . $_HOAM_language ['field_names']['budget']['invoice']['line_items'] . '</h3>';
						
						$charge_list = $budget_class -> listChargesOnInvoice ($trusted_invoice_id);
						// The customer for all charges on a single invoice
						// should always be the same. 
						$customer = $budget_class -> returnCustomerId ($charge_list[0]);
						$thead = '<thead>';
						$thead .= '<tr>';
						$thead .= '<th>Item</th>';
						$thead .= '<th>Memo</th>';
						$thead .= '<th>Due Date</th>';
						$thead .= '<th>Amount</th>';
						$thead .= '<th>Balance</th>';
						$thead .= '</tr>';
						$thead .= '</thead>';
						$tbody = '<tbody>';
						if (is_array ($charge_list)) {
							foreach ($charge_list as $charge) {
								$amount = $budget_class -> returnAmount ($charge);
								$invoice_amount += $amount;
								$tbody .= '<tr>';
								$tbody .= '<td class="center">' . $item++ . '</td>';
								$tbody .= '<td>' . html_encode (stripslashes ($budget_class -> returnMemo ($charge))) . '</td>';
								$tbody .= '<td class="center">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateDue ($charge))) . '</td>';
								$tbody .= '<td class="currency">' . HOAM_returnCurrency ($amount) . '</td>';
								$tbody .= '<td class="currency">' . HOAM_returnCurrency ($invoice_amount) . '</td>';
								$tbody .= '</tr>';
							}
						} else {
							$tbody .= '<tr>';
							$tbody .= '<td>No Charges Found.</td>';
							$tbody .= '</tr>';
						}
						$tbody .= '</tbody>';
						$HTML .= '<table>' . $thead . $tbody . '</table>';
						$HTML .= '<div class="budget_invoice_total">';
						$HTML .= '<h2>Total</h2>';
						$HTML .= 'Due Date: ' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_invoice_class -> returnDateDue ($trusted_invoice_id)));
						$HTML .= '<br /><br />';
						$HTML .= 'Amount Due by Check: ' . HOAM_returnCurrency ($invoice_amount, 2);
					} else {
						$HTML .= $_HOAM_language ['errors']['generic']['invalid-id'];
					}
				} else {
					header ('Location: /admin/financial/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['financial'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['financial'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/money.png" alt="' . $_HOAM_language ['field_names']['admin']['financial'] . '" class="LHS" /><a href="/admin/financial/">' . $_HOAM_language ['field_names']['admin']['financial'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/calculator_add.png\')"><a href="/admin/financial/invoice/">' . $_HOAM_language ['field_names']['budget']['invoice']['add'] . '</a></li>';
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/money.png\')"><a href="/admin/financial/payment/">' . $_HOAM_language ['field_names']['budget']['payment']['add'] . '</a></li>';
					}
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
//						$HTML .= '<li style="list-style: none">' . HOAM_adminBudgetHomeowner () . '</li>';
						$HTML .= '<li style="list-style: none">' . HOAM_adminBudgetVendor () . '</li>';
						$HTML .= '<li style="list-style: none">' . HOAM_adminBudgetAccount () . '</li>';
						$HTML .= '<li style="list-style: none">' . HOAM_adminBudgetBudget () . '</li>';
						$HTML .= '<li style="list-style: none">' . HOAM_adminBudgetCategory () . '</li>';
					}
					if (($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_delete'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_edit'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_view'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) AND
						$_HOAM_setting ['budget']['insurance']['enable']) {
						$HTML .= '<li style="list-style: none">' . HOAM_adminInsurance () . '</li>';
					}
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminBudgetAccount ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$budget_account_class = new HOAM_budget_account ('budget_account');
	$budget_class = new HOAM_budget ('budget');
	$group_members_class = new HOAM_group_members ('group_members');

	function HOAM_adminBudgetAccountList ($actions = FALSE) {
		global $_HOAM_language, $_HOAM_setting;
		
		$budget_account_class = new HOAM_budget_account ('budget_account');
		$group_members_class = new HOAM_group_members ('group_members');
		
		$HTML = '<table class="center">';
		$colgroup = '<colgroup>';
		if ($actions) {
			$colgroup .= '<col width="20%" />';
			$colgroup .= '<col width="30%" />';
			$colgroup .= '<col width="10%" />';
			$colgroup .= '<col width="10%" />';
			$colgroup .= '<col width="20%" />';
			$colgroup .= '<col width="10%" />';
		} else {
			$colgroup .= '<col width="25%" />';
			$colgroup .= '<col width="35%" />';
			$colgroup .= '<col width="10%" />';
			$colgroup .= '<col width="20%" />';
			$colgroup .= '<col width="10%" />';
		}
		$colgroup .= '</colgroup>';
		$thead = '<thead><tr>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['account']['name'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['account']['description'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['account']['number'] . '</th>';
		if ($actions) {
			$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
		}
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['account']['contact'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['account']['flags'] . '</th>';
		$thead .= '</tr></thead>';
		$tbody = '<tbody id="account_list">';
		$account_list = $budget_account_class -> listAllId ('name', 'ASC');
		if (is_array ($account_list)) {
			foreach ($account_list as $account) {
				$account_info = $budget_account_class -> listAll ($account);
				$account_info = $account_info [0];
				$tbody .=  '<tr>';
				$tbody .= '<td>' . html_encode (stripslashes ($account_info ['name'])) . '</td>';
				$tbody .= '<td>' . html_encode (stripslashes ($account_info ['description'])) . '</td>';
				$tbody .= '<td>' . $account_info ['account_num'] . '</td>';
				if ($actions) {
					$tbody .= '<td>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$tbody .= '<small><a href="/admin/financial/account/edit/' . $account_info['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
					}
					$tbody .= ' &nbsp; ';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$tbody .= '<small><a class="error" href="/admin/financial/account/delete/' . $account_info['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
					}
					$tbody .= '</td>';
				}
				$tbody .= '<td>&nbsp;</td>';
				$tbody .= '<td>&nbsp;</td>';
				$tbody .= '</tr>';
			}
		} else {
			$tbody .= '<tr><td colspan="5">' . $_HOAM_language['errors']['budget']['account']['none'] . '</td></tr>';
		}
		$tbody .= '</tbody>';
		$HTML = '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody . '</table>';
		return ($HTML);
	}
	
	if (isset ($GLOBALS['global_url_array'][3])) {
		switch ($GLOBALS['global_url_array'][3]) {
			case ('add'):
				$_HOAM_log -> add ('Loading /admin/financial/account/add/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['account']['add'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['account']['add'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
					// OK, we're checking to see that the account we're trying to edit is the one that we
					// have information on in the account_information variable.
					// If the referrer doesn't match this current page, then we know we came from
					// somewhere else on the site, and thus the information is suspect.
					// Also, trusted_id will /only/ be set if an account was being modified, not added.
					if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
						if (isset ($_SESSION['error_class_item'])) {
							unset ($_SESSION['error_class_item']);
						}
						if (isset ($_SESSION['account_information'])) {
							unset ($_SESSION['account_information']);
						}
					}

					$HTML .= '<form id="account_add" action="/hoam/scripts/budget/account_edit_script.php" enctype="multipart/form-data" method="post">';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['budget']['account']['add'] . '</button>';
					$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="account_information" title="' . $_HOAM_language ['form_tabs']['budget']['account']['add'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formText ('budget|account|name', $_HOAM_language ['field_names']['budget']['account']['name'], $_SESSION['account_information']['trusted_budget|account|name'], '', $budget_account_class -> returnColumnSize ('name'), $_HOAM_setting ['budget']['minimum_name_length']);
					$HTML .= HOAM_formText ('budget|account|description', $_HOAM_language ['field_names']['budget']['account']['description'], $_SESSION['account_information']['trusted_budget|account|description'], '', $budget_account_class -> returnColumnSize ('description'), $_HOAM_setting ['budget']['minimum_description_length']);
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_formField ('budget|account|number', $_HOAM_language ['field_names']['budget']['account']['number'], $_SESSION['account_information']['trusted_budget|account|number'], '', $budget_account_class -> returnColumnSize ('account_num'), 0);
					$HTML .= '</div>';
					$HTML .= '</div>'; // End New Account Tab
				
					$HTML .= '<div class="tabbertab" id="budget_account_list" title="' . $_HOAM_language ['form_tabs']['budget']['account']['list'] . '">';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_adminBudgetAccountList (); 
					$HTML .= '</div>';
					$HTML .= '</div>'; // End Account List Tab

					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case ('delete'):
				$_HOAM_log -> add ('Loading /admin/financial/account/delete/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					if ($GLOBALS['global_url_array'][4]) {
						$trusted_id = $budget_account_class -> superClean ($GLOBALS['global_url_array'][4], $budget_account_class -> returnColumnSize ('id'));
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['account']['delete'])));
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['account']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
						// OK, we're checking to see that the account we're trying to edit is the one that we
						// have information on in the account_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if an account was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['account_information'])) {
								unset ($_SESSION['account_information']);
							}
						}

						$HTML .= '<form id="account_delete" action="/hoam/scripts/budget/account_edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="account|id" id="account|id" value="' . $trusted_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="account_information" title="' . $_HOAM_language ['form_tabs']['budget']['account']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formTextDisabled ('budget|account|name', $_HOAM_language ['field_names']['budget']['account']['name'], $budget_account_class -> returnName ($trusted_id));
						$HTML .= HOAM_formTextDisabled ('budget|account|description', $_HOAM_language ['field_names']['budget']['account']['description'], $budget_account_class -> returnDescription ($trusted_id));
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formFieldDisabled ('budget|account|number', $_HOAM_language ['field_names']['budget']['account']['number'], $budget_account_class -> returnAccountNum ($trusted_id));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Delete Account Tab
				
						$HTML .= '<div class="tabbertab" id="budget_account_list" title="' . $_HOAM_language ['form_tabs']['budget']['account']['list'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_adminBudgetAccountList (); 
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Account List Tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /admin/financial/account/list/');
						exit ();
					}
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case ('edit'):
				$_HOAM_log -> add ('Loading /admin/financial/account/edit/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					if ($GLOBALS['global_url_array'][4]) {
						$trusted_id = $budget_account_class -> superClean ($GLOBALS['global_url_array'][4], $budget_account_class -> returnColumnSize ('id'));
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['account']['edit'])));
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['account']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
						// OK, we're checking to see that the account we're trying to edit is the one that we
						// have information on in the account_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if an account was being modified, not added.
						if (((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) OR (isset ($_SESSION['homeowner_information']['trusted_homeowner|id']) AND ($trusted_id != $_SESSION['account_information']['trusted_budget|account|id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['account_information'])) {
								unset ($_SESSION['account_information']);
							}
						}

						$HTML .= '<form id="account_edit" action="/hoam/scripts/budget/account_edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="account|id" id="account|id" value="' . $trusted_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="account_information" title="' . $_HOAM_language ['form_tabs']['budget']['account']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formText ('budget|account|name', $_HOAM_language ['field_names']['budget']['account']['name'], $_SESSION['account_information']['trusted_budget|account|name'], $budget_account_class -> returnName ($trusted_id), $budget_account_class -> returnColumnSize ('name'), $_HOAM_setting ['budget']['minimum_name_length']);
						$HTML .= HOAM_formText ('budget|account|description', $_HOAM_language ['field_names']['budget']['account']['description'], $_SESSION['account_information']['trusted_budget|account|description'], $budget_account_class -> returnDescription ($trusted_id), $budget_account_class -> returnColumnSize ('description'), $_HOAM_setting ['budget']['minimum_description_length']);
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formField ('budget|account|number', $_HOAM_language ['field_names']['budget']['account']['number'], $_SESSION['account_information']['trusted_budget|account|number'], $budget_account_class -> returnAccountNum ($trusted_id), $budget_account_class -> returnColumnSize ('account_num'), 0);
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Edit Account Tab
				
						$HTML .= '<div class="tabbertab" id="budget_account_list" title="' . $_HOAM_language ['form_tabs']['budget']['account']['list'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_adminBudgetAccountList (); 
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Account List Tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /admin/financial/account/list/');
						exit ();
					}
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case ('list') :
				$_HOAM_log -> add ('Loading /admin/financial/account/list/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['account']['list'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['account']['list'] . '</h1>';
					$HTML .= HOAM_adminBudgetAccountList (TRUE);
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			default:
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['financial'])));
			$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['budget_account'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/user.png" alt="' . $_HOAM_language ['field_names']['admin']['budget_account'] . '" class="LHS" /><a href="/admin/financial/account/">' . $_HOAM_language ['field_names']['admin']['budget_account'] . '</a>';
			$HTML .= '<ul>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/account/add/">' . $_HOAM_language ['field_names']['budget']['account']['add'] . '</a></li>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/account/list/">' . $_HOAM_language ['field_names']['budget']['account']['list'] . '</a></li>';
			$HTML .= '</ul>';
		} else {
			header ('Location: /error/403/');
			exit ();
		}
	}
	return ($HTML);
}

function HOAM_adminBudgetBudget ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;
	
	$budget_class = new HOAM_budget ('budget');
	$group_members_class = new HOAM_group_members ('group_members');
	
	switch ($GLOBALS['global_url_array'][3]) {
		case ('add'):
			$_HOAM_log -> add ('Loading /admin/financial/budget/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetBudgetJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
				$budget_class = new HOAM_budget_budget ('budget_budget');
				$budget_category_class = new HOAM_budget_category ('budget_category');
				
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				// Also, trusted_id will /only/ be set if an account was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['budget_information'])) {
						unset ($_SESSION['budget_information']);
					}
				}

				$HTML .= '<form id="budget_add" action="/hoam/scripts/budget/budget_edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['budget']['budget']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="account_information" title="' . $_HOAM_language ['form_tabs']['budget']['budget']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formText ('budget|budget|name', $_HOAM_language ['field_names']['budget']['budget']['name'], $_SESSION['budget_information']['trusted_budget|budget|name'], '', $budget_class -> returnColumnSize ('name'), $_HOAM_setting ['budget']['minimum_name_length']);
				$HTML .= HOAM_formDate ('budget|budget|start', $_HOAM_language ['field_names']['budget']['budget']['date']['start'], $_SESSION['budget_information']['trusted_budget|budget|start'], '', $budget_class -> returnColumnSize ('datestart'), $_HOAM_setting ['budget']['minimum_description_length']);
				$HTML .= HOAM_formDate ('budget|budget|end', $_HOAM_language ['field_names']['budget']['budget']['date']['end'], $_SESSION['budget_information']['trusted_budget|budget|end'], '', $budget_class -> returnColumnSize ('dateend'), $_HOAM_setting ['budget']['minimum_description_length']);
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formCheckbox ('budget|budget|annual', $_HOAM_language ['field_names']['budget']['flags']['annual'], 1, $_SESSION['budget_information']['trusted_budget|budget|annual'], 0);
				$HTML .= HOAM_formCheckbox ('budget|budget|public', $_HOAM_language ['field_names']['budget']['flags']['public'], 1, $_SESSION['budget_information']['trusted_budget|budget|public'], 0);
				$HTML .= '<br /><br />';
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory">';
				$HTML .= '<table style="background: transparent">';
				$HTML .= '<tbody class="nostripe">';
				$HTML .= '<tr><td>';
				$HTML .= HOAM_formSelectMultiple ('budget|categories', $_HOAM_language ['field_names']['budget']['budget']['category']['selected'], 5);
				if (isset ($_SESSION['budget_information']['trusted_budget|budget|categories'])) {
					$default_categories = $_SESSION['budget_information']['trusted_budget|budget|categories'];
				} else {
					$default_categories = NULL;
				}
				if (is_array ($default_categories)) {
					foreach ($default_categories as $category) {
						$HTML .= '<option value="' . $category . '">' . $budget_category_class -> returnName ($category) . '</option>';
					}
				}
				$HTML .= '</select>';
				$HTML .= '</td><td>';
				$HTML .= '<button type="button" id="budget|category|add">&lt;&lt;</button><br />';
				$HTML .= '<button type="button" id="budget|category|remove">&gt;&gt;</button>';
				$HTML .= '</td><td>';
				$HTML .= '<label for="budget|category|all">' . $_HOAM_language ['field_names']['budget']['budget']['category']['all'];
				$HTML .= '</label>';
				$HTML .= '<select multiple="multiple" id="budget|category|all" size="5">';
 				$category_list = $budget_category_class -> listCategoryAll ();
				$optgroup = FALSE;
				foreach ($category_list as $category) {
					// Is this a parent category?
					if ($category[1] == 0) {
						if ($optgroup) {
							$HTML .= '</optgroup>';
						} else {
							$optgroup = TRUE;
						}
						$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
						continue;
					}
					if (!in_array ($category[0], $default_categories)) {
						$HTML .= '<option value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
					}
				}
				if ($optgroup) {
					$HTML .= '</optgroup>';
				}
				$HTML .= '</select>';
				$HTML .= '</td></tr>';
				$HTML .= '</tbody>';
				$HTML .= '</table>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End New Budget Tab
				
				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('delete'):
			$_HOAM_log -> add ('Loading /admin/financial/budget/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['delete'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetBudgetJS');
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['delete'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
					$budget_class = new HOAM_budget_budget ('budget_budget');
					$budget_category_class = new HOAM_budget_category ('budget_category');
					$budget_tracking_class = new HOAM_budget_track ('budget_track');
					
					$trusted_id = $budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_class -> returnColumnSize ('id'));
					
					// If the referrer doesn't match this current page, then we know we came from
					// somewhere else on the site, and thus the information is suspect.
					// Also, trusted_id will /only/ be set if an account was being modified, not added.
					if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
						if (isset ($_SESSION['error_class_item'])) {
							unset ($_SESSION['error_class_item']);
						}
						if (isset ($_SESSION['budget_information'])) {
							unset ($_SESSION['budget_information']);
						}
					}

					$HTML .= '<form id="budget_delete" action="/hoam/scripts/budget/budget_edit_script.php" enctype="multipart/form-data" method="post">';
					$HTML .= '<input type="hidden" name="budget|budget|id" id="budget|budget|id" value="' . $trusted_id . '" />';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['budget']['budget']['delete'] . '</button>';
					$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="account_information" title="' . $_HOAM_language ['form_tabs']['budget']['budget']['information'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formTextDisabled ('budget|budget|name', $_HOAM_language ['field_names']['budget']['budget']['name'], $budget_class -> returnName ($trusted_id));
					$HTML .= HOAM_formDateDisabled ('budget|budget|start', $_HOAM_language ['field_names']['budget']['budget']['date']['start'], date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateStart ($trusted_id))));
					$HTML .= HOAM_formDateDisabled ('budget|budget|end', $_HOAM_language ['field_names']['budget']['budget']['date']['end'], date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateEnd ($trusted_id))));
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_formCheckboxDisabled ('budget|budget|annual', $_HOAM_language ['field_names']['budget']['flags']['annual'], 1, $budget_class -> isBitSet ($trusted_id, $_HOAM_setting ['budget']['flags']['annual']));
					$HTML .= HOAM_formCheckboxDisabled ('budget|budget|public', $_HOAM_language ['field_names']['budget']['flags']['public'], 1, $budget_class -> isBitSet ($trusted_id, $_HOAM_setting ['budget']['flags']['public']));
					$HTML .= '<br /><br />';
					$HTML .= '</div>';
					$HTML .= '<div class="mandatory">';
					$HTML .= '<table style="background: transparent">';
					$HTML .= '<tbody class="nostripe">';
					$HTML .= '<tr><td>';
					$HTML .= HOAM_formSelectMultipleDisabled ('budget|categories', $_HOAM_language ['field_names']['budget']['budget']['category']['selected'], 5);
					if (isset ($_SESSION['budget_information']['trusted_budget|budget|categories'])) {
						$default_categories = $_SESSION['budget_information']['trusted_budget|budget|categories'];
					} else {
				    	$default_categories = $budget_tracking_class -> ListCategoryId ($trusted_id);
					}
					foreach ($default_categories as $category) {
						$HTML .= '<option value="' . $category . '">' . $budget_category_class -> returnName ($budget_tracking_class -> returnCategoryId ($category)) . '</option>';
					}
					$HTML .= '</select>';
					$HTML .= '</td><td>';
					$HTML .= '<button disabled type="button" id="budget|category|add">&lt;&lt;</button><br />';
					$HTML .= '<button disabled type="button" id="budget|category|remove">&gt;&gt;</button>';
					$HTML .= '</td><td>';
					$HTML .= '<label for="budget|category|all">' . $_HOAM_language ['field_names']['budget']['budget']['category']['all'];
					$HTML .= '</label>';
					$HTML .= '<select disabled multiple="multiple" id="budget|category|all" size="5">';
	 				$category_list = $budget_category_class -> listCategoryAll ();
					$optgroup = FALSE;
					foreach ($category_list as $category) {
						// Is this a parent category?
						if ($category[1] == 0) {
							if ($optgroup) {
								$HTML .= '</optgroup>';
							} else {
								$optgroup = TRUE;
							}
							$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
							continue;
						}
						$id = $budget_tracking_class -> returnIdForCategory ($category[0], $trusted_id);
						if (!$id) {
							$HTML .= '<option value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
						}
					}
					if ($optgroup) {
						$HTML .= '</optgroup>';
					}
					$HTML .= '</select>';
					$HTML .= '</td></tr>';
					$HTML .= '</tbody>';
					$HTML .= '</table>';
					$HTML .= '</div>';
					$HTML .= '</div>'; // End New Budget Tab
				
					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
				} else {
					header ('Location: /admin/financial/budget/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('edit'):
			$_HOAM_log -> add ('Loading /admin/financial/budget/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['information'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetBudgetJS');
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['information'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
					$budget_class = new HOAM_budget_budget ('budget_budget');
					$budget_category_class = new HOAM_budget_category ('budget_category');
					$budget_tracking_class = new HOAM_budget_track ('budget_track');
					
					$trusted_id = $budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_class -> returnColumnSize ('id'));
					
					// If the referrer doesn't match this current page, then we know we came from
					// somewhere else on the site, and thus the information is suspect.
					// Also, trusted_id will /only/ be set if an account was being modified, not added.
					if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
						if (isset ($_SESSION['error_class_item'])) {
							unset ($_SESSION['error_class_item']);
						}
						if (isset ($_SESSION['budget_information'])) {
							unset ($_SESSION['budget_information']);
						}
					}

					$HTML .= '<form id="budget_edit" action="/hoam/scripts/budget/budget_edit_script.php" enctype="multipart/form-data" method="post">';
					$HTML .= '<input type="hidden" name="budget|budget|id" id="budget|budget|id" value="' . $trusted_id . '" />';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
					$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="account_information" title="' . $_HOAM_language ['form_tabs']['budget']['budget']['information'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formText ('budget|budget|name', $_HOAM_language ['field_names']['budget']['budget']['name'], $_SESSION['budget_information']['trusted_budget|budget|name'], $budget_class -> returnName ($trusted_id), $budget_class -> returnColumnSize ('name'), $_HOAM_setting ['budget']['minimum_name_length']);
					$HTML .= HOAM_formDate ('budget|budget|start', $_HOAM_language ['field_names']['budget']['budget']['date']['start'], $_SESSION['budget_information']['trusted_budget|budget|start'], date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateStart ($trusted_id))), $budget_class -> returnColumnSize ('datestart'), $_HOAM_setting ['budget']['minimum_description_length']);
					$HTML .= HOAM_formDate ('budget|budget|end', $_HOAM_language ['field_names']['budget']['budget']['date']['end'], $_SESSION['budget_information']['trusted_budget|budget|end'], date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateEnd ($trusted_id))), $budget_class -> returnColumnSize ('dateend'), $_HOAM_setting ['budget']['minimum_description_length']);
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_formCheckbox ('budget|budget|annual', $_HOAM_language ['field_names']['budget']['flags']['annual'], 1, $_SESSION['budget_information']['trusted_budget|budget|annual'], $budget_class -> isBitSet ($trusted_id, $_HOAM_setting ['budget']['flags']['annual']));
					$HTML .= HOAM_formCheckbox ('budget|budget|public', $_HOAM_language ['field_names']['budget']['flags']['public'], 1, $_SESSION['budget_information']['trusted_budget|budget|public'], $budget_class -> isBitSet ($trusted_id, $_HOAM_setting ['budget']['flags']['public']));
					$HTML .= '<br /><br />';
					$HTML .= '</div>';
					$HTML .= '<div class="mandatory">';
					$HTML .= '<table style="background: transparent">';
					$HTML .= '<tbody class="nostripe">';
					$HTML .= '<tr><td>';
					$HTML .= HOAM_formSelectMultiple ('budget|budget|categories', $_HOAM_language ['field_names']['budget']['budget']['category']['selected'], 5);
					if (isset ($_SESSION['budget_information']['trusted_budget|budget|categories'])) {
						$default_categories = $_SESSION['budget_information']['trusted_budget|budget|categories'];
					} else {
				    	$default_categories = $budget_tracking_class -> ListCategoryId ($trusted_id);
					}
					foreach ($default_categories as $category) {
						$HTML .= '<option value="' . $budget_tracking_class -> returnCategoryId ($category) . '">' . $budget_category_class -> returnName ($budget_tracking_class -> returnCategoryId ($category)) . '</option>';
					}
					$HTML .= '</select>';
					$HTML .= '</td><td>';
					$HTML .= '<button type="button" id="budget|category|add">&lt;&lt;</button><br />';
					$HTML .= '<button type="button" id="budget|category|remove">&gt;&gt;</button>';
					$HTML .= '</td><td>';
					$HTML .= '<label for="budget|category|all">' . $_HOAM_language ['field_names']['budget']['budget']['category']['all'];
					$HTML .= '</label>';
					$HTML .= '<select multiple="multiple" id="budget|category|all" size="5">';
	 				$category_list = $budget_category_class -> listCategoryAll ();
					$optgroup = FALSE;
					foreach ($category_list as $category) {
						// Is this a parent category?
						if ($category[1] == 0) {
							if ($optgroup) {
								$HTML .= '</optgroup>';
							} else {
								$optgroup = TRUE;
							}
							$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
							continue;
						}
						$id = $budget_tracking_class -> returnIdForCategory ($category[0], $trusted_id);
						if (!$id) {
							$HTML .= '<option value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
						}
					}
					if ($optgroup) {
						$HTML .= '</optgroup>';
					}
					$HTML .= '</select>';
					$HTML .= '</td></tr>';
					$HTML .= '</tbody>';
					$HTML .= '</table>';
					$HTML .= '</div>';
					$HTML .= '</div>'; // End New Budget Tab
				
					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
				} else {
					header ('Location: /admin/financial/budget/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('entry'):
			$_HOAM_log -> add ('Loading /admin/financial/budget/entry/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$budget_class = new HOAM_budget_budget ('budget_budget');
					$budget_category_class = new HOAM_budget_category ('budget_category');
					$budget_tracking_class = new HOAM_budget_track ('budget_track');

					$trusted_id = $budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_class -> returnColumnSize ('id'));
					if ($budget_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['category']['selected'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetBudgetJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['category']['selected'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
					
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if an account was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['budget_information'])) {
								unset ($_SESSION['budget_information']);
							}
						}

						$HTML .= '<form id="budget_delete" action="/hoam/scripts/budget/tracking_edit_script.php" enctype="multipart/form-data" method="post">';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="account_information" title="' . $_HOAM_language ['form_tabs']['budget']['budget']['category']['selected'] . '">';
						$HTML .= '<div class="mandatory">';
						$tracking_list = $budget_tracking_class -> listCategoryId ($trusted_id);
						$category_list = $budget_category_class -> listCategoryAll ();
						$optgroup = FALSE;
						foreach ($category_list as $category) {
							// Is this a parent category?
							if ($category[1] == 0) {
								if ($optgroup) {
									$HTML .= '</fieldset>';
								} else {
									$optgroup = TRUE;
								}
								$HTML .= '<fieldset><br />';
								$HTML .= '<label>' . $budget_category_class -> returnName ($category[0]) . '</label>';
								continue;
							}
							$entry_id = $budget_tracking_class -> returnIdForCategory ($category[0], $trusted_id);
							if ($entry_id) {
								$HTML .= '<label for="budget|entry">' . $budget_category_class -> returnName ($category[0]) . '</label>';
								$HTML .= '<input type="text" name="budget|entry|' . $entry_id . '" size="' . $budget_tracking_class -> returnColumnSize ('amount') . '" value="' . round ($budget_tracking_class -> returnAmount ($entry_id), 2) . '" />';
							}
						}
						if ($optgroup) {
							$HTML .= '</fieldset>';
						}
						$HTML .= '</div>';
						$HTML .= '</div>'; // End New Budget Tab
				
						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/financial/budget/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;			
		case ('list') :
			$_HOAM_log -> add ('Loading /admin/financial/budget/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['list'] . '</h1>';
				$budget_class = new HOAM_budget_budget ('budget_budget');
				$budget_list = $budget_class -> listAllId ();
				$colgroup = '<colgroup>';
				$colgroup .= '</colgroup>';
				$thead = '<thead>';
				$thead .= '<tr>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['description'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['start'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['end'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '</tr>';
				$thead .= '</thead>';
				$thead .= '<tbody>';
				if (is_array ($budget_list) AND !empty ($budget_list)) {
					$tbody = '';
					foreach ($budget_list as $budget) {
						$tbody .= '<tr>';
						$tbody .= '<td>' . $budget_class -> returnName ($budget) . '</td>';
						$tbody .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateStart ($budget))) . '</td>';
						$tbody .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateEnd ($budget))) . '</td>';
						$tbody .= '<td>';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/financial/budget/entry/' . $budget . '/">' . $_HOAM_language ['action']['budget']['amount'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/financial/budget/edit/' . $budget . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a class="error" href="/admin/financial/budget/delete/' . $budget . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/financial/budget/view/' . $budget . '/">' . $_HOAM_language ['action']['generic']['view'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/financial/budget/net/' . $budget . '/">' . $_HOAM_language ['action']['budget']['net'] . '</a></small>';
						}
						$tbody .= '</td>';
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="4">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>'. $tbody . '<tbody></table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('net'):
			$_HOAM_log -> add ('Loading /admin/financial/budget/net/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$budget_budget_class = new HOAM_budget_budget ('budget_budget');

					$trusted_id = $budget_budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_budget_class -> returnColumnSize ('id'));
					if ($budget_budget_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['net'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetNetJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['net'] . '</h1>';
						$date_start = $budget_budget_class -> returnDateStart ($trusted_id);
						$date_end = $budget_budget_class -> returnDateEnd ($trusted_id);
						if (($date_start < date ($_HOAM_country['date']['format_mysql'])) AND ($date_end > date ($_HOAM_country['date']['format_mysql']))) {
							$date_start = date ($_HOAM_country['date']['format_mysql'], mktime (0, 0, 0, date ('n'), 1, date ('Y')));
							$date_end = date ($_HOAM_country['date']['format_mysql'], mktime (0, 0, 0, date ('n'), date ('t'), date ('Y')));
						}
						$HTML .= '<form class="donotprint" id="budget|view|form" action="">';
						$HTML .= '<div>';
						$HTML .= '<input type="hidden" id="budget|view|id" value="' . $trusted_id . '">';
						$HTML .= 'First Date <input type="text" id="budget|view|start" value="' . $date_start . '" />';
						$HTML .= '&nbsp;';
						$HTML .= 'Last Date <input type="text" id="budget|view|end" value="' . $date_end . '" />';
						$HTML .= '&nbsp;<button type="button" id="budget|view|update">Update</button>';
						$HTML .= '</div>';
						$HTML .= '</form>';
						$HTML .= '<h2 class="center">' . $budget_budget_class -> returnName ($trusted_id) . '</h2>';
						$HTML .= '<h3 class="center">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_budget_class -> returnDateStart ($trusted_id))) . ' ' . $_HOAM_language ['common']['to'] . ' ' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_budget_class -> returnDateEnd ($trusted_id))) . '</h3>';
						$HTML .= '<div id="budget_detail"></div>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/financial/budget/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;			
		case ('view'):
			$_HOAM_log -> add ('Loading /admin/financial/budget/view/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$budget_budget_class = new HOAM_budget_budget ('budget_budget');

					$trusted_id = $budget_budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_budget_class -> returnColumnSize ('id'));
					if ($budget_budget_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['view'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetViewJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['view'] . '</h1>';
						$date_start = $budget_budget_class -> returnDateStart ($trusted_id);
						$date_end = $budget_budget_class -> returnDateEnd ($trusted_id);
						if (($date_start < date ($_HOAM_country['date']['format_mysql'])) AND ($date_end > date ($_HOAM_country['date']['format_mysql']))) {
							$date_start = date ($_HOAM_country['date']['format_mysql'], mktime (0, 0, 0, date ('n'), 1, date ('Y')));
							$date_end = date ($_HOAM_country['date']['format_mysql'], mktime (0, 0, 0, date ('n'), date ('t'), date ('Y')));
						}
						$HTML .= '<form class="donotprint" id="budget|view|form" action="">';
						$HTML .= '<div>';
						$HTML .= '<input type="hidden" id="budget|view|id" value="' . $trusted_id . '">';
						$HTML .= 'First Date <input type="text" id="budget|view|start" value="' . $date_start . '" />';
						$HTML .= '&nbsp;';
						$HTML .= 'Last Date <input type="text" id="budget|view|end" value="' . $date_end . '" />';
						$HTML .= '&nbsp;<button type="button" id="budget|view|update">Update</button>';
						$HTML .= '</div>';
						$HTML .= '</form>';
						$HTML .= '<h2 class="center">' . $budget_budget_class -> returnName ($trusted_id) . '</h2>';
						$HTML .= '<h3 class="center">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_budget_class -> returnDateStart ($trusted_id))) . ' ' . $_HOAM_language ['common']['to'] . ' ' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_budget_class -> returnDateEnd ($trusted_id))) . '</h3>';
						$HTML .= '<div id="budget_detail"></div>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/financial/budget/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;			
		default:
			if (empty ($GLOBALS['global_url_array'][3])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['budget_budget'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['budget_budget'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/user.png" alt="' . $_HOAM_language ['field_names']['admin']['budget_budget'] . '" class="LHS" /><a href="/admin/financial/budget/">' . $_HOAM_language ['field_names']['admin']['budget_budget'] . '</a>';
					$HTML .= '<ul>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/budget/add/">' . $_HOAM_language ['field_names']['budget']['budget']['add'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/budget/list/">' . $_HOAM_language ['field_names']['budget']['budget']['list'] . '</a></li>';
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminBudgetCategory ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$budget_class = new HOAM_budget ('budget');
	$budget_category_class = new HOAM_budget_category ('budget_category');
	$group_members_class = new HOAM_group_members ('group_members');

	function HOAM_budgetCategoryList ($action = FALSE) {
		global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
		
		$budget_class = new HOAM_budget ('budget');
		$budget_category_class = new HOAM_budget_category ('budget_category');
		$group_members_class = new HOAM_group_members ('group_members');
		
		$_HOAM_assessment_list = &$_HOAM_language ['budget']['assessment'];
		
		$colgroup  = '<colgroup>';
		$colgroup .= '<col width="20%" />';
		$colgroup .= '<col width="15%" />';
		$colgroup .= '<col width="10%" />';
		if ($action) {
			$colgroup .= '<col width="10%" />';
		}
		$colgroup .= '<col width="35%" />';
		$colgroup .= '<col span="4" width="5%" />';
		$colgroup .= '</colgroup>';
		$thead  = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['category']['name'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['category']['parent'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['category']['type'] . '</th>';
		if ($action) {
			$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
		}
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['category']['description'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['category']['type'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['flags']['interest'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['flags']['fee_late'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['flags']['sales_tax'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['category']['users'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$tbody  = '<tbody id="budget|category|list">';
		$category_list = $budget_category_class -> listCategoryAll ();
		if (is_array ($category_list)) {
			foreach ($category_list as $category) {
				$category_info = $budget_category_class -> listAll ($category[0]);
				$category_info = $category_info [0];
				$tbody .=  '<tr>';
				if ($category_info['parent_category']) {
					$tbody .= '<td style="padding-left: 3em">' . html_encode (stripslashes ($category_info ['name'])) . '</td>';
				} else {
					$tbody .= '<td style="font-weight: bold">' . html_encode (stripslashes ($category_info ['name'])) . '</td>';
				}
				if ($category_info ['parent_category']) {
					$tbody .= '<td class="center">' . $budget_category_class -> returnName ($category_info ['parent_category']) . '</td>';
				} else {
					$tbody .= '<td class="center less-opacity">( ' . ucfirst ($_HOAM_language ['common']['none'])  . ' )</td>';
				}
				$tbody .= '<td class="center">';
				foreach ($_HOAM_assessment_list as &$key ) {
					if ((int) $_HOAM_setting ['budget']['flags'][$key['value']] & (int) $category_info ['flags']) {
						$tbody .= $key['name'];
						break;
					}
				}
				$tbody .= '</td>';
				if ($action) {
					$tbody .= '<td class="center">';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$tbody .= '<small><a href="/admin/financial/category/edit/' . $category_info['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
					}
					$tbody .= ' &nbsp; ';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$tbody .= '<small><a class="error" href="/admin/financial/category/delete/' . $category_info['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
					}
					$tbody .= '</td>';
				}
				$tbody .= '<td>' . html_encode (stripslashes ($category_info ['description'])) . '</td>';
				$tbody .= '<td class="center">';
				if ((int) $category_info ['flags'] & (int) $_HOAM_setting ['budget']['flags']['category_income']) {
					$tbody .= $_HOAM_language ['field_names']['budget']['category']['income'];
				} else {
					$tbody .= $_HOAM_language ['field_names']['budget']['category']['expense'];
				}
				$tbody .= '</td>';
				$tbody .= '<td class="center">';
				if ((int) $category_info ['flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_interest']) {
					$tbody .= '<span class="complete">' . $_HOAM_language ['common']['symbol']['check'] . '</span>';
				}
				$tbody .= '</td>';
				$tbody .= '<td class="center">';
				if ((int) $category_info ['flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_fee_late']) {
					$tbody .= '<span class="complete">' . $_HOAM_language ['common']['symbol']['check'] . '</span>';
				}
				$tbody .= '</td>';
				$tbody .= '<td class="center">';
				if ((int) $category_info ['flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_sales_tax']) {
					$tbody .= '<span class="complete">' . $_HOAM_language ['common']['symbol']['check'] . '</span>';
				}
				$tbody .= '</td>';
				$tbody .= '<td class="center">' . $budget_class -> returnCountCategoryId ($category_info['id']) . '</td>';
				$tbody .= '</tr>';
			}
		} else {
			$tbody .= '<tr><td colspan="7">' . $_HOAM_language ['errors']['budget']['category']['none'] . '</td></tr>';
		}
		$tbody .= '</tbody>';
		$HTML = '<table>' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody . '</table>';

		return ($HTML);
	}
	
	switch ($GLOBALS['global_url_array'][3]) {
		case ('add'):
			$_HOAM_log -> add ('Loading /admin/financial/category/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['category']['add'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['category']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
				$_HOAM_assessment_list = &$_HOAM_language ['budget']['assessment'];
				
				// OK, we're checking to see that the category we're trying to edit is the one that we
				// have information on in the category_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				// Also, trusted_id will /only/ be set if a category was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['category_information'])) {
						unset ($_SESSION['category_information']);
					}
				}

				$HTML .= '<form id="category_add" action="/hoam/scripts/budget/category_edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['budget']['category']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="category_information" title="' . $_HOAM_language ['form_tabs']['budget']['category']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formText ('budget|category|name', $_HOAM_language ['field_names']['budget']['category']['name'], $_SESSION['category_information']['trusted_budget|category|name'], '', $budget_category_class -> returnColumnSize ('name'), $_HOAM_setting ['budget']['minimum_name_length']);
				$HTML .= HOAM_formSelect ('budget|category|type', $_HOAM_language ['field_names']['budget']['category']['type']);
				if (isset ($_SESSION['category_information']['trusted_budget|category|type'])) {
    				$default_type = $_SESSION['category_information']['trusted_budget|category|type'];
    			} else {
    				$default_type = NULL;
    			}
    			foreach ($_HOAM_assessment_list as $assessment) {
    				$HTML .= '<option ';
    				if ($default_type == $_HOAM_setting ['budget']['flags'][$assessment['value']]) {
    					$HTML .= 'selected="selected" ';
    				}
    				$HTML .= 'value="' . $_HOAM_setting ['budget']['flags'][$assessment['value']] . '">' . $assessment['name'] . '</option>';
    			}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelect ('budget|category|track', $_HOAM_language ['field_names']['budget']['category']['track']);
				if (isset ($_SESSION['category_information']['trusted_budget|category|track'])) {
    				$default_type = $_SESSION['category_information']['trusted_budget|category|track'];
    			} else {
    				$default_type = NULL;
    			}
				if ($default_type >= 1) {
   					$HTML .= '<option value="0">' . $_HOAM_language ['field_names']['budget']['category']['expense'] . '</option>';
					$HTML .= '<option selected="selected" value="1">' . $_HOAM_language ['field_names']['budget']['category']['income'] . '</option>';
    			} else {
    				$HTML .= '<option selected="selected" value="0">' . $_HOAM_language ['field_names']['budget']['category']['expense'] . '</option>';
					$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['budget']['category']['income'] . '</option>';
    			}
				$HTML .= '</select>';
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formSelect ('budget|category|parent', $_HOAM_language ['field_names']['budget']['category']['[parent']);
				$category_parents = $budget_category_class -> ListParentCategories ();
    			if (isset ($_SESSION['category_information']['trusted_budget|category|parent'])) {
    				$default_parent = $_SESSION['category_information']['trusted_budget|category|parent'];
    			} else {
    				$default_parent = NULL;
    			}
    			$HTML .= '<option value="0">' . ucfirst ($_HOAM_language ['common']['none']) . '</option>';
    			foreach ($category_parents as $parent) {
    				$HTML .= '<option ';
    				if ($default_parent == $parent) {
    					$HTML .= 'selected="selected" ';
    				}
    				$HTML .= 'value="' . $parent . '">' . $budget_category_class -> returnName ($parent) . '</option>';
    			}
    			$HTML .= '</select>';
    			$HTML .= HOAM_formText ('budget|category|description', $_HOAM_language ['field_names']['budget']['category']['description'], $_SESSION['category_information']['trusted_budget|category|description'], '', $budget_category_class -> returnColumnSize ('description'), $_HOAM_setting ['budget']['minimum_description_length']);
    			
				$HTML .= '<label for="budget|category|flags">' . $_HOAM_language ['field_names']['budget']['category']['flags'] . '</label>';
				$HTML .= '<fieldset id="budget_category_flags" class="advanced_options">';
      			$HTML .= '<div class="checkbox_option">';
        		$HTML .= '<input ';
          		if (isset ($_SESSION['category_information']['trusted_budget|category|flags'])) {
          			if ((int) $_SESSION['category_information']['trusted_budget|category|flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_interest']) {
          				$HTML .= 'checked="checked" ';
					}
				}
        		$HTML .= 'type="checkbox" name="budget|category|flag|interest" value="1" />';
        		$HTML .= '<label for="budget|category|flag|interest">' . $_HOAM_language ['field_names']['budget']['invoice_interest'] . '</label>';
      			$HTML .= '</div>';
				$HTML .= '<div class="checkbox_option">';
        		$HTML .= '<input ';
				if (isset ($_SESSION['category_information']['trusted_budget|category|flags'])) {
					if ((int) $_SESSION['category_information']['trusted_budget|category|flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_fee_late']) {
						$HTML .= 'checked="checked" ';
					}
				}
				$HTML .= 'type="checkbox" name="budget|category|flag|late" value="1" />';
				$HTML .= '<label for="budget|category|flag|late">' . $_HOAM_language ['field_names']['budget']['invoice_late'] . '</label>';
				$HTML .= '</div>';
				$HTML .= '<div class="checkbox_option">';
				$HTML .= '<input ';
				if (isset ($_SESSION['category_information']['trusted_budget|category|flags'])) {
					if ((int) $_SESSION['category_information']['trusted_budget|category|flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_sales_tax']) {
						$HTML .= 'checked="checked" ';
					}
				}
				$HTML .= 'type="checkbox" name="budget|category|flag|sales-tax" value="1" />';
				$HTML .= '<label for="budget|category|flag|sales-tax">' . $_HOAM_language ['field_names']['budget']['invoice_taxable'] . '</label>';
				$HTML .= '</div>';
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End New Category Tab
				
				$HTML .= '<div class="tabbertab" id="budget_category_list" title="' . $_HOAM_language ['form_tabs']['budget']['category']['list'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_budgetCategoryList (); 
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Category List Tab

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('delete'):
			$_HOAM_log -> add ('Loading /admin/financial/category/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_category_id = $budget_category_class -> superClean ($GLOBALS['global_url_array'][4], $budget_category_class -> returnColumnSize ('id'));
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['category']['delete'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['category']['delete'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'forms.php');

					$_HOAM_assessment_list = &$_HOAM_language ['budget']['assessment'];
				
					// OK, we're checking to see that the category we're trying to edit is the one that we
					// have information on in the category_information variable.
					// If the referrer doesn't match this current page, then we know we came from
					// somewhere else on the site, and thus the information is suspect.
					// Also, trusted_id will /only/ be set if a category was being modified, not added.
					if (((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) OR (isset ($_SESSION['homeowner_information']['trusted_homeowner|id']) AND ($trusted_category_id != $_SESSION['category_information']['trusted_budget|category|id']))) {
						if (isset ($_SESSION['error_class_item'])) {
							unset ($_SESSION['error_class_item']);
						}
						if (isset ($_SESSION['category_information'])) {
							unset ($_SESSION['category_information']);
						}
					}

					$HTML .= '<form id="category_delete" action="/hoam/scripts/budget/category_edit_script.php" enctype="multipart/form-data" method="post">';
					$HTML .= '<input type="hidden" name="category|id" id="category|id" value="' . $trusted_category_id . '" />';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['delete'] . '</button>';
					$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="category_information" title="' . $_HOAM_language ['form_tabs']['budget']['category']['information'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formTextDisabled ('budget|category|name', $_HOAM_language ['field_names']['budget']['category']['name'], $budget_category_class -> returnName ($trusted_category_id));
					$HTML .= HOAM_formSelectDisabled ('budget|category|type', $_HOAM_language ['field_names']['budget']['category']['type']);
					if (isset ($_SESSION['category_information']['trusted_budget|category|type'])) {
    					$default_type = $_SESSION['category_information']['trusted_budget|category|type'];
    				} else {
      					foreach ($_HOAM_assessment_list as &$key ) {
      						if ((int) $_HOAM_setting ['budget']['flags'][$key['value']] & (int) $budget_category_class -> returnFlags ($trusted_category_id)) {
      							$default_type = $_HOAM_setting ['budget']['flags'][$key['value']];
       							break;
							}
						}
    				}
    				foreach ($_HOAM_assessment_list as $assessment) {
    					$HTML .= '<option ';
    					if ($default_type == $_HOAM_setting ['budget']['flags'][$assessment['value']]) {
	    					$HTML .= 'selected="selected" ';
    					}
    					$HTML .= 'value="' . $_HOAM_setting ['budget']['flags'][$assessment['value']] . '">' . $assessment['name'] . '</option>';
    				}
					$HTML .= '</select>';
					$HTML .= HOAM_formSelectDisabled ('budget|category|track', $_HOAM_language ['field_names']['budget']['category']['track']);
					if (isset ($_SESSION['category_information']['trusted_budget|category|track'])) {
    					$default_type = $_SESSION['category_information']['trusted_budget|category|track'];
	    			} else {
	    				if ($budget_category_class -> isBitSet ($trusted_category_id, $_HOAM_setting ['budget']['flags']['category_income'])) {
	    					$default_type = 1;
	    				} else {
	    					$default_type = NULL;
	    				}
    				}
					if ($default_type >= 1) {
   						$HTML .= '<option value="0">' . $_HOAM_language ['field_names']['budget']['category']['expense'] . '</option>';
						$HTML .= '<option selected="selected" value="1">' . $_HOAM_language ['field_names']['budget']['category']['income'] . '</option>';
	    			} else {
    					$HTML .= '<option selected="selected" value="0">' . $_HOAM_language ['field_names']['budget']['category']['expense'] . '</option>';
						$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['budget']['category']['income'] . '</option>';
    				}
					$HTML .= '</select>';
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_formSelect ('budget|category|parent', $_HOAM_language ['field_names']['budget']['category']['parent']);
					$category_parents = $budget_category_class -> ListParentCategories ();
    				if (isset ($_SESSION['category_information']['trusted_budget|category|parent'])) {
    					$default_parent = $_SESSION['category_information']['trusted_budget|category|parent'];
    				} else {
	    				$default_parent = $budget_category_class -> returnParentCategory ($trusted_category_id);
    				}
    				$HTML .= '<option value="0">' . ucfirst ($_HOAM_language ['common']['none']) . '</option>';
    				foreach ($category_parents as $parent) {
    					$HTML .= '<option ';
	    				if ($default_parent == $parent) {
    						$HTML .= 'selected="selected" ';
    					}
    					$HTML .= 'value="' . $parent . '">' . $budget_category_class -> returnName ($parent) . '</option>';
    				}
    				$HTML .= '</select>';
	    			$HTML .= HOAM_formTextDisabled ('budget|category|description', $_HOAM_language ['field_names']['budget']['category']['description'], $budget_category_class -> returnDescription ($trusted_category_id));
    			
					$HTML .= '<label for="budget|category|flags">' . $_HOAM_language ['field_names']['budget']['category']['flags'] . '</label>';
					$HTML .= '<fieldset id="budget_category_flags" class="advanced_options">';
      				$HTML .= '<div class="checkbox_option">';
    	    		$HTML .= '<input disabled="disabled" ';
	          		if (isset ($_SESSION['category_information']['trusted_budget|category|flags'])) {
          				if ((int) $_SESSION['category_information']['trusted_budget|category|flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_interest']) {
          					$HTML .= 'checked="checked" ';
						}
					} elseif ($budget_category_class -> isBitSet ($trusted_category_id, $_HOAM_setting ['budget']['flags']['apply_interest'])) {
						$HTML .= 'checked="checked" ';
					}
					$HTML .= 'type="checkbox" name="budget|category|flag|interest" value="1" />';
        			$HTML .= '<label for="budget|category|flag|interest">' . $_HOAM_language ['field_names']['budget']['invoice_interest'] . '</label>';
      				$HTML .= '</div>';
					$HTML .= '<div class="checkbox_option">';
    	    		$HTML .= '<input disabled="disabled" ';
					if (isset ($_SESSION['category_information']['trusted_budget|category|flags'])) {
						if ((int) $_SESSION['category_information']['trusted_budget|category|flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_fee_late']) {
							$HTML .= 'checked="checked" ';
						}
					} elseif ($budget_category_class -> isBitSet ($trusted_category_id, $_HOAM_setting ['budget']['flags']['apply_fee_late'])) {
						$HTML .= 'checked="checked" ';
					}
					$HTML .= 'type="checkbox" name="budget|category|flag|late" value="1" />';
					$HTML .= '<label for="budget|category|flag|late">' . $_HOAM_language ['field_names']['budget']['invoice_late'] . '</label>';
					$HTML .= '</div>';
					$HTML .= '<div class="checkbox_option">';
					$HTML .= '<input disabled="disabled" ';
					if (isset ($_SESSION['category_information']['trusted_budget|category|flags'])) {
						if ((int) $_SESSION['category_information']['trusted_budget|category|flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_sales_tax']) {
							$HTML .= 'checked="checked" ';
						}
					} elseif ($budget_category_class -> isBitSet ($trusted_category_id, $_HOAM_setting ['budget']['flags']['apply_sales_tax'])) {
						$HTML .= 'checked="checked" ';
					}
					$HTML .= 'type="checkbox" name="budget|category|flag|sales-tax" value="1" />';
					$HTML .= '<label for="budget|category|flag|sales-tax">' . $_HOAM_language ['field_names']['budget']['invoice_taxable'] . '</label>';
					$HTML .= '</div>';
					$HTML .= '</fieldset>';
					$HTML .= '</div>';
					$HTML .= '</div>'; // End New Category Tab
				
					$HTML .= '<div class="tabbertab" id="budget_category_list" title="' . $_HOAM_language ['form_tabs']['budget']['category']['list'] . '">';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_budgetCategoryList (); 
					$HTML .= '</div>';
					$HTML .= '</div>'; // End Category List Tab

					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
				} else {
					header ('Location: /admin/financial/category/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('edit'):
			$_HOAM_log -> add ('Loading /admin/financial/category/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_category_id = $budget_category_class -> superClean ($GLOBALS['global_url_array'][4], $budget_category_class -> returnColumnSize ('id'));
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['category']['edit'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['category']['edit'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'forms.php');

					$_HOAM_assessment_list = &$_HOAM_language ['budget']['assessment'];
				
					// OK, we're checking to see that the category we're trying to edit is the one that we
					// have information on in the category_information variable.
					// If the referrer doesn't match this current page, then we know we came from
					// somewhere else on the site, and thus the information is suspect.
					// Also, trusted_id will /only/ be set if a category was being modified, not added.
					if (((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) OR (isset ($_SESSION['homeowner_information']['trusted_homeowner|id']) AND ($trusted_category_id != $_SESSION['category_information']['trusted_budget|category|id']))) {
						if (isset ($_SESSION['error_class_item'])) {
							unset ($_SESSION['error_class_item']);
						}
						if (isset ($_SESSION['category_information'])) {
							unset ($_SESSION['category_information']);
						}
					}
					$HTML .= '<form id="category_edit" action="/hoam/scripts/budget/category_edit_script.php" enctype="multipart/form-data" method="post">';
					$HTML .= '<input type="hidden" name="category|id" id="category|id" value="' . $trusted_category_id . '" />';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
					$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="category_information" title="' . $_HOAM_language ['form_tabs']['budget']['category']['information'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formText ('budget|category|name', $_HOAM_language ['field_names']['budget']['category']['name'], $_SESSION['category_information']['trusted_budget|category|name'], $budget_category_class -> returnName ($trusted_category_id), $budget_category_class -> returnColumnSize ('name'), $_HOAM_setting ['budget']['minimum_name_length']);
					$HTML .= HOAM_formSelect ('budget|category|type', $_HOAM_language ['field_names']['budget']['category']['type']);
					if (isset ($_SESSION['category_information']['trusted_budget|category|type'])) {
    					$default_type = $_SESSION['category_information']['trusted_budget|category|type'];
    				} else {
      					foreach ($_HOAM_assessment_list as &$key ) {
      						if ((int) $_HOAM_setting ['budget']['flags'][$key['value']] & (int) $budget_category_class -> returnFlags ($trusted_category_id)) {
      							$default_type = $_HOAM_setting ['budget']['flags'][$key['value']];
       							break;
							}
						}
    				}
    				foreach ($_HOAM_assessment_list as $assessment) {
    					$HTML .= '<option ';
    					if ($default_type == $_HOAM_setting ['budget']['flags'][$assessment['value']]) {
	    					$HTML .= 'selected="selected" ';
    					}
    					$HTML .= 'value="' . $_HOAM_setting ['budget']['flags'][$assessment['value']] . '">' . $assessment['name'] . '</option>';
    				}
					$HTML .= '</select>';
					$HTML .= HOAM_formSelect ('budget|category|track', $_HOAM_language ['field_names']['budget']['category']['track']);
					if (isset ($_SESSION['category_information']['trusted_budget|category|track'])) {
    					$default_type = $_SESSION['category_information']['trusted_budget|category|track'];
	    			} else {
	    				if ($budget_category_class -> isBitSet ($trusted_category_id, $_HOAM_setting ['budget']['flags']['category_income'])) {
	    					$default_type = 1;
	    				} else {
	    					$default_type = NULL;
	    				}
    				}
					if ($default_type >= 1) {
   						$HTML .= '<option value="0">' . $_HOAM_language ['field_names']['budget']['category']['expense'] . '</option>';
						$HTML .= '<option selected="selected" value="1">' . $_HOAM_language ['field_names']['budget']['category']['income'] . '</option>';
	    			} else {
    					$HTML .= '<option selected="selected" value="0">' . $_HOAM_language ['field_names']['budget']['category']['expense'] . '</option>';
						$HTML .= '<option value="1">' . $_HOAM_language ['field_names']['budget']['category']['income'] . '</option>';
    				}
					$HTML .= '</select>';
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_formSelect ('budget|category|parent', $_HOAM_language ['field_names']['budget']['category']['parent']);
					$category_parents = $budget_category_class -> ListParentCategories ();
    				if (isset ($_SESSION['category_information']['trusted_budget|category|parent'])) {
    					$default_parent = $_SESSION['category_information']['trusted_budget|category|parent'];
    				} else {
	    				$default_parent = $budget_category_class -> returnParentCategory ($trusted_category_id);
    				}
    				$HTML .= '<option value="0">' . ucfirst ($_HOAM_language ['common']['none']) . '</option>';
    				foreach ($category_parents as $parent) {
    					$HTML .= '<option ';
	    				if ($default_parent == $parent) {
    						$HTML .= 'selected="selected" ';
    					}
    					$HTML .= 'value="' . $parent . '">' . $budget_category_class -> returnName ($parent) . '</option>';
    				}
    				$HTML .= '</select>';
	    			$HTML .= HOAM_formText ('budget|category|description', $_HOAM_language ['field_names']['budget']['category']['description'], $_SESSION['category_information']['trusted_budget|category|description'], $budget_category_class -> returnDescription ($trusted_category_id), $budget_category_class -> returnColumnSize ('description'), $_HOAM_setting ['budget']['minimum_description_length']);
    			
					$HTML .= '<label for="budget|category|flags">' . $_HOAM_language ['field_names']['budget']['category']['flags'] . '</label>';
					$HTML .= '<fieldset id="budget_category_flags" class="advanced_options">';
      				$HTML .= '<div class="checkbox_option">';
    	    		$HTML .= '<input ';
	          		if (isset ($_SESSION['category_information']['trusted_budget|category|flags'])) {
          				if ((int) $_SESSION['category_information']['trusted_budget|category|flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_interest']) {
          					$HTML .= 'checked="checked" ';
						}
					} elseif ($budget_category_class -> isBitSet ($trusted_category_id, $_HOAM_setting ['budget']['flags']['apply_interest'])) {
						$HTML .= 'checked="checked" ';
					}
					$HTML .= 'type="checkbox" name="budget|category|flag|interest" value="1" />';
        			$HTML .= '<label for="budget|category|flag|interest">' . $_HOAM_language ['field_names']['budget']['invoice_interest'] . '</label>';
      				$HTML .= '</div>';
					$HTML .= '<div class="checkbox_option">';
    	    		$HTML .= '<input ';
					if (isset ($_SESSION['category_information']['trusted_budget|category|flags'])) {
						if ((int) $_SESSION['category_information']['trusted_budget|category|flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_fee_late']) {
							$HTML .= 'checked="checked" ';
						}
					} elseif ($budget_category_class -> isBitSet ($trusted_category_id, $_HOAM_setting ['budget']['flags']['apply_fee_late'])) {
						$HTML .= 'checked="checked" ';
					}
					$HTML .= 'type="checkbox" name="budget|category|flag|late" value="1" />';
					$HTML .= '<label for="budget|category|flag|late">' . $_HOAM_language ['field_names']['budget']['invoice_late'] . '</label>';
					$HTML .= '</div>';
					$HTML .= '<div class="checkbox_option">';
					$HTML .= '<input ';
					if (isset ($_SESSION['category_information']['trusted_budget|category|flags'])) {
						if ((int) $_SESSION['category_information']['trusted_budget|category|flags'] & (int) $_HOAM_setting ['budget']['flags']['apply_sales_tax']) {
							$HTML .= 'checked="checked" ';
						}
					} elseif ($budget_category_class -> isBitSet ($trusted_category_id, $_HOAM_setting ['budget']['flags']['apply_sales_tax'])) {
						$HTML .= 'checked="checked" ';
					}
					$HTML .= 'type="checkbox" name="budget|category|flag|sales-tax" value="1" />';
					$HTML .= '<label for="budget|category|flag|sales-tax">' . $_HOAM_language ['field_names']['budget']['invoice_taxable'] . '</label>';
					$HTML .= '</div>';
					$HTML .= '</fieldset>';
					$HTML .= '</div>';
					$HTML .= '</div>'; // End New Category Tab
				
					$HTML .= '<div class="tabbertab" id="budget_category_list" title="' . $_HOAM_language ['form_tabs']['budget']['category']['list'] . '">';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_budgetCategoryList (); 
					$HTML .= '</div>';
					$HTML .= '</div>'; // End Category List Tab

					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
				} else {
					header ('Location: /admin/financial/category/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			$_HOAM_log -> add ('Loading /admin/financial/category/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['category']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['category']['list'] . '</h1>';
				$HTML .= HOAM_budgetCategoryList (TRUE); 
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][3])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['budget_category'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['budget_category'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/user.png" alt="' . $_HOAM_language ['field_names']['admin']['budget_category'] . '" class="LHS" /><a href="/admin/financial/category/">' . $_HOAM_language ['field_names']['admin']['budget_category'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/category/add/">' . $_HOAM_language ['field_names']['budget']['category']['add'] . '</a></li>';
					}
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/category/list/">' . $_HOAM_language ['field_names']['budget']['category']['list'] . '</a></li>';
					}
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminBudgetEntry ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;
	
	$group_members_class = new HOAM_group_members ('group_members');

	switch ($GLOBALS['global_url_array'][3]) {
		case ('edit'):
			$_HOAM_log -> add ('Loading /admin/financial/entry/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$budget_class = new HOAM_budget_budget ('budget_budget');
					$budget_tracking_class = new HOAM_budget_track ('budget_track');

					$trusted_id = $budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_class -> returnColumnSize ('id'));
					if ($budget_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['delete'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetBudgetJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
					
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if an account was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['budget_information'])) {
								unset ($_SESSION['budget_information']);
							}
						}

						$HTML .= '<form id="budget_delete" action="/hoam/scripts/budget/budget_edit_script.php" enctype="multipart/form-data" method="post">';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['budget']['budget']['delete'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="account_information" title="' . $_HOAM_language ['form_tabs']['budget']['budget']['delete'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<label>' . $_HOAM_language ['field_names']['budget']['budget']['category']['selected'] . '</label>';
						$category_list = $budget_tracking_class -> listCategories ($trusted_id);
						$x = 1;
						foreach ($category_list as $category) {
							$HTML .= '<input type="text" name="budget|budget|' . $x . '" size="' . $budget_tracking_class -> returnColumnSize ('amount') . '"></input>';
						}
						$HTML .= '</div>';
						$HTML .= '</div>'; // End New Budget Tab
				
						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/financial/budget/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			$_HOAM_log -> add ('Loading /admin/financial/entry/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['list'] . '</h1>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][3])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['financial'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['financial'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/user.png" alt="' . $_HOAM_language ['field_names']['admin']['financial'] . '" class="LHS" /><a href="/admin/financial/budget/">' . $_HOAM_language ['field_names']['admin']['financial'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/budget/list/">' . $_HOAM_language ['field_names']['budget']['budget']['list'] . '</a></li>';
					}
					$HTML .= '</ul>';
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminBudgetHomeowner ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$budget_class = new HOAM_budget ('budget');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$group_members_class = new HOAM_group_members ('group_members');
	
	switch ($GLOBALS['global_url_array'][3]) {
		case ('invoice') :
			$_HOAM_log -> add ('Loading /admin/financial/homeowner/invoice/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					include_once (HOAM_PATH_FUNCTION . 'budget.php');
					$trusted_customer_id = $budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_class -> returnColumnSize ('customer_id'));
					// This check needs to be added back in once all of the
					// customer_id stuff is rethought.
					// if ($_SESSION['budget_customer_class_item'] -> idExists ($trusted_customer_id)) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['invoice']['current'] . ' (' . HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_customer_id)) . ')')));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['invoice']['current'] . '</h1>';
					
					$HTML .= '<div class="center donotprint">';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<small><a href="/admin/homeowner/edit/' . $trusted_customer_id . '/">' . $_HOAM_language ['action']['homeowner']['edit'] . '</a></small>';
						$HTML .= ' &nbsp; ';
					}
					$HTML .= '<small><a href="/admin/financial/homeowner/review/' . $trusted_customer_id . '/">' . $_HOAM_language ['action']['budget']['review'] . '</a></small>';
					$HTML .= '</div>';
					
					$HTML .= '{{LETTER_NOPRINT}}';
					$HTML .= HOAM_budgetInvoiceGenerate ($trusted_customer_id);
				} else {
					header ('Location: /admin/financial/homeowner/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('invoice_list') :
			$_HOAM_log -> add ('Loading /admin/financial/homeowner/invoice_list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['invoice_list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['invoice_list'] . '</h1>';
				include_once (HOAM_PATH_FUNCTION . 'budget.php');
				$HTML .= '{{LETTER_NOPRINT}}';
				$unpaid_list = $budget_class -> listCustomersWithOpenCharges ();
				foreach ($unpaid_list as $current_customer) {
		        	$total_amount_due = $budget_class -> returnTotalAmountDue ($current_customer);
					//	if (($total_amount_due < 286.41) AND ($total_amount_due > 0)){
					if ($total_amount_due > 0){
						$HTML .= HOAM_budgetInvoiceGenerate ($current_customer);
						// If this homeowner does NOT receive mail at their
						// residence, should we send a copy to the residence?
						if ((!$homeowner_class -> isBitSet ($current_customer, $_HOAM_setting ['homeowner']['flags']['residence_mailing'])) AND
							$_HOAM_setting ['homeowner']['send_current_resident']) {
							$HTML .= HOAM_budgetInvoiceGenerate ($current_customer);
						}
					}
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('invoice_pdf') :
			$_HOAM_log -> add ('Loading /admin/financial/homeowner/invoice_mockup/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_customer_id = $budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_class -> returnColumnSize ('customer_id'));
					require_once (HOAM_PATH_FUNCTION . 'budget.php');
					
					$HTML .= HOAM_budgetInvoiceGeneratePDF ($trusted_customer_id);
				} else {
					header ('Location: /admin/financial/homeowner/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			$_HOAM_log -> add ('Loading /admin/financial/homeowner/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['customer']['list'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetCustomerListJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['customer']['list'] . '</h1>';
				$HTML .= '<div class="center">';
				$HTML .= '<input type="checkbox" id="showPrevious" />' . $_HOAM_language ['field_names']['homeowner']['previous'];
				$HTML .= '<input type="checkbox" id="showZero" />' . $_HOAM_language ['field_names']['budget']['zero'];
				$HTML .= '</div>';
				$colgroup .= '<colgroup>';
				$colgroup .= '<col span="1" width="20%" />';
				$colgroup .= '<col span="1" width="35%" />';
				$colgroup .= '<col span="1" width="15%" />';
				$colgroup .= '<col span="1" width="10%" char="." />';
				$colgroup .= '<col span="1" width="10%" />';
				$colgroup .= '<col span="1" width="10%" />';
				$colgroup .= '</colgroup>';
				$thead .= '<thead><tr>';
				$thead .= '<th id="sort_residence">' . $_HOAM_language ['tables']['column']['lot']['residence'] . '</th>';
				$thead .= '<th id="sort_customer">' . $_HOAM_language ['tables']['column']['homeowner']['name'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th id="sort_due">' . $_HOAM_language ['tables']['column']['budget']['delinquent_amount'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['last_payment'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['flags'] . '</th>';
				$thead .= '</tr></thead>';
				$tfoot .= '<tfoot><tr>';
				$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['lot']['residence'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['name'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['budget']['delinquent_amount'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['budget']['last_payment'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['generic']['flags'] . '</th>';
				$tfoot .= '</tr></tfoot>';
				$tbody .= '<tbody id="budget_customer_list_tbody">';
				$customer_list = $budget_class -> listCustomers ();
				if (is_array ($customer_list)) {
					foreach ($customer_list as $customer) {
						if ($homeowner_class -> isCurrentOwner ($customer)) {
							$tbody .= '<tr>';
						} else {
							$tbody .= '<tr class="previous_owner">';
						}
						// Who is the current owner for this lot?
						$tbody .= '<td>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($customer)) . '</td>';
						$tbody .= '<td>' . html_encode (stripslashes ($homeowner_class -> returnName ($customer))) . '</td>';

						$tbody .= '<td>';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/homeowner/edit/' . $customer . '/">' . $_HOAM_language ['action']['homeowner']['edit'] . '</a></small>';
							$tbody .= ' &nbsp; ';
						}
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/financial/homeowner/review/' . $customer . '/">' . $_HOAM_language ['action']['budget']['review'] . '</a></small>';
							$tbody .= ' &nbsp; ';
							$tbody .= '<small><a href="/admin/financial/homeowner/invoice/' . $customer . '/">' . $_HOAM_language ['action']['budget']['invoice'] . '</a></small>';
						}
						$tbody .= '</td>';
						$amount_due = $budget_class -> returnTotalAmountDue ($customer);
						if ($amount_due > 0) {
							$oldest_charge = $budget_class -> returnOldestCharge ($customer);
							if (date ($_HOAM_country ['date']['format_mysql']) <= $budget_class -> returnDateLate ($oldest_charge)) {
								$tbody .= '<td class="caution currency">' . HOAM_returnCurrency ($amount_due) . '</td>';
							} else {
								$tbody .= '<td class="error currency">' . HOAM_returnCurrency ($amount_due) . '</td>';
							}
						} else {
							$tbody .= '<td class="currency">' . HOAM_returnCurrency ($amount_due) . '</td>';
						}
						$last_payment = $budget_class -> returnLastPayment ($customer);
						$tbody .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDatePosted ($last_payment))) . '</td>';
						$tbody .= '<td>';
						if (!$homeowner_class -> isBitSet ($customer, $_HOAM_setting ['homeowner']['flags']['resident'])) {
							$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['abbr']['lr']) . '">R</span> ';
						}
						$budget_flags = $homeowner_class -> returnFlags ($customer, 'budget_flags');
						$homeowner_flags = $homeowner_class -> returnFlags ($customer);
						switch (TRUE) {
							case ((int) $budget_flags & (int) $_HOAM_setting ['homeowner']['flags']['ignore_budget']) :
								$tbody .= '<span class="caution cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['homeowner']['flags']['ignore_budget']) . '">E</span> ';
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan']) :
								$tbody .= '<span class="complete cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['payment_plan_description']) . '">P</span> ';
							case ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan_default']) :
								$tbody .= '<span class="complete cursor_help warning" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['payment_plan_default_description']) . '">P</span> ';
						}
						if ((int) $homeowner_flags & (int) $_HOAM_setting ['homeowner']['flags']['bankrupt']) {
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
						}
						if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_lien']) {
							$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_lien_description']) . '">L</span> ';
						}
						$tbody .= '</td>';
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="6">' . $_HOAM_language ['errors']['homeowner']['none-found'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= '<table class="center">' . $colgroup . $thead . $tfoot . '<tbody>'. $tbody . '<tbody></table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('payment_list') :
			$_HOAM_log -> add ('Loading /admin/financial/homeowner/payment_list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['payment_list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['payment_list'] . '</h1>';
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_customer_id = $budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_class -> returnColumnSize ('customer_id'));
					$query = "SELECT id FROM budget WHERE customer_id='$trusted_customer_id' AND flags & " . ($_HOAM_setting ['budget']['flags']['credit'] + $_HOAM_setting ['budget']['flags']['payment']);
					$payment_list = $budget_class -> listSimpleQuery ($query);
					$HTML .= '<table class="center">';
					$HTML .= '<colgroup>';
					$HTML .= '<col width="15%" />';
					$HTML .= '<col width="20%" />';
					$HTML .= '<col width="35%" />';
					$HTML .= '<col width="10%" span="3" />';
					$HTML .= '</colgroup>';
					$thead .= '<thead>';
					$thead .= '<tr>';
					$thead .= '<th>' . $_HOAM_language ['tables']['column']['lot']['residence'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['name'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['memo'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['date'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['amount'] . '</th>';
					$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['check'] . '</th>';
					$thead .= '</tr>';
					$thead .= '</thead>';
					$HTML .= $thead . preg_replace ('/thead/', 'tfoot', $thead); 
					$HTML .= '<tbody>';
					if (is_array ($payment_list)) {
						foreach ($payment_list as $payment) {
							$tbody .= '<tr>';
							$tbody .= '<td>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_customer_id)) . '</td>';
							$tbody .= '<td>' . html_encode (stripslashes ($homeowner_class -> returnName ($trusted_customer_id))) . '</td>';
							$tbody .= '<td>' . html_encode (stripslashes ($budget_class -> returnMemo ($payment))) . '</td>';
							$tbody .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateCreated ($payment))) . '</td>';
							$total_amount += $budget_class -> returnAmount ($payment);
							$tbody .= '<td>' . HOAM_returnCurrency ($budget_class -> returnAmount ($payment)) . '</td>';
							$tbody .= '<td>' . $budget_class -> returnCheckNumber ($payment) . '</td>';
							$tbody .= '</tr>';
						}
					} else {
						 $tbody = '<tr><td colspan="6">' . $_HOAM_language ['errors']['budget']['no_entries'] . '</td></tr>';
					}
					$HTML .= $tbody;
					$HTML .= '</tbody>';
					$HTML .= '</table>';
					$HTML .= 'Total Paid: ' . HOAM_returnCurrency ($total_amount);
				} else {
					// The customer ID was not specified, or the ID specified doesn't exist.
					header ('Location: /error/unknown_id/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('review') :
			$_HOAM_log -> add ('Loading /admin/financial/homeowner/review/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_customer_id = $budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_class -> returnColumnSize ('customer_id'));
					// This check needs to be changed to use the customer_id 
					// once all of the customer_id stuff is rethought.
					if ($homeowner_class -> idExists ($trusted_customer_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['customer']['review'] . ' (' . HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_customer_id)) . ')')));
						require_once (HOAM_PATH_FUNCTION . 'budget.php');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['customer']['review'] . '</h1>';

						$HTML .= '<div class="center donotprint">';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$HTML .= '<small><a href="/admin/homeowner/edit/' . $trusted_customer_id . '/">' . $_HOAM_language ['action']['homeowner']['edit'] . '</a></small>';
							$HTML .= ' &nbsp; ';
						}
						$HTML .= '<small><a href="/admin/financial/homeowner/invoice/' . $trusted_customer_id . '/">' . $_HOAM_language ['action']['budget']['invoice'] . '</a></small>';
						$HTML .= '</div>';
						
						$HTML .= HOAM_budgetStatementHeader ($trusted_customer_id);
						$HTML .= HOAM_budgetStatementCategories ($trusted_customer_id);
						$HTML .= HOAM_budgetAccountHistory ($trusted_customer_id);
					} else {
						// The customer ID was not specified, or the ID specified doesn't exist.
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/financial/homeowner/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('review2') :
			$_HOAM_log -> add ('Loading /admin/financial/homeowner/review/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_customer_id = $budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_class -> returnColumnSize ('customer_id'));
					// This check needs to be changed to use the customer_id 
					// once all of the customer_id stuff is rethought.
					if ($homeowner_class -> idExists ($trusted_customer_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['customer']['review'] . ' (' . HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_customer_id)) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetReviewJS');
						require_once (HOAM_PATH_FUNCTION . 'budget.php');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['customer']['review'] . '</h1>';

						$HTML .= '<div class="center donotprint">';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$HTML .= '<small><a href="/admin/homeowner/edit/' . $trusted_customer_id . '/">' . $_HOAM_language ['action']['homeowner']['edit'] . '</a></small>';
							$HTML .= ' &nbsp; ';
						}
						$HTML .= '<small><a href="/admin/financial/homeowner/invoice/' . $trusted_customer_id . '/">' . $_HOAM_language ['action']['budget']['invoice'] . '</a></small>';
						$HTML .= '</div>';
						
						$HTML .= HOAM_budgetStatementHeader ($trusted_customer_id);
						$HTML .= HOAM_budgetStatementCategories ($trusted_customer_id);
						$HTML .= HOAM_budgetAccountHistory2 ($trusted_customer_id);
					} else {
						// The customer ID was not specified, or the ID specified doesn't exist.
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/financial/homeowner/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('statement_list') :
			$_HOAM_log -> add ('Loading /admin/financial/homeowner/statement_list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['statement_list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['statement_list'] . '</h1>';
				include_once (HOAM_PATH_FUNCTION . 'budget.php');
				$HTML .= '{{LETTER_NOPRINT}}';
				$unpaid_list = $budget_class -> listCustomersWithOpenCharges ();
				foreach ($unpaid_list as $current_customer) {
		        	$total_amount_due = $budget_class -> returnTotalAmountDue ($current_customer);
					if (($total_amount_due > 0) AND ((int) $homeowner_class -> returnFlags ($current_customer, 'budget_flags') & (int) $_HOAM_setting ['budget']['flags']['late_attorney'])) {
//						$HTML .= HOAM_budgetStatementAttorney ($current_customer);
						$HTML .= HOAM_budgetStatementHeader ($current_customer);
						$HTML .= HOAM_budgetStatementCategories ($current_customer);
						$HTML .= HOAM_budgetAccountHistory ($current_customer);
						$HTML .= '<br style="page-break-after: always" />';
					}
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][3])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['financial'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['financial'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/user.png" alt="' . $_HOAM_language ['field_names']['admin']['budget_homeowner'] . '" class="LHS" /><a href="/admin/financial/homeowner/">' . $_HOAM_language ['field_names']['admin']['budget_homeowner'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/homeowner/invoice/">' . $_HOAM_language ['field_names']['budget']['invoice']['current'] . '</a></li>';
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/homeowner/invoice_list/">' . $_HOAM_language ['field_names']['budget']['invoice_list'] . '</a></li>';
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/homeowner/statement_list/">' . $_HOAM_language ['field_names']['budget']['statement_list'] . '</a></li>';
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/group.png\')"><a href="/admin/financial/homeowner/list/">' . $_HOAM_language ['field_names']['budget']['customer']['list'] . '</a></li>';
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/homeowner/review/">' . $_HOAM_language ['field_names']['budget']['customer']['review'] . '</a></li>';
					}
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminBudgetVendor ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$group_members_class = new HOAM_group_members ('group_members');

	function HOAM_budgetVendorList () {
		global $_HOAM_country, $_HOAM_language, $_HOAM_setting;

		$budget_class = new HOAM_budget ('budget');
		$budget_category_class = new HOAM_budget_category ('budget_category');
		$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
		$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
		$group_members_class = new HOAM_group_members ('group_members');
		$_HOAM_term_list = &$_HOAM_language ['budget']['term'];
		
		$colgroup  = '<colgroup>';
		$colgroup .= '<col width="25%" />';
		$colgroup .= '<col span="5" width="15%" />';
		$colgroup .= '</colgroup>';
		$thead  = '<thead><tr>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['name'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['contact'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['category'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['last_invoice'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['last_payment'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['term'] . '</th>';
		$thead .= '</tr></thead>';
		$tbody  = '<tbody id="budget|category|list">';
		$vendor_list = $budget_vendor_class -> listVendors ();
		if (is_array ($vendor_list)) {
			foreach ($vendor_list as $vendor) {
				$vendor_info = $budget_vendor_class -> listAll ($vendor);
				$vendor_info = $vendor_info [0];
				$tbody .= '<tr>';
				$tbody .= '<td>' . html_encode (stripslashes ($vendor_info ['name'])) . '</td>';
				$tbody .= '<td>' . '</td>';
				$tbody .= '<td>' . html_encode (stripslashes ($budget_category_class -> returnName ($vendor_info ['category_id']))) . '</td>';
				$last_invoice = $budget_class -> returnLastInvoiceVendor ($vendor_info ['id']);
				$tbody .= '<td>';
				if ($last_invoice) {
					$invoice_charge = $budget_class -> listChargesOnInvoice ($last_invoice);
					$tbody .= date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateDue ($invoice_charge[0])));
					unset ($invoice_charge);
				} else {
					$tbody .= '<span class="less-opacity">' . $_HOAM_language ['common']['na'] . '</span>';
				}
				unset ($last_invoice);
				$tbody .= '</td>';
				$last_payment = $budget_class -> returnLastPaymentVendor ($vendor);
				$tbody .= '<td>';
				if ($last_payment) {
					$tbody .= date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDatePosted ($last_payment)));	
				} else {
					$tbody .= '<span class="less-opacity">' . $_HOAM_language ['common']['na'] . '</span>';
				}
				unset ($last_payment);
				$tbody .= '</td>';
				$tbody .= '<td>';
   				foreach ($_HOAM_term_list as $term) {
   					if ((int) $_HOAM_setting ['budget']['flags'][$term['value']] & (int) $vendor_info['flags']) {
   						$tbody .= $term['name'];
   						break;  
   					}
   				}
				$tbody .= '</td>';
				$tbody .= '</tr>';
			}
		} else {
			$tbody .= '<tr><td colspan="7">' . $_HOAM_language ['errors']['budget']['vendor']['none'] . '</td></tr>';
		}
		$tbody .= '</tbody>';
		$HTML = '<table class="center">' . $colgroup . $thead . $tbody . '</table>';

		return ($HTML);
	}
	
	switch ($GLOBALS['global_url_array'][3]) {
		case ('add'):
			$_HOAM_log -> add ('Loading /admin/financial/vendor/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['vendor']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['vendor']['add'] . '</h1>';
				$budget_category_class = new HOAM_budget_category ('budget_category');
				$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
					
				require_once (HOAM_PATH_FUNCTION . 'forms.php');

				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetVendorJS');
				
				$_HOAM_term_list = &$_HOAM_language ['budget']['term'];
					
				$HTML .= '<form id="vendor_edit" action="/hoam/scripts/budget/vendor_edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['budget']['vendor']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="vendor_information" title="' . $_HOAM_language ['form_tabs']['budget']['vendor']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formText ('budget|vendor|name', $_HOAM_language ['field_names']['budget']['vendor']['name'], $_SESSION['vendor_information']['trusted_budget|vendor|name'], '', $budget_vendor_class -> returnColumnSize ('name'), $_HOAM_setting ['budget']['minimum_name_length']);
				$HTML .= HOAM_formSelect ('budget|vendor|category', $_HOAM_language ['field_names']['budget']['vendor']['category']);
				if (isset ($_SESSION['vendor_information']['trusted_budget|vendor|category'])) {
   					$default_category = $_SESSION['vendor_information']['trusted_budget|vendor|category'];
    			} else {
   					$default_category = NULL;
   				}
 				$category_list = $budget_category_class -> listCategoryAll ();
				$optgroup = FALSE;
				foreach ($category_list as $category) {
					// Is this a parent category?
					if ($category[1] == 0) {
						if ($optgroup) {
							$HTML .= '</optgroup>';
						} else {
							$optgroup = TRUE;
						}
						$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
						continue;
					}
					$HTML .= '<option '; 
					if ($category[0] == $default_category) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
				}
				if ($optgroup) {
					$HTML .= '</optgroup>';
				}
   				$HTML .= '</select>';
				$HTML .= HOAM_formSelect ('budget|vendor|term', $_HOAM_language ['field_names']['budget']['vendor']['term']);
				if (isset ($_SESSION['vendor_information']['trusted_budget|vendor|term'])) {
   					$default_type = $_SESSION['vendor_information']['trusted_budget|vendor|term'];
    			} else {
   					$default_type = NULL;
   				}
   				foreach ($_HOAM_term_list as $term) {
   					$HTML .= '<option ';
   					if ($default_type == $term['value']) {
   						$HTML .= 'selected="selected" ';
    				}
   					$HTML .= 'value="' . $_HOAM_setting ['budget']['flags'][$term['value']] . '">' . $term['name'] . '</option>';
   				}
				$HTML .= '</select>';
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formText ('budget|vendor|address1', $_HOAM_language ['field_names']['budget']['vendor']['address1'], $_SESSION['vendor_information']['trusted_budget|vendor|address1'], ''); 
				$HTML .= HOAM_formText ('budget|vendor|address2', $_HOAM_language ['field_names']['budget']['vendor']['address2'], $_SESSION['vendor_information']['trusted_budget|vendor|address2'], '');
				$HTML .= HOAM_formText ('budget|vendor|address3', $_HOAM_language ['field_names']['budget']['vendor']['address3'], $_SESSION['vendor_information']['trusted_budget|vendor|address3'], '');
				$HTML .= HOAM_formText ('budget|vendor|city', $_HOAM_language ['field_names']['budget']['vendor']['city'], $_SESSION['vendor_information']['trusted_budget|vendor||city'], $_HOAM_setting ['organization']['mailing_address']['city'], $budget_vendor_class -> returnColumnSize ('city'), 1);
				$HTML .= HOAM_formSelect ('budget|vendor|state', $_HOAM_language ['field_names']['budget']['vendor']['state']);
				if (isset ($_SESSION['vendor_information']['trusted_budget|vendor|state'])) {
					$default_state = $_SESSION['vendor_information']['trusted_budget|vendor|state'];
				} else {
					$default_state = $_HOAM_setting ['organization']['mailing_address']['state'];
				}
				foreach ($_HOAM_country ['state'] as $state) {
					$HTML .= '<option ';
					if ($state['value'] == $default_state) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $state['value'] . '">' . $state['description'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formField ('budget|vendor|postalcode', $_HOAM_language ['field_names']['budget']['vendor']['postalcode'], $_SESSION['vendor_information']['trusted_budget|vendor|postalcode'], '', $budget_vendor_class -> returnColumnSize ('postalcode'), $_HOAM_country ['postalcode']['minlength']);
				$HTML .= HOAM_formField ('budget|vendor|telephone|work', $_HOAM_language ['field_names']['budget']['vendor']['telephone']['work'], $_SESSION['vendor_information']['trusted_budget|vendor|telephone|work'], '', $budget_vendor_class -> returnColumnSize ('telephone_work'), $_HOAM_country ['telephone']['length']);
				$HTML .= HOAM_formField ('budget|vendor|telephone|fax', $_HOAM_language ['field_names']['budget']['vendor']['telephone']['fax'], $_SESSION['vendor_information']['trusted_budget|vendor|telephone|fax'], '', $budget_vendor_class -> returnColumnSize ('telephone_fax'), $_HOAM_country ['telephone']['length']);
				$HTML .= HOAM_formText ('budget|vendor|email', $_HOAM_language ['field_names']['budget']['vendor']['email'], $_SESSION['vendor_information']['trusted_budget|vendor|email'], '', $budget_vendor_class -> returnColumnSize ('email'));
				$HTML .= HOAM_formText ('budget|vendor|website', $_HOAM_language ['field_names']['budget']['vendor']['website'], $_SESSION['vendor_information']['trusted_budget|vendor|website'], '', $budget_vendor_class -> returnColumnSize ('website'));
				$HTML .= '<label for="budget|vendor|attachments">' . $_HOAM_language ['field_names']['budget']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('budget|vendor|attachments');
				$HTML .= HOAM_formDisplayError ('budget|vendor|attachments');
				$HTML .= '</label>';
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= HOAM_formCheckbox ('budget|vendor|ignore_average', $_HOAM_language ['field_names']['budget']['flags']['ignore_average'], 1, $_SESSION['vendor_information']['trusted_budget|vendor|ignore_average'], 0);
				$HTML .= '<br />';
				$HTML .= '<br />';
				$HTML .= HOAM_formField ('budget|vendor|federal_id', $_HOAM_language ['field_names']['budget']['vendor']['federal_id'], $_SESSION['vendor_information']['trusted_budget|vendor|federal_id'], '', $budget_vendor_class -> returnColumnSize ('federal_id'), $_HOAM_country ['vendor']['federal_id']['minlength']);
				$HTML .= HOAM_formField ('budget|vendor|state_id', $_HOAM_language ['field_names']['budget']['vendor']['state_id'], $_SESSION['vendor_information']['trusted_budget|vendor|state_id'], '', $budget_vendor_class -> returnColumnSize ('state_id'), $_HOAM_country ['vendor']['state_id']['minlength']);
				$HTML .= '</div>';

				$HTML .= '</div>'; // End New Vendor Tab
				
				$HTML .= '<div class="tabbertab" id="budget_vendor_list" title="' . $_HOAM_language ['form_tabs']['budget']['vendor']['list'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_budgetVendorList (); 
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Vendor List Tab

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('delete'):
			$_HOAM_log -> add ('Loading /admin/financial/vendor/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					
					$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
					
					$trusted_vendor_id = $budget_vendor_class -> superClean ($GLOBALS['global_url_array'][4], $budget_vendor_class -> returnColumnSize ('id'));
					if ($budget_vendor_class -> idExists ($trusted_vendor_id)) {
						$budget_category_class = new HOAM_budget_category ('budget_category');
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['vendor']['delete'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetVendorJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['vendor']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
						$_HOAM_term_list = &$_HOAM_language ['budget']['term'];
					
						// OK, we're checking to see that the category we're trying to edit is the one that we
						// have information on in the category_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if a category was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['vendor_information'])) {
								unset ($_SESSION['vendor_information']);
							}
						}
	
						$HTML .= '<form id="vendor_delete" action="/hoam/scripts/budget/vendor_edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="vendor|id" id="vendor|id" value="' . $trusted_vendor_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" id="delete_button" name="delete">' . $_HOAM_language ['form_buttons']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="vendor_information" title="' . $_HOAM_language ['form_tabs']['budget']['vendor']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formTextDisabled ('budget|vendor|name', $_HOAM_language ['field_names']['budget']['vendor']['name'], $budget_vendor_class -> returnName ($trusted_vendor_id));
						$HTML .= HOAM_formSelectDisabled ('budget|vendor|category', $_HOAM_language ['field_names']['budget']['vendor']['category']);
   						$default_category = $budget_vendor_class -> returnCategoryId ($trusted_vendor_id);
		 				$category_list = $budget_category_class -> listCategoryAll ();
						$optgroup = FALSE;
						foreach ($category_list as $category) {
							// Is this a parent category?
							if ($category[1] == 0) {
								if ($optgroup) {
									$HTML .= '</optgroup>';
								} else {
									$optgroup = TRUE;
								}
								$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
								continue;
							}
							$HTML .= '<option '; 
							if ($category[0] == $default_category) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
						}
						if ($optgroup) {
							$HTML .= '</optgroup>';
						}
	    				$HTML .= '</select>';
						$HTML .= HOAM_formSelectDisabled ('budget|vendor|term', $_HOAM_language ['field_names']['budget']['vendor']['term']);
						if (isset ($_SESSION['vendor_information']['trusted_budget|vendor|term'])) {
    						$default_type = $_SESSION['vendor_information']['trusted_budget|vendor|term'];
	    				} else {
    						$default_type = NULL;
    						$flags = $budget_vendor_class -> returnFlags ($trusted_vendor_id);
		    				foreach ($_HOAM_term_list as $term) {
		    					if ((int) $_HOAM_setting ['budget']['flags'][$term['value']] & (int) $flags) {
	    							$default_type = $term['value'];
	    							break;  
	    						}
	    					}
	    				}
    					foreach ($_HOAM_term_list as $term) {
    						$HTML .= '<option ';
    						if ($default_type == $term['value']) {
    							$HTML .= 'selected="selected" ';
	    					}
    						$HTML .= 'value="' . $_HOAM_setting ['budget']['flags'][$term['value']] . '">' . $term['name'] . '</option>';
	    				}
						$HTML .= '</select>';
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formTextDisabled ('budget|vendor|email', $_HOAM_language ['field_names']['budget']['vendor']['email'], $budget_vendor_class -> returnEmail ($trusted_vendor_id));
						$HTML .= HOAM_formTextDisabled ('budget|vendor|website', $_HOAM_language ['field_names']['budget']['vendor']['website'], $budget_vendor_class -> returnWebsite ($trusted_vendor_id));
						$HTML .= '<label for="budget|vendor|attachments">' . $_HOAM_language ['field_names']['budget']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('budget|vendor|attachments');
						$HTML .= HOAM_formDisplayError ('budget|vendor|attachments');
						$HTML .= '</label>';
						$attachment_class = new HOAM_attachments ('attachments');
						$attachment_list = $attachment_class -> listWithOwner ($trusted_vendor_id, $_HOAM_setting ['attachment']['flags']['budget_vendor']);
						if (!empty ($attachment_list)) {
							$year = date ('Y', strtotime ($attachment_class -> returnDateCreated ($attachment_list[0])));
							$HTML .= '<span style="font-weight: bold" field="year">&#x0229F; ' . $year . '</span>';
							$HTML .= '<div>';
							foreach ($attachment_list as $attachment) {
								if (mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4) == $year) {
								} else {
									$year = mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4);
									$HTML .= '</div>';
									$HTML .= '<span style="clear: left; display: block" field="year">&#x0229E; ' . $year . '</span>';
									$HTML .= '<div style="display: none">';
								}
								$HTML .= HOAM_attachmentReturnHTML ($attachment);
							}
							$HTML .= '</div>';
							$HTML .= '<br class="clear" />';
						}
						$HTML .= '<div>';
						$HTML .= '<input disabled="disabled" type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= HOAM_formCheckboxDisabled ('budget|vendor|ignore_average', $_HOAM_language ['field_names']['budget']['flags']['ignore_average'], 1, $budget_vendor_class -> isBitSet ($trusted_vendor_id, $_HOAM_setting ['budget']['flags']['ignore_average']));
						$HTML .= '<br />';
						$HTML .= '<br />';
						$HTML .= HOAM_formFieldDisabled ('budget|vendor|federal', $_HOAM_language ['field_names']['budget']['vendor']['federal_id'], $budget_vendor_class -> returnFederalId ($trusted_vendor_id), $budget_vendor_class -> returnColumnSize ('federal_id'));
						$HTML .= HOAM_formFieldDisabled ('budget|vendor|state', $_HOAM_language ['field_names']['budget']['vendor']['state_id'], $budget_vendor_class -> returnStateId ($trusted_vendor_id), $budget_vendor_class -> returnColumnSize ('state_id'));
						$HTML .= '</div>';
	
						$HTML .= '</div>'; // End New Vendor Tab
				
						$HTML .= '<div class="tabbertab" id="budget_vendor_list" title="' . $_HOAM_language ['form_tabs']['budget']['vendor']['list'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_budgetVendorList (); 
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Vendor List Tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/financial/vendor/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('edit'):
			$_HOAM_log -> add ('Loading /admin/financial/vendor/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
					
					$trusted_vendor_id = $budget_vendor_class -> superClean ($GLOBALS['global_url_array'][4], $budget_vendor_class -> returnColumnSize ('id'));
					if ($budget_vendor_class -> idExists ($trusted_vendor_id)) {
						$budget_category_class = new HOAM_budget_category ('budget_category');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['vendor']['edit'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetVendorJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['vendor']['edit'] . '</h1>';
				
						$_HOAM_term_list = &$_HOAM_language ['budget']['term'];
					
						// OK, we're checking to see that the category we're trying to edit is the one that we
						// have information on in the category_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if a category was being modified, not added.
						if (((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) OR (isset ($_SESSION['homeowner_information']['trusted_homeowner|id']) AND ($trusted_vendor_id != $_SESSION['vendor_information']['trusted_budget|vendor|id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['vendor_information'])) {
								unset ($_SESSION['vendor_information']);
							}
						}

						$HTML .= '<form id="vendor_edit" action="/hoam/scripts/budget/vendor_edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="vendor|id" id="vendor|id" value="' . $trusted_vendor_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="vendor_information" title="' . $_HOAM_language ['form_tabs']['budget']['vendor']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formText ('budget|vendor|name', $_HOAM_language ['field_names']['budget']['vendor']['name'], $_SESSION['vendor_information']['trusted_budget|vendor|name'], stripslashes ($budget_vendor_class -> returnName ($trusted_vendor_id)), $budget_vendor_class -> returnColumnSize ('name'), $_HOAM_setting ['budget']['minimum_name_length']);
						$HTML .= HOAM_formSelect ('budget|vendor|category', $_HOAM_language ['field_names']['budget']['vendor']['category']);
						if (isset ($_SESSION['vendor_information']['trusted_budget|vendor|category'])) {
    						$default_category = $_SESSION['vendor_information']['trusted_budget|vendor|category'];
		    			} else {
    						$default_category = $budget_vendor_class -> returnCategoryId ($trusted_vendor_id);
    					}
    					$category_list = $budget_category_class -> ListCategoryAll ();
						$optgroup = FALSE;
						foreach ($category_list as $category) {
							// Is this a parent category?
							if ($category[1] == 0) {
								if ($optgroup) {
									$HTML .= '</optgroup>';
								} else {
									$optgroup = TRUE;
								}
								$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
								continue;
							}
							$HTML .= '<option '; 
							if ($category[0] == $default_category) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
						}
						if ($optgroup) {
							$HTML .= '</optgroup>';
						}
	    				$HTML .= '</select>';
						$HTML .= HOAM_formSelect ('budget|vendor|term', $_HOAM_language ['field_names']['budget']['vendor']['term']);
						if (isset ($_SESSION['vendor_information']['trusted_budget|vendor|term'])) {
    						$default_type = $_SESSION['vendor_information']['trusted_budget|vendor|term'];
	    				} else {
    						$default_type = NULL;
    						$flags = $budget_vendor_class -> returnFlags ($trusted_vendor_id);
		    				foreach ($_HOAM_term_list as $term) {
		    					if ((int) $_HOAM_setting ['budget']['flags'][$term['value']] & (int) $flags) {
	    							$default_type = $term['value'];
	    							break;  
	    						}
	    					}
	    				}
    					foreach ($_HOAM_term_list as $term) {
    						$HTML .= '<option ';
    						if ($default_type == $term['value']) {
    							$HTML .= 'selected="selected" ';
	    					}
    						$HTML .= 'value="' . $_HOAM_setting ['budget']['flags'][$term['value']] . '">' . $term['name'] . '</option>';
	    				}
						$HTML .= '</select>';
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formText ('budget|vendor|address1', $_HOAM_language ['field_names']['budget']['vendor']['address1'], $_SESSION['vendor_information']['trusted_budget|vendor|address1'], $budget_vendor_class -> returnAddress1 ($trusted_vendor_id)); 
						$HTML .= HOAM_formText ('budget|vendor|address2', $_HOAM_language ['field_names']['budget']['vendor']['address2'], $_SESSION['vendor_information']['trusted_budget|vendor|address2'], $budget_vendor_class -> returnAddress2 ($trusted_vendor_id));
						$HTML .= HOAM_formText ('budget|vendor|address3', $_HOAM_language ['field_names']['budget']['vendor']['address3'], $_SESSION['vendor_information']['trusted_budget|vendor|address3'], $budget_vendor_class -> returnAddress3 ($trusted_vendor_id));
						$HTML .= HOAM_formText ('budget|vendor|city', $_HOAM_language ['field_names']['budget']['vendor']['city'], $_SESSION['vendor_information']['trusted_budget|vendor||city'], $budget_vendor_class -> returnCity ($trusted_vendor_id), $budget_vendor_class -> returnColumnSize ('city'), 1);
						$HTML .= HOAM_formSelect ('budget|vendor|state', $_HOAM_language ['field_names']['budget']['vendor']['state']);
						if (isset ($_SESSION['vendor_information']['trusted_budget|vendor|state'])) {
							$default_state = $_SESSION['vendor_information']['trusted_budget|vendor|state'];
						} else {
							$default_state = $budget_vendor_class -> returnState ($trusted_vendor_id);
						}
						foreach ($_HOAM_country ['state'] as $state) {
							$HTML .= '<option ';
							if ($state['value'] == $default_state) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $state['value'] . '">' . $state['description'] . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formField ('budget|vendor|postalcode', $_HOAM_language ['field_names']['budget']['vendor']['postalcode'], $_SESSION['vendor_information']['trusted_budget|vendor|postalcode'], $budget_vendor_class -> returnPostalcode ($trusted_vendor_id), $budget_vendor_class -> returnColumnSize ('postalcode'), $_HOAM_country ['postalcode']['minlength']);
						$HTML .= HOAM_formField ('budget|vendor|telephone|work', $_HOAM_language ['field_names']['budget']['vendor']['telephone']['work'], $_SESSION['vendor_information']['trusted_budget|vendor|telephone|work'], preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $budget_vendor_class -> returnTelephoneWork ($trusted_vendor_id), 1), $budget_vendor_class -> returnColumnSize ('telephone_work'), $_HOAM_country ['telephone']['length']);
						$HTML .= HOAM_formField ('budget|vendor|telephone|fax', $_HOAM_language ['field_names']['budget']['vendor']['telephone']['fax'], $_SESSION['vendor_information']['trusted_budget|vendor|telephone|fax'], preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $budget_vendor_class -> returnTelephoneFax ($trusted_vendor_id), 1), $budget_vendor_class -> returnColumnSize ('telephone_fax'), $_HOAM_country ['telephone']['length']);
						$HTML .= HOAM_formText ('budget|vendor|email', $_HOAM_language ['field_names']['budget']['vendor']['email'], $_SESSION['vendor_information']['trusted_budget|vendor|email'], $budget_vendor_class -> returnEmail ($trusted_vendor_id), $budget_vendor_class -> returnColumnSize ('email'));
						$HTML .= HOAM_formText ('budget|vendor|website', $_HOAM_language ['field_names']['budget']['vendor']['website'], $_SESSION['vendor_information']['trusted_budget|vendor|website'], $budget_vendor_class -> returnWebsite ($trusted_vendor_id), $budget_vendor_class -> returnColumnSize ('website'));
						$HTML .= '<label for="budget|vendor|attachments">' . $_HOAM_language ['field_names']['budget']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('budget|vendor|attachments');
						$HTML .= HOAM_formDisplayError ('budget|vendor|attachments');
						$HTML .= '</label>';
						$attachment_class = new HOAM_attachments ('attachments');
						$attachment_list = $attachment_class -> listWithOwner ($trusted_vendor_id, $_HOAM_setting ['attachment']['flags']['budget_vendor']);
						if (!empty ($attachment_list)) {
							$year = date ('Y', strtotime ($attachment_class -> returnDateCreated ($attachment_list[0])));
							$HTML .= '<span style="font-weight: bold" field="year">&#x0229F; ' . $year . '</span>';
							$HTML .= '<div>';
							foreach ($attachment_list as $attachment) {
								if (mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4) == $year) {
								} else {
									$year = mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4);
									$HTML .= '</div>';
									$HTML .= '<span style="clear: left; display: block" field="year">&#x0229E; ' . $year . '</span>';
									$HTML .= '<div style="display: none">';
								}
								$HTML .= HOAM_attachmentReturnHTML ($attachment);
							}
							$HTML .= '</div>';
							$HTML .= '<br class="clear" />';
						}
						$HTML .= '<div>';
						$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= HOAM_formCheckbox ('budget|vendor|ignore_average', $_HOAM_language ['field_names']['budget']['flags']['ignore_average'], 1, $_SESSION['vendor_information']['trusted_budget|vendor|ignore_average'], $budget_vendor_class -> isBitSet ($trusted_vendor_id, $_HOAM_setting ['budget']['flags']['ignore_average']));
						$HTML .= '<br />';
						$HTML .= '<br />';
						$HTML .= HOAM_formField ('budget|vendor|federal_id', $_HOAM_language ['field_names']['budget']['vendor']['federal_id'], $_SESSION['vendor_information']['trusted_budget|vendor|federal_id'], preg_replace ($_HOAM_country ['vendor']['federal_id']['match'], $_HOAM_country ['vendor']['federal_id']['format'], $budget_vendor_class -> returnFederalId ($trusted_vendor_id), 1), $budget_vendor_class -> returnColumnSize ('federal_id'), $_HOAM_country ['vendor']['federal_id']['minlength']);
						$HTML .= HOAM_formField ('budget|vendor|state_id', $_HOAM_language ['field_names']['budget']['vendor']['state_id'], $_SESSION['vendor_information']['trusted_budget|vendor|state_id'], preg_replace ($_HOAM_country ['vendor']['state_id']['match'], $_HOAM_country ['vendor']['state_id']['format'], $budget_vendor_class -> returnStateId ($trusted_vendor_id), 1), $budget_vendor_class -> returnColumnSize ('state_id'), $_HOAM_country ['vendor']['state_id']['minlength']);
						$HTML .= '</div>';
	
						$HTML .= '</div>'; // End New Vendor Tab
				
						$HTML .= '<div class="tabbertab" id="budget_vendor_list" title="' . $_HOAM_language ['form_tabs']['budget']['vendor']['list'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_budgetVendorList (); 
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Vendor List Tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/financial/vendor/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('invoice') :
			$HTML = HOAM_adminBudgetVendorInvoice (TRUE);
			break;
		case ('list') :
			$_HOAM_log -> add ('Loading /admin/financial/vendor/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['vendor']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['vendor']['list'] . '</h1>';

				$budget_class = new HOAM_budget ('budget');
				$budget_category_class = new HOAM_budget_category ('budget_category');
				$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
				$work_request_class = new HOAM_work_request ('work_requests');
				$_HOAM_term_list = &$_HOAM_language ['budget']['term'];
						
				$colgroup  = '<colgroup>';
				$colgroup .= '<col width="22%" />';
				$colgroup .= '<col span="6" width="13%" />';
				$colgroup .= '</colgroup>';
				$thead  = '<thead><tr>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['name'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['contact'] . '</th>';
				$thead .= '<th class="donotprint">' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['category'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['last_invoice'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['last_payment'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['term'] . '</th>';
				$thead .= '</tr></thead>';
				$tbody  = '<tbody id="budget|category|list">';
				$vendor_list = $budget_vendor_class -> listVendors ();
				if (is_array ($vendor_list)) {
					foreach ($vendor_list as $vendor) {
						$vendor_info = $budget_vendor_class -> listAll ($vendor);
						$vendor_info = $vendor_info [0];
						$tbody .= '<tr>';
						$tbody .= '<td class="center">' . html_encode (stripslashes ($vendor_info ['name'])) . '</td>';
						$tbody .= '<td class="center">' . '</td>';
						$tbody .= '<td class="center donotprint">';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/financial/vendor/edit/' . $vendor_info['id'] . '/" title="' . $_HOAM_language ['action']['generic']['edit'] . '">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
							$tbody .= ' &nbsp; ';
						}
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a class="error" href="/admin/financial/vendor/delete/' . $vendor_info['id'] . '/" title="' . $_HOAM_language ['action']['generic']['delete'] . '">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
							$tbody .= ' &nbsp; ';
						}
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							if ($work_request_class -> listVendorRequest ($vendor_info ['id'])) {
								$tbody .= '<small><a href="/admin/work_request/vendor/' . $vendor_info['id'] . '/">' . $_HOAM_language ['action']['work_request']['vendor'] . '</a></small>';
								$tbody .= ' &nbsp; ';
							}
						}
						$tbody .= '<small><a href="/admin/financial/vendor/view/' . $vendor_info['id'] . '/" title="' . $_HOAM_language ['action']['generic']['view'] . '">' . $_HOAM_language ['action']['generic']['view'] . '</a></small>';
						$tbody .= '</td>';
						$tbody .= '<td class="center">' . html_encode (stripslashes ($budget_category_class -> returnName ($vendor_info ['category_id']))) . '</td>';
						$last_invoice = $budget_class -> returnLastInvoiceVendor ($vendor);
						$tbody .= '<td class="center">';
						if ($last_invoice) {
							$invoice_charge = $budget_class -> listChargesOnInvoice ($last_invoice);
							$tbody .= date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateDue ($invoice_charge[0])));
							unset ($invoice_charge);	
						} else {
							$tbody .= '<span class="less-opacity">' . $_HOAM_language ['common']['na'] . '</span>';
						}
						unset ($last_invoice);
						$tbody .= '</td>';
						$last_payment = $budget_class -> returnLastPaymentVendor ($vendor);
						$tbody .= '<td class="center">';
						if ($last_payment) {
							$tbody .= date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDatePosted ($last_payment)));	
						} else {
							$tbody .= '<span class="less-opacity">' . $_HOAM_language ['common']['na'] . '</span>';
						}
						unset ($last_payment);
						$tbody .= '</td>';
						$tbody .= '<td class="center">';
   						foreach ($_HOAM_term_list as $term) {
		   					if ((int) $_HOAM_setting ['budget']['flags'][$term['value']] & (int) $vendor_info['flags']) {
   								$tbody .= $term['name'];
   								break;  
		   					}
   						}
						$tbody .= '</td>';
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="7" class="center">' . $_HOAM_language ['errors']['budget']['vendor']['none'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= sprintf ($_HOAM_language ['tables']['generic']['default'], $colgroup, $thead . preg_replace ('/thead/', 'tfoot', $thead), $tbody);
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('view'):
			$_HOAM_log -> add ('Loading /admin/financial/vendor/view/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {

					$budget_class = new HOAM_budget ('budget');					
					$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
					$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
					$_HOAM_term_list = &$_HOAM_language ['budget']['term'];
					
					$trusted_vendor_id = $budget_vendor_class -> superClean ($GLOBALS['global_url_array'][4], $budget_vendor_class -> returnColumnSize ('id'));
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['vendor']['view'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['vendor']['view'] . '</h1>';
				
					if ($budget_vendor_class -> idExists ($trusted_vendor_id)) {
						$HTML .= '<h2>' . html_encode (stripslashes ($budget_vendor_class -> returnName ($trusted_vendor_id))) . '</h2>';
						$HTML .= '<h3>' . 'Contact Information' . '</h3>';
						if ($budget_vendor_class -> returnAddress3 ($trusted_vendor_id)) {
							if ($budget_vendor_class -> returnAddress1 ($trusted_vendor_id)) {
								$HTML .= html_encode (stripslashes ($budget_vendor_class -> returnAddress1 ($trusted_vendor_id))) . '<br />';
							}
							if ($budget_vendor_class -> returnAddress2 ($trusted_vendor_id)) {
								$HTML .= html_encode (stripslashes ($budget_vendor_class -> returnAddress2 ($trusted_vendor_id))) . '<br />';
							}
							if ($budget_vendor_class -> returnAddress3 ($trusted_vendor_id)) {
								$HTML .= html_encode (stripslashes ($budget_vendor_class -> returnAddress3 ($trusted_vendor_id))) . '<br />';
							}
							$HTML .= $budget_vendor_class -> returnCity ($trusted_vendor_id) . ', ';
							$HTML .= $budget_vendor_class -> returnState ($trusted_vendor_id) . ' ';
							$HTML .= preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $budget_vendor_class -> returnPostalcode ($trusted_vendor_id), 1);
							$HTML .= '<br />';
							if ($budget_vendor_class -> returnTelephoneWork ($trusted_vendor_id)) {
								$HTML .= '<br />' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $budget_vendor_class -> returnTelephoneWork ($trusted_vendor_id), 1) . ' ' . ucfirst ($_HOAM_language ['common']['work']);
							}
							if ($budget_vendor_class -> returnTelephoneFax ($trusted_vendor_id)) {
								$HTML .= '<br />' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $budget_vendor_class -> returnTelephoneFax ($trusted_vendor_id), 1) . ' ' . ucfirst ($_HOAM_language ['common']['fax']);
							}
							$HTML .= '<br />';
							if ($budget_vendor_class -> returnEmail ($trusted_vendor_id)) {
								$HTML .= '<br /><a href="mailto:' . $budget_vendor_class -> returnEmail ($trusted_vendor_id) . '">' . $budget_vendor_class -> returnEmail ($trusted_vendor_id) . '</a>';
							}
							if ($budget_vendor_class -> returnWebsite ($trusted_vendor_id)) {
								$HTML .= '<br /><a href="' . $budget_vendor_class -> returnWebsite ($trusted_vendor_id) . '">' . $budget_vendor_class -> returnWebsite ($trusted_vendor_id) . '</a>';
							}
						} else {
							$HTML .= '<span style="color: grey; font-style: italic">' . $_HOAM_language ['errors']['generic']['none_entered'] . '</span>';							
						}
						$HTML .= '<h3>' . 'General Information' . '</h3>';
						$HTML .= '<ul style="list-style: none">';
						if ($budget_vendor_class -> returnFederalId ($trusted_vendor_id)) {
							$HTML .= '<li>' . $_HOAM_language ['field_names']['budget']['vendor']['federal_id'] . ': ' . preg_replace ($_HOAM_country ['vendor']['federal_id']['match'], $_HOAM_country ['vendor']['federal_id']['format'], $budget_vendor_class -> returnFederalId ($trusted_vendor_id), 1) . '</li>';
						} else {
							$HTML .= '<li>' . $_HOAM_language ['field_names']['budget']['vendor']['federal_id'] . ': <span style="color: grey; font-style: italic">' . $_HOAM_language ['errors']['generic']['none_entered'] . '</span></li>';
						}
						if ($budget_vendor_class -> returnStateId ($trusted_vendor_id)) {
							$HTML .= '<li>' . $_HOAM_language ['field_names']['budget']['vendor']['state_id'] . ': ' . preg_replace ($_HOAM_country ['vendor']['state_id']['match'], $_HOAM_country ['vendor']['state_id']['format'], $budget_vendor_class -> returnStateId ($trusted_vendor_id), 1) . '</li>';
						} else {
							$HTML .= '<li>' . $_HOAM_language ['field_names']['budget']['vendor']['state_id'] . ': <span style="color: grey; font-style: italic">' . $_HOAM_language ['errors']['generic']['none_entered'] . '</span></li>';
						}
   						foreach ($_HOAM_term_list as $term) {
		   					if ((int) $_HOAM_setting ['budget']['flags'][$term['value']] & (int) $budget_vendor_class -> returnFlags ($trusted_vendor_id)) {
   								$HTML .= '<li>Payment Terms: ' . $term['name'] . '</li>';
   								break;  
				   			}
   						}
						$HTML .= '</ul>';
						$HTML .= '<h3>' . 'Invoices' . '</h3>';
						$total_amount_due = 0;
						$total_amount_paid = 0;
						$colgroup = '<colgroup>';
						$colgroup .= '</colgroup>';
						$thead = '<thead>';
						$thead .= '<tr>';
						$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['invoice_due_date'] . '</th>';
						$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['invoice']['number'] . '</th>';
						$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['invoice']['amount'] . '</th>';
						$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['delinquent_amount'] . '</th>';
						$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
						$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['last_payment'] . '</th>';
						$thead .= '</tr>'; 
						$thead .= '</thead>';
						$tbody = '';
						$invoice_list = $budget_invoice_class -> listInvoicesForVendor ($trusted_vendor_id);
						if (is_array ($invoice_list)) {
							foreach ($invoice_list as $invoice) {
								$tbody .= '<tr>';
								$amount_due = $budget_invoice_class -> returnTotalAmountDue ($invoice);
								$total_amount_due += $amount_due;
								$date_due = $budget_invoice_class -> returnDateDue ($invoice);
								$last_payment = $budget_class -> returnDatePosted ($budget_class -> returnLastPaymentInvoice ($invoice));
								$today = date ($_HOAM_country ['date']['format_mysql']); 
								if (($today >= $date_due) AND ($amount_due > 0)) {
									if ($today == $date_due) {
										$tbody .= '<td class="caution center">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($date_due)) . '</td>';
									} else {
										$tbody .= '<td class="warning center">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($date_due)) . '</td>';
									}
								} else {
									$tbody .= '<td class="center">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($date_due)) . '</td>';
								}

								$tbody .= '<td class="center">' . $budget_invoice_class -> returnNumber ($invoice) . '</td>';
								$invoice_charges = $budget_class -> listChargesOnInvoice ($invoice);
								$invoice_amount = 0;
								foreach ($invoice_charges as $charge) {
									$charge_amount = $budget_class -> returnAmount ($charge);
									if ($charge_amount > 0) {
										$invoice_amount += $charge_amount;
									}
								}
								$total_amount_paid += $invoice_amount;
								$tbody .= '<td class="currency">' . HOAM_returnCurrency ($invoice_amount) . '</td>';
								$tbody .= '<td class="currency">' . HOAM_returnCurrency ($amount_due) . '</td>';
								$tbody .= '<td class="center donotprint">';
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
									$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
									($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
									$tbody .= '<small><a class="error" href="/admin/financial/vendor/invoice/delete/' . $invoice . '/" title="' . $_HOAM_language ['action']['generic']['delete'] . '">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
									$tbody .= ' &nbsp;';
								}
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
									$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
									$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
									($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
									if ($amount_due > 0) {
										$tbody .= '<small><a href="/admin/financial/vendor/invoice/payment/' . $invoice . '/">' . $_HOAM_language ['action']['budget']['pay'] . '</a></small>';
										$tbody .= ' &nbsp;';
									}
								}
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
									$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
									($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
									$tbody .= '<small><a href="/admin/financial/view/' . $invoice . '/" title="' . $_HOAM_language ['action']['generic']['view'] . '">' . $_HOAM_language ['action']['generic']['view'] . '</a></small>';
									$tbody .= ' &nbsp;';
								}
								$tbody .= '</td>';
								$tbody .= '<td class="center">';
								if ($last_payment) {
									$tbody .= date ($_HOAM_country ['date']['format_reports'], strtotime ($last_payment));
								} else {
									$tbody .= $_HOAM_language ['common']['na'];
								}
								$tbody .= '</td>';
								$tbody .= '</tr>';
							}
						} else {
							$tbody .= '<tr><td colspan="7" class="center">' . $_HOAM_language ['errors']['budget']['invoice']['none'] . '</td></tr>';
						}
						$HTML .= 'Total Invoiced: ' . HOAM_returnCurrency ($total_amount_paid);
						$HTML .= ' (' . HOAM_returnCurrency ($budget_invoice_class -> returnAverageInvoice ($trusted_vendor_id)) . ' ' . ucfirst ($_HOAM_language ['common']['average']);
						$HTML .= ', ' . HOAM_returnCurrency ($total_amount_due) . ' Due)';
						$HTML .= '<table>' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody . '</table>';
					} else {
						$HTML .= $_HOAM_language ['errors']['generic']['invalid-id'];
					}
				} else {
					header ('Location: /admin/financial/vendor/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][3])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['budget_vendor'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['budget_vendor'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/lorry.png" alt="' . $_HOAM_language ['field_names']['admin']['budget_vendor'] . '" class="LHS" /><a href="/admin/financial/vendor/">' . $_HOAM_language ['field_names']['admin']['budget_vendor'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/lorry_add.png\')"><a href="/admin/financial/vendor/add/">' . $_HOAM_language ['field_names']['budget']['vendor']['add'] . '</a></li>';
					}
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/vendor/list/">' . $_HOAM_language ['field_names']['budget']['vendor']['list'] . '</a></li>';
					}
					$HTML .= '<li style="list-style: none">' . HOAM_adminBudgetVendorInvoice () . '</li>';
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminBudgetVendorInvoice ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$budget_class = new HOAM_budget ('budget');
	$group_members_class = new HOAM_group_members ('group_members');

	switch ($GLOBALS['global_url_array'][4]) {
		case ('add'):
			$_HOAM_log -> add ('Loading /admin/financial/vendor/invoice/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['vendor']['invoice'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetVendorInvoiceJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['vendor']['invoice'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
			
				$budget_category_class = new HOAM_budget_category ('budget_category');
				$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
				$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');

				// OK, we're checking to see that the invoice we're trying to edit is the one that we
				// have information on in the invoice_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				// Also, trusted_id will /only/ be set if an invoice was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['invoice_information']['trusted_id']))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['invoice_information'])) {
						unset ($_SESSION['invoice_information']);
					}
				}

				$HTML .= '<form id="new_invoice" action="/hoam/scripts/budget/vendor_invoice_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['budget']['invoice']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';
			
				$HTML .= '<div class="tabbertab" id="add_invoice" title="' . $_HOAM_language ['form_tabs']['budget']['invoice']['add'] . '">';

				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelect ('budget|invoice|vendor', $_HOAM_language ['field_names']['budget']['invoice']['vendor']);
				if (isset ($_SESSION ['invoice_information']['trusted_budget|invoice|vendor'])) {
					$default_vendor = $_SESSION ['invoice_information']['trusted_budget|invoice|vendor'];
				} else {
					$default_vendor = NULL;
				}
				$vendor_list = $budget_vendor_class -> listAllId ('name', 'ASC');
				foreach ($vendor_list as $vendor) {
					$HTML .= '<option ';
					if ($vendor == $default_vendor) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $vendor . '">' . stripslashes ($budget_vendor_class -> returnName ($vendor)) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formField ('budget|invoice|amount', $_HOAM_language ['field_names']['budget']['invoice']['amount'], number_format ($_SESSION['invoice_information']['trusted_budget|invoice|amount'], 2), '', 10, 1);
				// Real hacky way to do this, should really be done through a
				// realtime Javascript popup, or at least cleaner integration.
				if ((isset ($_SESSION['error_class_item'])) AND ($_SESSION['error_class_item'] -> ShowCategory ('budget|invoice|amount') != '')) {
					$HTML .= HOAM_formCheckbox ('budget|invoice|amount|ignore', $_HOAM_language ['errors']['generic']['ignore'], 1, 0, 0);
				}
				$HTML .= HOAM_formField ('budget|invoice|number', $_HOAM_language ['field_names']['budget']['invoice']['number'], $_SESSION['invoice_information']['trusted_budget|invoice|number'], '', $budget_invoice_class -> returnColumnSize ('number'), 1);
				// Real hacky way to do this, should really be done through a
				// realtime Javascript popup, or at least cleaner integration.
				if ((isset ($_SESSION['error_class_item'])) AND ($_SESSION['error_class_item'] -> ShowCategory ('budget|invoice|number') != '')) {
					$HTML .= HOAM_formCheckbox ('budget|invoice|number|ignore', $_HOAM_language ['errors']['generic']['ignore'], 1, 0, 0);
				}
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formText ('budget|invoice|memo', $_HOAM_language ['field_names']['budget']['invoice']['memo'], $_SESSION['invoice_information']['trusted_budget|invoice|memo'], '', $budget_class -> returnColumnSize ('memo'), 1);
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelect ('budget|invoice|category', $_HOAM_language ['field_names']['budget']['invoice']['category']);
				if (isset ($_SESSION['invoice_information']['trusted_budget|invoice|category'])) {
					$default_category = $_SESSION['invoice_information']['trusted_budget|invoice|category'];
				} else {
					$default_category = NULL;
				}
				$category_list = $budget_category_class -> listCategoryAll ();
				$optgroup = FALSE;
				foreach ($category_list as $category) {
					// Is this a parent category?
					if ($category[1] == 0) {
						if ($optgroup) {
							$HTML .= '</optgroup>';
						} else {
							$optgroup = TRUE;
						}
						$HTML .= '<optgroup label="' . $budget_category_class -> returnName ($category[0]) . '">';
						continue;
					}
					$HTML .= '<option '; 
					if ($category[0] == $default_category) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $category[0] . '">' . $budget_category_class -> returnName ($category[0]) . '</option>';
				}
				if ($optgroup) {
					$HTML .= '</optgroup>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formDate ('budget|invoice|datedue', $_HOAM_language ['field_names']['budget']['invoice']['date']['due'], $_SESSION['invoice_information']['trusted_budget|invoice|datedue'], HOAM_date ($_HOAM_country ['date']['format_reports']));
				// Real hacky way to do this, should really be done through a
				// realtime Javascript popup, or at least cleaner integration.
				if ((isset ($_SESSION['error_class_item'])) AND ($_SESSION['error_class_item'] -> ShowCategory ('budget|invoice|datedue') != '')) {
					$HTML .= HOAM_formCheckbox ('budget|invoice|datedue|ignore', $_HOAM_language ['errors']['generic']['ignore'], 1, 0, 0);
				}
				$HTML .= '</div>';
				
				$HTML .= '<div class="optional">';
				$HTML .= '<label for="budget|invoice|attachments">' . $_HOAM_language ['field_names']['budget']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('budget|invoice|attachments');
				$HTML .= HOAM_formDisplayError ('budget|invoice|attachments');
				$HTML .= '</label>';
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= '</div>';

				$HTML .= '</div>'; // End Invoice Add

				$HTML .= '<div class="tabbertab" id="advanced" title="' . $_HOAM_language ['form_tabs']['budget']['vendor']['payment'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formSelect ('budget|payment|method', $_HOAM_language ['field_names']['budget']['payment']['method']);
				switch ($_SESSION ['invoice_information']['trusted_budget|payment|method']) {
					case 'autodraft':
						$HTML .= '<option selected="selected" value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
						$HTML .= '<option value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
						$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
						break;
					case 'check':
						$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
						$HTML .= '<option selected="selected" value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
						$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
						break;
					case 'credit':
						$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
						$HTML .= '<option value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
						$HTML .= '<option selected="selected" value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
						break;
					default: 
						$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
						$HTML .= '<option selected="selected" value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
						$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
						break;
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formFieldHidden ('budget|payment|autodraft', $_HOAM_language ['field_names']['budget']['payment']['autodraft'], $_SESSION ['invoice_information']['trusted_budget|payment|autodraft'], '', $budget_class -> returnColumnSize ('checknum'), 1);
				$HTML .= HOAM_formField ('budget|payment|checknum', $_HOAM_language ['field_names']['budget']['payment']['checknum'], $_SESSION ['invoice_information']['trusted_budget|payment|checknum'], '', $budget_class -> returnColumnSize ('checknum'), 1);
				// Real hacky way to do this, should really be done through a
				// realtime Javascript popup, or at least cleaner integration.
				if ((isset ($_SESSION['error_class_item'])) AND ($_SESSION['error_class_item'] -> ShowCategory ('budget|payment|checknum') != '')) {
					$HTML .= HOAM_formCheckbox ('budget|payment|checknum|ignore', $_HOAM_language ['errors']['generic']['ignore'], 1, 0, 0);
				}
				$HTML .= HOAM_formFieldHidden ('budget|payment|credit', $_HOAM_language ['field_names']['budget']['payment']['credit'], $_SESSION ['invoice_information']['trusted_budget|payment|credit'], '', $budget_class -> returnColumnSize ('checknum'), 1);
				$HTML .= HOAM_formDate ('budget|invoice|datepaid', $_HOAM_language ['field_names']['budget']['invoice']['date']['paid'], $_SESSION ['invoice_information']['trusted_budget|invoice|datepaid'], HOAM_date ($_HOAM_country ['date']['format_reports'])); 
				// Real hacky way to do this, should really be done through a
				// realtime Javascript popup, or at least cleaner integration.
				if ((isset ($_SESSION['error_class_item'])) AND ($_SESSION['error_class_item'] -> ShowCategory ('budget|invoice|datepaid') != '')) {
					$HTML .= HOAM_formCheckbox ('budget|invoice|datepaid|ignore', $_HOAM_language ['errors']['generic']['ignore'], 1, 0, 0);
				}
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Advanced Options
				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('delete'):
			$_HOAM_log -> add ('Loading /admin/financial/vendor/invoice/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][5]) {
					$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
					$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
					$trusted_invoice_id = $budget_invoice_class -> superClean ($GLOBALS['global_url_array'][5], $budget_invoice_class -> returnColumnSize ('id'));
					if ($budget_invoice_class -> idExists ($trusted_invoice_id)) {				
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['invoice']['delete'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetPaymentJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['invoice']['information'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'attachments.php');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						
						// OK, we're checking to see that the invoice we're trying to edit is the one that we
						// have information on in the invoice_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if an invoice was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['payment_information']['trusted_id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['payment_information'])) {
								unset ($_SESSION['payment_information']);
							}
						}
						$HTML .= '<form id="delete_invoice" action="/hoam/scripts/budget/vendor_invoice_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="budget|invoice|id" id="budget|invoice|id" value="' . $trusted_invoice_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="delete_invoice" title="' . $_HOAM_language ['form_tabs']['budget']['invoice']['information'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formFieldDisabled ('budget|invoice|vendor|name', $_HOAM_language ['field_names']['budget']['vendor']['name'], $budget_vendor_class -> returnName ($budget_invoice_class -> returnVendorId ($trusted_invoice_id)), $budget_vendor_class -> returnColumnSize ('name'));
						$HTML .= HOAM_formFieldDisabled ('budget|invoice|vendor|number', $_HOAM_language ['field_names']['budget']['invoice']['number'], $budget_invoice_class -> returnNumber ($trusted_invoice_id), $budget_invoice_class -> returnColumnSize ('number'));
						$HTML .= '</div>';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<label for="budget|invoice|amount">' . $_HOAM_language ['field_names']['budget']['invoice']['amount'];
						$HTML .= HOAM_formDisplayError ('budget|payment|method');
						$HTML .= '</label>';
						$HTML .= number_format ($budget_invoice_class -> returnTotalAmountDue ($trusted_invoice_id), 2);
						$HTML .= HOAM_formFieldDisabled ('budget|payment|amount', $_HOAM_language ['field_names']['budget']['payment']['amount'], $budget_invoice_class -> returnTotalAmountDue ($trusted_invoice_id), 10);
						$HTML .= HOAM_formSelectDisabled ('budget|payment|method', $_HOAM_language ['field_names']['budget']['payment']['method']);
						switch ($_SESSION['payment_information']['budget|payment|method']) {
							case 'autodraft':
								$HTML .= '<option selected="selected" value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
							case 'check':
								$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option selected="selected" value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
							case 'credit':
								$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option selected="selected" value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
							default: 
								$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option selected="selected" value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formFieldDisabled ('budget|payment|checknum', $_HOAM_language ['field_names']['budget']['payment']['checknum'], '', $budget_class -> returnColumnSize ('checknum'));
						$HTML .= HOAM_formDateDisabled ('budget|invoice|datepaid', $_HOAM_language ['field_names']['budget']['invoice']['date']['paid'], HOAM_date ($_HOAM_country ['date']['format_reports']));
						$HTML .= '<label for="budget|attachments">' . $_HOAM_language ['field_names']['budget']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('budget|attachments');
						$HTML .= HOAM_formDisplayError ('budget|attachments');
						$HTML .= '</label>';
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_invoice_id, $_HOAM_setting ['attachment']['flags']['budget']);
						if (is_array ($attachment_list)) {
							$attachment_html = '';
							foreach ($attachment_list as $attachment) {
								$attachment_html .= HOAM_attachmentReturnHTML ($attachment);
							}
						}
						if ($attachment_html) {
							$HTML .= $attachment_html;
							$HTML .= '<br class="clear" />';
							unset ($attachment_html);
						}
						$HTML .= '<div>';
						$HTML .= '<input disabled type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formTextDisabled ('budget|payment|memo', $_HOAM_language ['field_names']['budget']['payment']['memo'], '');
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Payment Add

						$HTML .= '<div class="tabbertab" id="advanced" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formTextareaDisabled ('budget|payment|description', $_HOAM_language ['field_names']['budget']['payment']['description'], $_SESSION['payment_information']['trusted_budget|payment|description'], '', $_SESSION['current_user']['edit']['row'], $budget_class -> returnColumnSize ('description'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Advanced Options

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					}
				} else {
					header ('Location: /admin/financial/vendor/list_all/');
					exit ();
				}						
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('edit'):
			$_HOAM_log -> add ('Loading /admin/financial/vendor/invoice/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][5]) {
					$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
					$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
					$trusted_invoice_id = $budget_invoice_class -> superClean ($GLOBALS['global_url_array'][5], $budget_invoice_class -> returnColumnSize ('id'));
					if ($budget_invoice_class -> idExists ($trusted_invoice_id)) {				
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['invoice']['edit'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetPaymentJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['invoice']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'attachments.php');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						
						// OK, we're checking to see that the invoice we're trying to edit is the one that we
						// have information on in the invoice_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if an invoice was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['payment_information']['trusted_id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['payment_information'])) {
								unset ($_SESSION['payment_information']);
							}
						}
						$HTML .= '<form id="new_payment" action="/hoam/scripts/budget/vendor_invoice_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="budget|invoice|id" id="budget|invoice|id" value="' . $trusted_invoice_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['common']['modify'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="add_payment" title="' . $_HOAM_language ['form_tabs']['budget']['payment']['add'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formSelect ('budget|invoice|vendor', $_HOAM_language ['field_names']['budget']['invoice']['vendor']);
						if (isset ($_SESSION ['invoice_information']['trusted_budget|invoice|vendor'])) {
							$default_vendor = $_SESSION ['invoice_information']['trusted_budget|invoice|vendor'];
						} else {
							$default_vendor = $budget_invoice_class -> returnVendorId ($trusted_invoice_id);
						}
						$vendor_list = $budget_vendor_class -> listAllId ('name', 'ASC');
						foreach ($vendor_list as $vendor) {
							$HTML .= '<option ';
							if ($vendor == $default_vendor) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $vendor . '">' . stripslashes ($budget_vendor_class -> returnName ($vendor)) . '</option>';
						}
						$HTML .= '</select>';
						
						$HTML .= HOAM_formField ('budget|invoice|number', $_HOAM_language ['field_names']['budget']['invoice']['number'], $_SESSION['invoice_information']['trusted_budget|invoice|number'], $budget_invoice_class -> returnNumber ($trusted_invoice_id), $budget_invoice_class -> returnColumnSize ('number'));
						$HTML .= HOAM_formField ('budget|invoice|amount', $_HOAM_language ['field_names']['budget']['invoice']['amount'], $_SESSION['invoice_information']['trusted_budget|invoice|amount'], $budget_invoice_class -> returnTotalAmountDue ($trusted_invoice_id), 10, 1);
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formSelect ('budget|payment|method', $_HOAM_language ['field_names']['budget']['payment']['method']);
						switch ($_SESSION['invoice_information']['trusted_budget|payment|method']) {
							case 'autodraft':
								$HTML .= '<option selected="selected" value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
							case 'check':
								$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option selected="selected" value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
							case 'credit':
								$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option selected="selected" value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
							default: 
								$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option selected="selected" value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formField ('budget|payment|checknum', $_HOAM_language ['field_names']['budget']['payment']['checknum'], $_SESSION['invoice_information']['trusted_budget|payment|checknum'], '', $budget_class -> returnColumnSize ('checknum'), 1);
						$HTML .= HOAM_formDate ('budget|invoice|datepaid', $_HOAM_language ['field_names']['budget']['invoice']['date']['paid'], $_SESSION['invoice_information']['trusted_budget|invoice|datepaid'], HOAM_date ($_HOAM_country ['date']['format_reports']));
						$HTML .= '<label for="budget|attachments">' . $_HOAM_language ['field_names']['budget']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('budget|attachments');
						$HTML .= HOAM_formDisplayError ('budget|attachments');
						$HTML .= '</label>';
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_invoice_id, $_HOAM_setting ['attachment']['flags']['budget']);
						if (is_array ($attachment_list)) {
							$attachment_html = '';
							foreach ($attachment_list as $attachment) {
								$attachment_html .= HOAM_attachmentReturnHTML ($attachment);
							}
						}
						if ($attachment_html) {
							$HTML .= $attachment_html;
							$HTML .= '<br class="clear" />';
							unset ($attachment_html);
						}
						$HTML .= '<div>';
						$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formText ('budget|invoice|memo', $_HOAM_language ['field_names']['budget']['payment']['memo'], $_SESSION['invoice_information']['trusted_budget|payment|memo'], '', $budget_class -> returnColumnSize ('memo'), 1);
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Payment Add

						$HTML .= '<div class="tabbertab" id="advanced" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formTextarea ('budget|invoice|description', $_HOAM_language ['field_names']['budget']['payment']['description'], $_SESSION['invoice_information']['trusted_budget|payment|description'], '', $_SESSION['current_user']['edit']['row'], $budget_class -> returnColumnSize ('description'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Advanced Options

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					}
				} else {
					header ('Location: /admin/financial/vendor/list_all/');
					exit ();
				}						
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			$_HOAM_log -> add ('Loading /admin/financial/vendor/invoice/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['vendor']['list_all_invoices'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminVendorInvoiceListJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['vendor']['list_all_invoices'] . '</h1>';

				$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
				$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
				$_HOAM_term_list = &$_HOAM_language ['budget']['term'];
						
				$colgroup  = '<colgroup>';
				$colgroup .= '<col span="2" width="20%" />';
				$colgroup .= '<col span="3" width="10%" />';
				$colgroup .= '<col class="donotprint" width="10%" />';
				$colgroup .= '<col width="10%" />';
				$colgroup .= '</colgroup>';
				$thead  = '<thead>';
				$thead .= '<tr>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['invoice_due_date'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['name'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['invoice']['number'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['invoice']['amount'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['budget']['delinquent_amount'] . '</th>';
				$thead .= '<th class="donotprint">' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['budget']['vendor']['last_payment'] . '</th>';
				$thead .= '</tr>';
				$thead .= '</thead>';
				$invoice_list = $budget_invoice_class -> listInvoicesForCustomer ($_HOAM_setting ['budget']['ids']['organization'], TRUE);
				if (is_array ($invoice_list)) {
					$invoice = $budget_invoice_class -> listAll ($invoice_list[0]);
					$year = date ('Y', strtotime ($invoice[0]['dateinvoice']));
					$HTML .= '<span style="font-weight: bold" field="year">&#x0229F; ' . $year . '</span>';
					$HTML .= '<div>';
					$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead);
					$HTML .= '<tbody>';
					foreach ($invoice_list as $invoice) {
						$invoice = $budget_invoice_class -> listAll ($invoice);
						$invoice = $invoice[0];
//						print_r ($invoice);
						if (mb_substr ($invoice['dateinvoice'], 0, 4) == $year) {
						} else {
							$year = mb_substr ($invoice['dateinvoice'], 0, 4);
							$HTML .= '</tbody>';
							$HTML .= '</table>';
							$HTML .= '</div>';
							$HTML .= '<span style="display: block" field="year">&#x0229E; ' . $year . '</span>';
							$HTML .= '<div style="display: none">';
							$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead);
							$HTML .= '<tbody>';
						}					
						$HTML .= '<tr>';
						$amount_due = $budget_invoice_class -> returnTotalAmountDue ($invoice['id']);
						$date_due = $budget_invoice_class -> returnDateDue ($invoice['id']);
						$last_payment = $budget_class -> returnDatePosted ($budget_class -> returnLastPaymentInvoice ($invoice['id']));
						$today = date ($_HOAM_country ['date']['format_mysql']); 
						if (($today >= $date_due) AND ($amount_due > 0)) {
							if ($today == $date_due) {
								$HTML .= '<td class="caution">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($date_due)) . '</td>';
							} else {
								$HTML .= '<td class="warning">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($date_due)) . '</td>';
							}
						} else {
							$HTML .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($date_due)) . '</td>';
						}
						$vendor_name = html_encode (stripslashes ($budget_vendor_class -> returnName ($invoice ['vendor_id'])));
						if (!empty ($vendor_name)) {
							$HTML .= '<td>' . $vendor_name . '</td>';
						} else {
							$HTML .= '<td class="error">' . ucfirst ($_HOAM_language ['common']['unknown']) . '</td>';
						}
						$HTML .= '<td>' . $invoice['number'] . '</td>';
						$invoice_amount = $budget_invoice_class -> returnTotalAmount ($invoice['id']);
						$HTML .= '<td class="currency">' . HOAM_returnCurrency ($invoice_amount) . '</td>';
						$HTML .= '<td class="currency">' . HOAM_returnCurrency ($budget_invoice_class -> returnTotalAmountDue ($invoice ['id'])) . '</td>';
						$HTML .= '<td class="donotprint">';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$HTML .= '<small><a class="error" href="/admin/financial/vendor/invoice/delete/' . $invoice['id'] . '/" title="' . $_HOAM_language ['action']['generic']['delete'] . '">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
							$HTML .= ' &nbsp;';
						}
						if ($amount_due > 0) {
							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
								$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
								$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
								($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
								$HTML .= '<small><a href="/admin/financial/vendor/invoice/edit/' . $invoice['id'] . '/" title="' . $_HOAM_language ['action']['generic']['edit'] . '">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
								$HTML .= ' &nbsp;';
								$HTML .= '<small><a href="/admin/financial/vendor/invoice/payment/' . $invoice['id'] . '/">' . $_HOAM_language ['action']['budget']['pay'] . '</a></small>';
								$HTML .= ' &nbsp;';
							}
						}
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$HTML .= '<small><a href="/admin/financial/view/' . $invoice['id'] . '/" title="' . $_HOAM_language ['action']['generic']['view'] . '">' . $_HOAM_language ['action']['generic']['view'] . '</a></small>';
							$HTML .= ' &nbsp;';
						}
						$HTML .= '</td>';
						$HTML .= '<td>';
						if ($last_payment) {
							$HTML .= date ($_HOAM_country ['date']['format_reports'], strtotime ($last_payment));
						} else {
							$HTML .= $_HOAM_language ['common']['na'];
						}
						$HTML .= '</td>';
						$HTML .= '</tr>';
					}
				} else {
					$HTML .= '<tr><td colspan="7">' . $_HOAM_language ['errors']['budget']['invoice']['none'] . '</td></tr>';
				}
				$HTML .= '</tbody>';
				$HTML .= '</table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('payment') :
			$_HOAM_log -> add ('Loading /admin/financial/vendor/invoice/payment/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][5]) {
					$budget_invoice_class = new HOAM_budget_invoice ('budget_invoice');
					$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
					$trusted_invoice_id = $budget_invoice_class -> superClean ($GLOBALS['global_url_array'][5], $budget_invoice_class -> returnColumnSize ('id'));
					if ($budget_invoice_class -> idExists ($trusted_invoice_id)) {				
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['vendor']['payment'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminBudgetPaymentJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['vendor']['payment'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'attachments.php');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						
						// OK, we're checking to see that the invoice we're trying to edit is the one that we
						// have information on in the invoice_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if an invoice was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['payment_information']['trusted_id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['payment_information'])) {
								unset ($_SESSION['payment_information']);
							}
						}
						$HTML .= '<form id="new_payment" action="/hoam/scripts/budget/vendor_payment_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="budget|invoice|id" id="budget|invoice|id" value="' . $trusted_invoice_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['budget']['payment']['add'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="add_payment" title="' . $_HOAM_language ['form_tabs']['budget']['payment']['add'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formFieldReadonly ('budget|invoice|vendor|name', $_HOAM_language ['field_names']['budget']['vendor']['name'], $budget_vendor_class -> returnName ($budget_invoice_class -> returnVendorId ($trusted_invoice_id)), $budget_vendor_class -> returnColumnSize ('name'));
						$HTML .= HOAM_formFieldReadonly ('budget|invoice|vendor|number', $_HOAM_language ['field_names']['budget']['invoice']['number'], $budget_invoice_class -> returnNumber ($trusted_invoice_id), $budget_invoice_class -> returnColumnSize ('number'));
						$HTML .= '</div>';
						$HTML .= '<div class="mandatory">';
						$amount_due = $budget_invoice_class -> returnTotalAmountDue ($trusted_invoice_id);
						if ($amount_due == $budget_invoice_class -> returnTotalAmount ($trusted_invoice_id)) {
							$HTML .= '<label for="budget|invoice|amount">' . $_HOAM_language ['field_names']['budget']['invoice']['amount'];
						} else {
							$HTML .= '<label for="budget|invoice|amount">' . $_HOAM_language ['field_names']['budget']['invoice']['remaining'];
						}
						$HTML .= '</label>';
						$HTML .= HOAM_returnCurrency ($amount_due);
						$HTML .= HOAM_formField ('budget|payment|amount', $_HOAM_language ['field_names']['budget']['payment']['amount'], $_SESSION['payment_information']['trusted_budget|payment|amount'], $budget_invoice_class -> returnTotalAmountDue ($trusted_invoice_id), 10, 1);
						$HTML .= HOAM_formSelect ('budget|payment|method', $_HOAM_language ['field_names']['budget']['payment']['method']);
						switch ($_SESSION['payment_information']['trusted_budget|payment|method']) {
							case 'autodraft':
								$HTML .= '<option selected="selected" value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
							case 'check':
								$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option selected="selected" value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
							case 'credit':
								$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option selected="selected" value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
							default: 
								$HTML .= '<option value="autodraft">' . $_HOAM_language ['field_names']['budget']['payment']['autodraft'] . '</option>';
								$HTML .= '<option selected="selected" value="check">' . $_HOAM_language ['field_names']['budget']['payment']['check'] . '</option>';
								$HTML .= '<option value="credit">' . $_HOAM_language ['field_names']['budget']['payment']['credit'] . '</option>';
								break;
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formField ('budget|payment|checknum', $_HOAM_language ['field_names']['budget']['payment']['checknum'], $_SESSION['payment_information']['trusted_budget|payment|checknum'], '', $budget_class -> returnColumnSize ('checknum'), 1);
						$HTML .= HOAM_formDate ('budget|invoice|datepaid', $_HOAM_language ['field_names']['budget']['invoice']['date']['paid'], $_SESSION['payment_information']['trusted_budget|invoice|datepaid'], HOAM_date ($_HOAM_country ['date']['format_reports']));
						$HTML .= '<label for="budget|attachments">' . $_HOAM_language ['field_names']['budget']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('budget|attachments');
						$HTML .= HOAM_formDisplayError ('budget|attachments');
						$HTML .= '</label>';
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_invoice_id, $_HOAM_setting ['attachment']['flags']['budget']);
						if (is_array ($attachment_list)) {
							$attachment_list = '';
							foreach ($attachment_list as $attachment) {
								$attachment_html .= HOAM_attachmentReturnHTML ($attachment);
							}
						}
						if ($attachment_html) {
							$HTML .= $attachment_html;
							$HTML .= '<br class="clear" />';
							unset ($attachment_html);
						}
						$HTML .= '<div>';
						$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formText ('budget|payment|memo', $_HOAM_language ['field_names']['budget']['payment']['memo'], $_SESSION['payment_information']['trusted_budget|payment|memo'], '', $budget_class -> returnColumnSize ('memo'), 1);
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Payment Add

						$HTML .= '<div class="tabbertab" id="advanced" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formTextarea ('budget|payment|description', $_HOAM_language ['field_names']['budget']['payment']['description'], $_SESSION['payment_information']['trusted_budget|payment|description'], '', $_SESSION['current_user']['edit']['row'], $budget_class -> returnColumnSize ('description'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Advanced Options

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					}
				} else {
					header ('Location: /admin/financial/vendor/list_all/');
					exit ();
				}						
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][4])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['financial'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['budget_vendor_invoice'] . '</h1>' : $HTML = '<li style="list-style-image: url(\'/hoam/images/icons/lorry_add.png\')"><a href="/admin/financial/vendor/invoice/">' . $_HOAM_language ['field_names']['admin']['budget_vendor_invoice'] . '</a></li>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/lorry_add.png\')"><a href="/admin/financial/vendor/invoice/add/">' . $_HOAM_language ['field_names']['budget']['vendor']['invoice'] . '</a></li>';
					}
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/vendor/invoice/list/">' . $_HOAM_language ['field_names']['budget']['vendor']['list_all_invoices'] . '</a></li>';
					}
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminFormLetter ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$group_members_class = new HOAM_group_members ('group_members');
	$lot_class = new HOAM_lot ('lots');

	if (isset ($GLOBALS['global_url_array'][2])) {
		switch ($GLOBALS['global_url_array'][2]) {
			case ('add') :
				$_HOAM_log -> add ('Loading /admin/letter/add/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['letter']['add'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminLetterJS');
					$HTML = '<h1>' . $_HOAM_language ['field_names']['letter']['add'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'attachments.php');
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
					$letters_directory = HOAM_PATH . 'hoam/letters/';
					$HTML .= '<form id="letter" action="/hoam/scripts/letter/edit_script.php" enctype="multipart/form-data" method="post">';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" id="letter|submit" name="add">' . $_HOAM_language ['form_buttons']['letter']['add'] . '</button>';
					$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="letter|add" title="' . $_HOAM_language ['form_tabs']['letter']['add'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formSelect ('letter|name', $_HOAM_language ['field_names']['letter']['name']);
					$HTML .= '<option value="0">' . $_HOAM_language ['common']['please-select'] . '</option>';
					$letter_list = HOAM_returnFiles ($letters_directory, 'xml');
					if (isset ($_SESSION['letter_information']['trusted_letter|name'])) {
						$default_letter = $_SESSION['letter_information']['trusted_letter|name'];
					} else {
						$default_letter = NULL;
					}
				
					foreach ($letter_list as $letter) {
						$contents = file_get_contents ($letters_directory . $letter);
						$index = array ();
						$values = array ();

						$parser = xml_parser_create("");
						xml_parse_into_struct ($parser, $contents, $values, $index);
						$HTML .= '<option ';
						if ($default_letter == $letter) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $letter . '">' . $values [$index['NAME'][0]]['value'] . '</option>';
					}
					$HTML .= '</select>';

					$HTML .= HOAM_formSelect ('letter|address', $_HOAM_language ['field_names']['letter']['address']);
					if (isset ($_SESSION['letter_information']['trusted_letter|address'])) {
						$default_lot = $_SESSION['letter_information']['trusted_letter|address'];
					} else {
						$default_lot = NULL;
					}
					$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
					foreach ($lot_list as $lot) {
						$HTML .= '<option ';
						if ($lot['id'] == $default_lot) {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
						$HTML .= '</option>';
					}
					$HTML .= '</select>';
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= '<fieldset>';
					$HTML .= HOAM_formSelect ('letter|person', $_HOAM_language ['field_names']['organization']['authorized']);
					$HTML .= '<option value="' . $_HOAM_language ['field_names']['organization']['agent'] . '">' . $_HOAM_language ['field_names']['organization']['agent'] . '</option>';
					$HTML .= '<option value="' . $_HOAM_language ['field_names']['organization']['president'] . '">' . $_HOAM_language ['field_names']['organization']['president'] . '</option>';
					$HTML .= '</select>';
					$HTML .= '<label for="letter|approver">' . $_HOAM_language ['field_names']['organization']['approver'] . '</label>';
					$HTML .= '<input type="text" name="letter|approver" id="letter|approver" maxlegth="64"></input>';
					$HTML .= '</fieldset>';
					$HTML .= '</div>';
					$HTML .= '<div id="letter|delivery|options" class="optional">';
					$HTML .= '<fieldset>';
					$HTML .= HOAM_formSelect ('letter|delivery', $_HOAM_language ['field_names']['letter']['delivery']);
					switch ($_SESSION['letter_information']['trusted_letter|delivery']) {
						case 0:
							$HTML .= '<option selected="selected" value="' . 0 . '">' . ucfirst ($_HOAM_language ['common']['none']) . '</option>';
							$HTML .= '<option value="' . 1 . '">' . $_HOAM_language ['field_names']['letter']['delivery_confirmation'] . '</option>';
							$HTML .= '<option value="' . 2 . '">' . $_HOAM_language ['field_names']['letter']['delivery_signature'] . '</option>';
							$HTML .= '<option value="' . 3 . '">' . $_HOAM_language ['field_names']['letter']['delivery_certified'] . '</option>';
							break;
						case 1:
							$HTML .= '<option value="' . 0 . '">' . ucfirst ($_HOAM_language ['common']['none']) . '</option>';
							$HTML .= '<option selected="selected" value="' . 1 . '">' . $_HOAM_language ['field_names']['letter']['delivery_confirmation'] . '</option>';
							$HTML .= '<option value="' . 2 . '">' . $_HOAM_language ['field_names']['letter']['delivery_signature'] . '</option>';
							$HTML .= '<option value="' . 3 . '">' . $_HOAM_language ['field_names']['letter']['delivery_certified'] . '</option>';
							break;
						case 2:
							$HTML .= '<option value="' . 0 . '">' . ucfirst ($_HOAM_language ['common']['none']) . '</option>';
							$HTML .= '<option value="' . 1 . '">' . $_HOAM_language ['field_names']['letter']['delivery_confirmation'] . '</option>';
							$HTML .= '<option selected="selected" value="' . 2 . '">' . $_HOAM_language ['field_names']['letter']['delivery_signature'] . '</option>';
							$HTML .= '<option value="' . 3 . '">' . $_HOAM_language ['field_names']['letter']['delivery_certified'] . '</option>';
							break;
						case 3:
							$HTML .= '<option value="' . 0 . '">' . ucfirst ($_HOAM_language ['common']['none']) . '</option>';
							$HTML .= '<option value="' . 1 . '">' . $_HOAM_language ['field_names']['letter']['delivery_confirmation'] . '</option>';
							$HTML .= '<option value="' . 2 . '">' . $_HOAM_language ['field_names']['letter']['delivery_signature'] . '</option>';
							$HTML .= '<option selected="selected" value="' . 3 . '">' . $_HOAM_language ['field_names']['letter']['delivery_certified'] . '</option>';
							break;
						default:
							$HTML .= '<option value="' . 0 . '">' . ucfirst ($_HOAM_language ['common']['none']) . '</option>';
							$HTML .= '<option value="' . 1 . '">' . $_HOAM_language ['field_names']['letter']['delivery_confirmation'] . '</option>';
							$HTML .= '<option value="' . 2 . '">' . $_HOAM_language ['field_names']['letter']['delivery_signature'] . '</option>';
							$HTML .= '<option value="' . 3 . '">' . $_HOAM_language ['field_names']['letter']['delivery_certified'] . '</option>';
							break;
					}
					$HTML .= '</select>';
					$HTML .= HOAM_formFieldHidden ('letter|delivery_tracking', $_HOAM_language ['field_names']['letter']['delivery_tracking'], $_SESSION['letter_information']['trusted_letter|delivery_tracking'], '', 27, 10);
					$HTML .= '</fieldset>';
				
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_formText ('letter|specific', $_HOAM_language ['field_names']['letter']['specific'], $_SESSION['letter_information']['trusted_letter|specific'], '', 1024);
					$HTML .= '</div>';
					$HTML .= '</div>'; // End Add Letter

					$HTML .= '<div class="tabbertab" id="letter|view" title="' . $_HOAM_language ['form_tabs']['letter']['view'] . '">';
					$HTML .= '<div class="optional">';
					$HTML .= '<div id="letter|content" class="violation_letter">';
					$HTML .= '</div>';
					$HTML .= '</div>';
					$HTML .= '</div>'; // End View Letter

					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			default:
				header ('Location: /error/404/');
				exit ();
				break;
		}	
	} else {
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['letter'])));
			$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['letter'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/page.png" alt="' . $_HOAM_language ['field_names']['admin']['letter'] . '" class="LHS" /><a href="/admin/letter/">' . $_HOAM_language ['field_names']['admin']['letter'] . '</a>';
			$HTML .= '<ul>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/page.png\')"><a href="/admin/letter/add/">' . $_HOAM_language ['field_names']['letter']['add'] . '</a></li>';
			$HTML .= '</ul>';
		}
	}
	return ($HTML);
}

function HOAM_adminHomeowner ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	require_once (HOAM_PATH_FUNCTION . 'violations.php');

	$budget_class = new HOAM_budget ('budget');
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$user_class = new HOAM_user ('users');

	switch ($GLOBALS['global_url_array'][2]) {
		case ('add') :
			$_HOAM_log -> add ('Loading /admin/homeowner/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['homeowner']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminHomeownerJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['homeowner']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				// OK, we're checking to see that the homeowner we're trying to edit is the one that we
				// have information on in the homeowner_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				// Also, trusted_id will /only/ be set if a homeowner was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['homeowner_information'])) {
						unset ($_SESSION['homeowner_information']);
					}
				}

				$HTML .= '<form id="homeowner_add" action="/hoam/scripts/homeowner/edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" id="homeowner|add" name="add">' . $_HOAM_language ['form_buttons']['homeowner']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="homeowner_information" title="' . $_HOAM_language ['form_tabs']['homeowner']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelect ('homeowner|address', $_HOAM_language ['field_names']['homeowner']['address']);
				if (isset ($_SESSION['homeowner_information']['trusted_homeowner|address'])) {
					$default_lot = $_SESSION['homeowner_information']['trusted_homeowner|address'];
				} elseif (isset ($GLOBALS['global_url_array'][3])) {
					$session_lot = (string) $lot_class -> superClean ($GLOBALS['global_url_array'][3], $lot_class -> returnColumnSize ('id'));
					if ($lot_class -> idExists ($session_lot)) {
						$default_lot = $session_lot;
						unset ($session_lot);
					} else {
						$default_lot = NULL;
					}
				} else {
					$default_lot = NULL;
				}
				$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
				$HTML .= '<option value="0">' . $_HOAM_language ['common']['please-select'] . '</option>';
				foreach ($lot_list as $lot) {
					$HTML .= '<option ';
					if ($lot['id'] == $default_lot) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
					$HTML .= '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formText ('homeowner|name', $_HOAM_language ['field_names']['homeowner']['name'], $_SESSION['homeowner_information']['trusted_homeowner|name'], '');
				$HTML .= '<label for="homeowner|residence_mailing">' . $_HOAM_language ['field_names']['homeowner']['mailing_address'];
				$HTML .= HOAM_formDisplayHelp ('homeowner|residence_mailing');
				$HTML .= HOAM_formDisplayError ('homeowner|residence_mailing');
				$HTML .= '</label>';
				if (!isset ($_SESSION['homeowner_information']) OR (isset ($_SESSION['homeowner_information']['trusted_homeowner|residence_mailing']) AND is_true ($_SESSION['homeowner_information']['trusted_homeowner|residence_mailing']))) {
					$HTML .= '<input checked="checked" type="checkbox" name="homeowner|residence_mailing" id="homeowner|residence_mailing" value="1" />';
					$HTML .= '</div>';
					$HTML .= '<fieldset id="homeowner|mailing_address" style="display: none">';
				} else {
					$HTML .= '<input type="checkbox" name="homeowner|residence_mailing" id="homeowner|residence_mailing" value="1" />';
					$HTML .= '</div>';
					$HTML .= '<fieldset id="homeowner|mailing_address">';
				}
				$HTML .= '<div class="mandatory">';
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formCheckbox ('homeowner|resident', $_HOAM_language ['field_names']['homeowner']['resident'], 1, (isset ($_SESSION['homeowner_information']['trusted_homeowner|resident']) AND is_false ($_SESSION['homeowner_information']['trusted_homeowner|resident'])), '');
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formText ('homeowner|address1', $_HOAM_language ['field_names']['homeowner']['address1'], $_SESSION['homeowner_information']['trusted_homeowner|address1'], '');
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formText ('homeowner|address2', $_HOAM_language ['field_names']['homeowner']['address2'], $_SESSION['homeowner_information']['trusted_homeowner|address2'], '');
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formText ('homeowner|address3', $_HOAM_language ['field_names']['homeowner']['address3'], $_SESSION['homeowner_information']['trusted_homeowner|address3'], '');
				$HTML .= HOAM_formText ('homeowner|city', $_HOAM_language ['field_names']['homeowner']['city'], $_SESSION['homeowner_information']['trusted_homeowner|city'], $_HOAM_setting ['homeowner']['default']['city'], $homeowner_class -> returnColumnSize ('city'), 1);
				$HTML .= HOAM_formSelect ('homeowner|state', $_HOAM_language ['field_names']['homeowner']['state']);
				if (isset ($_SESSION['homeowner_information']['trusted_homeowner|state'])) {
					$default_state = $_SESSION['homeowner_information']['trusted_homeowner|state'];
				} else {
					$default_state = $_HOAM_setting ['homeowner']['default']['state'];
				}
				foreach ($_HOAM_country ['state'] as $state) {
					$HTML .= '<option ';
					if ($state['value'] == $default_state) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $state['value'] . '">' . $state['description'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formField ('homeowner|postalcode', $_HOAM_language ['field_names']['homeowner']['postalcode'], $_SESSION['homeowner_information']['trusted_homeowner|postalcode'], $_HOAM_setting ['homeowner']['default']['postalcode'], $homeowner_class -> returnColumnSize ('zipcode'), $_HOAM_country ['postalcode']['length']);
				$HTML .= '</div>';
				$HTML .= '</fieldset>';
				$HTML .= '<fieldset>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formField ('homeowner|telephone|home', $_HOAM_language ['field_names']['homeowner']['telephone']['home'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|home'], '', $homeowner_class -> returnColumnSize ('telephone_home'), $_HOAM_country ['telephone']['length']);
				$HTML .= HOAM_formField ('homeowner|telephone|work', $_HOAM_language ['field_names']['homeowner']['telephone']['work'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|work'], '', $homeowner_class -> returnColumnSize ('telephone_work'), $_HOAM_country ['telephone']['length']);
				$HTML .= HOAM_formField ('homeowner|telephone|mobile', $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|mobile'], '', $homeowner_class -> returnColumnSize ('telephone_mobile'), $_HOAM_country ['telephone']['length']);
				$HTML .= HOAM_formField ('homeowner|telephone|fax', $_HOAM_language ['field_names']['homeowner']['telephone']['fax'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|fax'], '', $homeowner_class -> returnColumnSize ('telephone_fax'), $_HOAM_country ['telephone']['length']);
				$HTML .= HOAM_formText ('homeowner|email', $_HOAM_language ['field_names']['homeowner']['email'], $_SESSION['homeowner_information']['trusted_homeowner|email'], '', $homeowner_class -> returnColumnSize ('email'));
				$HTML .= HOAM_formField ('homeowner|access_code', $_HOAM_language ['field_names']['homeowner']['access_code'], $_SESSION['homeowner_information']['trusted_homeowner|access_code'], '', $homeowner_class -> returnColumnSize ('access_code'), 0);
				$HTML .= '</div>';
				$HTML .= '</fieldset>';

				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formDate ('homeowner|purchasedate', $_HOAM_language ['field_names']['homeowner']['purchasedate'], $_SESSION['homeowner_information']['trusted_homeowner|purchasedate'], HOAM_date ($_HOAM_country ['date']['format_reports']));
				$HTML .= '</div>';

				$HTML .= '<div class="optional">';
				$HTML .= '<label for="homeowner|attachments">' . $_HOAM_language ['field_names']['homeowner']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('homeowner|attachments');
				$HTML .= HOAM_formDisplayError ('homeowner|attachments');
				$HTML .= '</label>';
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= HOAM_formTextarea ('homeowner|comments', $_HOAM_language ['field_names']['homeowner']['comments'], $_SESSION['homeowner_information']['trusted_homeowner|comments'], '', $_SESSION['current_user']['edit']['row'], $homeowner_class -> returnColumnSize ('comments'));
				$HTML .= '</div>';
				$HTML .= '</div>'; // End new homeowner tab

				$HTML .= '<div class="tabbertab" id="homeowner_budget" title="' . $_HOAM_language ['form_tabs']['homeowner']['assessment'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '<fieldset id="homeowner|assessment|flags" class="advanced_options">';
				$HTML .= HOAM_formCheckbox ('homeowner|assessment|exempt', $_HOAM_language ['field_names']['homeowner']['assessment']['exempt'], 1, $_SESSION['homeowner_information']['trusted_homeowner|assessment|flags'] & $_HOAM_setting ['homeowner']['flags']['ignore_budget'], '');
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End budget tab

				$HTML .= '<div class="tabbertab" id="homeowner_violation" title="' . $_HOAM_language ['form_tabs']['homeowner']['violation'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '<fieldset id="homeowner|violaton|flags" class="advanced_options">';
				$HTML .= HOAM_formCheckbox ('homeowner|ignore_violations', $_HOAM_language ['field_names']['homeowner']['ignore_violations'], 1, (isset ($_SESSION['homeowner_information']['trusted_homeowner|ignore_violations']) AND is_true ($_SESSION['homeowner_information']['trusted_homeowner|ignore_violations'])), '');
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Violation tab

				$HTML .= '<div class="tabbertab" id="advanced" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formDate ('homeowner|saledate', $_HOAM_language ['field_names']['homeowner']['saledate'], $_SESSION['homeowner_information']['trusted_homeowner|saledate'], '');
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Violation tab

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('delete') :
			$_HOAM_log -> add ('Loading /admin/homeowner/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_homeowner_id = $homeowner_class -> superClean ($GLOBALS['global_url_array'][3], $homeowner_class -> returnColumnSize ('id'));
					if ($homeowner_class -> idExists ($trusted_homeowner_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['homeowner']['delete'] . ' (' . HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_homeowner_id)) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminHomeownerJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['homeowner']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'attachments.php');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the homeowner we're trying to edit is the one that we
						// have information on in the homeowner_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if a homeowner was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['homeowner_information']['trusted_homeowner|id']) AND ($trusted_homeowner_id != $_SESSION['homeowner_information']['trusted_homeowner|id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['homeowner_information'])) {
								unset ($_SESSION['homeowner_information']);
							}
						}

						$HTML .= '<form id="homeowner_delete" action="/hoam/scripts/homeowner/edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" id="homeowner|delete" name="delete">' . $_HOAM_language ['form_buttons']['homeowner']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="homeowner_information" title="' . $_HOAM_language ['form_tabs']['homeowner']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<input type="hidden" id="homeowner|id" name="homeowner|id" value="' . $trusted_homeowner_id . '" />';
						$HTML .= HOAM_formSelectDisabled ('homeowner|address', $_HOAM_language ['field_names']['homeowner']['address']);
						if (isset ($_SESSION['homeowner_information']['trusted_address'])) {
							$default_lot = $_SESSION['homeowner_information']['trusted_address'];
						} else {
							$default_lot = $homeowner_class -> returnLotId ($trusted_homeowner_id);
						}
						$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
						foreach ($lot_list as $lot) {
							$HTML .= '<option ';
							if ($lot['id'] == $default_lot) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
							$HTML .= '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formTextDisabled ('homeowner|name', $_HOAM_language ['field_names']['homeowner']['name'], $homeowner_class -> returnName ($trusted_homeowner_id));
						$HTML .= '<label for="homeowner|residence_mailing">' . $_HOAM_language ['field_names']['homeowner']['mailing_address'];
						$HTML .= HOAM_formDisplayHelp ('homeowner|residence_mailing');
						$HTML .= HOAM_formDisplayError ('homeowner|residence_mailing');
						$HTML .= '</label>';
						if ((isset ($_SESSION['homeowner_information']['trusted_homeowner|residence_mailing']) AND is_true ($_SESSION['homeowner_information']['trusted_homeowner|residence_mailing'])) OR
						$homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['homeowner']['flags']['residence_mailing'])) {
							$HTML .= '<input disabled="disabled" checked="checked" type="checkbox" name="homeowner|residence_mailing" id="homeowner|residence_mailing" value="1" />';
							$HTML .= '</div>';
							$HTML .= '<fieldset id="homeowner|mailing_address" style="display: none">';
						} else {
							$HTML .= '<input disabled="disabled" type="checkbox" name="homeowner|residence_mailing" id="homeowner|residence_mailing" value="1" />';
							$HTML .= '</div>';
							$HTML .= '<fieldset id="homeowner|mailing_address">';
						}
						$HTML .= '<div class="mandatory">';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formCheckboxDisabled ('homeowner|resident', $_HOAM_language ['field_names']['homeowner']['resident'], 1, $homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['homeowner']['flags']['resident']));
						$HTML .= '</fieldset>';
						$HTML .= HOAM_formTextDisabled ('homeowner|address1', $_HOAM_language ['field_names']['homeowner']['address1'], $homeowner_class -> returnAddress1 ($trusted_homeowner_id));
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formTextDisabled ('homeowner|address2', $_HOAM_language ['field_names']['homeowner']['address2'], $homeowner_class -> returnAddress2 ($trusted_homeowner_id));
						$HTML .= '</div>';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formTextDisabled ('homeowner|address3', $_HOAM_language ['field_names']['homeowner']['address3'], $homeowner_class -> returnAddress3 ($trusted_homeowner_id));
						$HTML .= HOAM_formTextDisabled ('homeowner|city', $_HOAM_language ['field_names']['homeowner']['city'], $homeowner_class -> returnCity ($trusted_homeowner_id), $homeowner_class -> returnColumnSize ('city'), 1);
						$HTML .= HOAM_formSelectDisabled ('homeowner|state', $_HOAM_language ['field_names']['homeowner']['state']);
						if (isset ($_SESSION['homeowner_information']['trusted_homeowner|state'])) {
							$default_state = $_SESSION['homeowner_information']['trusted_homeowner|state'];
						} else {
							$default_state = $homeowner_class -> returnState ($trusted_homeowner_id);
						}
						foreach ($_HOAM_country ['state'] as $state) {
							$HTML .= '<option ';
							if ($state['value'] == $default_state) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $state['value'] . '">' . $state['description'] . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</div>';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formFieldDisabled ('homeowner|postalcode', $_HOAM_language ['field_names']['homeowner']['postalcode'], preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_class -> returnzipcode ($trusted_homeowner_id), 1), $homeowner_class -> returnColumnSize ('zipcode'));
						$HTML .= '</div>';
						$HTML .= '</fieldset>';
						$HTML .= '<fieldset>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formFieldDisabled ('homeowner|telephone|home', $_HOAM_language ['field_names']['homeowner']['telephone']['home'], preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_class -> returnTelephoneHome ($trusted_homeowner_id), 1), $homeowner_class -> returnColumnSize ('telephone_home'));
						$HTML .= HOAM_formFieldDisabled ('homeowner|telephone|work', $_HOAM_language ['field_names']['homeowner']['telephone']['work'], preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_class -> returnTelephoneWork ($trusted_homeowner_id), 1), $homeowner_class -> returnColumnSize ('telephone_work'));
						$HTML .= HOAM_formFieldDisabled ('homeowner|telephone|mobile', $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'], preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_class -> returnTelephoneMobile ($trusted_homeowner_id), 1), $homeowner_class -> returnColumnSize ('telephone_mobile'));
						$HTML .= HOAM_formFieldDisabled ('homeowner|telephone|fax', $_HOAM_language ['field_names']['homeowner']['telephone']['fax'], preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_class -> returnTelephoneFax ($trusted_homeowner_id), 1), $homeowner_class -> returnColumnSize ('telephone_fax'));
						$HTML .= HOAM_formTextDisabled ('homeowner|email', $_HOAM_language ['field_names']['homeowner']['email'], $homeowner_class -> returnEmail ($trusted_homeowner_id));
						$HTML .= HOAM_formFieldDisabled ('homeowner|access_code', $_HOAM_language ['field_names']['homeowner']['access_code'], $homeowner_class -> returnAccessCode ($trusted_homeowner_id), $homeowner_class -> returnColumnSize ('access_code'));
						$HTML .= '</div>';
						$HTML .= '</fieldset>';

						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formDateDisabled ('homeowner|purchasedate', $_HOAM_language ['field_names']['homeowner']['purchasedate'], HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_class -> returnPurchasedate ($trusted_homeowner_id))));
						// We need to accomodate saledates that are null or all
						// zeroes (eg, the residence has not been sold).
						$trusted_saledate = $homeowner_class -> returnSaledate ($trusted_homeowner_id);
						if (!empty ($trusted_saledate) AND ($trusted_saledate != '1970-01-01')) {
							$trusted_saledate = HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($trusted_saledate));
						} else {
							$trusted_saledate = '';
						}
						$HTML .= HOAM_formDateDisabled ('homeowner|saledate', $_HOAM_language ['field_names']['homeowner']['saledate'], $trusted_saledate);
						$HTML .= '</div>';

						$HTML .= '<div class="optional">';
						$HTML .= '<label for="homeowner|attachments">' . $_HOAM_language ['field_names']['homeowner']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('homeowner|attachments');
						$HTML .= HOAM_formDisplayError ('homeowner|attachments');
						$HTML .= '</label>';
						$attachment_class = new HOAM_attachments ('attachments');
						$attachment_list = $attachment_class -> listWithOwner ($trusted_homeowner_id, $_HOAM_setting ['attachment']['flags']['homeowner']);
						if (!empty ($attachment_list)) {
							$year = date ('Y', strtotime ($attachment_class -> returnDateCreated ($attachment_list[0])));
							$HTML .= '<span style="font-weight: bold" field="year">&#x0229F; ' . $year . '</span>';
							$HTML .= '<div>';
							foreach ($attachment_list as $attachment) {
								if (mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4) == $year) {
								} else {
									$year = mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4);
									$HTML .= '</div>';
									$HTML .= '<span style="clear: left; display: block" field="year">&#x0229E; ' . $year . '</span>';
									$HTML .= '<div style="display: none">';
								}
								$HTML .= HOAM_attachmentReturnHTML ($attachment);
							}
							$HTML .= '</div>';
							$HTML .= '<br class="clear" />';
						}
						$HTML .= '<div>';
						$HTML .= '<input disabled="disabled" type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= HOAM_formTextareaDisabled ('homeowner|comments', $_HOAM_language ['field_names']['homeowner']['comments'], $_SESSION['homeowner_information']['trusted_homeowner|comments'], $homeowner_class -> returnComments ($trusted_homeowner_id), $_SESSION['current_user']['edit']['row'], $homeowner_class -> returnColumnSize ('comments'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End new homeowner tab

						$HTML .= '<div class="tabbertab" id="homeowner|budget" title="' . $_HOAM_language ['form_tabs']['homeowner']['assessment'] . '">';
						$HTML .= '<div class="optional">';
						$account_balance = $budget_class -> returnAccountBalance ($trusted_homeowner_id);
						$HTML .= 'Current Account Balance: ' . HOAM_returnCurrency ($account_balance);
						if ($account_balance > 0) {
							$oldest_charge = $budget_class -> returnOldestCharge ($trusted_homeowner_id);
							$last_payment = $budget_class -> returnLastPayment ($trusted_homeowner_id);
							$HTML .= ' (' . round ((time () - strtotime ($budget_class -> returnDateDue ($oldest_charge))) / 86400) . $_HOAM_language ['field_names']['budget']['last_payment'];
							if ($last_payment) {
								$HTML .= HOAM_date ($_HOAM_country ['date']['format_long'], strtotime ($budget_class -> returnDatePosted ($last_payment)));
							} else {
								$HTML .= $_HOAM_language ['common']['never'];
							}
							$HTML .= ')';
							$HTML .= '<br /><a href="/admin/financial/homeowner/review/' . $trusted_homeowner_id . '/">Review Account Statement</a>';
							$HTML .= '<br /><a href="/admin/financial/homeowner/payment_list/' . $trusted_homeowner_id . '/">Payment History</a>';
							$HTML .= '<br /><a href="/admin/financial/homeowner/invoice/' . $trusted_homeowner_id . '/">View Current Invoice</a>';
							$HTML .= '<ul style="list-style: none">';
							if ($homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['budget']['flags']['late_notice'], 'budget_flags')) {
								$HTML .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_notice_description'] . '</li>';
							}
							if ($homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['budget']['flags']['late_delinquent'], 'budget_flags')) {
								$HTML .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_delinquent_description'] . '</li>';
							}
							if ($homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['budget']['flags']['late_default'], 'budget_flags')) {
								$HTML .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_default_description'] . '</li>';
							}
							if ($homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['budget']['flags']['late_attorney'], 'budget_flags')) {
								$HTML .= '<span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_attorney_description'] . '</li>';
							}
							$HTML .= '</ul>';
						}
						$HTML .= '<fieldset id="homeowner|assessment|flags" class="advanced_options">';
						$HTML .= HOAM_formCheckboxDisabled ('homeowner|assessment|payment-plan', $_HOAM_language ['field_names']['homeowner']['assessment']['payment_plan'], 1, $homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags'));
						$HTML .= '<fieldset id="payment_plan_info"';
						if ($homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['budget']['flags']['payment_plan'])) {
						} else {
							$HTML .= ' style="display: none"';
						}
						$HTML .= HOAM_formTextareaDisabled ('homeowner|assessment|payment-plan-details', $_HOAM_language ['field_names']['homeowner']['assessment']['payment-plan-details'], $_SESSION['homeowner_information']['trusted_homeowner|assessment|payment-plan-details'], $homeowner_class -> returnPaymentPlanDetails ($trusted_homeowner_id), $_SESSION['current_user']['edit']['row'], $homeowner_class -> returnColumnSize ('payment_plan_details'));
						$HTML .= '</fieldset>';
						$HTML .= HOAM_formCheckboxDisabled ('homeowner|assessment|exempt', $_HOAM_language ['field_names']['homeowner']['assessment']['exempt'], 1, $homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['homeowner']['flags']['ignore_budget']));
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End budget tab

						$HTML .= '<div class="tabbertab" id="homeowner_violation" title="' . $_HOAM_language ['form_tabs']['homeowner']['violation'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formCheckboxDisabled ('homeowner|ignore_violations', $_HOAM_language ['field_names']['homeowner']['ignore_violations'], 1, $homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['homeowner']['flags']['ignore_violations']));
						$HTML .= '</fieldset>';
						$HTML .= '<h3 style="display: block; text-align: center; text-decoration: underline;">' . $_HOAM_language ['field_names']['violation']['homeowner']['current'] . '</h3>';
						// Pick an arbitrary HOAM_date in the past for the last HOAM_date.
						$HTML .= HOAM_violationResidenceHistory ($homeowner_class -> returnLotId ($trusted_homeowner_id), $homeowner_class -> returnPurchaseDate ($trusted_homeowner_id), '3000-01-01');
						$HTML .= '<h3 style="display: block; text-align: center; text-decoration: underline;">' . $_HOAM_language ['field_names']['violation']['homeowner']['previous'] . '</h3>';
						// Pick an arbitrary HOAM_date in the past for the first HOAM_date.
						$HTML .= HOAM_violationResidenceHistory ($homeowner_class -> returnLotId ($trusted_homeowner_id), '1900-01-01', $homeowner_class -> returnPurchaseDate ($trusted_homeowner_id));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Violation tab

						$HTML .= '<div class="tabbertab" id="homeowner_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End History tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/homeowner/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('edit') :
			$_HOAM_log -> add ('Loading /admin/homeowner/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_homeowner_id = $homeowner_class -> superClean ($GLOBALS['global_url_array'][3], $homeowner_class -> returnColumnSize ('id'));
					if ($homeowner_class -> idExists ($trusted_homeowner_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['homeowner']['edit'] . ' (' . HOAM_returnAddress ($homeowner_class -> returnLotId ($trusted_homeowner_id)) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminHomeownerJS');
						
						$work_request_class = new HOAM_work_request ('work_requests');
						
						$HTML = '<h1>' . $_HOAM_language ['field_names']['homeowner']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'attachments.php');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the homeowner we're trying to edit is the one that we
						// have information on in the homeowner_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if a homeowner was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['homeowner_information']['trusted_homeowner|id']) AND ($trusted_homeowner_id != $_SESSION['homeowner_information']['trusted_homeowner|id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['homeowner_information'])) {
								unset ($_SESSION['homeowner_information']);
							}
						}

						$HTML .= '<form id="homeowner_edit" action="/hoam/scripts/homeowner/edit_script.php" enctype="multipart/form-data" method="post">';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';
						$HTML .= '<div class="tabbertab" id="homeowner_information" title="' . $_HOAM_language ['form_tabs']['homeowner']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<input type="hidden" id="homeowner|id" name="homeowner|id" value="' . $trusted_homeowner_id . '" />';
						$HTML .= HOAM_formSelect ('homeowner|address', $_HOAM_language ['field_names']['homeowner']['address']);
						if (isset ($_SESSION['homeowner_information']['trusted_address'])) {
							$default_lot = $_SESSION['homeowner_information']['trusted_address'];
						} else {
							$default_lot = $homeowner_class -> returnLotId ($trusted_homeowner_id);
						}
						$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
						foreach ($lot_list as $lot) {
							$HTML .= '<option ';
							if ($lot['id'] == $default_lot) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
							$HTML .= '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formText ('homeowner|name', $_HOAM_language ['field_names']['homeowner']['name'], $_SESSION['homeowner_information']['trusted_homeowner|name'], stripslashes ($homeowner_class -> returnName ($trusted_homeowner_id)));
						$HTML .= '<label for="homeowner|residence_mailing">' . $_HOAM_language ['field_names']['homeowner']['mailing_address'];
						$HTML .= HOAM_formDisplayHelp ('homeowner|residence_mailing');
						$HTML .= HOAM_formDisplayError ('homeowner|residence_mailing');
						$HTML .= '</label>';
						if ((isset ($_SESSION['homeowner_information']['trusted_homeowner|residence_mailing']) AND is_true ($_SESSION['homeowner_information']['trusted_homeowner|residence_mailing'])) OR
						$homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['homeowner']['flags']['residence_mailing'])) {
							$HTML .= '<input checked="checked" type="checkbox" name="homeowner|residence_mailing" id="homeowner|residence_mailing" value="1" />';
							$HTML .= '</div>';
							$HTML .= '<fieldset id="homeowner|mailing_address" style="display: none">';
						} else {
							$HTML .= '<input type="checkbox" name="homeowner|residence_mailing" id="homeowner|residence_mailing" value="1" />';
							$HTML .= '</div>';
							$HTML .= '<fieldset id="homeowner|mailing_address">';
						}
						$HTML .= '<div class="mandatory">';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formCheckbox ('homeowner|resident', $_HOAM_language ['field_names']['homeowner']['resident'], 1, (isset ($_SESSION['homeowner_information']['trusted_homeowner|resident']) AND is_true ($_SESSION['homeowner_information']['trusted_homeowner|resident'])), $homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['homeowner']['flags']['resident']));
						$HTML .= '</fieldset>';
						$HTML .= HOAM_formText ('homeowner|address1', $_HOAM_language ['field_names']['homeowner']['address1'], $_SESSION['homeowner_information']['trusted_homeowner|address1'], $homeowner_class -> returnAddress1 ($trusted_homeowner_id));
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formText ('homeowner|address2', $_HOAM_language ['field_names']['homeowner']['address2'], $_SESSION['homeowner_information']['trusted_homeowner|address2'], $homeowner_class -> returnAddress2 ($trusted_homeowner_id));
						$HTML .= '</div>';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formText ('homeowner|address3', $_HOAM_language ['field_names']['homeowner']['address3'], $_SESSION['homeowner_information']['trusted_homeowner|address3'], $homeowner_class -> returnAddress3 ($trusted_homeowner_id));
						$HTML .= HOAM_formText ('homeowner|city', $_HOAM_language ['field_names']['homeowner']['city'], $_SESSION['homeowner_information']['trusted_homeowner|city'], $homeowner_class -> returnCity ($trusted_homeowner_id), $homeowner_class -> returnColumnSize ('city'), 1);
						$HTML .= HOAM_formSelect ('homeowner|state', $_HOAM_language ['field_names']['homeowner']['state']);
						if (isset ($_SESSION['homeowner_information']['trusted_homeowner|state'])) {
							$default_state = $_SESSION['homeowner_information']['trusted_homeowner|state'];
						} else {
							$default_state = $homeowner_class -> returnState ($trusted_homeowner_id);
						}
						foreach ($_HOAM_country ['state'] as $state) {
							$HTML .= '<option ';
							if ($state['value'] == $default_state) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $state['value'] . '">' . $state['description'] . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</div>';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formField ('homeowner|postalcode', $_HOAM_language ['field_names']['homeowner']['postalcode'], $_SESSION['homeowner_information']['trusted_homeowner|postalcode'], preg_replace ($_HOAM_country ['postalcode']['match'], $_HOAM_country ['postalcode']['format'], $homeowner_class -> returnzipcode ($trusted_homeowner_id), 1), $homeowner_class -> returnColumnSize ('zipcode'), $_HOAM_country ['postalcode']['minlength']);
						$HTML .= '</div>';
						$HTML .= '</fieldset>';
						$HTML .= '<fieldset>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formField ('homeowner|telephone|home', $_HOAM_language ['field_names']['homeowner']['telephone']['home'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|home'], preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_class -> returnTelephoneHome ($trusted_homeowner_id), 1), $homeowner_class -> returnColumnSize ('telephone_home'), $_HOAM_country ['telephone']['length']);
						$HTML .= HOAM_formField ('homeowner|telephone|work', $_HOAM_language ['field_names']['homeowner']['telephone']['work'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|work'], preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_class -> returnTelephoneWork ($trusted_homeowner_id), 1), $homeowner_class -> returnColumnSize ('telephone_work'), $_HOAM_country ['telephone']['length']);
						$HTML .= HOAM_formField ('homeowner|telephone|mobile', $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|mobile'], preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_class -> returnTelephoneMobile ($trusted_homeowner_id), 1), $homeowner_class -> returnColumnSize ('telephone_mobile'), $_HOAM_country ['telephone']['length']);
						$HTML .= HOAM_formField ('homeowner|telephone|fax', $_HOAM_language ['field_names']['homeowner']['telephone']['fax'], $_SESSION['homeowner_information']['trusted_homeowner|telephone|fax'], preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_class -> returnTelephoneFax ($trusted_homeowner_id), 1), $homeowner_class -> returnColumnSize ('telephone_fax'), $_HOAM_country ['telephone']['length']);
						$HTML .= HOAM_formText ('homeowner|email', $_HOAM_language ['field_names']['homeowner']['email'], $_SESSION['homeowner_information']['trusted_homeowner|email'], $homeowner_class -> returnEmail ($trusted_homeowner_id), $homeowner_class -> returnColumnSize ('email'));
						$HTML .= HOAM_formField ('homeowner|access_code', $_HOAM_language ['field_names']['homeowner']['access_code'], $_SESSION['homeowner_information']['trusted_homeowner|access_code'], $homeowner_class -> returnAccessCode ($trusted_homeowner_id), $homeowner_class -> returnColumnSize ('access_code'), 0);
						$HTML .= '</div>';
						$HTML .= '</fieldset>';

						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formDate ('homeowner|purchasedate', $_HOAM_language ['field_names']['homeowner']['purchasedate'], $_SESSION['homeowner_information']['trusted_homeowner|purchasedate'], HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_class -> returnPurchasedate ($trusted_homeowner_id))));
						// We need to accomodate saledates that are null or all
						// zeroes (eg, the residence has not been sold).
						$trusted_saledate = $homeowner_class -> returnSaledate ($trusted_homeowner_id);
						if (!empty ($trusted_saledate) AND ($trusted_saledate != '1970-01-01')) {
							$trusted_saledate = HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($trusted_saledate));
						} else {
							$trusted_saledate = '';
						}
						$HTML .= HOAM_formDate ('homeowner|saledate', $_HOAM_language ['field_names']['homeowner']['saledate'], $_SESSION['homeowner_information']['trusted_homeowner|saledate'], $trusted_saledate);
						$HTML .= '</div>';

						$HTML .= '<div class="optional">';
						$HTML .= '<label for="homeowner|attachments">' . $_HOAM_language ['field_names']['homeowner']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('homeowner|attachments');
						$HTML .= HOAM_formDisplayError ('homeowner|attachments');
						$HTML .= '</label>';
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_homeowner_id, $_HOAM_setting ['attachment']['flags']['homeowner']);
						if (!empty ($attachment_list)) {
							$year = date ('Y', strtotime ($attachments -> returnDateCreated ($attachment_list[0])));
							$HTML .= '<span style="font-weight: bold" field="year">&#x0229F; ' . $year . '</span>';
							$HTML .= '<div>';
							foreach ($attachment_list as $attachment) {
								if (mb_substr ($attachments -> returnDateCreated ($attachment), 0, 4) == $year) {
								} else {
									$year = mb_substr ($attachments -> returnDateCreated ($attachment), 0, 4);
									$HTML .= '</div>';
									$HTML .= '<span style="clear: left; display: block" field="year">&#x0229E; ' . $year . '</span>';
									$HTML .= '<div style="display: none">';
								}
								$HTML .= HOAM_attachmentReturnHTML ($attachment);
							}
							$HTML .= '</div>';
							$HTML .= '<br class="clear" />';
						}
						$HTML .= '<div>';
						$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= HOAM_formTextarea ('homeowner|comments', $_HOAM_language ['field_names']['homeowner']['comments'], $_SESSION['homeowner_information']['trusted_homeowner|comments'], $homeowner_class -> returnComments ($trusted_homeowner_id), $_SESSION['current_user']['edit']['row'], $homeowner_class -> returnColumnSize ('comments'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End new homeowner tab

						$HTML .= '<div class="tabbertab" id="homeowner|budget" title="' . $_HOAM_language ['form_tabs']['homeowner']['assessment'] . '">';
						$HTML .= '<div class="optional">';
						$account_balance = $budget_class -> returnAccountBalance ($trusted_homeowner_id);
						$HTML .= 'Current Account Balance: ' . HOAM_returnCurrency ($account_balance);
						if ($account_balance > 0) {
							$oldest_charge = $budget_class -> returnOldestCharge ($trusted_homeowner_id);
							$last_payment = $budget_class -> returnLastPayment ($trusted_homeowner_id);
							$HTML .= ' (' . round ((time () - strtotime ($budget_class -> returnDateDue ($oldest_charge))) / 86400) . $_HOAM_language ['field_names']['budget']['last_payment'];
							if ($last_payment) {
								$HTML .= HOAM_date ($_HOAM_country ['date']['format_long'], strtotime ($budget_class -> returnDatePosted ($last_payment)));
							} else {
								$HTML .= $_HOAM_language ['common']['never'];
							}
							$HTML .= ')';
						}
						$HTML .= '<br /><a href="/admin/financial/homeowner/review/' . $trusted_homeowner_id . '/">Review Account Statement</a>';
						$HTML .= '<br /><a href="/admin/financial/homeowner/payment_list/' . $trusted_homeowner_id . '/">Payment History</a>';
						$HTML .= '<br /><a href="/admin/financial/homeowner/invoice/' . $trusted_homeowner_id . '/">View Current Invoice</a>';
						$homeowner_flags = $homeowner_class -> returnFlags ($trusted_homeowner_id);
						if ($account_balance > 0) {
							$budget_flags = $homeowner_class -> returnFlags ($trusted_homeowner_id, 'budget_flags');
							$flags_already_set = '';
							$flags_to_set = '';
							if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_notice']) {
								$flags_already_set .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_notice_description'] . '</li>';
								$HTML .= '<input type="hidden" name="homeowner|flags|late_notice" value="1" />';								
								if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_delinquent']) {
									$flags_already_set .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_delinquent_description'] . '</li>';
									$HTML .= '<input type="hidden" name="homeowner|flags|late_delinquent" value="1" />';								
									if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_default']) {
										$flags_already_set .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_default_description'] . '</li>';
										$HTML .= '<input type="hidden" name="homeowner|flags|late_default" value="1" />';								
										if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_lien']) {
											$flags_already_set .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_lien'] . '</li>';
											$HTML .= '<input type="hidden" name="homeowner|flags|late_lien" value="1" />';								
										} else {
											$flags_to_set .= HOAM_formCheckbox ('homeowner|flags|late_lien', $_HOAM_language ['field_names']['budget']['flags']['late_lien'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['late_lien'], (int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_lien']);
										}
										if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney']) {
											$flags_already_set .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_attorney_description'] . '</li>';
											$HTML .= '<input type="hidden" name="homeowner|flags|late_attorney" value="1" />';								
											if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_demand']) {
												$flags_already_set .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_attorney_demand_description'] . '</li>';
												$HTML .= '<input type="hidden" name="homeowner|flags|late_attorney_demand" value="1" />';
												if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_foreclosure']) {
													$flags_already_set .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_attorney_foreclosure_description'] . '</li>';
													$HTML .= '<input type="hidden" name="homeowner|flags|late_attorney_foreclosure" value="1" />';								
													if ((int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_eviction']) {
														$flags_already_set .= '<li><span class="complete">' . $_HOAM_language ['field_names']['tasklist']['mini']['complete'] . '</span> ' . $_HOAM_language ['field_names']['budget']['flags']['late_attorney_eviction_description'] . '</li>';
														$HTML .= '<input type="hidden" name="homeowner|flags|late_attorney_eviction" value="1" />';							
													} else {
														$flags_to_set .= HOAM_formCheckbox ('homeowner|flags|late_attorney_eviction', $_HOAM_language ['field_names']['budget']['flags']['late_attorney_eviction_description'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['late_attorney_eviction'], (int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_eviction']);
													}									
												} else {
													$flags_to_set .= HOAM_formCheckbox ('homeowner|flags|late_attorney_foreclosure', $_HOAM_language ['field_names']['budget']['flags']['late_attorney_foreclosure_description'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['late_attorney_foreclosure'], (int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_foreclosure']);
												}									
											} else {
												$flags_to_set .= HOAM_formCheckbox ('homeowner|flags|late_attorney_demand', $_HOAM_language ['field_names']['budget']['flags']['late_attorney_demand_description'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['late_attorney_demand'], (int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney_demand']);
											}									
										} else {
											$flags_to_set .= HOAM_formCheckbox ('homeowner|flags|late_attorney', $_HOAM_language ['field_names']['budget']['flags']['late_attorney_description'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['late_attorney'], (int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_attorney']);
										}									
									} else {
										$flags_to_set .= HOAM_formCheckbox ('homeowner|flags|late_default', $_HOAM_language ['field_names']['budget']['flags']['late_default_description'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['late_default'], (int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_default']);
									}									
								} else {
									$flags_to_set .= HOAM_formCheckbox ('homeowner|flags|late_delinquent', $_HOAM_language ['field_names']['budget']['flags']['late_delinquent_description'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['late_delinquent'], (int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_delinquent']);
								}
							} else {
								$flags_to_set .= HOAM_formCheckbox ('homeowner|flags|late_notice', $_HOAM_language ['field_names']['budget']['flags']['late_notice_description'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['late_notice'], (int) $budget_flags & (int) $_HOAM_setting ['budget']['flags']['late_notice']);
							}
							if (isset ($flags_already_set)) {						
								$HTML .= '<ul style="list-style: none">';
								$HTML .= $flags_already_set;
								$HTML .= '</ul>';
							}
						}
						$HTML .= '<label for="homeowner|assessment|flags">' . $_HOAM_language ['field_names']['homeowner']['assessment']['flags'];
						$HTML .= '</label>';
						$HTML .= '<fieldset id="homeowner|assessment|flags" class="advanced_options">';
						$HTML .= $flags_to_set;

						$payment_plan_date = $homeowner_class -> returnPaymentPlanDate ($trusted_homeowner_id);
						if (isset ($_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan']) OR $homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['budget']['flags']['payment_plan'], 'budget_flags')) { 
							$payment_plan = TRUE;
						} else {
							$payment_plan = FALSE;
						}
						if ($payment_plan_date AND ($payment_plan_date != '1970-01-01')) {
							if ($payment_plan) {
								$HTML .= 'Payment Plan Effective Date: ' . date ($_HOAM_country ['date']['format_long'], strtotime ($payment_plan_date)) . '<br />';
								$HTML .= HOAM_formCheckbox ('homeowner|assessment|payment_plan_default', $_HOAM_language ['field_names']['homeowner']['assessment']['payment_plan_default'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['payment_plan_default'], (int) $homeowner_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan_default']);
							} else {
								$HTML .= 'Payment Plan Default Date: ' . date ($_HOAM_country ['date']['format_long'], strtotime ($payment_plan_date)) . '<br />';
								$HTML .= HOAM_formCheckbox ('homeowner|assessment|payment_plan', $_HOAM_language ['field_names']['homeowner']['assessment']['payment_plan'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['payment_plan'], (int) $homeowner_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan']);
							}
						} else {
							$HTML .= HOAM_formCheckbox ('homeowner|assessment|payment_plan', $_HOAM_language ['field_names']['homeowner']['assessment']['payment_plan'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['budget']['flags']['payment_plan'], (int) $homeowner_flags & (int) $_HOAM_setting ['budget']['flags']['payment_plan']);
						}
						$HTML .= '<fieldset id="payment_plan_info"';
						if ($payment_plan) {
							$HTML .= ' style="clear: left; text-align: left"';
						} else {
							$HTML .= ' style="clear: left; display: none; text-align: left"';
						}
						$HTML .= '>';
						$HTML .= HOAM_formTextarea ('homeowner|assessment|payment_plan_details', $_HOAM_language ['field_names']['homeowner']['assessment']['payment_plan_details'], $_SESSION['homeowner_information']['trusted_homeowner|assessment|payment_plan_details'], $homeowner_class -> returnPaymentPlanDetails ($trusted_homeowner_id), $_SESSION['current_user']['edit']['row'], $homeowner_class -> returnColumnSize ('payment_plan_details'));
						$HTML .= '</fieldset>';

						$bankrupt_date = $homeowner_class -> returnDateBankrupt ($trusted_homeowner_id);
						if (((int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['bankrupt']) OR ((int) $budget_flags & (int) $_HOAM_setting ['homeowner']['flags']['bankrupt'])) { 
							$bankrupt = TRUE;
						}
						$HTML .= HOAM_formCheckbox ('homeowner|flags|bankrupt', $_HOAM_language ['field_names']['budget']['flags']['bankrupt'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['bankrupt'], (int) $budget_flags & (int) $_HOAM_setting ['homeowner']['flags']['bankrupt']);
						$HTML .= '<fieldset id="bankruptcy_info"';
						if ($bankrupt) {
							$HTML .= ' style="clear: left; text-align: left"';
						} else {
							$HTML .= ' style="clear: left; display: none; text-align: left"';
						}
						$HTML .= '>';
						if ($bankrupt) {
							$HTML .= HOAM_formDate ('homeowner|assessment|bankrupt_date', $_HOAM_language ['field_names']['homeowner']['assessment']['bankrupt_date'], $_SESSION['homeowner_information']['trusted_homeowner|assessment|bankrupt_date'], date ($_HOAM_country ['date']['format_reports'], strtotime ($bankrupt_date)), $homeowner_class -> returnColumnSize ('bankruptcy_date'));
						} else{
							$HTML .= HOAM_formDate ('homeowner|assessment|bankrupt_date', $_HOAM_language ['field_names']['homeowner']['assessment']['bankrupt_date'], $_SESSION['homeowner_information']['trusted_homeowner|assessment|bankrupt_date'], '', $homeowner_class -> returnColumnSize ('bankruptcy_date'));
						} 
						$HTML .= '</fieldset>';

						$HTML .= HOAM_formCheckbox ('homeowner|flags|service_member', $_HOAM_language ['field_names']['homeowner']['flags']['service_member'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['service_member'], (int) $homeowner_flags & (int) $_HOAM_setting ['homeowner']['flags']['service_member']);
						$HTML .= HOAM_formCheckbox ('homeowner|flags|no_fees', $_HOAM_language ['field_names']['homeowner']['flags']['no_fees'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['no_fees'], (int) $homeowner_flags & (int) $_HOAM_setting ['homeowner']['flags']['no_fees']);
						$HTML .= HOAM_formCheckbox ('homeowner|assessment|exempt', $_HOAM_language ['field_names']['homeowner']['assessment']['exempt'], 1, (int) $_SESSION['homeowner_information']['trusted_homeowner|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['ignore_budget'], (int) $homeowner_flags & (int) $_HOAM_setting ['homeowner']['flags']['ignore_budget']);
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End budget tab

						$HTML .= '<div class="tabbertab" id="homeowner_violation" title="' . $_HOAM_language ['form_tabs']['homeowner']['violation'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formCheckbox ('homeowner|ignore_violations', $_HOAM_language ['field_names']['homeowner']['ignore_violations'], 1, isset ($_SESSION['homeowner_information']['trusted_homeowner|ignore_violations']) AND is_true ($_SESSION['homeowner_information']['trusted_homeowner|ignore_violations']), $homeowner_class -> isBitSet ($trusted_homeowner_id, $_HOAM_setting ['homeowner']['flags']['ignore_violations']));
						$HTML .= '</fieldset>';
						$HTML .= '<h3 style="display: block; text-align: center; text-decoration: underline;">' . $_HOAM_language ['field_names']['violation']['homeowner']['current'] . '</h3>';
						// Pick an arbitrary date in the past for the last date.
						$HTML .= HOAM_violationResidenceHistory ($homeowner_class -> returnLotId ($trusted_homeowner_id), $homeowner_class -> returnPurchaseDate ($trusted_homeowner_id), '3000-01-01');
						$HTML .= '<h3 style="display: block; text-align: center; text-decoration: underline;">' . $_HOAM_language ['field_names']['violation']['homeowner']['previous'] . '</h3>';
						// Pick an arbitrary date in the past for the first date.
						$HTML .= HOAM_violationResidenceHistory ($homeowner_class -> returnLotId ($trusted_homeowner_id), '1900-01-01', $homeowner_class -> returnPurchaseDate ($trusted_homeowner_id));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Violation tab

						$HTML .= '<div class="tabbertab" id="request_list" title="' . $_HOAM_language ['form_tabs']['homeowner']['work_request'] . '">';
						$HTML .= '<div class="optional">';
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
						$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
						$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['requester'] . '</th>';
						$thead .= '</tr>';
						$thead .= '</thead>';
						$tbody = '<tbody>';
						$request_list = $work_request_class -> listLotByDate ($homeowner_class -> returnLotId ($trusted_homeowner_id), $homeowner_class -> returnPurchaseDate ($trusted_homeowner_id), date ($_HOAM_country['date']['format_mysql']));
						if (is_array ($request_list) AND !empty ($request_list)) {
							foreach ($request_list as $request) {
								$request_info = $work_request_class -> ListAll ($request[0]);
								// We reassign it to itself, because the ListAll() function assumes you're retrieving multiple rows.
								$request_info = $request_info[0];

								$tbody .= '<tr>';
								$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($request_info['datecreated'])) . '</td>';
								switch (TRUE) {
									case ((int) $_HOAM_setting ['work_request']['flags']['urgent'] == (int) $request_info['priority']) :
										$tbody .= '<td class="error">';
										break;
									case ((int) $_HOAM_setting ['work_request']['flags']['high'] == (int) $request_info['priority']) :
										$tbody .= '<td class="warning">';
										break;
									default:
										$tbody .= '<td>';
										break;
								}
   								foreach ($_HOAM_language ['priority'] as $priority) {
   									if ((int) $_HOAM_setting ['work_request']['flags'][$priority['value']] == (int) $request_info['priority']) {
   										$tbody .= $priority['name'];
   										break;  
	   								}
   								}
								$tbody .= '</td>';
								$tbody .= '<td>';
   								foreach ($_HOAM_language ['status'] as $status) {
   									if ((int) $_HOAM_setting ['work_request']['flags'][$status['value']] == (int) $request_info['status']) {
   										$tbody .= $status['name'];
   										break;  
	   								}
   								}
								$tbody .= '</td>';
								$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($request_info['datedue'])) . '</td>';
								$tbody .= '<td>' . HOAM_returnAddress ($request_info['lot_id']) . '</td>';
								$tbody .= '<td style="text-align: left">';
								if ($request_info['parent_id']) {
									$tbody .= '&nbsp; &#9492;&#9472; &nbsp;';
								}
								$tbody .= '' . HOAM_returnTruncatedString ($request_info['title'], 40) . '</td>';
								$tbody .= '<td><small><a href="/admin/work_request/edit/' . $request_info['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small></td>';
								$tbody .= '<td>' . $user_class -> returnAccountName ($request_info['user_id']) . '</td>';
								$tbody .= '</tr>';
							}
						} else {
							$tbody .= '<tr><td colspan="8">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
						}
						$tbody .= '</tbody>';
						$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>'. $tbody . '<tbody></table>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Work Request tab
						
						$HTML .= '<div class="tabbertab" id="account_list" title="' . $_HOAM_language ['form_tabs']['homeowner']['users'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<table>';
						$HTML .= '<colgroup>';
						$HTML .= '<col span="1" width="10%" />';
						$HTML .= '</colgroup>';
						$HTML .= '<thead><tr>';
						$HTML .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['primary'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['user']['accountname'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['user']['name']['full'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['tables']['column']['user']['last_login'] . '</th>';
						$HTML .= '</tr></thead>';
						$HTML .= '<tbody>';
						$associated_accounts = $user_class -> listIDGivenField ('homeowner_id', $trusted_homeowner_id);
						if (is_array ($associated_accounts)) {
							foreach ($associated_accounts as $account) {
								$account_info = $user_class -> listAll ($account);
								$account_info = $account_info[0];
								$HTML .= '<tr>';
								$HTML .= '<td class="center">';
								if ((int) $account_info['flags'] & (int) $_HOAM_setting ['user']['flags']['residence_primary']) {
									$HTML .= '&#10003;';
								} else {
									$HTML .= '&nbsp;';
								}
								$HTML .= '</td>';
								$HTML .= '<td class="center">' . $account_info['accountname'] . '</td>';
								($account_info['firstname'] OR $account_info['lastname']) ? $HTML .= '<td class="center">' . $account_info['firstname'] . ' ' . $account_info['lastname'] . '</td>' : $HTML .= '<td class="center">' . $_HOAM_language ['field_names']['generic']['not_provided'] . '</center>';
								$HTML .= '<td class="center">';
								$account_info['datelastlogin'] ? $HTML .= HOAM_date ($_HOAM_country ['date']['format_long'] . ' ' . $_HOAM_country ['time']['format_long'], strtotime ($account_info['datelastlogin'])) : $HTML .= $_HOAM_language ['common']['never'];
								$HTML .= '</td>';
								$HTML .= '</tr>';
							}
						} else {
							$HTML .= '<tr><td colspan="4" class="center">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
						}
						$HTML .= '</tbody>';
						$HTML .= '</table>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Associated accounts tab
						
						$HTML .= '<div class="tabbertab" id="previous_homeowners" title="' . $_HOAM_language ['form_tabs']['homeowner']['previous'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<table>';
						$HTML .= '<colgroup><col span="1" width="10%" /></colgroup>';
						$HTML .= '<thead><tr>';
						$HTML .= '<th>#</th><th>' . $_HOAM_language ['field_names']['user']['name']['full'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['date']['purchased'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['date']['sold'] . '</th>';
						$HTML .= '</tr></thead>';
						$HTML .= '<tbody>';
						$previous_owners = $homeowner_class -> listResidenceHomeowners ($homeowner_class -> returnLotId ($trusted_homeowner_id));
						$previous_count = count ($previous_owners);
						for ($i = 0; $i < $previous_count; $i++) {
							$homeowner_info = $homeowner_class -> listAll ($previous_owners[$i]);
							$homeowner_info = $homeowner_info[0];
							$HTML .= '<tr>';
							$HTML .= '<td class="center">' . ($previous_count - $i) . '</td>';
							$HTML .= '<td class="center"><a href="/admin/homeowner/edit/' . $homeowner_info['id'] . '/">' . stripslashes ($homeowner_info['name']) . '</a></td>';
							$HTML .= '<td class="center">' . HOAM_date ($_HOAM_country ['date']['format_long'], strtotime ($homeowner_info['purchasedate'])) . '</td>';
							$HTML .= '<td class="center">';
							if ($homeowner_info['saledate'] AND ($homeowner_info['saledate'] != '1970-01-01')) {
								$HTML .= HOAM_date ($_HOAM_country ['date']['format_long'], strtotime ($homeowner_info['saledate']));
							} else {
								$HTML .= $_HOAM_language ['common']['na'];
							}
							$HTML .= '</td>';
							$HTML .= '</tr>';
						}
						$HTML .= '</tbody>';
						$HTML .= '</table>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End previous homeowner tab

						$HTML .= '<div class="tabbertab" id="homeowner_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End History tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/homeowner/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			$_HOAM_log -> add ('Loading /admin/homeowner/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['homeowner']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['homeowner']['list'] . '</h1>';
				$homeowner_count = 0;
				$HTML .= '<table class="center">';
				$thead = '<thead><tr>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['address'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['name'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['telephone']['home'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['telephone']['mobile'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['email'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['flags'] . '</th>';
				$thead .= '</tr></thead>';
				$tbody = '<tbody id="homeowner_list">';
				$lot_list = $lot_class -> listAll (NULL, 'street, address, building, suite');
				if (is_array ($lot_list)) {
					foreach ($lot_list as $lot) {
						$tbody .= '<tr>';
						$tbody .= '<td>' . HOAM_returnAddress ($lot) . '</td>';
						// Who is the current owner for this lot?
						$current_homeowner = $homeowner_class -> returnCurrentOwner ($lot['id']);
						if ($current_homeowner) {
							$homeowner_count++;
							// Get all info for the current owner
							$homeowner_info = $homeowner_class -> listAll ($current_homeowner);
							$homeowner_info = $homeowner_info[0];
							$tbody .= '<td>' . html_encode (stripslashes ($homeowner_info['name'])) . '</td>';
							$tbody .= '<td>';
							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
								$tbody .= '<small><a href="/admin/homeowner/edit/' . $homeowner_info['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
							}
							$tbody .= ' &nbsp; ';
							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
								$tbody .= '<small><a class="error" href="/admin/homeowner/delete/' . $homeowner_info['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
							}
							$tbody .= '</td>';
							if ($homeowner_info['telephone_home']) {
								$tbody .= '<td>' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_home'], 1). '</td>';
							} else {
								$tbody .= '<td>&nbsp;</td>';
							}
							if ($homeowner_info['telephone_mobile']) {
								$tbody .= '<td>' . preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $homeowner_info['telephone_mobile'], 1). '</td>';
							} else {
								$tbody .= '<td>&nbsp;</td>';
							}
							if ($homeowner_info['email']) {
								$tbody .= '<td>' . $homeowner_info['email'] . '</td>';
							} else {
								$tbody .= '<td>&nbsp;</td>';
							}
							$tbody .= '<td>';
							if ((int) $homeowner_info['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['resident']) {
							} else {
								$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['abbr']['lr']) . '">R</span> ';
							}
							if ((int) $homeowner_info['flags'] & (int) $_HOAM_setting ['homeowner']['flags']['no_fees']) {
								$tbody .= '<span class="cursor_help caution" title="' . strip_tags ($_HOAM_language ['abbr']['nf']) . '">&curren;</span> ';
							}
							if ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['homeowner']['flags']['ignore_budget']) {
								$tbody .= '<span class="caution cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['homeowner']['flags']['ignore_budget']) . '">E</span> ';
							}
							if ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['payment_plan']) {
								$tbody .= '<span class="complete cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['payment_plan_description']) . '">P</span> ';
							}
							if ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['payment_plan_default']) {
								$tbody .= '<span class="complete cursor_help warning" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['payment_plan_default_description']) . '">P</span> ';
							}
							if ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['homeowner']['flags']['bankrupt']) {
								$tbody .= '<span class="caution cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['bankrupt']) . '">B</span> ';
							}
							switch (TRUE) {
								case ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['late_attorney']) :
									$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_description']) . '">A</span>';
									switch (TRUE) {
										case ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['late_attorney_eviction']) :
											$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_eviction_description']) . '">(E)</span>';
											break;
										case ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['late_attorney_foreclosure']) :
											$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_foreclosure_description']) . '">(F)</span>';
											break;
										case ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['late_attorney_demand']) :
											$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_attorney_demand_description']) . '">(D)</span>';
											break;
									}
									$tbody .= ' ';
									break;
								case ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['late_default']) :
									$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_default_description']) . '">3</span> ';
									break;
								case ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['late_delinquent']) :
									$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_delinquent_description']) . '">2</span> ';
									break;
								case ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['late_notice']) :
									$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_notice_description']) . '">1</span> ';
									break;
								default:
									break;
							}
							if ((int) $homeowner_info['budget_flags'] & (int) $_HOAM_setting ['budget']['flags']['late_lien']) {
								$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['budget']['flags']['late_lien_description']) . '">L</span> ';
							}
							$tbody .= '</td>';
						} else {
							$tbody .= '<td colspan="7" class="error"><strong>' . $_HOAM_language ['errors']['homeowner']['no-current'] . '</strong></td>';
						}
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="7">' . $_HOAM_language ['errors']['homeowner']['none-found'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= '<caption>' . sprintf ($_HOAM_language ['field_names']['homeowner']['found'], $homeowner_count) . '</caption>';
				$HTML .= $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody;
				$HTML .= '</table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('sale') :
			$_HOAM_log -> add ('Loading /admin/homeowner/sale/ page.', HOAM_MESSAGE_DEBUG);
			$HTML = HOAM_adminHomeownerSale (TRUE);
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['homeowner'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['homeowner'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/user.png" alt="' . $_HOAM_language ['field_names']['admin']['homeowner'] . '" class="LHS" /><a href="/admin/homeowner/">' . $_HOAM_language ['field_names']['admin']['homeowner'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/user_add.png\')"><a href="/admin/homeowner/add/">' . $_HOAM_language ['field_names']['homeowner']['add'] . '</a></li>';
					}
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/user.png\')"><a href="/admin/homeowner/list/">' . $_HOAM_language ['field_names']['homeowner']['list'] . '</a></li>';
					}
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house_go.png\')"><a href="/admin/homeowner/sale/">' . $_HOAM_language ['field_names']['homeowner']['sale']['sale'] . '</a></li>';
					}
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminHomeownerSale ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');

	if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
		$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
		$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
		($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
		$homeowner_sale = new HOAM_homeowner_sale ('homeowner_sale');
		switch ($GLOBALS['global_url_array'][3]) {
			case 'add' :
				$_HOAM_log -> add ('Loading /admin/homeowner/sale/add/ page.', HOAM_MESSAGE_DEBUG);
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['homeowner']['sale']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminHomeownerSaleJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['homeowner']['sale']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
				// OK, we're checking to see that the entry we're trying
				// to edit is the one that we have information on in the
				// sale_information variable. If the referrer doesn't
				// match this current page, then we know we came from
				// somewhere else on the site, and thus the information
				// is suspect. Also, trusted_id will /only/ be set if an
				// invoice was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['sale_information']['trusted_id']))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['sale_information'])) {
						unset ($_SESSION['sale_information']);
					}
				}
				$HTML .= '<form id="add_sale" action="/hoam/scripts/homeowner/sale_edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['homeowner']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';
				$HTML .= '<div class="tabbertab" id="sale" title="' . $_HOAM_language ['form_tabs']['homeowner']['information'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelect ('homeowner|sale|address', $_HOAM_language ['field_names']['homeowner']['address']);
				if (isset ($_SESSION['sale_information']['trusted_homeowner|sale|address'])) {
					$default_lot = $_SESSION['sale_information']['trusted_homeowner|sale|address'];
				} else {
					$default_lot = NULL;
				}
				$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
				foreach ($lot_list as $lot) {
					$HTML .= '<option ';
					if ($lot['id'] == $default_lot) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
					$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($lot['id'])))) . ')</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formDate ('homeowner|sale|saledate', $_HOAM_language ['field_names']['homeowner']['sale']['saledate'], $_SESSION['sale_information']['trusted_homeowner|sale|saledate'], '', 10, 1);
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formText ('homeowner|sale|name', $_HOAM_language ['field_names']['homeowner']['sale']['name'], $_SESSION['sale_information']['trusted_homeowner|sale|name'], '', $homeowner_sale -> returnColumnSize ('name'), 0);
				$HTML .= HOAM_formText ('homeowner|sale|title', $_HOAM_language ['field_names']['homeowner']['sale']['title'], $_SESSION['sale_information']['trusted_homeowner|sale|title'], '', $homeowner_sale -> returnColumnSize ('title_company'), 0);
				$HTML .= '<label for="homeowner|sale|attachments">' . $_HOAM_language ['field_names']['homeowner']['sale']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('homeowner|sale|attachments');
				$HTML .= HOAM_formDisplayError ('homeowner|sale|attachments');
				$HTML .= '</label>';
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				
				$HTML .= HOAM_formTextarea ('homeowner|sale|comments', $_HOAM_language ['field_names']['homeowner']['sale']['comments'], $_SESSION['sale_information']['trusted_homeowner|sale|comments'], '', $_SESSION['current_user']['edit']['row'], '', 0);
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Add new residence tab.
				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
				break;
			case 'delete' :
				$_HOAM_log -> add ('Loading /admin/homeowner/sale/delete/ page.', HOAM_MESSAGE_DEBUG);
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['homeowner']['sale']['delete'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminHomeownerSaleJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['homeowner']['sale']['delete'] . '</h1>';
				$trusted_id = $homeowner_sale -> superClean ($GLOBALS['global_url_array'][4], $homeowner_sale -> returnColumnSize ('id'));
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
				// OK, we're checking to see that the entry we're trying
				// to edit is the one that we have information on in the
				// sale_information variable. If the referrer doesn't
				// match this current page, then we know we came from
				// somewhere else on the site, and thus the information
				// is suspect. Also, trusted_id will /only/ be set if an
				// invoice was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['sale_information']['trusted_id']))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['sale_information'])) {
						unset ($_SESSION['sale_information']);
					}
				}

				$HTML .= '<form id="delete_sale" action="/hoam/scripts/homeowner/sale_edit_script.php" enctype="multipart/form-data" method="post">';
				$HTML .= '<input type="hidden" name="homeowner|sale|id" id="homeowner|sale|id" value="' . $trusted_id . '" />';
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" id="sale|delete" name="delete">' . $_HOAM_language ['form_buttons']['delete'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="sale" title="' . $_HOAM_language ['form_tabs']['homeowner']['information'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelectDisabled ('homeowner|sale|address', $_HOAM_language ['field_names']['homeowner']['address']);
				$default_lot = $homeowner_sale -> returnLotId ($trusted_id);
				$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
				foreach ($lot_list as $lot) {
					$HTML .= '<option ';
					if ($lot['id'] == $default_lot) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
					$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($lot['id'])))) . ')</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formDateDisabled ('homeowner|sale|saledate', $_HOAM_language ['field_names']['homeowner']['sale']['saledate'], $homeowner_sale -> returnSaledate ($trusted_id));
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formTextDisabled ('homeowner|sale|name', $_HOAM_language ['field_names']['homeowner']['sale']['name'], $homeowner_sale -> returnName ($trusted_id));
				$HTML .= HOAM_formTextDisabled ('homeowner|sale|title', $_HOAM_language ['field_names']['homeowner']['sale']['title'], $homeowner_sale -> returnTitleCompany ($trusted_id));
				$HTML .= '<fieldset id="homeowner|sale|flags" class="advanced_options">';
				$flags = $homeowner_sale -> returnFlags ($trusted_id);
				$HTML .= HOAM_formCheckboxDisabled ('homeowner|sale|flag|payment_received', $_HOAM_language ['field_names']['homeowner']['sale']['flag']['payment_received'], 1, (int) $_SESSION['sale_information']['trusted_homeowner|sale|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['payment_received'], (int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['payment_received']);
				$HTML .= HOAM_formCheckboxDisabled ('homeowner|sale|flag|packet_ready', $_HOAM_language ['field_names']['homeowner']['sale']['flag']['packet_ready'], 1, (int) $_SESSION['sale_information']['trusted_homeowner|sale|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['packet_delivered'], (int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['packet_ready']);
				$HTML .= HOAM_formCheckboxDisabled ('homeowner|sale|flag|packet_delivered', $_HOAM_language ['field_names']['homeowner']['sale']['flag']['packet_delivered'], 1, (int) $_SESSION['sale_information']['trusted_homeowner|sale|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['packet_ready'], (int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['packet_delivered']);
				$HTML .= HOAM_formCheckboxDisabled ('homeowner|sale|flag|residence_sold', $_HOAM_language ['field_names']['homeowner']['sale']['flag']['residence_sold'], 1, (int) $_SESSION['sale_information']['trusted_homeowner|sale|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['residence_sold'], (int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['residence_sold']);
				$HTML .= HOAM_formCheckboxDisabled ('homeowner|sale|flag|residence_off_market', $_HOAM_language ['field_names']['homeowner']['sale']['flag']['residence_off_market'], 1, (int) $_SESSION['sale_information']['trusted_homeowner|sale|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['residence_off_market'], (int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['residence_off_market']);
				$HTML .= '</fieldset>';
				$HTML .= '<label for="homeowner|sale|attachments">' . $_HOAM_language ['field_names']['homeowner']['sale']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('homeowner|sale|attachments');
				$HTML .= HOAM_formDisplayError ('homeowner|sale|attachments');
				$HTML .= '</label>';
				$attachment_class = new HOAM_attachments ('attachments');
				$attachment_list = $attachment_class -> listWithOwner ($trusted_id, $_HOAM_setting ['attachment']['flags']['homeowner_sale']);
				if (!empty ($attachment_list)) {
					$year = date ('Y', strtotime ($attachment_class -> returnDateCreated ($attachment_list[0])));
					$HTML .= '<span style="font-weight: bold" field="year">&#x0229F; ' . $year . '</span>';
					$HTML .= '<div>';
					foreach ($attachment_list as $attachment) {
						if (mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4) == $year) {
						} else {
							$year = mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4);
							$HTML .= '</div>';
							$HTML .= '<span style="clear: left; display: block" field="year">&#x0229E; ' . $year . '</span>';
							$HTML .= '<div style="display: none">';
						}
						$HTML .= HOAM_attachmentReturnHTML ($attachment);
					}
					$HTML .= '</div>';
					$HTML .= '<br class="clear" />';
				}
				$HTML .= '<div>';
				$HTML .= '<input disabled="disabled" type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= HOAM_formTextareaDisabled ('homeowner|sale|comments', $_HOAM_language ['field_names']['homeowner']['sale']['comments'], $_SESSION['sale_information']['trusted_homeowner|sale|comments'], $homeowner_sale -> returnComments ($trusted_id), $_SESSION['current_user']['edit']['row'], $homeowner_sale -> returnColumnSize ('comments'), 0);
				$HTML .= '</div>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
				break;
			case 'edit' :
				$_HOAM_log -> add ('Loading /admin/homeowner/sale/edit/ page.', HOAM_MESSAGE_DEBUG);
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['homeowner']['sale']['edit'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminHomeownerSaleJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['homeowner']['sale']['edit'] . '</h1>';
				$trusted_id = $homeowner_sale -> superClean ($GLOBALS['global_url_array'][4], $homeowner_sale -> returnColumnSize ('id'));
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
				// OK, we're checking to see that the entry we're trying
				// to edit is the one that we have information on in the
				// sale_information variable. If the referrer doesn't
				// match this current page, then we know we came from
				// somewhere else on the site, and thus the information
				// is suspect. Also, trusted_id will /only/ be set if an
				// invoice was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ("http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['sale_information']['trusted_id']))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['sale_information'])) {
						unset ($_SESSION['sale_information']);
					}
				}

				$HTML .= '<form id="edit_sale" action="/hoam/scripts/homeowner/sale_edit_script.php" enctype="multipart/form-data" method="post">';
				$HTML .= '<input type="hidden" name="homeowner|sale|id" id="homeowner|sale|id" value="' . $trusted_id . '" />';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="sale" title="' . $_HOAM_language ['form_tabs']['homeowner']['information'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelect ('homeowner|sale|address', $_HOAM_language ['field_names']['homeowner']['address']);
				if (isset ($_SESSION['sale_information']['trusted_homeowner|sale|address'])) {
					$default_lot = $_SESSION['sale_information']['trusted_homeowner|sale|address'];
				} else {
					$default_lot = $homeowner_sale -> returnLotId ($trusted_id);
				}
				$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
				foreach ($lot_list as $lot) {
					$HTML .= '<option ';
					if ($lot['id'] == $default_lot) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
					$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($lot['id'])))) . ')</option>';
				}
				$HTML .= '</select>';
								$HTML .= HOAM_formDate ('homeowner|sale|saledate', $_HOAM_language ['field_names']['homeowner']['sale']['saledate'], $_SESSION['sale_information']['trusted_homeowner|sale|saledate'], $homeowner_sale -> returnSaledate ($trusted_id), 10, 1);
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formText ('homeowner|sale|name', $_HOAM_language ['field_names']['homeowner']['sale']['name'], $_SESSION['sale_information']['trusted_homeowner|sale|name'], $homeowner_sale -> returnName ($trusted_id), $homeowner_sale -> returnColumnSize ('name'), 0);
				$HTML .= HOAM_formText ('homeowner|sale|title', $_HOAM_language ['field_names']['homeowner']['sale']['title'], $_SESSION['sale_information']['trusted_homeowner|sale|title'], $homeowner_sale -> returnTitleCompany ($trusted_id), $homeowner_sale -> returnColumnSize ('title_company'), 0);
				$HTML .= '<label for="homeowner|sale|flags">' . $_HOAM_language ['field_names']['homeowner']['sale']['flags'];
				$HTML .= '</label>';
				$HTML .= '<fieldset id="homeowner|sale|flags" class="advanced_options">';
				$flags = $homeowner_sale -> returnFlags ($trusted_id);
				$HTML .= HOAM_formCheckbox ('homeowner|sale|flag|payment_received', $_HOAM_language ['field_names']['homeowner']['sale']['flag']['payment_received'], 1, (int) $_SESSION['sale_information']['trusted_homeowner|sale|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['payment_received'], (int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['payment_received']);
				$HTML .= HOAM_formCheckbox ('homeowner|sale|flag|packet_ready', $_HOAM_language ['field_names']['homeowner']['sale']['flag']['packet_ready'], 1, (int) $_SESSION['sale_information']['trusted_homeowner|sale|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['packet_delivered'], (int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['packet_ready']);
				$HTML .= HOAM_formCheckbox ('homeowner|sale|flag|packet_delivered', $_HOAM_language ['field_names']['homeowner']['sale']['flag']['packet_delivered'], 1, (int) $_SESSION['sale_information']['trusted_homeowner|sale|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['packet_ready'], (int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['packet_delivered']);
				$HTML .= HOAM_formCheckbox ('homeowner|sale|flag|residence_sold', $_HOAM_language ['field_names']['homeowner']['sale']['flag']['residence_sold'], 1, (int) $_SESSION['sale_information']['trusted_homeowner|sale|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['residence_sold'], (int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['residence_sold']);
				$HTML .= HOAM_formCheckbox ('homeowner|sale|flag|residence_off_market', $_HOAM_language ['field_names']['homeowner']['sale']['flag']['residence_off_market'], 1, (int) $_SESSION['sale_information']['trusted_homeowner|sale|flags'] & (int) $_HOAM_setting ['homeowner']['flags']['residence_off_market'], (int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['residence_off_market']);
				$HTML .= '</fieldset>';
				$HTML .= '<label for="homeowner|sale|attachments">' . $_HOAM_language ['field_names']['homeowner']['sale']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('homeowner|sale|attachments');
				$HTML .= HOAM_formDisplayError ('homeowner|sale|attachments');
				$HTML .= '</label>';
				$attachment_class = new HOAM_attachments ('attachments');
				$attachment_list = $attachment_class -> listWithOwner ($trusted_id, $_HOAM_setting ['attachment']['flags']['homeowner_sale']);
				if (!empty ($attachment_list)) {
					$year = date ('Y', strtotime ($attachment_class -> returnDateCreated ($attachment_list[0])));
					$HTML .= '<span style="font-weight: bold" field="year">&#x0229F; ' . $year . '</span>';
					$HTML .= '<div>';
					foreach ($attachment_list as $attachment) {
						if (mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4) == $year) {
						} else {
							$year = mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4);
							$HTML .= '</div>';
							$HTML .= '<span style="clear: left; display: block" field="year">&#x0229E; ' . $year . '</span>';
							$HTML .= '<div style="display: none">';
						}
						$HTML .= HOAM_attachmentReturnHTML ($attachment);
					}
					$HTML .= '</div>';
					$HTML .= '<br class="clear" />';
				}
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= HOAM_formTextarea ('homeowner|sale|comments', $_HOAM_language ['field_names']['homeowner']['sale']['comments'], $_SESSION['sale_information']['trusted_homeowner|sale|comments'], $homeowner_sale -> returnComments ($trusted_id), $_SESSION['current_user']['edit']['row'], $homeowner_sale -> returnColumnSize ('comments'), 0);
				$HTML .= '</div>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
				break;
			case 'list':
				$_HOAM_log -> add ('Loading /admin/homeowner/sale/list/ page.', HOAM_MESSAGE_DEBUG);
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['homeowner']['sale']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['homeowner']['sale']['list'] . '</h1>';
				$HTML .= '<table class="center">';
				$colgroup = '<colgroup>';
				$colgroup .= '<col width="15%" />';
				$colgroup .= '<col width="18%" />';
				$colgroup .= '<col span="2" width="15%" />';
				$colgroup .= '<col width="18%" />';
				$colgroup .= '<col width="10%" class="donotprint" />';
				$colgroup .= '<col width="9%" />';
				$colgroup .= '</colgroup>';
				$thead = '<thead><tr>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['address'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['name'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['sale']['requestdate'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['sale']['saledate'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['sale']['name'] . '</th>';
				$thead .= '<th class="donotprint">' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['flags'] . '</th>';
				$thead .= '</tr></thead>';
				$tbody = '<tbody id="homeowner_list">';
				//$homeowner_list = $homeowner_sale -> listAllId ();
				$homeowner_list = $homeowner_sale -> listSimpleQuery ('SELECT id FROM homeowner_sale ORDER BY datecreated DESC');
				if (is_array ($homeowner_list)) {
					foreach ($homeowner_list as $homeowner) {
						$tbody .= '<tr>';
						$tbody .= '<td>' . HOAM_returnAddress ($homeowner_sale -> returnLotId ($homeowner)) . '</td>';
						$tbody .= '<td>' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnOwner ($homeowner_sale -> returnDateCreated ($homeowner), $homeowner_sale -> returnLotId ($homeowner))))) . '</td>';
						$tbody .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_sale -> returnDateCreated ($homeowner))) . '</td>';
						if (date ($_HOAM_country ['date']['format_mysql']) > $homeowner_sale -> returnSaleDate ($homeowner)) {
							$tbody .= '<td class="error">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_sale -> returnSaleDate ($homeowner))) . '</td>';
						} elseif (date ($_HOAM_country ['date']['format_mysql']) == $homeowner_sale -> returnSaleDate ($homeowner)) {
							$tbody .= '<td class="warning">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_sale -> returnSaleDate ($homeowner))) . '</td>';
						} else {					
							$tbody .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_sale -> returnSaleDate ($homeowner))) . '</td>';
						}
						$tbody .= '<td>' . html_encode (stripslashes ($homeowner_sale -> returnName ($homeowner))) . '</td>';
						$tbody .= '<td class="donotprint">';
						$tbody .= '<small><a href="/admin/homeowner/edit/' . $homeowner_class -> returnOwner ($homeowner_sale -> returnDateCreated ($homeowner), $homeowner_sale -> returnLotId ($homeowner)) . '/">' . $_HOAM_language ['action']['homeowner']['edit'] . '</a></small>';
						$tbody .= ' &nbsp; ';
						$tbody .= '<small><a href="/admin/homeowner/sale/edit/' . $homeowner . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
						$tbody .= ' &nbsp; ';
						$tbody .= '<small><a href="/admin/homeowner/sale/delete/' . $homeowner . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
						$tbody .= '</td>';
						$tbody .= '<td>';
						$flags = $homeowner_sale -> returnFlags ($homeowner);
						if ((int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['payment_received']) {
							$tbody .= '<span class="complete cursor_help" title="' . $_HOAM_language ['field_names']['homeowner']['sale']['flag']['payment_received'] . '">$</span> ';
						}
						if ((int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['packet_ready']) {
							$tbody .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['homeowner']['sale']['flag']['packet_ready'] . '">R</span> ';
						}
						if ((int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['packet_delivered']) {
							$tbody .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['homeowner']['sale']['flag']['packet_delivered'] . '">D</span> ';
						}
						if ((int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['residence_sold']) {
							$tbody .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['homeowner']['sale']['flag']['residence_sold'] . '">S</span> ';
						}
						if ((int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['residence_off_market']) {
							$tbody .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['homeowner']['sale']['flag']['residence_off_market'] . '">O</span> ';
						}
						$tbody .= '</td>';
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="7">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody;
				$HTML .= '</table>';
				break;
			default:
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['homeowner']['sale']['sale'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['homeowner']['sale']['sale'] . '</h1>';
				$HTML .= '<table class="center">';
				$colgroup = '<colgroup>';
				$colgroup .= '<col width="15%" />';
				$colgroup .= '<col width="18%" />';
				$colgroup .= '<col span="2" width="15%" />';
				$colgroup .= '<col width="18%" />';
				$colgroup .= '<col width="10%" />';
				$colgroup .= '<col width="9%" />';
				$colgroup .= '</colgroup>';
				$thead = '<thead><tr>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['address'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['name'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['sale']['requestdate'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['sale']['saledate'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['homeowner']['sale']['name'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['flags'] . '</th>';
				$thead .= '</tr></thead>';
				$tbody = '<tbody id="homeowner_list">';
				$homeowner_list = $homeowner_sale -> listSimpleQuery ('SELECT id FROM homeowner_sale WHERE ' . 
					'~flags & ' . $_HOAM_setting ['homeowner']['flags']['residence_sold'] . ' AND ' . 
					'~flags & ' . $_HOAM_setting ['homeowner']['flags']['residence_off_market'] . ' ORDER BY datecreated DESC');
				if (is_array ($homeowner_list)) {
					foreach ($homeowner_list as $homeowner) {
						$tbody .= '<tr>';
						$tbody .= '<td>' . HOAM_returnAddress ($homeowner_sale -> returnLotId ($homeowner)) . '</td>';
						$tbody .= '<td>' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnOwner ($homeowner_sale -> returnDateCreated ($homeowner), $homeowner_sale -> returnLotId ($homeowner))))) . '</td>';
						$tbody .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_sale -> returnDateCreated ($homeowner))) . '</td>';
						if (date ($_HOAM_country ['date']['format_mysql']) > $homeowner_sale -> returnSaleDate ($homeowner)) {
							$tbody .= '<td class="error">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_sale -> returnSaleDate ($homeowner))) . '</td>';
						} elseif (date ($_HOAM_country ['date']['format_mysql']) == $homeowner_sale -> returnSaleDate ($homeowner)) {
							$tbody .= '<td class="warning">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_sale -> returnSaleDate ($homeowner))) . '</td>';
						} else {					
							$tbody .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_sale -> returnSaleDate ($homeowner))) . '</td>';
						}
						$tbody .= '<td>' . html_encode (stripslashes ($homeowner_sale -> returnName ($homeowner))) . '</td>';
						$tbody .= '<td>';
						$tbody .= '<small><a href="/admin/homeowner/edit/' . $homeowner_class -> returnOwner ($homeowner_sale -> returnDateCreated ($homeowner), $homeowner_sale -> returnLotId ($homeowner)) . '/">' . $_HOAM_language ['action']['homeowner']['edit'] . '</a></small>';
						$tbody .= ' &nbsp; ';
						$tbody .= '<small><a href="/admin/homeowner/sale/edit/' . $homeowner . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
						$tbody .= ' &nbsp; ';
						$tbody .= '<small><a href="/admin/homeowner/sale/delete/' . $homeowner . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
						$tbody .= '</td>';
						$tbody .= '<td>';
						$flags = $homeowner_sale -> returnFlags ($homeowner);
						if ((int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['payment_received']) {
							$tbody .= '<span class="complete cursor_help" title="' . $_HOAM_language ['field_names']['homeowner']['sale']['flag']['payment_received'] . '">$</span> ';
						}
						if ((int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['packet_ready']) {
							$tbody .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['homeowner']['sale']['flag']['packet_ready'] . '">R</span> ';
						}
						if ((int) $flags & (int) $_HOAM_setting ['homeowner']['flags']['packet_delivered']) {
							$tbody .= '<span class="cursor_help" title="' . $_HOAM_language ['field_names']['homeowner']['sale']['flag']['packet_delivered'] . '">D</span> ';
						}
						$tbody .= '</td>';
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="7">' . $_HOAM_language ['errors']['homeowner']['none-found'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody;
				$HTML .= '</table>';
				$HTML .= '<ul>';
				$HTML .= '<li><a href="/admin/homeowner/sale/add/">' . $_HOAM_language ['field_names']['homeowner']['sale']['add'] . '</a></li>';
				$HTML .= '<li><a href="/admin/homeowner/sale/list/">' . $_HOAM_language ['field_names']['homeowner']['sale']['list'] . '</a></li>';
				$HTML .= '</ul>';
				break;
		}
	} else {
		header ('Location: /error/403/');
		exit ();
	}
	return ($HTML);
}

function HOAM_adminInsurance ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$attachment_class = new HOAM_attachments ('attachments');
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$insurance_class = new HOAM_insurance ('insurance');
	$lot_class = new HOAM_lot ('lots');

	require_once (HOAM_PATH_FUNCTION . 'insurance.php');

	switch ($GLOBALS['global_url_array'][3]) {
		case ('add'):
			$_HOAM_log -> add ('Loading /admin/financial/insurance/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['insurance']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['insurance']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
				// OK, we're checking to see that the account we're trying to edit is the one that we
				// have information on in the account_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				// Also, trusted_id will /only/ be set if an account was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['insurance_information'])) {
						unset ($_SESSION['insurance_information']);
					}
				}

				$HTML .= '<form id="insurance_add" action="/hoam/scripts/insurance/edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="policy_information" title="' . $_HOAM_language ['form_tabs']['insurance']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelect ('insurance|vendor', $_HOAM_language ['field_names']['budget']['vendor']['name']);
				if (isset ($_SESSION ['insurance_information']['trusted_insurance|vendor'])) {
					$default_vendor = $_SESSION ['insurance_information']['trusted_insurance|vendor'];
				} else {
					$default_vendor = NULL;
				}
				$vendor_list = $budget_vendor_class -> listAllId ('name', 'ASC');
				foreach ($vendor_list as $vendor) {
					$HTML .= '<option ';
					if ($vendor == $default_vendor) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $vendor . '">' . stripslashes ($budget_vendor_class -> returnName ($vendor)) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelect ('insurance|type', $_HOAM_language ['field_names']['insurance']['policy']['type']);
				// There are 17 current policy types defined.
				for ($i = 1; $i < 18; $i++) {
					$HTML .= '<option ';
					if ($i == $_SESSION['insurance_information']['trusted_insurance|type']) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $i . '">' . $_HOAM_language ['field_names']['insurance']['policy'][$i] . '</option>';
				}
				$HTML .= '</select>';
				
				$HTML .= HOAM_formField ('insurance|policy-number', $_HOAM_language ['field_names']['insurance']['policy']['number'], $_SESSION['insurance_information']['trusted_insurance|policy-num'], '', $insurance_class -> returnColumnSize ('policy_num'), 1);
				$HTML .= HOAM_formSelect ('insurance|address', $_HOAM_language ['field_names']['lot']['address']);
				if (isset ($_SESSION['insurance_information']['trusted_insurance|address'])) {
					$default_lot = $_SESSION['insurance_information']['trusted_insurance|address'];
				} else {
					$default_lot = NULL;
				}
				if (isset ($_HOAM_setting ['lot']['common']['area']) OR ($default_lot == 'common_area')) {
					$HTML .= '<option ';
					if ($default_lot == 'common_area') {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="common_area">' . $_HOAM_language ['field_names']['lot']['common']['area'] . '</option>';
				}
				if (isset ($_HOAM_setting ['lot']['common']['clubhouse']) OR ($default_lot == 'common_clubhouse')) {
					$HTML .= '<option ';
					if ($default_lot == 'common_clubhouse') {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="common_clubhouse">' . $_HOAM_language ['field_names']['lot']['common']['clubhouse'] . '</option>';
				}
				if (isset ($_HOAM_setting ['lot']['common']['pool']) OR ($default_lot == 'common_pool')) {
					$HTML .= '<option ';
					if ($default_lot == 'common_pool') {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="common_pool">' . $_HOAM_language ['field_names']['lot']['common']['pool'] . '</option>';
				}
				$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
				foreach ($lot_list as $lot) {
					$HTML .= '<option ';
					if ($lot['id'] == $default_lot) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
					$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($lot['id'])))) . ')';
					$HTML .= '</option>';
					}
				$HTML .= '</select>';
				$HTML .= HOAM_formDate ('insurance|renewal-date', $_HOAM_language ['field_names']['insurance']['renewal-date'], $_SESSION['insurance_information']['trusted_insurance|renewal-date'], HOAM_date ($_HOAM_country ['date']['format_reports']));
				$HTML .= HOAM_formDate ('insurance|expiration-date', $_HOAM_language ['field_names']['insurance']['expiration-date'], $_SESSION['insurance_information']['trusted_insurance|expiration-date'], HOAM_date ($_HOAM_country ['date']['format_reports'], mktime (0, 0, 0, date('m'), date('d'), date('Y') + 1)));
				
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= '<label for="insurance|attachment">' . $_HOAM_language ['field_names']['insurance']['attachment'];
				$HTML .= HOAM_formDisplayHelp ('insurance|attachments');
				$HTML .= HOAM_formDisplayError ('insurance|attachment');
				$HTML .= '</label>';
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= HOAM_formTextarea ('insurance|comments', $_HOAM_language ['field_names']['insurance']['comments'], $_SESSION['insurance_information']['trusted_insurance|comments'], '', $_SESSION['current_user']['edit']['row'], $insurance_class -> returnColumnSize ('comments'));
				$HTML .= '</div>';
				$HTML .= '</div>'; // End New Account Tab
				
				$HTML .= '<div class="tabbertab" id="insurance-policy-list" title="' . $_HOAM_language ['form_tabs']['insurance']['list'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_insuranceList (); 
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Account List Tab

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('delete'):
			$_HOAM_log -> add ('Loading /admin/financial/insurance/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_id = $insurance_class -> superClean ($GLOBALS['global_url_array'][4], $insurance_class -> returnColumnSize ('id'));
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['insurance']['edit'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminInsuranceJS');
					$HTML = '<h1>' . $_HOAM_language ['field_names']['insurance']['delete'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
					// OK, we're checking to see that the account we're trying to edit is the one that we
					// have information on in the account_information variable.
					// If the referrer doesn't match this current page, then we know we came from
					// somewhere else on the site, and thus the information is suspect.
					// Also, trusted_id will /only/ be set if an account was being modified, not added.
					if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
						if (isset ($_SESSION['error_class_item'])) {
							unset ($_SESSION['error_class_item']);
						}
						if (isset ($_SESSION['insurance_information'])) {
							unset ($_SESSION['insurance_information']);
						}
					}

					$HTML .= '<form id="insurance_delete" action="/hoam/scripts/insurance/edit_script.php" enctype="multipart/form-data" method="post">';
					$HTML .= '<input type="hidden" name="insurance|id" id="insurance|id" value="' . $trusted_id . '" />';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['delete'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="policy_information" title="' . $_HOAM_language ['form_tabs']['insurance']['information'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formSelectDisabled ('insurance|vendor', $_HOAM_language ['field_names']['budget']['vendor']['name']);
					if (isset ($_SESSION ['insurance_information']['trusted_insurance|vendor'])) {
						$default_vendor = $_SESSION ['insurance_information']['trusted_insurance|vendor'];
					} else {
						$default_vendor = $insurance_class -> returnVendorId ($trusted_id);
					}
					$vendor_list = $budget_vendor_class -> listAllId ('name', 'ASC');
					foreach ($vendor_list as $vendor) {
						$HTML .= '<option ';
						if ($vendor == $default_vendor) {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="' . $vendor . '">' . stripslashes ($budget_vendor_class -> returnName ($vendor)) . '</option>';
					}
					$HTML .= '</select>';
					$HTML .= HOAM_formSelectDisabled ('insurance|type', $_HOAM_language ['field_names']['insurance']['policy']['type']);
					// There are 17 current policy types defined.
					for ($i = 1; $i < 18; $i++) {
						$HTML .= '<option ';
						if (($i == $_SESSION['insurance_information']['trusted_insurance|type']) OR ($i == $insurance_class -> returnPolicyType ($trusted_id))) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $i . '">' . $_HOAM_language ['field_names']['insurance']['policy'][$i] . '</option>';
					}
					$HTML .= '</select>';
				
					$HTML .= HOAM_formFieldDisabled ('insurance|policy-number', $_HOAM_language ['field_names']['insurance']['policy']['number'], $insurance_class -> returnPolicyNum ($trusted_id), $insurance_class -> returnColumnSize ('policy_num'), 1);
					$HTML .= HOAM_formSelectDisabled ('insurance|address', $_HOAM_language ['field_names']['lot']['address']);
					if (isset ($_SESSION['insurance_information']['trusted_insurance|address'])) {
						$default_lot = $_SESSION['insurance_information']['trusted_insurance|address'];
					} else {
						$default_lot = $insurance_class -> returnLotId ($trusted_id);
					}
					if (isset ($_HOAM_setting ['lot']['common']['area']) OR ($default_lot == 'common_area')) {
						$HTML .= '<option ';
						if ($default_lot == 'common_area') {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="common_area">' . $_HOAM_language ['field_names']['lot']['common']['area'] . '</option>';
					}
					if (isset ($_HOAM_setting ['lot']['common']['clubhouse']) OR ($default_lot == 'common_clubhouse')) {
						$HTML .= '<option ';
						if ($default_lot == 'common_clubhouse') {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="common_clubhouse">' . $_HOAM_language ['field_names']['lot']['common']['clubhouse'] . '</option>';
					}
					if (isset ($_HOAM_setting ['lot']['common']['pool']) OR ($default_lot == 'common_pool')) {
						$HTML .= '<option ';
						if ($default_lot == 'common_pool') {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="common_pool">' . $_HOAM_language ['field_names']['lot']['common']['pool'] . '</option>';
					}
					$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
					foreach ($lot_list as $lot) {
						$HTML .= '<option ';
						if ($lot['id'] == $default_lot) {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
						$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($lot['id'])))) . ')';
						$HTML .= '</option>';
					}
					$HTML .= '</select>';
					$HTML .= HOAM_formDateDisabled ('insurance|renewal-date', $_HOAM_language ['field_names']['insurance']['renewal-date'], HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($insurance_class -> returnRenewalDate ($trusted_id))));
					$HTML .= HOAM_formDateDisabled ('insurance|expiration-date', $_HOAM_language ['field_names']['insurance']['expiration-date'], HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($insurance_class -> returnExpirationDate ($trusted_id))));
					$HTML .= '<br />';
					$HTML .= HOAM_formCheckboxDisabled ('insurance|replaced', $_HOAM_language ['field_names']['insurance']['flags']['replaced'], 1, $insurance_class -> isBitSet ($trusted_id, $_HOAM_setting ['budget']['insurance']['flags']['replaced']));
					$HTML .= '<br />';

					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= '<label for="insurance|attachment">' . $_HOAM_language ['field_names']['insurance']['attachment'];
					$HTML .= HOAM_formDisplayHelp ('insurance|attachments');
					$HTML .= HOAM_formDisplayError ('insurance|attachment');
					$HTML .= '</label>';
					$attachments = new HOAM_attachments ('attachments');
					$attachment_list = $attachments -> listWithOwner ($trusted_id, $_HOAM_setting ['attachment']['flags']['insurance']);
					foreach ($attachment_list as $attachment) {
						$attachment_html .= HOAM_attachmentReturnHTML ($attachment);
					}
					if ($attachment_html) {
						$HTML .= $attachment_html;
						$HTML .= '<br class="clear" />';
						unset ($attachment_html);
					}
					$HTML .= HOAM_formTextareaDisabled ('insurance|comments', $_HOAM_language ['field_names']['insurance']['comments'], $insurance_class -> returnComments ($trusted_id), $_SESSION['current_user']['edit']['row'], $insurance_class -> returnColumnSize ('comments'));
					$HTML .= '</div>';
					$HTML .= '</div>'; // End New Account Tab

					$HTML .= '<div class="tabbertab" id="policy_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
					$HTML .= '</div>'; // End History tab
									
					$HTML .= '<div class="tabbertab" id="insurance-policy-list" title="' . $_HOAM_language ['form_tabs']['insurance']['list'] . '">';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_insuranceList (); 
					$HTML .= '</div>';
					$HTML .= '</div>'; // End Account List Tab

					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
				} else {
					header ('Location: /admin/financial/insurance/list/');					
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('edit'):
			$_HOAM_log -> add ('Loading /admin/financial/insurance/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_id = $insurance_class -> superClean ($GLOBALS['global_url_array'][4], $insurance_class -> returnColumnSize ('id'));
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['insurance']['edit'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminInsuranceJS');
					$HTML = '<h1>' . $_HOAM_language ['field_names']['insurance']['edit'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
					// OK, we're checking to see that the account we're trying to edit is the one that we
					// have information on in the account_information variable.
					// If the referrer doesn't match this current page, then we know we came from
					// somewhere else on the site, and thus the information is suspect.
					// Also, trusted_id will /only/ be set if an account was being modified, not added.
					if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
						if (isset ($_SESSION['error_class_item'])) {
							unset ($_SESSION['error_class_item']);
						}
						if (isset ($_SESSION['insurance_information'])) {
							unset ($_SESSION['insurance_information']);
						}
					}

					$HTML .= '<form id="insurance_edit" action="/hoam/scripts/insurance/edit_script.php" enctype="multipart/form-data" method="post">';
					$HTML .= '<input type="hidden" name="insurance|id" id="insurance|id" value="' . $trusted_id . '" />';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
					$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="policy_information" title="' . $_HOAM_language ['form_tabs']['insurance']['information'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formSelect ('insurance|vendor', $_HOAM_language ['field_names']['budget']['vendor']['name']);
					if (isset ($_SESSION ['insurance_information']['trusted_insurance|vendor'])) {
						$default_vendor = $_SESSION ['insurance_information']['trusted_insurance|vendor'];
					} else {
						$default_vendor = $insurance_class -> returnVendorId ($trusted_id);
					}
					$vendor_list = $budget_vendor_class -> listAllId ('name', 'ASC');
					foreach ($vendor_list as $vendor) {
						$HTML .= '<option ';
						if ($vendor == $default_vendor) {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="' . $vendor . '">' . stripslashes ($budget_vendor_class -> returnName ($vendor)) . '</option>';
					}
					$HTML .= '</select>';
					$HTML .= HOAM_formSelect ('insurance|type', $_HOAM_language ['field_names']['insurance']['policy']['type']);
					// There are 17 current policy types defined.
					for ($i = 1; $i < 18; $i++) {
						$HTML .= '<option ';
						if (($i == $_SESSION['insurance_information']['trusted_insurance|type']) OR ($i == $insurance_class -> returnPolicyType ($trusted_id))) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $i . '">' . $_HOAM_language ['field_names']['insurance']['policy'][$i] . '</option>';
					}
					$HTML .= '</select>';
				
					$HTML .= HOAM_formField ('insurance|policy-number', $_HOAM_language ['field_names']['insurance']['policy']['number'], $_SESSION['insurance_information']['trusted_insurance|policy-num'], $insurance_class -> returnPolicyNum ($trusted_id), $insurance_class -> returnColumnSize ('policy_num'), 1);
					$HTML .= HOAM_formSelect ('insurance|address', $_HOAM_language ['field_names']['lot']['address']);
					if (isset ($_SESSION['insurance_information']['trusted_insurance|address'])) {
						$default_lot = $_SESSION['insurance_information']['trusted_insurance|address'];
					} else {
						$default_lot = $insurance_class -> returnLotId ($trusted_id);
					}
					if (isset ($_HOAM_setting ['lot']['common']['area']) OR ($default_lot == 'common_area')) {
						$HTML .= '<option ';
						if ($default_lot == 'common_area') {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="common_area">' . $_HOAM_language ['field_names']['lot']['common']['area'] . '</option>';
					}
					if (isset ($_HOAM_setting ['lot']['common']['clubhouse']) OR ($default_lot == 'common_clubhouse')) {
						$HTML .= '<option ';
						if ($default_lot == 'common_clubhouse') {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="common_clubhouse">' . $_HOAM_language ['field_names']['lot']['common']['clubhouse'] . '</option>';
					}
					if (isset ($_HOAM_setting ['lot']['common']['pool']) OR ($default_lot == 'common_pool')) {
						$HTML .= '<option ';
						if ($default_lot == 'common_pool') {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="common_pool">' . $_HOAM_language ['field_names']['lot']['common']['pool'] . '</option>';
					}
					$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
					foreach ($lot_list as $lot) {
						$HTML .= '<option ';
						if ($lot['id'] == $default_lot) {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
						$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($lot['id'])))) . ')';
						$HTML .= '</option>';
					}
					$HTML .= '</select>';
					$HTML .= HOAM_formDate ('insurance|renewal-date', $_HOAM_language ['field_names']['insurance']['renewal-date'], $_SESSION['insurance_information']['trusted_insurance|renewal-date'], HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($insurance_class -> returnRenewalDate ($trusted_id))));
					$HTML .= HOAM_formDate ('insurance|expiration-date', $_HOAM_language ['field_names']['insurance']['expiration-date'], $_SESSION['insurance_information']['trusted_insurance|expiration-date'], HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($insurance_class -> returnExpirationDate ($trusted_id))));
					if ((isset ($_SESSION['error_class_item'])) AND ($_SESSION['error_class_item'] -> ShowCategory ('insurance|expiration-date') != '')) {
						$HTML .= HOAM_formCheckbox ('insurance|expiration-date|ignore', $_HOAM_language ['errors']['generic']['ignore'], 1, 0, 0);
					}
					$HTML .= '<br />';
					$HTML .= HOAM_formCheckbox ('insurance|replaced', $_HOAM_language ['field_names']['insurance']['flags']['replaced'], 1, $_SESSION['insurance_information']['trusted_insurance|replaced'], $insurance_class -> isBitSet ($trusted_id, $_HOAM_setting ['budget']['insurance']['flags']['replaced']));
					$HTML .= '<br />';

					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= '<label for="insurance|attachment">' . $_HOAM_language ['field_names']['insurance']['attachment'];
					$HTML .= HOAM_formDisplayHelp ('insurance|attachments');
					$HTML .= HOAM_formDisplayError ('insurance|attachment');
					$HTML .= '</label>';
					$attachments = new HOAM_attachments ('attachments');
					$attachment_list = $attachments -> listWithOwner ($trusted_id, $_HOAM_setting ['attachment']['flags']['insurance']);
					foreach ($attachment_list as $attachment) {
						$attachment_html .= HOAM_attachmentReturnHTML ($attachment);
					}
					if ($attachment_html) {
						$HTML .= $attachment_html;
						$HTML .= '<br class="clear" />';
						unset ($attachment_html);
					}
					$HTML .= '<div>';
					$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
					$HTML .= '</div>';
					$HTML .= HOAM_formTextarea ('insurance|comments', $_HOAM_language ['field_names']['insurance']['comments'], $_SESSION['insurance_information']['trusted_insurance|comments'], $insurance_class -> returnComments ($trusted_id), $_SESSION['current_user']['edit']['row'], $insurance_class -> returnColumnSize ('comments'));
					$HTML .= '</div>';
					$HTML .= '</div>'; // End New Account Tab

					$HTML .= '<div class="tabbertab" id="policy_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
					$HTML .= '</div>'; // End History tab
									
					$HTML .= '<div class="tabbertab" id="insurance-policy-list" title="' . $_HOAM_language ['form_tabs']['insurance']['list'] . '">';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_insuranceList (); 
					$HTML .= '</div>';
					$HTML .= '</div>'; // End Account List Tab

					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
				} else {
					header ('Location: /admin/financial/insurance/list/');
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			$_HOAM_log -> add ('Loading /admin/financial/insurance/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['insurance']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['insurance']['list'] . '</h1>';
				$HTML .= HOAM_insuranceList (TRUE);
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][3])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['insurance'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['insurance'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/user.png" alt="' . $_HOAM_language ['field_names']['admin']['insurance'] . '" class="LHS" /><a href="/admin/financial/insurance/">' . $_HOAM_language ['field_names']['admin']['insurance'] . '</a>';
					$HTML .= '<ul>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/insurance/add/">' . $_HOAM_language ['field_names']['insurance']['add'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/financial/insurance/list/">' . $_HOAM_language ['field_names']['insurance']['list'] . '</a></li>';
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminLot ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');

	switch ($GLOBALS['global_url_array'][2]) {
		case ('add'):
			$_HOAM_log -> add ('Loading /admin/lot/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['lot']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminLotJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['lot']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				// OK, we're checking to see that the homeowner we're trying to edit is the one that we
				// have information on in the homeowner_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				// Also, trusted_id will /only/ be set if a homeowner was being modified, not added.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['lot_information'])) {
						unset ($_SESSION['lot_information']);
					}
				}

				$HTML .= '<form id="lot_add" action="/hoam/scripts/lot/edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['lot']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="lot_information" title="' . $_HOAM_language ['form_tabs']['lot']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelect ('lot|street', $_HOAM_language ['field_names']['lot']['street']);
				$street_list = $lot_class -> listStreets ();
				if (isset ($_SESSION['lot_information']['trusted_lot|street'])) {
					$default_street = $_SESSION['lot_information']['trusted_lot|street'];
				} else {
					$default_street = NULL;
				}
				foreach ($street_list as $street) {
					$HTML .= '<option ';
					if ($default_street == $street) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $street . '">' . $street . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('lot|street-other', $_HOAM_language ['field_names']['lot']['street-other'], $_SESSION['lot_information']['trusted_lot|street-other'], '', $lot_class -> returnColumnSize ('street'), $_HOAM_setting ['lot']['minimum_street_length']);
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formField ('lot|address', $_HOAM_language ['field_names']['lot']['address'], $_SESSION['lot_information']['trusted_lot|address'], '', $lot_class -> returnColumnSize ('address'), $_HOAM_setting ['lot']['minimum_address_length']);
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formField ('lot|building', $_HOAM_language ['field_names']['lot']['building'], $_SESSION['lot_information']['trusted_lot|building'], '', $lot_class -> returnColumnSize ('building'), $_HOAM_setting ['lot']['minimum_building_length']);
				$HTML .= HOAM_formField ('lot|suite', $_HOAM_language ['field_names']['lot']['suite'], $_SESSION['lot_information']['trusted_lot|suite'], '', $lot_class -> returnColumnSize ('suite'), $_HOAM_setting ['lot']['minimum_suite_length']);
				$HTML .= HOAM_formField ('lot|sqft', $_HOAM_language ['field_names']['lot']['sqft'], $_SESSION['lot_information']['trusted_lot|sqft'], '', $lot_class -> returnColumnSize ('sqft'), $_HOAM_setting ['lot']['minimum_sqft_size']);
				$HTML .= '<label for="lot|view|front">' . $_HOAM_language ['field_names']['lot']['view']['front'];
				$HTML .= HOAM_formDisplayHelp ('lot|view|front');
				$HTML .= HOAM_formDisplayError ('lot|view|front');
				$HTML .= '</label>';
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="lot|view|front" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= '<label for="lot|view|front">' . $_HOAM_language ['field_names']['lot']['view']['rear'];
				$HTML .= HOAM_formDisplayHelp ('lot|view|rear');
				$HTML .= HOAM_formDisplayError ('lot|view|rear');
				$HTML .= '</label>';
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="lot|view|rear" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= '<label for="lot|attachments">' . $_HOAM_language ['field_names']['lot']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('lot|attachments');
				$HTML .= HOAM_formDisplayError ('lot|attachments');
				$HTML .= '</label>';
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= HOAM_formTextarea ('lot|comments', $_HOAM_language ['field_names']['lot']['comments'], $_SESSION['lot_information']['trusted_lot|comments'], '', $_SESSION['current_user']['edit']['row'], $lot_class -> returnColumnSize ('comment'), 0);
				$HTML .= '</div>';
				$HTML .= '</div>'; // End New lot tab

				$HTML .= '<div class="tabbertab" id="lot_plat" title="' . $_HOAM_language ['form_tabs']['lot']['plat'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formField ('lot|block', $_HOAM_language ['field_names']['lot']['block'], $_SESSION['lot_information']['trusted_lot|block'], '', $lot_class -> returnColumnSize ('block'), $_HOAM_setting ['lot']['minimum_block_length']);
				$HTML .= HOAM_formField ('lot|lot', $_HOAM_language ['field_names']['lot']['lot'], $_SESSION['lot_information']['trusted_lot|lot'], '', $lot_class -> returnColumnSize ('lot'), $_HOAM_setting ['lot']['minimum_lot_length']);
				$HTML .= HOAM_formField ('lot|plat', $_HOAM_language ['field_names']['lot']['plat'], $_SESSION['lot_information']['trusted_lot|plat'], '', $lot_class -> returnColumnSize ('plat'), $_HOAM_setting ['lot']['minimum_plat_length']);
				$HTML .= HOAM_formField ('lot|latitude', $_HOAM_language ['field_names']['lot']['latitude'], $_SESSION['lot_information']['trusted_lot|latitude'], '', $lot_class -> returnColumnSize ('latitude'), $_HOAM_setting ['lot']['minimum_latitude_length']);
				$HTML .= HOAM_formField ('lot|longitude', $_HOAM_language ['field_names']['lot']['longitude'], $_SESSION['lot_information']['trusted_lot|longitude'], '', $lot_class -> returnColumnSize ('longitude'), $_HOAM_setting ['lot']['minimum_longitude_length']);
				$HTML .= '</div>';
				$HTML .= '</div>'; // End lot Plat tab

				$HTML .= '<div class="tabbertab" id="lot_list" title="' . $_HOAM_language ['form_tabs']['lot']['list'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formSelect ('lot|list_existing', $_HOAM_language ['field_names']['lot']['street']);
				$street_list = $lot_class -> listStreets ();
				foreach ($street_list as $street) {
					$HTML .= '<option value="' . $street . '">' . $street . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '<br /><br />';
				$HTML .= '<table>';
				$HTML .= '<colgroup>';
				$HTML .= '<col span="6" width="10%" />';
				$HTML .= '<col width="15%" />';
				$HTML .= '<col width="15%" />';
				$HTML .= '</colgroup>';
				$HTML .= '<thead><tr>';
				$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['address'] . '</th>';
				$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['building'] . '</th>';
				$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['suite'] . '</th>';
				$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['block'] . '</th>';
				$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['lot'] . '</th>';
				$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['plat'] . '</th>';
				$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['latitude'] . '</th>';
				$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['longitude'] . '</th>';
				$HTML .= '</tr></thead>';
				$HTML .= '<tbody id="existing_lot_list"></tbody>';
				$HTML .= '</table>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End lot List Tab

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'delete' :
			$_HOAM_log -> add ('Loading /admin/lot/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_lot_id = $lot_class -> superClean ($GLOBALS['global_url_array'][3], $lot_class -> returnColumnSize ('id'));
					if ($lot_class -> idExists ($trusted_lot_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['lot']['delete'] . ' (' . HOAM_returnAddress ($trusted_lot_id) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminLotJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['lot']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'attachments.php');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the homeowner we're trying to edit is the one that we
						// have information on in the homeowner_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if a homeowner was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['lot_information']['trusted_lot|id']) AND ($trusted_lot_id != $_SESSION['lot_information']['trusted_lot|id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['lot_information'])) {
								unset ($_SESSION['lot_information']);
							}
						}

						$HTML .= '<form id="lot_edit" action="/hoam/scripts/lot/edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="lot|id" id="lot|id" value="' . $trusted_lot_id . '" />';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['lot']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="lot_information" title="' . $_HOAM_language ['form_tabs']['lot']['add'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formSelectDisabled ('lot|street', $_HOAM_language ['field_names']['lot']['street']);
						$street_list = $lot_class -> listStreets ();
						if (isset ($_SESSION['lot_information']['trusted_street'])) {
							$default_street = $lot_class -> returnStreet ($_SESSION['lot_information']['trusted_lot|street']);
						} else {
							$default_street = $lot_class -> returnStreet ($trusted_lot_id);
						}
						foreach ($street_list as $street) {
							$HTML .= '<option ';
							if ($default_street == $street) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $street . '">' . $street . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formFieldDisabled ('lot|address', $_HOAM_language ['field_names']['lot']['address'], $lot_class -> returnAddress ($trusted_lot_id), $lot_class -> returnColumnSize ('address'));
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formFieldDisabled ('lot|building', $_HOAM_language ['field_names']['lot']['building'], $lot_class -> returnBuilding ($trusted_lot_id), $lot_class -> returnColumnSize ('building'));
						$HTML .= HOAM_formFieldDisabled ('lot|suite', $_HOAM_language ['field_names']['lot']['suite'], $lot_class -> returnSuite ($trusted_lot_id), $lot_class -> returnColumnSize ('suite'));
						$HTML .= HOAM_formFieldDisabled ('lot|sqft', $_HOAM_language ['field_names']['lot']['sqft'], $lot_class -> returnSqft ($trusted_lot_id), $lot_class -> returnColumnSize ('sqft'));
						$HTML .= '<label for="lot|view|front">' . $_HOAM_language ['field_names']['lot']['view']['front'];
						$HTML .= HOAM_formDisplayHelp ('lot|view|front');
						$HTML .= HOAM_formDisplayError ('lot|view|front');
						$HTML .= '</label>';
						$HTML .= '<div>';
						$HTML .= '<input type="button" id="lot|view|front" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= '<label for="lot|view|front">' . $_HOAM_language ['field_names']['lot']['view']['rear'];
						$HTML .= HOAM_formDisplayHelp ('lot|view|rear');
						$HTML .= HOAM_formDisplayError ('lot|view|rear');
						$HTML .= '</label>';
						$HTML .= '<div>';
						$HTML .= '<input type="button" id="lot|view|rear" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= '<label for="lot|attachments">' . $_HOAM_language ['field_names']['lot']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('lot|attachments');
						$HTML .= HOAM_formDisplayError ('lot|attachments');
						$HTML .= '</label>';
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_lot_id, $_HOAM_setting ['attachment']['flags']['lot']);
						$HTML .= '<div>';
						if (!empty ($attachment_list)) {
							foreach ($attachment_list as $attachment) {
								$HTML .= HOAM_attachmentReturnHTML ($attachment);
							}
							$HTML .= '<br /><br /><br />';
						}
						$HTML .= '<input disabled="disabled" type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= HOAM_formTextareaDisabled ('lot|comments', $_HOAM_language ['field_names']['lot']['comments'], $lot_class -> returnComments ($trusted_lot_id), $_SESSION['current_user']['edit']['row'], $lot_class -> returnColumnSize ('comment'), 0);
						$HTML .= '</div>';
						$HTML .= '</div>'; // End New lot tab

						$HTML .= '<div class="tabbertab" id="lot_plat" title="' . $_HOAM_language ['form_tabs']['lot']['plat'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formFieldDisabled ('lot|block', $_HOAM_language ['field_names']['lot']['block'], $lot_class -> returnBlock ($trusted_lot_id), $lot_class -> returnColumnSize ('block'));
						$HTML .= HOAM_formFieldDisabled ('lot|lot', $_HOAM_language ['field_names']['lot']['lot'], $lot_class -> returnLot ($trusted_lot_id), $lot_class -> returnColumnSize ('lot'));
						$HTML .= HOAM_formFieldDisabled ('lot|plat', $_HOAM_language ['field_names']['lot']['plat'], $lot_class -> returnPlat ($trusted_lot_id), $lot_class -> returnColumnSize ('plat'));
						$HTML .= HOAM_formFieldDisabled ('lot|latitude', $_HOAM_language ['field_names']['lot']['latitude'], $lot_class -> returnLatitude ($trusted_lot_id), $lot_class -> returnColumnSize ('latitude'));
						$HTML .= HOAM_formFieldDisabled ('lot|longitude', $_HOAM_language ['field_names']['lot']['longitude'], $lot_class -> returnLongitude ($trusted_lot_id), $lot_class -> returnColumnSize ('longitude'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End lot Plat tab

						$HTML .= '<div class="tabbertab" id="lot_list" title="' . $_HOAM_language ['form_tabs']['lot']['list'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formSelectDisabled ('lot|list_existing', $_HOAM_language ['field_names']['lot']['street']);
						$street_list = $lot_class -> listStreets ();
						foreach ($street_list as $street) {
							$HTML .= '<option value="' . $street . '">' . $street . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '<br /><br />';
						$HTML .= '<table>';
						$HTML .= '<colgroup>';
						$HTML .= '<col span="6" width="10%" />';
						$HTML .= '<col width="15%" />';
						$HTML .= '<col width="15%" />';
						$HTML .= '</colgroup>';
						$HTML .= '<thead><tr>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['address'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['building'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['suite'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['block'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['lot'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['plat'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['latitude'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['longitude'] . '</th>';
						$HTML .= '</tr></thead>';
						$HTML .= '<tbody id="existing_lot_list"></tbody>';
						$HTML .= '</table>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End lot List Tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/lot/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'edit' :
			$_HOAM_log -> add ('Loading /admin/lot/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_lot_id = $lot_class -> superClean ($GLOBALS['global_url_array'][3], $lot_class -> returnColumnSize ('id'));
					if ($lot_class -> idExists ($trusted_lot_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['lot']['edit'] . ' (' . HOAM_returnAddress ($trusted_lot_id) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminLotJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['lot']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'attachments.php');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the homeowner we're trying to edit is the one that we
						// have information on in the homeowner_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						// Also, trusted_id will /only/ be set if a homeowner was being modified, not added.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']))) OR (isset ($_SESSION['lot_information']['trusted_lot|id']) AND ($trusted_lot_id != $_SESSION['lot_information']['trusted_lot|id']))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['lot_information'])) {
								unset ($_SESSION['lot_information']);
							}
						}

						$HTML .= '<form id="lot_edit" action="/hoam/scripts/lot/edit_script.php" enctype="multipart/form-data" method="post">';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<input type="hidden" name="lot|id" id="lot|id" value="' . $trusted_lot_id . '" />';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="lot_information" title="' . $_HOAM_language ['form_tabs']['lot']['add'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formSelect ('lot|street', $_HOAM_language ['field_names']['lot']['street']);
						$street_list = $lot_class -> listStreets ();
						if (isset ($_SESSION['lot_information']['trusted_street'])) {
							$default_street = $lot_class -> returnStreet ($_SESSION['lot_information']['trusted_lot|street']);
						} else {
							$default_street = $lot_class -> returnStreet ($trusted_lot_id);
						}
						foreach ($street_list as $street) {
							$HTML .= '<option ';
							if ($default_street == $street) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $street . '">' . $street . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formFieldEnable ('lot|street-other', $_HOAM_language ['field_names']['lot']['street-other'], $_SESSION['lot_information']['trusted_lot|street-other'], '', $lot_class -> returnColumnSize ('street'), $_HOAM_setting ['lot']['minimum_street_length']);
						$HTML .= '</fieldset>';
						$HTML .= HOAM_formField ('lot|address', $_HOAM_language ['field_names']['lot']['address'], $_SESSION['lot_information']['trusted_lot|address'], $lot_class -> returnAddress ($trusted_lot_id), $lot_class -> returnColumnSize ('address'), $_HOAM_setting ['lot']['minimum_address_length']);
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formField ('lot|building', $_HOAM_language ['field_names']['lot']['building'], $_SESSION['lot_information']['trusted_lot|building'], $lot_class -> returnBuilding ($trusted_lot_id), $lot_class -> returnColumnSize ('building'), $_HOAM_setting ['lot']['minimum_building_length']);
						$HTML .= HOAM_formField ('lot|suite', $_HOAM_language ['field_names']['lot']['suite'], $_SESSION['lot_information']['trusted_lot|suite'], $lot_class -> returnSuite ($trusted_lot_id), $lot_class -> returnColumnSize ('suite'), $_HOAM_setting ['lot']['minimum_suite_length']);
						$HTML .= HOAM_formField ('lot|sqft', $_HOAM_language ['field_names']['lot']['sqft'], $_SESSION['lot_information']['trusted_lot|sqft'], $lot_class -> returnSqft ($trusted_lot_id), $lot_class -> returnColumnSize ('sqft'), $_HOAM_setting ['lot']['minimum_sqft_size']);
						$HTML .= '<label for="lot|view|front">' . $_HOAM_language ['field_names']['lot']['view']['front'];
						$HTML .= HOAM_formDisplayHelp ('lot|view|front');
						$HTML .= HOAM_formDisplayError ('lot|view|front');
						$HTML .= '</label>';
						$HTML .= '<div>';
						$HTML .= '<input type="button" id="lot|view|front" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= '<label for="lot|view|front">' . $_HOAM_language ['field_names']['lot']['view']['rear'];
						$HTML .= HOAM_formDisplayHelp ('lot|view|rear');
						$HTML .= HOAM_formDisplayError ('lot|view|rear');
						$HTML .= '</label>';
						$HTML .= '<div>';
						$HTML .= '<input type="button" id="lot|view|rear" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= '<label for="lot|attachments">' . $_HOAM_language ['field_names']['lot']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('lot|attachments');
						$HTML .= HOAM_formDisplayError ('lot|attachments');
						$HTML .= '</label>';
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_lot_id, $_HOAM_setting ['attachment']['flags']['lot']);
						$HTML .= '<div>';
						if (!empty ($attachment_list)) {
							foreach ($attachment_list as $attachment) {
								$HTML .= HOAM_attachmentReturnHTML ($attachment);
							}
							$HTML .= '<br /><br /><br />';
						}
						$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= HOAM_formTextarea ('lot|comments', $_HOAM_language ['field_names']['lot']['comments'], $_SESSION['lot_information']['trusted_lot|comments'], $lot_class -> returnComments ($trusted_lot_id), $_SESSION['current_user']['edit']['row'], $lot_class -> returnColumnSize ('comment'), 0);
						$HTML .= '</div>';
						$HTML .= '</div>'; // End New lot tab

						$HTML .= '<div class="tabbertab" id="lot_plat" title="' . $_HOAM_language ['form_tabs']['lot']['plat'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formField ('lot|block', $_HOAM_language ['field_names']['lot']['block'], $_SESSION['lot_information']['trusted_lot|block'], $lot_class -> returnBlock ($trusted_lot_id), $lot_class -> returnColumnSize ('block'), $_HOAM_setting ['lot']['minimum_block_length']);
						$HTML .= HOAM_formField ('lot|lot', $_HOAM_language ['field_names']['lot']['lot'], $_SESSION['lot_information']['trusted_lot|lot'], $lot_class -> returnLot ($trusted_lot_id), $lot_class -> returnColumnSize ('lot'), $_HOAM_setting ['lot']['minimum_lot_length']);
						$HTML .= HOAM_formField ('lot|plat', $_HOAM_language ['field_names']['lot']['plat'], $_SESSION['lot_information']['trusted_lot|plat'], $lot_class -> returnPlat ($trusted_lot_id), $lot_class -> returnColumnSize ('plat'), $_HOAM_setting ['lot']['minimum_plat_length']);
						$HTML .= HOAM_formField ('lot|latitude', $_HOAM_language ['field_names']['lot']['latitude'], $_SESSION['lot_information']['trusted_lot|latitude'], $lot_class -> returnLatitude ($trusted_lot_id), $lot_class -> returnColumnSize ('latitude'), $_HOAM_setting ['lot']['minimum_latitude_length']);
						$HTML .= HOAM_formField ('lot|longitude', $_HOAM_language ['field_names']['lot']['longitude'], $_SESSION['lot_information']['trusted_lot|longitude'], $lot_class -> returnLongitude ($trusted_lot_id), $lot_class -> returnColumnSize ('longitude'), $_HOAM_setting ['lot']['minimum_longitude_length']);
						$HTML .= '</div>';
						$HTML .= '</div>'; // End lot Plat tab

						$HTML .= '<div class="tabbertab" id="lot_list" title="' . $_HOAM_language ['form_tabs']['lot']['list'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formSelect ('lot|list_existing', $_HOAM_language ['field_names']['lot']['street']);
						$street_list = $lot_class -> listStreets ();
						foreach ($street_list as $street) {
							$HTML .= '<option value="' . $street . '">' . $street . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '<br /><br />';
						$HTML .= '<table>';
						$HTML .= '<colgroup>';
						$HTML .= '<col span="6" width="10%" />';
						$HTML .= '<col width="15%" />';
						$HTML .= '<col width="15%" />';
						$HTML .= '</colgroup>';
						$HTML .= '<thead><tr>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['address'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['building'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['suite'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['block'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['lot'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['plat'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['latitude'] . '</th>';
						$HTML .= '<th>' . $_HOAM_language ['field_names']['lot']['longitude'] . '</th>';
						$HTML .= '</tr></thead>';
						$HTML .= '<tbody id="existing_lot_list"></tbody>';
						$HTML .= '</table>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End lot List Tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/lot/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			$_HOAM_log -> add ('Loading /admin/lot/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				require_once (HOAM_PATH_FUNCTION . 'attachments.php');
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['lot']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['lot']['list'] . '</h1>';
				$colgroup = '<colgroup>';
				$colgroup .= '<col width="15%" />';
				$colgroup .= '<col width="5%" />';
				$colgroup .= '<col width="5%" />';
				$colgroup .= '<col width="5%" />';
				$colgroup .= '<col width="5%" />';
				$colgroup .= '<col width="5%" />';
				$colgroup .= '<col width="25%" />';
				$colgroup .= '<col width="35%" />';
				$colgroup .= '</colgroup>';
				$thead = '<thead><tr>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['lot']['address'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['lot']['sqft'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['lot']['block'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['lot']['lot'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['lot']['plat'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['attachment']['attachments'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['homeowners'] . '<br /><small>' . $_HOAM_language ['tables']['column']['details'] . '</small></th>';
				$thead .= '</tr></thead>';
				$tbody = '<tbody id="lot_list">';
				$lot_list = $lot_class -> listAll (NULL, 'street, address, building, suite');
				if (is_array ($lot_list)) {
					foreach ($lot_list as $lot) {
						$tbody .= '<tr>';
						// Who is the current owner for this lot?
						$tbody .= '<td>' . HOAM_returnAddress ($lot) . '</td>';
						$tbody .= '<td>' . $lot['sqft'] . '</td>';
						$tbody .= '<td>' . $lot['block'] . '</td>';
						$tbody .= '<td>' . $lot['lot'] . '</td>';
						$tbody .= '<td>' . $lot['plat'] . '</td>';

						$tbody .= '<td>';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/lot/edit/' . $lot['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a class="error" href="/admin/lot/delete/' . $lot['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
						}
						$tbody .= '</td>';
						$tbody .= '<td>';
						$lot_attachments = new HOAM_attachments ('attachments');
						$attachment_list = $lot_attachments -> listWithOwner ($lot['id'], $_HOAM_setting ['attachment']['flags']['lot']);
						if (!empty ($attachment_list)) {
							foreach ($attachment_list as $attachment) {
								$tbody .= HOAM_attachmentReturnHTML ($attachment);
							}
						} else {
							$tbody .= $_HOAM_language ['errors']['attachment']['none-found'];
						}
						$tbody .= '</td>';

						$owner_list = $homeowner_class -> listByLotId ($lot['id']);
						if (!empty ($owner_list)) {
							$tbody .= '<td><ul>';
							foreach ($owner_list as $owner) {
								$tbody .= '<li><a href="/admin/homeowner/edit/' . $owner . '/" title="' . $_HOAM_language ['titles']['homeowner']['view-record'] . '">' . html_encode (stripslashes ($homeowner_class -> returnName ($owner))) . '</a></li>';
							}
							$tbody .= '</ul></td>';
						} else {
							$tbody .= '<td>' . $_HOAM_language ['errors']['homeowner']['none-found'] . '</td>';
						}
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="8" class="center">' . $_HOAM_language ['errors']['lot']['none-found'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= '<table class="center">';
				$HTML .= '<caption>' . sprintf ($_HOAM_language ['field_names']['lot']['found'], count ($lot_list)) . '</caption>';
				$HTML .= $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody;
				$HTML .= '</table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('property') :
			$_HOAM_log -> add ('Loading /admin/lot/property/ page.', HOAM_MESSAGE_DEBUG);
			$HTML = HOAM_adminLotProperty (TRUE);
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['lot'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminLotJS');
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['lot'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/house.png" alt="' . $_HOAM_language ['field_names']['admin']['lot'] . '" class="LHS" /><a href="/admin/lot/">' . $_HOAM_language ['field_names']['admin']['lot'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_add'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/lot/add/">' . $_HOAM_language ['field_names']['lot']['add'] . '</a></li>';
					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/lot/list/">' . $_HOAM_language ['field_names']['lot']['list'] . '</a></li>';
					if ($_HOAM_setting ['lot']['property']) {
						$HTML .= '<li style="list-style: none">' . HOAM_adminLotProperty () . '</li>';
					}
					$HTML .= '</ul>';
					
					// Let's ask if the user wants to add a new homeowner if a
					// new lot was just added.
					if (isset ($_SESSION['lot_added'])) {
						$HTML .= '<br data="' . $_SESSION['lot_added'] . '" id="lot_added" />';
						unset ($_SESSION['lot_added']);
					}
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminLotProperty ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$property_class = new HOAM_property ('misc_property');

	function HOAM_propertyList ($action = FALSE) {
		global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
		
		$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
		$group_members_class = new HOAM_group_members ('group_members');
		$homeowner_class = new HOAM_homeowner ('homeowners');
		$property_class = new HOAM_property ('misc_property');
		
		$colgroup  = '<colgroup>';
		$colgroup .= '<col width="20%" />';
		if ($action) {
			$colgroup .= '<col width="10%" />';
		}
		$colgroup .= '<col span="3" width="10%" />';
		$colgroup .= '<col width="20%" />';
		$colgroup .= '<col width="10%" />';
		$colgroup .= '<col width="20%" />';
		$colgroup .= '</colgroup>';
		$thead  = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['property']['description'] . '</th>';
		if ($action) {
			$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
		}
		$thead .= '<th>' . $_HOAM_language ['field_names']['property']['deposit_amount'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['property']['rental_amount'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['property']['fee_amount'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['property']['vendor'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['property']['flags']['rented'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['property']['owner'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$tbody  = '<tbody id="property|list">';
		$property_list = $property_class -> listAllId ();
		if (is_array ($property_list)) {
			foreach ($property_list as $property) {
				$property_info = $property_class -> listAll ($property);
				$property_info = $property_info [0];
				$tbody .=  '<tr>';
				$tbody .= '<td>' . html_encode (stripslashes ($property_info ['description'])) . '</td>';
				if ($action) {
					$tbody .= '<td>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$tbody .= '<small><a href="/admin/lot/property/edit/' . $property_info['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
					}
					$tbody .= ' &nbsp; ';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$tbody .= '<small><a class="error" href="/admin/lot/property/delete/' . $property_info['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
					}
					$tbody .= '</td>';
				}
				$tbody .= '<td>' . number_format ($property_info ['deposit_amount'], 2) . '</td>';
				$tbody .= '<td>' . number_format ($property_info ['rental_amount'], 2) . '</td>';
				$tbody .= '<td>' . number_format ($property_info ['fee_amount'], 2) . '</td>';
				$tbody .= '<td>' . html_encode (stripslashes ($budget_vendor_class -> returnName ($property_info ['vendor_id']))) . '</td>';
				$tbody .= '<td>';
				if ((int) $property_info['flags'] & (int) $_HOAM_setting ['property']['flags']['rented']) {
					$tbody .= $_HOAM_language ['common']['symbol']['check'];
					$tbody .= '</td>';
					$tbody .= '<td>' . html_encode (stripslashes ($homeowner_class -> returnName ($property_info ['owner_id']))) . '</td>';
				} else {
					$tbody .= '</td>';
					$tbody .= '<td>&nbsp;</td>';
				}
				$tbody .= '</tr>';
			}
		} else {
			if ($action) {
				$tbody .= '<tr><td colspan="8">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
			} else {	
				$tbody .= '<tr><td colspan="7">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
			}
		}
		$tbody .= '</tbody>';
		$HTML = '<table style="text-align: center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody . '</table>';

		return ($HTML);
	}
	
	if (isset ($GLOBALS['global_url_array'][3])) {
		switch ($GLOBALS['global_url_array'][3]) {
			case ('add'):
				$_HOAM_log -> add ('Loading /admin/lot/property/add/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_add'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['property']['add'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminPropertyJS');
					$HTML = '<h1>' . $_HOAM_language ['field_names']['property']['add'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'forms.php');
				
					if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
						if (isset ($_SESSION['error_class_item'])) {
							unset ($_SESSION['error_class_item']);
						}
						if (isset ($_SESSION['property_information'])) {
							unset ($_SESSION['property_information']);
						}
					}

					$HTML .= '<form id="property_add" action="/hoam/scripts/lot/property_edit_script.php" enctype="multipart/form-data" method="post">';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['property']['add'] . '</button>';
					$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="property_information" title="' . $_HOAM_language ['form_tabs']['property']['add'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formText ('property|description', $_HOAM_language ['field_names']['property']['description'], $_SESSION['property_information']['trusted_property|description'], '', 32, $_HOAM_setting ['lot']['minimum_address_length']);
					$HTML .= HOAM_formField ('property|deposit_amount', $_HOAM_language ['field_names']['property']['deposit_amount'], $_SESSION['property_information']['trusted_property|deposit_amount'], '', 6, 1);
					$HTML .= HOAM_formField ('property|rental_amount', $_HOAM_language ['field_names']['property']['rental_amount'], $_SESSION['property_information']['trusted_property|rental_amount'], '', 6, 1);
					$HTML .= HOAM_formSelect ('property|vendor', $_HOAM_language ['field_names']['property']['vendor']);
					if (isset ($_SESSION ['property_information']['trusted_property|vendor'])) {
						$default_vendor = $_SESSION ['property_information']['trusted_property|vendor'];
					} else {
						$default_vendor = NULL;
					}
					$vendor_list = $budget_vendor_class -> listAllId ('name', 'ASC');
					foreach ($vendor_list as $vendor) {
						$HTML .= '<option ';
						if ($vendor == $default_vendor) {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="' . $vendor . '">' . stripslashes ($budget_vendor_class -> returnName ($vendor)) . '</option>';
					}
					$HTML .= '</select>';

					$HTML .= HOAM_formSelect ('property|frequency', $_HOAM_language ['field_names']['property']['frequency']);
					if (isset ($_SESSION['property_information']['trusted_property|frequency'])) {
						$default_unit = $_SESSION['property_information']['trusted_property|frequency'];
					} else {
						$default_unit = NULL;
					}
					switch ($default_unit) {
						case $_HOAM_setting ['property']['flags']['annual'] :
							$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
							break;
						case $_HOAM_setting ['property']['flags']['monthly'] :
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
							$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
							break;
						case $_HOAM_setting ['property']['flags']['weekly'] :
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
							$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
							break;
						case $_HOAM_setting ['property']['flags']['daily'] :
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
							$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
							break;
						default :
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
							$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
							break;
					}
					$HTML .= '</select>';
				
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_formField ('property|fee_amount', $_HOAM_language ['field_names']['property']['fee_amount'], $_SESSION['property_information']['trusted_property|fee_amount'], '', 6, 1);
					$HTML .= '<br />';
					$HTML .= HOAM_formCheckbox ('property|rented', $_HOAM_language ['field_names']['property']['flags']['rented'], 1, $_SESSION['property_information']['trusted_property|rented'], 0);
					$HTML .= '<br />';
					$HTML .= '<br />';
					$HTML .= '<fieldset id="property|renter">';
					$HTML .= HOAM_formCheckbox ('property|renew', $_HOAM_language ['field_names']['property']['flags']['renew'], 1, $_SESSION['property_information']['trusted_property|renew'], 0);
					$HTML .= '<br />';
					$HTML .= '<br />';
					$HTML .= HOAM_formSelect ('property|owner', $_HOAM_language ['field_names']['property']['owner']);
					if (isset ($_SESSION['property_information']['trusted_property|owner'])) {
						$default_owner = $_SESSION['property_information']['trusted_property|owner'];
					} else {
						$default_owner = NULL;
					}
					$homeowner_list = $homeowner_class -> listCurrentHomeowners ();
					foreach ($homeowner_list as $owner) {
						$HTML .= '<option ';
						if ($owner == $default_owner) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $owner . '">' . HOAM_returnAddress ($homeowner_class -> returnLotId ($owner));
						$HTML .= '&nbsp;(' . html_encode (stripslashes ($homeowner_class -> returnName ($owner))) . ')</option>';
					}
					$HTML .= '</select>';
					$HTML .= '</fieldset>';
					$HTML .= HOAM_formTextarea ('property|comments', $_HOAM_language ['field_names']['property']['comments'], $_SESSION['property_information']['trusted_property|comments'], '', $_SESSION['current_user']['edit']['row'], $property_class -> returnColumnSize ('comments'));
					$HTML .= '</div>';
					$HTML .= '</div>'; // End New Property Tab

					$HTML .= '<div class="tabbertab" id="property_list" title="' . $_HOAM_language ['form_tabs']['property']['list'] . '">';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_propertyList (); 
					$HTML .= '</div>';
					$HTML .= '</div>'; // End Property List Tab
									
					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case ('delete'):
				$_HOAM_log -> add ('Loading /admin/lot/property/delete/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					if ($GLOBALS['global_url_array'][4]) {
						$trusted_id = $property_class -> superClean ($GLOBALS['global_url_array'][4], $property_class -> returnColumnSize ('id'));
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['property']['delete'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminPropertyJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['property']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');

						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['property_information'])) {
								unset ($_SESSION['property_information']);
							}
						}

						$HTML .= '<form id="property_edit" action="/hoam/scripts/lot/property_edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="property|id" id="property|id" value="' . $trusted_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['property']['delete'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="property_information" title="' . $_HOAM_language ['form_tabs']['property']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formTextDisabled ('property|description', $_HOAM_language ['field_names']['property']['description'], html_encode (stripslashes ($property_class -> returnDescription ($trusted_id))), 32, $_HOAM_setting ['lot']['minimum_address_length']);
						$HTML .= HOAM_formFieldDisabled ('property|deposit_amount', $_HOAM_language ['field_names']['property']['deposit_amount'], number_format ($property_class -> returnDepositAmount ($trusted_id), 2), 6, 1);
						$HTML .= HOAM_formFieldDisabled ('property|rental_amount', $_HOAM_language ['field_names']['property']['rental_amount'], number_format ($property_class -> returnRentalAmount ($trusted_id), 2), 6, 1);
						$HTML .= HOAM_formSelectDisabled ('property|vendor', $_HOAM_language ['field_names']['property']['vendor']);
						if (isset ($_SESSION ['property_information']['trusted_property|vendor'])) {
							$default_vendor = $_SESSION ['property_information']['trusted_property|vendor'];
						} else {
							$default_vendor = $property_class -> returnVendorId ($trusted_id);
						}
						$vendor_list = $budget_vendor_class -> listAllId ('name', 'ASC');
						foreach ($vendor_list as $vendor) {
							$HTML .= '<option ';
							if ($vendor == $default_vendor) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $vendor . '">' . stripslashes ($budget_vendor_class -> returnName ($vendor)) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formSelectDisabled ('property|frequency', $_HOAM_language ['field_names']['property']['frequency']);
						if (isset ($_SESSION['property_information']['trusted_property|frequency'])) {
							$default_unit = $_SESSION['property_information']['trusted_property|frequency'];
						} else {
							$default_unit = $property_class -> returnFlags ($trusted_id);
						}
						switch (TRUE) {
							case ((int) $default_unit & (int) $_HOAM_setting ['property']['flags']['annual']) :
								$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
								break;
							case ((int) $default_unit & (int) $_HOAM_setting ['property']['flags']['monthly']) :
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
								$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
								break;
							case ((int) $default_unit & (int) $_HOAM_setting ['property']['flags']['weekly']) :
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
								$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
								break;
							case ((int) $default_unit & (int) $_HOAM_setting ['property']['flags']['daily']) :
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
								$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
								break;
							default :
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
								break;
						}
						$HTML .= '</select>';
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formFieldDisabled ('property|fee_amount', $_HOAM_language ['field_names']['property']['fee_amount'], number_format ($property_class -> returnFeeAmount ($trusted_id), 2), 6, 1);
						$HTML .= '<br />';
						$HTML .= HOAM_formCheckboxDisabled ('property|rented', $_HOAM_language ['field_names']['property']['flags']['rented'], 1, $property_class -> isBitSet ($trusted_id, $_HOAM_setting ['property']['flags']['rented']));
						$HTML .= '<br />';
						$HTML .= '<br />';
						$HTML .= '<fieldset id="property|renter">';
						$HTML .= HOAM_formCheckboxDisabled ('property|renew', $_HOAM_language ['field_names']['property']['flags']['renew'], 1, $property_class -> isBitSet ($trusted_id, $_HOAM_setting ['property']['flags']['renew']));
						$HTML .= '<br />';
						$HTML .= '<br />';
						$HTML .= HOAM_formSelectDisabled ('property|owner', $_HOAM_language ['field_names']['property']['owner']);
						if (isset ($_SESSION['property_information']['trusted_property|owner'])) {
							$default_owner = $_SESSION['property_information']['trusted_property|owner'];
						} else {
							$default_owner = $property_class -> returnOwnerId ($trusted_id);
						}
						$homeowner_list = $homeowner_class -> listCurrentHomeowners ();
						foreach ($homeowner_list as $owner) {
							$HTML .= '<option ';
							if ($owner == $default_owner) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $owner . '">' . HOAM_returnAddress ($homeowner_class -> returnLotId ($owner));
							$HTML .= '&nbsp;(' . html_encode (stripslashes ($homeowner_class -> returnName ($owner))) . ')</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</fieldset>';
						$HTML .= HOAM_formTextareaDisabled ('property|comments', $_HOAM_language ['field_names']['property']['comments'], $property_class -> returnComments ($trusted_id), $_SESSION['current_user']['edit']['row'], $property_class -> returnColumnSize ('comments'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End New Property Tab
					
						$HTML .= '<div class="tabbertab" id="property_list" title="' . $_HOAM_language ['form_tabs']['property']['list'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_propertyList (); 
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Property List Tab

						$HTML .= '<div class="tabbertab" id="property_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End History tab
					
						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /admin/lot/property/list/');
						exit ();
					}
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case ('edit'):
				$_HOAM_log -> add ('Loading /admin/lot/property/edit/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					if ($GLOBALS['global_url_array'][4]) {
						$trusted_id = $property_class -> superClean ($GLOBALS['global_url_array'][4], $property_class -> returnColumnSize ('id'));
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['property']['edit'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminPropertyJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['property']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');

						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['property_information'])) {
								unset ($_SESSION['property_information']);
							}
						}

						$HTML .= '<form id="property_edit" action="/hoam/scripts/lot/property_edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="property|id" id="property|id" value="' . $trusted_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="property_information" title="' . $_HOAM_language ['form_tabs']['property']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formText ('property|description', $_HOAM_language ['field_names']['property']['description'], $_SESSION['property_information']['trusted_property|description'], html_encode (stripslashes ($property_class -> returnDescription ($trusted_id))), 32, $_HOAM_setting ['lot']['minimum_address_length']);
						$HTML .= HOAM_formField ('property|deposit_amount', $_HOAM_language ['field_names']['property']['deposit_amount'], $_SESSION['property_information']['trusted_property|deposit_amount'], number_format ($property_class -> returnDepositAmount ($trusted_id), 2), 6, 1);
						$HTML .= HOAM_formField ('property|rental_amount', $_HOAM_language ['field_names']['property']['rental_amount'], $_SESSION['property_information']['trusted_property|rental_amount'], number_format ($property_class -> returnRentalAmount ($trusted_id), 2), 6, 1);
						$HTML .= HOAM_formSelect ('property|vendor', $_HOAM_language ['field_names']['property']['vendor']);
						if (isset ($_SESSION ['property_information']['trusted_property|vendor'])) {
							$default_vendor = $_SESSION ['property_information']['trusted_property|vendor'];
						} else {
							$default_vendor = $property_class -> returnVendorId ($trusted_id);
						}
						$vendor_list = $budget_vendor_class -> listAllId ('name', 'ASC');
						foreach ($vendor_list as $vendor) {
							$HTML .= '<option ';
							if ($vendor == $default_vendor) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $vendor . '">' . stripslashes ($budget_vendor_class -> returnName ($vendor)) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formSelect ('property|frequency', $_HOAM_language ['field_names']['property']['frequency']);
						if (isset ($_SESSION['property_information']['trusted_property|frequency'])) {
							$default_unit = $_SESSION['property_information']['trusted_property|frequency'];
						} else {
							$default_unit = $property_class -> returnFlags ($trusted_id);
						}
						switch (TRUE) {
							case ((int) $default_unit & (int) $_HOAM_setting ['property']['flags']['annual']) :
								$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
								break;
							case ((int) $default_unit & (int) $_HOAM_setting ['property']['flags']['monthly']) :
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
								$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
								break;
							case ((int) $default_unit & (int) $_HOAM_setting ['property']['flags']['weekly']) :
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
								$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
								break;
							case ((int) $default_unit & (int) $_HOAM_setting ['property']['flags']['daily']) :
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
								$HTML .= '<option selected="selected" value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
								break;
							default :
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['annual'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_annual'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['monthly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_monthly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['weekly'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_weekly'] . '</option>';
								$HTML .= '<option value="' . $_HOAM_setting ['property']['flags']['daily'] . '">' . $_HOAM_language ['field_names']['budget']['assessment']['frequency_daily'] . '</option>';
								break;
						}
						$HTML .= '</select>';
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formField ('property|fee_amount', $_HOAM_language ['field_names']['property']['fee_amount'], $_SESSION['property_information']['trusted_property|fee_amount'], number_format ($property_class -> returnFeeAmount ($trusted_id), 2), 6, 1);
						$HTML .= '<br />';
						$HTML .= HOAM_formCheckbox ('property|rented', $_HOAM_language ['field_names']['property']['flags']['rented'], 1, $_SESSION['property_information']['trusted_property|rented'], $property_class -> isBitSet ($trusted_id, $_HOAM_setting ['property']['flags']['rented']));
						$HTML .= '<br />';
						$HTML .= '<br />';
						$HTML .= '<fieldset id="property|renter">';
						$HTML .= HOAM_formCheckbox ('property|renew', $_HOAM_language ['field_names']['property']['flags']['renew'], 1, $_SESSION['property_information']['trusted_property|renew'], $property_class -> isBitSet ($trusted_id, $_HOAM_setting ['property']['flags']['renew']));
						$HTML .= '<br />';
						$HTML .= '<br />';
						$HTML .= HOAM_formSelect ('property|owner', $_HOAM_language ['field_names']['property']['owner']);
						if (isset ($_SESSION['property_information']['trusted_property|owner'])) {
							$default_owner = $_SESSION['property_information']['trusted_property|owner'];
						} else {
							$default_owner = $property_class -> returnOwnerId ($trusted_id);
						}
						$homeowner_list = $homeowner_class -> listCurrentHomeowners ();
						foreach ($homeowner_list as $owner) {
							$HTML .= '<option ';
							if ($owner == $default_owner) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $owner . '">' . HOAM_returnAddress ($homeowner_class -> returnLotId ($owner));
							$HTML .= '&nbsp;(' . html_encode (stripslashes ($homeowner_class -> returnName ($owner))) . ')</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</fieldset>';
						$HTML .= HOAM_formTextarea ('property|comments', $_HOAM_language ['field_names']['property']['comments'], $_SESSION['property_information']['trusted_property|comments'], $property_class -> returnComments ($trusted_id), $_SESSION['current_user']['edit']['row'], $property_class -> returnColumnSize ('comments'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End New Property Tab

						$HTML .= '<div class="tabbertab" id="property_list" title="' . $_HOAM_language ['form_tabs']['property']['list'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_propertyList (); 
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Property List Tab

						$HTML .= '<div class="tabbertab" id="property_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End History tab

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /admin/lot/property/list/');
						exit ();
					}
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case ('list') :
				$_HOAM_log -> add ('Loading /admin/lot/property/list/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['property']['list'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['property']['list'] . '</h1>';
					$HTML .= HOAM_propertyList (TRUE);
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			default:
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_add'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_delete'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['lot_edit'], $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['property'])));
			$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['property'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/building.png" alt="' . $_HOAM_language ['field_names']['admin']['property'] . '" class="LHS" /><a href="/admin/lot/property/">' . $_HOAM_language ['field_names']['admin']['property'] . '</a>';
			$HTML .= '<ul>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/building.png\')"><a href="/admin/lot/property/add/">' . $_HOAM_language ['field_names']['property']['add'] . '</a></li>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/building.png\')"><a href="/admin/lot/property/list/">' . $_HOAM_language ['field_names']['property']['list'] . '</a></li>';
			$HTML .= '</ul>';
		} else {
			header ('Location: /error/403/');
			exit ();
		}
	}	
	return ($HTML);
}

function HOAM_adminReport ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	require_once (HOAM_PATH_FUNCTION . 'budget.php');

	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');

	if (isset ($GLOBALS['global_url_array'][2])) {
		switch ($GLOBALS['global_url_array'][2]) {
			case ('homeowner_changes'):
				$_HOAM_log -> add ('Loading /admin/report/homeowner_changes/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['reports']['homeowner_changes'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['reports']['homeowner_changes'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'homeowner.php');

					$new_homeowners = $homeowner_class -> listRecent (20);
					$changed_homeowners = $homeowner_class -> ListRecentModified (20);

					$homeowner_count = count ($new_homeowners);
					if ($homeowner_count > 20) {
						$homeowner_count = 20;
					}
					$colgroup = '<colgroup>';
					$colgroup .= '<col width="5%" />';
					$colgroup .= '<col width="45%" />';
					$colgroup .= '<col width="5%" />';
					$colgroup .= '<col width="45%" />';
					$colgroup .= '</colgroup>';
					$thead = sprintf ($_HOAM_language ['tables']['generic']['thead'], '#', $homeowner_count . ' ' . $_HOAM_language ['tables']['column']['new_homeowners'], '#', $homeowner_count . ' ' . $_HOAM_language ['tables']['column']['new_homeowner_changes']);
					$tbody = '<tbody>';
					if (!empty ($new_homeowners)) {
						for ($i = 0; $i < $homeowner_count; $i++) {
							$tbody .= '<tr>';
							$tbody .= '<td class="center">' . ($i + 1) . '</td>';
							$tbody .= '<td>';
							$tbody .= '<strong>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($new_homeowners[$i])) . '</strong><br />';
							$tbody .= '(' . $_HOAM_language ['common']['purchased'] . ' ' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_class -> returnPurchaseDate ($new_homeowners[$i])))  . ', ' . $_HOAM_language ['common']['entered'] . ' ' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_class -> returnDateCreated ($new_homeowners[$i]))) . ')<br />';
							$tbody .= '<a href="/admin/homeowner/edit/' . $new_homeowners[$i] . '/">' . HOAM_homeownerReturnMailingAddress ($new_homeowners[$i]) . '</a>';
							$tbody .= '</td>';
							$tbody .= '<td class="center">' . ($i + 1) . '</td>';
							$tbody .= '<td>';
							$tbody .= '<strong>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($changed_homeowners[$i])) . '</strong><br />';
							$tbody .= '(' . $_HOAM_language ['common']['purchased'] . ' ' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_class -> returnPurchaseDate ($changed_homeowners[$i])))  . ', ' . $_HOAM_language ['common']['Modified'] . ' ' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_class -> returnDateModified ($changed_homeowners[$i])))  . ')<br />';
							$tbody .= '<a href="/admin/homeowner/edit/' . $changed_homeowners[$i] . '/">' . HOAM_homeownerReturnMailingAddress ($changed_homeowners[$i]) . '</a>';
							$tbody .= '</td>';
							$tbody .= '</tr>';
						}
					} else {
						$tbody .= '<tr>';
						$tbody .= '<td colspan="4" class="center">' . $_HOAM_language ['errors']['homeowner']['none-found'] . '</td>';
						$tbody .= '</tr>';
					}
					$tbody .= '</tbody>';
					$HTML .= sprintf ($_HOAM_language ['tables']['generic']['default'], $colgroup, $thead  . preg_replace ('/thead/', 'tfoot', $thead), $tbody);
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case ('other') :
				$_HOAM_log -> add ('Loading /admin/report/other/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['reports']['other'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_reportJS');
					$HTML = '<h1>' . $_HOAM_language ['field_names']['reports']['other'] . '</h1>';
					$HTML .= '<form class="donotprint">';
					$HTML .= '<center><select name="report_name" id="report_name">';
					$HTML .= '<option value="0">' . $_HOAM_language ['common']['please-select'] . '</option>';
					$report_list = HOAM_returnFiles (HOAM_PATH_REPORT, 'xml');
					foreach ($report_list as $report) {
						$contents = file_get_contents (HOAM_PATH_REPORT . $report);
						$index = array ();
						$values = array ();
						$parser = xml_parser_create('');
						xml_parse_into_struct ($parser, $contents, $values, $index);
						$HTML .= '<option value="' . $report . '">' . $values [$index['NAME'][0]]['value'] . '</option>';
					}
					$HTML .= '</select></center>';
					$HTML .= '</form>';
					$HTML .= '<div id="fillme"></div>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'not_mailing' :
				$_HOAM_log -> add ('Loading /admin/report/not_mailing/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['reports']['not_mailing'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['reports']['not_mailing'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'homeowner.php');

					$homeowner_list = $homeowner_class -> listCurrentOwnersWithBitSet ((int) $_HOAM_setting ['homeowner']['flags']['residence_mailing']);
					$colgroup = '<colgroup>';
					$colgroup .= '<col width="5%" />';
					$colgroup .= '<col width="20%" />';
					$colgroup .= '<col width="15%" />';
					$colgroup .= '<col width="60%" />';
					$colgroup .= '</colgroup>';
					$thead = sprintf ($_HOAM_language ['tables']['generic']['thead'], '#', $_HOAM_language ['field_names']['homeowner']['address'], $_HOAM_language ['common']['purchased'], $_HOAM_language ['form_tabs']['homeowner']['information']);
					$tbody = '<tbody>';
					$i = 1;
					if (!empty ($homeowner_list)) {
						foreach ($homeowner_list as $homeowner) {
							// Get the lot_id of the homeowner;
							$lot_id = $homeowner_class -> returnLotId ($homeowner);
							// Who is the current owner of the lot_id?
							$current_owner = $homeowner_class -> returnCurrentOwner ($lot_id);
							// If the current_owner is the same as the owner w/o mailing at the resident, then we know they're the current resident.
							if ($current_owner == $homeowner) {
								$tbody .= '<tr>';
								$tbody .= '<td class="center">' . $i++ . '</td>';
								$tbody .= '<td class="center">' . HOAM_returnAddress ($lot_id) . '</td>';
								$tbody .= '<td class="center">' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_class -> returnPurchaseDate ($homeowner))) . '</td>';
								$tbody .= '<td><a href="/admin/homeowner/edit/' . $homeowner . '/">' . HOAM_homeownerReturnMailingAddress ($homeowner) . '</a></td>';
								$tbody .= '</tr>';
							}
						}
					} else {
						$tbody .= '<tr>';
						$tbody .= '<td colspan="4" class="center">' . $_HOAM_language ['errors']['homeowner']['no-non-residents'] . '</td>';
						$tbody .= '</tr>';
					}
					$tbody .= '</tbody>';
					$HTML .= sprintf ($_HOAM_language ['tables']['generic']['default'], $colgroup, $thead . preg_replace ('/thead/', 'tfoot', $thead), $tbody);
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'not_residents' :
				$_HOAM_log -> add ('Loading /admin/report/not_residents/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['reports']['not_residents'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['reports']['not_residents'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'homeowner.php');

					$homeowner_list = $homeowner_class -> listCurrentOwnersWithBitSet ((int) $_HOAM_setting ['homeowner']['flags']['resident']);
					$colgroup = '<colgroup>';
					$colgroup .= '<col width="5%" />';
					$colgroup .= '<col width="20%" />';
					$colgroup .= '<col width="15%" />';
					$colgroup .= '<col width="60%" />';
					$colgroup .= '</colgroup>';
					$thead = sprintf ($_HOAM_language ['tables']['generic']['thead'], '#', $_HOAM_language ['field_names']['homeowner']['address'], $_HOAM_language ['common']['purchased'], $_HOAM_language ['form_tabs']['homeowner']['information']);
					$tbody = '<tbody>';
					$i = 1;
					if (!empty ($homeowner_list)) {
						foreach ($homeowner_list as $homeowner) {
							// Get the lot_id of the homeowner;
							$lot_id = $homeowner_class -> returnLotId ($homeowner);
							// Who is the current owner of the lot_id?
							$current_owner = $homeowner_class -> returnCurrentOwner ($lot_id);
							// If the current_owner is the same as the owner w/o mailing at the resident, then we know they're the current resident.
							if ($current_owner == $homeowner) {
								$tbody .= '<tr>';
								$tbody .= '<td class="center">' . $i++ . '</td>';
								$tbody .= '<td class="center">' . HOAM_returnAddress ($lot_id) . '</td>';
								$tbody .= '<td class="center">' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($homeowner_class -> returnPurchaseDate ($homeowner))) . '</td>';
								$tbody .= '<td><a href="/admin/homeowner/edit/' . $homeowner . '/">' . HOAM_homeownerReturnMailingAddress ($homeowner) . '</a></td>';
								$tbody .= '</tr>';
							}
						}
					} else {
						$tbody .= '<tr>';
						$tbody .= '<td colspan="4" class="center">' . $_HOAM_language ['errors']['homeowner']['no-non-residents'] . '</td>';
						$tbody .= '</tr>';
					}
					$tbody .= '</tbody>';
					$HTML .= sprintf ($_HOAM_language ['tables']['generic']['default'], $colgroup, $thead . preg_replace ('/thead/', 'tfoot', $thead), $tbody);
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case ('rental_street') :
				$_HOAM_log -> add ('Loading /admin/report/rental_street/ page.', HOAM_MESSAGE_DEBUG);
				require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
	
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['reports']['rental_street'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['reports']['rental_street'] . '</h1>';
				$HTML .= HOAM_homeownerRentalStreet ();
				break;
			case 'unpaid_amount' :
				$_HOAM_log -> add ('Loading /admin/report/unpaid_amount/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['reports']['unpaid_amount'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['reports']['unpaid_amount'] . '</h1>';
					$HTML .= HOAM_budgetUnpaidAmount (TRUE);
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			case 'unpaid_street' :
				$_HOAM_log -> add ('Loading /admin/report/unpaid_street/ page.', HOAM_MESSAGE_DEBUG);
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					require_once (HOAM_PATH_FUNCTION . 'budget.php');
			
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['reports']['unpaid_street'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['reports']['unpaid_street'] . '</h1>';
					$HTML .= HOAM_budgetUnpaidStreet ();
				} else {
					header ('Location: /error/403/');
					exit ();
				}
				break;
			default:
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
			$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['report_view'], $_SESSION['current_user']['id']) OR
			($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['report'])));
			$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['report'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/report.png" alt="' . $_HOAM_language ['field_names']['admin']['report'] . '" class="LHS" /><a href="/admin/report/">' . $_HOAM_language ['field_names']['admin']['report'] . '</a>';
			$HTML .= '<ul>';
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/user.png\')"><a href="/admin/report/not_mailing/">' . $_HOAM_language ['field_names']['reports']['not_mailing'] . '</a></li>';
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/user.png\')"><a href="/admin/report/not_residents/">' . $_HOAM_language ['field_names']['reports']['not_residents'] . '</a></li>';
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/user.png\')"><a href="/admin/report/rental_street/">' . $_HOAM_language ['field_names']['reports']['rental_street'] . '</a></li>';
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/user.png\')"><a href="/admin/report/homeowner_changes/">' . $_HOAM_language ['field_names']['reports']['homeowner_changes'] . '</a></li>';
			}
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/money.png\')"><a href="/admin/report/unpaid_amount/">' . $_HOAM_language ['field_names']['reports']['unpaid_amount'] . '</a></li>';
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/money.png\')"><a href="/admin/report/unpaid_street/">' . $_HOAM_language ['field_names']['reports']['unpaid_street'] . '</a></li>';
			}
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="/admin/report/other/">' . $_HOAM_language ['field_names']['reports']['other'] . '</a></li>';
			$HTML .= '</ul>';
		} else {
			header ('Location: /error/403/');
			exit ();
		}
	}
	return ($HTML);
}

function HOAM_adminViolation ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	require_once (HOAM_PATH_FUNCTION . 'violations.php');

	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$user_class = new HOAM_user ('users');
	$violation_class = new HOAM_violation ('violations');
	$violation_category_class = new HOAM_violation_category ('violation_category');
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');

	switch ($GLOBALS['global_url_array'][2]) {
		case ('add'):
			$_HOAM_log -> add ('Loading /admin/violation/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminViolationJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				// OK, we're checking to see that the violation we're trying to edit is the one that we
				// have information on in the violation_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['violation_information'])) {
						unset ($_SESSION['violation_information']);
					}
				}

				$HTML .= '<form id="violation_form" action="/hoam/scripts/violation/edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" id="violation|add" name="add">' . $_HOAM_language ['form_buttons']['violation']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';
				$HTML .= '<div class="tabbertab" id="violation_information" title="' . $_HOAM_language ['form_tabs']['violation']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= '<div class="violation_quickinfo" id="violation|quickinfo"></div>';
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formSelect ('violation|address', $_HOAM_language ['field_names']['violation']['residence']);
				if (isset ($_SESSION['violation_information']['trusted_violation|address'])) {
					$default_address = $_SESSION['violation_information']['trusted_violation|address'];
				} else {
					$default_address = NULL;
				}
				$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
				$HTML .= '<option value="0">' . $_HOAM_language ['common']['please-select'] . '</option>';
				foreach ($lot_list as $lot) {
					$HTML .= '<option ';
					if ($lot['id'] == $default_address) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot) . ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($lot['id'])))) . ')</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</fieldset>';
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formSelectMultiple ('violation|categories', $_HOAM_language ['field_names']['violation']['categories'], $_SESSION['current_user']['items_per_page']);
				$category_list = $violation_category_class -> listCategoryAll ();
				if (isset ($_SESSION['violation_information']['trusted_violation|category'])) {
					$default_category = $_SESSION['violation_information']['trusted_violation|categories'];
				} else {
					$default_category = NULL;
				}
				$optgroup = FALSE;
				if (is_array ($category_list)) {
					foreach ($category_list as $category) {
						// Is this a parent category?
						if ($category[1] == 0) {
							if ($optgroup) {
								$HTML .= '</optgroup>';
							} else {
								$optgroup = TRUE;
							}
							$HTML .= '<optgroup label="' . $violation_category_class -> returnCategory ($category[0]) . '">';
							continue;
						}
						$HTML .= '<option ';
						if (is_array ($default_category) AND in_array ($category[0], $default_category)) {
							$HTML .= 'selected ';
						}
						$HTML .= 'value="' . $category[0] . '">' . $violation_category_class -> returnCategory ($category[0]) . '</option>';
					}
					if ($optgroup) {
						$HTML .= '</optgroup>';
					}
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelect ('violation|severity|current', $_HOAM_language ['field_names']['violation']['severity']['current']);
				$severity_list = $violation_severity_class -> listSeverity ();
				if (isset ($_SESSION['violation_information']['trusted_violation|severity|current'])) {
					$default_severity = $_SESSION['violation_information']['trusted_violation|severity|current'];
				} else {
					$default_severity = NULL;
				}
				foreach ($severity_list as $severity) {
					$HTML .= '<option ';
					if ($default_severity == $severity) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity). '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formDate ('violation|date|violation', $_HOAM_language ['field_names']['violation']['date']['violation'], $_SESSION['violation_information']['trusted_violation|date|violation'], HOAM_date ($_HOAM_country ['date']['format_reports']));
				if ($_HOAM_setting ['violation']['require_approval']) {
					$HTML .= HOAM_formSelect ('violation|approval', $_HOAM_language ['field_names']['violation']['approval']);
					if (isset ($_SESSION['violation_information']['trusted_violation|approval'])) {
						$default_option = $_SESSION['violation_information']['trusted_violation|approval'];
					} else {
						$default_option = $_HOAM_setting ['violation']['flags']['needs_approval'];
					}
					foreach ($_HOAM_language ['approval'] as $approval) {
						$HTML .= '<option ';
						if ((int) $_HOAM_setting ['violation']['flags'][$approval['value']] & (int) $default_option) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $_HOAM_setting ['violation']['flags'][$approval['value']] . '">' . $approval['name'] . '</option>';
					}
					$HTML .= '</select>';
				}
				$HTML .= '</div>'; // End mandatory fields
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formText ('violation|specific', $_HOAM_language ['field_names']['violation']['specific'], $_SESSION['violation_information']['trusted_violation|specific'], '', 255);
				$HTML .= '<label for="violation|attachments">' . $_HOAM_language ['field_names']['violation']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('violation|attachments');
				$HTML .= HOAM_formDisplayError ('violation|attachments');
				$HTML .= '</label>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Create New Violation

				$HTML .= '<div class="tabbertab" title="' . $_HOAM_language ['form_tabs']['violation']['letter'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= '<div id="violation|letter" class="violation_letter">';
				// Have we run through the edit_script yet? If so, this will be set and we want to import it.
				$HTML .= '<div id="violation|letter|head" class="violation_letter_head">' . $_HOAM_setting ['organization']['letter']['head'] . '</div>';
				$HTML .= '<br /><br />' . date ($_HOAM_country ['date']['format_long']) . '<br /><br />';
				$HTML .= '<div id="violation|letter|salutation" class="violation_letter_salutation"></div>';
				$HTML .= '<div id="violation|letter|preamble" class="violation_letter_preamble"></div>';
				$HTML .= '<div id="violation|letter|description" class="violation_letter_description"></div>';
				$HTML .= '<div id="violation|letter|specific" class="violation_letter_specific"></div>';
				$HTML .= '<div id="violation|letter|detail" class="violation_letter_detail"></div>';
				$HTML .= '<div id="violation|letter|closing" class="violation_letter_closing"></div>';
				$HTML .= '<div id="violation|letter|signature" class="violation_letter_signature">' . $_HOAM_setting ['organization']['letter']['signature'] . '</div>';
				$HTML .= '</div>';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End View Violation Letter

				$HTML .= '<div class="tabbertab" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formDate ('violation|date|resolveby', $_HOAM_language ['field_names']['violation']['date']['resolveby'], $_SESSION['violation_information']['trusted_violation|date|resolveby'], '');
				$HTML .= '<br />';
				$HTML .= HOAM_formCheckbox ('violation|assume_resolved', $_HOAM_language ['field_names']['violation']['flags']['assume_resolved'], 1, $_SESSION['violation_information']['trusted_violation|assume_resolved'], 0);
				$HTML .= '<br />';
				$HTML .= '<br />';
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Advanced Options

				$HTML .= '<div class="tabbertab" title="' . $_HOAM_language ['form_tabs']['violation']['history'] . '">';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_violationTablePreload ();
				$HTML .= '</div>';
				$HTML .= '</div>'; // End Residence Violation History

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('category') :
			$_HOAM_log -> add ('Loading /admin/violation/category/ page.', HOAM_MESSAGE_DEBUG);
			$HTML = HOAM_adminViolationCategory (TRUE);
			break;
		case 'delete' :
			$_HOAM_log -> add ('Loading /admin/violation/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_id = $violation_class -> superClean ($GLOBALS['global_url_array'][3], $violation_class -> returnColumnSize ('id'));
					if ($violation_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['edit'] . ' (' . HOAM_returnAddress ($violation_class -> returnLotId ($trusted_id)) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminViolationJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the violation we're trying to edit is the one that we
						// have information on in the violation_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['violation_information'])) {
								unset ($_SESSION['violation_information']);
							}
						}

						$HTML .= '<form id="violation_form" action="/hoam/scripts/violation/edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" id="violation|id" name="violation|id" value="' . $trusted_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" id="violation|delete" name="delete">' . $_HOAM_language ['form_buttons']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';
						$HTML .= '<div class="tabbertab" id="violation_information" title="' . $_HOAM_language ['form_tabs']['violation']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<div class="violation_quickinfo" id="violation|quickinfo"></div>';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formSelect ('violation|address', $_HOAM_language ['field_names']['violation']['residence']);
						if (isset ($_SESSION['violation_information']['trusted_violation|address'])) {
							$default_address = $_SESSION['violation_information']['trusted_violation|address'];
						} else {
							$default_address = $violation_class -> returnLotId ($trusted_id);
						}
						$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
						foreach ($lot_list as $lot) {
							$HTML .= '<option ';
							if ($lot['id'] == $default_address) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</fieldset>';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formSelectMultipleDisabled ('violation|categories', $_HOAM_language ['field_names']['violation']['categories'], $_SESSION['current_user']['items_per_page']);
						$category_list = $violation_category_class -> listCategoryAll ();
						if (isset ($_SESSION['violation_information']['trusted_violation|category'])) {
							$default_category = $_SESSION['violation_information']['trusted_violation|categories'];
						} else {
							$default_category = unserialize ($violation_class -> returnCategory ($trusted_id));
						}
						$optgroup = FALSE;
						if (is_array ($category_list)) {
							foreach ($category_list as $category) {
								// Is this a parent category?
								if ($category[1] == 0) {
									if ($optgroup) {
										$HTML .= '</optgroup>';
									} else {
										$optgroup = TRUE;
									}
									$HTML .= '<optgroup label="' . $violation_category_class -> returnCategory ($category[0]) . '">';
									continue;
								}
								$HTML .= '<option ';
								if (in_array ($category[0], $default_category)) {
									$HTML .= 'selected ';
								}
								$HTML .= 'value="' . $category[0] . '">' . $violation_category_class -> returnCategory ($category[0]) . '</option>';
							}
							if ($optgroup) {
								$HTML .= '</optgroup>';
							}
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formSelectDisabled ('violation|severity|current', $_HOAM_language ['field_names']['violation']['severity']['current']);
						$severity_list = $violation_severity_class -> listSeverity ();
						if (isset ($_SESSION['violation_information']['trusted_violation|severity|current'])) {
							$default_severity = $_SESSION['violation_information']['trusted_violation|severity|current'];
						} else {
							$default_severity = $violation_class -> returnSeverity ($trusted_id);
						}
						foreach ($severity_list as $severity) {
							$HTML .= '<option ';
							if ($default_severity == $severity) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity). '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</fieldset>';
						$HTML .= HOAM_formDateDisabled ('violation|date|violation', $_HOAM_language ['field_names']['violation']['date']['violation'], date ($_HOAM_country ['date']['format_reports'], strtotime ($violation_class -> returnViolationDate ($trusted_id))));
						if ($_HOAM_setting ['violation']['require_approval']) {
							$HTML .= HOAM_formSelectDisabled ('violation|approval', $_HOAM_language ['field_names']['violation']['approval']);
							if (isset ($_SESSION['violation_information']['trusted_violation|approval'])) {
								$default_option = $_SESSION['violation_information']['trusted_violation|approval'];
							} else {
								$default_option = $violation_class -> returnFlags ($trusted_id);
							}
							foreach ($_HOAM_language ['approval'] as $approval) {
								$HTML .= '<option ';
								if ((int) $_HOAM_setting ['violation']['flags'][$approval['value']] & (int) $default_option) {
									$HTML .= 'selected="selected" ';
								}
								$HTML .= 'value="' . $_HOAM_setting ['violation']['flags'][$approval['value']] . '">' . $approval['name'] . '</option>';
							}
							$HTML .= '</select>';
						}
						$HTML .= '</div>'; // End mandatory fields
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formTextDisabled ('violation|specific', $_HOAM_language ['field_names']['violation']['specific'], $violation_class -> returnSpecific ($trusted_id));
						$HTML .= '<label for="violation|attachments">' . $_HOAM_language ['field_names']['violation']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('violation|attachments');
						$HTML .= HOAM_formDisplayError ('violation|attachments');
						$HTML .= '</label>';
						// Does this report already have any attachments?
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_id, $_HOAM_setting ['attachment']['flags']['violation']);
						if (is_array ($attachment_list)) {
							// Display the current attachments
							foreach ($attachment_list as $attachment) {
								$HTML .= HOAM_attachmentReturnHTML ($attachment);
							}
							$HTML .= '<br class="clear" />';
						}
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Edit Violation

						$HTML .= '<div class="tabbertab" title="' . $_HOAM_language ['form_tabs']['violation']['letter'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<fieldset>';
						$HTML .= '<div id="violation|letter" class="violation_letter">';
						// Have we run through the edit_script yet? If so, this will be set and we want to import it.
						$HTML .= '<div id="violation|letter|head" class="violation_letter_head">' . $_HOAM_setting ['organization']['letter']['head'] . '</div>';
						$HTML .= '<br /><br />' . date ($_HOAM_country ['date']['format_long']) . '<br /><br />';
						$HTML .= '<div id="violation|letter|salutation" class="violation_letter_salutation"></div>';
						$HTML .= '<div id="violation|letter|preamble" class="violation_letter_preamble"></div>';
						$HTML .= '<div id="violation|letter|description" class="violation_letter_description"></div>';
						$HTML .= '<div id="violation|letter|specific" class="violation_letter_specific"></div>';
						$HTML .= '<div id="violation|letter|detail" class="violation_letter_detail"></div>';
						$HTML .= '<div id="violation|letter|closing" class="violation_letter_closing"></div>';
						$HTML .= '<div id="violation|letter|signature" class="violation_letter_signature">' . $_HOAM_setting ['organization']['letter']['signature'] . '</div>';
						$HTML .= '</div>';
						$HTML .= '</div>';
						$HTML .= '</fieldset>';
						$HTML .= '</div>'; // End View Violation Letter

						$HTML .= '<div class="tabbertab" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formDateDisabled ('violation|date|resolveby', $_HOAM_language ['field_names']['violation']['date']['resolveby'], date ($_HOAM_country ['date']['format_reports'], strtotime ($violation_class -> returnResolveByDate ($trusted_id))));
						$HTML .= '<br />';
						$HTML .= HOAM_formCheckboxDisabled ('violation|assume_resolved', $_HOAM_language ['field_names']['violation']['flags']['assume_resolved'], 1, $violation_class -> isBitSet ($trusted_id, $_HOAM_setting ['violation']['flags']['assume_resolved']));
						$HTML .= '<br />';
						$HTML .= '<br />';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Advanced Options

						$HTML .= '<div class="tabbertab" title="' . $_HOAM_language ['form_tabs']['violation']['history'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_violationTablePreload ();
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Residence Violation History

						$HTML .= '<div class="tabbertab" id="violation_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End History tab
						
						$HTML .= '</div>'; // End Tabber
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/violation/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'edit' :
			$_HOAM_log -> add ('Loading /admin/violation/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_id = $violation_class -> superClean ($GLOBALS['global_url_array'][3], $violation_class -> returnColumnSize ('id'));
					if ($violation_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['edit'] . ' (' . HOAM_returnAddress ($violation_class -> returnLotId ($trusted_id)) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminViolationJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'attachments.php');
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the violation we're trying to edit is the one that we
						// have information on in the violation_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['violation_information'])) {
								unset ($_SESSION['violation_information']);
							}
						}

						$HTML .= '<form id="violation_form" action="/hoam/scripts/violation/edit_script.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" id="violation|id" name="violation|id" value="' . $trusted_id . '" />';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" id="violation|edit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';
						$HTML .= '<div class="tabbertab" id="violation_information" title="' . $_HOAM_language ['form_tabs']['violation']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<div class="violation_quickinfo" id="violation|quickinfo"></div>';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formSelect ('violation|address', $_HOAM_language ['field_names']['violation']['residence']);
						if (isset ($_SESSION['violation_information']['trusted_violation|address'])) {
							$default_address = $_SESSION['violation_information']['trusted_violation|address'];
						} else {
							$default_address = $violation_class -> returnLotId ($trusted_id);
						}
						$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
						foreach ($lot_list as $lot) {
							$HTML .= '<option ';
							if ($lot['id'] == $default_address) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</fieldset>';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formSelectMultiple ('violation|categories', $_HOAM_language ['field_names']['violation']['categories'], $_SESSION['current_user']['items_per_page']);
						$category_list = $violation_category_class -> listCategoryAll ();
						if (isset ($_SESSION['violation_information']['trusted_violation|category'])) {
							$default_category = $_SESSION['violation_information']['trusted_violation|categories'];
						} else {
							$default_category = unserialize ($violation_class -> returnCategory ($trusted_id));
						}
						$optgroup = FALSE;
						if (is_array ($category_list)) {
							foreach ($category_list as $category) {
								// Is this a parent category?
								if ($category[1] == 0) {
									if ($optgroup) {
										$HTML .= '</optgroup>';
									} else {
										$optgroup = TRUE;
									}
									$HTML .= '<optgroup label="' . $violation_category_class -> returnCategory ($category[0]) . '">';
									continue;
								}
								$HTML .= '<option ';
								if (in_array ($category[0], $default_category)) {
									$HTML .= 'selected ';
								}
								$HTML .= 'value="' . $category[0] . '">' . $violation_category_class -> returnCategory ($category[0]) . '</option>';
							}
							if ($optgroup) {
								$HTML .= '</optgroup>';
							}
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formSelect ('violation|severity|current', $_HOAM_language ['field_names']['violation']['severity']['current']);
						$severity_list = $violation_severity_class -> listSeverity ();
						if (isset ($_SESSION['violation_information']['trusted_violation|severity|current'])) {
							$default_severity = $_SESSION['violation_information']['trusted_violation|severity|current'];
						} else {
							$default_severity = $violation_class -> returnSeverity ($trusted_id);
						}
						foreach ($severity_list as $severity) {
							$HTML .= '<option ';
							if ($default_severity == $severity) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity). '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</fieldset>';
						$HTML .= HOAM_formDate ('violation|date|violation', $_HOAM_language ['field_names']['violation']['date']['violation'], $_SESSION['violation_information']['trusted_violation|date|violation'], date ($_HOAM_country ['date']['format_reports'], strtotime ($violation_class -> returnViolationDate ($trusted_id))));
						if ($_HOAM_setting ['violation']['require_approval']) {
							$HTML .= HOAM_formSelect ('violation|approval', $_HOAM_language ['field_names']['violation']['approval']);
							if (isset ($_SESSION['violation_information']['trusted_violation|approval'])) {
								$default_option = $_SESSION['violation_information']['trusted_violation|approval'];
							} else {
								$default_option = $violation_class -> returnFlags ($trusted_id);
							}
							foreach ($_HOAM_language ['approval'] as $approval) {
								$HTML .= '<option ';
								if ((int) $_HOAM_setting ['violation']['flags'][$approval['value']] & (int) $default_option) {
									$HTML .= 'selected="selected" ';
								}
								$HTML .= 'value="' . $_HOAM_setting ['violation']['flags'][$approval['value']] . '">' . $approval['name'] . '</option>';
							}
							$HTML .= '</select>';
						}
						$HTML .= '</div>'; // End mandatory fields
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formText ('violation|specific', $_HOAM_language ['field_names']['violation']['specific'], $_SESSION['violation_information']['trusted_violation|specific'], $violation_class -> returnSpecific ($trusted_id), 255);
						$HTML .= '<label for="violation|attachments">' . $_HOAM_language ['field_names']['violation']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('violation|attachments');
						$HTML .= HOAM_formDisplayError ('violation|attachments');
						$HTML .= '</label>';
						// Does this report already have any attachments?
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_id, $_HOAM_setting ['attachment']['flags']['violation']);
						$HTML .= '<div>';
						if (is_array ($attachment_list)) {
							// Display the current attachments
							foreach ($attachment_list as $attachment) {
								$HTML .= HOAM_attachmentReturnHTML ($attachment);
							}
							$HTML .= '<br class="clear" />';
						}
						$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Edit Violation

						$HTML .= '<div class="tabbertab" title="' . $_HOAM_language ['form_tabs']['violation']['letter'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= '<fieldset>';
						$HTML .= '<div id="violation|letter" class="violation_letter">';
						// Have we run through the edit_script yet? If so, this will be set and we want to import it.
						$HTML .= '<div id="violation|letter|head" class="violation_letter_head">' . $_HOAM_setting ['organization']['letter']['head'] . '</div>';
						$HTML .= '<br /><br />' . date ($_HOAM_country ['date']['format_long']) . '<br /><br />';
						$HTML .= '<div id="violation|letter|salutation" class="violation_letter_salutation"></div>';
						$HTML .= '<div id="violation|letter|preamble" class="violation_letter_preamble"></div>';
						$HTML .= '<div id="violation|letter|description" class="violation_letter_description"></div>';
						$HTML .= '<div id="violation|letter|specific" class="violation_letter_specific"></div>';
						$HTML .= '<div id="violation|letter|detail" class="violation_letter_detail"></div>';
						$HTML .= '<div id="violation|letter|closing" class="violation_letter_closing"></div>';
						$HTML .= '<div id="violation|letter|signature" class="violation_letter_signature">' . $_HOAM_setting ['organization']['letter']['signature'] . '</div>';
						$HTML .= '</div>';
						$HTML .= '</div>';
						$HTML .= '</fieldset>';
						$HTML .= '</div>'; // End View Violation Letter

						$HTML .= '<div class="tabbertab" title="' . $_HOAM_language ['form_tabs']['advanced'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formDate ('violation|date|resolveby', $_HOAM_language ['field_names']['violation']['date']['resolveby'], $_SESSION['violation_information']['trusted_violation|date|resolveby'], date ($_HOAM_country ['date']['format_reports'], strtotime ($violation_class -> returnResolveByDate ($trusted_id))));
						$HTML .= '<br />';
						$HTML .= HOAM_formCheckbox ('violation|assume_resolved', $_HOAM_language ['field_names']['violation']['flags']['assume_resolved'], 1, $_SESSION['violation_information']['trusted_violation|assume_resolved'], $violation_class -> isBitSet ($trusted_id, $_HOAM_setting ['violation']['flags']['assume_resolved']));
						$HTML .= '<br />';
						$HTML .= '<br />';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Advanced Options

						$HTML .= '<div class="tabbertab" title="' . $_HOAM_language ['form_tabs']['violation']['history'] . '">';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_violationTablePreload ();
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Residence Violation History

						$HTML .= '<div class="tabbertab" id="violation_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End History tab
						
						$HTML .= '</div>'; // End Tabber
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/violation/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'letter' :
			$_HOAM_log -> add ('Loading /admin/violation/letter/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_approve'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_violation_id = $violation_class -> superClean ($GLOBALS['global_url_array'][3], $violation_class -> returnColumnSize ('id'));
					if ($violation_class -> idExists ($trusted_violation_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['letter'] . ' (' . HOAM_returnAddress ($violation_class -> returnLotId ($trusted_violation_id)) . ')')));
						$HTML  = '<h1 class="donotprint">' . $_HOAM_language ['field_names']['violation']['letter'] . '</h1>';
						// The style entry needs to be second currently because of the
						// css used when printing to make sure the header dissapears.
						$HTML .= '{{LETTER_NOPRINT}}';
						$HTML .= '<div class="center donotprint">';
						$homeowner_id = $homeowner_class -> returnOwner ($violation_class -> returnViolationDate ($trusted_violation_id), $violation_class -> returnLotId ($trusted_violation_id));
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$HTML .= '<small><a href="/admin/homeowner/edit/' . $homeowner_id . '/">' . $_HOAM_language ['action']['homeowner']['edit'] . '</a></small>';
							$HTML .= ' &nbsp; ';
						}
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$HTML .= '<small><a href="/admin/financial/homeowner/review/' . $homeowner_id . '/">' . $_HOAM_language ['action']['budget']['review'] . '</a></small>';
							$HTML .= ' &nbsp; ';
							$HTML .= '<small><a href="/admin/financial/homeowner/invoice/' . $homeowner_id . '/">' . $_HOAM_language ['action']['budget']['invoice'] . '</a></small>';
						}
						$HTML .= '</div>';
						$HTML .= stripslashes (str_replace ('\n', "\n", str_replace ('\r\n', "\r\n", $violation_class -> returnOriginalLetter ($trusted_violation_id))));
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/violation/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'letters_date' :
			$_HOAM_log -> add ('Loading /admin/violation/letters_date/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_approve'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['letters_date'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminViolationPrintJS');
				$HTML  = '<h1 class="donotprint">' . $_HOAM_language ['field_names']['violation']['letters_date'] . '</h1>';
				// The style entry needs to be second currently because of the
				// css used when printing to make sure the header dissapears.
				$HTML .= '<style>@media print { #page_footer { display: none } }</style>';
				$HTML .= '<form class="donotprint" id="violation|letter|form" action="">';
				$HTML .= '<select name="date" id="violation|letter|date">';
				$dates = $violation_class -> listViolationDates ();
				if (is_array ($dates)) {
					$HTML .= '<option>Select a date</option>';
					$date_count = count ($dates);
					for ($i = 0; $i < $date_count; $i++) {
						$HTML .= '<option value="' . $dates[$i] . '">' . $dates[$i] . '</option>';
					}
				} else {
					$HTML .= '<option>' . $_HOAM_language ['errors']['violation']['history_none'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			$_HOAM_log -> add ('Loading /admin/violation/list/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['list'] . '</h1>';
				$colgroup = '<colgroup>';
				$colgroup .= '</colgroup>';
				$thead = '<thead><tr>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['date']['violation'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['homeowner'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['residence'] . '</th>';
				if ($_HOAM_setting ['violation']['require_approval']) {
					$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['approval'] . '</th>';
				}
				$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['severity']['name'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['author'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['date']['created'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['field_names']['generic']['date']['modified'] . '</th>';
				$thead .= '</tr></thead>';
				$tbody = '<tbody id="violation_list_tbody">';
				$violation_list = $violation_class -> ListAllId ();
				if (is_array ($violation_list)) {
					foreach ($violation_list as $violation) {
						$violation_info = $violation_class -> ListAll ($violation);
						// We reassign it to itself, because the ListAll() function assumes you're retrieving multiple="multiple" rows.
						$violation_info = $violation_info[0];
						$tbody .= '<tr>';
						$tbody .= '<td>' . date ($_HOAM_country['date']['format_reports'], strtotime ($violation_info['dateviolation'])) . '</td>';
						$tbody .= '<td>' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnOwner ($violation_info['dateviolation'], $violation_info['lot_id'])))) . '</td>';
						$tbody .= '<td>' . HOAM_returnAddress ($violation_info['lot_id']) . '</td>';
						if ($_HOAM_setting ['violation']['require_approval']) {
							$tbody .= '<td>';
							$flags = $violation_class -> returnFlags ($violation);
				   			foreach ($_HOAM_language ['approval'] as $approval) {
   								if ((int) $_HOAM_setting ['violation']['flags'][$approval['value']] & (int) $flags) {
   									$tbody .= $approval['name'];
   									break;  
   								}
   							}
							unset ($flags);
							$tbody .= '</td>';
						}
						$tbody .= '<td>' . $violation_severity_class -> returnSeverity ($violation_info['severity']) . '</td>';
						$tbody .= '<td>';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_view'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/violation/letter/' . $violation_info['id'] . '/">' . $_HOAM_language ['action']['violation']['letter'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/violation/edit/' . $violation_info['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
							$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
							($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a class="error" href="/admin/violation/delete/' . $violation_info['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
						}
						$tbody .= '</td>';
						$tbody .= '<td>' . $user_class -> returnAccountName ($violation_info['user_id']) . '</td>';
						$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($violation_info['datecreated'])) . '</td>';
						$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($violation_info['datemodified'])) . '</td>';
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="8">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>'. $tbody . '<tbody></table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('severity') :
			$_HOAM_log -> add ('Loading /admin/violation/severity/ page.', HOAM_MESSAGE_DEBUG);
			$HTML = HOAM_adminViolationSeverity (TRUE);
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['violation'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['violation'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/house.png" alt="' . $_HOAM_language ['field_names']['admin']['violation'] . '" class="LHS" /><a href="/admin/violation/">' . $_HOAM_language ['field_names']['admin']['violation'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/violation/add/">' . $_HOAM_language ['field_names']['violation']['add'] . '</a></li>';
					}
//					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
//					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
//					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
//						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/violation/delete/">' . $_HOAM_language ['field_names']['violation']['delete'] . '</a></li>';
//					}
//					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
//					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
//					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
//						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/violation/edit/">' . $_HOAM_language ['field_names']['violation']['edit'] . '</a></li>';
//					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/violation/letter/">' . $_HOAM_language ['field_names']['violation']['letter'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/violation/letters_date/">' . $_HOAM_language ['field_names']['violation']['letters_date'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/violation/list/">' . $_HOAM_language ['field_names']['violation']['list'] . '</a></li>';
					$HTML .= '<li style="list-style: none">' . HOAM_adminViolationCategory () . '</li>';
					$HTML .= '<li style="list-style: none">' . HOAM_adminViolationSeverity () . '</li>';
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminViolationCategory ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;

	require_once (HOAM_PATH_FUNCTION . 'violations.php');
	
	$group_members_class = new HOAM_group_members ('group_members');
	$user_class = new HOAM_user ('users');
	$violation_category_class = new HOAM_violation_category ('violation_category');
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');

	switch ($GLOBALS['global_url_array'][3]) {
		case ('add'):
			$_HOAM_log -> add ('Loading /admin/violation/category/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['category']['add'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['category']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				// OK, we're checking to see that the category we're trying to edit is the one that we
				// have information on in the violation_category_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['violation_category_information'])) {
						unset ($_SESSION['violation_category_information']);
					}
				}

				$HTML .= '<form id="violation_category_add" action="/hoam/scripts/violation/violation_category_edit.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['violation']['category']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="violation_category_information" title="' . $_HOAM_language ['form_tabs']['violation']['category']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelect ('violation|category|parent_category', $_HOAM_language ['field_names']['violation']['category']['parent']);
				$parent_category_list = $violation_category_class -> listParentCategories ();
				if (isset ($_SESSION['violation_category_information']['trusted_violation|category|parent_category'])) {
					$default_parent_category = $_SESSION['violation_category_information']['trusted_violation|category|parent_category'];
				} else {
					$default_parent_category = NULL;
				}
				foreach ($parent_category_list as $parent) {
					$HTML .= '<option ';
					if ($default_parent_category == $parent) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $parent . '">' . $violation_category_class -> returnCategory ($parent) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '<fieldset>';
				$HTML .= HOAM_formFieldEnable ('violation|category|parent_category-other', $_HOAM_language ['field_names']['violation']['category']['parent-other'], $_SESSION['violation_category_information']['trusted_violation|category|parent_category-other'], '', $violation_category_class -> returnColumnSize ('category'), $_HOAM_setting ['violation']['minimum_category_length']);
				$HTML .= '</fieldset>';
				$HTML .= HOAM_formText ('violation|category|name', $_HOAM_language ['field_names']['violation']['category']['name'], $_SESSION['violation_category_information']['trusted_violation|category|name'], '', $violation_category_class -> returnColumnSize ('category'));
				$HTML .= HOAM_formSelect ('violation|category|initial_severity', $_HOAM_language ['field_names']['violation']['category']['initial_severity']);
				$severity_list = $violation_severity_class -> listSeverity ();
				if (isset ($_SESSION['violation_category_information']['trusted_violation|category|initial_severity'])) {
					$default_initial_severity = $_SESSION['violation_category_information']['trusted_violation|category|initial_severity'];
				} else {
					$default_initial_severity = NULL;
				}
				foreach ($severity_list as $severity) {
					$HTML .= '<option ';
					if ($default_initial_severity == $severity) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formTextarea ('violation|category|description', $_HOAM_language ['field_names']['violation']['category']['description'], $_SESSION['violation_category_information']['trusted_violation|category|description'], '', $_SESSION['current_user']['edit']['row'], $violation_category_class -> returnColumnSize ('description'));
				$HTML .= HOAM_formTextarea ('violation|category|detail', $_HOAM_language ['field_names']['violation']['category']['detail'], $_SESSION['violation_category_information']['trusted_violation|category|detail'], '', $_SESSION['current_user']['edit']['row'], $violation_category_class -> returnColumnSize ('detail'));
				$HTML .= '</div>';
				$HTML .= '</div>'; // End New Category Tab

				// Insert list of existing categories
				$HTML .= HOAM_violationShowExistingCategoryList ();

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'delete' :
			$_HOAM_log -> add ('Loading /admin/violation/category/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_category_id = $violation_category_class -> superClean ($GLOBALS['global_url_array'][4], $violation_category_class -> returnColumnSize ('id'));
					if ($violation_category_class -> idExists ($trusted_category_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['category']['delete'])));
						$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['category']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the category we're trying to edit is the one that we
						// have information on in the violation_category_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['violation_category_information'])) {
								unset ($_SESSION['violation_category_information']);
							}
						}

						$HTML .= '<form id="violation_category_delete" action="/hoam/scripts/violation/violation_category_edit.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="violation|category|id" value="' . $trusted_category_id . '" />';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['violation']['category']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="violation_category_information" title="' . $_HOAM_language ['form_tabs']['violation']['category']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						// If we're modifying a parent category, don't show
						// the option for a parent_category
						$parent_category_list = $violation_category_class -> listParentCategories ();
						if (!in_array ($trusted_category_id, $parent_category_list)) {
							$HTML .= HOAM_formSelectDisabled ('violation|category|parent_category', $_HOAM_language ['field_names']['violation']['category']['parent']);
							if (isset ($_SESSION['violation_category_information']['trusted_violation|category|parent_category'])) {
								$default_parent_category = $_SESSION['violation_category_information']['trusted_violation|category|parent_category'];
							} else {
								$default_parent_category = $violation_category_class -> returnParentCategory ($trusted_category_id);
							}
							foreach ($parent_category_list as $parent) {
								$HTML .= '<option ';
								if ($default_parent_category == $parent) {
									$HTML .= 'selected ';
								}
								$HTML .= 'value="' . $parent . '">' . $violation_category_class -> returnCategory ($parent) . '</option>';
							}
							$HTML .= '</select>';
						}
						$HTML .= HOAM_formTextDisabled ('violation|category|name', $_HOAM_language ['field_names']['violation']['category']['name'], $violation_category_class -> returnCategory ($trusted_category_id));
						$HTML .= HOAM_formSelectDisabled ('violation|category|initial_severity', $_HOAM_language ['field_names']['violation']['category']['initial_severity']);
						$severity_list = $violation_severity_class -> listSeverity ();
						if (isset ($_SESSION['violation_category_information']['trusted_violation|category|initial_severity'])) {
							$default_initial_severity = $_SESSION['violation_category_information']['trusted_violation|category|initial_severity'];
						} else {
							$default_initial_severity = $violation_category_class -> returnInitialSeverity ($trusted_category_id);
						}
						foreach ($severity_list as $severity) {
							$HTML .= '<option ';
							if ($default_initial_severity == $severity) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formTextareaDisabled ('violation|category|description', $_HOAM_language ['field_names']['violation']['category']['description'], $_SESSION['violation_category_information']['trusted_violation|category|description'], $violation_category_class -> returnDescription ($trusted_category_id), $_SESSION['current_user']['edit']['row'], $violation_category_class -> returnColumnSize ('description'));
						$HTML .= HOAM_formTextareaDisabled ('violation|category|detail', $_HOAM_language ['field_names']['violation']['category']['detail'], $_SESSION['violation_category_information']['trusted_violation|category|detail'], $violation_category_class -> returnDetail ($trusted_category_id), $_SESSION['current_user']['edit']['row'], $violation_category_class -> returnColumnSize ('detail'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Delete Category Tab

						// Insert list of existing categories
						$HTML .= HOAM_violationShowExistingCategoryList ();

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/violation/category/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'edit' :
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_category_id = $violation_category_class -> superClean ($GLOBALS['global_url_array'][4], $violation_category_class -> returnColumnSize ('id'));
					if ($violation_category_class -> idExists ($trusted_category_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['category']['edit'])));
						$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['category']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the category we're trying to edit is the one that we
						// have information on in the violation_category_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['violation_category_information'])) {
								unset ($_SESSION['violation_category_information']);
							}
						}

						$HTML .= '<form id="violation_category_edit" action="/hoam/scripts/violation/violation_category_edit.php" enctype="multipart/form-data" method="post">';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<input type="hidden" name="violation|category|id" value="' . $trusted_category_id . '" />';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="violation_category_information" title="' . $_HOAM_language ['form_tabs']['violation']['category']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						// If we're modifying a parent category, don't show
						// the option for a parent_category
						$parent_category_list = $violation_category_class -> listParentCategories ();
						if (in_array ($trusted_category_id, $parent_category_list)) {
							$HTML .= HOAM_formCheckbox ('violation|category|change', $_HOAM_language ['field_names']['violation']['category']['change'], 1, $_SESSION['violation_category_information']['trusted_violaton|category|change'], 0);
							$HTML .= '<br />';
							$HTML .= '<br />';
							$HTML .= '<fieldset style="display: none" id="parent_change">';
						} else {
							$HTML .= '<fieldset id="parent_change">';
						}
						$HTML .= HOAM_formSelect ('violation|category|parent_category', $_HOAM_language ['field_names']['violation']['category']['parent']);
						if (isset ($_SESSION['violation_category_information']['trusted_violation|category|parent_category'])) {
							$default_parent_category = $_SESSION['violation_category_information']['trusted_violation|category|parent_category'];
						} else {
							$default_parent_category = $violation_category_class -> returnParentCategory ($trusted_category_id);
						}
						foreach ($parent_category_list as $parent) {
							if ($parent != $trusted_category_id) {
								$HTML .= '<option ';
								if ($default_parent_category == $parent) {
									$HTML .= 'selected ';
								}
								$HTML .= 'value="' . $parent . '">' . $violation_category_class -> returnCategory ($parent) . '</option>';
							}
						}
						$HTML .= '</select>';
						$HTML .= '<fieldset>';
						$HTML .= HOAM_formFieldEnable ('violation|category|parent_category-other', $_HOAM_language ['field_names']['violation']['category']['parent-other'], $_SESSION['violation_category_information']['trusted_violation|category|parent_category-other'], '', $violation_category_class -> returnColumnSize ('category'), $_HOAM_setting ['violation']['minimum_category_length']);
						$HTML .= '</fieldset>';
						$HTML .= '</fieldset>';

						$HTML .= HOAM_formText ('violation|category|name', $_HOAM_language ['field_names']['violation']['category']['name'], $_SESSION['violation_category_information']['trusted_violation|category|name'], $violation_category_class -> returnCategory ($trusted_category_id), $violation_category_class -> returnColumnSize ('category'));
						$HTML .= HOAM_formSelect ('violation|category|initial_severity', $_HOAM_language ['field_names']['violation']['category']['initial_severity']);
						$severity_list = $violation_severity_class -> listSeverity ();
						if (isset ($_SESSION['violation_category_information']['trusted_violation|category|initial_severity'])) {
							$default_initial_severity = $_SESSION['violation_category_information']['trusted_violation|category|initial_severity'];
						} else {
							$default_initial_severity = $violation_category_class -> returnInitialSeverity ($trusted_category_id);
						}
						foreach ($severity_list as $severity) {
							$HTML .= '<option ';
							if ($default_initial_severity == $severity) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formTextarea ('violation|category|description', $_HOAM_language ['field_names']['violation']['category']['description'], $_SESSION['violation_category_information']['trusted_violation|category|description'], $violation_category_class -> returnDescription ($trusted_category_id), $_SESSION['current_user']['edit']['row'], $violation_category_class -> returnColumnSize ('description'));
						$HTML .= HOAM_formTextarea ('violation|category|detail', $_HOAM_language ['field_names']['violation']['category']['detail'], $_SESSION['violation_category_information']['trusted_violation|category|detail'], $violation_category_class -> returnDetail ($trusted_category_id), $_SESSION['current_user']['edit']['row'], $violation_category_class -> returnColumnSize ('detail'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Edit Category Tab

						// Insert list of existing categories
						$HTML .= HOAM_violationShowExistingCategoryList ();

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/violation/category/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['category']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['category']['list'] . '</h1>';
				$colgroup = '<colgroup>';
				$colgroup .= '<col width="50%" />';
				$colgroup .= '<col width="10%" />';
				$colgroup .= '<col width="10%" />';
				$colgroup .= '<col width="15%" />';
				$colgroup .= '<col width="15%" />';
				$colgroup .= '</colgroup>';
				$thead = '<thead>';
				$thead .= '<tr>';
				$thead .= '<th id="sort_name">' . $_HOAM_language ['field_names']['violation']['category']['name'] . '</th>';
				$thead .= '<th id="sort_severity">' . $_HOAM_language ['tables']['column']['violation']['category']['initial_severity'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th id="sort_created">' . $_HOAM_language ['field_names']['generic']['date']['created'] . '</th>';
				$thead .= '<th id="sort_modified">' . $_HOAM_language ['field_names']['generic']['date']['modified'] . '</th>';
				$thead .= '</tr>';
				$thead .= '</thead>';
				$tfoot = '<tfoot>';
				$tfoot .= '<tr>';
				$tfoot .= '<th>' . $_HOAM_language ['field_names']['violation']['category']['name'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['violation']['category']['initial_severity'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['field_names']['generic']['date']['created'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['field_names']['generic']['date']['modified'] . '</th>';
				$tfoot .= '</tfoot>';
				$tbody = '<tbody id="violation_category_list_tbody">';
				$category_list = $violation_category_class -> ListCategoryAll ();
				if (is_array ($category_list)) {
					foreach ($category_list as $category) {
						$category_info = $violation_category_class -> ListAll ($category[0]);
						$category_info = $category_info[0];
						$tbody .= '<tr>';
						if ($category[1] == '1') {
							$tbody .= '<td style="padding-left: 3em; text-align: left">' . html_encode (stripslashes ($category_info['category'])) . '</td>';
						} else {
							$tbody .= '<td style="font-weight: bold; text-align: left">' . html_encode (stripslashes ($category_info['category'])) . '</td>';
						}
						$tbody .= '<td>' . $violation_severity_class -> returnSeverity ($category_info['initialseverity']) . '</td>';
						$tbody .= '<td>';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/violation/category/edit/' . $category_info['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a class="error" href="/admin/violation/category/delete/' . $category_info['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
						}
						$tbody .= '</td>';
						$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($category_info['datecreated'])) . '</td>';
						$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($category_info['datemodified'])) . '</td>';
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="6">' . $_HOAM_language ['errors']['violation']['category']['none-found'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= '<table class="center">' . $colgroup . $thead . $tfoot . '<tbody>'. $tbody . '<tbody></table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2]) OR ($GLOBALS['global_url_array'][2] == 'category')) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['violation'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['violation_category'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/house.png" alt="' . $_HOAM_language ['field_names']['admin']['violation_category'] . '" class="LHS" /><a href="/admin/violation/category/">' . $_HOAM_language ['field_names']['admin']['violation_category'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/violation/category/add/">' . $_HOAM_language ['field_names']['violation']['category']['add'] . '</a></li>';
					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/violation/category/list/">' . $_HOAM_language ['field_names']['violation']['category']['list'] . '</a></li>';
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminViolationSeverity ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting, $_HOAM_queue_head;

	require_once (HOAM_PATH_FUNCTION . 'violations.php');
	
	$group_members_class = new HOAM_group_members ('group_members');
	$user_class = new HOAM_user ('users');
	$violation_category_class = new HOAM_violation_category ('violation_category');
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');

	switch ($GLOBALS['global_url_array'][3]) {
		case ('add'):
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['severity']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminViolationJS');
				$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['severity']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				// OK, we're checking to see that the severity we're trying to add is the one that we
				// have information on in the violation_severity_information variable.
				// If the referrer doesn't match this current page, then we know we came from
				// somewhere else on the site, and thus the information is suspect.
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['violation_severity_information'])) {
						unset ($_SESSION['violation_severity_information']);
					}
				}

				$HTML .= '<form id="violation_add" action="/hoam/scripts/violation/violation_severity_edit.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" name="add">' . $_HOAM_language ['form_buttons']['violation']['severity']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="violation_severity_information" title="' . $_HOAM_language ['form_tabs']['violation']['severity']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formField ('violation|severity|name', $_HOAM_language ['field_names']['violation']['severity']['name'], $_SESSION['violation_severity_information']['trusted_violation|severity|name'], '', $violation_severity_class -> returnColumnSize ('severity'));
				$HTML .= HOAM_formField ('violation|severity|numdays', $_HOAM_language ['field_names']['violation']['severity']['numdays'], $_SESSION['violation_severity_information']['trusted_violation|severity|numdays'], '', $violation_severity_class -> returnColumnSize ('numdays'));
				$HTML .= HOAM_formField ('violation|severity|fine_per_notice', $_HOAM_language ['field_names']['violation']['severity']['fine_per_notice'], $_SESSION['violation_severity_information']['trusted_violation|severity|fine_per_notice'], '', $violation_severity_class -> returnColumnSize ('fine_per_notice'));
				$HTML .= HOAM_formField ('violation|severity|fine_per_day', $_HOAM_language ['field_names']['violation']['severity']['fine_per_day'], $_SESSION['violation_severity_information']['trusted_violation|severity|fine_per_day'], '', $violation_severity_class -> returnColumnSize ('fine_per_day'));
				$HTML .= HOAM_formField ('violation|severity|fine_interest', $_HOAM_language ['field_names']['violation']['severity']['fine_interest'], $_SESSION['violation_severity_information']['trusted_violation|severity|fine_interest'], '', $violation_severity_class -> returnColumnSize ('fine_interest'));
				$HTML .= '<br />';
				$HTML .= HOAM_formCheckbox ('violation|severity|assume_resolved', $_HOAM_language ['field_names']['violation']['flags']['assume_resolved'], 1, $_SESSION['violation_severity_information']['trusted_violation|severity|assumed_resolved'], 0);
				$HTML .= '<br />';
				$HTML .= '<br />';
				$HTML .= HOAM_formSelect ('violation|severity|escalate', $_HOAM_language ['field_names']['violation']['severity']['escalate']);
				$severity_list = $violation_severity_class -> listSeverity ();
				if (isset ($_SESSION['violation_severity_information']['trusted_violation|severity|escalate'])) {
					$default_escalation = $_SESSION['violation_severity_information']['trusted_violation|severity|escalate'];
				} else {
					$default_escalation = NULL;
				}
				foreach ($severity_list as $severity) {
					$HTML .= '<option ';
					if ($default_escalation == $severity) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formTextarea ('violation|severity|preamble', $_HOAM_language ['field_names']['violation']['severity']['preamble'], $_SESSION['violation_severity_information']['trusted_violation|severity|preamble'], '', $_SESSION['current_user']['edit']['row'], $violation_severity_class -> returnColumnSize ('preamble'));
				$HTML .= HOAM_formTextarea ('violation|severity|closing', $_HOAM_language ['field_names']['violation']['severity']['closing'], $_SESSION['violation_severity_information']['trusted_violation|severity|closing'], '', $_SESSION['current_user']['edit']['row'], $violation_severity_class -> returnColumnSize ('closing'));
				$HTML .= '</div>';
				$HTML .= '</div>'; // End New Severity Tab

				// Insert list of existing categories
				$HTML .= HOAM_violationShowExistingSeverityList ();

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'delete' :
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_severity_id = $violation_severity_class -> superClean ($GLOBALS['global_url_array'][4], $violation_severity_class -> returnColumnSize ('id'));
					if ($violation_severity_class -> idExists ($trusted_severity_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['severity']['delete'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminViolationJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['severity']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the severity we're trying to delete is the one that we
						// have information on in the violation_severity_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['violation_severity_information'])) {
								unset ($_SESSION['violation_severity_information']);
							}
						}

						$HTML .= '<form id="violation_severity_delete" action="/hoam/scripts/violation/violation_severity_edit.php" enctype="multipart/form-data" method="post">';
						$HTML .= '<input type="hidden" name="violation|severity|id" value="' . $trusted_severity_id . '" />';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="delete">' . $_HOAM_language ['form_buttons']['violation']['severity']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="violation_severity_information" title="' . $_HOAM_language ['form_tabs']['violation']['severity']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formFieldDisabled ('violation|severity|name', $_HOAM_language ['field_names']['violation']['severity']['name'], $violation_severity_class -> returnSeverity ($trusted_severity_id), $violation_severity_class -> returnColumnSize ('severity'));
						$HTML .= HOAM_formFieldDisabled ('violation|severity|numdays', $_HOAM_language ['field_names']['violation']['severity']['numdays'], $violation_severity_class -> returnNumdays ($trusted_severity_id), $violation_severity_class -> returnColumnSize ('numdays'));
						$HTML .= HOAM_formFieldDisabled ('violation|severity|fine_per_notice', $_HOAM_language ['field_names']['violation']['severity']['fine_per_notice'], number_format ($violation_severity_class -> returnFinePerNotice ($trusted_severity_id), 2), $violation_severity_class -> returnColumnSize ('fine_per_notice'));
						$HTML .= HOAM_formFieldDisabled ('violation|severity|fine_per_day', $_HOAM_language ['field_names']['violation']['severity']['fine_per_day'], number_format ($violation_severity_class -> returnFinePerDay ($trusted_severity_id), 2), $violation_severity_class -> returnColumnSize ('fine_per_day'));
						$HTML .= HOAM_formFieldDisabled ('violation|severity|fine_interest', $_HOAM_language ['field_names']['violation']['severity']['fine_interest'], number_format ($violation_severity_class -> returnFineInterest ($trusted_severity_id), 2), $violation_severity_class -> returnColumnSize ('fine_interest'));
						$HTML .= '<br />';
						$HTML .= HOAM_formCheckboxDisabled ('violation|severity|assume_resolved', $_HOAM_language ['field_names']['violation']['flags']['assume_resolved'], 1, $violation_severity_class -> isBitSet ($trusted_severity_id, $_HOAM_setting ['violation']['flags']['assume_resolved']));
						$HTML .= '<br />';
						$HTML .= '<br />';
						$HTML .= HOAM_formSelectDisabled ('violation|severity|escalate', $_HOAM_language ['field_names']['violation']['severity']['escalate']);
						$severity_list = $violation_severity_class -> listSeverity ();
						if (isset ($_SESSION['violation_severity_information']['trusted_violation|severity|escalate'])) {
							$default_escalation = $_SESSION['violation_severity_information']['trusted_violation|severity|escalate'];
						} else {
							$default_escalation = $violation_severity_class -> returnEscalate ($trusted_severity_id);
						}
						foreach ($severity_list as $severity) {
							$HTML .= '<option ';
							if ($default_escalation == $severity) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formTextareaDisabled  ('violation|severity|preamble', $_HOAM_language ['field_names']['violation']['severity']['preamble'], $_SESSION['violation_severity_information']['trusted_violation|severity|preamble'], $violation_severity_class -> returnPreamble ($trusted_severity_id), $_SESSION['current_user']['edit']['row'], $violation_severity_class -> returnColumnSize ('preamble'));
						$HTML .= HOAM_formTextareaDisabled ('violation|severity|closing', $_HOAM_language ['field_names']['violation']['severity']['closing'], $_SESSION['violation_severity_information']['trusted_violation|severity|closing'], $violation_severity_class -> returnClosing ($trusted_severity_id), $_SESSION['current_user']['edit']['row'], $violation_severity_class -> returnColumnSize ('closing'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Delete Severity Tab

						// Insert list of existing categories
						$HTML .= HOAM_violationShowExistingSeverityList ();

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/violation/severity/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case 'edit' :
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][4]) {
					$trusted_severity_id = $violation_severity_class -> superClean ($GLOBALS['global_url_array'][4], $violation_severity_class -> returnColumnSize ('id'));
					if ($violation_severity_class -> idExists ($trusted_severity_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['severity']['edit'])));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminViolationJS');
						$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['severity']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						// OK, we're checking to see that the severity we're trying to edit is the one that we
						// have information on in the violation_severity_information variable.
						// If the referrer doesn't match this current page, then we know we came from
						// somewhere else on the site, and thus the information is suspect.
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['violation_severity_information'])) {
								unset ($_SESSION['violation_severity_information']);
							}
						}

						$HTML .= '<form id="violation_severity_edit" action="/hoam/scripts/violation/violation_severity_edit.php" enctype="multipart/form-data" method="post">';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<input type="hidden" name="violation|severity|id" value="' . $trusted_severity_id . '" />';
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
						$HTML .= '<div class="tabber">';

						$HTML .= '<div class="tabbertab" id="violation_severity_information" title="' . $_HOAM_language ['form_tabs']['violation']['severity']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formField ('violation|severity|name', $_HOAM_language ['field_names']['violation']['severity']['name'], $_SESSION['violation_severity_information']['trusted_violation|severity|name'], $violation_severity_class -> returnSeverity ($trusted_severity_id), $violation_severity_class -> returnColumnSize ('severity'));
						$HTML .= HOAM_formField ('violation|severity|numdays', $_HOAM_language ['field_names']['violation']['severity']['numdays'], $_SESSION['violation_severity_information']['trusted_violation|severity|numdays'], $violation_severity_class -> returnNumdays ($trusted_severity_id), $violation_severity_class -> returnColumnSize ('numdays'));
						$HTML .= HOAM_formField ('violation|severity|fine_per_notice', $_HOAM_language ['field_names']['violation']['severity']['fine_per_notice'], $_SESSION['violation_severity_information']['trusted_violation|severity|fine_per_notice'], number_format ($violation_severity_class -> returnFinePerNotice ($trusted_severity_id), 2), $violation_severity_class -> returnColumnSize ('fine_per_notice'));
						$HTML .= HOAM_formField ('violation|severity|fine_per_day', $_HOAM_language ['field_names']['violation']['severity']['fine_per_day'], $_SESSION['violation_severity_information']['trusted_violation|severity|fine_per_day'], number_format ($violation_severity_class -> returnFinePerDay ($trusted_severity_id), 2), $violation_severity_class -> returnColumnSize ('fine_per_day'));
						$HTML .= HOAM_formField ('violation|severity|fine_interest', $_HOAM_language ['field_names']['violation']['severity']['fine_interest'], $_SESSION['violation_severity_information']['trusted_violation|severity|fine_interest'], number_format ($violation_severity_class -> returnFineInterest ($trusted_severity_id), 2), $violation_severity_class -> returnColumnSize ('fine_interest'));
						$HTML .= '<br />';
						$HTML .= HOAM_formCheckbox ('violation|severity|assume_resolved', $_HOAM_language ['field_names']['violation']['flags']['assume_resolved'], 1, $_SESSION['violation_severity_information']['trusted_violation|severity|assumed_resolved'], $violation_severity_class -> isBitSet ($trusted_severity_id, $_HOAM_setting ['violation']['flags']['assume_resolved']));
						$HTML .= '<br />';
						$HTML .= '<br />';
						$HTML .= HOAM_formSelect ('violation|severity|escalate', $_HOAM_language ['field_names']['violation']['severity']['escalate']);
						$severity_list = $violation_severity_class -> listSeverity ();
						if (isset ($_SESSION['violation_severity_information']['trusted_violation|severity|escalate'])) {
							$default_escalation = $_SESSION['violation_severity_information']['trusted_violation|severity|escalate'];
						} else {
							$default_escalation = $violation_severity_class -> returnEscalate ($trusted_severity_id);
						}
						foreach ($severity_list as $severity) {
							$HTML .= '<option ';
							if ($default_escalation == $severity) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formTextarea ('violation|severity|preamble', $_HOAM_language ['field_names']['violation']['severity']['preamble'], $_SESSION['violation_severity_information']['trusted_violation|severity|preamble'], $violation_severity_class -> returnPreamble ($trusted_severity_id), $_SESSION['current_user']['edit']['row'], $violation_severity_class -> returnColumnSize ('preamble'));
						$HTML .= HOAM_formTextarea ('violation|severity|closing', $_HOAM_language ['field_names']['violation']['severity']['closing'], $_SESSION['violation_severity_information']['trusted_violation|severity|closing'], $violation_severity_class -> returnClosing ($trusted_severity_id), $_SESSION['current_user']['edit']['row'], $violation_severity_class -> returnColumnSize ('closing'));
						$HTML .= '</div>';
						$HTML .= '</div>'; // End Edit Severity Tab

						// Insert list of existing categories
						$HTML .= HOAM_violationShowExistingSeverityList ();

						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/violation/severity/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_view'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['severity']['list'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['violation']['severity']['list'] . '</h1>';
				$colgroup = '<colgroup>';
				$colgroup .= '</colgroup>';
				$thead = '<thead>';
				$thead .= '<tr>';
				$thead .= '<th id="sort_name">' . $_HOAM_language ['field_names']['violation']['severity']['name'] . '</th>';
				$thead .= '<th id="sort_numdays">' . $_HOAM_language ['field_names']['violation']['severity']['numdays'] . '</th>';
				$thead .= '<th id="sort_escalate">' . $_HOAM_language ['field_names']['violation']['severity']['escalate'] . '</th>';
				$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$thead .= '<th id="sort_author">' . $_HOAM_language ['tables']['column']['generic']['author'] . '</th>';
				$thead .= '<th id="sort_created">' . $_HOAM_language ['field_names']['generic']['date']['created'] . '</th>';
				$thead .= '<th id="sort_modified">' . $_HOAM_language ['field_names']['generic']['date']['modified'] . '</th>';
				$thead .= '</tr>';
				$thead .= '</thead>';
				$tfoot = '<tfoot>';
				$tfoot .= '<tr>';
				$tfoot .= '<th>' . $_HOAM_language ['field_names']['violation']['severity']['name'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['field_names']['violation']['severity']['numdays'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['field_names']['violation']['severity']['escalate'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['generic']['author'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['field_names']['generic']['date']['created'] . '</th>';
				$tfoot .= '<th>' . $_HOAM_language ['field_names']['generic']['date']['modified'] . '</th>';
				$tfoot .= '</tr>';
				$tfoot .= '</tfoot>';
				$tbody = '<tbody id="violation_severity_list_tbody">';
				$severity_list = $violation_severity_class -> ListSeverity ();
				if (is_array ($severity_list)) {
					foreach ($severity_list as $severity) {
						$severity_info = $violation_severity_class -> ListAll ($severity);
						// We reassign it to itself, because the ListAll() function assumes you're retrieving multiple rows.
						$severity_info = $severity_info[0];

						$tbody .= '<tr>';
						$tbody .= '<td>' . $severity_info['severity'] . '</td>';
						$tbody .= '<td>' . $severity_info['numdays'] . '</td>';
						$tbody .= '<td>' . $violation_severity_class -> returnSeverity ($severity_info['escalate']) . '</td>';
						$tbody .= '<td>';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a href="/admin/violation/severity/edit/' . $severity . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
						}
						$tbody .= ' &nbsp; ';
						if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
							$tbody .= '<small><a class="error" href="/admin/violation/severity/delete/' . $severity . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
						}
						$tbody .= '</td>';
						$tbody .= '<td>' . $user_class -> returnAccountName ($severity_info['user_id']) . '</td>';
						$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($severity_info['datecreated'])) . '</td>';
						$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($severity_info['datemodified'])) . '</td>';
						$tbody .= '</tr>';
					}
				} else {
					$tbody .= '<tr><td colspan="6">' . $_HOAM_language ['errors']['violation']['severity']['none-found'] . '</td></tr>';
				}
				$tbody .= '</tbody>';
				$HTML .= '<table class="center">' . $colgroup . $thead . $tfoot . '<tbody>'. $tbody . '<tbody></table>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2]) OR ($GLOBALS['global_url_array'][2] == 'severity')) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['violation'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['violation_severity'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/house.png" alt="' . $_HOAM_language ['field_names']['admin']['violation_severity'] . '" class="LHS" /><a href="/admin/violation/severity/">' . $_HOAM_language ['field_names']['admin']['violation_severity'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/violation/severity/add/">' . $_HOAM_language ['field_names']['violation']['severity']['add'] . '</a></li>';
					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')"><a href="/admin/violation/severity/list/">' . $_HOAM_language ['field_names']['violation']['severity']['list'] . '</a></li>';
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminWorkRequest ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_setting, $_HOAM_queue_head;
	
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$user_class = new HOAM_user ('users');
	$work_request_class = new HOAM_work_request ('work_requests');

	switch ($GLOBALS['global_url_array'][2]) {
		case ('add'):
			$_HOAM_log -> add ('Loading /admin/work_request/add/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['work_request']['add'])));
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
				HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminWorkRequestJS');
				
				$HTML = '<h1>' . $_HOAM_language ['field_names']['work_request']['add'] . '</h1>';
				require_once (HOAM_PATH_FUNCTION . 'forms.php');
				if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
					if (isset ($_SESSION['error_class_item'])) {
						unset ($_SESSION['error_class_item']);
					}
					if (isset ($_SESSION['request_information'])) {
						unset ($_SESSION['request_information']);
					}
				}
				$HTML .= '<form id="request_add" action="/hoam/scripts/work_request/edit_script.php" enctype="multipart/form-data" method="post">';
				if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
					$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
				}
				$HTML .= '<fieldset>';
				$HTML .= '<button type="submit" id="work_request|add" name="add">' . $_HOAM_language ['form_buttons']['work_request']['add'] . '</button>';
				$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
				$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
				$HTML .= '</fieldset>';
				$HTML .= $_HOAM_language ['help_popups']['mandatory'];
				
				$HTML .= '<div class="tabber">';

				$HTML .= '<div class="tabbertab" id="request_information" title="' . $_HOAM_language ['form_tabs']['work_request']['add'] . '">';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formSelect ('work_request|address', $_HOAM_language ['field_names']['work_request']['address']);
				if (isset ($_SESSION['request_information']['trusted_work_request|address'])) {
					$default_lot = $_SESSION['request_information']['trusted_work_request|address'];
				} else {
					$default_lot = NULL;
				}
				if (isset ($_HOAM_setting ['lot']['common']['area']) OR ($default_lot == 'common_area')) {
					$HTML .= '<option ';
					if ($default_lot == 'common_area') {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="common_area">' . $_HOAM_language ['field_names']['lot']['common']['area'] . '</option>';
				}
				if (isset ($_HOAM_setting ['lot']['common']['clubhouse']) OR ($default_lot == 'common_clubhouse')) {
					$HTML .= '<option ';
					if ($default_lot == 'common_clubhouse') {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="common_clubhouse">' . $_HOAM_language ['field_names']['lot']['common']['clubhouse'] . '</option>';
				}
				if (isset ($_HOAM_setting ['lot']['common']['pool']) OR ($default_lot == 'common_pool')) {
					$HTML .= '<option ';
					if ($default_lot == 'common_pool') {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="common_pool">' . $_HOAM_language ['field_names']['lot']['common']['pool'] . '</option>';
				}
				$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
				foreach ($lot_list as $lot) {
					$HTML .= '<option ';
					if ($lot['id'] == $default_lot) {
						$HTML .= 'selected ';
					}
					$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
					$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($lot['id'])))) . ')';
					$HTML .= '</option>';
					}
				$HTML .= '</select>';
				
				$HTML .= HOAM_formText ('work_request|title', $_HOAM_language ['field_names']['work_request']['title'], $_SESSION['request_information']['trusted_work_request|title'], '', $work_request_class -> returnColumnSize ('title'));
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formCheckbox ('work_request|child', $_HOAM_language ['field_names']['work_request']['child'], 1, $_SESSION['request_information']['trusted_work_request|child'], '');
				$HTML .= '<br /><br />';
				$HTML .= '<fieldset id="parent_id">';
				$HTML .= HOAM_formSelect ('work_request|parent', $_HOAM_language ['field_names']['work_request']['parent']);
				if (isset ($_SESSION['request_information']['trusted_work_request|parent'])) {
					$default_option = $_SESSION['request_information']['trusted_work_request|parent'];
				} else {
					$default_option = NULL;
				}
				$request_list = $work_request_class -> listDue (0, 0, $_HOAM_setting ['work_request']['flags']['new'] + $_HOAM_setting ['work_request']['flags']['assigned'] + $_HOAM_setting ['work_request']['flags']['in_progress'] + $_HOAM_setting ['work_request']['flags']['on_hold']);
				$HTML .= '<option value="">' . $_HOAM_language ['common']['please-select'] . '</option>';
				foreach ($request_list as $request) {
					// Not allowed to create a child of a child, or
					// assign itself as a child.
					if (($request[1] != '1') AND ($trusted_id != $request[0])) {
						$HTML .= '<option ';
						if ($request[0] == $default_option) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $request[0] . '">';
						if ($request[1]) {
							$HTML .= '&nbsp; &#9492;&#9472; &nbsp;';
						}
						$HTML .= HOAM_returnTruncatedString (html_encode (stripslashes ($work_request_class -> returnTitle ($request[0]))), 40);
						$HTML .= '</option>';
					}
				}
				$HTML .= '</select>';
				$HTML .= '</fieldset>';
				$HTML .= '</div>';
				$HTML .= '<div class="mandatory">';
				$HTML .= HOAM_formTextarea ('work_request|description', $_HOAM_language ['field_names']['work_request']['description'], $_SESSION['request_information']['trusted_work_request|description'], '', $_SESSION['current_user']['edit']['row'] / 4, $work_request_class -> returnColumnSize ('description'));
				$HTML .= '</div>';
				$HTML .= '<div class="optional">';
				$HTML .= HOAM_formTextarea ('work_request|notes', $_HOAM_language ['field_names']['work_request']['notes'], $_SESSION['request_information']['trusted_work_request|notes'], '', $_SESSION['current_user']['edit']['row'] / 2, $work_request_class -> returnColumnSize ('notes'));
				$HTML .= '<label for="work_request|attachments">' . $_HOAM_language ['field_names']['work_request']['attachments'];
				$HTML .= HOAM_formDisplayHelp ('work_request|attachments');
				$HTML .= HOAM_formDisplayError ('work_request|attachments');
				$HTML .= '</label>';
				$HTML .= '<div>';
				$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
				$HTML .= '</div>';

				$date_due = new DateTime ();
				$date_due -> modify ('+' . 14 . ' day');
				$HTML .= HOAM_formDate ('work_request|date|due', $_HOAM_language ['field_names']['work_request']['date']['due'], $_SESSION['request_information']['trusted_work_request|date|due'], $date_due -> format ($_HOAM_country ['date']['format_reports']));
				$HTML .= HOAM_formSelect ('work_request|priority', $_HOAM_language ['field_names']['work_request']['priority']);
				if (isset ($_SESSION['request_information']['trusted_work_request|priority'])) {
					$default_option = $_SESSION['request_information']['trusted_work_request|priority'];
				} else {
					// Default the priority to Normal.
					$default_option = $_HOAM_setting ['work_request']['flags']['normal'];
				}
				foreach ($_HOAM_language ['priority'] as $priority) {
					$HTML .= '<option ';
					if ($_HOAM_setting ['work_request']['flags'][$priority['value']] == $default_option) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $_HOAM_setting ['work_request']['flags'][$priority['value']] . '">' . $priority['name'] . '</option>';
				}
				$HTML .= '</select>';
				if ($_HOAM_setting ['work_request']['require_approval']) {
					$HTML .= HOAM_formSelect ('work_request|approval', $_HOAM_language ['field_names']['work_request']['approval']);
					if (isset ($_SESSION['request_information']['trusted_work_request|approval'])) {
						$default_option = $_SESSION['request_information']['trusted_work_request|approval'];
					} else {
						$default_option = $_HOAM_setting ['work_request']['flags']['needs_approval'];
					}
					foreach ($_HOAM_language ['approval'] as $approval) {
						$HTML .= '<option ';
						if ($_HOAM_setting ['work_request']['flags'][$approval['value']] == $default_option) {
							$HTML .= 'selected="selected" ';
						}
						$HTML .= 'value="' . $_HOAM_setting ['work_request']['flags'][$approval['value']] . '">' . $approval['name'] . '</option>';
					}
					$HTML .= '</select>';
				}
				$HTML .= HOAM_formSelect ('work_request|status', $_HOAM_language ['field_names']['work_request']['status']);
				if (isset ($_SESSION['request_information']['trusted_work_request|status'])) {
					$default_option = $_SESSION['request_information']['trusted_work_request|status'];
				} else {
					// Default the status to New
					$default_option = $_HOAM_setting ['work_request']['flags']['new'];
				}
				foreach ($_HOAM_language ['status'] as $status) {
					$HTML .= '<option ';
					if ($_HOAM_setting ['work_request']['flags'][$status['value']] == $default_option) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $_HOAM_setting ['work_request']['flags'][$status['value']] . '">' . $status['name'] . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= HOAM_formSelect ('work_request|vendor', $_HOAM_language ['field_names']['work_request']['vendor']);
				if (isset ($_SESSION['request_information']['trusted_work_request|vendor'])) {
					$default_option = $_SESSION['request_information']['trusted_work_request|vendor'];
				} else {
					$default_option = NULL;
				}
				$vendor_list = $budget_vendor_class -> listAll (NULL, 'name ASC'); 
				$HTML .= '<option value="">' . $_HOAM_language ['common']['please-select'] . '</option>';
				foreach ($vendor_list as $vendor) {
					$HTML .= '<option ';
					if ($vendor['id'] == $default_option) {
						$HTML .= 'selected="selected" ';
					}
					$HTML .= 'value="' . $vendor['id'] . '">' . html_encode (stripslashes ($vendor['name'])) . '</option>';
				}
				$HTML .= '</select>';
				$HTML .= '</div>'; // End new request tab

				$HTML .= '</div>'; // End Tabber
				$HTML .= '</form>';
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('delete'):
			$_HOAM_log -> add ('Loading /admin/work_request/delete/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_id = $work_request_class -> superClean ($GLOBALS['global_url_array'][3], $work_request_class -> returnColumnSize ('id'));
					$attachment_class = new HOAM_attachments ('attachments');
					if ($work_request_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['work_request']['delete'] . ' (' . HOAM_returnAddress ($work_request_class -> returnLotId ($trusted_id)) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminWorkRequestJS');
				
						$HTML = '<h1>' . $_HOAM_language ['field_names']['work_request']['delete'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['request_information'])) {
								unset ($_SESSION['request_information']);
							}
						}
						
						if ($work_request_class -> returnStatus ($trusted_id) == $_HOAM_setting ['work_request']['flags']['complete']) {
							$HTML .= sprintf ($_HOAM_language ['help_pages']['work_request']['complete'], date ($_HOAM_country ['date']['format_reports'], strtotime ($work_request_class -> returnDateComplete ($trusted_id))));
						}
						
						$HTML .= '<form id="request_delete" action="/hoam/scripts/work_request/edit_script.php" enctype="multipart/form-data" method="post">';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" id="request|delete" name="delete">' . $_HOAM_language ['form_buttons']['work_request']['delete'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					
						$HTML .= '<div class="tabber">';
	
						$HTML .= '<div class="tabbertab" id="request_information" title="' . $_HOAM_language ['form_tabs']['work_request']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<input type="hidden" name="work_request|id" id="work_request|id" value="' . $trusted_id . '" />';
						$HTML .= HOAM_formSelectDisabled ('work_request|address', $_HOAM_language ['field_names']['work_request']['address']);
						if (isset ($_SESSION['request_information']['trusted_work_request|address'])) {
							$default_lot = $_SESSION['request_information']['trusted_work_request|address'];
						} else {
							$default_lot = $work_request_class -> returnLotId ($trusted_id);
						}
						if ($_HOAM_setting ['lot']['common']['area'] OR ($default_lot == 'common_area')) {
							$HTML .= '<option ';
							if ($default_lot == 'common_area') {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="common_area">' . $_HOAM_language ['field_names']['lot']['common']['area'] . '</option>';
						}
						if ($_HOAM_setting ['lot']['common']['clubhouse'] OR ($default_lot == 'common_clubhouse')) {
							$HTML .= '<option ';
							if ($default_lot == 'common_clubhouse') {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="common_clubhouse">' . $_HOAM_language ['field_names']['lot']['common']['clubhouse'] . '</option>';
						}
						if ($_HOAM_setting ['lot']['common']['pool'] OR ($default_lot == 'common_pool')) {
							$HTML .= '<option ';
							if ($default_lot == 'common_pool') {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="common_pool">' . $_HOAM_language ['field_names']['lot']['common']['pool'] . '</option>';
						}
						$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
						foreach ($lot_list as $lot) {
							$HTML .= '<option ';
							if ($lot['id'] == $default_lot) {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
							$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($lot['id'])))) . ')';
							$HTML .= '</option>';
							}
						$HTML .= '</select>';
				
						$HTML .= HOAM_formTextDisabled ('work_request|title', $_HOAM_language ['field_names']['work_request']['title'], $work_request_class -> returnTitle ($trusted_id));
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formCheckboxDisabled ('work_request|child', $_HOAM_language ['field_names']['work_request']['child'], 1, $work_request_class -> returnParentId ($trusted_id));
						$HTML .= '<br /><br />';
						$HTML .= '<fieldset id="parent_id">';
						$HTML .= HOAM_formSelectDisabled ('work_request|parent', $_HOAM_language ['field_names']['work_request']['parent']);
						if (isset ($_SESSION['request_information']['trusted_work_request|parent'])) {
							$default_option = $_SESSION['request_information']['trusted_work_request|parent'];
						} else {
							$default_option = $work_request_class -> returnParentId ($trusted_id);
						}
						$request_list = $work_request_class -> listDue (0, 0, $_HOAM_setting ['work_request']['flags']['new'] + $_HOAM_setting ['work_request']['flags']['assigned'] + $_HOAM_setting ['work_request']['flags']['in_progress'] + $_HOAM_setting ['work_request']['flags']['on_hold']);
						$HTML .= '<option value="">' . $_HOAM_language ['common']['please-select'] . '</option>';
						foreach ($request_list as $request) {
							// Not allowed to create a child of a child, or
							// assign itself as a child.
							if (($request[1] != '1') AND ($trusted_id != $request[0])) {
								$HTML .= '<option ';
								if ($request[0] == $default_option) {
									$HTML .= 'selected="selected" ';
								}
								$HTML .= 'value="' . $request[0] . '">';
								if ($request[1]) {
									$HTML .= '&nbsp; &#9492;&#9472; &nbsp;';
								}
								$HTML .= HOAM_returnTruncatedString (html_encode (stripslashes ($work_request_class -> returnTitle ($request[0]))), 40);
								$HTML .= '</option>';
							}
						}
						$HTML .= '</select>';
						$HTML .= '</fieldset>';						
						$HTML .= HOAM_formTextareaDisabled ('work_request|description', $_HOAM_language ['field_names']['work_request']['description'], $work_request_class -> returnDescription ($trusted_id), $_SESSION['current_user']['edit']['row'] / 4);
						$HTML .= HOAM_formTextareaDisabled ('work_request|notes', $_HOAM_language ['field_names']['work_request']['notes'], $work_request_class -> returnNotes ($trusted_id), $_SESSION['current_user']['edit']['row'] / 2);

						$HTML .= '<label for="work_request|attachments">' . $_HOAM_language ['field_names']['work_request']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('work_request|attachments');
						$HTML .= HOAM_formDisplayError ('work_request|attachments');
						$HTML .= '</label>';
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_id, $_HOAM_setting ['attachment']['flags']['work_request']);
						$HTML .= '<div>';
						if (!empty ($attachment_list)) {
							$year = date ('Y', strtotime ($attachment_class -> returnDateCreated ($attachment_list[0])));
							$HTML .= '<span style="font-weight: bold" field="year">&#x0229F; ' . $year . '</span>';
							$HTML .= '<div>';
							foreach ($attachment_list as $attachment) {
								if (mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4) == $year) {
								} else {
									$year = mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4);
									$HTML .= '</div>';
									$HTML .= '<span style="clear: left; display: block" field="year">&#x0229E; ' . $year . '</span>';
									$HTML .= '<div style="display: none">';
								}
								$HTML .= HOAM_attachmentReturnHTML ($attachment);
							}
							$HTML .= '</div>';
							$HTML .= '<br class="clear" />';
						}
						$HTML .= '<input disabled="disabled" type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';

						$date_due = new DateTime ();
						$date_due -> modify ('+' . 14 . ' day');
						$HTML .= HOAM_formDateDisabled ('work_request|date|due', $_HOAM_language ['field_names']['work_request']['date']['due'], HOAM_date (date ($_HOAM_country ['date']['format_reports'], strtotime ($work_request_class -> returnDateDue ($trusted_id)))), $date_due -> format ($_HOAM_country ['date']['format_reports']));
						$HTML .= HOAM_formSelectDisabled ('work_request|priority', $_HOAM_language ['field_names']['work_request']['priority']);
						$default_option = $work_request_class -> returnPriority ($trusted_id);
						foreach ($_HOAM_language ['priority'] as $priority) {
							$HTML .= '<option ';
							if ($_HOAM_setting ['work_request']['flags'][$priority['value']] == $default_option) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $_HOAM_setting ['work_request']['flags'][$priority['value']] . '">' . $priority['name'] . '</option>';
						}
						$HTML .= '</select>';
						if ($_HOAM_setting ['work_request']['require_approval']) {
							$HTML .= HOAM_formSelectDisabled ('work_request|approval', $_HOAM_language ['field_names']['work_request']['approval']);
							if (isset ($_SESSION['request_information']['trusted_work_request|approval'])) {
								$default_option = $_SESSION['request_information']['trusted_work_request|approval'];
							} else {
								$default_option = $work_request_class -> returnFlags ($trusted_id);
							}
							foreach ($_HOAM_language ['approval'] as $approval) {
								$HTML .= '<option ';
								if ($_HOAM_setting ['work_request']['flags'][$approval['value']] == $default_option) {
									$HTML .= 'selected="selected" ';
								}
								$HTML .= 'value="' . $_HOAM_setting ['work_request']['flags'][$approval['value']] . '">' . $approval['name'] . '</option>';
							}
							$HTML .= '</select>';
						}
						$HTML .= HOAM_formSelectDisabled ('work_request|status', $_HOAM_language ['field_names']['work_request']['status']);
						$default_option = $work_request_class -> returnStatus ($trusted_id);
						foreach ($_HOAM_language ['status'] as $status) {
							$HTML .= '<option ';
							if ($_HOAM_setting ['work_request']['flags'][$status['value']] == $default_option) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $_HOAM_setting ['work_request']['flags'][$status['value']] . '">' . $status['name'] . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formSelectDisabled ('work_request|vendor', $_HOAM_language ['field_names']['work_request']['vendor']);
						$default_option = $work_request_class -> returnVendorId ($trusted_id);
						$vendor_list = $budget_vendor_class -> listAll (NULL, 'name ASC'); 
						$HTML .= '<option value="">' . $_HOAM_language ['common']['please-select'] . '</option>';
						foreach ($vendor_list as $vendor) {
							$HTML .= '<option ';
							if ($vendor['id'] == $default_option) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $vendor['id'] . '">' . html_encode (stripslashes ($vendor['name'])) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</div>'; // End new request tab
	
						$HTML .= '<div class="tabbertab" id="request_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End History tab
	
						$HTML .= '</div>'; // End Tabber
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/work_request/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('edit'):
			$_HOAM_log -> add ('Loading /admin/work_request/edit/ page.', HOAM_MESSAGE_DEBUG);
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_id = $work_request_class -> superClean ($GLOBALS['global_url_array'][3], $work_request_class -> returnColumnSize ('id'));
					$attachment_class = new HOAM_attachments ('attachments');
					if ($work_request_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['work_request']['edit'] . ' (' . HOAM_returnAddress ($work_request_class -> returnLotId ($trusted_id)) . ')')));
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminAttachmentJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminCalendarJS');
						HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminWorkRequestJS');
				
						$HTML = '<h1>' . $_HOAM_language ['field_names']['work_request']['edit'] . '</h1>';
						require_once (HOAM_PATH_FUNCTION . 'forms.php');
						if ((isset ($_SERVER['HTTP_REFERER']) AND ($_SERVER['HTTP_REFERER'] != ('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])))) {
							if (isset ($_SESSION['error_class_item'])) {
								unset ($_SESSION['error_class_item']);
							}
							if (isset ($_SESSION['request_information'])) {
								unset ($_SESSION['request_information']);
							}
						}

						if ($work_request_class -> returnStatus ($trusted_id) == $_HOAM_setting ['work_request']['flags']['complete']) {
							$HTML .= sprintf ($_HOAM_language ['help_pages']['work_request']['complete'], date ($_HOAM_country ['date']['format_reports'], strtotime ($work_request_class -> returnDateComplete ($trusted_id))));
						}
						
						$HTML .= '<form id="request_edit" action="/hoam/scripts/work_request/edit_script.php" enctype="multipart/form-data" method="post">';
						if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
							$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
						}
						$HTML .= '<fieldset>';
						$HTML .= '<button type="submit" id="modify" name="modify">' . $_HOAM_language ['form_buttons']['submit'] . '</button>';
						$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
						$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
						$HTML .= '</fieldset>';
						$HTML .= $_HOAM_language ['help_popups']['mandatory'];
					
						$HTML .= '<div class="tabber">';
	
						$HTML .= '<div class="tabbertab" id="request_information" title="' . $_HOAM_language ['form_tabs']['work_request']['information'] . '">';
						$HTML .= '<div class="mandatory">';
						$HTML .= '<input type="hidden" name="work_request|id" id="work_request|id" value="' . $trusted_id . '" />';
						$HTML .= HOAM_formSelect ('work_request|address', $_HOAM_language ['field_names']['work_request']['address']);
						if (isset ($_SESSION['request_information']['trusted_work_request|address'])) {
							$default_lot = $_SESSION['request_information']['trusted_work_request|address'];
						} else {
							$default_lot = $work_request_class -> returnLotId ($trusted_id);
						}
						if (isset ($_HOAM_setting ['lot']['common']['area']) OR ($default_lot == 'common_area')) {
							$HTML .= '<option ';
							if ($default_lot == 'common_area') {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="common_area">' . $_HOAM_language ['field_names']['lot']['common']['area'] . '</option>';
						}
						if (isset ($_HOAM_setting ['lot']['common']['clubhouse']) OR ($default_lot == 'common_clubhouse')) {
							$HTML .= '<option ';
							if ($default_lot == 'common_clubhouse') {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="common_clubhouse">' . $_HOAM_language ['field_names']['lot']['common']['clubhouse'] . '</option>';
						}
						if (isset ($_HOAM_setting ['lot']['common']['pool']) OR ($default_lot == 'common_pool')) {
							$HTML .= '<option ';
							if ($default_lot == 'common_pool') {
								$HTML .= 'selected ';
							}
							$HTML .= 'value="common_pool">' . $_HOAM_language ['field_names']['lot']['common']['pool'] . '</option>';
						}
						$lot_list = $lot_class -> ListAll (NULL, 'street, address, building, suite');
						foreach ($lot_list as $lot) {
							$HTML .= '<option ';
							if ($lot['id'] == $default_lot) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $lot['id'] . '">' . HOAM_returnAddress ($lot);
							$HTML .= ' (' . html_encode (stripslashes ($homeowner_class -> returnName ($homeowner_class -> returnCurrentOwner ($lot['id'])))) . ')';
							$HTML .= '</option>';
							}
						$HTML .= '</select>';
										
						$HTML .= HOAM_formText ('work_request|title', $_HOAM_language ['field_names']['work_request']['title'], $_SESSION['request_information']['trusted_work_request|title'], $work_request_class -> returnTitle ($trusted_id), $work_request_class -> returnColumnSize ('title'));
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formCheckbox ('work_request|child', $_HOAM_language ['field_names']['work_request']['child'], 1, $_SESSION['request_information']['trusted_work_request|child'], $work_request_class -> returnParentId ($trusted_id));
						$HTML .= '<br /><br />';
						$HTML .= '<fieldset id="parent_id">';
						$HTML .= HOAM_formSelect ('work_request|parent', $_HOAM_language ['field_names']['work_request']['parent']);
						if (isset ($_SESSION['request_information']['trusted_work_request|parent'])) {
							$default_option = $_SESSION['request_information']['trusted_work_request|parent'];
						} else {
							$default_option = $work_request_class -> returnParentId ($trusted_id);
						}
						$request_list = $work_request_class -> listDue (0, 0, $_HOAM_setting ['work_request']['flags']['new'] + $_HOAM_setting ['work_request']['flags']['assigned'] + $_HOAM_setting ['work_request']['flags']['in_progress'] + $_HOAM_setting ['work_request']['flags']['on_hold']);
						$HTML .= '<option value="">' . $_HOAM_language ['common']['please-select'] . '</option>';
						foreach ($request_list as $request) {
							// Not allowed to create a child of a child, or
							// assign itself as a child.
							if (($request[1] != '1') AND ($trusted_id != $request[0])) {
								$HTML .= '<option ';
								if ($request[0] == $default_option) {
									$HTML .= 'selected="selected" ';
								}
								$HTML .= 'value="' . $request[0] . '">';
								if ($request[1]) {
									$HTML .= '&nbsp; &#9492;&#9472; &nbsp;';
								}
								$HTML .= HOAM_returnTruncatedString (html_encode (stripslashes ($work_request_class -> returnTitle ($request[0]))), 40);
								$HTML .= '</option>';
							}
						}
						$HTML .= '</select>';
						$HTML .= '</fieldset>';
						$HTML .= '</div>';
						$HTML .= '<div class="mandatory">';
						$HTML .= HOAM_formTextarea ('work_request|description', $_HOAM_language ['field_names']['work_request']['description'], $_SESSION['request_information']['trusted_work_request|description'], $work_request_class -> returnDescription ($trusted_id), $_SESSION['current_user']['edit']['row'] / 4, $work_request_class -> returnColumnSize ('description'));
						$HTML .= '</div>';
						$HTML .= '<div class="optional">';
						$HTML .= HOAM_formTextarea ('work_request|notes', $_HOAM_language ['field_names']['work_request']['notes'], $_SESSION['request_information']['trusted_work_request|notes'], $work_request_class -> returnNotes ($trusted_id), $_SESSION['current_user']['edit']['row'] / 2, $work_request_class -> returnColumnSize ('notes'));

						$HTML .= '<label for="work_request|attachments">' . $_HOAM_language ['field_names']['work_request']['attachments'];
						$HTML .= HOAM_formDisplayHelp ('work_request|attachments');
						$HTML .= HOAM_formDisplayError ('work_request|attachments');
						$HTML .= '</label>';
						$attachments = new HOAM_attachments ('attachments');
						$attachment_list = $attachments -> listWithOwner ($trusted_id, $_HOAM_setting ['attachment']['flags']['work_request']);
						$HTML .= '<div>';
						if (!empty ($attachment_list)) {
							$year = date ('Y', strtotime ($attachment_class -> returnDateCreated ($attachment_list[0])));
							$HTML .= '<span style="font-weight: bold" field="year">&#x0229F; ' . $year . '</span>';
							$HTML .= '<div>';
							foreach ($attachment_list as $attachment) {
								if (mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4) == $year) {
								} else {
									$year = mb_substr ($attachment_class -> returnDateCreated ($attachment), 0, 4);
									$HTML .= '</div>';
									$HTML .= '<span style="clear: left; display: block" field="year">&#x0229E; ' . $year . '</span>';
									$HTML .= '<div style="display: none">';
								}
								$HTML .= HOAM_attachmentReturnHTML ($attachment);
							}
							$HTML .= '</div>';
							$HTML .= '<br class="clear" />';
						}
						$HTML .= '<input type="button" id="attachment|add" value="' . $_HOAM_language ['field_names']['attachment']['add'] . '" />';
						$HTML .= '</div>';

						$date_due = new DateTime ();
						$date_due -> modify ('+' . 14 . ' day');
						$HTML .= HOAM_formDate ('work_request|date|due', $_HOAM_language ['field_names']['work_request']['date']['due'], HOAM_date (date ($_HOAM_country ['date']['format_reports'], strtotime ($work_request_class -> returnDateDue ($trusted_id)))), $date_due -> format ($_HOAM_country ['date']['format_reports']));
						// Real hacky way to do this, should really be done through a
						// realtime Javascript popup, or at least cleaner integration.
						if ((isset ($_SESSION['error_class_item'])) AND ($_SESSION['error_class_item'] -> ShowCategory ('work_request|date|due') != '')) {
							$HTML .= HOAM_formCheckbox ('work_request|date|due|ignore', $_HOAM_language ['errors']['generic']['ignore'], 1, 0, 0);
						}
						$HTML .= HOAM_formSelect ('work_request|priority', $_HOAM_language ['field_names']['work_request']['priority']);
						if (isset ($_SESSION['request_information']['trusted_work_request|priority'])) {
							$default_option = $_SESSION['request_information']['trusted_work_request|priority'];
						} else {
							$default_option = $work_request_class -> returnPriority ($trusted_id);
						}
						foreach ($_HOAM_language ['priority'] as $priority) {
							$HTML .= '<option ';
							if ($_HOAM_setting ['work_request']['flags'][$priority['value']] == $default_option) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $_HOAM_setting ['work_request']['flags'][$priority['value']] . '">' . $priority['name'] . '</option>';
						}
						$HTML .= '</select>';
						if ($_HOAM_setting ['work_request']['require_approval']) {
							$HTML .= HOAM_formSelect ('work_request|approval', $_HOAM_language ['field_names']['work_request']['approval']);
							if (isset ($_SESSION['request_information']['trusted_work_request|approval'])) {
								$default_option = $_SESSION['request_information']['trusted_work_request|approval'];
							} else {
								$default_option = $work_request_class -> returnFlags ($trusted_id);
							}
							foreach ($_HOAM_language ['approval'] as $approval) {
								$HTML .= '<option ';
								if ($_HOAM_setting ['work_request']['flags'][$approval['value']] == $default_option) {
									$HTML .= 'selected="selected" ';
								}
								$HTML .= 'value="' . $_HOAM_setting ['work_request']['flags'][$approval['value']] . '">' . $approval['name'] . '</option>';
							}
							$HTML .= '</select>';
						}
						$HTML .= HOAM_formSelect ('work_request|status', $_HOAM_language ['field_names']['work_request']['status']);
						if (isset ($_SESSION['request_information']['trusted_work_request|status'])) {
							$default_option = $_SESSION['request_information']['trusted_work_request|status'];
						} else {
							$default_option = $work_request_class -> returnStatus ($trusted_id);
						}
						foreach ($_HOAM_language ['status'] as $status) {
							$HTML .= '<option ';
							if ($_HOAM_setting ['work_request']['flags'][$status['value']] == $default_option) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $_HOAM_setting ['work_request']['flags'][$status['value']] . '">' . $status['name'] . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= HOAM_formSelect ('work_request|vendor', $_HOAM_language ['field_names']['work_request']['vendor']);
						if (isset ($_SESSION['request_information']['trusted_work_request|vendor'])) {
							$default_option = $_SESSION['request_information']['trusted_work_request|vendor'];
						} else {
							$default_option = $work_request_class -> returnVendorId ($trusted_id);
						}
						$vendor_list = $budget_vendor_class -> listAll (NULL, 'name ASC'); 
						$HTML .= '<option value="">' . $_HOAM_language ['common']['please-select'] . '</option>';
						foreach ($vendor_list as $vendor) {
							$HTML .= '<option ';
							if ($vendor['id'] == $default_option) {
								$HTML .= 'selected="selected" ';
							}
							$HTML .= 'value="' . $vendor['id'] . '">' . html_encode (stripslashes ($vendor['name'])) . '</option>';
						}
						$HTML .= '</select>';
						$HTML .= '</div>';
						$HTML .= '</div>'; // End new request tab

						$HTML .= '<div class="tabbertab" id="request_history" title="' . $_HOAM_language ['form_tabs']['history'] . '">';
						$HTML .= '</div>'; // End History tab

						$HTML .= '</div>'; // End Tabber
							
						$HTML .= '</form>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/work_request/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list') :
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['work_request']['list'])));
				require_once (HOAM_PATH_FUNCTION . 'work_request.php');

				$HTML = '<h1>' . $_HOAM_language ['field_names']['work_request']['list'] . '</h1>';
				$HTML .= HOAM_workListAllRequests (TRUE);
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('list_all') :
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['work_request']['list'])));
				require_once (HOAM_PATH_FUNCTION . 'work_request.php');
				
				$HTML = '<h1>' . $_HOAM_language ['field_names']['work_request']['list_all'] . '</h1>';
				$HTML .= HOAM_workListAllRequests ();
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		case ('vendor') :
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
				if ($GLOBALS['global_url_array'][3]) {
					$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
					$trusted_id = $budget_vendor_class -> superClean ($GLOBALS['global_url_array'][3], $budget_vendor_class -> returnColumnSize ('id'));
					if ($budget_vendor_class -> idExists ($trusted_id)) {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['work_request']['vendor'])));
						$HTML = '<h1>' . $_HOAM_language ['field_names']['work_request']['vendor'] . '</h1>';
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
						$thead .= '<th class="donotprint">' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
						$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['requester'] . '</th>';
						$thead .= '</tr>';
						$thead .= '</thead>';
						$tfoot = '<tfoot>';
						$tfoot .= '<tr>';
						$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['generic']['created'] . '</th>';
						$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['priority'] . '</th>';
						$tfoot .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['status'] . '</th>';
						$tfoot .= '<th colspan="4">' . $_HOAM_language ['tables']['column']['work_request']['description'] . '</th>';
						$tfoot .= '<th class="donotprint">&nbsp;</th>';
						$tfoot .= '</tr>';
						$tfoot .= '</tfoot>';
						$tbody = '<tbody>';
						$request_list = $work_request_class -> listVendorRequest ($trusted_id);
						if (is_array ($request_list) AND !empty ($request_list)) {
							$caption = '<caption>' . sprintf ($_HOAM_language ['field_names']['work_request']['found'], count ($request_list)) . '</caption>';
							foreach ($request_list as $request) {
								$request_info = $work_request_class -> ListAll ($request[0]);
								// We reassign it to itself, because the ListAll() function assumes you're retrieving multiple rows.
								$request_info = $request_info[0];

								$tbody .= '<tr>';
								$tbody .= '<td rowspan="2">' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($request_info['datecreated'])) . '</td>';
								switch (TRUE) {
									case ((int) $_HOAM_setting ['work_request']['flags']['urgent'] == (int) $request_info['priority']) :
										$tbody .= '<td class="error" rowspan="2">';
										break;
									case ((int) $_HOAM_setting ['work_request']['flags']['high'] == (int) $request_info['priority']) :
										$tbody .= '<td class="warning" rowspan="2">';
										break;
									default:
										$tbody .= '<td rowspan="2">';
										break;
								}
   								foreach ($_HOAM_language ['priority'] as $priority) {
		   							if ((int) $_HOAM_setting ['work_request']['flags'][$priority['value']] == (int) $request_info['priority']) {
   										$tbody .= $priority['name'];
   										break;  
   									}
		   						}
								$tbody .= '</td>';
								$tbody .= '<td rowspan="2">';
   								foreach ($_HOAM_language ['status'] as $status) {
		   							if ((int) $_HOAM_setting ['work_request']['flags'][$status['value']] == (int) $request_info['status']) {
   										$tbody .= $status['name'];
   										break;  
   									}
   								}
								$tbody .= '</td>';
								$tbody .= '<td>' . HOAM_date ($_HOAM_country['date']['format_reports'], strtotime ($request_info['datedue'])) . '</td>';
								$tbody .= '<td>';
								switch ($request_info['lot_id']) {
									case 'common_area' :
										$tbody .= $_HOAM_language ['field_names']['lot']['common']['area'];
										break;
									case 'common_clubhouse' :
										$tbody .= $_HOAM_language ['field_names']['lot']['common']['clubhouse'];
										break;
									case 'common_pool' :
										$tbody .= $_HOAM_language ['field_names']['lot']['common']['pool'];
										break;
									default:
										$tbody .= HOAM_returnAddress ($request_info['lot_id']);
										break;
								}
								$tbody .= '</td>';
								$tbody .= '<td style="text-align: left" title="' . html_encode (stripslashes ($request_info['title'])) . '">';
								if ($request_info['parent_id']) {
									$tbody .= '&nbsp; &#9492;&#9472; &nbsp;';
								}
								$tbody .= HOAM_returnTruncatedString (html_encode (stripslashes ($request_info['title'])), 50) . '</td>';
								$tbody .= '<td class="donotprint">';
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
									$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
									$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
									($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
									$tbody .= '<small><a href="/admin/work_request/edit/' . $request_info['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
								}
								$tbody .= ' &nbsp; ';
								if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
									$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
									$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
									($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
									$tbody .= '<small><a class="error" href="/admin/work_request/delete/' . $request_info['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
								}
								$tbody .= '</td>';
								$tbody .= '<td>' . $user_class -> returnAccountName ($request_info['user_id']) . '</td>';
								$tbody .= '</tr>';
								// Now add the request notes to the display
								$tbody .= '<tr>';
								$tbody .= '<td colspan="4">' . HOAM_returnTruncatedString (html_encode (stripslashes ($request_info['description'])), 127) . '</td>';
								$tbody .= '<td class="donotprint">&nbsp;</td>';
								$tbody .= '</tr>';
								
							}
						} else {
							$tbody = '<tr><td colspan="8">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
						}
						$tbody .= '</tbody>';
						$HTML .= '<table class="center">' . $caption . $colgroup . $thead . $tfoot . '<tbody>'. $tbody . '<tbody></table>';
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /admin/work_request/list/');
					exit ();
				}
			} else {
				header ('Location: /error/403/');
				exit ();
			}
			break;
		default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['work_request'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['work_request'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/wrench.png" alt="' . $_HOAM_language ['field_names']['admin']['work_request'] . '" class="LHS" /><a href="/admin/work_request/">' . $_HOAM_language ['field_names']['admin']['work_request'] . '</a>';
					$HTML .= '<ul>';
					if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
						$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
						($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
						$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/wrench.png\')"><a href="/admin/work_request/add/">' . $_HOAM_language ['field_names']['work_request']['add'] . '</a></li>';
					}
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/wrench.png\')"><a href="/admin/work_request/list/">' . $_HOAM_language ['field_names']['work_request']['list'] . '</a></li>';
					$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/wrench.png\')"><a href="/admin/work_request/list_all/">' . $_HOAM_language ['field_names']['work_request']['list_all'] . '</a></li>';
					$HTML .= '</ul>';
				} else {
					header ('Location: /error/403/');
					exit ();
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
	}
	return ($HTML);
}

function HOAM_adminAttachmentJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/attachment.js"></script>';
}

function HOAM_adminCalendarJS () {
	echo '<script type="application/javascript" src="/3rdparty/mattkruse/CalendarPopup_combined_compact.js"></script>';
}

function HOAM_adminBudgetBudgetJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminBudgetBudget.js"></script>';
}

function HOAM_adminBudgetCustomerListJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminBudgetCustomerList.js"></script>';
}

function HOAM_adminBudgetInvoiceJS () {
	HOAM_adminCalendarJS ();
	echo '<script type="application/javascript" src="/3rdparty/prototype/event.simulate.js"></script>';
	echo '<script type="application/javascript" src="/hoam/javascript/adminBudgetInvoice.js"></script>';
}

function HOAM_adminBudgetPaymentJS () {
	HOAM_adminCalendarJS ();
	echo '<script type="application/javascript" src="/hoam/javascript/adminBudgetPayment.js"></script>';
}

function HOAM_adminBudgetReviewJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminBudgetReview.js"></script>';
}

function HOAM_adminBudgetVendorJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminBudgetVendor.js"></script>';
}

function HOAM_adminBudgetVendorInvoiceJS () {
	HOAM_adminCalendarJS ();
	echo '<script type="application/javascript" src="/hoam/javascript/adminBudgetVendorInvoice.js"></script>';
}

function HOAM_adminBudgetNetJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminBudgetNet.js"></script>';
}

function HOAM_adminBudgetViewJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminBudgetView.js"></script>';
}

function HOAM_adminHomeownerJS () {
	HOAM_adminCalendarJS ();
	echo '<script type="application/javascript" src="/hoam/javascript/adminHomeowner.js"></script>';
}

function HOAM_adminHomeownerSaleJS () {
	HOAM_adminCalendarJS ();
	echo '<script type="application/javascript" src="/hoam/javascript/adminHomeownerSale.js"></script>';
}

function HOAM_adminInsuranceJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminInsurance.js"></script>';
}

function HOAM_adminLetterJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/letter.js"></script>';
}

function HOAM_adminLotJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminLot.js"></script>';
}

function HOAM_adminPropertyJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminProperty.js"></script>';
}

function HOAM_reportJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/report.js"></script>';
}

function HOAM_adminVendorInvoiceListJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminVendorInvoiceList.js"></script>';
}

function HOAM_adminViolationJS () {
	HOAM_adminCalendarJS ();
	echo '<script type="application/javascript" src="/hoam/javascript/adminViolation.js"></script>';
}

function HOAM_adminViolationCategoryJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminViolationCategory.js"></script>';
}

function HOAM_adminViolationPrintJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminViolationPrint.js"></script>';
}

function HOAM_adminViolationSeverityJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminViolationSeverity.js"></script>';
}

function HOAM_adminWorkRequestJS () {
	echo '<script type="application/javascript" src="/hoam/javascript/adminWorkRequest.js"></script>';
}

if (isset ($_SESSION['current_user']['id'])) {
	if (isset ($GLOBALS['global_url_array'][1])) {
		switch ($GLOBALS['global_url_array'][1]) {
			case ('financial') :
				$_HOAM_log -> add ('Loading /admin/financial/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminFinancial (TRUE);
				break;
			case ('homeowner') :
				$_HOAM_log -> add ('Loading /admin/homeowner/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminHomeowner (TRUE);
				break;
			case ('letter') :
				$_HOAM_log -> add ('Loading /admin/letter/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminFormLetter (TRUE);
				break;
			case ('lot') :
				$_HOAM_log -> add ('Loading /admin/lot/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminLot (TRUE);
				break;
			case ('report') :
				$_HOAM_log -> add ('Loading /admin/report/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminReport (TRUE);
				break;
			case ('violation') :
				$_HOAM_log -> add ('Loading /admin/violation/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminViolation (TRUE);
				break;
			case ('work_request') :
				$_HOAM_log -> add ('Loading /admin/work_request/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_adminWorkRequest (TRUE);
				break;
			default :
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['hoa'])));
		echo '<h1>' . $_HOAM_language ['field_names']['admin']['hoa'] . '</h1>';
		echo HOAM_adminHomeowner ();
		echo HOAM_adminFinancial ();
		echo HOAM_adminFormLetter ();
		echo HOAM_adminLot ();
		echo HOAM_adminReport ();
		echo HOAM_adminViolation ();
		echo HOAM_adminWorkRequest ();
	}
} else {
	header ('Location: /error/403/');
	exit ();
}

?>
