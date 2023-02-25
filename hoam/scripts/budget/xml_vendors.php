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

if (isset ($_GET['id'])) {
	$budget_vendor_class = new HOAM_budget_vendor ('budget_vendor');
	// Just get info for the specified vendor
	$trusted_id = $budget_vendor_class -> superClean ($_GET['id'], $budget_vendor_class -> returnColumnSize ('id'));
	
	if ($budget_vendor_class -> idExists ($trusted_id)) {
		$flags = $budget_vendor_class -> returnFlags ($trusted_id);
		$xw = xmlwriter_open_memory ();
		xmlwriter_set_indent ($xw, 1);
		xmlwriter_set_indent_string ($xw, ' ');
		xmlwriter_start_document ($xw, '1.0', 'UTF-8');
		// A first element
		xmlwriter_start_element ($xw, 'ajax-response');
		xmlwriter_start_element ($xw, 'item');

		xmlwriter_write_element ($xw, 'id', $trusted_id);
		xmlwriter_write_element ($xw, 'default_category', $budget_vendor_class -> returnCategoryId ($trusted_id));
		xmlwriter_write_element ($xw, 'terms', '');

		// Generate the XML file and echo it back to the client
		xmlwriter_end_element ($xw); // item
		xmlwriter_end_element ($xw); // ajax-response
		xmlwriter_end_document ($xw);
		header ('Content-type: text/xml');
		echo xmlwriter_output_memory ($xw);
	} else {
		header ('Location: /error/unknown_id/');		
	}
} else {
	// We don't know what action we're being asked to perform
	header ('Location: /error/unknown_action/');
}

exit ();

?>