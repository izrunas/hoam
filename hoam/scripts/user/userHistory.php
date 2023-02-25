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

if (isset ($_GET['id'])) {
	echo '<div class="optional">';
	if (isset ($_SESSION['current_user']['id']) AND
		($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
		$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_edit'], $_SESSION['current_user']['id']) OR
		$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['user_delete'], $_SESSION['current_user']['id']) OR
		($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
		
		$user_class = new HOAM_user ('users');
		
		$trusted_id = $user_class -> superClean ($_GET['id'], $user_class -> returnColumnSize ('id'));
		$history_list = $_HOAM_log -> listOwnerBit ($trusted_id, (int) $_HOAM_setting ['log']['flags']['user']);
	} else {
		$history_list = $_HOAM_log -> listOwnerBit ($_SESSION['current_user']['id'], (int) $_HOAM_setting ['log']['flags']['user']);
	}
	if (!empty ($history_list)) {
		$tbody = '';
		foreach ($history_list as $history_item) {
			$event_details = $_HOAM_log -> listAll ($history_item);
			$event_details = $event_details[0];
			$tablerow = '<tr>';
			$tablerow .= '<td class="center">' . date ($_HOAM_country ['date']['format_short'] . ' ' . $_HOAM_country ['time']['format_long'], strtotime ($event_details['datecreated'])) . "</td>";
			$tablerow .= '<td class="center">' . stripslashes (nl2br (str_replace ('\r\n', "\r\n", $event_details['message']))) . '</td>';
			$tablerow .= '<td class="center">' . $user_class -> returnAccountName ($event_details['user_id']) . "</td>";
			$tablerow .= '</tr>';
			$tbody .= $tablerow;
		}
	} else {
		$tbody = '<tr><td class="center" colspan="3">' . $_HOAM_language ['errors']['log']['none'] . '</td></tr>';
	}
	$thead = sprintf ($_HOAM_language ['log']['generic']['table']['thead'], ucfirst ($_HOAM_language ['common']['date']), ucfirst ($_HOAM_language ['common']['description']), ucfirst ($_HOAM_language ['common']['user']));
	$tbody = sprintf ($_HOAM_language ['log']['generic']['table']['tbody'], $tbody);
	printf ($_HOAM_language ['log']['generic']['table']['default'], $_HOAM_language ['log']['generic']['table']['colgroup'], $thead, $tbody);
	echo '</div>';
}

exit ();

?>