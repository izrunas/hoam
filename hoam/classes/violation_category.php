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

class HOAM_violation_category extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!

	function changeCategory ($id, $value) {
		return ($this -> changeString ($id, 'category', $value));
	}

	function changeDetail ($id, $value) {
		return ($this -> changeString ($id, 'detail', $value));
	}

	function changeDescription ($id, $item) {
		return ($this -> changeString ($id, 'description', $item));
	}
	
	function changeInitialSeverity ($id, $value) {
		return ($this -> changeString ($id, 'initialseverity', $value));
	}

	function changeParentCategory ($id, $value) {
		return ($this -> changeString ($id, 'parent_category', $value));
	}

	function ListCategories ($number = 0, $offset = 0) {
		$parent_list = $this -> listParentCategories ();
		$category_list = array ();
		foreach ($parent_list as $parent) {
			array_push ($category_list, $parent);
			$child_list = $this -> listChildCategories ($parent);
			if (is_array ($child_list)) {
				foreach ($child_list as $child) {
					array_push ($category_list, array (NULL, $child));
				}
			}
		}
		return ($category_list);
	}

	function listCategoryAll () {
		// return an array containing the id of all categories, grouped by parent/child
		$complete_list = array ();
		$parent_categories = $this -> listParentCategories ();
		foreach ($parent_categories as $parent) {
			// If this is a parent (or just doesn't have any children)
			// append a 0 to the array
			array_push ($complete_list, array ($parent, 0));
			$query_children = "SELECT id FROM $this->table
								WHERE parent_category = '$parent' ORDER BY category ASC";
			$child_categories = $this -> listSimpleQuery ($query_children);
			if (is_array ($child_categories)) {
				foreach ($child_categories as $child) {
					// If this is a child, append a 1 to the array
					// This is used to later when building the category select list
					// among other things
					array_push ($complete_list, array ($child, 1));
				}
			}
		}
		return ($complete_list);
	}
	
	function ListChildCategories ($parent, $number = 0, $offset = 0) {
		// Return an array containing a list of all categories organized by
		// parent.
		$query = "SELECT id FROM $this->table
					WHERE parent_category = '$parent'
					ORDER BY category ASC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function ListParentCategories ($number = 0, $offset = 0) {
		// Return an array containing a list of all parent categories
		$query = "SELECT id FROM $this->table
					WHERE parent_category is NULL
					ORDER BY category ASC";
		if ($number > 0) {
			$query .= " LIMIT $offset, $number";
		}
		return ($this -> listSimpleQuery ($query));
	}

	function returnCountCategory ($item) {
		return ($this -> returnCountQuery ("WHERE category = '$item'"));
	}

	function returnCountUserId ($item) {
		return ($this -> returnCountQuery ("WHERE user_id = '$item'"));
	}
	
	function returnCountRange ($newest, $oldest) {
		return ($this -> returnCountQuery ("WHERE (datecreated BETWEEN '$newest' AND '$oldest')"));
	}
	
	function returnCategory ($id) {
		return ($this -> returnItem ($id, 'category'));
	}

	function returnDetail ($id) {
		return ($this -> returnItem ($id, 'detail'));
	}

	function returnDescription ($id) {
		return ($this -> returnItem ($id, 'description'));
	}
	
	function returnInitialSeverity ($id) {
		return ($this -> returnItem ($id, 'initialseverity'));
	}

	function returnParentCategory ($id) {
		return ($this -> returnItem ($id, 'parent_category'));
	}
}

?>
