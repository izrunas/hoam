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

function HOAM_violationRecommendedSeverity ($violation_id) {
	global $_HOAM_setting;
	
	$violation_class = new HOAM_violation ('violations');
	$violation_category_class = new HOAM_violation_category ('violation_category');
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');
		
	// Get the severity level for the specified violation;
	if ($violation_class -> idExists ($violation_id)) {
		$violation_severity = $violation_class -> returnSeverity ($violation_id);
		$violation_category = unserialize ($violation_class -> returnCategory ($violation_id));
		// Was the previous violation over the reset_violation_calendar threshold?
		if ((strtotime (date ("Y-m-d")) - strtotime ($violation_class -> returnViolationDate ($violation_id))) >= (((int) $_HOAM_setting ['violation']['days_to_reset']) * 60 * 60 * 24)) {
			// Just use the default severity of the first category found.
			$recommended_severity = $violation_category_class -> returnInitialSeverity ($violation_category[0]);
		} else {
			$recommended_severity = $violation_severity_class -> returnEscalate ($violation_severity);
		}
	} else {
		// We'll hit this if there are no violations on record for a
		// homeowner (eg, $violation_id is invalid).
		$recommended_severity = $_HOAM_setting ['violation']['default_severity'];
	}
	return ($recommended_severity);
}

function HOAM_violationResidenceHistory ($lot, $date_start, $date_end) {
	global $_HOAM_country, $_HOAM_language;
	
	$violation_class = new HOAM_violation ('violations');
	$violation_category_class = new HOAM_violation_category ('violation_category');
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');
				
	$colgroup = '<colgroup span="4">';
	$colgroup .= '<col width="15%" />';
	$colgroup .= '<col width="10%" />';
	$colgroup .= '<col />';
	$colgroup .= '<col width="15%" />';
	$colgroup .= '<col width="15%" />';
	$colgroup .= '</colgroup>';
	$thead = '<thead><tr>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['date']['violation'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['severity']['header'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['category']['name'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['date']['resolveby'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['date']['resolution'] . '</th>';
	$thead .= '</tr></thead>';
	$tbody = '<tbody>';
	$violation_list = $violation_class -> listRangeLotId ($date_start, $date_end, $lot);
	if (is_array ($violation_list)) {
		for ($i = 0; $i < count ($violation_list); $i++) {
			$violation_info = $violation_class -> listAll ($violation_list[$i]);
			$violation_info = $violation_info[0];
			$tbody .= '<tr>';
			$tbody .= '<td class="center"><a href="/admin/violation/letter/' . $violation_info['id'] . '">' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($violation_info['dateviolation'])) . '</a></td>';
			$tbody .= '<td class="center">' . $violation_severity_class -> returnSeverity ($violation_info['severity']) . '</td>';
			$tbody .= '<td><ul style="list-style: none">';
			$category_list = unserialize ($violation_info['category']);
			foreach ($category_list as $category) {
				$tbody .= "<li>" . $violation_category_class -> returnCategory ($category). "</li>";
			}
			$tbody .= "</ul></td>";
			$tbody .= '<td class="center">' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($violation_info['dateresolveby'])) . '</td>';
			if ($violation_info['dateresolution'] AND ($violation_info['dateresolution'] != '1970-01-01')) {
				$resolution_date = HOAM_date ($_HOAM_country ['date']['format_compare'], strtotime ($violation_info['dateresolution']));
				$current_date = HOAM_date ($_HOAM_country ['date']['format_compare']);
				switch (TRUE) {
					case (($resolution_date > $current_date) OR ($resolution_date < HOAM_date ($_HOAM_country ['date']['format_compare'], strtotime ($violation_info['dateviolation'])))) :
						$tbody .= '<td class="center error">';
						break;
					case ($resolution_date == $current_date) :
						$tbody .= '<td class="center caution">';
						break;
					case ($resolution_date < $current_date) :
						$tbody .= '<td class="center complete">';
						break;
					default:
						$tbody .= '<td class="center">';
				}
				$tbody .= HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($resolution_date));
			} else {
				$tbody .= '<td class="center">' . $_HOAM_language ['common']['na'];
			}
			$tbody .= '</td></tr>';
		}
	} else {
		$tbody .= '<tr><td class="center" colspan="5">' . $_HOAM_language ['errors']['violation']['history_none'] . '</td></tr>';
	}
	$tbody .= '</tbody>';
	$HTML ='<table>' . $colgroup . $thead . $tbody . '</table>';
	return ($HTML);
}

function HOAM_violationShowExistingCategoryList () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting, $_HOAM_queue_head;
	
	$violation_category_class = new HOAM_violation_category ('violation_category');
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');
	
	HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminViolationCategoryJS');
	$HTML  = '<div class="tabbertab" id="category_list" title="' . $_HOAM_language ['form_tabs']['violation']['category']['list'] . '">';
	$HTML .= '<div class="optional">';
	$HTML .= '<label for="violation|category|show|name">' . $_HOAM_language ['field_names']['violation']['category']['name'] . '</label>';
	$HTML .= '<select id="violation|category|show|name">';
	$category_list = $violation_category_class -> listCategoryAll ();
	$optgroup = FALSE;
	if (is_array ($category_list)) {
		foreach ($category_list as $category) {
			// Is this a parent category?
			if ($category[1] == 0) {
				if ($optgroup) {
					$HTML .= '/optgroup>';
				} else {
					$optgroup = TRUE;
				}
				$HTML .= '<optgroup label="' . $violation_category_class -> returnCategory ($category[0]) . '">';
				continue;
			}
			$HTML .= '<option value="' . $category[0] . '">' . $violation_category_class -> returnCategory ($category[0]) . '</option>';
		}
		if ($optgroup) {
			$HTML .= '</optgroup>';
		}
	}
	$HTML .= '</select>';
	$HTML .= '<label for="violation|category|show|severity">' . $_HOAM_language ['field_names']['violation']['category']['initial_severity'] . '</label>';
	$HTML .= '<input readonly="readonly" id="violation|category|show|severity" size="' . $violation_severity_class -> returnColumnSize ('severity') . '" />';
	$HTML .= '<label for="violation|category|show|description">' . $_HOAM_language ['field_names']['violation']['category']['description'] . '</label>';
	$HTML .= '<textarea readonly="readonly" rows="' . $_SESSION['current_user']['edit']['row'] . '" id="violation|category|show|description"></textarea>';
	$HTML .= '<label for="violation|category|show|detail">' . $_HOAM_language ['field_names']['violation']['category']['detail'] . '</label>';
	$HTML .= '<textarea readonly="readonly" rows="' . $_SESSION['current_user']['edit']['row'] . '" id="violation|category|show|detail"></textarea>';
	$HTML .= '</div>';
	$HTML .= '</div>'; // End Category List Tab

	return ($HTML);
}

function HOAM_violationShowExistingSeverityList () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting, $_HOAM_queue_head;
	
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');
	
	HOAM_queueInsertAfter ($_HOAM_queue_head, 'headScripts', 'HOAM_adminViolationSeverityJS');
	$HTML  = '<div class="tabbertab" id="category_list" title="' . $_HOAM_language ['form_tabs']['violation']['severity']['list'] . '">';
	$HTML .= '<div class="optional">';
	$HTML .= '<label for="violation|severity|show|name">' . $_HOAM_language ['field_names']['violation']['severity']['name'] . '</label>';
	$HTML .= '<select id="violation|severity|show|name">';
	$severity_list = $violation_severity_class -> listSeverity ();
	if (is_array ($severity_list)) {
		foreach ($severity_list as $severity) {
			$HTML .= '<option value="' . $severity . '">' . $violation_severity_class -> returnSeverity ($severity) . '</option>';
		}
	}
	$HTML .= '</select>';
	$HTML .= '<label for="violation|severity|show|numdays">' . $_HOAM_language ['field_names']['violation']['severity']['numdays'] . '</label>';
	$HTML .= '<input type="text" readonly="readonly" id="violation|severity|show|numdays" size="' . $violation_severity_class -> returnColumnSize ('numdays') . '" />';
	$HTML .= '<label for="violation|severity|show|fine_per_notice">' . $_HOAM_language ['field_names']['violation']['severity']['fine_per_notice'] . '</label>';
	$HTML .= '<input type="text" readonly="readonly" id="violation|severity|show|fine_per_notice" size="' . $violation_severity_class -> returnColumnSize ('fine_per_notice') . '" />';
	$HTML .= '<label for="violation|severity|show|fine_per_day">' . $_HOAM_language ['field_names']['violation']['severity']['fine_per_day'] . '</label>';
	$HTML .= '<input type="text" readonly="readonly" id="violation|severity|show|fine_per_day" size="' . $violation_severity_class -> returnColumnSize ('fine_per_day') . '" />';
	$HTML .= '<label for="violation|severity|show|fine_interest">' . $_HOAM_language ['field_names']['violation']['severity']['fine_interest'] . '</label>';
	$HTML .= '<input type="text" readonly="readonly" id="violation|severity|show|fine_interest" size="' . $violation_severity_class -> returnColumnSize ('fine_interest') . '" />';
	$HTML .= '<label for="violation|severity|show|escalate">' . $_HOAM_language ['field_names']['violation']['severity']['escalate'] . '</label>';
	$HTML .= '<input type="text" readonly="readonly" id="violation|severity|show|escalate" size="' . $violation_severity_class -> returnColumnSize ('severity') . '" />';
	$HTML .= '<label for="violation|severity|show|preamble">' . $_HOAM_language ['field_names']['violation']['severity']['preamble'] . '</label>';
	$HTML .= '<textarea readonly="readonly" rows="' . $_SESSION['current_user']['edit']['row'] . '" id="violation|severity|show|preamble"></textarea>';
	$HTML .= '<label for="violation|severity|show|closing">' . $_HOAM_language ['field_names']['violation']['severity']['closing'] . '</label>';
	$HTML .= '<textarea readonly="readonly" rows="' . $_SESSION['current_user']['edit']['row'] . '" id="violation|severity|show|closing"></textarea>';
	$HTML .= '</div>';
	$HTML .= '</div>'; // End Category List Tab

	return ($HTML);
}

function HOAM_violationShowUnresolved () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$homeowner_class = new HOAM_homeowner ('homeowners');
    $violation_class = new HOAM_violation ('violations');
    $violation_severity_class = new HOAM_violation_severity ('violation_severity');
    
	$colgroup = '<colgroup>';
	$colgroup .= '<col width="10%" />';
	if ($_HOAM_setting ['violation']['require_approval']) {
		$colgroup .= '<col width="10%" />';
		$colgroup .= '<col width="10%" />';
		$colgroup .= '<col width="25%" />';
		$colgroup .= '<col width="25%" />';
	} else {
		$colgroup .= '<col width="10%" />';
		$colgroup .= '<col width="30%" />';
		$colgroup .= '<col width="30%" />';
	}
	$colgroup .= '<col width="10%" />';
	$colgroup .= '<col width="10%" />';
	$colgroup .= '</colgroup>';
	$thead = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['date']['violaton'] . '</th>';
	if ($_HOAM_setting ['violation']['require_approval']) {
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['approval'] . '</th>';
	}
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['date']['resolveby'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['address'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['homeowner'] . '</th>';
	$thead .= '<th class="donotprint">' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['violation']['severity'] . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$tbody = '<tbody class="lot_list">';
	$violation_list = $violation_class -> listOpen ();
	if (is_array ($violation_list)) {
		foreach ($violation_list as $violation) {
			$tbody .= '<tr>';
			$owner = $homeowner_class -> returnOwner ($violation_class -> returnViolationDate ($violation), $violation_class -> returnLotId ($violation));
			$tbody .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($violation_class -> returnViolationDate ($violation))) . '</td>';
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
			$resolveby = $violation_class -> returnResolveByDate ($violation);
			if (date ($_HOAM_country ['date']['format_mysql']) < $resolveby) {
				if (date ($_HOAM_country ['date']['format_mysql']) == $resolveby) { 
					$tbody .= '<td style="color: red">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($resolveby)) . '</td>';
				} else {
					$tbody .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($resolveby)) . '</td>';
				}
			} else {
				$tbody .= '<td class="error">' . date ($_HOAM_country ['date']['format_reports'], strtotime ($resolveby)) . '</td>';
			}
			$tbody .= '<td>' . HOAM_returnAddress ($violation_class -> returnLotId ($violation)) . '</td>';
			$tbody .= '<td>' . $homeowner_class -> returnName ($owner) . '</td>';
			$tbody .= '<td class="donotprint"><a href="/admin/violation/letter/' . $violation . '/">Letter</a></td>';
			$tbody .= '<td>' . $violation_severity_class -> returnSeverity ($violation_class -> returnSeverity ($violation)) . '</td>';
			$tbody .= '</tr>';
		}
	} else {
		$tbody .= '<tr><td colspan="4">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
	}
	$tbody .= '</tbody>';
	$HTML = '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>'. $tbody . '<tbody></table>';
	return ($HTML);
}

function HOAM_violationTablePreload () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	$HTML = '<h3 style="display: block; text-align: center; text-decoration: underline;">' . $_HOAM_language ['field_names']['violation']['homeowner']['current'] . '</h3>';
	$colgroup  = '<colgroup span="4">';
	$colgroup .= '<col width="15%" />';
	$colgroup .= '<col width="10%" />';
	$colgroup .= '<col />';
	$colgroup .= '<col width="10%" />';
	$colgroup .= '<col width="15%" />';
	$colgroup .= '</colgroup>';
	$thead  = '<thead><tr>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['date']['violation'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['severity']['header'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['category']['name'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['date']['resolveby'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['field_names']['violation']['date']['resolution'] . '</th>';
	$thead .= '</tr></thead>';
	$tbody  = '<tbody id="violation|history|homeowner">';
	$tbody .= '<tr><td class="center" colspan="5">' . $_HOAM_language ['errors']['violation']['history_none'] . '</td></tr>';
	$tbody .= '</tbody>';

	$HTML .= '<table>' . $colgroup . $thead . $tbody . '</table>';

	$HTML .= '<h3 style="display: block; text-align: center; text-decoration: underline;">' . $_HOAM_language ['field_names']['violation']['homeowner']['previous'] . '</h3>';
	$tbody  = '<tbody id="violation|history|previous">';
	$tbody .= '<tr><td class="center" colspan="5">' . $_HOAM_language ['errors']['violation']['history_none'] . '</td></tr>';
	$tbody .= '</tbody>';

	$HTML .= '<table>' . $colgroup . $thead . $tbody . '</table>';
	return ($HTML);
}

?>