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
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_edit'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) OR isset ($_POST['cancel']) OR isset ($_POST['delete']) OR isset ($_POST['modify'])) {
		$violation_category_class = new HOAM_violation_category ('violation_category');
		$violation_severity_class = new HOAM_violation_severity ('violation_severity');
		
		// If this is the second (or greater) time through the script (ie,
		// there were errors that had to be fixed before committing the
		// changes) get rid of the old cached information the user entered,
		// we're going to populate it with information just received from the
		// form posting.
		if (isset ($_SESSION['violation_category_information'])) {
			unset ($_SESSION['violation_category_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			// Just send them back to the admin page
				header ('Location: /admin/violation/category/');
		} elseif (isset ($_POST['delete'])) {
			// Make sure the current user is the owner of the item, OR has
			// admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['violation_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
			// Make sure we found the category
				$category_id = (string) $violation_category_class -> superClean ($_POST['violation|category|id'], $violation_category_class -> returnColumnSize ('id'));
				if ($violation_category_class -> idExists ($category_id)) {
					// Need to add a check that we aren't deleting a category
					// that is in use by any violations.
					$violation_class = new HOAM_violation ('violations');
					if ($violation_class -> returnCategoryInUse ($category_id)) {
						// Error Removing Item
						header ('Location: /error/inuse/');
					} else {
						if ($violation_category_class -> deleteId ($category_id)) {
							header ('Location: /admin/violation/category/');
						} else {
							// Error Removing Item
							header ('Location: /error/remove/');
						}
					}
				} else {
					// The article couldn't be found
					header ('Location: /error/unknown_id/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// If we're still here, then we must be adding or modifying an
			// existing category.
			$_SESSION['violation_category_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');
		
			if (isset ($_POST['violation|category|id'])) {
				$_SESSION['violation_category_information']['trusted_violation|category|id'] = (string) $violation_category_class -> superClean ($_POST['violation|category|id'], $violation_category_class -> returnColumnSize ('id'));
				if ($violation_category_class -> idExists ($_SESSION['violation_category_information']['trusted_violation|category|id'])) {
				} else {
					// The category is unknown.
					$_SESSION ['error_class_item'] -> addError ('violation|category|id', $_HOAM_language ['errors']['violation']['category']['unknown_id']);
				}
			}
			if (isset ($_POST['violation|category|parent_category'])) {
				$_SESSION['violation_category_information']['trusted_violation|category|parent_category'] = $violation_category_class -> superClean ($_POST['violation|category|parent_category'], $violation_category_class -> returnColumnSize ('parent_category'));
				if ($violation_category_class -> idExists ($_SESSION['violation_category_information']['trusted_violation|category|parent_category'])) {
				} else {
					// The category is unknown.
					$_SESSION ['error_class_item'] -> addError ('violation|category|parent_category', $_HOAM_language ['errors']['violation']['category_unknown_id']);
				}
			}
			if (isset ($_POST['violation|category|parent_category-other_enable'])) {
				$_SESSION['violation_category_information']['trusted_violation|category|parent_category-other_enable'] = (int) $violation_category_class -> superClean ($_POST['violation|category|parent_category-other_enable'], 1);
				if ($_SESSION['violation_category_information']['trusted_violation|category|parent_category-other_enable']) {
					$_SESSION['violation_category_information']['trusted_violation|category|parent_category-other'] = (string) ucfirst ($violation_category_class -> superClean ($_POST['violation|category|parent_category-other'], $violation_category_class -> returnColumnSize ('category')));
					// Make sure the category name isn't too short.
					if (mb_strlen ($_SESSION['violation_category_information']['trusted_violation|category|parent_category-other']) >= $_HOAM_setting ['violation']['category']['minimum_category_length']) {
					} else {
						// The category name is too short.
						$_SESSION ['error_class_item'] -> addError ('violation|category|parent_category-other', sprintf ($_HOAM_language ['errors']['violation']['category']['too_short'], $_HOAM_setting ['violation']['category']['minimum_category_length']));
					}
					// Make sure the category name isn't a duplicate of an
					// existing category.
					$category_list = $violation_category_class -> listParentCategories ();
					if (is_array ($category_list)) {
						foreach ($category_list as $category) {
							if (strtoupper ($_SESSION['violation_category_information']['trusted_violation|category|parent_category-other']) == strtoupper ($violation_category_class -> returnCategory ($category))) {
								$_SESSION ['error_class_item'] -> addError ('violation|category|parent_category-other', $_HOAM_language ['errors']['violation']['category']['duplicate']);
							}
						}
					}
				}
			}
			if (isset ($_POST['violation|category|initial_severity'])) {
				$_SESSION['violation_category_information']['trusted_violation|category|initial_severity'] = (string) $violation_severity_class -> superClean ($_POST['violation|category|initial_severity'], $violation_severity_class -> returnColumnSize ('id'));
				// Make sure the severity is valid.
				if ($violation_severity_class -> idExists ($_SESSION['violation_category_information']['trusted_violation|category|initial_severity'])) {
				} else {
					// The severity level does not exist.
					$_SESSION ['error_class_item'] -> addError ('violation|category|initial_severity', $_HOAM_language ['errors']['violation']['severity']['unknown_id']);
				}
			}
			if (isset ($_POST['violation|category|name'])) {
				$_SESSION['violation_category_information']['trusted_violation|category|name'] = (string) ucfirst ($violation_category_class -> clean ($_POST['violation|category|name'], $violation_category_class -> returnColumnSize ('category')));
				// Make sure the category isn't too short.
				if (mb_strlen ($_SESSION['violation_category_information']['trusted_violation|category|name']) >= $_HOAM_setting ['violation']['category']['minimum_category_length']) {
				} else {
					// The category is too short.
					$_SESSION ['error_class_item'] -> addError ('violation|category|name', $_HOAM_language ['errors']['violation']['category']['too_short'], $_HOAM_setting ['violation']['category']['minimum_category_length']);
				}
				// Make sure the category name isn't a duplicate of an existing category.
				if ($_SESSION ['violation_category_information']['trusted_category|category|parent_category-other_enable']) {
					// If we're creating a new parent category, there's no way
					// for this category to be in conflict with another.
					$category_list = array();
				} else {
					$category_list = $violation_category_class -> listChildCategories ($_SESSION['violation_category_information']['trusted_violation|category|parent_category']);
				}
				if (is_array ($category_list)) {
					foreach ($category_list as $category) {
						if (strtoupper ($_SESSION ['violation_category_information']['trusted_violation|category|name']) == strtoupper ($violation_category_class -> returnCategory ($category))) {
							// Make sure we're not reporting on our own id.
							if ($_SESSION ['violation_category_information']['trusted_violation|category|id'] != $category) {
								$_SESSION ['error_class_item'] -> addError ('violation|category|name', $_HOAM_language ['errors']['violation']['category']['duplicate']);
							}
						}
					}
				}
			}
			if (isset ($_POST['violation|category|description'])) {
				$_SESSION['violation_category_information']['trusted_violation|category|description'] = (string) $violation_category_class -> clean ($_POST['violation|category|description'], $violation_category_class -> returnColumnSize ('description'));
				// Make sure the category description isn't too short.
				if (mb_strlen ($_SESSION['violation_category_information']['trusted_violation|category|description']) >= $_HOAM_setting ['violation']['category']['minimum_description_length']) {
				} else {
					// The description is too short.
					$_SESSION ['error_class_item'] -> addError ('violation|category|description', sprintf ($_HOAM_language ['errors']['violation']['category']['description_too_short'], $_HOAM_setting ['violation']['category']['minimum_description_length']));
				}
			}
			if (isset ($_POST['violation|category|detail'])) {
				$_SESSION['violation_category_information']['trusted_violation|category|detail'] = (string) $violation_category_class -> clean ($_POST['violation|category|detail'], $violation_category_class -> returnColumnSize ('detail'));
				// Make sure the deed restriction detail isn't too short.
				if (mb_strlen ($_SESSION['violation_category_information']['trusted_violation|category|detail']) >= $_HOAM_setting ['violation']['category']['minimum_detail_length']) {
				} else {
					// The detail is too short.
					$_SESSION ['error_class_item'] -> addError ('violation|category|detail', sprintf ($_HOAM_language ['errors']['violation']['category']['detail_too_short'], $_HOAM_setting ['violation']['category']['minimum_detail_length']));
				}
			}

// print_r ($_POST); print_r ($_SESSION['violation_category_information']); print_r ($_SESSION['error_class_item'] -> ShowErrors ()); exit();

			// Are there any errors that the current user needs to fix before
			// committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /admin/violation/category/add/');
				} else {
					header ('location: /admin/violation/category/edit/' . $_SESSION['violation_category_information']['trusted_violation|category|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the
				// database.
				if (isset ($_POST['add'])) {
					$category_id = $violation_category_class -> add ($_SESSION['current_user']['id']);
					if ($category_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					}
				} else {
					// Use the information gathered above.
					$category_id = $_SESSION['violation_category_information']['trusted_violation|category|id'];
					$violation_category_class -> changeDateModified ($category_id);
				}
				// By now, we've already verified that all of our data is
				// correct and valid, therefore we can just update the db.
				// Check whether we're using an existing parent or adding new.
				if ($_SESSION['violation_category_information']['trusted_violation|category|parent_category-other_enable']) {
					// We first need to actually create the new parent category
					$parent_id = $violation_category_class -> add ($_SESSION['current_user']['id']);
					if ($parent_id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						$violation_category_class -> changeCategory ($parent_id, $_SESSION['violation_category_information']['trusted_violation|category|parent_category-other']);
						$violation_category_class -> changeInitialSeverity ($parent_id, $_HOAM_setting ['violation']['category']['initial_severity']);
						$violation_category_class -> changeParentCategory ($category_id, $parent_id);
					}
				} else {
					$violation_category_class -> changeParentCategory ($category_id, $_SESSION['violation_category_information']['trusted_violation|category|parent_category']);
				}
				$violation_category_class -> changeCategory ($category_id, $_SESSION['violation_category_information']['trusted_violation|category|name']);
				$violation_category_class -> changeDescription ($category_id, $_SESSION['violation_category_information']['trusted_violation|category|description']);
				$violation_category_class -> changeDetail ($category_id, $_SESSION['violation_category_information']['trusted_violation|category|detail']);
				$violation_category_class -> changeInitialSeverity ($category_id, $_SESSION['violation_category_information']['trusted_violation|category|initial_severity']);

				// OK, all done. Get rid of our data and finish up.
				if (isset ($_SESSION['violation_category_information'])) {
					unset ($_SESSION['violation_category_information']);
				}
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}

				header ('Location: /admin/violation/category/list/');
			}
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're
		// being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
	exit ();
}

?>
