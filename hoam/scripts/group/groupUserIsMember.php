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

/*
 *
 * This file simply checks to see whether a given user has membership in the
 * specified group.
 *
 */

session_start();

$group_members_class = new HOAM_group_members ('group_members');

// Is the current user allowed to add / edit groups?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['group_edit'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_GET['uid']) AND isset ($_GET['gid'])) {
		$group_class = new HOAM_group ('group_list');
		$user_class = new HOAM_user ('users');
		
		$trusted_user_id = $user_class -> superClean ($_GET['uid'], $user_class -> returnColumnSize ('id'));
		$trusted_group_id = $group_class -> superClean ($_GET['gid'], $group_class -> returnColumnSize ('id'));
		if ($group_members_class -> isMember ($trusted_group_id, $trusted_user_id)) {
			// One, or TRUE, the user is a member.
			echo "1";
		} else {
			// Zero, or FALSE, the user is not a member.
			echo "0";
		}
	} else {
		header ('Location: /error/id_required/');
	}
} else {
	header ('Location: /error/403/');
}

exit ();

?>
