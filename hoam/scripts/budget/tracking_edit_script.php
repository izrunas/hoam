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

// Is the current user allowed to add / edit violation categories?
if (isset ($_SESSION ['current_user']['id']) and
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION ['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION ['current_user']['id']) OR
	($_HOAM_setting ['users']['ids']['root'] === $_SESSION ['current_user'] ['id']))) {
	if (isset ($_POST ['cancel']) OR isset ($_POST ['modify'])) {
		$budget_class = new HOAM_budget_budget ('budget_budget');
		$budget_tracking_class = new HOAM_budget_track ('budget_track');

		// If this is the second (or greater) time through the script (ie, there
		// were errors that had to be fixed before committing the changes) get
		// rid of the old cached information the user entered, we're going to
		// populate it with information just received from the form posting.
		if (isset ($_SESSION ['budget_information'])) {
			unset ($_SESSION ['budget_information']);
		}
		
		if (isset ($_POST ['cancel'])) {
			// Just send them back to the admin page
			header ('Location: /admin/financial/budget/');
		} else {
			// If we're still here, then we must be adding or modifying an existing budget.
			
			foreach ($_POST as $key => $value) {
				// Yeah, we'll end up wasting time looking for a entry named
				// 'modify', but it shouldn't cause an issue. 
				$trusted_entry_id = preg_replace ('/budget\|entry\|/', '', $key);
				$trusted_entry_id = (string) $budget_class -> superClean ($trusted_entry_id, $budget_tracking_class -> returnColumnSize ('category_id'));
				if ($budget_tracking_class -> idExists ($trusted_entry_id)) {
					$trusted_amount = (float) $budget_class -> superClean ($value, $budget_tracking_class -> returnColumnSize ('amount'));
					$budget_tracking_class -> changeAmount ($trusted_entry_id, $trusted_amount);
				}
			}
// print_r ($_POST); exit();
			
			// OK, all done. Get rid of our data and finish up.
			header ('Location: /admin/financial/budget/entry/');
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
