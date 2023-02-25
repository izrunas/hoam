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

class HOAM_budget_track extends HOAM_common {
	// We rely upon the calling code to clean all of the data and format we're given.
	// This class does not perform any cleansing of data!
	
	function changeAmount ($id, $item) {
		return ($this -> changeFloat ($id, 'amount', $item));
	}
	
	function changeBudgetId ($id, $item) {
		return ($this -> changeString ($id, 'budget_id', $item));
	}
	
	function changeCategoryId ($id, $item) {
		return ($this -> changeString ($id, 'category_id', $item));
	}
	
	function ListCategories ($id) {
		// Return an array containing the id of all categories
		global $_HOAM_setting;
		
		// Get a list of the categories assigned to this budget.
		$query = "SELECT category_id FROM $this->table
					WHERE budget_id='$id'";
		$budget_category_list = $this -> listSimpleQuery ($query);
		
		// Now, get a list of all parent categories.
		$budget_category_class = new HOAM_budget_category ('budget_category');
		$parent_list = $budget_category_class -> listParentCategories ();
		
		$new_parent_list = array ();
		foreach ($parent_list as $parent) {
			if (in_array ($parent, $budget_category_list)) {
				array_push ($new_parent_list, $parent);
			}
		}
		$parent_list = $new_parent_list;
		unset ($new_parent_list);
		
		// Now, go through all of the categories assigned to this budget and
		// create a new list using the parent/child list.
		$budget_parent_child_list = array ();
		foreach ($parent_list as $parent) {
			$income = $budget_category_class -> isBitSet ($parent, $_HOAM_setting ['budget']['flags']['category_income']);
			if ($income) {
				array_push ($budget_parent_child_list, array ($parent, 0, 1));
			} else {
				array_push ($budget_parent_child_list, array ($parent, 0, 0));
			}
			$child_list = $budget_category_class -> listChildCategories ($parent);
			if (is_array ($child_list)) {
				foreach ($child_list as $child) {
					$income = $budget_category_class -> isBitSet ($child, $_HOAM_setting ['budget']['flags']['category_income']);
					if ($income) {
						array_push ($budget_parent_child_list, array ($child, 1, 1));
					} else {
						array_push ($budget_parent_child_list, array ($child, 1, 0));
					}
				}				
			}
		}
		return ($budget_parent_child_list);
	}

	function ListCategoryId ($id) {
		// Return an array containing the id of all tracked categories for the
		// given budget
		$query = "SELECT $this->table.id FROM $this->table, budget_category
					WHERE budget_id='$id'
					AND category_id = budget_category.id
					ORDER BY budget_category.name, budget_category.parent_category";
		// Execute the query
		return ($this -> listSimpleQuery ($query));
	}
	
	function returnAmount ($id) {
		return ($this -> returnItem ($id, 'amount'));
	}

	function returnBudgetId ($id) {
		return ($this -> returnItem ($id, 'budget_id'));
	}

	function returnCategoryId ($id) {
		return ($this -> returnItem ($id, 'category_id'));
	}

	function returnIdForCategory ($id, $budget_id) {
		// Return an array containing the id of all categories
		$query = "SELECT id FROM $this->table
					WHERE category_id='$id' AND budget_id='$budget_id'";
		// Execute the query
		return ($this -> itemQuery ($query));
	}
}

?>
