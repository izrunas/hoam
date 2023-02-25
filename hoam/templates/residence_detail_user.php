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

function HOAM_residenceAccountList ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;
	
	$group_members_class = new HOAM_group_members ('group_members');
	$user_class = new HOAM_user ('users');
	
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['account']['list'])));
	HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_residenceDetailUserJS');
	if ($h1) {
		$HTML = '<h1>' . $_HOAM_language ['field_names']['account']['list'] . '</h1>';
		$HTML .= '<div class="tabber">';
		$HTML .= '<div class="tabbertab" id="account_list" title="' . $_HOAM_language ['field_names']['account']['list'] . '">';
		$HTML .= '<div class="optional">';
		$HTML .= '<form id="residence_accounts">';
		$HTML .= '<table>';
		$HTML .= '<colgroup><col span="1" width="10%" /></colgroup>';
		$HTML .= '<thead><tr><th>' . $_HOAM_language ['tables']['column']['homeowner']['primary'] . '</th><th>' . $_HOAM_language ['field_names']['user']['accountname'] . '</th><th>' . $_HOAM_language ['field_names']['user']['name']['full'] . '</th><th>' . $_HOAM_language ['tables']['column']['user']['last_login'] . '</th></tr></thead>';
		$HTML .= '<tbody>';
		$associated_accounts = $user_class -> listIDGivenField ('homeowner_id', $GLOBALS['homeowner_id']);
		foreach ($associated_accounts as $account) {
			$account_info = $user_class -> listAll ($account);
			$account_info = $account_info[0];
			$HTML .= '<tr>';
			$HTML .= '<td class="center">';
			// Only allow homeowners to be primary contacts
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $_SESSION['current_user']['id'])) {
				$HTML .= '<input ';
				if ((int) $account_info['flags'] & (int) $_HOAM_setting ['user']['flags']['residence_primary']) $HTML .= 'checked ';
				$HTML .= 'type="radio" name="residence_primary" id="residence_primary" value="' . $account_info['id'] . '" />';
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
		$HTML .= '</tbody>';
		$HTML .= '</table>';
		$HTML .= '</form>';
		$HTML .= '</div>';
		$HTML .= '</div>'; // End account list tab
	} else {
		$HTML = '<a href="accounts/">' . $_HOAM_language ['field_names']['account']['list'] . '</a>';
	}
	return ($HTML);
}

function HOAM_residenceAssociation ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_queue_head, $_HOAM_setting;
	
	$budget_budget_class = new HOAM_budget_budget ('budget_budget');
	$group_members_class = new HOAM_group_members ('group_members');
	
	$budget_list = $budget_budget_class -> listAllId ();
	$public_budgets = FALSE;
	foreach ($budget_list as $budget) {
		if ($budget_budget_class -> isBitSet ($budget, $_HOAM_setting ['budget']['flags']['public'])) {
			$public_budgets = TRUE;
			break;
		}
	} 
	if (isset ($GLOBALS['global_url_array'][2])) {
		switch ($GLOBALS['global_url_array'][2]) {
			case ('budget') :
				$_HOAM_log -> add ('Loading /residence/association/budget/ page.', HOAM_MESSAGE_DEBUG);
				if ($public_budgets) {
					if (isset ($GLOBALS['global_url_array'][3])) {
						switch ($GLOBALS['global_url_array'][3]) {
							case 'view' :
								$_HOAM_log -> add ('Loading /residence/association/budget/view/ page.', HOAM_MESSAGE_DEBUG);
								$trusted_id = $budget_budget_class -> superClean ($GLOBALS['global_url_array'][4], $budget_budget_class -> returnColumnSize ('id'));
								if ($budget_budget_class -> idExists ($trusted_id)) {
									HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['information'])));
									HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_residenceBudgetViewJS');
									$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['information'] . '</h1>';
									$HTML .= '<h2 class="center">' . $budget_budget_class -> returnName ($trusted_id) . '</h2>';
									$HTML .= '<h3 class="center">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_budget_class -> returnDateStart ($trusted_id))) . ' ' . $_HOAM_language ['common']['to'] . ' ' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_budget_class -> returnDateEnd ($trusted_id))) . '</h3>';
									$HTML .= '<form class="donotprint" id="budget|view|form" action="">';
									$HTML .= '<input type="hidden" id="budget|view|id" value="' . $trusted_id . '">';
									$date_start = $budget_budget_class -> returnDateStart ($trusted_id);
									$date_end = $budget_budget_class -> returnDateEnd ($trusted_id);
									if (($date_start < date ($_HOAM_country['date']['format_mysql'])) AND ($date_end > date ($_HOAM_country['date']['format_mysql']))) {
										$date_start = date ($_HOAM_country['date']['format_mysql'], mktime (0, 0, 0, date ('n'), 1, date ('Y')));
										$date_end = date ($_HOAM_country['date']['format_mysql'], mktime (0, 0, 0, date ('n'), date ('t'), date ('Y')));
									}
									$HTML .= '<input type="hidden" id="budget|view|start" value="' . $date_start . '">';
									$HTML .= '<input type="hidden" id="budget|view|end" value="' . $date_end . '">';
									$HTML .= '</form>';
									$HTML .= '<table id="budget_detail" style="text-align: right">';
									$HTML .= '</table>';
								} else {
									header ('Location: /error/unknown_id/');
									exit ();
								}
								break;
							default:
								break;
						}
					} else {
						HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['budget']['information'])));
						$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['budget']['information'] . '</h1>';
						$HTML .= '<ul>';
						foreach ($budget_list as $budget) {
							$HTML .= '<li><a href="/residence/association/budget/view/' . $budget . '/"</a>' . $budget_budget_class -> returnName ($budget) . '</a></li>';
						}
						$HTML .= '</ul>';
					}
				} else {
					header ('Location: /error/feature_disabled/');
					exit ();
				}
				break;
			case ('rental_street') :
				$_HOAM_log -> add ('Loading /residence/association/rental_street/ page.', HOAM_MESSAGE_DEBUG);
				require_once (HOAM_PATH_FUNCTION . 'homeowner.php');

				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['reports']['rental_street'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['reports']['rental_street'] . '</h1>';
				$HTML .= HOAM_homeownerRentalStreet ();
				break;
			case ('unpaid_street') :
				$_HOAM_log -> add ('Loading /residence/association/unpaid_street/ page.', HOAM_MESSAGE_DEBUG);
				require_once (HOAM_PATH_FUNCTION . 'budget.php');

				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['reports']['unpaid_street'])));
				$HTML = '<h1>' . $_HOAM_language ['field_names']['reports']['unpaid_street'] . '</h1>';
				$HTML .= HOAM_budgetUnpaidStreet ();
				break;
			default:
				header ('Location: /error/404/');
				exit ();
			break;
		}
	} else {
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['account']['association'])));
		if ($h1) {
			$HTML = '<h1>' . $_HOAM_language ['field_names']['account']['association'] . '</h1>';
			$HTML .= '<ul>';
			if ($public_budgets) {
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="budget/">' . $_HOAM_language ['field_names']['budget']['budget']['information'] . '</a></li>';
			}
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="rental_street/">' . $_HOAM_language ['field_names']['reports']['rental_street'] . '</a></li>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="unpaid_street/">' . $_HOAM_language ['field_names']['reports']['unpaid_street'] . '</a></li>';
			$HTML .= '</ul>';
		} else {
			$HTML = '<a href="association/">' . $_HOAM_language ['field_names']['account']['association'] . '</a>';
		}
	}
	return ($HTML);
}

function HOAM_residenceBudget ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_queue_head, $_HOAM_setting;
	
	$group_members_class = new HOAM_group_members ('group_members');
	
	if (isset ($GLOBALS['global_url_array'][2])) {
		switch ($GLOBALS['global_url_array'][2]) {
			case ('history') :
				$_HOAM_log -> add ('Loading /residence/budget/history/ page.', HOAM_MESSAGE_DEBUG);
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['history'])));
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $_SESSION['current_user']['id'])) {
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['history'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'budget.php');
					$HTML .= $_HOAM_language ['help_pages']['generic']['not_official'];
					$HTML .= '{{LETTER_NOPRINT}}';
					$HTML .= HOAM_budgetAccountHistory ($GLOBALS['homeowner_id']);
				} else {
					header ('Location: /error/homeowner/');
					exit();
				}
				break;
			case ('invoice') :
				$_HOAM_log -> add ('Loading /residence/budget/invoice/ page.', HOAM_MESSAGE_DEBUG);
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['invoice']['current'])));
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $_SESSION['current_user']['id'])) {
					$HTML = '<h1>' . $_HOAM_language ['field_names']['budget']['invoice']['current'] . '</h1>';
					require_once (HOAM_PATH_FUNCTION . 'budget.php');
					$HTML .= $_HOAM_language ['help_pages']['generic']['not_official'];
					if (isset ($GLOBALS['global_url_array'][3]) AND ($GLOBALS['global_url_array'][3] == 'file')) {
						HOAM_budgetInvoiceGeneratePDF ($GLOBALS['homeowner_id']);
					}
					$HTML .= '{{LETTER_NOPRINT}}';
					// No actions, just display the normal page content.
					$HTML .= '<div style="margin: 1em; text-align: center"><a href="file/">' . $_HOAM_language ['field_names']['export']['download']['pdf'] . '</a></div>';
					$HTML .= HOAM_budgetInvoiceGenerate ($GLOBALS['homeowner_id']);
				} else {
					header ('Location: /error/homeowner/');
					exit();
				}
				break;
			case ('payment') :
				$_HOAM_log -> add ('Loading /residence/budget/payment/ page.', HOAM_MESSAGE_DEBUG);
				HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['budget']['payment'])));
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $_SESSION['current_user']['id'])) {
					echo 'Pay current amount via PayPal...';
				} else {
					header ('Location: /error/homeowner/');
					exit();
				}
				break;
			default:
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['account']['budget'])));
		if ($h1) {
			$HTML = '<h1>' . $_HOAM_language ['field_names']['account']['budget'] . '</h1>';
			$HTML .= '<ul>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="history/">' . $_HOAM_language ['field_names']['budget']['history'] . '</a></li>';
			$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/report.png\')"><a href="invoice/">' . $_HOAM_language ['field_names']['budget']['invoice']['current'] . '</a></li>';
			// $HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/money_dollar.png\')"><a href="payment/">' . $_HOAM_language ['field_names']['budget']['payment'] . '</a></li>';
			$HTML .= '</ul>';
		} else {
			$HTML = '<a href="budget/">' . $_HOAM_language ['field_names']['account']['budget'] . '</a>';
		}
	}
	return ($HTML);
}

function HOAM_residencePreviousOwners ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;
	
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	
	HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['residence']['previous'])));
	if ($h1) {
		$HTML = '<h1>' . $_HOAM_language ['field_names']['residence']['previous'] . '</h1>';
		$HTML .= '<table>';
		$HTML .= '<colgroup><col span="1" width="10%" /></colgroup>';
		$thead = '<thead>';
		$thead .= '<tr>';
		$thead .= '<th>#</th>';
		$thead .= '<th>' . $_HOAM_language ['field_names']['user']['name']['full'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['date']['purchased'] . '</th>';
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['homeowner']['date']['sold'] . '</th>';
		$thead .= '</tr>';
		$thead .= '</thead>';
		$HTML .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
		$HTML .= '<tbody>';
		$previous_owners = $homeowner_class -> listResidenceHomeowners ($homeowner_class -> returnLotId ($GLOBALS['homeowner_id']));
		$previous_count = count ($previous_owners);
		for ($i = 0; $i < $previous_count; $i++) {
			$homeowner_info = $homeowner_class -> listAll ($previous_owners[$i]);
			$homeowner_info = $homeowner_info[0];
			$HTML .= '<tr>';
			$HTML .= '<td class="center">' . ($previous_count - $i) . '</td>';
			$HTML .= '<td class="center">' . $homeowner_info['name'] . '</td>';
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
	} else {
		$HTML = '<a href="previous/">' . $_HOAM_language ['field_names']['residence']['previous'] . '</a>';
	}
	return ($HTML);
}

function HOAM_residenceViolations ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_queue_head, $_HOAM_setting;

	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$violation_class = new HOAM_violation ('violations');
	
	if (isset ($GLOBALS['global_url_array'][2])) {
		switch ($GLOBALS['global_url_array'][2]) {
			case 'letter' :
				$_HOAM_log -> add ('Loading /residence/violations/letter/ page.', HOAM_MESSAGE_DEBUG);
				if ($GLOBALS['global_url_array'][3]) {
					$trusted_violation_id = $violation_class -> superClean ($GLOBALS['global_url_array'][3], $violation_class -> returnColumnSize ('id'));
					if ($violation_class -> idExists ($trusted_violation_id)) {
						// Make sure this is a violation when the current user was
						// a homeowner (eg, don't let them see other homeowners'
						// violations.
						$violation_info = $violation_class -> listAll ($trusted_violation_id);
						$violation_info = $violation_info[0];
						if ($GLOBALS['homeowner_id'] == $homeowner_class -> returnOwner ($violation_info['dateviolation'], $violation_info['lot_id'])) {
							HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['violation']['letter'])));
							$HTML  = '<h1>' . $_HOAM_language ['field_names']['violation']['letter'] . '</h1>';
							// The style entry needs to be second currently because of the
							// css used when printing to make sure the header dissapears.
							$HTML .= $_HOAM_language ['help_pages']['generic']['not_official'];
							$HTML .= '{{LETTER_NOPRINT}}';
							$HTML .= '<div class="violation_letter">' . $violation_class -> returnOriginalLetter ($trusted_violation_id) . '</div>';
						} else {
							header ('Location: /error/homeowner/');
							exit();
						}
					} else {
						header ('Location: /error/unknown_id/');
						exit ();
					}
				} else {
					header ('Location: /residence/violations/');
					exit ();
				}
				break;
			default:
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['form_tabs']['violation']['history'])));
		require_once (HOAM_PATH_FUNCTION . 'violations.php');
		if ($h1) {
			$HTML = '<h1>' . $_HOAM_language ['form_tabs']['violation']['history'] . '</h1>';
			$HTML .= '<div class="tabber">';
			$HTML .= '<div class="tabbertab" id="account_list" title="' . $_HOAM_language ['form_tabs']['violation']['history'] . '">';
			$HTML .= '<div class="optional">';
			// Pick an arbitrary HOAM_date in the past for the last
			// HOAM_date.
			$HTML .= HOAM_violationResidenceHistory ($homeowner_class -> returnLotId ($GLOBALS['homeowner_id']), $homeowner_class -> returnPurchaseDate ($GLOBALS['homeowner_id']), '3000-01-01', TRUE);
			$HTML .= '</div>';
			$HTML .= '</div>'; // End homeowner tab
			$HTML .= '<div class="tabbertab" title="' . $_HOAM_language ['field_names']['violation']['homeowner']['previous'] . '">';
			$HTML .= '<div class="optional">';
			// Pick an arbitrary HOAM_date in the past for the first
			// HOAM_date.
			$HTML .= HOAM_violationResidenceHistory ($homeowner_class -> returnLotId ($GLOBALS['homeowner_id']), '1900-01-01', $homeowner_class -> returnPurchaseDate ($GLOBALS['homeowner_id']));
			$HTML .= '</div>';
			$HTML .= '</div>'; // End previous homeowner tab
			$HTML .= '</div>'; // End tabber
		} else {
			$HTML = '<a href="violations/">' . $_HOAM_language ['form_tabs']['violation']['history'] . '</a>';
		}
	}
	return ($HTML);
}

function HOAM_residenceWorkRequest ($h1 = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_log, $_HOAM_queue_head, $_HOAM_setting;
	
	if ($_HOAM_setting ['work_request']['allow_user_requests']) {
		$group_members_class = new HOAM_group_members ('group_members');
		$user_class = new HOAM_user ('users');
		$work_request_class = new HOAM_work_request ('work_requests');
	
		if (isset ($GLOBALS['global_url_array'][2])) {
			switch ($GLOBALS['global_url_array'][2]) {
				case ('add'):
					$_HOAM_log -> add ('Loading /residence/work_request/add/ page.', HOAM_MESSAGE_DEBUG);
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['work_request']['add'])));
					HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_residenceCalendarJS');

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
					$HTML .= '<form id="request_add" action="/hoam/scripts/work_request/user_edit_script.php" enctype="multipart/form-data" method="post">';
					if (isset ($_SESSION['error_class_item']) AND ($_SESSION ['error_class_item'] -> errorCount > 0)) {
						$HTML .= $_HOAM_language ['help_pages']['generic']['errors_exist'];
					}
					$HTML .= '<fieldset>';
					$HTML .= '<button type="submit" id="request|add" name="add">' . $_HOAM_language ['form_buttons']['work_request']['add'] . '</button>';
					$HTML .= '<button type="reset">' . $_HOAM_language ['form_buttons']['reset'] . '</button>';
					$HTML .= '<button type="submit" name="cancel">' . $_HOAM_language ['form_buttons']['cancel'] . '</button>';
					$HTML .= '</fieldset>';
					$HTML .= $_HOAM_language ['help_popups']['mandatory'];

					$HTML .= '<div class="tabber">';

					$HTML .= '<div class="tabbertab" id="request_information" title="' . $_HOAM_language ['form_tabs']['work_request']['add'] . '">';
					$HTML .= '<div class="mandatory">';
					$HTML .= HOAM_formText ('work_request|title', $_HOAM_language ['field_names']['work_request']['title'], $_SESSION['request_information']['trusted_work_request|title'], '', $work_request_class -> returnColumnSize ('title'));
					$HTML .= '</div>';
					$HTML .= '<div class="optional">';
					$HTML .= HOAM_formTextarea ('work_request|description', $_HOAM_language ['field_names']['work_request']['description'], $_SESSION['request_information']['trusted_work_request|description'], '', $_SESSION['current_user']['edit']['row'], $work_request_class -> returnColumnSize ('description'));
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
					$HTML .= '</div>'; // End new request tab

					$HTML .= '</div>'; // End Tabber
					$HTML .= '</form>';
					break;
				case ('edit'):
					$_HOAM_log -> add ('Loading /residence/work_request/edit/ page.', HOAM_MESSAGE_DEBUG);
					if ($GLOBALS['global_url_array'][3]) {
						$trusted_id = $work_request_class -> superClean ($GLOBALS['global_url_array'][3], $work_request_class -> returnColumnSize ('id'));
						if ($work_request_class -> idExists ($trusted_id)) {
							HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['work_request']['add'])));
							HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_residenceCalendarJS');

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

							if ($work_request_class -> returnStatus ($trusted_id) == $_HOAM_setting ['work_request']['flags']['complete']) {
								$HTML .= sprintf ($_HOAM_language ['help_pages']['work_request']['complete'], date ($_HOAM_country ['date']['format_reports'], strtotime ($work_request_class -> returnDateComplete ($trusted_id))));
							}
						
							$HTML .= '<form id="request_add" action="/hoam/scripts/work_request/user_edit_script.php" enctype="multipart/form-data" method="post">';
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
							$HTML .= HOAM_formText ('work_request|title', $_HOAM_language ['field_names']['work_request']['title'], $_SESSION['request_information']['trusted_work_request|title'], $work_request_class -> returnTitle ($trusted_id), $work_request_class -> returnColumnSize ('title'));
							$HTML .= '</div>';
							$HTML .= '<div class="optional">';
							$HTML .= HOAM_formTextarea ('work_request|description', $_HOAM_language ['field_names']['work_request']['description'], $_SESSION['request_information']['trusted_work_request|description'], $work_request_class -> returnDescription ($trusted_id), $_SESSION['current_user']['edit']['row'], $work_request_class -> returnColumnSize ('description'));
							$date_due = new DateTime ();
							$date_due -> modify ('+' . 14 . ' day');
							$HTML .= HOAM_formDate ('work_request|date|due', $_HOAM_language ['field_names']['work_request']['date']['due'], HOAM_date ($work_request_class -> returnDateDue ($trusted_id)), $date_due -> format ($_HOAM_country ['date']['format_reports']));
							$HTML .= HOAM_formSelect ('work_request|priority', $_HOAM_language ['field_names']['work_request']['priority']);
							if (isset ($_SESSION['request_information']['trusted_work_request|priority'])) {
								$default_option = $_SESSION['request_information']['trusted_work_request|priority'];
							} else {
								// Default the priority to Normal.
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
							$HTML .= '</div>'; // End new request tab
							$HTML .= '</div>'; // End Tabber
							$HTML .= '</form>';
						} else {
							header ('Location: /error/unknown_id/');
							exit ();
						}
					} else {
						header ('Location: /residence/work_request/list/');
						exit ();
					}
					break;
				case ('list') :
					$_HOAM_log -> add ('Loading /residence/work_request/list/ page.', HOAM_MESSAGE_DEBUG);
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['work_request']['mine'])));
					$HTML = '<h1>' . $_HOAM_language ['field_names']['work_request']['mine'] . '</h1>';
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
					$thead .= '</tfoot>';
					$tbody = '<tbody>';
					$request_list = $work_request_class -> listUserRequest ($_SESSION['current_user']['id']);
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
							$tbody .= '' . HOAM_returnTruncatedString (html_encode (stripslashes ($request_info['title'])), 50) . '</td>';
							$tbody .= '<td><small><a href="/residence/work_request/edit/' . $request_info['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small></td>';
							$tbody .= '<td>' . $user_class -> returnAccountName ($request_info['user_id']) . '</td>';
							$tbody .= '</tr>';
						}
					} else {
						$tbody .= '<tr><td colspan="8">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
					}
					$tbody .= '</tbody>';
					$HTML .= '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>'. $tbody . '<tbody></table>';
					break;
				default:
					header ('Location: /error/404/');
					exit ();
					break;
			}
		} else {
			$_HOAM_log -> add ('Loading /residence/work_request/ page.', HOAM_MESSAGE_DEBUG);
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['admin']['work_request'])));
			if ($h1) {
				$HTML = '<h1>' . $_HOAM_language ['field_names']['admin']['work_request'] . '</h1>';
				$HTML .= '<ul>';
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/wrench.png\')"><a href="/residence/work_request/add/">' . $_HOAM_language ['field_names']['work_request']['add'] . '</a></li>';
				$HTML .= '<li style="list-style-image: url(\'/hoam/images/icons/wrench.png\')"><a href="/residence/work_request/list/">' . $_HOAM_language ['field_names']['work_request']['mine'] . '</a></li>';
				$HTML .= '</ul>';
			} else {
				$HTML = '<a href="/residence/work_request/">' . $_HOAM_language ['field_names']['admin']['work_request'] . '</a>';
			}
		}
		return ($HTML);
	} else {
		header ('Location: /error/feature_disabled/');
		exit ();
	}
}

function HOAM_residenceBudgetViewJS () {
	echo '<script type="text/javascript" src="/hoam/javascript/residenceBudgetView.js"></script>';
}

function HOAM_residenceCalendarJS () {
	echo '<script type="text/javascript" src="/3rdparty/mattkruse/CalendarPopup_combined_compact.js"></script>';
}

function HOAM_residenceDetailUserJS () {
	echo '<script type="text/javascript" src="/hoam/javascript/residence_detail_user.js"></script>';
}

function HOAM_residenceWorkRequestJS () {
	echo '<script type="text/javascript" src="/hoam/javascript/residence_detail_user.js"></script>';
}

$budget_class = new HOAM_budget ('budget');
$group_members_class = new HOAM_group_members ('group_members');
$homeowner_class = new HOAM_homeowner ('homeowners');
$user_class = new HOAM_user ('users');

$homeowner_id = $user_class -> returnHomeownerId ($_SESSION['current_user']['id']);

// OK, basically we're checking that the current user is logged in, and that
// they have been validated as a homeowner or resident, and that the homeowner
// this user is associated with is the same as the current owner of the
// residence.
if (isset ($_SESSION['current_user']['id']) AND
	($homeowner_id === $homeowner_class -> returnCurrentOwner ($homeowner_class -> returnLotId ($homeowner_id))) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['resident'], $_SESSION['current_user']['id']))) {
	if (isset ($GLOBALS['global_url_array'][1])) {
		switch ($GLOBALS['global_url_array'][1]) {
			case ('accounts') :
				$_HOAM_log -> add ('Loading /residence/accounts/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_residenceAccountList (TRUE);
				break;
			case ('association') :
				$_HOAM_log -> add ('Loading /residence/association/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_residenceAssociation (TRUE);
				break;
			case ('budget') :
				$_HOAM_log -> add ('Loading /residence/budget/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_residenceBudget (TRUE);
				break;
			case ('violations') :
				$_HOAM_log -> add ('Loading /residence/violations/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_residenceViolations (TRUE);
				break;
			case ('previous') :
				$_HOAM_log -> add ('Loading /residence/previous/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_residencePreviousOwners (TRUE);
				break;
			case ('work_request') :
				$_HOAM_log -> add ('Loading /residence/work_request/ page.', HOAM_MESSAGE_DEBUG);
				echo HOAM_residenceWorkRequest (TRUE);
				break;
			default:
				header ('Location: /error/404/');
				exit ();
				break;
		}
	} else {
		require_once (HOAM_PATH_FUNCTION . 'homeowner.php');
		HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['residence']['details'])));
				
		echo '<h1>' . $_HOAM_language ['field_names']['residence']['details'] . '</h1>';
				
		echo '<em><strong>' . $_HOAM_language ['field_names']['homeowner']['address'] . '</strong></em><br />' . HOAM_returnAddress ($homeowner_class -> returnLotId ($homeowner_id));
		echo '<br /><br /><em><strong>' . $_HOAM_language ['field_names']['homeowner']['mailing'] . '</strong></em><br />' . HOAM_homeownerReturnMailingAddress ($homeowner_id);
		echo '<br /><br /><em><strong>' . $_HOAM_language ['field_names']['homeowner']['purchasedate'] . '</strong></em><br />' . date ($_HOAM_country ['date']['format_long'], strtotime ($homeowner_class -> returnPurchaseDate ($homeowner_id)));
		$account_balance = $budget_class -> returnAccountBalance ($homeowner_id);
		echo '<br /><br /><em><strong>' . $_HOAM_language ['field_names']['budget']['balance'] . '</strong></em><br />';
		echo HOAM_returnCurrency ($account_balance);
		echo '<br /><br />';
		echo '<ul>';
		echo '<li style="list-style-image: url(\'/hoam/images/icons/money.png\')">' . HOAM_residenceBudget () . '</li>';
		echo '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')">' . HOAM_residenceViolations () . '</li>';
		echo '<li style="list-style-image: url(\'/hoam/images/icons/user.png\')">' . HOAM_residenceAccountList () . '</li>';
		echo '<li style="list-style-image: url(\'/hoam/images/icons/house.png\')">' . HOAM_residencePreviousOwners () . '</li>';
		echo '</ul>';
		if ($_HOAM_setting ['work_request']['allow_user_requests']) {
			echo '<ul>';
			echo '<li style="list-style-image: url(\'/hoam/images/icons/wrench.png\')">' . HOAM_residenceWorkRequest () . '</li>';
			echo '</ul>';
		}
		echo '<ul>';
		echo '<li style="list-style-image: url(\'/hoam/images/icons/money.png\')">' . HOAM_residenceAssociation () . '</li>';
		echo '</ul>';
	}
} else {
	header ('Location: /error/401/');
	exit ();
}

?>
