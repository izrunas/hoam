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

if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['budget_view'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	$budget_class = new HOAM_budget ('budget');
	$homeowner_class = new HOAM_homeowner ('homeowners');

	if (isset ($_GET['id'])) {
		$trusted_owner_id = $homeowner_class -> superClean ($_GET['id'], $homeowner_class -> returnColumnSize ('id'));
		if (!$homeowner_class -> idExists ($trusted_owner_id)) {
			header ('Location: /error/unknown_id/');
			exit ();
		}
	} else {
		$trusted_owner_id = NULL;
	}
   	if (isset ($_GET['start'])) {
   		$date_start = date ($_HOAM_country['date']['format_mysql'], strtotime ($budget_class -> superClean ($_GET['start'], $budget_class -> returnColumnSize ('datecreated'))));
   		if (isset ($_GET['end'])) {
    		$date_end = date ($_HOAM_country['date']['format_mysql'], strtotime ($budget_class -> superClean ($_GET['end'], $budget_class -> returnColumnSize ('datecreated'))));
   		} else {
			$date_end = date ($_HOAM_country['date']['format_mysql'], mktime (0, 0, 0, date ('n', strtotime ($date_start)) + 1, date ('d', strtotime ($date_start)), date ('Y', strtotime ($date_start))));		
   		}
   	} else {
		$date_start = date ($_HOAM_country['date']['format_mysql'], mktime (0, 0, 0, date ('n'), 1, date ('Y')));
		$date_end = date ($_HOAM_country['date']['format_mysql'], mktime (0, 0, 0, date ('n'), date ('t'), date ('Y')));
	}

	if ($trusted_owner_id === NULL) {
		$query = "SELECT id FROM budget WHERE customer_id!='" . $_HOAM_setting ['budget']['ids']['organization'] . "' AND datecreated>='$date_start' AND datecreated <= '$date_end' AND flags & " . ($_HOAM_setting ['budget']['flags']['credit'] + $_HOAM_setting ['budget']['flags']['payment']);
	} else {
		$query = "SELECT id FROM budget WHERE customer_id!='" . $_HOAM_setting ['budget']['ids']['organization'] . "' AND customer_id='$trusted_owner_id' AND datecreated >= '$date_start' AND datecreated <= '$date_end' AND flags & " . ($_HOAM_setting ['budget']['flags']['credit'] + $_HOAM_setting ['budget']['flags']['payment']);			
	}
//	echo $query; exit;
	$payment_list = $budget_class -> listSimpleQuery ($query);
	$total_amount = 0;
	$total_count = 0;
	$tbody = '';
	if (is_array ($payment_list)) {
		foreach ($payment_list as $payment) {
			$tbody .= '<tr>';
			$tbody .= '<td>' . HOAM_returnAddress ($homeowner_class -> returnLotId ($budget_class -> returnCustomerId ($payment))) . '</td>';
			$tbody .= '<td>' . html_encode (stripslashes ($homeowner_class -> returnName ($budget_class -> returnCustomerId ($payment)))) . '</td>';
			$tbody .= '<td>' . html_encode (stripslashes ($budget_class -> returnMemo ($payment))) . '</td>';
			$tbody .= '<td>' . date ($_HOAM_country ['date']['format_reports'], strtotime ($budget_class -> returnDateCreated ($payment))) . '</td>';
			$total_amount += -$budget_class -> returnAmount ($payment);
			$total_count++;
			$tbody .= '<td>' . HOAM_returnCurrency (-$budget_class -> returnAmount ($payment)) . '</td>';
			$tbody .= '<td>' . $budget_class -> returnCheckNumber ($payment) . '</td>';
			$tbody .= '</tr>';
		}
	}
				
	$xw = xmlwriter_open_memory ();
	xmlwriter_set_indent ($xw, 1);
	xmlwriter_set_indent_string ($xw, ' ');
	xmlwriter_start_document ($xw, '1.0', 'UTF-8');
	// A first element
	xmlwriter_start_element ($xw, 'ajax-response');

	xmlwriter_start_element ($xw, 'data');
	xmlwriter_write_cdata ($xw, $tbody);
	xmlwriter_end_element ($xw); // data
	xmlwriter_start_element ($xw, 'total_amount');
	xmlwriter_write_cdata ($xw, HOAM_returnCurrency ($total_amount));
	xmlwriter_end_element ($xw); // total_amount
	xmlwriter_start_element ($xw, 'total_count');
	xmlwriter_write_cdata ($xw, $total_count);
	xmlwriter_end_element ($xw); // total_count
	// Generate the XML file and echo it back to the client
	xmlwriter_end_element ($xw); // ajax-response
	xmlwriter_end_document ($xw);
	header ('Content-type: text/xml');
	echo xmlwriter_output_memory ($xw);
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
}

exit ();

?>
