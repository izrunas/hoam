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

if (isset ($_GET['category'])) {
	$violation_category_class = new HOAM_violation_category ('violation_category');
	$violation_severity_class = new HOAM_violation_severity ('violation_severity');
	
	// Just get info for the specified category
	$category_list = explode (',', $_GET['category']);
	$trusted_category_list = array ();
	foreach ($category_list as $category) {
		$clean_category = $violation_category_class -> superClean ($category, $violation_category_class -> returnColumnSize ('category'));
		if ($violation_category_class -> idExists ($clean_category)) {
			array_push ($trusted_category_list, $clean_category);
		}
	}

	$xw = xmlwriter_open_memory ();
	xmlwriter_set_indent ($xw, 1);
	xmlwriter_set_indent_string ($xw, ' ');
	xmlwriter_start_document ($xw, '1.0', 'UTF-8');
	// A first element
	xmlwriter_start_element ($xw, 'ajax-response');

	if (is_array ($trusted_category_list)) {
		foreach ($trusted_category_list as $category) {
			xmlwriter_start_element ($xw, 'item');
			xmlwriter_write_element ($xw, 'id', $category);
			// It's pretty likely that the various category descriptions will contain HTML, so we'll just enclose them all in CDATA tags
			xmlwriter_start_element ($xw, 'category');
			xmlwriter_write_cdata ($xw, $violation_category_class -> returnCategory ($category));
			xmlwriter_end_element ($xw); // category
			xmlwriter_start_element ($xw, 'description');
			xmlwriter_write_cdata ($xw, stripslashes (str_replace ('\r\n', "\r\n", $violation_category_class -> returnDescription ($category))));
			xmlwriter_end_element ($xw); // description
			xmlwriter_start_element ($xw, 'detail');
			xmlwriter_write_cdata ($xw, stripslashes (str_replace ('\r\n', "\r\n", $violation_category_class -> returnDetail ($category))));
			xmlwriter_end_element ($xw); // detail
			xmlwriter_start_element ($xw, 'parentcategory');
			xmlwriter_write_cdata ($xw, $violation_category_class -> returnCategory ($violation_category_class -> returnParentCategory ($category)));
			xmlwriter_end_element ($xw); // parentcategory
			xmlwriter_start_element ($xw, 'parentcategory_id');
			xmlwriter_write_cdata ($xw, $violation_category_class -> returnParentCategory ($category));
			xmlwriter_end_element ($xw); // parentcategory_id
			xmlwriter_start_element ($xw, 'initialseverity');
			xmlwriter_write_cdata ($xw, $violation_severity_class -> returnSeverity ($violation_category_class -> returnInitialSeverity ($category)));
			xmlwriter_end_element ($xw); // initialseverity
			xmlwriter_start_element ($xw, 'initialseverity_id');
			xmlwriter_write_cdata ($xw, $violation_category_class -> returnInitialSeverity ($category));
			xmlwriter_end_element ($xw); // initialseverity_id
			xmlwriter_end_element ($xw); // item
		}
	}
	// Generate the XML file and echo it back to the client
	xmlwriter_end_element ($xw); // ajax-response
	xmlwriter_end_document ($xw);
	header ('Content-type: text/xml');
	echo xmlwriter_output_memory ($xw);
} else {
	// We don't know what action we're being asked to perform
	header ('Location: /error/unknown_action/');
}

exit ();

?>