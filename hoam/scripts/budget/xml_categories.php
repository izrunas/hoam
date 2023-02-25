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

// There's no reason to secure this information, there's nothing sensitive.

$budget_category_class = new HOAM_budget_category ('budget_category');
if (isset ($_GET['id'])) {
	// Just get info for the specified category
	$trusted_id = $budget_category_class -> superClean ($_GET['id'], $budget_category_class -> returnColumnSize ('id'));
	if ($budget_category_class -> idExists ($trusted_id)) {
		$category_list = array (array ($trusted_id, 0));
	} else {
		header ('Location: /error/unknown_id/');		
	}
} else {
	$category_list = $budget_category_class -> listCategoryAll ();
}
if (is_array ($category_list)) {
	$xw = xmlwriter_open_memory ();
	xmlwriter_set_indent ($xw, 1);
	xmlwriter_set_indent_string ($xw, ' ');
	xmlwriter_start_document ($xw, '1.0', 'UTF-8');
	// A first element
	xmlwriter_start_element ($xw, 'ajax-response');

	foreach ($category_list as $category) {
		xmlwriter_start_element ($xw, 'item');
		// It's pretty likely that the various category descriptions will contain HTML, so we'll just enclose them all in CDATA tags
		xmlwriter_write_element ($xw, 'id', $category[0]);
		
		xmlwriter_start_element ($xw, 'name');
		xmlwriter_write_cdata ($xw, $budget_category_class -> returnName ($category[0]));
		xmlwriter_end_element ($xw); // name
		xmlwriter_write_element ($xw, 'child', $category[1]);
		$flags = $budget_category_class -> returnFlags ($category[0]);
		if ((int) $flags & (int) $_HOAM_setting ['budget']['flags']['apply_interest']) {
			$interest = 1;
		} else {
			$interest = 0;
		}
		if ((int) $flags & (int) $_HOAM_setting ['budget']['flags']['apply_sales_tax']) {
			$sales_tax = 1;
		} else {
			$sales_tax = 0;
		}
		xmlwriter_write_element ($xw, 'apply_interest', $interest);
		xmlwriter_write_element ($xw, 'apply_sales_tax', $sales_tax);
		xmlwriter_end_element ($xw); // item
	}
	// Generate the XML file and echo it back to the client
	xmlwriter_end_element ($xw); // ajax-response
	xmlwriter_end_document ($xw);
	header ('Content-type: text/xml');
	echo xmlwriter_output_memory ($xw);
	exit ();
}

// Punt. There should alwayss be categories in the DB, so this is a safe
// assumption.
header ('Location: /error/unknown_action/');
exit ();

?>