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

function HOAM_workListAllRequests ($current = FALSE) {
	global $_HOAM_country, $_HOAM_language, $_HOAM_setting;

	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$lot_class = new HOAM_lot ('lots');
	$user_class = new HOAM_user ('users');
	$work_request_class = new HOAM_work_request ('work_requests');
	$colgroup = '<colgroup>';
	$colgroup .= '</colgroup>';
	$thead = '<thead>';
	$thead .= '<tr>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['generic']['created'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['priority'] . '</th>';
	if ($_HOAM_setting ['work_request']['require_approval']) {
		$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['approval'] . '</th>';
	}
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['status'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['date']['due'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['lot'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['title'] . '</th>';
	$thead .= '<th class="donotprint">' . $_HOAM_language ['tables']['column']['generic']['action'] . '</th>';
	$thead .= '<th>' . $_HOAM_language ['tables']['column']['work_request']['requester'] . '</th>';
	$thead .= '</tr>';
	$thead .= '</thead>';
	$tbody = '<tbody>';
	if ($current) {
		$all_flags = $_HOAM_setting ['work_request']['flags']['new'] +
					$_HOAM_setting ['work_request']['flags']['assigned'] +
					$_HOAM_setting ['work_request']['flags']['planning'] +
					$_HOAM_setting ['work_request']['flags']['in_progress'] +
					$_HOAM_setting ['work_request']['flags']['on_hold'];
		$request_list = $work_request_class -> listDue (0, 0, $all_flags);
		unset ($all_flags);
	} else {
		$request_list = $work_request_class -> listDue ();
	}
	if (is_array ($request_list) AND !empty ($request_list)) {
		$caption = '<caption>' . sprintf ($_HOAM_language ['field_names']['work_request']['found'], count ($request_list)) . '</caption>';
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
			if ($_HOAM_setting ['work_request']['require_approval']) {
				$tbody .= '<td>';
	   			foreach ($_HOAM_language ['approval'] as $approval) {
   					if ((int) $_HOAM_setting ['work_request']['flags'][$approval['value']] == (int) $request_info['flags']) {
   						$tbody .= $approval['name'];
   						break;  
   					}
   				}
				$tbody .= '</td>';
			}
			$tbody .= '<td>';
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
		}
	} else {
		$tbody = '<tr><td colspan="8">' . $_HOAM_language ['errors']['generic']['no-entries-available'] . '</td></tr>';
	}
	$tbody .= '</tbody>';
	$HTML = '<table class="center">' . $caption . $colgroup . $thead . preg_replace ('/thead/', 'tfoot', $thead) . '<tbody>'. $tbody . '<tbody></table>';

	return ($HTML);
}

?>