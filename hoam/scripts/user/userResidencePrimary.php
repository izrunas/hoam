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

if (isset ($_SESSION['current_user']['id'])) {
	$group_members_class = new HOAM_group_members ('group_members');
	$homeowner_class = new HOAM_homeowner ('homeowners');
	$user_class = new HOAM_user ('users');

	$homeowner_id = $user_class -> returnHomeownerId ($_SESSION['current_user']['id']);
	if ($homeowner_id AND
		$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['homeowner'], $_SESSION['current_user']['id']) AND
		($homeowner_id == $homeowner_class -> returnCurrentOwner ($homeowner_class -> returnLotId ($homeowner_id)))) {
		if (isset ($_POST['residence_primary'])) {
			$trusted_primary = $user_class -> superClean ($_POST['residence_primary'], $user_class -> returnColumnSize ('id'));
			if ($user_class -> idExists ($trusted_primary)) {
				// Make sure none of the other accounts at this residence are
				// primary
				$associated_accounts = $user_class -> listIDGivenField ('homeowner_id', $homeowner_id);
				foreach ($associated_accounts as $account) {
					$user_class -> clearBit ($account, $_HOAM_setting ['user']['flags']['residence_primary']);
				}
				// Set the new primary account
				$user_class -> setBit ($trusted_primary, $_HOAM_setting ['user']['flags']['residence_primary']);
				// Update the residence history.
				$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['homeowner']['primary'], $user_class -> returnAccountName ($trusted_primary)),
									HOAM_MESSAGE_INFORMATIONAL,
									array ('flags' => $_HOAM_setting ['log']['flags']['homeowner'],
											'owner_id' => $trusted_primary));
				// success
				echo strip_tags (sprintf ($_HOAM_language ['log']['homeowner']['primary'], $user_class -> returnAccountName ($trusted_primary)));
			} else {
				// unknown_id
				echo strip_tags ($_HOAM_language ['help_pages']['website']['unknown_id']);
			}
		} else {
			// unknown_action
			echo strip_tags ($_HOAM_language ['help_pages']['website']['unknown_action']);
		}
	} else {
		// not permitted
		echo strip_tags ($_HOAM_language ['help_pages']['website']['401']);
	}
} else {
	// not permitted
	echo strip_tags ($_HOAM_language ['help_pages']['website']['401']);
}

?>
