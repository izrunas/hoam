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

$budget_budget_class = new HOAM_budget_budget ('budget_budget');
$group_members_class = new HOAM_group_members ('group_members');

$trusted_budget_id = $budget_budget_class -> superClean ($_GET['id'], $budget_budget_class -> returnColumnSize ('id'));

if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_approve'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_edit'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) OR
	// Is the budget public?
	($budget_budget_class -> returnFlags ($trusted_budget_id) & $_HOAM_setting['budget']['flags']['public'])) {
	if (isset ($_GET['id']) AND isset ($_GET['start']) AND isset ($_GET['end'])) {
		
		require_once (HOAM_PATH_FUNCTION . 'budget.php');
		
		$trusted_date_start = $budget_budget_class -> superClean ($_GET['start'], $budget_budget_class -> returnColumnSize ('datestart'));
		$trusted_date_end = $budget_budget_class -> superClean ($_GET['end'], $budget_budget_class -> returnColumnSize ('dateend'));

		$xw = xmlwriter_open_memory ();
		xmlwriter_set_indent ($xw, 1);
		xmlwriter_set_indent_string ($xw, ' ');
		xmlwriter_start_document ($xw, '1.0', 'UTF-8');
		// A first element
		xmlwriter_start_element ($xw, 'ajax-response');
		
		xmlwriter_start_element ($xw, 'data');
		xmlwriter_write_cdata ($xw, HOAM_budgetView ($trusted_budget_id, $trusted_date_start, $trusted_date_end));
		xmlwriter_end_element ($xw); // data
		
		// Generate the XML file and echo it back to the client
		xmlwriter_end_element ($xw); // ajax-response
		xmlwriter_end_document ($xw);
		header ('Content-type: text/xml');
		echo xmlwriter_output_memory ($xw);
	} else {
		// Do not call the page directly
		header ('Location: /error/unknown_action/');
	}

} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
}

exit ();

?>
