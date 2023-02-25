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

// Is the current user allowed to add / edit letters?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
		require_once (HOAM_PATH_FUNCTION . 'letter.php');
	
		$attachment_class = new HOAM_attachments ('attachments');
		$budget_class = new HOAM_budget ('budget');
		$homeowner_class = new HOAM_homeowner ('homeowners');
		$lot_class = new HOAM_lot ('lots');
		$wiki = new HOAM_wiki_parser ();
	
		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['letter_information'])) {
			unset ($_SESSION['letter_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the action, send them back to the main page
			header ('Location: /admin/');
		} elseif (isset ($_POST['delete'])) {
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
				$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
				$result = $attachment_class -> deleteId ((string) $attachment_class -> superClean ($_POST['letter|id'], $attachment_class -> returnColumnSize ('id')));
				if ($result) {
					// Send the user back to the administration page.
					header ('Location: /admin/');
				} else {
					// Error Removing Item
					header ('Location: /error/remove/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// Start populating the letter_information array, and check for
			// errors.
			$_SESSION['letter_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			if (isset ($_POST['letter|address'])) {
				$_SESSION['letter_information']['trusted_letter|address'] = (string) $lot_class -> superClean ($_POST['letter|address'], $lot_class -> returnColumnSize ('id'));
				// Make sure the address is valid.
				if ($lot_class -> idExists ($_SESSION['letter_information']['trusted_letter|address'])) {
				} else {
					// The address is unknown.
					$_SESSION ['error_class_item'] -> addError ('letter|address', $_HOAM_language ['errors']['lot']['unknown_id']);
				}
			}
			if (isset ($_POST['letter|approver'])) {
				$_SESSION['letter_information']['trusted_letter|approver'] = (string) $lot_class -> superClean ($_POST['letter|approver'], 64);
			}
			if (isset ($_POST['letter|delivery_tracking'])) {
				$_SESSION['letter_information']['trusted_letter|delivery_tracking'] = (string) $lot_class -> superClean ($_POST['letter|delivery_tracking'], 64);
			}
			if (isset ($_POST['letter|name'])) {
				$_SESSION['letter_information']['trusted_letter|name'] = (string) $lot_class -> superClean ($_POST['letter|name'], 128);
				$_SESSION['letter_information']['trusted_letter|name'] = escapeshellcmd ($_SESSION['letter_information']['trusted_letter|name']);
				if (file_exists (HOAM_PATH_LETTER . $_SESSION['letter_information']['trusted_letter|name'])) {
					if (is_file (HOAM_PATH_LETTER . $_SESSION['letter_information']['trusted_letter|name'])) {
						if (is_readable (HOAM_PATH_LETTER . $_SESSION['letter_information']['trusted_letter|name'])) {
							$contents = file_get_contents (HOAM_PATH_LETTER . $_SESSION['letter_information']['trusted_letter|name']);
							$index = array ();
							$values = array ();

							$parser = xml_parser_create ('');
							xml_parse_into_struct ($parser, $contents, $values, $index);
						} else {
							$_SESSION ['error_class_item'] -> addError ('letter|name', sprintf ($_HOAM_language ['errors']['hoam']['installation']['file-not-readable'], $trusted_letter));
						}
					} else {
						$_SESSION ['error_class_item'] -> addError ('letter|name', $_HOAM_language ['errors']['generic']['invalid-file']);
					}
				} else {
					$_SESSION ['error_class_item'] -> addError ('letter|name', sprintf ($_HOAM_language ['errors']['hoam']['installation']['missing-file'], $trusted_letter));
				}
			}
			if (isset ($_POST['letter|person'])) {
				$_SESSION['letter_information']['trusted_letter|person'] = (string) $lot_class -> superClean ($_POST['letter|person'], 64);
			}
			if (isset ($_POST['letter|specific'])) {
				$_SESSION['letter_information']['trusted_letter|specific'] = (string) $lot_class -> superClean ($_POST['letter|specific'], 256);
			}
			// Check any prerequisites the letter may have.
			if (isset ($values [$index['PREREQUISITES'][0]]['value'])) {
				$prerequisites_result = eval ($values [$index['PREREQUISITES'][0]]['value']);
			}
			// Make sure any required additional fields are completed.
			if (isset ($index['ADDITIONAL_FIELDS'])) {
				foreach ($index['ADDITIONAL_FIELDS'] as $field) {
					if ($values [$field + 1]['tag'] == 'FIELD') {
						$trusted_field = (string) $lot_class -> superclean ($_POST['additional|' . $values[$field + 4]['value']], 1024);
						if (($values[$field + 6]['value'] == '1') AND ($trusted_field == '')) {
							$_SESSION ['error_class_item'] -> addError ('additional|' . $values[$field + 4]['value'], $_HOAM_language ['errors']['generic']['missing']);
						}
					}
				}
			}
			
// echo print_r ($_POST); print_r ($_SESSION['letter_information']); $_SESSION['error_class_item'] -> errorCount; print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/letter/add/');
				} else {
					header ('location: /admin/letter/edit/' . $_SESSION ['letter_information']['trusted_letter|id'] . '/');
				}
			} else {
				$current_homeowner = $homeowner_class -> returnCurrentOwner ($_SESSION['letter_information']['trusted_letter|address']);
				$homeowner_name = $homeowner_class -> returnName ($current_homeowner);
				$last_payment = $budget_class -> returnDatePosted ($budget_class -> returnLastPayment ($current_homeowner));
				$current_due = $budget_class -> returnTotalAmountDue ($current_homeowner);
				if (($current_due == '') or ($current_due == FALSE)) {
					$current_due = 0;
				}
				if ($last_payment) {
					$last_payment = date ($_HOAM_country ['date']['format_long'], strtotime ($last_payment));
				} else {
					$last_payment = ucfirst ($_HOAM_language ['common']['never']);
				}
				// If there is pdf-specific formatting, use that.
				if ($values [$index['PDF'][0]]['value']) {
					$letter = $values [$index['PDF'][0]]['value'];
				} else {
					$letter = $values [$index['CONTENT'][0]]['value'];
				}
			
				$letter_subs = array (
					'/\{\{LETTER_ASSESSMENT_AMOUNT\}\}/i' => '\\' . HOAM_returnCurrency (HOAM_letterAssessmentAmount ($_SESSION['letter_information']['trusted_letter|address'])),
					'/\{\{BUDGET_DUE\}\}/i' => '\\' . HOAM_returnCurrency ($current_due),
					'/\{\{BUDGET_LAST_PAYMENT\}\}/i' => $last_payment,
					'/\{\{HOMEOWNER_MAILING\}\}/i' => HOAM_homeownerReturnMailingAddress ($current_homeowner),
					'/\{\{HOMEOWNER_NAME\}\}/i' => $homeowner_name,
					'/\{\{HOMEOWNER_RESIDENCE\}\}/i' => HOAM_returnAddress ($_SESSION['letter_information']['trusted_letter|address']),
					'/\{\{LETTER_APPROVER\}\}/i' => $_SESSION['letter_information']['trusted_letter|approver'],
					'/\{\{LETTER_PERSON\}\}/i' => $_SESSION['letter_information']['trusted_letter|person'],
					'/\{\{LETTER_SPECIFIC\}\}/i' => $_SESSION['letter_information']['trusted_letter|specific'],
					'/\{\{LETTER_TRACKING\}\}/i' => $_HOAM_language ['field_names']['letter']['delivery_tracking'] . '<br />' . $_SESSION['letter_information']['trusted_letter|delivery_tracking'],
					'/\{\{LETTER_TOPIC\}\}/i' => $values [$index['NAME'][0]]['value'],
					'/\{\{LOT_BLOCK\}\}/i' => $lot_class -> returnBlock ($_SESSION['letter_information']['trusted_letter|address']),
					'/\{\{LOT_LOT\}\}/i' => $lot_class -> returnLot ($_SESSION['letter_information']['trusted_letter|address']),
					'/\{\{LOT_PLAT\}\}/i' => $lot_class -> returnPlat ($_SESSION['letter_information']['trusted_letter|address'])
					);
				$budget_sub = '/\{\{BUDGET_DUE([\+\-])(\d{1,5}(\.\d{1,2})?)\}\}/i';
				$date_sub = '/\{\{DATE([+\-])(\d{1,3})\}\}/i';

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

				// Make sure any required additional fields are completed.
				if (isset ($index['ADDITIONAL_FIELDS'])) {
					foreach ($index['ADDITIONAL_FIELDS'] as $field) {
						if ($values [$field + 1]['tag'] == 'FIELD') {
							$trusted_field = (string) $lot_class -> superclean ($_POST['additional|' . $values[$field + 4]['value']], 1024);
							$parsed_letter = preg_replace ('/\{\{' . $values[$field + 4]['value'] . '\}\}/i', $trusted_field, $parsed_letter);
						}
					}
				}

				// Import the default CSS.
				$css = '<style>' . file_get_contents(HOAM_PATH . 'hoam/css/default.css') . '</style>';

// echo $css . $parsed_letter; exit;

				// create new PDF document
				$pdf = new HOAM_LetterPDF (PDF_PAGE_ORIENTATION, 'mm', 'LETTER', TRUE, 'UTF-8', FALSE);
				$pdf -> SetCreator (PDF_CREATOR);
				$pdf -> SetAuthor ('HOAM v' . HOAM_VERSION . ' ' . HOAM_RELEASE . ' :: ' . HOAM_URL);
				$pdf -> SetTitle (date ($_HOAM_country ['date']['format_mysql']) . ' ' . $values [$index['NAME'][0]]['value'] . ' for ' . HOAM_returnAddress ($_SESSION['letter_information']['trusted_letter|address']) . ' (' . $homeowner_name . ')');
				$pdf -> SetSubject (date ($_HOAM_country ['date']['format_mysql']) . ' ' . $values [$index['NAME'][0]]['value'] . ' for ' . HOAM_returnAddress ($_SESSION['letter_information']['trusted_letter|address']) . ' (' . $homeowner_name . ')');
				$pdf -> SetKeywords ('HOAM, homeowner, letter, ' . $values [$index['NAME'][0]]['value'] . ', ');
				$tmargin = $rmargin = $bmargin = $lmargin = 12.7;
				$pdf -> SetMargins ($lmargin, $tmargin);

				// remove default header/footer
				$pdf -> setPrintHeader (FALSE);
				$pdf -> setPrintFooter (FALSE);

				//set auto page breaks
				$pdf -> SetAutoPageBreak (TRUE, 12.7);

				// set image scale factor
				$pdf -> setImageScale (PDF_IMAGE_SCALE_RATIO);

				// set the default font
				$pdf->SetFont('dejavusans', '', 10, '', TRUE);

				// Add the first page of data.
				$pdf -> AddPage ();

				// output the HTML content
				$pdf -> writeHTML ($css . $parsed_letter);

				// reset pointer to the last page
				$pdf -> lastPage();	

				$filename = date ('Ymd') . '_' . $_HOAM_setting ['website']['title'] . ' ' . HOAM_returnAddress ($_SESSION['letter_information']['trusted_letter|address']) . '_' . $values [$index['NAME'][0]]['value'] . '.pdf';
				if (FALSE) {
					// return an array with the filename and PDF data in a string. 
					$data = $pdf -> Output ('Test', 'S');
					$attachment_id = FALSE;
					$attachment_id = HOAM_attachmentAddInternal ($filename,
						$data,
						'application/pdf',
						$_SESSION['current_user']['id'],
						$_HOAM_setting ['attachment']['flags']['homeowner'],
						'Letter to homeowner ' . $homeowner_name,
						array ($current_homeowner));
					// Did the letter generate and attach correctly?
					if ($attachment_id) {
						if (isset ($values [$index['FINISH'][0]]['value'])) {
							// $finish_result = eval ($values [$index['FINISH'][0]]['value']);
						}
					} else {
						$_HOAM_log -> add ('Letter: failed to generate letter for homeowner.', HOAM_MESSAGE_ERROR);
					}
				} else {
					// Output a file to the client browser and exit.
					$pdf -> Output ($filename, 'D');
				}
			
				/*************************************************************************/

				// Clear the error_class_item variable since everything worked ok.
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}
				// Clear the letter_information variable since everything worked ok.
				if (isset ($_SESSION['letter_information'])) {
					unset ($_SESSION['letter_information']);
				}

				header ('Location: /admin/');
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
