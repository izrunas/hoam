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

session_start();

$group_members_class = new HOAM_group_members ('group_members');

if (isset ($_GET['address']) AND isset ($_GET['approver']) AND isset ($_GET['letter']) AND isset ($_GET['person'])) {
	if (isset ($_SESSION['current_user']['id']) AND
		($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
		$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner-edit'], $_SESSION['current_user']['id']) OR
		($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
		require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
		require_once (HOAM_PATH_FUNCTION . 'letter.php');
		
		$budget_class = new HOAM_budget ('budget');
		$homeowner_class = new HOAM_homeowner ('homeowners');
		$lot_class = new HOAM_lot ('lots');
		$wiki = new HOAM_wiki_parser ();
		
		$trusted_address = $lot_class -> superClean ($_GET['address'], $lot_class -> returnColumnSize ('id'));
		if (!$lot_class -> idExists ($trusted_address)) {
			echo $_HOAM_language ['errors']['lot']['unknown_id'];
			exit ();
		}
		$trusted_approver = $lot_class -> superClean ($_GET['approver'], 64);
		$trusted_person = $lot_class -> superClean ($_GET['person'], 64);
		$trusted_letter = $lot_class -> superClean ($_GET['letter'], 128);
		$trusted_letter = escapeshellcmd ($trusted_letter);
		
		if (file_exists (HOAM_PATH_LETTER . $trusted_letter)) {
			if (is_file (HOAM_PATH_LETTER . $trusted_letter)) {
				if (is_readable (HOAM_PATH_LETTER . $trusted_letter)) {
					$contents = file_get_contents (HOAM_PATH_LETTER . $trusted_letter);
					$index = array ();
					$values = array ();

					$parser = xml_parser_create ('');
					xml_parse_into_struct ($parser, $contents, $values, $index);

					$current_homeowner = $homeowner_class -> returnCurrentOwner ($trusted_address);
					$current_due = $budget_class -> returnTotalAmountDue ($current_homeowner);
					if (($current_due == '') or ($current_due == FALSE)) {
						$current_due = 0;
					}
					$last_payment = $budget_class -> returnDatePosted ($budget_class -> returnLastPayment ($current_homeowner));
					if ($last_payment) {
						$last_payment = date ($_HOAM_country ['date']['format_long'], strtotime ($last_payment));
					} else {
						$last_payment = ucfirst ($_HOAM_language ['common']['never']);
					}
					$letter_subs = array (
					'/\{\{LETTER_ASSESSMENT_AMOUNT\}\}/i' => '\\' . HOAM_returnCurrency (HOAM_letterAssessmentAmount ($trusted_address)),
					'/\{\{BUDGET_DUE\}\}/i' => '\\' . HOAM_returnCurrency ($current_due),
					'/\{\{BUDGET_LAST_PAYMENT\}\}/i' => $last_payment,
					'/\{\{HOMEOWNER_MAILING\}\}/i' => HOAM_homeownerReturnMailingAddress ($current_homeowner),
					'/\{\{HOMEOWNER_NAME\}\}/i' => $homeowner_class -> returnName ($current_homeowner),
					'/\{\{HOMEOWNER_RESIDENCE\}\}/i' => HOAM_returnAddress ($trusted_address),
					'/\{\{LETTER_APPROVER\}\}/i' => $trusted_approver,
					'/\{\{LETTER_PERSON\}\}/i' => $trusted_person,
					'/\{\{LETTER_TOPIC\}\}/i' => $values [$index['NAME'][0]]['value'],
					'/\{\{LOT_BLOCK\}\}/i' => $lot_class -> returnBlock ($trusted_address),
					'/\{\{LOT_LOT\}\}/i' => $lot_class -> returnLot ($trusted_address),
					'/\{\{LOT_PLAT\}\}/i' => $lot_class -> returnPlat ($trusted_address)
					);
					$budget_sub = '/\{\{BUDGET_DUE([\+\-])\$?(\d{1,5}(\.\d{1,2})?)\}\}/i';
					$date_sub = '/\{\{DATE([+\-])(\d{1,3})\}\}/i';
															
					$letter = $values [$index['CONTENT'][0]]['value'];
					$parsed_letter = preg_replace (array_keys ($letter_subs), array_values ($letter_subs), $letter);
					// Process any wiki tags. Do this before the budget or date
					// tags in case there are any nested tags such as
					// {{BUDGET_DUE+{{ORG_DOC_RESALE}}}} 
					$parsed_letter = $wiki -> parse ($parsed_letter);
					// Process any budget or date tags
					$parsed_letter = preg_replace_callback ($budget_sub, 'HOAM_letterBudgetCallback', $parsed_letter);
					$parsed_letter = preg_replace_callback ($date_sub, 'HOAM_letterDateCallback', $parsed_letter);

					// Finally, we're going to eval the letter for any code
					// contained within.
					ob_start ();
					eval ('?>' . $parsed_letter . '<?php ');
					$parsed_letter = ob_get_contents ();
					ob_end_clean ();

					$xw = xmlwriter_open_memory ();
					xmlwriter_set_indent ($xw, 1);
					xmlwriter_set_indent_string ($xw, ' ');
					xmlwriter_start_document ($xw, '1.0', 'UTF-8');
					// A first element
					xmlwriter_start_element ($xw, 'ajax-response');
					xmlwriter_start_element ($xw, 'item');
					xmlwriter_write_element ($xw, 'require_delivery_certified', $values [$index['REQUIRE_DELIVERY_CERTIFIED'][0]]['value']);
					xmlwriter_write_element ($xw, 'require_delivery_confirmation', $values [$index['REQUIRE_DELIVERY_CONFIRMATION'][0]]['value']);
					xmlwriter_write_element ($xw, 'suggest_delivery_certified', $values [$index['SUGGEST_DELIVERY_CONFIRMATION'][0]]['value']);
					xmlwriter_write_element ($xw, 'suggest_delivery_confirmation', $values [$index['SUGGEST_DELIVERY_CONFIRMATION'][0]]['value']);
					xmlwriter_start_element ($xw, 'content');
					xmlwriter_write_cdata ($xw, $parsed_letter);
					xmlwriter_end_element ($xw); // content
					if (isset ($index['ADDITIONAL_FIELDS'])) {
						xmlwriter_start_element ($xw, 'additional_fields');
						// The resulting array looks different if there's only
						// one additional field. 
						foreach ($index['ADDITIONAL_FIELDS'] as $field) {
							if ($values [$field + 1]['tag'] == 'FIELD') {
								xmlwriter_start_element ($xw, 'field');
								xmlwriter_write_element ($xw, 'description', $values[$field + 2]['value']);
								xmlwriter_write_element ($xw, 'id', $values[$field + 4]['value']);
								xmlwriter_write_element ($xw, 'required', $values[$field + 6]['value']);
								xmlwriter_end_element ($xw); // field
							}
						}
						xmlwriter_end_element ($xw); // additional_fields
					}
					xmlwriter_end_element ($xw); // item
					// Generate the XML file and echo it back to the client
					xmlwriter_end_element ($xw); // ajax-response
					xmlwriter_end_document ($xw);
					header ('Content-type: text/xml');
					echo xmlwriter_output_memory ($xw);
				} else {
					printf ($_HOAM_language ['errors']['hoam']['installation']['file-not-readable'], $trusted_letter);
				}
			} else {
				echo $_HOAM_language ['errors']['generic']['invalid-file'];
			}
		} else {
			printf ($_HOAM_language ['errors']['hoam']['installation']['missing-file'], $trusted_letter);
		}
	} else {
		echo $_HOAM_language ['help_pages']['website']['403'];
	}
} else {
	echo $_HOAM_language ['errors']['generic']['not-all-parameters'];
}

exit ();

?>
