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

// Is the current user allowed to add / edit the system configuration?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['cancel']) OR isset ($_POST['modify'])) {
		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['config_information'])) {
			unset ($_SESSION['config_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			// Just send them back to the admin page
			header ('Location: /website/');
		} else {
			// If we're still here, then we must be modifying the system
			// configuration. Start populating the config_information array,
			// and check for errors.
			$_SESSION['config_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');

			$article_class = new HOAM_article ('articles');
			$advertising_class_item = new HOAM_advertising ('advertising');
			$attachment_class_item = new HOAM_attachments ('attachments');
			$budget_account_class = new HOAM_budget_account ('budget_account');
			$budget_category_class = new HOAM_budget_category ('budget_category');
			$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
			$group_class = new HOAM_group ('group_list');
			$homeowner_class = new HOAM_homeowner ('homeowners');
			$lot_class = new HOAM_lot ('lots');
			$user_class = new HOAM_user ('users');
			$work_request_class = new HOAM_work_request ('work_requests');

			require_once (HOAM_PATH_FUNCTION . 'forms.php');
			
			// Run through the various sections in order.
			
			/*******************************************************************
			 * Advertising settings
			 *******************************************************************/
			if (isset ($_POST['advertising|minimum_description_length'])) {
				$_SESSION['config_information']['trusted_advertising|minimum_description_length'] = HOAM_formValidateValue ('advertising|minimum_description_length', (int) $group_members_class -> superClean ($_POST['advertising|minimum_description_length'], $advertising_class_item -> returnColumnSize ('description')), $_SESSION['error_class_item'], 0, $advertising_class_item -> returnColumnSize ('description'));
			}
			if (isset ($_POST['advertising|minimum_url_length'])) {
				$_SESSION['config_information']['trusted_advertising|minimum_url_length'] = HOAM_formValidateValue ('advertising|minimum_url_length', (int) $group_members_class -> superClean ($_POST['advertising|minimum_url_length'], $advertising_class_item -> returnColumnSize ('url')), $_SESSION['error_class_item'], 0, $advertising_class_item -> returnColumnSize ('url'));
			}
			if (isset ($_POST['advertising|flags|position1'])) {
				$_SESSION['config_information']['trusted_advertising|flags|position1'] = HOAM_formValidateFlagValue ('advertising|flags|position1', (int) $group_members_class -> superClean ($_POST['advertising|flags|position1'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['advertising|flags|position2'])) {
				$_SESSION['config_information']['trusted_advertising|flags|position2'] = HOAM_formValidateFlagValue ('advertising|flags|position2', (int) $group_members_class -> superClean ($_POST['advertising|flags|position2'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['advertising|flags|position3'])) {
				$_SESSION['config_information']['trusted_advertising|flags|position3'] = HOAM_formValidateFlagValue ('advertising|flags|position3', (int) $group_members_class -> superClean ($_POST['advertising|flags|position3'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['advertising|flags|position4'])) {
				$_SESSION['config_information']['trusted_advertising|flags|position4'] = HOAM_formValidateFlagValue ('advertising|flags|position4', (int) $group_members_class -> superClean ($_POST['advertising|flags|position4'], 20), $_SESSION ['error_class_item']);
			}
			
			/*******************************************************************
			 * Attachment settings
			 *******************************************************************/
			if (isset ($_POST['attachment|maximum_file_size'])) {
				$_SESSION['config_information']['trusted_attachment|maximum_file_size'] = HOAM_formValidateValue ('attachment|maximum_file_size', (int) $group_members_class -> superClean ($_POST['attachment|maximum_file_size'], 20), $_SESSION['error_class_item'], 0, 268435456);
			}
			if (isset ($_POST['attachment|minimum_description_length'])) {
				$_SESSION['config_information']['trusted_attachment|minimum_description_length'] = HOAM_formValidateValue ('attachment|minimum_description_length', (int) $group_members_class -> superClean ($_POST['attachment|minimum_description_length'], $attachment_class_item -> returnColumnSize ('description')), $_SESSION['error_class_item'], 0, $attachment_class_item -> returnColumnSize ('description'));
			}
			if (isset ($_POST['attachment|flags|advertising'])) {
				$_SESSION['config_information']['trusted_attachment|flags|advertising'] = HOAM_formValidateFlagValue ('attachment|flags|advertising', (int) $group_members_class -> superClean ($_POST['attachment|flags|advertising'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|flags|budget'])) {
				$_SESSION['config_information']['trusted_attachment|flags|budget'] = HOAM_formValidateFlagValue ('attachment|flags|budget', (int) $group_members_class -> superClean ($_POST['attachment|flags|budget'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|flags|budget_vendor'])) {
				$_SESSION['config_information']['trusted_attachment|flags|budget_vendor'] = HOAM_formValidateFlagValue ('attachment|flags|budget_vendor', (int) $group_members_class -> superClean ($_POST['attachment|flags|budget_vendor'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|flags|homeowner'])) {
				$_SESSION['config_information']['trusted_attachment|flags|homeowner'] = HOAM_formValidateFlagValue ('attachment|flags|homeowner', (int) $group_members_class -> superClean ($_POST['attachment|flags|homeowner'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|flags|homeowner_sale'])) {
				$_SESSION['config_information']['trusted_attachment|flags|homeowner_sale'] = HOAM_formValidateFlagValue ('attachment|flags|homeowner_sale', (int) $group_members_class -> superClean ($_POST['attachment|flags|homeowner_sale'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|flags|insurance'])) {
				$_SESSION['config_information']['trusted_attachment|flags|insurance'] = HOAM_formValidateFlagValue ('attachment|flags|homeowner', (int) $group_members_class -> superClean ($_POST['attachment|flags|insurance'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|flags|lot'])) {
				$_SESSION['config_information']['trusted_attachment|flags|lot'] = HOAM_formValidateFlagValue ('attachment|flags|lot', (int) $group_members_class -> superClean ($_POST['attachment|flags|lot'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|flags|property'])) {
				$_SESSION['config_information']['trusted_attachment|flags|property'] = HOAM_formValidateFlagValue ('attachment|flags|property', (int) $group_members_class -> superClean ($_POST['attachment|flags|property'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|flags|violation'])) {
				$_SESSION['config_information']['trusted_attachment|flags|violation'] = HOAM_formValidateFlagValue ('attachment|flags|violation', (int) $group_members_class -> superClean ($_POST['attachment|flags|violation'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|flags|vote'])) {
				$_SESSION['config_information']['trusted_attachment|flags|vote'] = HOAM_formValidateFlagValue ('attachment|flags|vote', (int) $group_members_class -> superClean ($_POST['attachment|flags|vote'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|flags|work_request'])) {
				$_SESSION['config_information']['trusted_attachment|flags|work_request'] = HOAM_formValidateFlagValue ('attachment|flags|work_request', (int) $group_members_class -> superClean ($_POST['attachment|flags|work_request'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['attachment|preview|enable'])) {
				$_SESSION['config_information']['trusted_attachment|preview|enable'] = (int) $group_members_class -> superClean ($_POST['attachment|preview|enable'], 1);
				if ($_SESSION['config_information']['trusted_attachment|preview|enable']) {
					// Make sure the imageMagick convert program is available and in
					// the path.
					exec ("convert -version", $output, $return_code);
					if ($return_code) {
						// Not found
						$_SESSION ['error_class_item'] -> addError ('attachment|preview|enable', $_HOAM_language ['errors']['attachment']['imagemagick']);
						// Disable the option.
						$_SESSION['config_information']['trusted_attachment|preview|enable'] = 0;
					}
				}
			} elseif (isset ($_POST['attachment|preview|enable_enable'])) {
				$_SESSION['config_information']['trusted_attachment|preview|enable'] = 0;
			}
			
			if (isset ($_POST['attachment|preview|height'])) {
				$_SESSION['config_information']['trusted_attachment|preview|height'] = HOAM_formValidateValue ('attachment|preview|height', (int) $group_members_class -> superClean ($_POST['attachment|preview|height']), $_SESSION ['error_class_item'], 60, 768);
			}
			if (isset ($_POST['attachment|preview|width'])) {
				$_SESSION['config_information']['trusted_attachment|preview|width'] = HOAM_formValidateValue ('attachment|preview|width', (int) $group_members_class -> superClean ($_POST['attachment|preview|width']), $_SESSION ['error_class_item'], 80, 1024);
			}
			
			/*******************************************************************
			 * Budget settings
			 *******************************************************************/
			if (isset ($_POST['budget|assessment|amount'])) {
				$_SESSION['config_information']['trusted_budget|assessment|amount'] = HOAM_formValidateValue ('budget|assessment|amount', (float) $group_members_class -> superClean ($_POST['budget|assessment|amount'], 20), $_SESSION['error_class_item'], 0, 50000);
			}
			if (isset ($_POST['budget|assessment|date'])) {
				$_SESSION['config_information']['trusted_budget|assessment|date'] = (string) $homeowner_class -> superClean ($_POST['budget|assessment|date'], $homeowner_class -> returnColumnSize ('purchasedate'));
				if (mb_strlen ($_SESSION['config_information']['trusted_budget|assessment|date']) < $homeowner_class -> returnColumnSize ('purchasedate')) {
					$_SESSION['error_class_item'] -> addError ('budget|assessment|date', $_HOAM_language ['errors']['generic']['date-too-short']);
				}
				// Make sure we have a valid date
				if (checkdate (date ('m', strtotime ($_SESSION['config_information']['trusted_budget|assessment|date'])), date ('d', strtotime ($_SESSION['config_information']['trusted_budget|assessment|date'])), date ('Y', strtotime ($_SESSION['config_information']['trusted_budget|assessment|date'])))) {
				} else {
					$_SESSION['error_class_item'] -> addError ('budget|assessment|date', $_HOAM_language ['errors']['generic']['invalid-date']);
				}
			}
			if (isset ($_POST['budget|assessment|time'])) {
				$_SESSION['config_information']['trusted_budget|assessment|time'] = HOAM_formValidateValue ('budget|assessment|time', (string) $group_members_class -> superClean ($_POST['budget|assessment|time'], 20), $_SESSION['error_class_item'], 0, 64);
			}
			if (isset ($_POST['budget|assessment|days_due'])) {
				$_SESSION['config_information']['trusted_budget|assessment|days_due'] = HOAM_formValidateValue ('budget|assessment|days_due', (int) $group_members_class -> superClean ($_POST['budget|assessment|days_due'], 20), $_SESSION['error_class_item'], 0, 731);
			}
			if (isset ($_POST['budget|assessment|days_late'])) {
				$_SESSION['config_information']['trusted_budget|assessment|days_late'] = HOAM_formValidateValue ('budget|assessment|days_late', (int) $group_members_class -> superClean ($_POST['budget|assessment|days_late'], 20), $_SESSION['error_class_item'], 0, 731);
			}
			if (isset ($_POST['budget|assessment|months_due'])) {
				$_SESSION['config_information']['trusted_budget|assessment|months_due'] = HOAM_formValidateValue ('budget|assessment|months_due', (int) $group_members_class -> superClean ($_POST['budget|assessment|months_due'], 20), $_SESSION['error_class_item'], 0, 24);
			}
			if (isset ($_POST['budget|assessment|months_late'])) {
				$_SESSION['config_information']['trusted_budget|assessment|months_late'] = HOAM_formValidateValue ('budget|assessment|months_late', (int) $group_members_class -> superClean ($_POST['budget|assessment|months_late'], 20), $_SESSION['error_class_item'], 0, 24);
			}
			if (isset ($_POST['budget|assessment|frequency'])) {
				$_SESSION['config_information']['trusted_budget|assessment|frequency'] = $group_members_class -> superClean ($_POST['budget|assessment|frequency'], 16);
				$allowed_schedules = array ('annual', 'biannual', 'monthly', 'quarterly');
				if (!in_array ($_SESSION['config_information']['trusted_budget|assessment|frequency'], $allowed_schedules)) {
					$_SESSION['error_class_item'] -> addError ('budget|assessment|frequency', $_HOAM_language ['errors']['generic']['unknown-id']);				
				}
			}
			if (isset ($_POST['budget|assessment|unit'])) {
				$_SESSION['config_information']['trusted_budget|assessment|unit'] = $group_members_class -> superClean ($_POST['budget|assessment|unit'], 8);
				$allowed_units = array ('single', 'sqft');
				if (!in_array ($_SESSION['config_information']['trusted_budget|assessment|unit'], $allowed_units)) {
					$_SESSION['error_class_item'] -> addError ('budget|assessment|unit', $_HOAM_language ['errors']['generic']['unknown-id']);				
				}
			}

			if (isset ($_POST['budget|fee|collection'])) {
				$_SESSION['config_information']['trusted_budget|fee|collection'] = number_format (HOAM_formValidateValue ('budget|fee|collection', (float) $group_members_class -> superClean ($_POST['budget|fee|collection'], 20), $_SESSION['error_class_item'], 0, 1000), 2);
			}
			if (isset ($_POST['budget|fee|late'])) {
				$_SESSION['config_information']['trusted_budget|fee|late'] = number_format (HOAM_formValidateValue ('budget|fee|late', (float) $group_members_class -> superClean ($_POST['budget|fee|late'], 20), $_SESSION['error_class_item'], 0, 1000), 2);
			}
			if (isset ($_POST['budget|fee|returned'])) {
				$_SESSION['config_information']['trusted_budget|fee|returned'] = number_format (HOAM_formValidateValue ('budget|fee|returned', (float) $group_members_class -> superClean ($_POST['budget|fee|returned'], 20), $_SESSION['error_class_item'], 0, 1000), 2);
			}
			if (isset ($_POST['budget|interest_rate'])) {
				$_SESSION['config_information']['trusted_budget|interest_rate'] = number_format (HOAM_formValidateValue ('budget|interest_rate', (float) $group_members_class -> superClean ($_POST['budget|interest_rate'], 20), $_SESSION['error_class_item'], 0, 30), 2);
			}
			if (isset ($_POST['config|budget|payment_plan|interest'])) {
				$_SESSION['config_information']['trusted_config|budget|payment_plan|interest'] = (int) $group_members_class -> superClean ($_POST['config|budget|payment_plan|interest'], 1);
			} elseif (isset ($_POST['config|budget|payment_plan|interest_enable'])) {
				$_SESSION['config_information']['trusted_config|budget|payment_plan|interest'] = 0;
			}
			
			if (isset ($_POST['budget|flags|annual'])) {
				$_SESSION['config_information']['trusted_budget|flags|annual'] = HOAM_formValidateFlagValue ('budget|flags|annual', (int) $group_members_class -> superClean ($_POST['budget|flags|annual'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|assessment'])) {
				$_SESSION['config_information']['trusted_budget|flags|assessment'] = HOAM_formValidateFlagValue ('budget|flags|assessment', (int) $group_members_class -> superClean ($_POST['budget|flags|assessment'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|assessment_other'])) {
				$_SESSION['config_information']['trusted_budget|flags|assessment_other'] = HOAM_formValidateFlagValue ('budget|flags|assessment_other', (int) $group_members_class -> superClean ($_POST['budget|flags|assessment_other'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|assessment'])) {
				$_SESSION['config_information']['trusted_budget|flags|assessment_special'] = HOAM_formValidateFlagValue ('budget|flags|assessment_special', (int) $group_members_class -> superClean ($_POST['budget|flags|assessment_special'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|credit'])) {
				$_SESSION['config_information']['trusted_budget|flags|credit'] = HOAM_formValidateFlagValue ('budget|flags|credit', (int) $group_members_class -> superClean ($_POST['budget|flags|credit'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|fee_administration'])) {
				$_SESSION['config_information']['trusted_budget|flags|fee_administration'] = HOAM_formValidateFlagValue ('budget|flags|fee_administration', (int) $group_members_class -> superClean ($_POST['budget|flags|fee_administration'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|fee_fine'])) {
				$_SESSION['config_information']['trusted_budget|flags|fee_fine'] = HOAM_formValidateFlagValue ('budget|flags|fee_fine', (int) $group_members_class -> superClean ($_POST['budget|flags|fee_fine'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|fee_interest'])) {
				$_SESSION['config_information']['trusted_budget|flags|fee_interest'] = HOAM_formValidateFlagValue ('budget|flags|fee_interest', (int) $group_members_class -> superClean ($_POST['budget|flags|fee_interest'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|fee_late'])) {
				$_SESSION['config_information']['trusted_budget|flags|fee_late'] = HOAM_formValidateFlagValue ('budget|flags|fee_late', (int) $group_members_class -> superClean ($_POST['budget|flags|fee_late'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|fee_legal'])) {
				$_SESSION['config_information']['trusted_budget|flags|fee_legal'] = HOAM_formValidateFlagValue ('budget|flags|fee_legal', (int) $group_members_class -> superClean ($_POST['budget|flags|fee_legal'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|ignore_average'])) {
				$_SESSION['config_information']['trusted_budget|flags|ignore_average'] = HOAM_formValidateFlagValue ('budget|flags|ignore_average', (int) $group_members_class -> superClean ($_POST['budget|flags|ignore_average'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|payment'])) {
				$_SESSION['config_information']['trusted_budget|flags|payment'] = HOAM_formValidateFlagValue ('budget|flags|payment', (int) $group_members_class -> superClean ($_POST['budget|flags|payment'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|public'])) {
				$_SESSION['config_information']['trusted_budget|flags|public'] = HOAM_formValidateFlagValue ('budget|flags|public', (int) $group_members_class -> superClean ($_POST['budget|flags|public'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|sales_tax'])) {
				$_SESSION['config_information']['trusted_budget|flags|sales_tax'] = HOAM_formValidateFlagValue ('budget|flags|sales_tax', (int) $group_members_class -> superClean ($_POST['budget|flags|sales_tax'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|apply_current_homeowner'])) {
				$_SESSION['config_information']['trusted_budget|flags|apply_current_homeowner'] = HOAM_formValidateFlagValue ('budget|flags|apply_current_homeowner', (int) $group_members_class -> superClean ($_POST['budget|flags|apply_current_homeowner'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|apply_fee_late'])) {
				$_SESSION['config_information']['trusted_budget|flags|apply_fee_late'] = HOAM_formValidateFlagValue ('budget|flags|apply_fee_late', (int) $group_members_class -> superClean ($_POST['budget|flags|apply_fee_late'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|apply_interest'])) {
				$_SESSION['config_information']['trusted_budget|flags|apply_interest'] = HOAM_formValidateFlagValue ('budget|flags|apply_interest', (int) $group_members_class -> superClean ($_POST['budget|flags|apply_interest'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|apply_sales_tax'])) {
				$_SESSION['config_information']['trusted_budget|flags|apply_sales_tax'] = HOAM_formValidateFlagValue ('budget|flags|apply_sales_tax', (int) $group_members_class -> superClean ($_POST['budget|flags|apply_sales_tax'], 20), $_SESSION ['error_class_item']);
			}
/*			if (isset ($_POST['budget|flags|account_closed'])) {
				$_SESSION['config_information']['trusted_budget|flags|account_closed'] = HOAM_formValidateFlagValue ('budget|flags|account_closed', (int) $group_members_class -> superClean ($_POST['budget|flags|account_closed'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|account_frozen'])) {
				$_SESSION['config_information']['trusted_budget|flags|account_frozen'] = HOAM_formValidateFlagValue ('budget|flags|account_frozen', (int) $group_members_class -> superClean ($_POST['budget|flags|account_closed'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|account_expense'])) {
				$_SESSION['config_information']['trusted_budget|flags|account_expense'] = HOAM_formValidateFlagValue ('budget|flags|account_expense', (int) $group_members_class -> superClean ($_POST['budget|flags|account_expense'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|account_homeowner'])) {
				$_SESSION['config_information']['trusted_budget|flags|account_homeowner'] = HOAM_formValidateFlagValue ('budget|flags|account_homeowner', (int) $group_members_class -> superClean ($_POST['budget|flags|account_homeowner'], 20), $_SESSION ['error_class_item']);
			}
*/			if (isset ($_POST['budget|flags|category_income'])) {
				$_SESSION['config_information']['trusted_budget|flags|category_income'] = HOAM_formValidateFlagValue ('budget|flags|category_income', (int) $group_members_class -> superClean ($_POST['budget|flags|category_income'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|due_15'])) {
				$_SESSION['config_information']['trusted_budget|flags|due_15'] = HOAM_formValidateFlagValue ('budget|flags|due_15', (int) $group_members_class -> superClean ($_POST['budget|flags|due_15'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|due_20'])) {
				$_SESSION['config_information']['trusted_budget|flags|due_20'] = HOAM_formValidateFlagValue ('budget|flags|due_20', (int) $group_members_class -> superClean ($_POST['budget|flags|due_20'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|due_30'])) {
				$_SESSION['config_information']['trusted_budget|flags|due_30'] = HOAM_formValidateFlagValue ('budget|flags|due_30', (int) $group_members_class -> superClean ($_POST['budget|flags|due_30'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|due_45'])) {
				$_SESSION['config_information']['trusted_budget|flags|due_45'] = HOAM_formValidateFlagValue ('budget|flags|due_45', (int) $group_members_class -> superClean ($_POST['budget|flags|due_45'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|due_receipt'])) {
				$_SESSION['config_information']['trusted_budget|flags|due_receipt'] = HOAM_formValidateFlagValue ('budget|flags|due_receipt', (int) $group_members_class -> superClean ($_POST['budget|flags|due_receipt'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|late_notice'])) {
				$_SESSION['config_information']['trusted_budget|flags|late_notice'] = HOAM_formValidateFlagValue ('budget|flags|late_notice', (int) $group_members_class -> superClean ($_POST['budget|flags|late_notice'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|late_delinquent'])) {
				$_SESSION['config_information']['trusted_budget|flags|late_delinquent'] = HOAM_formValidateFlagValue ('budget|flags|late_delinquent', (int) $group_members_class -> superClean ($_POST['budget|flags|late_delinquent'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|late_default'])) {
				$_SESSION['config_information']['trusted_budget|flags|late_default'] = HOAM_formValidateFlagValue ('budget|flags|late_default', (int) $group_members_class -> superClean ($_POST['budget|flags|late_default'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|late_attorney'])) {
				$_SESSION['config_information']['trusted_budget|flags|late_attorney'] = HOAM_formValidateFlagValue ('budget|flags|late_attorney', (int) $group_members_class -> superClean ($_POST['budget|flags|late_attorney'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|payment_plan'])) {
				$_SESSION['config_information']['trusted_budget|flags|payment_plan'] = HOAM_formValidateFlagValue ('budget|flags|payment_plan', (int) $group_members_class -> superClean ($_POST['budget|flags|payment_plan'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|flags|payment_plan_default'])) {
				$_SESSION['config_information']['trusted_budget|flags|payment_plan_default'] = HOAM_formValidateFlagValue ('budget|flags|payment_plan_default', (int) $group_members_class -> superClean ($_POST['budget|flags|payment_plan_default'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['budget|invoice|fineprint'])) {
				$_SESSION['config_information']['trusted_budget|invoice|fineprint'] = (string) $group_members_class -> Clean ($_POST['budget|invoice|fineprint'], 4096);
			}
			if (isset ($_POST['budget|sales_tax_rate'])) {
				$_SESSION['config_information']['trusted_budget|sales_tax_rate'] = number_format (HOAM_formValidateValue ('budget|sales_tax_rate', (float) $group_members_class -> superClean ($_POST['budget|sales_tax_rate'], 20), $_SESSION['error_class_item'], 0, 30), 2);
			}
			if (isset ($_POST['config|budget|category|administrative'])) {
				$_SESSION['config_information']['trusted_budget|category|administrative'] = (string) $budget_category_class -> superClean ($_POST['config|budget|category|administrative'], 32);
				if (!$budget_category_class -> idExists ($_SESSION['config_information']['trusted_budget|category|administrative'])) {
					$_SESSION['error_class_item'] -> addError ('config|budget|category|administrative', $_HOAM_language ['errors']['budget']['category']['unknown-id']);
				}
			}
			if (isset ($_POST['config|budget|category|credit'])) {
				$_SESSION['config_information']['trusted_budget|category|credit'] = (string) $budget_category_class -> superClean ($_POST['config|budget|category|credit'], 32);
				if (!$budget_category_class -> idExists ($_SESSION['config_information']['trusted_budget|category|credit'])) {
					$_SESSION['error_class_item'] -> addError ('config|budget|category|credit', $_HOAM_language ['errors']['budget']['category']['unknown-id']);
				}
			}
			if (isset ($_POST['config|budget|category|dues'])) {
				$_SESSION['config_information']['trusted_budget|category|dues'] = (string) $budget_category_class -> superClean ($_POST['config|budget|category|dues'], 32);
				if (!$budget_category_class -> idExists ($_SESSION['config_information']['trusted_budget|category|dues'])) {
					$_SESSION['error_class_item'] -> addError ('config|budget|category|dues', $_HOAM_language ['errors']['budget']['category']['unknown-id']);
				}
			}
			if (isset ($_POST['config|budget|category|interest'])) {
				$_SESSION['config_information']['trusted_budget|category|interest'] = (string) $budget_category_class -> superClean ($_POST['config|budget|category|interest'], 32);
				if (!$budget_category_class -> idExists ($_SESSION['config_information']['trusted_budget|category|interest'])) {
					$_SESSION['error_class_item'] -> addError ('config|budget|category|interest', $_HOAM_language ['errors']['budget']['category']['unknown-id']);
				}
			}
			if (isset ($_POST['config|budget|category|late'])) {
				$_SESSION['config_information']['trusted_budget|category|late'] = (string) $budget_category_class -> superClean ($_POST['config|budget|category|late'], 32);
				if (!$budget_category_class -> idExists ($_SESSION['config_information']['trusted_budget|category|late'])) {
					$_SESSION['error_class_item'] -> addError ('config|budget|category|late', $_HOAM_language ['errors']['budget']['category']['unknown-id']);
				}
			}
			if (isset ($_POST['config|budget|category|property'])) {
				$_SESSION['config_information']['trusted_budget|category|property'] = (string) $budget_category_class -> superClean ($_POST['config|budget|category|property'], 32);
				if (!$budget_category_class -> idExists ($_SESSION['config_information']['trusted_budget|category|property'])) {
					$_SESSION['error_class_item'] -> addError ('config|budget|category|property', $_HOAM_language ['errors']['budget']['category']['unknown-id']);
				}
			}
			if (isset ($_POST['config|budget|vendor|management'])) {
				$_SESSION['config_information']['trusted_budget|vendor|management'] = (string) $budget_vendor_class -> superClean ($_POST['config|budget|vendor|management'], 32);
				if (!$budget_vendor_class -> idExists ($_SESSION['config_information']['trusted_budget|vendor|management'])) {
					$_SESSION['error_class_item'] -> addError ('config|budget|vendor|management', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['config|budget|vendor|organization'])) {
				$_SESSION['config_information']['trusted_budget|vendor|organization'] = (string) $budget_vendor_class -> superClean ($_POST['config|budget|vendor|organization'], 32);
				if (!$budget_vendor_class -> idExists ($_SESSION['config_information']['trusted_budget|vendor|organization'])) {
					$_SESSION['error_class_item'] -> addError ('config|budget|vendor|organization', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['config|budget|account|income'])) {
				$_SESSION['config_information']['trusted_budget|account|income'] = (string) $budget_vendor_class -> superClean ($_POST['config|budget|account|income'], 32);
				if (!$budget_account_class -> idExists ($_SESSION['config_information']['trusted_budget|account|income'])) {
					$_SESSION['error_class_item'] -> addError ('config|budget|account|income', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['config|budget|account|expense'])) {
				$_SESSION['config_information']['trusted_budget|account|expense'] = (string) $budget_vendor_class -> superClean ($_POST['config|budget|account|expense'], 32);
				if (!$budget_account_class -> idExists ($_SESSION['config_information']['trusted_budget|account|expense'])) {
					$_SESSION['error_class_item'] -> addError ('config|budget|account|expense', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['config|budget|insurance|enable'])) {
				$_SESSION['config_information']['trusted_config|budget|insurance|enable'] = (int) $group_members_class -> superClean ($_POST['config|budget|insurance|enable'], 1);
			} elseif (isset ($_POST['config|budget|insurance|enable_enable'])) {
				$_SESSION['config_information']['trusted_config|budget|insurance|enable'] = 0;
			}
			if (isset ($_POST['config|budget|insurance|days'])) {
				$_SESSION['config_information']['trusted_config|budget|insurance|days'] = (int) $group_members_class -> superClean ($_POST['config|budget|insurance|days'], 3);
				if ($_SESSION['config_information']['trusted_config|budget|insurance|days'] < 0) {
					$_SESSION['error_class_item'] -> addError ('config|budget|insurance|days', sprintf ($_HOAM_language ['errors']['generic']['value-too-small'], 0));
				} elseif ($_SESSION['config_information']['trusted_config|budget|insurance|days'] > 255) {
					$_SESSION['error_class_item'] -> addError ('config|budget|insurance|days', sprintf ($_HOAM_language ['errors']['generic']['value-too-large'], 180));
				} 
			}
			if (isset ($_POST['config|budget|insurance|minimum_policy_length'])) {
				$_SESSION['config_information']['trusted_config|budget|insurance|minimum_policy_length'] = (int) $group_members_class -> superClean ($_POST['config|budget|insurance|minimum_policy_length'], 3);
				if ($_SESSION['config_information']['trusted_config|budget|insurance|minimum_policy_length'] < 1) {
					$_SESSION['error_class_item'] -> addError ('config|budget|insurance|minimum_policy_length', sprintf ($_HOAM_language ['errors']['generic']['value-too-small'], 1));
				} elseif ($_SESSION['config_information']['trusted_config|budget|insurance|minimum_policy_length'] > 50) {
					$_SESSION['error_class_item'] -> addError ('config|budget|insurance|minimum_policy_length', sprintf ($_HOAM_language ['errors']['generic']['value-too-large'], 50));
				} 
			}
			if (isset ($_POST['config|budget|insurance|reminders'])) {
				$_SESSION['config_information']['trusted_config|budget|insurance|reminders'] = (int) $group_members_class -> superClean ($_POST['config|budget|insurance|reminders'], 1);
			} elseif (isset ($_POST['config|budget|insurance|reminders_enable'])) {
				$_SESSION['config_information']['trusted_config|budget|insurance|reminders'] = 0;
			}
			if (isset ($_POST['config|budget|insurance|flags|replaced'])) {
				$_SESSION['config_information']['trusted_config|budget|insurance|flags|replaced'] = HOAM_formValidateFlagValue ('config|budget|insurance|flags|replaced', (int) $group_members_class -> superClean ($_POST['config|budget|insurance|flags|replaced'], 20), $_SESSION ['error_class_item']);
			}

			if (isset ($_POST['config|budget|paypal|enable'])) {
				$_SESSION['config_information']['trusted_config|budget|paypal|enable'] = (int) $group_members_class -> superClean ($_POST['config|budget|paypal|enable'], 1);
			} elseif (isset ($_POST['config|budget|paypal|enable_enable'])) {
				$_SESSION['config_information']['trusted_config|budget|paypal|enable'] = 0;
			}
			if (isset ($_POST['config|budget|paypal|email'])) {
				$_SESSION['config_information']['trusted_config|budget|paypal|email'] = (string) $group_members_class -> superClean ($_POST['config|budget|paypal|email'], 256);
				if (mb_strlen ($_SESSION['config_information']['trusted_config|budget|paypal|email']) > 0) {
					if (!filter_var ($_SESSION['config_information']['trusted_config|budget|paypal|email'], FILTER_VALIDATE_EMAIL)) {
						$_SESSION['error_class_item'] -> addError ('config|budget|paypal|email', $_HOAM_language ['errors']['generic']['invalid-email']);
					}
				}
			}
			if (isset ($_POST['config|budget|paypal|surcharge_amount'])) {
				$_SESSION['config_information']['trusted_config|budget|paypal|surcharge_amount'] = number_format (HOAM_formValidateValue ('config|budget|paypal|surcharge_amount', (float) $group_members_class -> superClean ($_POST['config|budget|paypal|surcharge_amount'], 20), $_SESSION['error_class_item'], 0, 100), 2);
			}
			if (isset ($_POST['config|budget|paypal|surcharge_percent'])) {
				$_SESSION['config_information']['trusted_config|budget|paypal|surcharge_percent'] = number_format (HOAM_formValidateValue ('config|budget|paypal|surcharge_percent', (float) $group_members_class -> superClean ($_POST['config|budget|paypal|surcharge_percent'], 20), $_SESSION['error_class_item'], 0, 10), 2);
			}

			if (isset ($_POST['config|budget|zero_amount|enable'])) {
				$_SESSION['config_information']['trusted_config|budget|zero_amount|enable'] = (int) $group_members_class -> superClean ($_POST['config|budget|zero_amount|enable'], 1);
			} elseif (isset ($_POST['config|budget|zero_amount|enable_enable'])) {
				$_SESSION['config_information']['trusted_config|budget|zero_amount|enable'] = 0;
			}

			if (isset ($_POST['config|budget|vendor|percent_different'])) {
				$_SESSION['config_information']['trusted_config|budget|vendor|percent_different'] = number_format (HOAM_formValidateValue ('config|budget|vendor|percent_different', (int) $group_members_class -> superClean ($_POST['config|budget|vendor|percent_different'], 3), $_SESSION['error_class_item'], 0, 100), 2);
			}

			/*******************************************************************
			 * Email settings
			 *******************************************************************/
			if (isset ($_POST['config|email|automated'])) {
				$_SESSION['config_information']['trusted_email|automated'] = (int) $group_members_class -> superClean ($_POST['config|email|automated'], 1);
			} elseif (isset ($_POST['config|email|automated_enable'])) {
				$_SESSION['config_information']['trusted_email|automated'] = 0;
			}
			if (isset ($_POST['config|email|server'])) {
				$_SESSION['config_information']['trusted_email|server'] = $group_members_class -> superClean ($_POST['config|email|server'], 128);
				if (empty ($_SESSION['config_information']['trusted_email|server'])) {
					$_SESSION['error_class_item'] -> addError ('config|email|server', $_HOAM_language ['errors']['generic']['blank']);
				} else {
					if (!filter_var ($_SESSION['config_information']['trusted_email|server'], FILTER_VALIDATE_DOMAIN)) {
						$_SESSION['error_class_item'] -> addError ('config|email|server', $_HOAM_language ['errors']['generic']['invalid-url']);
					}
				}
			}
			if (isset ($_POST['config|email|port'])) {
				$_SESSION['config_information']['trusted_email|port'] = number_format (HOAM_formValidateValue ('config|email|port', (int) $group_members_class -> superClean ($_POST['config|email|port'], 3), $_SESSION['error_class_item'], 0, 65535), 0);
				if (empty ($_SESSION['config_information']['trusted_email|port'])) {
					$_SESSION['error_class_item'] -> addError ('config|email|port', $_HOAM_language ['errors']['generic']['blank']);
				}
			}
			if (isset ($_POST['config|email|auth|enable'])) {
				$_SESSION['config_information']['trusted_email|auth|enable'] = (int) $group_members_class -> superClean ($_POST['config|email|auth|enable'], 1);
			} elseif (isset ($_POST['config|email|auth|enable_enable'])) {
				$_SESSION['config_information']['trusted_email|auth|enable'] = 0;
			}
			if (isset ($_POST['config|email|username'])) {
				$_SESSION['config_information']['trusted_email|username'] = $group_members_class -> superClean ($_POST['config|email|username'], 128);
				if (empty ($_SESSION['config_information']['trusted_email|username'])) {
					$_SESSION['error_class_item'] -> addError ('config|email|username', $_HOAM_language ['errors']['generic']['blank']);
				} else {
					if (!filter_var ($_SESSION['config_information']['trusted_email|username'], FILTER_VALIDATE_EMAIL)) {
						$_SESSION['error_class_item'] -> addError ('config|email|username', $_HOAM_language ['errors']['generic']['invalid-email']);
					}
				}
			}
			if (isset ($_POST['config|email|password'])) {
				$_SESSION['config_information']['trusted_email|password'] = $group_members_class -> superClean ($_POST['config|email|password'], 128);
				if (empty ($_SESSION['config_information']['trusted_email|password'])) {
					$_SESSION['error_class_item'] -> addError ('config|email|password', $_HOAM_language ['errors']['generic']['blank']);
				}
			}
			if (isset ($_POST['config|email|do_not_reply'])) {
				$_SESSION['config_information']['trusted_email|do_not_reply'] = (int) $group_members_class -> superClean ($_POST['config|email|do_not_reply'], 1);
			}

			/*******************************************************************
			 * File settings
			 *******************************************************************/
			if (isset ($_POST['files|banned_username'])) {
				$_SESSION['config_information']['trusted_files|banned_username'] = (string) escapeshellcmd ($group_members_class -> Clean ($_POST['files|banned_username'], 128));
				if (file_exists (HOAM_PATH . $_SESSION['config_information']['trusted_files|banned_username']) AND
					is_readable (HOAM_PATH . $_SESSION['config_information']['trusted_files|banned_username'])) {
				} else {
					$_SESSION['error_class_item'] -> addError ('files|banned_username', $_HOAM_language ['errors']['generic']['invalid-file']);
				}
			} else {
				if ($_HOAM_setting ['files']['banned_username'] == '') {
					$_SESSION['error_class_item'] -> addError ('files|banned_username', $_HOAM_language ['errors']['generic']['invalid-file']);
				}
			}
			if (isset ($_POST['files|censored_words'])) {
				$_SESSION['config_information']['trusted_files|censored_words'] = (string) escapeshellcmd ($group_members_class -> Clean ($_POST['files|censored_words'], 128));
				if (file_exists (HOAM_PATH . $_SESSION['config_information']['trusted_files|censored_words']) AND
					is_readable (HOAM_PATH . $_SESSION['config_information']['trusted_files|censored_words'])) {
				} else {
					$_SESSION['error_class_item'] -> addError ('files|censored_words', $_HOAM_language ['errors']['generic']['invalid-file']);
				}
			} else {
				if ($_HOAM_setting ['files']['censored_words'] == '') {
					$_SESSION['error_class_item'] -> addError ('files|censored_words', $_HOAM_language ['errors']['generic']['invalid-file']);
				}
			}

			/*******************************************************************
			 * Group settings
			 *******************************************************************/
			if (isset ($_POST['group|minimum_description_length'])) {
				$_SESSION['config_information']['trusted_group|minimum_description_length'] = HOAM_formValidateValue ('group|minimum_description_length', $group_class -> superClean ($_POST['group|minimum_description_length'], $group_class -> returnColumnSize ('description')), $_SESSION['error_class_item'], 0, $group_class -> returnColumnSize ('description'));
			}
			if (isset ($_POST['group|ids|admin'])) {
				$_SESSION['config_information']['trusted_group|ids|admin'] = $group_class -> superClean ($_POST['group|ids|admin'], $group_class -> returnColumnSize ('id'));
				if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|admin'])) {
					$_SESSION['error_class_item'] -> addError ('group|ids|admin', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['group|ids|anonymous'])) {
				$_SESSION['config_information']['trusted_group|ids|anonymous'] = $group_class -> superClean ($_POST['group|ids|anonymous'], $group_class -> returnColumnSize ('id'));
				if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|anonymous'])) {
					$_SESSION['error_class_item'] -> addError ('group|ids|anonymous', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['group|ids|board'])) {
				$_SESSION['config_information']['trusted_group|ids|board'] = $group_class -> superClean ($_POST['group|ids|board'], $group_class -> returnColumnSize ('id'));
				if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|board'])) {
					$_SESSION['error_class_item'] -> addError ('group|ids|board', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
            if (isset ($_POST['group|ids|advertising_add'])) {
                $_SESSION['config_information']['trusted_group|ids|advertising_add'] = $group_class -> superClean ($_POST['group|ids|advertising_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|advertising_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|advertising_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|advertising_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|advertising_delete'] = $group_class -> superClean ($_POST['group|ids|advertising_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|advertising_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|advertising_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|advertising_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|advertising_edit'] = $group_class -> superClean ($_POST['group|ids|advertising_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|advertising_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|advertising_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|article_add'])) {
                $_SESSION['config_information']['trusted_group|ids|article_add'] = $group_class -> superClean ($_POST['group|ids|article_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|article_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|article_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|article_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|article_delete'] = $group_class -> superClean ($_POST['group|ids|article_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|article_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|article_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|article_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|article_edit'] = $group_class -> superClean ($_POST['group|ids|article_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|article_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|article_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|attachment_add'])) {
                $_SESSION['config_information']['trusted_group|ids|attachment_add'] = $group_class -> superClean ($_POST['group|ids|attachment_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|attachment_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|attachment_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|attachment_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|attachment_delete'] = $group_class -> superClean ($_POST['group|ids|attachment_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|attachment_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|attachment_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|attachment_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|attachment_edit'] = $group_class -> superClean ($_POST['group|ids|attachment_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|attachment_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|attachment_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|attachment_view'])) {
                $_SESSION['config_information']['trusted_group|ids|attachment_view'] = $group_class -> superClean ($_POST['group|ids|attachment_view'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|attachment_view'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|attachment_view', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|budget_add'])) {
                $_SESSION['config_information']['trusted_group|ids|budget_add'] = $group_class -> superClean ($_POST['group|ids|budget_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|budget_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|budget_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|budget_approve'])) {
                $_SESSION['config_information']['trusted_group|ids|budget_approve'] = $group_class -> superClean ($_POST['group|ids|budget_approve'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|budget_approve'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|budget_approve', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|budget_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|budget_delete'] = $group_class -> superClean ($_POST['group|ids|budget_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|budget_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|budget_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|budget_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|budget_edit'] = $group_class -> superClean ($_POST['group|ids|budget_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|budget_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|budget_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|budget_view'])) {
                $_SESSION['config_information']['trusted_group|ids|budget_view'] = $group_class -> superClean ($_POST['group|ids|budget_view'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|budget_view'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|budget_view', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
			if (isset ($_POST['group|ids|everyone'])) {
				$_SESSION['config_information']['trusted_group|ids|everyone'] = $group_class -> superClean ($_POST['group|ids|everyone'], $group_class -> returnColumnSize ('id'));
				if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|everyone'])) {
					$_SESSION['error_class_item'] -> addError ('group|ids|everyone', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
            if (isset ($_POST['group|ids|group_add'])) {
                $_SESSION['config_information']['trusted_group|ids|group_add'] = $group_class -> superClean ($_POST['group|ids|group_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|group_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|group_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|group_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|group_delete'] = $group_class -> superClean ($_POST['group|ids|group_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|group_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|group_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|group_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|group_edit'] = $group_class -> superClean ($_POST['group|ids|group_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|group_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|group_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
			if (isset ($_POST['group|ids|homeowner'])) {
				$_SESSION['config_information']['trusted_group|ids|homeowner'] = $group_class -> superClean ($_POST['group|ids|homeowner'], $group_class -> returnColumnSize ('id'));
				if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|homeowner'])) {
					$_SESSION['error_class_item'] -> addError ('group|ids|homeowner', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
            if (isset ($_POST['group|ids|homeowner_add'])) {
                $_SESSION['config_information']['trusted_group|ids|homeowner_add'] = $group_class -> superClean ($_POST['group|ids|homeowner_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|homeowner_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|homeowner_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|homeowner_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|homeowner_delete'] = $group_class -> superClean ($_POST['group|ids|homeowner_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|homeowner_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|homeowner_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|homeowner_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|homeowner_edit'] = $group_class -> superClean ($_POST['group|ids|homeowner_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|homeowner_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|homeowner_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|insurance_add'])) {
                $_SESSION['config_information']['trusted_group|ids|insurance_add'] = $group_class -> superClean ($_POST['group|ids|insurance_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|insurance_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|insurance_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|insurance_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|insurance_delete'] = $group_class -> superClean ($_POST['group|ids|insurance_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|insurance_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|insurance_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|insurance_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|insurance_edit'] = $group_class -> superClean ($_POST['group|ids|insurance_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|insurance_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|insurance_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|insurance_view'])) {
                $_SESSION['config_information']['trusted_group|ids|insurance_view'] = $group_class -> superClean ($_POST['group|ids|insurance_view'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|insurance_view'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|insurance_view', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|lot_add'])) {
                $_SESSION['config_information']['trusted_group|ids|lot_add'] = $group_class -> superClean ($_POST['group|ids|lot_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|lot_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|lot_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|lot_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|lot_delete'] = $group_class -> superClean ($_POST['group|ids|lot_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|lot_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|lot_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|lot_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|lot_edit'] = $group_class -> superClean ($_POST['group|ids|lot_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|lot_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|lot_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|messageboard_add'])) {
                $_SESSION['config_information']['trusted_group|ids|messageboard_add'] = $group_class -> superClean ($_POST['group|ids|messageboard_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|messageboard_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|messageboard_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|messageboard_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|messageboard_delete'] = $group_class -> superClean ($_POST['group|ids|messageboard_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|messageboard_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|messageboard_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|messageboard_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|messageboard_edit'] = $group_class -> superClean ($_POST['group|ids|messageboard_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|messageboard_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|messageboard_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|messageboard_post'])) {
                $_SESSION['config_information']['trusted_group|ids|messageboard_post'] = $group_class -> superClean ($_POST['group|ids|messageboard_post'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|messageboard_post'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|messageboard_post', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|messageboard_view'])) {
                $_SESSION['config_information']['trusted_group|ids|messageboard_view'] = $group_class -> superClean ($_POST['group|ids|messageboard_view'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|messageboard_view'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|messageboard_view', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|news_add'])) {
                $_SESSION['config_information']['trusted_group|ids|news_add'] = $group_class -> superClean ($_POST['group|ids|news_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|news_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|news_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|news_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|news_delete'] = $group_class -> superClean ($_POST['group|ids|news_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|news_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|news_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|news_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|news_edit'] = $group_class -> superClean ($_POST['group|ids|news_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|news_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|news_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
			if (isset ($_POST['group|ids|officer'])) {
				$_SESSION['config_information']['trusted_group|ids|officer'] = $group_class -> superClean ($_POST['group|ids|officer'], $group_class -> returnColumnSize ('id'));
				if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|officer'])) {
					$_SESSION['error_class_item'] -> addError ('group|ids|officer', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['group|ids|registered'])) {
				$_SESSION['config_information']['trusted_group|ids|registered'] = $group_class -> superClean ($_POST['group|ids|registered'], $group_class -> returnColumnSize ('id'));
				if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|registered'])) {
					$_SESSION['error_class_item'] -> addError ('group|ids|registered', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['group|ids|resident'])) {
				$_SESSION['config_information']['trusted_group|ids|resident'] = $group_class -> superClean ($_POST['group|ids|resident'], $group_class -> returnColumnSize ('id'));
				if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|resident'])) {
					$_SESSION['error_class_item'] -> addError ('group|ids|resident', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
            if (isset ($_POST['group|ids|user_add'])) {
                $_SESSION['config_information']['trusted_group|ids|user_add'] = $group_class -> superClean ($_POST['group|ids|user_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|user_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|user_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|user_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|user_delete'] = $group_class -> superClean ($_POST['group|ids|user_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|user_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|user_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|user_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|user_edit'] = $group_class -> superClean ($_POST['group|ids|user_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|user_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|user_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|violation_add'])) {
                $_SESSION['config_information']['trusted_group|ids|violation_add'] = $group_class -> superClean ($_POST['group|ids|violation_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|violation_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|violation_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|violation_approve'])) {
                $_SESSION['config_information']['trusted_group|ids|violation_approve'] = $group_class -> superClean ($_POST['group|ids|violation_approve'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|violation_approve'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|violation_approve', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|violation_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|violation_delete'] = $group_class -> superClean ($_POST['group|ids|violation_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|violation_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|violation_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|budget_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|violation_edit'] = $group_class -> superClean ($_POST['group|ids|violation_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|violation_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|violation_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|work_request_add'])) {
                $_SESSION['config_information']['trusted_group|ids|work_request_add'] = $group_class -> superClean ($_POST['group|ids|work_request_add'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|work_request_add'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|work_request_add', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|work_request_delete'])) {
                $_SESSION['config_information']['trusted_group|ids|work_request_delete'] = $group_class -> superClean ($_POST['group|ids|work_request_delete'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|work_request_delete'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|work_request_delete', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
            if (isset ($_POST['group|ids|work_request_edit'])) {
                $_SESSION['config_information']['trusted_group|ids|work_request_edit'] = $group_class -> superClean ($_POST['group|ids|work_request_edit'], $group_class -> returnColumnSize ('id'));
                if (!$group_class -> idExists ($_SESSION['config_information']['trusted_group|ids|work_request_edit'])) {
                    $_SESSION['error_class_item'] -> addError ('group|ids|work_request_edit', $_HOAM_language ['errors']['generic']['invalid-id']);
                }
            }
			
			/*******************************************************************
			 * Homeowner settings
			 *******************************************************************/
			if (isset ($_POST['homeowner|default|city'])) {
				$_SESSION['config_information']['trusted_homeowner|default|city'] = (string) $group_members_class -> superClean ($_POST['homeowner|default|city'], 64);
			}
			if (isset ($_POST['homeowner|flags|bankrupt'])) {
				$_SESSION['config_information']['trusted_homeowner|flags|bankrupt'] = HOAM_formValidateFlagValue ('homeowner|flags|bankrupt', (int) $group_members_class -> superClean ($_POST['homeowner|flags|bankrupt'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['homeowner|flags|service_member'])) {
				$_SESSION['config_information']['trusted_homeowner|flags|service_member'] = HOAM_formValidateFlagValue ('homeowner|flags|service_member', (int) $group_members_class -> superClean ($_POST['homeowner|flags|service_member'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['homeowner|default|postalcode'])) {
				$_SESSION['config_information']['trusted_homeowner|default|postalcode'] = (string) $group_members_class -> superClean ($_POST['homeowner|default|postalcode'], $_HOAM_country ['postalcode']['length']);
			}
			if (isset ($_POST['homeowner|default|state'])) {
				$_SESSION['config_information']['trusted_homeowner|default|state'] = (string) $group_members_class -> superClean ($_POST['homeowner|default|state'], 64);
			}
			if (isset ($_POST['config|homeowner|send_current_resident'])) {
				$_SESSION['config_information']['trusted_homeowner|send_current_resident'] = (int) $group_members_class -> superClean ($_POST['config|homeowner|send_current_resident'], 1);
			} elseif (isset ($_POST['config|homeowner|send_current_resident_enable'])) {
				$_SESSION['config_information']['trusted_homeowner|send_current_resident'] = 0;
			}
			if (isset ($_POST['homeowner|minimum_comments_length'])) {
				$_SESSION['config_information']['trusted_homeowner|minimum_comments_length'] = HOAM_formValidateValue ('homeowner|minimum_comments_length', (int) $homeowner_class -> superClean ($_POST['homeowner|minimum_comments_length'], 20), $_SESSION['error_class_item'], 0, $homeowner_class -> returnColumnSize ('comments'));
			}
			if (isset ($_POST['homeowner|flags|bankrupt'])) {
				$_SESSION['config_information']['trusted_homeowner|flags|bankrupt'] = HOAM_formValidateFlagValue ('homeowner|flags|bankrupt', (int) $group_members_class -> superClean ($_POST['homeowner|flags|bankrupt'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['homeowner|flags|ignore_budget'])) {
				$_SESSION['config_information']['trusted_homeowner|flags|ignore_budget'] = HOAM_formValidateFlagValue ('homeowner|flags|ignore_budget', (int) $group_members_class -> superClean ($_POST['homeowner|flags|ignore_budget'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['homeowner|flags|ignore_violations'])) {
				$_SESSION['config_information']['trusted_homeowner|flags|ignore_violations'] = HOAM_formValidateFlagValue ('homeowner|flags|ignore_violations', (int) $group_members_class -> superClean ($_POST['homeowner|flags|ignore_violations'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['homeowner|flags|ignore_violations_temporary'])) {
				$_SESSION['config_information']['trusted_homeowner|flags|ignore_violations_temporary'] = HOAM_formValidateFlagValue ('homeowner|flags|ignore_violations_temporary', (int) $group_members_class -> superClean ($_POST['homeowner|flags|ignore_violations_temporary'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|homeowner|flags|no_fees'])) {
				$_SESSION['config_information']['trusted_config|homeowner|flags|no_fees'] = HOAM_formValidateFlagValue ('config|homeowner|flags|no_fees', (int) $group_members_class -> superClean ($_POST['config|homeowner|flags|no_fees'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['homeowner|flags|residence_mailing'])) {
				$_SESSION['config_information']['trusted_homeowner|flags|residence_mailing'] = HOAM_formValidateFlagValue ('homeowner|flags|residence_mailing', (int) $group_members_class -> superClean ($_POST['homeowner|flags|residence_mailing'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['homeowner|flags|resident'])) {
				$_SESSION['config_information']['trusted_homeowner|flags|resident'] = HOAM_formValidateFlagValue ('homeowner|flags|resident', (int) $group_members_class -> superClean ($_POST['homeowner|flags|resident'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['homeowner|flags|service_member'])) {
				$_SESSION['config_information']['trusted_homeowner|flags|service_member'] = HOAM_formValidateFlagValue ('homeowner|flags|service_member', (int) $group_members_class -> superClean ($_POST['homeowner|flags|service_member'], 20), $_SESSION ['error_class_item']);
			}

			/*******************************************************************
			 * Letter / Envelope settings
			 *******************************************************************/
			if (isset ($_POST['config|letter|envelope'])) {
				$_SESSION['config_information']['trusted_letter|envelope'] = (int) $group_members_class -> superClean ($_POST['config|letter|envelope'], 3);
			}
			if (isset ($_POST['config|letter|paper'])) {
				$_SESSION['config_information']['trusted_letter|paper'] = (int) $group_members_class -> superClean ($_POST['config|letter|paper'], 3);
			}

			/*******************************************************************
			 * Log settings
			 *******************************************************************/
			if (isset ($_POST['config|log|browser'])) {
				$_SESSION['config_information']['trusted_config|log|browser'] = (int) $group_members_class -> superClean ($_POST['config|log|browser'], 1);
			} elseif (isset ($_POST['config|log|browser_enable'])) {
				$_SESSION['config_information']['trusted_config|log|browser'] = 0;
			}
			
			$_SESSION['config_information']['trusted_log|destinations'] = 0;
			if (isset ($_POST['log|destination|db'])) {
				$_SESSION['config_information']['trusted_log|destination|db'] = (int) $group_members_class -> superClean ($_POST['log|destination|db'], 1);
				$_SESSION['config_information']['trusted_log|destinations'] += HOAM_LOG_DB;
			} elseif (isset ($_POST['log|destination|db_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['destinations'] & (int) HOAM_LOG_DB) {
					$_SESSION['config_information']['trusted_log|destinations'] += HOAM_LOG_DB;
				}
			}
			if (isset ($_POST['log|destination|file'])) {
				$_SESSION['config_information']['trusted_log|destination|file'] = (int) $group_members_class -> superClean ($_POST['log|destination|file'], 1);
				$_SESSION['config_information']['trusted_log|destinations'] += HOAM_LOG_FILE;
			} elseif (isset ($_POST['log|destination|file_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['destinations'] & (int) HOAM_LOG_FILE) {
					$_SESSION['config_information']['trusted_log|destinations'] += HOAM_LOG_FILE;
				}
			}
			if (isset ($_POST['log|destination|syslog'])) {
				$_SESSION['config_information']['trusted_log|destination|syslog'] = (int) $group_members_class -> superClean ($_POST['log|destination|syslog'], 1);
				$_SESSION['config_information']['trusted_log|destinations'] += HOAM_LOG_SYSLOG;
			} elseif (isset ($_POST['log|destination|syslog_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['destinations'] & (int) HOAM_LOG_SYSLOG) {
					$_SESSION['config_information']['trusted_log|destinations'] += HOAM_LOG_SYSLOG;
				}
			}
			$_SESSION['config_information']['trusted_log|levels'] = 0;
			if (isset ($_POST['log|level|emergency'])) {
				$_SESSION['config_information']['trusted_log|level|emergency'] = (int) $group_members_class -> superClean ($_POST['log|level|emergency'], 1);
				$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_EMERGENCY;
			} elseif (isset ($_POST['log|level|emergency_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_EMERGENCY) {
					$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_EMERGENCY;
				}
			}
			if (isset ($_POST['log|level|critical'])) {
				$_SESSION['config_information']['trusted_log|level|critical'] = (int) $group_members_class -> superClean ($_POST['log|level|critical'], 1);
				$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_CRITICAL;
			} elseif (isset ($_POST['log|level|critical_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_CRITICAL) {
					$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_CRITICAL;
				}
			}
			if (isset ($_POST['log|level|alert'])) {
				$_SESSION['config_information']['trusted_log|level|alert'] = (int) $group_members_class -> superClean ($_POST['log|level|alert'], 1);
				$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_ALERT;
			} elseif (isset ($_POST['log|level|alert_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_ALERT) {
					$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_ALERT;
				}
			}
			if (isset ($_POST['log|level|error'])) {
				$_SESSION['config_information']['trusted_log|level|error'] = (int) $group_members_class -> superClean ($_POST['log|level|error'], 1);
				$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_ERROR;
			} elseif (isset ($_POST['log|level|error_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_ERROR) {
					$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_ERROR;
				}
			}
			if (isset ($_POST['log|level|warning'])) {
				$_SESSION['config_information']['trusted_log|level|warning'] = (int) $group_members_class -> superClean ($_POST['log|level|warning'], 1);
				$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_WARNING;
			} elseif (isset ($_POST['log|level|warning_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_WARNING) {
					$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_WARNING;
				}
			}
			if (isset ($_POST['log|level|notice'])) {
				$_SESSION['config_information']['trusted_log|level|notice'] = (int) $group_members_class -> superClean ($_POST['log|level|notice'], 1);
				$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_NOTICE;
			} elseif (isset ($_POST['log|level|notice_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_NOTICE) {
					$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_NOTICE;
				}
			}
			if (isset ($_POST['log|level|informational'])) {
				$_SESSION['config_information']['trusted_log|level|informational'] = (int) $group_members_class -> superClean ($_POST['log|level|informational'], 1);
				$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_INFORMATIONAL;
			} elseif (isset ($_POST['log|level|informational_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_INFORMATIONAL) {
					$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_INFORMATIONAL;
				}
			}
			if (isset ($_POST['log|level|debug'])) {
				$_SESSION['config_information']['trusted_log|level|debug'] = (int) $group_members_class -> superClean ($_POST['log|level|debug'], 1);
				$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_DEBUG;
			} elseif (isset ($_POST['log|level|debug_enable'])) {
				// Let the value clear
			} else {
				// if the value was already set, and we're not modifying it,
				// then make sure it's saved. 
				if ((int) $_HOAM_setting ['log']['levels'] & (int) HOAM_MESSAGE_DEBUG) {
					$_SESSION['config_information']['trusted_log|levels'] += HOAM_MESSAGE_DEBUG;
				}
			}
			if (isset ($_POST['config|log|flags|advertisement'])) {
				$_SESSION['config_information']['trusted_log|flags|advertisement'] = HOAM_formValidateFlagValue ('config|log|flags|advertisement', (int) $group_members_class -> superClean ($_POST['config|log|flags|advertisement'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|article'])) {
				$_SESSION['config_information']['trusted_log|flags|article'] = HOAM_formValidateFlagValue ('config|log|flags|article', (int) $group_members_class -> superClean ($_POST['config|log|flags|article'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|attachment'])) {
				$_SESSION['config_information']['trusted_log|flags|attachment'] = HOAM_formValidateFlagValue ('config|log|flags|attachment', (int) $group_members_class -> superClean ($_POST['config|log|flags|attachment'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|budget_category'])) {
				$_SESSION['config_information']['trusted_log|flags|budget_category'] = HOAM_formValidateFlagValue ('config|log|flags|budget_category', (int) $group_members_class -> superClean ($_POST['config|log|flags|budget_category'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|budget_invoice'])) {
				$_SESSION['config_information']['trusted_log|flags|budget_invoice'] = HOAM_formValidateFlagValue ('config|log|flags|budget_invoice', (int) $group_members_class -> superClean ($_POST['config|log|flags|budget_invoice'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|budget_vendor'])) {
				$_SESSION['config_information']['trusted_log|flags|budget_vendor'] = HOAM_formValidateFlagValue ('config|log|flags|budget_vendor', (int) $group_members_class -> superClean ($_POST['config|log|flags|budget_vendor'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|group'])) {
				$_SESSION['config_information']['trusted_log|flags|group'] = HOAM_formValidateFlagValue ('config|log|flags|group', (int) $group_members_class -> superClean ($_POST['config|log|flags|group'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|homeowner'])) {
				$_SESSION['config_information']['trusted_log|flags|homeowner'] = HOAM_formValidateFlagValue ('config|log|flags|homeowner', (int) $group_members_class -> superClean ($_POST['config|log|flags|homeowner'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|insurance'])) {
				$_SESSION['config_information']['trusted_log|flags|insurance'] = HOAM_formValidateFlagValue ('config|log|flags|insurance', (int) $group_members_class -> superClean ($_POST['config|log|flags|insurance'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|lot'])) {
				$_SESSION['config_information']['trusted_log|flags|lot'] = HOAM_formValidateFlagValue ('config|log|flags|lot', (int) $group_members_class -> superClean ($_POST['config|log|flags|lot'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|messageboard'])) {
				$_SESSION['config_information']['trusted_log|flags|messageboard'] = HOAM_formValidateFlagValue ('config|log|flags|messageboard', (int) $group_members_class -> superClean ($_POST['config|log|flags|messageboard'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|news'])) {
				$_SESSION['config_information']['trusted_log|flags|news'] = HOAM_formValidateFlagValue ('config|log|flags|news', (int) $group_members_class -> superClean ($_POST['config|log|flags|news'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|property'])) {
				$_SESSION['config_information']['trusted_log|flags|property'] = HOAM_formValidateFlagValue ('config|log|flags|property', (int) $group_members_class -> superClean ($_POST['config|log|flags|property'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|upgrade'])) {
				$_SESSION['config_information']['trusted_log|flags|upgrade'] = HOAM_formValidateFlagValue ('config|log|flags|upgrade', (int) $group_members_class -> superClean ($_POST['config|log|flags|upgrade'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|user'])) {
				$_SESSION['config_information']['trusted_log|flags|user'] = HOAM_formValidateFlagValue ('config|log|flags|user', (int) $group_members_class -> superClean ($_POST['config|log|flags|user'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|violation'])) {
				$_SESSION['config_information']['trusted_log|flags|violation'] = HOAM_formValidateFlagValue ('config|log|flags|violation', (int) $group_members_class -> superClean ($_POST['config|log|flags|violation'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|violation_category'])) {
				$_SESSION['config_information']['trusted_log|flags|violation_category'] = HOAM_formValidateFlagValue ('config|log|flags|violation_category', (int) $group_members_class -> superClean ($_POST['config|log|flags|violation_category'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|violation_severity'])) {
				$_SESSION['config_information']['trusted_log|flags|violation_severity'] = HOAM_formValidateFlagValue ('config|log|flags|violation_severity', (int) $group_members_class -> superClean ($_POST['config|log|flags|violation_severity'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|log|flags|work_request'])) {
				$_SESSION['config_information']['trusted_log|flags|work_request'] = HOAM_formValidateFlagValue ('config|log|flags|work_request', (int) $group_members_class -> superClean ($_POST['config|log|flags|work_request'], 20), $_SESSION ['error_class_item']);
			}

			/*******************************************************************
			 * Lot settings
			 *******************************************************************/
			if (isset ($_POST['lot|common|area'])) {
				$_SESSION['config_information']['trusted_lot|common|area'] = (int) $lot_class -> superClean ($_POST['lot|common|area'], 1);
			} elseif (isset ($_POST['lot|common|area_enable'])) {
				$_SESSION['config_information']['trusted_lot|common|area'] = 0;
			}
			if (isset ($_POST['lot|common|clubhouse'])) {
				$_SESSION['config_information']['trusted_lot|common|clubhouse'] = (int) $lot_class -> superClean ($_POST['lot|common|clubhouse'], 1);
			} elseif (isset ($_POST['lot|common|clubhouse_enable'])) {
				$_SESSION['config_information']['trusted_lot|common|clubhouse'] = 0;
			}
			if (isset ($_POST['lot|common|pool'])) {
				$_SESSION['config_information']['trusted_lot|common|pool'] = (int) $lot_class -> superClean ($_POST['lot|common|pool'], 1);
			} elseif (isset ($_POST['lot|common|pool_enable'])) {
				$_SESSION['config_information']['trusted_lot|common|pool'] = 0;
			}
			if (isset ($_POST['lot|minimum_address_length'])) {
				$_SESSION['config_information']['trusted_lot|minimum_address_length'] = HOAM_formValidateValue ('minimum_address_length', $group_members_class -> superClean ($_POST['lot|minimum_address_length'], 5), $_SESSION['error_class_item'], 0, 5);
			}
			if (isset ($_POST['lot|minimum_building_length'])) {
				$_SESSION['config_information']['trusted_lot|minimum_building_length'] = HOAM_formValidateValue ('minimum_building_length', $group_members_class -> superClean ($_POST['lot|minimum_building_length'], 5), $_SESSION['error_class_item'], 0, 5);
			}
			if (isset ($_POST['lot|minimum_street_length'])) {
				$_SESSION['config_information']['trusted_lot|minimum_street_length'] = HOAM_formValidateValue ('minimum_street_length', $group_members_class -> superClean ($_POST['lot|minimum_street_length'], 5), $_SESSION['error_class_item'], 0, 64);
			}
			if (isset ($_POST['lot|minimum_suite_length'])) {
				$_SESSION['config_information']['trusted_lot|minimum_suite_length'] = HOAM_formValidateValue ('minimum_suite_length', $group_members_class -> superClean ($_POST['lot|minimum_suite_length'], 5), $_SESSION['error_class_item'], 0, 5);
			}
			if (isset ($_POST['lot|minimum_sqft_size'])) {
				$_SESSION['config_information']['trusted_lot|minimum_sqft_size'] = HOAM_formValidateValue ('minimum_sqft_size', $group_members_class -> superClean ($_POST['lot|minimum_sqft_size'], 5), $_SESSION['error_class_item'], 0, 5);
			}
			if (isset ($_POST['lot|minimum_block_length'])) {
				$_SESSION['config_information']['trusted_lot|minimum_block_length'] = HOAM_formValidateValue ('minimum_block_length', $group_members_class -> superClean ($_POST['lot|minimum_block_length'], 5), $_SESSION['error_class_item'], 0, 3);
			}
			if (isset ($_POST['lot|minimum_lot_length'])) {
				$_SESSION['config_information']['trusted_lot|minimum_lot_length'] = HOAM_formValidateValue ('minimum_lot_length', $group_members_class -> superClean ($_POST['lot|minimum_lot_length'], 5), $_SESSION['error_class_item'], 0, 3);
			}
			if (isset ($_POST['lot|minimum_plat_length'])) {
				$_SESSION['config_information']['trusted_lot|minimum_plat_length'] = HOAM_formValidateValue ('minimum_plat_length', $group_members_class -> superClean ($_POST['lot|minimum_plat_length'], 5), $_SESSION['error_class_item'], 0, 6);
			}
			if (isset ($_POST['lot|minimum_latitude_length'])) {
				$_SESSION['config_information']['trusted_lot|minimum_latitude_length'] = HOAM_formValidateValue ('minimum_latitude_length', $group_members_class -> superClean ($_POST['lot|minimum_latitude_length'], 5), $_SESSION['error_class_item'], 0, 11);
			}
			if (isset ($_POST['lot|minimum_longitude_length'])) {
				$_SESSION['config_information']['trusted_lot|minimum_longitude_length'] = HOAM_formValidateValue ('minimum_longitude_length', $group_members_class -> superClean ($_POST['lot|minimum_longitude_length'], 5), $_SESSION['error_class_item'], 0, 11);
			}
			if (isset ($_POST['config|lot|property'])) {
				$_SESSION['config_information']['trusted_lot|property'] = (int) $lot_class -> superClean ($_POST['config|lot|property'], 1);
			} elseif (isset ($_POST['config|lot|property_enable'])) {
				$_SESSION['config_information']['trusted_lot|property'] = 0;
			}
			if (isset ($_POST['config|property|flags|renew'])) {
				$_SESSION['config_information']['trusted_property|flags|renew'] = HOAM_formValidateFlagValue ('config|property|flags|renew', (int) $group_members_class -> superClean ($_POST['config|property|flags|renew'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|property|flags|rented'])) {
				$_SESSION['config_information']['trusted_property|flags|rented'] = HOAM_formValidateFlagValue ('config|property|flags|rented', (int) $group_members_class -> superClean ($_POST['config|property|flags|rented'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|property|flags|annual'])) {
				$_SESSION['config_information']['trusted_property|flags|annual'] = HOAM_formValidateFlagValue ('config|property|flags|annual', (int) $group_members_class -> superClean ($_POST['config|property|flags|annual'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|property|flags|monthly'])) {
				$_SESSION['config_information']['trusted_property|flags|monthly'] = HOAM_formValidateFlagValue ('config|property|flags|monthly', (int) $group_members_class -> superClean ($_POST['config|property|flags|monthly'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|property|flags|weekly'])) {
				$_SESSION['config_information']['trusted_property|flags|weekly'] = HOAM_formValidateFlagValue ('config|property|flags|weekly', (int) $group_members_class -> superClean ($_POST['config|property|flags|weekly'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|property|flags|daily'])) {
				$_SESSION['config_information']['trusted_property|flags|daily'] = HOAM_formValidateFlagValue ('config|property|flags|daily', (int) $group_members_class -> superClean ($_POST['config|property|flags|daily'], 20), $_SESSION ['error_class_item']);
			}

			/*******************************************************************
			 * Messageboard settings
			 *******************************************************************/
			if (isset ($_POST['config|messageboard|flags|display_top'])) {
				$_SESSION['config_information']['trusted_messageboard|flags|display_top'] = HOAM_formValidateFlagValue ('config|messageboard|flags|display_top', (int) $group_members_class -> superClean ($_POST['config|messageboard|flags|display_top'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|messageboard|flags|do_not_delete'])) {
				$_SESSION['config_information']['trusted_messageboard|flags|do_not_delete'] = HOAM_formValidateFlagValue ('config|messageboard|flags|do_not_delete', (int) $group_members_class -> superClean ($_POST['config|messageboard|flags|do_not_delete'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|messageboard|flags|no_more_comments'])) {
				$_SESSION['config_information']['trusted_messageboard|flags|no_more_comments'] = HOAM_formValidateFlagValue ('config|messageboard|flags|no_more_comments', (int) $group_members_class -> superClean ($_POST['config|messageboard|flags|no_more_comments'], 20), $_SESSION ['error_class_item']);
			}
			
			/*******************************************************************
			 * Organization settings
			 *******************************************************************/
			if (isset ($_POST['organization|letter|head'])) {
				$_SESSION['config_information']['trusted_organization|letter|head'] = (string) $group_members_class -> Clean ($_POST['organization|letter|head'], 4096);
			}
			if (isset ($_POST['organization|letter|salutation'])) {
				$_SESSION['config_information']['trusted_organization|letter|salutation'] = (string) $group_members_class -> Clean ($_POST['organization|letter|salutation'], 512);
			}
			if (isset ($_POST['organization|letter|signature'])) {
				$_SESSION['config_information']['trusted_organization|letter|signature'] = (string) $group_members_class -> Clean ($_POST['organization|letter|signature'], 512);
			}
			if (isset ($_POST['organization|letter|footer'])) {
				$_SESSION['config_information']['trusted_organization|letter|footer'] = (string) $group_members_class -> Clean ($_POST['organization|letter|footer'], 4096);
			}
			if (isset ($_POST['organization|mailing_address|line1'])) {
				$_SESSION['config_information']['trusted_organization|mailing_address|line1'] = (string) $group_members_class -> superClean ($_POST['organization|mailing_address|line1'], 255);
			}
			if (isset ($_POST['organization|mailing_address|line2'])) {
				$_SESSION['config_information']['trusted_organization|mailing_address|line2'] = (string) $group_members_class -> superClean ($_POST['organization|mailing_address|line2'], 255);
			}
			if (isset ($_POST['organization|mailing_address|line3'])) {
				$_SESSION['config_information']['trusted_organization|mailing_address|line3'] = (string) $group_members_class -> superClean ($_POST['organization|mailing_address|line3'], 255);
			}
			if (isset ($_POST['organization|mailing_address|city'])) {
				$_SESSION['config_information']['trusted_organization|mailing_address|city'] = (string) $group_members_class -> superClean ($_POST['organization|mailing_address|city'], 255);
			}
			if (isset ($_POST['organization|mailing_address|postalcode'])) {
				// Pad the length slightly to include dashes and other punc.
				$_SESSION['config_information']['trusted_organization|mailing_address|postalcode'] = (string) $group_members_class -> superClean ($_POST['organization|mailing_address|postalcode'], $_HOAM_country ['postalcode']['length'] + 2);
			}

			// We don't require a telephone number, but if one is entered make
			// sure it is valid.
			if (isset ($_POST['organization|phone|telephone'])) {
				$_SESSION['config_information']['trusted_organization|phone|telephone'] = (string) $group_members_class -> superClean ($_POST['organization|phone|telephone'], $_HOAM_country ['telephone']['length']);
				if (mb_strlen ($_SESSION['config_information']['trusted_organization|phone|telephone']) > 0) {
					if (preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION ['config_information']['trusted_organization|phone|telephone'], 1)) {
					} else {
						// The telephone # is invalid.
						$_SESSION ['error_class_item'] -> addError ('organization|phone|telephone', $_HOAM_language ['errors']['generic']['invalid-telephone']);
					}
				}
			}
			// We don't require a telephone number, but if one is entered make
			// sure it is valid.
			if (isset ($_POST['organization|phone|fax'])) {
				$_SESSION['config_information']['trusted_organization|phone|fax'] = (string) $group_members_class -> superClean ($_POST['organization|phone|fax'], $_HOAM_country ['telephone']['length']);
				if (mb_strlen ($_SESSION['config_information']['trusted_organization|phone|fax']) > 0) {
					if (preg_replace ($_HOAM_country ['telephone']['match'], $_HOAM_country ['telephone']['format'], $_SESSION ['config_information']['trusted_organization|phone|fax'], 1)) {
					} else {
						// The telephone # is invalid.
						$_SESSION ['error_class_item'] -> addError ('organization|phone|fax', $_HOAM_language ['errors']['generic']['invalid-telephone']);
					}
				}
			}

			if (isset ($_POST['organization|physical_address|line1'])) {
				$_SESSION['config_information']['trusted_organization|physical_address|line1'] = (string) $group_members_class -> superClean ($_POST['organization|physical_address|line1'], 255);
			}
			if (isset ($_POST['organization|physical_address|line2'])) {
				$_SESSION['config_information']['trusted_organization|physical_address|line2'] = (string) $group_members_class -> superClean ($_POST['organization|physical_address|line2'], 255);
			}
			if (isset ($_POST['organization|physical_address|line3'])) {
				$_SESSION['config_information']['trusted_organization|physical_address|line3'] = (string) $group_members_class -> superClean ($_POST['organization|physical_address|line3'], 255);
			}
			if (isset ($_POST['organization|physical_address|city'])) {
				$_SESSION['config_information']['trusted_organization|physical_address|city'] = (string) $group_members_class -> superClean ($_POST['organization|physical_address|city'], 255);
			}
			if (isset ($_POST['organization|physical_address|county'])) {
				$_SESSION['config_information']['trusted_organization|physical_address|county'] = (string) $group_members_class -> superClean ($_POST['organization|physical_address|county'], 255);
			}
			if (isset ($_POST['organization|physical_address|postalcode'])) {
				// Pad the length slightly to include dashes and other punc.
				$_SESSION['config_information']['trusted_organization|physical_address|postalcode'] = (string) $group_members_class -> superClean ($_POST['organization|physical_address|postalcode'], $_HOAM_country ['postalcode']['length'] + 2);
			}
			if (isset ($_POST['organization|name'])) {
				$_SESSION['config_information']['trusted_organization|name'] = (string) $group_members_class -> superClean ($_POST['organization|name'], 255);
			}
			if (isset ($_POST['organization|property_name'])) {
				$_SESSION['config_information']['trusted_organization|property_name'] = (string) $group_members_class -> superClean ($_POST['organization|property_name'], 255);
			}
			if (isset ($_POST['organization|email|board'])) {
				$_SESSION['config_information']['trusted_organization|email|board'] = (string) $group_members_class -> superClean ($_POST['organization|email|board'], 256);
				if (!filter_var ($_SESSION['config_information']['trusted_organization|email|board'], FILTER_VALIDATE_EMAIL)) {
					$_SESSION['error_class_item'] -> addError ('organization|email|board', $_HOAM_language ['errors']['generic']['invalid-email']);
				}
			}
			if (isset ($_POST['organization|email|officers'])) {
				$_SESSION['config_information']['trusted_organization|email|officers'] = (string) $group_members_class -> superClean ($_POST['organization|email|officers'], 256);
				if (!filter_var ($_SESSION['config_information']['trusted_organization|email|officers'], FILTER_VALIDATE_EMAIL)) {
					$_SESSION['error_class_item'] -> addError ('organization|email|officers', $_HOAM_language ['errors']['generic']['invalid-email']);
				}
			}
			if (isset ($_POST['organization|email|management'])) {
				$_SESSION['config_information']['trusted_organization|email|management'] = (string) $group_members_class -> superClean ($_POST['organization|email|management'], 256);
				if (mb_strlen ($_SESSION['config_information']['trusted_organization|email|management']) > 0) {
					if (!filter_var ($_SESSION['config_information']['trusted_organization|email|management'], FILTER_VALIDATE_EMAIL)) {
						$_SESSION['error_class_item'] -> addError ('organization|email|management', $_HOAM_language ['errors']['generic']['invalid-email']);
					}
				}
			}
			if (isset ($_POST['organization|fee|expedite'])) {
				$_SESSION['config_information']['trusted_organization|fee|expedite'] = HOAM_formValidateValue ('organization|fee|expedite', $group_members_class -> superClean ($_POST['organization|fee|expedite'], 5), $_SESSION['error_class_item'], 0, 1000);
			}
			if (isset ($_POST['organization|fee|refinance'])) {
				$_SESSION['config_information']['trusted_organization|fee|refinance'] = HOAM_formValidateValue ('organization|fee|refinance', $group_members_class -> superClean ($_POST['organization|fee|refinance'], 5), $_SESSION['error_class_item'], 0, 1000);
			}
			if (isset ($_POST['organization|fee|resale'])) {
				$_SESSION['config_information']['trusted_organization|fee|resale'] = HOAM_formValidateValue ('organization|fee|resale', $group_members_class -> superClean ($_POST['organization|fee|resale'], 5), $_SESSION['error_class_item'], 0, 1000);
			}

			/*******************************************************************
			 * User settings
			 *******************************************************************/
			if (isset ($_POST['config|user|ids|root'])) {
				$_SESSION['config_information']['trusted_user|ids|root'] = (string) $user_class -> superClean ($_POST['config|user|ids|root'], $user_class -> returnColumnSize ('id'));
				if (!$user_class -> idExists ($_SESSION['config_information']['trusted_user|ids|root'])) {
					$_SESSION['error_class_item'] -> addError ('config|user|ids|root', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['config|user|ids|system'])) {
				$_SESSION['config_information']['trusted_user|ids|system'] = (string) $user_class -> superClean ($_POST['config|user|ids|system'], $user_class -> returnColumnSize ('id'));
				if (!$user_class -> idExists ($_SESSION['config_information']['trusted_user|ids|system'])) {
					$_SESSION['error_class_item'] -> addError ('config|user|ids|system', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['config|user|email_validation'])) {
				$_SESSION['config_information']['trusted_user|email_validation'] = (int) $group_members_class -> superClean ($_POST['config|user|email_validation'], 1);
			} elseif (isset ($_POST['config|user|email_validation_enable'])) {
				$_SESSION['config_information']['trusted_user|email_validation'] = 0;
			}
			if (isset ($_POST['config|user|days_before_delete'])) {
				// 1826 is 5 years (assuming the possibility of 1 leap year).
				$_SESSION['config_information']['trusted_user|days_before_delete'] = HOAM_formValidateValue ('config|user|days_before_delete', (int) $user_class -> superClean ($_POST['config|user|days_before_delete'], 20), $_SESSION['error_class_item'], 90, 1826);
			}
			if (isset ($_POST['config|user|days_before_disable'])) {
				// 1096 is 3 years (assuming the possibility of a leap year).
				$_SESSION['config_information']['trusted_user|days_before_disable'] = HOAM_formValidateValue ('config|user|days_before_disable', (int) $user_class -> superClean ($_POST['config|user|days_before_disable'], 20), $_SESSION['error_class_item'], 14, 1096);
			}
			if (isset ($_POST['config|user|days_password_age'])) {
				// 1096 is 3 years (assuming the possibility of a leap year).
				$_SESSION['config_information']['trusted_user|days_password_age'] = HOAM_formValidateValue ('config|user|days_password_age', (int) $user_class -> superClean ($_POST['config|user|days_password_age'], 20), $_SESSION['error_class_item'], 30, 1096);
			}
			if (isset ($_POST['config|user|days_password_warn'])) {
				// 731 is 2 years (assuming the possibility of a leap year).
				$_SESSION['config_information']['trusted_user|days_password_warn'] = HOAM_formValidateValue ('config|user|days_password_warn', (int) $user_class -> superClean ($_POST['config|user|days_password_warn'], 20), $_SESSION['error_class_item'], 21, 731);
			}
			if (isset ($_POST['config|user|flags|acronyms'])) {
				$_SESSION['config_information']['trusted_user|flags|acronyms'] = HOAM_formValidateFlagValue ('config|user|flags|acronyms', (int) $user_class -> superClean ($_POST['config|user|flags|acronyms'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|user|flags|censor'])) {
				$_SESSION['config_information']['trusted_user|flags|censor'] = HOAM_formValidateFlagValue ('config|user|flags|censor', (int) $user_class -> superClean ($_POST['config|user|flags|censor'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|user|flags|convert_links'])) {
				$_SESSION['config_information']['trusted_user|flags|convert_links'] = HOAM_formValidateFlagValue ('config|user|flags|convert_links', (int) $user_class -> superClean ($_POST['config|user|flags|convert_links'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|user|flags|disabled'])) {
				$_SESSION['config_information']['trusted_user|flags|disabled'] = HOAM_formValidateFlagValue ('config|user|flags|disabled', (int) $user_class -> superClean ($_POST['config|user|flags|disabled'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|user|flags|email_validated'])) {
				$_SESSION['config_information']['trusted_user|flags|email_validated'] = HOAM_formValidateFlagValue ('config|user|flags|email_validated', (int) $user_class -> superClean ($_POST['config|user|flags|email_validated'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|user|flags|holidays'])) {
				$_SESSION['config_information']['trusted_user|flags|holidays'] = HOAM_formValidateFlagValue ('config|user|flags|holidays', (int) $user_class -> superClean ($_POST['config|user|flags|holidays'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|user|flags|password_old'])) {
				$_SESSION['config_information']['trusted_user|flags|password_old'] = HOAM_formValidateFlagValue ('config|user|flags|password_old', (int) $user_class -> superClean ($_POST['config|user|flags|password_old'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|user|flags|smileys'])) {
				$_SESSION['config_information']['trusted_user|flags|smileys'] = HOAM_formValidateFlagValue ('config|user|flags|smileys', (int) $user_class -> superClean ($_POST['config|user|flags|smileys'], 20), $_SESSION ['error_class_item']);
			}
			
			/*******************************************************************
			 * Violation settings
			 *******************************************************************/
			if (isset ($_POST['config|violation|approval_group'])) {
				$_SESSION['config_information']['trusted_violation|approval_group'] = $group_class -> superClean ($_POST['config|violation|approval_group'], $group_class -> returnColumnSize ('id'));
				if (!$group_class -> idExists ($_SESSION['config_information']['trusted_violation|approval_group'])) {
					$_SESSION['error_class_item'] -> addError ('config|violation|approval_group', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['config|violation|require_approval'])) {
				$_SESSION['config_information']['trusted_violation|require_approval'] = (int) $lot_class -> superClean ($_POST['config|violation|require_approval'], 1);
			} elseif (isset ($_POST['config|violation|require_approval_enable'])) {
				$_SESSION['config_information']['trusted_violation|require_approval'] = 0;
			}
			if (isset ($_POST['config|violation|grace_after_purchase'])) {
				$_SESSION['config_information']['trusted_violation|grace_after_purchase'] = HOAM_formValidateValue ('violation|grace_after_purchase', $group_members_class -> superClean ($_POST['config|violation|grace_after_purchase'], 5), $_SESSION['error_class_item'], 0, 365);
			}
			if (isset ($_POST['violation|days_to_reset'])) {
				$_SESSION['config_information']['trusted_violation|days_to_reset'] = (int) $group_members_class -> superClean ($_POST['violation|days_to_reset'], 4);
			}
			// Approval flags
			if (isset ($_POST['config|violation|flags|needs_approval'])) {
				$_SESSION['config_information']['trusted_violation|flags|needs_approval'] = HOAM_formValidateFlagValue ('config|violation|flags|needs_approval', (int) $group_members_class -> superClean ($_POST['config|violation|flags|needs_approval'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|violation|flags|more_information'])) {
				$_SESSION['config_information']['trusted_violation|flags|more_information'] = HOAM_formValidateFlagValue ('config|violation|flags|more_information', (int) $group_members_class -> superClean ($_POST['config|violation|flags|more_information'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|violation|flags|approved'])) {
				$_SESSION['config_information']['trusted_violation|flags|approved'] = HOAM_formValidateFlagValue ('config|violation|flags|approved', (int) $group_members_class -> superClean ($_POST['config|violation|flags|approved'], 20), $_SESSION ['error_class_item']);
			}
			if (isset ($_POST['config|violation|flags|not_approved'])) {
				$_SESSION['config_information']['trusted_violation|flags|not_approved'] = HOAM_formValidateFlagValue ('config|violation|flags|not_approved', (int) $group_members_class -> superClean ($_POST['config|violation|flags|not_approved'], 20), $_SESSION ['error_class_item']);
			}
			// Violation flags
			if (isset ($_POST['config|violation|flags|assume_resolved'])) {
				$_SESSION['config_information']['trusted_violation|flags|assume_resolved'] = HOAM_formValidateFlagValue ('config|violation|flags|assume_resolved', (int) $group_members_class -> superClean ($_POST['config|violation|flags|assume_resolved'], 20), $_SESSION ['error_class_item']);
			}
			
			/*******************************************************************
			 * Website settings
			 *******************************************************************/
			if (isset ($_POST['website|locale'])) {
				$_SESSION['config_information']['trusted_website|locale'] = (string) $group_members_class -> superClean ($_POST['website|locale'], 64);
			}
			if (isset ($_POST['website|mask_character'])) {
				$_SESSION['config_information']['trusted_website|mask_character'] = (string) $group_members_class -> superClean ($_POST['website|mask|character'], 1);
			}
			if (isset ($_POST['website|email|abuse'])) {
				$_SESSION['config_information']['trusted_website|email|abuse'] = (string) $group_members_class -> superClean ($_POST['website|email|abuse'], 256);
				if (mb_strlen ($_SESSION['config_information']['trusted_website|email|abuse']) > 0) {
					if (!filter_var ($_SESSION['config_information']['trusted_website|email|abuse'], FILTER_VALIDATE_EMAIL)) {
						$_SESSION['error_class_item'] -> addError ('website|email|abuse', $_HOAM_language ['errors']['generic']['invalid-email']);
					}
				}
			}
			if (isset ($_POST['website|email|root'])) {
				$_SESSION['config_information']['trusted_website|email|root'] = (string) $group_members_class -> superClean ($_POST['website|email|root'], 256);
				if (mb_strlen ($_SESSION['config_information']['trusted_website|email|root']) > 0) {
					if (!filter_var ($_SESSION['config_information']['trusted_website|email|root'], FILTER_VALIDATE_EMAIL)) {
						$_SESSION['error_class_item'] -> addError ('website|email|root', $_HOAM_language ['errors']['generic']['invalid-email']);
					}
				}
			}
			if (isset ($_POST['website|email|webmaster'])) {
				$_SESSION['config_information']['trusted_website|email|webmaster'] = (string) $group_members_class -> superClean ($_POST['website|email|webmaster'], 256);
				if (mb_strlen ($_SESSION['config_information']['trusted_website|email|webmaster']) > 0) {
					if (!filter_var ($_SESSION['config_information']['trusted_website|email|root'], FILTER_VALIDATE_EMAIL)) {
						$_SESSION['error_class_item'] -> addError ('website|email|root', $_HOAM_language ['errors']['generic']['invalid-email']);
					}
				}
			}
			if (isset ($_POST['website|hostname'])) {
				$_SESSION['config_information']['trusted_website|hostname'] = (string) $group_members_class -> superClean ($_POST['website|hostname'], 64);
				if (!filter_var ($_SESSION['config_information']['trusted_website|hostname'], FILTER_VALIDATE_DOMAIN)) {
					$_SESSION['error_class_item'] -> addError ('website|hostname', $_HOAM_language ['errors']['generic']['invalid-url']);
				}
			}
			if (isset ($_POST['website|title'])) {
				$_SESSION['config_information']['trusted_website|title'] = (string) $group_members_class -> superClean ($_POST['website|title'], 64);
			}
			if (isset ($_POST['website|timezone'])) {
				$_SESSION['config_information']['trusted_website|timezone'] = (string) $group_members_class -> superClean ($_POST['website|timezone'], 64);
			}
			if (isset ($_POST['website|url'])) {
				$_SESSION['config_information']['trusted_website|url'] = (string) $group_members_class -> superClean ($_POST['website|url'], 128);
				// Force a trailing slash at the end of the URL.
				if ($_SESSION['config_information']['trusted_website|url'][mb_strlen($_SESSION['config_information']['trusted_website|url']) - 1] != '/') {
					$_SESSION['config_information']['trusted_website|url'] .= '/';
				}
			}
			if (isset ($_POST['config|website|blurb'])) {
				$_SESSION['config_information']['trusted_website|blurb'] = (string) $group_members_class -> Clean ($_POST['config|website|blurb'], 4096);
			}
			if (isset ($_POST['config|website|online_time'])) {
				$_SESSION['config_information']['trusted_website|online_time'] = HOAM_formValidateValue ('config|website|online_time', (int) $group_members_class -> superClean ($_POST['config|website|online_time'], 20), $_SESSION['error_class_item'], 1, 999);
			}
			if (isset ($_POST['config|website|idle_time'])) {
				$_SESSION['config_information']['trusted_website|idle_time'] = HOAM_formValidateValue ('config|website|idle_time', (int) $group_members_class -> superClean ($_POST['config|website|idle_time'], 20), $_SESSION['error_class_item'], 5, 999);
			}
			if (isset ($_POST['config|website|record'])) {
				$_SESSION['config_information']['trusted_website|record'] = (int) $group_members_class -> superClean ($_POST['config|website|record'], 1);
			} elseif (isset ($_POST['config|website|record_enable'])) {
				$_SESSION['config_information']['trusted_website|record'] = 0;
			}

			/*******************************************************************
			 * Wiki / Article settings
			 *******************************************************************/
			if (isset ($_POST['article|minimum_article_length'])) {
				$_SESSION['config_information']['trusted_article|minimum_article_length'] = HOAM_formValidateValue ('article|minimum_article_length', (int) $article_class -> superClean ($_POST['article|minimum_article_length'], 20), $_SESSION['error_class_item'], 0, $article_class -> returnColumnSize ('article'));
			}
			if (isset ($_POST['article|minimum_keywords_length'])) {
				$_SESSION['config_information']['trusted_article|minimum_keywords_length'] = HOAM_formValidateValue ('article|minimum_keywords_length', (int) $article_class -> superClean ($_POST['article|minimum_keywords_length'], 20), $_SESSION['error_class_item'], 0, $article_class -> returnColumnSize ('keywords'));
			}
			if (isset ($_POST['article|minimum_leadin_length'])) {
				$_SESSION['config_information']['trusted_article|minimum_leadin_length'] = HOAM_formValidateValue ('article|minimum_leadin_length', (int) $article_class -> superClean ($_POST['article|minimum_leadin_length'], 20), $_SESSION['error_class_item'], 0, $article_class -> returnColumnSize ('leadin'));
			}
			if (isset ($_POST['article|minimum_summary_length'])) {
				$_SESSION['config_information']['trusted_article|minimum_summary_length'] = HOAM_formValidateValue ('article|minimum_summary_length', (int) $article_class -> superClean ($_POST['article|minimum_summary_length'], 20), $_SESSION['error_class_item'], 0, $article_class -> returnColumnSize ('summary'));
			}
			if (isset ($_POST['config|wiki|default_groups'])) {
				// if groups is set, then we need to verify the group membership.
				$_SESSION['config_information']['trusted_wiki|default_groups'] = array ();
				foreach ($_POST['config|wiki|default_groups'] as $group) {
					// First, find out if the group id is valid
					$group = (string) $group_class -> superClean ($group, $group_class -> returnColumnSize ('id'));
					if ($group_class -> idExists ($group)) {
						// Now, we need to make sure that the current user has access to a the group requested.
						if ($group_members_class -> isMember ($group, $_SESSION['current_user']['id'])) {
							// This is a valid group, and the current user has the appropriate group membership.
							array_push ($_SESSION['config_information']['trusted_wiki|default_groups'], $group);
						} else {
							// Users who are members of the system
							// administrators group and/or the root user are
							// exempt from this check.
							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
								($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
								array_push ($_SESSION['config_information']['trusted_wiki|default_groups'], $group);
							} else {
								$_SESSION['error_class_item'] -> addError ('config|wiki|all_groups', $_HOAM_language ['errors']['group']['not_member']);
								$_HOAM_log -> add ('Validate: user "' . $user_class -> returnAccountName ($_SESSION['current_user']['id']) . '" attempted (but failed) to assign "' . $group_class -> returnName ($group) . '" group to an article. This is a security, not a system issue.', HOAM_MESSAGE_ALERT);
							}
						}
					}
				}
				// Make sure the Administrators group was selected to have access. If it's not a member of the group list, add it.
				if (!in_array ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['config_information']['trusted_wiki|default_groups'])) {
					// Force the addition of the administrators group.
					array_push ($_SESSION['config_information']['trusted_wiki|default_groups'], $_HOAM_setting ['group']['ids']['admin']);
				}
			} else {
				// Force the addition of the administrators group.
				$_SESSION['config_information']['trusted_wiki|default_groups'] = array ($_HOAM_setting ['group']['ids']['admin']);
			}
			if (isset ($_POST['article|root_id'])) {
				$_SESSION['config_information']['trusted_article|root_id'] = $article_class -> superClean ($_POST['article|root_id'], $article_class -> returnColumnSize ('id'));
				if (!$article_class -> idExists ($_SESSION['config_information']['trusted_article|root_id'])) {
					$_SESSION['error_class_item'] -> addError ('article|root_id', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			
			if (isset ($_POST['article|flags|comments'])) {
				$_SESSION['config_information']['trusted_article|flags|comments'] = HOAM_formValidateFlagValue ('article|flags|comments', (int) $article_class -> superClean ($_POST['article|flags|comments'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['article|flags|draft'])) {
				$_SESSION['config_information']['trusted_article|flags|draft'] = HOAM_formValidateFlagValue ('article|flags|draft', (int) $article_class -> superClean ($_POST['article|flags|draft'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['article|flags|redirect'])) {
				$_SESSION['config_information']['trusted_article|flags|redirect'] = HOAM_formValidateFlagValue ('article|flags|redirect', (int) $article_class -> superClean ($_POST['article|flags|redirect'], 20), $_SESSION['error_class_item']);
			}

			/*******************************************************************
			 * Work Request settings
			 *******************************************************************/
			if (isset ($_POST['config|work_request|approval_group'])) {
				$_SESSION['config_information']['trusted_work_request|approval_group'] = $group_class -> superClean ($_POST['config|work_request|approval_group'], $group_class -> returnColumnSize ('id'));
				if (!$group_class -> idExists ($_SESSION['config_information']['trusted_work_request|approval_group'])) {
					$_SESSION['error_class_item'] -> addError ('config|work_request|approval_group', $_HOAM_language ['errors']['generic']['invalid-id']);
				}
			}
			if (isset ($_POST['work_request|allow_user_requests'])) {
				$_SESSION['config_information']['trusted_work_request|allow_user_requests'] = (int) $group_members_class -> superClean ($_POST['work_request|allow_user_requests'], 1);
			} elseif (isset ($_POST['work_request|allow_user_requests_enable'])) {
				$_SESSION['config_information']['trusted_work_request|allow_user_requests'] = 0;
			}
			if (isset ($_POST['work_request|minimum_title_length'])) {
				$_SESSION['config_information']['trusted_work_request|minimum_title_length'] = HOAM_formValidateValue ('work_request|minimum_title_length', (int) $work_request_class -> superClean ($_POST['work_request|minimum_title_length'], 20), $_SESSION['error_class_item'], 0, $work_request_class -> returnColumnSize ('title'));
			}
			if (isset ($_POST['work_request|minimum_description_length'])) {
				$_SESSION['config_information']['trusted_work_request|minimum_description_length'] = HOAM_formValidateValue ('work_request|minimum_description_length', (int) $work_request_class -> superClean ($_POST['work_request|minimum_description_length'], 20), $_SESSION['error_class_item'], 0, $work_request_class -> returnColumnSize ('description'));
			}
			if (isset ($_POST['work_request|minimum_notes_length'])) {
				$_SESSION['config_information']['trusted_work_request|minimum_notes_length'] = HOAM_formValidateValue ('work_request|minimum_notes_length', (int) $work_request_class -> superClean ($_POST['work_request|minimum_notes_length'], 20), $_SESSION['error_class_item'], 0, $work_request_class -> returnColumnSize ('notes'));
			}

			// Work request flags
			if (isset ($_POST['work_request|flags|require_approval'])) {
				$_SESSION['config_information']['trusted_work_request|flags|require_approval'] = HOAM_formValidateFlagValue ('work_request|flags|require_approval', (int) $group_members_class -> superClean ($_POST['work_request|flags|require_approval'], 20), $_SESSION['error_class_item']);
			}
			
			// Priority flags
			if (isset ($_POST['work_request|flags|low'])) {
				$_SESSION['config_information']['trusted_work_request|flags|low'] = HOAM_formValidateFlagValue ('work_request|flags|low', (int) $group_members_class -> superClean ($_POST['work_request|flags|low'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|normal'])) {
				$_SESSION['config_information']['trusted_work_request|flags|normal'] = HOAM_formValidateFlagValue ('work_request|flags|normal', (int) $group_members_class -> superClean ($_POST['work_request|flags|normal'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|high'])) {
				$_SESSION['config_information']['trusted_work_request|flags|high'] = HOAM_formValidateFlagValue ('work_request|flags|high', (int) $group_members_class -> superClean ($_POST['work_request|flags|high'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|urgent'])) {
				$_SESSION['config_information']['trusted_work_request|flags|urgent'] = HOAM_formValidateFlagValue ('work_request|flags|urgent', (int) $group_members_class -> superClean ($_POST['work_request|flags|urgent'], 20), $_SESSION['error_class_item']);
			}

			// Approval flags
			if (isset ($_POST['work_request|flags|needs_approval'])) {
				$_SESSION['config_information']['trusted_work_request|flags|needs_approval'] = HOAM_formValidateFlagValue ('work_request|flags|needs_approval', (int) $group_members_class -> superClean ($_POST['work_request|flags|needs_approval'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|approved'])) {
				$_SESSION['config_information']['trusted_work_request|flags|more_information'] = HOAM_formValidateFlagValue ('work_request|flags|more_information', (int) $group_members_class -> superClean ($_POST['work_request|flags|more_information'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|approved'])) {
				$_SESSION['config_information']['trusted_work_request|flags|approved'] = HOAM_formValidateFlagValue ('work_request|flags|approved', (int) $group_members_class -> superClean ($_POST['work_request|flags|approved'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|approved'])) {
				$_SESSION['config_information']['trusted_work_request|flags|not_approved'] = HOAM_formValidateFlagValue ('work_request|flags|not_approved', (int) $group_members_class -> superClean ($_POST['work_request|flags|not_approved'], 20), $_SESSION['error_class_item']);
			}

			// Status flags
			if (isset ($_POST['work_request|flags|new'])) {
				$_SESSION['config_information']['trusted_work_request|flags|new'] = HOAM_formValidateFlagValue ('work_request|flags|new', (int) $group_members_class -> superClean ($_POST['work_request|flags|new'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|assigned'])) {
				$_SESSION['config_information']['trusted_work_request|flags|assigned'] = HOAM_formValidateFlagValue ('work_request|flags|assigned', (int) $group_members_class -> superClean ($_POST['work_request|flags|assigned'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|planning'])) {
				$_SESSION['config_information']['trusted_work_request|flags|planning'] = HOAM_formValidateFlagValue ('work_request|flags|planning', (int) $group_members_class -> superClean ($_POST['work_request|flags|planning'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|in_progress'])) {
				$_SESSION['config_information']['trusted_work_request|flags|in_progress'] = HOAM_formValidateFlagValue ('work_request|flags|in_progress', (int) $group_members_class -> superClean ($_POST['work_request|flags|in_progress'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|on_hold'])) {
				$_SESSION['config_information']['trusted_work_request|flags|on_hold'] = HOAM_formValidateFlagValue ('work_request|flags|on_hold', (int) $group_members_class -> superClean ($_POST['work_request|flags|on_hold'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|cancelled'])) {
				$_SESSION['config_information']['trusted_work_request|flags|cancelled'] = HOAM_formValidateFlagValue ('work_request|flags|cancelled', (int) $group_members_class -> superClean ($_POST['work_request|flags|cancelled'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|rejected'])) {
				$_SESSION['config_information']['trusted_work_request|flags|rejected'] = HOAM_formValidateFlagValue ('work_request|flags|rejected', (int) $group_members_class -> superClean ($_POST['work_request|flags|rejected'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['work_request|flags|complete'])) {
				$_SESSION['config_information']['trusted_work_request|flags|complete'] = HOAM_formValidateFlagValue ('work_request|flags|complete', (int) $group_members_class -> superClean ($_POST['work_request|flags|complete'], 20), $_SESSION['error_class_item']);
			}
			if (isset ($_POST['config|work_request|require_approval'])) {
				$_SESSION['config_information']['trusted_work_request|require_approval'] = (int) $lot_class -> superClean ($_POST['config|work_request|require_approval'], 1);
			} elseif (isset ($_POST['config|work_request|require_approval_enable'])) {
				$_SESSION['config_information']['trusted_work_request|require_approval'] = 0;
			}

// print_r ($_POST); print_r ($_SESSION['config_information']); print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();
			
			// Are there any errors that the current user needs to fix before
			// committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				header ('location: /website/config/modify/');
				exit;
			} else {
				// Begin updating the database.
				// We're specifically checking that every value is set first
				// before updating it; we do this because values won't be set
				// if they weren't submitted. By default, values won't be
				// submitted unless the user specifically choses to modify it
				
				/***************************************************************
				 * Update Advertising settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_advertising|minimum_description_length'])) {
					$_HOAM_config -> setValue ('advertising/minimum_description_length', $_SESSION['config_information']['trusted_advertising|minimum_description_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_advertising|minimum_url_length'])) {
					$_HOAM_config -> setValue ('advertising/minimum_url_length', $_SESSION['config_information']['trusted_advertising|minimum_url_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_advertising|flags|position1'])) {
					$_HOAM_config -> setValue ('advertising/flags/position1', $_SESSION['config_information']['trusted_advertising|flags|position1']);
				}
				if (isset ($_SESSION['config_information']['trusted_advertising|flags|position2'])) {
					$_HOAM_config -> setValue ('advertising/flags/position2', $_SESSION['config_information']['trusted_advertising|flags|position2']);
				}
				if (isset ($_SESSION['config_information']['trusted_advertising|flags|position3'])) {
					$_HOAM_config -> setValue ('advertising/flags/position3', $_SESSION['config_information']['trusted_advertising|flags|position3']);
				}
				if (isset ($_SESSION['config_information']['trusted_advertising|flags|position4'])) {
					$_HOAM_config -> setValue ('advertising/flags/position4', $_SESSION['config_information']['trusted_advertising|flags|position4']);
				}

				/***************************************************************
				 * Update Attachment settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_attachment|maximum_file_size'])) {
					$_HOAM_config -> setValue ('attachment/maximum_file_size', $_SESSION['config_information']['trusted_attachment|maximum_file_size']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|minimum_description_length'])) {
					$_HOAM_config -> setValue ('attachment/minimum_description_length', $_SESSION['config_information']['trusted_attachment|minimum_description_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|advertising'])) {
					$_HOAM_config -> setValue ('attachment/flags/advertising', $_SESSION['config_information']['trusted_attachment|flags|advertising']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|budget'])) {
					$_HOAM_config -> setValue ('attachment/flags/budget', $_SESSION['config_information']['trusted_attachment|flags|budget']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|budget_vendor'])) {
					$_HOAM_config -> setValue ('attachment/flags/budget_vendor', $_SESSION['config_information']['trusted_attachment|flags|budget_vendor']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|homeowner'])) {
					$_HOAM_config -> setValue ('attachment/flags/homeowner', $_SESSION['config_information']['trusted_attachment|flags|homeowner']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|homeowner_sale'])) {
					$_HOAM_config -> setValue ('attachment/flags/homeowner_sale', $_SESSION['config_information']['trusted_attachment|flags|homeowner_sale']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|insurance'])) {
					$_HOAM_config -> setValue ('attachment/flags/insurance', $_SESSION['config_information']['trusted_attachment|flags|insurance']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|lot'])) {
					$_HOAM_config -> setValue ('attachment/flags/lot', $_SESSION['config_information']['trusted_attachment|flags|lot']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|property'])) {
					$_HOAM_config -> setValue ('attachment/flags/property', $_SESSION['config_information']['trusted_attachment|flags|property']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|violation'])) {
					$_HOAM_config -> setValue ('attachment/flags/violation', $_SESSION['config_information']['trusted_attachment|flags|violation']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|vote'])) {
					$_HOAM_config -> setValue ('attachment/flags/vote', $_SESSION['config_information']['trusted_attachment|flags|vote']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|flags|work_request'])) {
					$_HOAM_config -> setValue ('attachment/flags/work_request', $_SESSION['config_information']['trusted_attachment|flags|work_request']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|preview|enable'])) {
					$_HOAM_config -> setValue ('attachment/preview/enable', $_SESSION['config_information']['trusted_attachment|preview|enable']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|preview|height'])) {
					$_HOAM_config -> setValue ('attachment/preview/height', $_SESSION['config_information']['trusted_attachment|preview|height']);
				}
				if (isset ($_SESSION['config_information']['trusted_attachment|preview|width'])) {
					$_HOAM_config -> setValue ('attachment/preview/width', $_SESSION['config_information']['trusted_attachment|preview|width']);
				}
				
				/***************************************************************
				 * Update Budget settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|amount'])) {
					$_HOAM_config -> setValue ('budget/assessment/amount', $_SESSION['config_information']['trusted_budget|assessment|amount']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|date'])) {
					$_HOAM_config -> setValue ('budget/assessment/date', $_SESSION['config_information']['trusted_budget|assessment|date']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|days_due'])) {
					$_HOAM_config -> setValue ('budget/assessment/days_due', $_SESSION['config_information']['trusted_budget|assessment|days_due']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|days_late'])) {
					$_HOAM_config -> setValue ('budget/assessment/days_late', $_SESSION['config_information']['trusted_budget|assessment|days_late']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|frequency'])) {
					$_HOAM_config -> setValue ('budget/assessment/frequency', $_SESSION['config_information']['trusted_budget|assessment|frequency']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|months_due'])) {
					$_HOAM_config -> setValue ('budget/assessment/months_due', $_SESSION['config_information']['trusted_budget|assessment|months_due']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|months_late'])) {
					$_HOAM_config -> setValue ('budget/assessment/months_late', $_SESSION['config_information']['trusted_budget|assessment|months_late']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|time'])) {
					$_HOAM_config -> setValue ('budget/assessment/time', $_SESSION['config_information']['trusted_budget|assessment|time']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|assessment|unit'])) {
					$_HOAM_config -> setValue ('budget/assessment/unit', $_SESSION['config_information']['trusted_budget|assessment|unit']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|fee|collection'])) {
					$_HOAM_config -> setValue ('budget/fee/collection', $_SESSION['config_information']['trusted_budget|fee|collection']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|fee|late'])) {
					$_HOAM_config -> setValue ('budget/fee/late', $_SESSION['config_information']['trusted_budget|fee|late']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|fee|returned'])) {
					$_HOAM_config -> setValue ('budget/fee/returned', $_SESSION['config_information']['trusted_budget|fee|returned']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|interest_rate'])) {
					$_HOAM_config -> setValue ('budget/interest_rate', $_SESSION['config_information']['trusted_budget|interest_rate']);
				}
				if (isset ($_SESSION['config_information']['trusted_config|budget|payment_plan|interest'])) {
					$_HOAM_config -> setValue ('budget/payment_plan/interest', $_SESSION['config_information']['trusted_config|budget|payment_plan|interest']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|annual'])) {
					$_HOAM_config -> setValue ('budget/flags/annual', $_SESSION['config_information']['trusted_budget|flags|annual']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|assessment'])) {
					$_HOAM_config -> setValue ('budget/flags/assessment', $_SESSION['config_information']['trusted_budget|flags|assessment']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|assessment_other'])) {
					$_HOAM_config -> setValue ('budget/flags/assessment_other', $_SESSION['config_information']['trusted_budget|flags|assessment_other']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|assessment_special'])) {
					$_HOAM_config -> setValue ('budget/flags/assessment_special', $_SESSION['config_information']['trusted_budget|flags|assessment_special']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|credit'])) {
					$_HOAM_config -> setValue ('budget/flags/credit', $_SESSION['config_information']['trusted_budget|flags|credit']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|fee_administration'])) {
					$_HOAM_config -> setValue ('budget/flags/fee_administration', $_SESSION['config_information']['trusted_budget|flags|fee_administration']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|fee_fine'])) {
					$_HOAM_config -> setValue ('budget/flags/fee_fine', $_SESSION['config_information']['trusted_budget|flags|fee_fine']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|fee_interest'])) {
					$_HOAM_config -> setValue ('budget/flags/fee_interest', $_SESSION['config_information']['trusted_budget|flags|fee_interest']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|fee_late'])) {
					$_HOAM_config -> setValue ('budget/flags/fee_late', $_SESSION['config_information']['trusted_budget|flags|fee_late']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|fee_legal'])) {
					$_HOAM_config -> setValue ('budget/flags/fee_legal', $_SESSION['config_information']['trusted_budget|flags|fee_legal']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|ignore_average'])) {
					$_HOAM_config -> setValue ('budget/flags/ignore_average', $_SESSION['config_information']['trusted_budget|flags|ignore_average']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|payment'])) {
					$_HOAM_config -> setValue ('budget/flags/payment', $_SESSION['config_information']['trusted_budget|flags|payment']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|public'])) {
					$_HOAM_config -> setValue ('budget/flags/public', $_SESSION['config_information']['trusted_budget|flags|public']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|sales_tax'])) {
					$_HOAM_config -> setValue ('budget/flags/sales_tax', $_SESSION['config_information']['trusted_budget|flags|sales_tax']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|appy_current_homeowner'])) {
					$_HOAM_config -> setValue ('budget/flags/apply_current_homeowner', $_SESSION['config_information']['trusted_budget|flags|apply_current_homeowner']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|apply_fee_late'])) {
					$_HOAM_config -> setValue ('budget/flags/apply_fee_late', $_SESSION['config_information']['trusted_budget|flags|apply_fee_late']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|apply_interest'])) {
					$_HOAM_config -> setValue ('budget/flags/apply_interest', $_SESSION['config_information']['trusted_budget|flags|apply_interest']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|apply_sales_tax'])) {
					$_HOAM_config -> setValue ('budget/flags/apply_sales_tax', $_SESSION['config_information']['trusted_budget|flags|apply_sales_tax']);
				}
/*				if (isset ($_SESSION['config_information']['trusted_budget|flags|account_closed'])) {
					$_HOAM_config -> setValue ('budget/flags/account_closed', $_SESSION['config_information']['trusted_budget|flags|account_closed']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|account_frozen'])) {
					$_HOAM_config -> setValue ('budget/flags/account_frozen', $_SESSION['config_information']['trusted_budget|flags|account_frozen']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|account_expense'])) {
					$_HOAM_config -> setValue ('budget/flags/account_expense', $_SESSION['config_information']['trusted_budget|flags|account_expense']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|account_homeowner'])) {
					$_HOAM_config -> setValue ('budget/flags/account_homeowner', $_SESSION['config_information']['trusted_budget|flags|account_homeowner']);
				}
*/				if (isset ($_SESSION['config_information']['trusted_budget|flags|due_15'])) {
					$_HOAM_config -> setValue ('budget/flags/due_15', $_SESSION['config_information']['trusted_budget|flags|due_15']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|due_20'])) {
					$_HOAM_config -> setValue ('budget/flags/due_20', $_SESSION['config_information']['trusted_budget|flags|due_20']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|due_30'])) {
					$_HOAM_config -> setValue ('budget/flags/due_30', $_SESSION['config_information']['trusted_budget|flags|due_30']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|due_45'])) {
					$_HOAM_config -> setValue ('budget/flags/due_45', $_SESSION['config_information']['trusted_budget|flags|due_45']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|due_receipt'])) {
					$_HOAM_config -> setValue ('budget/flags/due_receipt', $_SESSION['config_information']['trusted_budget|flags|due_receipt']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|category_income'])) {
					$_HOAM_config -> setValue ('budget/flags/category_income', $_SESSION['config_information']['trusted_budget|flags|category_income']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|late_notice'])) {
					$_HOAM_config -> setValue ('budget/flags/late_notice', $_SESSION['config_information']['trusted_budget|flags|late_notice']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|late_delinquent'])) {
					$_HOAM_config -> setValue ('budget/flags/late_delinquent', $_SESSION['config_information']['trusted_budget|flags|late_delinquent']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|late_default'])) {
					$_HOAM_config -> setValue ('budget/flags/late_default', $_SESSION['config_information']['trusted_budget|flags|late_default']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|late_attorney'])) {
					$_HOAM_config -> setValue ('budget/flags/late_attorney', $_SESSION['config_information']['trusted_budget|flags|late_attorney']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|payment_plan'])) {
					$_HOAM_config -> setValue ('budget/flags/payment_plan', $_SESSION['config_information']['trusted_budget|flags|payment_plan']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|flags|payment_plan_default'])) {
					$_HOAM_config -> setValue ('budget/flags/payment_plan_default', $_SESSION['config_information']['trusted_budget|flags|payment_plan_default']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|invoice|fineprint'])) {
					$_HOAM_config -> setValue ('budget/invoice/fineprint', $_SESSION['config_information']['trusted_budget|invoice|fineprint']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|sales_tax_rate'])) {
					$_HOAM_config -> setValue ('budget/sales_tax_rate', $_SESSION['config_information']['trusted_budget|sales_tax_rate']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|category|administrative'])) {
					$_HOAM_config -> setValue ('budget/category/administrative', $_SESSION['config_information']['trusted_budget|category|administrative']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|category|credit'])) {
					$_HOAM_config -> setValue ('budget/category/credit', $_SESSION['config_information']['trusted_budget|category|credit']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|category|dues'])) {
					$_HOAM_config -> setValue ('budget/category/dues', $_SESSION['config_information']['trusted_budget|category|dues']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|category|interest'])) {
					$_HOAM_config -> setValue ('budget/category/interest', $_SESSION['config_information']['trusted_budget|category|interest']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|category|late'])) {
					$_HOAM_config -> setValue ('budget/category/late', $_SESSION['config_information']['trusted_budget|category|late']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|category|property'])) {
					$_HOAM_config -> setValue ('budget/category/property', $_SESSION['config_information']['trusted_budget|category|property']);
				}

				if (isset ($_SESSION['config_information']['trusted_budget|vendor|management'])) {
					$_HOAM_config -> setValue ('budget/ids/management', $_SESSION['config_information']['trusted_budget|vendor|management']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|vendor|organization'])) {
					$_HOAM_config -> setValue ('budget/ids/organization', $_SESSION['config_information']['trusted_budget|vendor|organization']);
				}

				if (isset ($_SESSION['config_information']['trusted_budget|account|income'])) {
					$_HOAM_config -> setValue ('budget/account/income', $_SESSION['config_information']['trusted_budget|account|income']);
				}
				if (isset ($_SESSION['config_information']['trusted_budget|account|expense'])) {
					$_HOAM_config -> setValue ('budget/account/expense', $_SESSION['config_information']['trusted_budget|account|expense']);
				}

				if (isset ($_SESSION['config_information']['trusted_config|budget|insurance|days'])) {
					$_HOAM_config -> setValue ('budget/insurance/days', $_SESSION['config_information']['trusted_config|budget|insurance|days']);
				}
				if (isset ($_SESSION['config_information']['trusted_config|budget|insurance|enable'])) {
					$_HOAM_config -> setValue ('budget/insurance/enable', $_SESSION['config_information']['trusted_config|budget|insurance|enable']);
				}
				if (isset ($_SESSION['config_information']['trusted_config|budget|insurance|minimum_policy_length'])) {
					$_HOAM_config -> setValue ('budget/insurance/minimum_policy_length', $_SESSION['config_information']['trusted_config|budget|insurance|minimum_policy_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_config|budget|insurance|reminders'])) {
					$_HOAM_config -> setValue ('budget/insurance/reminders', $_SESSION['config_information']['trusted_config|budget|insurance|reminders']);
				}
				if (isset ($_SESSION['config_information']['trusted_config|budget|insurance|flags|replaced'])) {
					$_HOAM_config -> setValue ('budget/insurance/flags/replaced', $_SESSION['config_information']['trusted_config|budget|insurance|flags|replaced']);
				}

				if (isset ($_SESSION['config_information']['trusted_config|budget|paypal|enable'])) {
					$_HOAM_config -> setValue ('budget/paypal/enable', $_SESSION['config_information']['trusted_config|budget|paypal|enable']);
				}
				if (isset ($_SESSION['config_information']['trusted_config|budget|paypal|email'])) {
					$_HOAM_config -> setValue ('budget/paypal/email', $_SESSION['config_information']['trusted_config|budget|paypal|email']);
				}
				if (isset ($_SESSION['config_information']['trusted_config|budget|paypal|surcharge_amount'])) {
					$_HOAM_config -> setValue ('budget/paypal/surcharge/amount', $_SESSION['config_information']['trusted_config|budget|paypal|surcharge_amount']);
				}
				if (isset ($_SESSION['config_information']['trusted_config|budget|paypal|surcharge_percent'])) {
					$_HOAM_config -> setValue ('budget/paypal/surcharge/percent', $_SESSION['config_information']['trusted_config|budget|paypal|surcharge_percent']);
				}

				if (isset ($_SESSION['config_information']['trusted_config|budget|zero_amount|enable'])) {
					$_HOAM_config -> setValue ('budget/zero_amount/enable', $_SESSION['config_information']['trusted_config|budget|zero_amount|enable']);
				}

				if (isset ($_SESSION['config_information']['trusted_config|budget|vendor|percent_different'])) {
					$_HOAM_config -> setValue ('budget/vendor/percent_different', $_SESSION['config_information']['trusted_config|budget|vendor|percent_different']);
				}

				/***************************************************************
				 * Update Email settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_email|auth|enable'])) {
					$_HOAM_config -> setValue ('email/auth', $_SESSION['config_information']['trusted_email|auth|enable']);
				}
				if (isset ($_SESSION['config_information']['trusted_email|automated'])) {
					$_HOAM_config -> setValue ('email/automated', $_SESSION['config_information']['trusted_email|automated']);
				}
				if (isset ($_SESSION['config_information']['trusted_email|do_not_reply'])) {
					$_HOAM_config -> setValue ('email/do_not_reply', $_SESSION['config_information']['trusted_email|do_not_reply']);
				}
				if (isset ($_SESSION['config_information']['trusted_email|password'])) {
					$_HOAM_config -> setValue ('email/password', $_SESSION['config_information']['trusted_email|password']);
				}
				if (isset ($_SESSION['config_information']['trusted_email|port'])) {
					$_HOAM_config -> setValue ('email/port', $_SESSION['config_information']['trusted_email|port']);
				}
				if (isset ($_SESSION['config_information']['trusted_email|server'])) {
					$_HOAM_config -> setValue ('email/server', $_SESSION['config_information']['trusted_email|server']);
				}
				if (isset ($_SESSION['config_information']['trusted_email|username'])) {
					$_HOAM_config -> setValue ('email/username', $_SESSION['config_information']['trusted_email|username']);
				}

				/***************************************************************
				 * Update file settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_files|banned_username'])) {
					$_HOAM_config -> setValue ('files/banned_username', $_SESSION['config_information']['trusted_files|banned_username']);
				}
				if (isset ($_SESSION['config_information']['trusted_files|censored_words'])) {
					$_HOAM_config -> setValue ('files/censored_words', $_SESSION['config_information']['trusted_files|censored_words']);
				}
								
				/***************************************************************
				 * Update Group settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_group|minimum_description_length'])) {
					$_HOAM_config -> setValue ('group/minimum_description_length', $_SESSION['config_information']['trusted_group|minimum_description_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_group|ids|admin'])) {
					$_HOAM_config -> setValue ('group/ids/admin', $_SESSION['config_information']['trusted_group|ids|admin']);
				}
				if (isset ($_SESSION['config_information']['trusted_group|ids|anonymous'])) {
					$_HOAM_config -> setValue ('group/ids/anonymous', $_SESSION['config_information']['trusted_group|ids|anonymous']);
				}
				if (isset ($_SESSION['config_information']['trusted_group|ids|board'])) {
					$_HOAM_config -> setValue ('group/ids/board', $_SESSION['config_information']['trusted_group|ids|board']);
				}
                if (isset ($_SESSION['config_information']['trusted_group|ids|advertising_add'])) {
                    $_HOAM_config -> setValue ('group/ids/advertising_add', $_SESSION['config_information']['trusted_group|ids|advertising_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|advertising_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/advertising_delete', $_SESSION['config_information']['trusted_group|ids|advertising_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|advertising_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/advertising_edit', $_SESSION['config_information']['trusted_group|ids|advertising_edit']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|article_add'])) {
                    $_HOAM_config -> setValue ('group/ids/article_add', $_SESSION['config_information']['trusted_group|ids|article_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|article_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/article_delete', $_SESSION['config_information']['trusted_group|ids|article_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|article_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/article_edit', $_SESSION['config_information']['trusted_group|ids|article_edit']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|attachment_add'])) {
                    $_HOAM_config -> setValue ('group/ids/attachment_add', $_SESSION['config_information']['trusted_group|ids|attachment_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|attachment_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/attachment_delete', $_SESSION['config_information']['trusted_group|ids|attachment_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|attachment_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/attachment_edit', $_SESSION['config_information']['trusted_group|ids|attachment_edit']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|attachment_view'])) {
                    $_HOAM_config -> setValue ('group/ids/attachment_view', $_SESSION['config_information']['trusted_group|ids|attachment_view']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|budget_add'])) {
                    $_HOAM_config -> setValue ('group/ids/budget_add', $_SESSION['config_information']['trusted_group|ids|budget_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|budget_approve'])) {
                    $_HOAM_config -> setValue ('group/ids/budget_approve', $_SESSION['config_information']['trusted_group|ids|budget_approve']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|budget_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/budget_delete', $_SESSION['config_information']['trusted_group|ids|budget_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|budget_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/budget_edit', $_SESSION['config_information']['trusted_group|ids|budget_edit']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|budget_view'])) {
                    $_HOAM_config -> setValue ('group/ids/budget_view', $_SESSION['config_information']['trusted_group|ids|budget_view']);
                }
				if (isset ($_SESSION['config_information']['trusted_group|ids|everyone'])) {
					$_HOAM_config -> setValue ('group/ids/everyone', $_SESSION['config_information']['trusted_group|ids|everyone']);
				}
                if (isset ($_SESSION['config_information']['trusted_group|ids|group_add'])) {
                    $_HOAM_config -> setValue ('group/ids/group_add', $_SESSION['config_information']['trusted_group|ids|group_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|group_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/group_delete', $_SESSION['config_information']['trusted_group|ids|group_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|group_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/group_edit', $_SESSION['config_information']['trusted_group|ids|group_edit']);
                }
				if (isset ($_SESSION['config_information']['trusted_group|ids|homeowner'])) {
					$_HOAM_config -> setValue ('group/ids/homeowner', $_SESSION['config_information']['trusted_group|ids|homeowner']);
				}
                if (isset ($_SESSION['config_information']['trusted_group|ids|homeowner_add'])) {
                    $_HOAM_config -> setValue ('group/ids/homeowner_add', $_SESSION['config_information']['trusted_group|ids|homeowner_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|homeowner_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/homeowner_delete', $_SESSION['config_information']['trusted_group|ids|homeowner_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|homeowner_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/homeowner_edit', $_SESSION['config_information']['trusted_group|ids|homeowner_edit']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|insurance_add'])) {
                    $_HOAM_config -> setValue ('group/ids/insurance_add', $_SESSION['config_information']['trusted_group|ids|insurance_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|insurance_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/insurance_delete', $_SESSION['config_information']['trusted_group|ids|insurance_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|insurance_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/insurance_edit', $_SESSION['config_information']['trusted_group|ids|insurance_edit']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|insurance_view'])) {
                    $_HOAM_config -> setValue ('group/ids/insurance_view', $_SESSION['config_information']['trusted_group|ids|insurance_view']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|lot_add'])) {
                    $_HOAM_config -> setValue ('group/ids/lot_add', $_SESSION['config_information']['trusted_group|ids|lot_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|lot_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/lot_delete', $_SESSION['config_information']['trusted_group|ids|lot_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|lot_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/lot_edit', $_SESSION['config_information']['trusted_group|ids|lot_edit']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|messageboard_add'])) {
                    $_HOAM_config -> setValue ('group/ids/messageboard_add', $_SESSION['config_information']['trusted_group|ids|messageboard_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|messageboard_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/messageboard_delete', $_SESSION['config_information']['trusted_group|ids|messageboard_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|messageboard_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/messageboard_edit', $_SESSION['config_information']['trusted_group|ids|messageboard_edit']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|messageboard_post'])) {
                    $_HOAM_config -> setValue ('group/ids/messageboard_post', $_SESSION['config_information']['trusted_group|ids|messageboard_post']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|messageboard_view'])) {
                    $_HOAM_config -> setValue ('group/ids/messageboard_view', $_SESSION['config_information']['trusted_group|ids|messageboard_view']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|news_add'])) {
                    $_HOAM_config -> setValue ('group/ids/news_add', $_SESSION['config_information']['trusted_group|ids|news_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|news_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/news_delete', $_SESSION['config_information']['trusted_group|ids|news_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|news_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/news_edit', $_SESSION['config_information']['trusted_group|ids|news_edit']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|user_add'])) {
                    $_HOAM_config -> setValue ('group/ids/user_add', $_SESSION['config_information']['trusted_group|ids|user_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|user_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/user_delete', $_SESSION['config_information']['trusted_group|ids|user_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|user_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/user_edit', $_SESSION['config_information']['trusted_group|ids|user_edit']);
                }
				if (isset ($_SESSION['config_information']['trusted_group|ids|officer'])) {
					$_HOAM_config -> setValue ('group/ids/officer', $_SESSION['config_information']['trusted_group|ids|officer']);
				}
				if (isset ($_SESSION['config_information']['trusted_group|ids|registered'])) {
					$_HOAM_config -> setValue ('group/ids/registered', $_SESSION['config_information']['trusted_group|ids|registered']);
				}
				if (isset ($_SESSION['config_information']['trusted_group|ids|resident'])) {
					$_HOAM_config -> setValue ('group/ids/resident', $_SESSION['config_information']['trusted_group|ids|resident']);
				}
                if (isset ($_SESSION['config_information']['trusted_group|ids|violation_add'])) {
                    $_HOAM_config -> setValue ('group/ids/violation_add', $_SESSION['config_information']['trusted_group|ids|violation_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|violation_approve'])) {
                    $_HOAM_config -> setValue ('group/ids/violation_approve', $_SESSION['config_information']['trusted_group|ids|violation_approve']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|violation_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/violation_delete', $_SESSION['config_information']['trusted_group|ids|violation_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|violation_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/violation_edit', $_SESSION['config_information']['trusted_group|ids|violation_edit']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|work_request_add'])) {
                    $_HOAM_config -> setValue ('group/ids/work_request_add', $_SESSION['config_information']['trusted_group|ids|work_request_add']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|work_request_delete'])) {
                    $_HOAM_config -> setValue ('group/ids/work_request_delete', $_SESSION['config_information']['trusted_group|ids|work_request_delete']);
                }
                if (isset ($_SESSION['config_information']['trusted_group|ids|work_request_edit'])) {
                    $_HOAM_config -> setValue ('group/ids/work_request_edit', $_SESSION['config_information']['trusted_group|ids|work_request_edit']);
                }
				
				/***************************************************************
				 * Update Homeowner settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_homeowner|default|city'])) {
					$_HOAM_config -> setValue ('homeowner/default/city', $_SESSION['config_information']['trusted_homeowner|default|city']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|default|postalcode'])) {
					$_HOAM_config -> setValue ('homeowner/default/postalcode', $_SESSION['config_information']['trusted_homeowner|default|postalcode']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|default|state'])) {
					$_HOAM_config -> setValue ('homeowner/default/state', $_SESSION['config_information']['trusted_homeowner|default|state']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|send_current_resident'])) {
					$_HOAM_config -> setValue ('homeowner/send_current_resident', $_SESSION['config_information']['trusted_homeowner|send_current_resident']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|minimum_comments_length'])) {
					$_HOAM_config -> setValue ('homeowner/minimum_comments_length', $_SESSION['config_information']['trusted_homeowner|minimum_comments_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|flags|bankrupt'])) {
					$_HOAM_config -> setValue ('homeowner/flags/bankrupt', $_SESSION['config_information']['trusted_homeowner|flags|bankrupt']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|flags|ignore_budget'])) {
					$_HOAM_config -> setValue ('homeowner/flags/ignore_budget', $_SESSION['config_information']['trusted_homeowner|flags|ignore_budget']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|flags|ignore_violations'])) {
					$_HOAM_config -> setValue ('homeowner/flags/ignore_violations', $_SESSION['config_information']['trusted_homeowner|flags|ignore_violations']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|flags|ignore_violations_temporary'])) {
					$_HOAM_config -> setValue ('homeowner/flags/ignore_violations_temporary', $_SESSION['config_information']['trusted_homeowner|flags|ignore_violations_temporary']);
				}
				if (isset ($_SESSION['config_information']['trusted_config|homeowner|flags|no_fees'])) {
					$_HOAM_config -> setValue ('homeowner/flags/no_fees', $_SESSION['config_information']['trusted_config|homeowner|flags|no_fees']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|flags|residence_mailing'])) {
					$_HOAM_config -> setValue ('homeowner/flags/residence_mailing', $_SESSION['config_information']['trusted_homeowner|flags|residence_mailing']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|flags|resident'])) {
					$_HOAM_config -> setValue ('homeowner/flags/resident', $_SESSION['config_information']['trusted_homeowner|flags|resident']);
				}
				if (isset ($_SESSION['config_information']['trusted_homeowner|flags|service_member'])) {
					$_HOAM_config -> setValue ('homeowner/flags/service_member', $_SESSION['config_information']['trusted_homeowner|flags|service_member']);
				}

				/***************************************************************
				 * Update Letter / Envelope settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_letter|envelope'])) {
					$_HOAM_config -> setValue ('letter/envelope', $_SESSION['config_information']['trusted_letter|envelope']);
				}
				if (isset ($_SESSION['config_information']['trusted_letter|paper'])) {
					$_HOAM_config -> setValue ('letter/paper', $_SESSION['config_information']['trusted_letter|paper']);
				}

				/***************************************************************
				 * Update Log settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_config|log|browser'])) {
					$_HOAM_config -> setValue ('log/browser', $_SESSION['config_information']['trusted_config|log|browser']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|destinations'])) {
					$_HOAM_config -> setValue ('log/destinations', $_SESSION['config_information']['trusted_log|destinations']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|levels'])) {
					$_HOAM_config -> setValue ('log/levels', $_SESSION['config_information']['trusted_log|levels']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|advertisement'])) {
					$_HOAM_config -> setValue ('log/flags/advertisement', $_SESSION['config_information']['trusted_log|flags|advertisement']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|article'])) {
					$_HOAM_config -> setValue ('log/flags/article', $_SESSION['config_information']['trusted_log|flags|article']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|attachment'])) {
					$_HOAM_config -> setValue ('log/flags/attachment', $_SESSION['config_information']['trusted_log|flags|attachment']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|budget_category'])) {
					$_HOAM_config -> setValue ('log/flags/budget_category', $_SESSION['config_information']['trusted_log|flags|budget_category']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|budget_invoice'])) {
					$_HOAM_config -> setValue ('log/flags/budget_invoice', $_SESSION['config_information']['trusted_log|flags|budget_invoice']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|budget_vendor'])) {
					$_HOAM_config -> setValue ('log/flags/budget_vendor', $_SESSION['config_information']['trusted_log|flags|budget_vendor']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|group'])) {
					$_HOAM_config -> setValue ('log/flags/group', $_SESSION['config_information']['trusted_log|flags|group']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|homeowner'])) {
					$_HOAM_config -> setValue ('log/flags/homeowner', $_SESSION['config_information']['trusted_log|flags|homeowner']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|insurance'])) {
					$_HOAM_config -> setValue ('log/flags/insurance', $_SESSION['config_information']['trusted_log|flags|insurance']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|lot'])) {
					$_HOAM_config -> setValue ('log/flags/lot', $_SESSION['config_information']['trusted_log|flags|lot']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|messageboard'])) {
					$_HOAM_config -> setValue ('log/flags/messageboard', $_SESSION['config_information']['trusted_log|flags|messageboard']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|news'])) {
					$_HOAM_config -> setValue ('log/flags/news', $_SESSION['config_information']['trusted_log|flags|news']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|property'])) {
					$_HOAM_config -> setValue ('log/flags/property', $_SESSION['config_information']['trusted_log|flags|property']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|user'])) {
					$_HOAM_config -> setValue ('log/flags/user', $_SESSION['config_information']['trusted_log|flags|user']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|upgrade'])) {
					$_HOAM_config -> setValue ('log/flags/upgrade', $_SESSION['config_information']['trusted_log|flags|upgrade']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|violation'])) {
					$_HOAM_config -> setValue ('log/flags/violation', $_SESSION['config_information']['trusted_log|flags|violation']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|violation_category'])) {
					$_HOAM_config -> setValue ('log/flags/violation_category', $_SESSION['config_information']['trusted_log|flags|violation_category']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|violation_severity'])) {
					$_HOAM_config -> setValue ('log/flags/violation_severity', $_SESSION['config_information']['trusted_log|flags|violation_severity']);
				}
				if (isset ($_SESSION['config_information']['trusted_log|flags|work_request'])) {
					$_HOAM_config -> setValue ('log/flags/work_request', $_SESSION['config_information']['trusted_log|flags|work_request']);
				}
				
				/***************************************************************
				 * Update Lot settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_lot|common|area'])) {
					$_HOAM_config -> setValue ('lot/common/area', $_SESSION['config_information']['trusted_lot|common|area']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|common|clubhouse'])) {
					$_HOAM_config -> setValue ('lot/common/clubhouse', $_SESSION['config_information']['trusted_lot|common|clubhouse']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|common|pool'])) {
					$_HOAM_config -> setValue ('lot/common/pool', $_SESSION['config_information']['trusted_lot|common|pool']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|minimum_address_length'])) {
					$_HOAM_config -> setValue ('lot/minimum_address_length', $_SESSION['config_information']['trusted_lot|minimum_address_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|minimum_building_length'])) {
					$_HOAM_config -> setValue ('lot/minimum_building_length', $_SESSION['config_information']['trusted_lot|minimum_building_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|minimum_street_length'])) {
					$_HOAM_config -> setValue ('lot/minimum_street_length', $_SESSION['config_information']['trusted_lot|minimum_street_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|minimum_suite_length'])) {
					$_HOAM_config -> setValue ('lot/minimum_suite_length', $_SESSION['config_information']['trusted_lot|minimum_suite_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|minimum_sqft_size'])) {
					$_HOAM_config -> setValue ('lot/minimum_sqft_size', $_SESSION['config_information']['trusted_lot|minimum_sqft_size']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|minimum_block_length'])) {
					$_HOAM_config -> setValue ('lot/minimum_block_length', $_SESSION['config_information']['trusted_lot|minimum_block_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|minimum_lot_length'])) {
					$_HOAM_config -> setValue ('lot/minimum_lot_length', $_SESSION['config_information']['trusted_lot|minimum_lot_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|minimum_latitude_length'])) {
					$_HOAM_config -> setValue ('lot/minimum_latitude_length', $_SESSION['config_information']['trusted_lot|minimum_latitude_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|minimum_longitude_length'])) {
					$_HOAM_config -> setValue ('lot/minimum_longitude_length', $_SESSION['config_information']['trusted_lot|minimum_longitude_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_lot|minimum_plat_length'])) {
					$_HOAM_config -> setValue ('lot/minimum_plat_length', $_SESSION['config_information']['trusted_lot|minimum_plat_length']);
				}

				/***************************************************************
				 * Update Messageboard settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_messageboard|flags|display_top'])) {
					$_HOAM_config -> setValue ('messageboard/flags/display_top', $_SESSION['config_information']['trusted_messageboard|flags|display_top']);
				}
				if (isset ($_SESSION['config_information']['trusted_messageboard|flags|do_not_delete'])) {
					$_HOAM_config -> setValue ('messageboard/flags/do_not_delete', $_SESSION['config_information']['trusted_messageboard|flags|do_not_delete']);
				}
				if (isset ($_SESSION['config_information']['trusted_messageboard|flags|no_more_comments'])) {
					$_HOAM_config -> setValue ('messageboard/flags/no_more_comments', $_SESSION['config_information']['trusted_messageboard|flags|no_more_comments']);
				}

				/***************************************************************
				 * Update Property settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_lot|property'])) {
					$_HOAM_config -> setValue ('lot/property', $_SESSION['config_information']['trusted_lot|property']);
				}
				if (isset ($_SESSION['config_information']['trusted_property|flags|renew'])) {
					$_HOAM_config -> setValue ('property/flags/renew', $_SESSION['config_information']['trusted_property|flags|renew']);
				}
				if (isset ($_SESSION['config_information']['trusted_property|flags|rented'])) {
					$_HOAM_config -> setValue ('property/flags/rented', $_SESSION['config_information']['trusted_property|flags|rented']);
				}
				if (isset ($_SESSION['config_information']['trusted_property|flags|annual'])) {
					$_HOAM_config -> setValue ('property/flags/annual', $_SESSION['config_information']['trusted_property|flags|annual']);
				}
				if (isset ($_SESSION['config_information']['trusted_property|flags|monthly'])) {
					$_HOAM_config -> setValue ('property/flags/monthly', $_SESSION['config_information']['trusted_property|flags|monthly']);
				}
				if (isset ($_SESSION['config_information']['trusted_property|flags|weekly'])) {
					$_HOAM_config -> setValue ('property/flags/weekly', $_SESSION['config_information']['trusted_property|flags|weekly']);
				}
				if (isset ($_SESSION['config_information']['trusted_property|flags|daily'])) {
					$_HOAM_config -> setValue ('property/flags/daily', $_SESSION['config_information']['trusted_property|flags|daily']);
				}
			
				/***************************************************************
				 * Update Organization settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_organization|letter|head'])) {
					$_HOAM_config -> setValue ('organization/letter/head', $_SESSION['config_information']['trusted_organization|letter|head']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|letter|salutation'])) {
					$_HOAM_config -> setValue ('organization/letter/salutation', $_SESSION['config_information']['trusted_organization|letter|salutation']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|letter|signature'])) {
					$_HOAM_config -> setValue ('organization/letter/signature', $_SESSION['config_information']['trusted_organization|letter|signature']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|letter|footer'])) {
					$_HOAM_config -> setValue ('organization/letter/footer', $_SESSION['config_information']['trusted_organization|letter|footer']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|mailing_address|line1'])) {
					$_HOAM_config -> setValue ('organization/mailing_address/line1', $_SESSION['config_information']['trusted_organization|mailing_address|line1']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|mailing_address|line2'])) {
					$_HOAM_config -> setValue ('organization/mailing_address/line2', $_SESSION['config_information']['trusted_organization|mailing_address|line2']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|mailing_address|line3'])) {
					$_HOAM_config -> setValue ('organization/mailing_address/line3', $_SESSION['config_information']['trusted_organization|mailing_address|line3']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|mailing_address|city'])) {
					$_HOAM_config -> setValue ('organization/mailing_address/city', $_SESSION['config_information']['trusted_organization|mailing_address|city']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|mailing_address|postalcode'])) {
					$_HOAM_config -> setValue ('organization/mailing_address/postalcode', $_SESSION['config_information']['trusted_organization|mailing_address|postalcode']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|line1'])) {
					$_HOAM_config -> setValue ('organization/physical_address/line1', $_SESSION['config_information']['trusted_organization|physical_address|line1']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|line2'])) {
					$_HOAM_config -> setValue ('organization/physical_address/line2', $_SESSION['config_information']['trusted_organization|physical_address|line2']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|line3'])) {
					$_HOAM_config -> setValue ('organization/physical_address/line3', $_SESSION['config_information']['trusted_organization|physical_address|line3']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|city'])) {
					$_HOAM_config -> setValue ('organization/physical_address/city', $_SESSION['config_information']['trusted_organization|physical_address|city']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|county'])) {
					$_HOAM_config -> setValue ('organization/physical_address/county', $_SESSION['config_information']['trusted_organization|physical_address|county']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|postalcode'])) {
					$_HOAM_config -> setValue ('organization/physical_address/postalcode', $_SESSION['config_information']['trusted_organization|physical_address|postalcode']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|name'])) {
					$_HOAM_config -> setValue ('organization/name', $_SESSION['config_information']['trusted_organization|name']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|property_name'])) {
					$_HOAM_config -> setValue ('organization/property_name', $_SESSION['config_information']['trusted_organization|property_name']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|phone|telephone'])) {
					$_HOAM_config -> setValue ('organization/telephone', $_SESSION['config_information']['trusted_organization|phone|telephone']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|phone|fax'])) {
					$_HOAM_config -> setValue ('organization/fax', $_SESSION['config_information']['trusted_organization|phone|fax']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|line1'])) {
					$_HOAM_config -> setValue ('organization/physical_address/line1', $_SESSION['config_information']['trusted_organization|physical_address|line1']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|line2'])) {
					$_HOAM_config -> setValue ('organization/physical_address/line2', $_SESSION['config_information']['trusted_organization|physical_address|line2']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|line3'])) {
					$_HOAM_config -> setValue ('organization/physical_address/line3', $_SESSION['config_information']['trusted_organization|physical_address|line3']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|city'])) {
					$_HOAM_config -> setValue ('organization/physical_address/city', $_SESSION['config_information']['trusted_organization|physical_address|city']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|physical_address|postalcode'])) {
					$_HOAM_config -> setValue ('organization/physical_address/postalcode', $_SESSION['config_information']['trusted_organization|physical_address|postalcode']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|email|board'])) {
					$_HOAM_config -> setValue ('organization/email/board', $_SESSION['config_information']['trusted_organization|email|board']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|email|officers'])) {
					$_HOAM_config -> setValue ('organization/email/officers', $_SESSION['config_information']['trusted_organization|email|officers']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|email|management'])) {
					$_HOAM_config -> setValue ('organization/email/management', $_SESSION['config_information']['trusted_organization|email|management']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|fee|expedite'])) {
					$_HOAM_config -> setValue ('organization/fee/expedite', $_SESSION['config_information']['trusted_organization|fee|expedite']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|fee|refinance'])) {
					$_HOAM_config -> setValue ('organization/fee/refinance', $_SESSION['config_information']['trusted_organization|fee|refinance']);
				}
				if (isset ($_SESSION['config_information']['trusted_organization|fee|resale'])) {
					$_HOAM_config -> setValue ('organization/fee/resale', $_SESSION['config_information']['trusted_organization|fee|resale']);
				}
				
				/***************************************************************
				 * Update User settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_user|ids|root'])) {
					$_HOAM_config -> setValue ('user/ids/root', $_SESSION['config_information']['trusted_user|ids|root']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|ids|system'])) {
					$_HOAM_config -> setValue ('user/ids/system', $_SESSION['config_information']['trusted_user|ids|system']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|days_before_delete'])) {
					$_HOAM_config -> setValue ('user/days_before_delete', $_SESSION['config_information']['trusted_user|days_before_delete']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|days_before_disable'])) {
					$_HOAM_config -> setValue ('user/days_before_disable', $_SESSION['config_information']['trusted_user|days_before_disable']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|days_password_age'])) {
					$_HOAM_config -> setValue ('user/days_password_age', $_SESSION['config_information']['trusted_user|days_password_age']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|days_password_warn'])) {
					$_HOAM_config -> setValue ('user/days_password_warn', $_SESSION['config_information']['trusted_user|days_password_warn']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|email_validation'])) {
					$_HOAM_config -> setValue ('user/email_validation', $_SESSION['config_information']['trusted_user|email_validation']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|flags|acronyms'])) {
					$_HOAM_config -> setValue ('user/flags/acronyms', $_SESSION['config_information']['trusted_user|flags|acronyms']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|flags|censor'])) {
					$_HOAM_config -> setValue ('user/flags/censor', $_SESSION['config_information']['trusted_user|flags|censor']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|flags|convert_links'])) {
					$_HOAM_config -> setValue ('user/flags/convert_links', $_SESSION['config_information']['trusted_user|flags|convert_links']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|flags|disabled'])) {
					$_HOAM_config -> setValue ('user/flags/disabled', $_SESSION['config_information']['trusted_user|flags|disabled']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|flags|email_validated'])) {
					$_HOAM_config -> setValue ('user/flags/email_validated', $_SESSION['config_information']['trusted_user|flags|email_validated']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|flags|holidays'])) {
					$_HOAM_config -> setValue ('user/flags/holidays', $_SESSION['config_information']['trusted_user|flags|holidays']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|flags|password_old'])) {
					$_HOAM_config -> setValue ('user/flags/password_old', $_SESSION['config_information']['trusted_user|flags|password_old']);
				}
				if (isset ($_SESSION['config_information']['trusted_user|flags|smileys'])) {
					$_HOAM_config -> setValue ('user/flags/smileys', $_SESSION['config_information']['trusted_user|flags|smileys']);
				}
				
				/***************************************************************
				 * Update Violation settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_violation|approval_group'])) {
					$_HOAM_config -> setValue ('violation/approval_group', $_SESSION['config_information']['trusted_violation|approval_group']);
				}
				if (isset ($_SESSION['config_information']['trusted_violation|require_approval'])) {
					$_HOAM_config -> setValue ('violation/require_approval', $_SESSION['config_information']['trusted_violation|require_approval']);
				}
				if (isset ($_SESSION['config_information']['trusted_violation|grace_after_purchase'])) {
					$_HOAM_config -> setValue ('violation/grace_after_purchase', $_SESSION['config_information']['trusted_violation|grace_after_purchase']);
				}
				if (isset ($_SESSION['config_information']['trusted_violation|require_approval'])) {
					$_HOAM_config -> setValue ('violation/require_approval', $_SESSION['config_information']['trusted_violation|require_approval']);
				}
				// Approval flags
				if (isset ($_SESSION['config_information']['trusted_violation|flags|needs_approval'])) {
					$_HOAM_config -> setValue ('violation/flags/needs_approval', $_SESSION['config_information']['trusted_violation|flags|needs_approval']);
				}
				if (isset ($_SESSION['config_information']['trusted_violation|flags|more_information'])) {
					$_HOAM_config -> setValue ('violation/flags/more_information', $_SESSION['config_information']['trusted_violation|flags|more_information']);
				}
				if (isset ($_SESSION['config_information']['trusted_violation|flags|approved'])) {
					$_HOAM_config -> setValue ('violation/flags/approved', $_SESSION['config_information']['trusted_violation|flags|approved']);
				}
				if (isset ($_SESSION['config_information']['trusted_violation|flags|not_approved'])) {
					$_HOAM_config -> setValue ('violation/flags/not_approved', $_SESSION['config_information']['trusted_violation|flags|not_approved']);
				}
				// Violation flags
				if (isset ($_SESSION['config_information']['trusted_violation|flags|assume_resolved'])) {
					$_HOAM_config -> setValue ('violation/flags/assume_resolved', $_SESSION['config_information']['trusted_violation|flags|assume_resolved']);
				}

				/***************************************************************
				 * Update Website settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_website|blurb'])) {
					$_HOAM_config -> setValue ('website/blurb', $_SESSION['config_information']['trusted_website|blurb']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|online_time'])) {
					$_HOAM_config -> setValue ('website/online_time', $_SESSION['config_information']['trusted_website|online_time']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|idle_time'])) {
					$_HOAM_config -> setValue ('website/idle_time', $_SESSION['config_information']['trusted_website|idle_time']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|email|abuse'])) {
					$_HOAM_config -> setValue ('website/email/abuse', $_SESSION['config_information']['trusted_website|email|abuse']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|email|root'])) {
					$_HOAM_config -> setValue ('website/email/root', $_SESSION['config_information']['trusted_website|email|root']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|email|webmaster'])) {
					$_HOAM_config -> setValue ('website/email/webmaster', $_SESSION['config_information']['trusted_website|email|webmaster']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|hostname'])) {
					$_HOAM_config -> setValue ('website/hostname', $_SESSION['config_information']['trusted_website|hostname']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|locale'])) {
					$_HOAM_config -> setValue ('website/locale', $_SESSION['config_information']['trusted_website|locale']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|mask_character'])) {
					$_HOAM_config -> setValue ('website/mask_character', $_SESSION['config_information']['trusted_website|mask_character']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|title'])) {
					$_HOAM_config -> setValue ('website/title', $_SESSION['config_information']['trusted_website|title']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|timezone'])) {
					$_HOAM_config -> setValue ('website/timezone', $_SESSION['config_information']['trusted_website|timezone']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|url'])) {
					$_HOAM_config -> setValue ('website/url', $_SESSION['config_information']['trusted_website|url']);
				}
				if (isset ($_SESSION['config_information']['trusted_website|record'])) {
					$_HOAM_config -> setValue ('website/record', $_SESSION['config_information']['trusted_website|record']);
				}
				
				/***************************************************************
				 * Update Wiki / Article settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_article|minimum_article_length'])) {
					$_HOAM_config -> setValue ('article/minimum_article_length', $_SESSION['config_information']['trusted_article|minimum_article_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_article|minimum_keywords_length'])) {
					$_HOAM_config -> setValue ('article/minimum_keywords_length', $_SESSION['config_information']['trusted_article|minimum_keywords_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_article|minimum_leadin_length'])) {
					$_HOAM_config -> setValue ('article/minimum_leadin_length', $_SESSION['config_information']['trusted_article|minimum_leadin_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_article|minimum_summary_length'])) {
					$_HOAM_config -> setValue ('article/minimum_summary_length', $_SESSION['config_information']['trusted_article|minimum_summary_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_wiki|default_groups'])) {
					$_HOAM_config -> setValue ('wiki/default_groups', serialize ($_SESSION['config_information']['trusted_wiki|default_groups']));
				}
				if (isset ($_SESSION['config_information']['trusted_article|root_id'])) {
					$_HOAM_config -> setValue ('article/ids/root', $_SESSION['config_information']['trusted_article|root_id']);
				}
				if (isset ($_SESSION['config_information']['trusted_article|flags|comments'])) {
					$_HOAM_config -> setValue ('article/flags/comments', $_SESSION['config_information']['trusted_article|flags|comments']);
				}
				if (isset ($_SESSION['config_information']['trusted_article|flags|draft'])) {
					$_HOAM_config -> setValue ('article/flags/draft', $_SESSION['config_information']['trusted_article|flags|draft']);
				}
				if (isset ($_SESSION['config_information']['trusted_article|flags|redirect'])) {
					$_HOAM_config -> setValue ('article/flags/redirect', $_SESSION['config_information']['trusted_article|flags|redirect']);
				}
				
				/***************************************************************
				 * Update Work Request settings
				 **************************************************************/
				if (isset ($_SESSION['config_information']['trusted_work_request|allow_user_requests'])) {
					$_HOAM_config -> setValue ('work_request/allow_user_requests', $_SESSION['config_information']['trusted_work_request|allow_user_requests']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|approval_group'])) {
					$_HOAM_config -> setValue ('work_request/approval_group', $_SESSION['config_information']['trusted_work_request|approval_group']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|minimum_title_length'])) {
					$_HOAM_config -> setValue ('work_request/minimum_title_length', $_SESSION['config_information']['trusted_work_request|minimum_title_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|minimum_description_length'])) {
					$_HOAM_config -> setValue ('work_request/minimum_description_length', $_SESSION['config_information']['trusted_work_request|minimum_description_length']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|minimum_notes_length'])) {
					$_HOAM_config -> setValue ('work_request/minimum_notes_length', $_SESSION['config_information']['trusted_work_request|minimum_notes_length']);
				}
				// Work request flags
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|require_approval'])) {
					$_HOAM_config -> setValue ('work_request/flags/require_approval', $_SESSION['config_information']['trusted_work_request|flags|require_approval']);
				}
				// Priority flags
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|low'])) {
					$_HOAM_config -> setValue ('work_request/flags/low', $_SESSION['config_information']['trusted_work_request|flags|low']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|normal'])) {
					$_HOAM_config -> setValue ('work_request/flags/normal', $_SESSION['config_information']['trusted_work_request|flags|normal']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|high'])) {
					$_HOAM_config -> setValue ('work_request/flags/high', $_SESSION['config_information']['trusted_work_request|flags|high']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|urgent'])) {
					$_HOAM_config -> setValue ('work_request/flags/urgent', $_SESSION['config_information']['trusted_work_request|flags|urgent']);
				}
				// Approval flags
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|needs_approval'])) {
					$_HOAM_config -> setValue ('work_request/flags/needs_approval', $_SESSION['config_information']['trusted_work_request|flags|needs_approval']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|more_information'])) {
					$_HOAM_config -> setValue ('work_request/flags/more_information', $_SESSION['config_information']['trusted_work_request|flags|more_information']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|approved'])) {
					$_HOAM_config -> setValue ('work_request/flags/approved', $_SESSION['config_information']['trusted_work_request|flags|approved']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|not_approved'])) {
					$_HOAM_config -> setValue ('work_request/flags/not_approved', $_SESSION['config_information']['trusted_work_request|flags|not_approved']);
				}
				// Status flags
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|new'])) {
					$_HOAM_config -> setValue ('work_request/flags/new', $_SESSION['config_information']['trusted_work_request|flags|new']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|assigned'])) {
					$_HOAM_config -> setValue ('work_request/flags/assigned', $_SESSION['config_information']['trusted_work_request|flags|assigned']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|planning'])) {
					$_HOAM_config -> setValue ('work_request/flags/planning', $_SESSION['config_information']['trusted_work_request|flags|planning']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|in_progress'])) {
					$_HOAM_config -> setValue ('work_request/flags/in_progress', $_SESSION['config_information']['trusted_work_request|flags|in_progress']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|on_hold'])) {
					$_HOAM_config -> setValue ('work_request/flags/on_hold', $_SESSION['config_information']['trusted_work_request|flags|on_hold']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|cancelled'])) {
					$_HOAM_config -> setValue ('work_request/flags/cancelled', $_SESSION['config_information']['trusted_work_request|flags|cancelled']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|rejected'])) {
					$_HOAM_config -> setValue ('work_request/flags/rejected', $_SESSION['config_information']['trusted_work_request|flags|rejected']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|flags|complete'])) {
					$_HOAM_config -> setValue ('work_request/flags/complete', $_SESSION['config_information']['trusted_work_request|flags|complete']);
				}
				if (isset ($_SESSION['config_information']['trusted_work_request|require_approval'])) {
					$_HOAM_config -> setValue ('work_request/require_approval', $_SESSION['config_information']['trusted_work_request|require_approval']);
				}

				unset ($_SESSION['config_information']);
				unset ($_SESSION['error_class_item']);

				header ('Location: /website/config/');
				exit ();
			}
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're being asked to perform
		header ('Location: /error/unknown_action/');
		exit ();
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

?>
