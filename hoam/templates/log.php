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

function HOAM_logSinceLastLogin ($h1 = FALSE) {
	global $_HOAM_language, $_HOAM_queue_head, $_HOAM_setting;
	
	$group_members_class = new HOAM_group_members ('group_members');
	
	if (isset ($_SESSION['current_user']['id'])) {
		switch ($GLOBALS['global_url_array'][2]) {
			default:
			if (empty ($GLOBALS['global_url_array'][2])) {
				if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['board'], $_SESSION['current_user']['id']) OR
					$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['officer'], $_SESSION['current_user']['id']) OR
					($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
					HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $_HOAM_language ['field_names']['log']['since-last'])));
					$h1 ? $HTML = '<h1>' . $_HOAM_language ['field_names']['log']['since-last'] . '</h1>' : $HTML = '<img src="/hoam/images/icons/date_magnify.png" alt="' . $_HOAM_language ['field_names']['log']['since-last'] . '" class="LHS" /><a href="/log/since-last/">' . $_HOAM_language ['field_names']['log']['since-last'] . '</a>';
				}
			} else {
				header ('Location: /error/404/');
				exit ();
			}
			break;
		}
		return ($HTML);
	}
	header ('Location: /error/logged_in/');
	exit ();
}

switch ($GLOBALS['global_url_array'][1]) {
	case ('since-last') :
		echo HOAM_userSinceLastLogin (TRUE);
		break;
	default:
		if (empty ($GLOBALS['global_url_array'][1])) {
			HOAM_queueInsertValue ($_HOAM_queue_head, 'headTitle', array ('params' => array ('title' => $GLOBALS['_HOAM_language'] ['field_names']['admin']['user'])));
			echo '<h1>' . $GLOBALS['_HOAM_language'] ['field_names']['admin']['user'] . '</h1>';
			echo '<ul class="none">';
			if (isset ($_SESSION['current_user']['id'])) {
				echo '<li>' . HOAM_userSinceLastLogin () . '</li>';
			}
			echo '</ul>';
		} else {
			header ('Location: /error/404/');
			exit ();
		}
		break;
}

?>
