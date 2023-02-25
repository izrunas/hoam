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

 function HOAM_insuranceList ($actions = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;

	$attachment_class = new HOAM_attachments ('attachments');
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$group_members_class = new HOAM_group_members ('group_members');
	$insurance_class = new HOAM_insurance ('insurance');
	$lot_class = new HOAM_lot ('lots');

	$HTML = '<table class="center">';
	$colgroup = '<colgroup>';
	if ($actions) {
		$colgroup .= '<col width="15%" />';
		$colgroup .= '<col width="15%" />';
		$colgroup .= '<col width="15%" />';
		$colgroup .= '<col width="10%" />';
		$colgroup .= '<col width="10%" />';
		$colgroup .= '<col width="15%" />';
		$colgroup .= '<col width="10%" />';
		$colgroup .= '<col width="5%" />';
		$colgroup .= '<col width="5%" />';
	} else {
		$colgroup .= '<col width="15%" />';
		$colgroup .= '<col width="15%" />';
		$colgroup .= '<col width="15%" />';
		$colgroup .= '<col width="10%" />';
		$colgroup .= '<col width="10%" />';
		$colgroup .= '<col width="20%" />';
		$colgroup .= '<col width="5%" />';
	}
	$colgroup .= '</colgroup>';
	$thead = '<thead style="background: silver"><tr style="text-align: center">';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['insurance']['policy']['type'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['tables']['column']['budget']['vendor']['name'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['tables']['column']['lot']['address'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['insurance']['policy']['number'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['insurance']['expiration-date'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['insurance']['renewal-date'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['tables']['column']['attachment']['attachments'] . '</th>';
	if ($actions) {
		$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
	}
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['generic']['flags'] . '</th>';
	$thead .= '</tr></thead>';
	$tbody = '<tbody id="policy_list">';
	$policy_list = $insurance_class -> listCurrentId ();
	if (is_array ($policy_list)) {
		foreach ($policy_list as $policy) {
			$policy_info = $insurance_class -> listAll ($policy);
			$policy_info = $policy_info [0];
			$tbody .=  '<tr>';
			$tbody .= '<td>' . $_HOAM_language ['field_names']['insurance']['policy'][$policy_info ['policy_type']] . '</td>';
			$tbody .= '<td>' . html_encode (stripslashes ($budget_vendor_class -> returnName ($policy_info ['vendor_id']))) . '</td>';
			switch ($policy_info['lot_id']) {
				case 'common_area':
					$tbody .= '<td>' . $_HOAM_language ['field_names']['lot']['common']['area'] . '</td>';
					break;
				case 'common_clubhouse':
					$tbody .= '<td>' . $_HOAM_language ['field_names']['lot']['common']['clubhouse'] . '</td>';
					break;
				case 'common_pool':
					$tbody .= '<td>' . $_HOAM_language ['field_names']['lot']['common']['pool'] . '</td>';
					break;
				default:
					$tbody .= '<td>' . HOAM_returnAddress ($policy_info['lot_id']) . '</td>';
					break;
			}
			$tbody .= '<td>' . $policy_info ['policy_num'] . '</td>';
			$tbody .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($policy_info ['dateexpiration'])) . '</td>';
			$tbody .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($policy_info ['daterenewal'])) . '</td>';
			$tbody .= '<td>';
			$attachment_list = $attachment_class -> listWithOwner ($policy_info['id'], $_HOAM_setting ['attachment']['flags']['insurance']);
			foreach ($attachment_list as $attachment) {
				$tbody .= HOAM_attachmentReturnHTML ($attachment);
			}
			$tbody .= '</td>';
			if ($actions) {
				$tbody .= '<td>';
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_edit'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					$tbody .= '<small><a href="/admin/financial/insurance/edit/' . $policy_info['id'] . '/">' . $_HOAM_language ['action']['generic']['edit'] . '</a></small>';
				}
				$tbody .= ' &nbsp; ';
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['insurance_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					$tbody .= '<small><a class="error" href="/admin/financial/insurance/delete/' . $policy_info['id'] . '/">' . $_HOAM_language ['action']['generic']['delete'] . '</a></small>';
				}
				$tbody .= '</td>';
			}
			$tbody .= '<td>';
			switch (TRUE) {
				case ((int) $policy_info ['flags'] & (int) $_HOAM_setting ['budget']['insurance']['flags']['replaced']) :
					$tbody .= '<span class="cursor_help" title="' . strip_tags ($_HOAM_language ['field_names']['config']['budget']['insurance']['flags']['replaced']) . '">R</span>';
					break;
				default :
					$tbody .= '&nbsp;';
			}
			$tbody .= '</td>';
			$tbody .= '</tr>';
		}
	} else {
		$tbody .= '<tr><td colspan="9">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
	}
	$tbody .= '</tbody>';
	$HTML = '<table class="center">' . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . $tbody . '</table>';
	return ($HTML);
}

function HOAM_insuranceExpiring () {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;
	
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$insurance_class = new HOAM_insurance ('insurance');

	$thead = '<thead style="background: silver"><tr style="font-weight: bold">';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['insurance']['policy']['type'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['tables']['column']['budget']['vendor']['name'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['lot']['address'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['insurance']['policy']['number'] . '</th>';
	$thead .= '<th style="border-bottom: .1em solid black; border-top: .1em solid black; text-align: center">' . $_HOAM_language ['field_names']['insurance']['expiration-date'] . '</th>';
	$thead .= '</tr></thead>';

	$HTML = '<table style="text-align: center; width: 100%">';
	$HTML .= $thead . preg_replace ('/thead/', 'tfoot', $thead);
	$HTML .= '<tbody>';

	$days_expiring = new DateTime ();
	$days_expiring -> modify ('+' . $_HOAM_setting ['budget']['insurance']['days'] . ' day');
	$policy_list = $insurance_class -> listExpiringId ($days_expiring -> format ($_HOAM_country ['date']['format_mysql']));
	if (is_array ($policy_list)) {
		foreach ($policy_list as $policy) {
			$HTML .= '<tr style="color: red">';
			$HTML .= '<td>' . $_HOAM_language ['field_names']['insurance']['policy'][$insurance_class -> returnPolicyType ($policy)] . '</td>';
			$HTML .= '<td>' . $budget_vendor_class -> returnName ($insurance_class -> returnVendorId ($policy)) . '</td>';
			$HTML .= '<td>';
			switch ($insurance_class -> returnLotId ($policy)) {
				case 'common_area':
					$HTML .= $_HOAM_language ['field_names']['lot']['common']['area'];
					break;
				case 'common_clubhouse':
					$HTML .= $_HOAM_language ['field_names']['lot']['common']['clubhouse'];
					break;
				case 'common_pool':
					$HTML .= $_HOAM_language ['field_names']['lot']['common']['pool'];
					break;
				default:
					$HTML .= HOAM_returnAddress ($insurance_class -> returnLotId ($policy));
			}
			$HTML .= '</td>';
			$HTML .= '<td>' . $insurance_class -> returnPolicyNum ($policy) . '</td>';
			$HTML .= '<td>' . HOAM_date ($_HOAM_country ['date']['format_reports'], strtotime ($insurance_class -> returnExpirationDate ($policy))) . '</td>';
			$HTML .= '</tr>';
		}
	} else {
		$tbody .= '<tr><td colspan="5">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
	}
	$HTML .= '</tbody></table>';

	return ($HTML);
}
