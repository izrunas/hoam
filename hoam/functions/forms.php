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
 * Questions specific to HOAM should be directed to ARP Realty. Please see the
 * HOAM web site at http://hoam.arprs.com/
 *
 * Some portions of HOAM incorporate ideas and/or code from other sources, and
 * those portions are explicitly mentioned and attributed in the relevant
 * section of HOAM source code. Questions about that code should be directed to
 * the original authors.
 *
 */

function HOAM_formCheckbox ($field_name, $field_desc, $value, $session_default, $default) {
	$HTML = '<div class="checkbox_option">';
	$HTML .= '<input ';
	if ($session_default) {
		$HTML .= 'checked="checked" ';
	} elseif ($default) {
		$HTML .= 'checked="checked" ';
	}
	$HTML .= 'type="checkbox" id="' . $field_name . '" name="' . $field_name . '" value="' . $value . '" />';
	$HTML .= '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '</div>';
	return ($HTML);
}

function HOAM_formCheckboxDisabled ($field_name, $field_desc, $value, $default) {
	$HTML = '<div class="checkbox_option">';
	$HTML .= '<input disabled="disabled" ';
	if ($default) {
		$HTML .= 'checked="checked" ';
	}
	$HTML .= 'type="checkbox" id="' . $field_name . '" name="' . $field_name . '" value="' . $value . '" />';
	$HTML .= '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '</div>';
	return ($HTML);
}

function HOAM_formCheckboxReadonly ($field_name, $field_desc, $default) {
	$HTML = '<div class="checkbox_option">';
	$HTML .= '<input readonly="readonly" ';
	if ($default) {
		$HTML .= 'checked="checked" ';
	}
	$HTML .= 'type="checkbox" id="' . $field_name . '" name="' . $field_name . '" />';
	$HTML .= '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= '</label>';
	$HTML .= '</div>';
	return ($HTML);
}

function HOAM_formConfigCheckbox ($field_name, $field_desc, $value, $session_default, $default) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input disabled="disabled" ';
	if ($session_default) {
		$HTML .= 'checked="checked" ';
	} elseif ($default) {
		$HTML .= 'checked="checked" ';
	}
	$HTML .= 'type="checkbox" id="' . $field_name . '" name="' . $field_name . '" value="' . $value . '" field="enable" />';
	return ($HTML);
}

function HOAM_formConfigPassword ($field_name, $field_desc, $session_default, $default, $max, $min = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input name="' . $field_name . '" id="' . $field_name . '" type="password" maxlength="' . $max . '" size="' . $max . '" value="';
	if ($session_default) {
		$HTML .= html_encode (stripslashes ($session_default));
	} else {
		$HTML .= html_encode (stripslashes ($default));
	}
	$HTML .= '" ';
	if ($min != NULL) {
		$HTML .= 'minlength="' . $min . '" ';
	}
	$HTML .= '/>';
	return ($HTML);
}

function HOAM_formDate ($field_name, $field_desc, $session_default, $default) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input name="' . $field_name . '" id="' . $field_name . '" size="10" value="';
	if (isset ($session_default)) {
		if ($session_default != '1970-01-01') {
			$HTML .= html_encode ($session_default);
		}
	} else {
		if ($default != '1970-01-01') {
			$HTML .= html_encode ($default);
		}
	}
	$HTML .= '" field="date"';
	$HTML .= '/>';
	return ($HTML);
}

function HOAM_formDateDisabled ($field_name, $field_desc, $default) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" size="10" value="'. html_encode ($default) . '" />';
	return ($HTML);
}

function HOAM_formDateEnable ($field_name, $field_desc, $session_default, $default) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" size="10" value="';
	if (isset ($session_default)) {
		$HTML .= html_encode ($session_default);
	} else {
		$HTML .= html_encode ($default);
	}
	$HTML .= '" ';
	$HTML .= ' field="enable" ';
	$HTML .= '/>';
	return ($HTML);
}

function HOAM_formDisplayError ($field_name) {
	$HTML = '';
	if (isset ($_SESSION['error_class_item'])) {
		$HTML .= '<span class="error_img">';
		$HTML .= $_SESSION['error_class_item'] -> ShowCategory ($field_name);
		$HTML .= '</span>';
	}
	return ($HTML);
}

function HOAM_formDisplayHelp ($field_name) {
	global $_HOAM_language;
	$HTML = '';
	$HTML .= '<span class="tooltip">&nbsp;<span class="tooltiptext">';
	$help = HOAM_returnLocalizedString ('help_popups|' . $field_name);
	if (!empty ($help)) {
		$HTML .= $help;
	} else {
		$HTML .= '<h1>' . ucwords (str_replace ('|', ' ', $field_name)) . '</h1>' . $_HOAM_language ['errors']['generic']['no-help-available'];
	}
	$HTML .= '</span></span>';
	return ($HTML);
}

function HOAM_formField ($field_name, $field_desc, $session_default, $default, $max, $min = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input name="' . $field_name . '" id="' . $field_name . '" maxlength="' . $max . '" size="' . $max . '" value="';
	if (isset ($session_default)) {
		$HTML .= html_encode (stripslashes ($session_default));
	} else {
		$HTML .= html_encode (stripslashes ($default));
	}
	$HTML .= '" ';
	if ($min != NULL) {
		$HTML .= 'minlength="' . $min . '" ';
	}
	$HTML .= '/>';
	return ($HTML);
}

function HOAM_formFieldDisabled ($field_name, $field_desc, $default, $max = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" size="' . $max . '" value="' . html_encode (stripslashes ($default)) . '" />';
	return ($HTML);
}

function HOAM_formFieldEnable ($field_name, $field_desc, $session_default, $default, $max, $min = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" maxlength="' . $max . '" size="' . $max . '" value="';
	if (isset ($session_default)) {
		$HTML .= html_encode (stripslashes ($session_default));
	} else {
		$HTML .= html_encode (stripslashes ($default));
	}
	$HTML .= '" ';
	if ($min != NULL) {
		$HTML .= 'minlength="' . $min . '"';
	}
	$HTML .= ' field="enable" ';
	$HTML .= '/>';
	return ($HTML);
}

function HOAM_formFieldEnableHidden ($field_name, $field_desc, $session_default, $default, $max, $min = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" maxlength="' . $max . '" size="' . $max . '" value="';
	if (isset ($session_default)) {
		$HTML .= html_encode (stripslashes ($session_default));
	} else {
		$HTML .= html_encode (stripslashes ($default));
	}
	$HTML .= '" ';
	if ($min != NULL) {
		$HTML .= 'minlength="' . $min . '"';
	}
	$HTML .= ' field="enable" ';
	$HTML .= 'style="display: none" />';
	return ($HTML);
}

function HOAM_formFieldHidden ($field_name, $field_desc, $session_default, $default, $max, $min = NULL) {
	$HTML = '<label for="' . $field_name . '" style="display: none">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input name="' . $field_name . '" id="' . $field_name . '" maxlength="' . $max . '" size="' . $max . '" value="';
	if (isset ($session_default)) {
		$HTML .= html_encode (stripslashes ($session_default));
	} else {
		$HTML .= html_encode (stripslashes ($default));
	}
	$HTML .= '" ';
	if ($min != NULL) {
		$HTML .= 'minlength="' . $min . '" ';
	}
	$HTML .= 'style="display: none" />';
	return ($HTML);
}

function HOAM_formFieldReadonly ($field_name, $field_desc, $default, $max) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input readonly="readonly" name="' . $field_name . '" id="' . $field_name . '" maxlength="' . $max . '" size="' . $max . '" value="';
	$HTML .= html_encode (stripslashes ($default));
	$HTML .= '" ';
	$HTML .= '/>';
	return ($HTML);
}

function HOAM_formGenerateFlag ($array) {
	// During upgrades and system housekeeping, if a flag has not been set,
	// generate a new valid ^2 that won't conflict with any existing flags.
	for ($new_flag = 1; $new_flag <= PHP_INT_MAX; $new_flag = ($new_flag * 2)) {
		if (!in_array ($new_flag, $array)) {
			break;
		}
	}
	if ($new_flag <= PHP_INT_MAX) {
		return $new_flag;
	} else {
		return FALSE;
	}
}

function HOAM_formPassword ($field_name, $field_desc, $session_default, $default, $max, $min = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input type="password" name="' . $field_name . '" id="' . $field_name . '" maxlength="' . $max . '" size="' . $max . '" value="';
	if (isset ($session_default)) {
		$HTML .= html_encode (stripslashes ($session_default));
	} else {
		$HTML .= html_encode (stripslashes ($default));
	}
	$HTML .= '" ';
	if ($min != NULL) {
		$HTML .= 'minlength="' . $min . '" ';
	}
	$HTML .= '/>';
	return ($HTML);
}

function HOAM_formPasswordDisabled ($field_name, $field_desc, $default, $max = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input type="password" disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" size="' . $max . '" value="' . html_encode (stripslashes ($default)) . '" />';
	return ($HTML);
}

function HOAM_formPasswordEnable ($field_name, $field_desc, $session_default, $default, $max, $min = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input type="password" disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" maxlength="' . $max . '" size="' . $max . '" value="';
	if (isset ($session_default)) {
		$HTML .= html_encode (stripslashes ($session_default));
	} else {
		$HTML .= html_encode (stripslashes ($default));
	}
	$HTML .= '" ';
	if ($min != NULL) {
		$HTML .= 'minlength="' . $min . '"';
	}
	$HTML .= ' field="enable" ';
	$HTML .= '/>';
	return ($HTML);
}

function HOAM_formRadio ($field_name, $field_desc, $fields, $session_default, $default) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	foreach ($fields as $field) {
		$HTML .= '<input type="radio" name="' . $field_name . '" id="' . $field_name . $field . '" value="' . $field . '"';
		if (isset ($session_default)) {
			if ($field == $session_default) {
				$HTML .= ' checked';
			}
		} else {
			if ($field == $default) {
				$HTML .= ' checked';
			}
		} 
		$HTML .= ">";
		$HTML .= $field . '</input>';
	}
	return ($HTML);
}

function HOAM_formSelect ($field_name, $field_desc) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<select id="' . $field_name . '" name="' . $field_name .'">';
	return ($HTML);
}

function HOAM_formSelectDisabled ($field_name, $field_desc) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<select disabled="disabled" id="' . $field_name . '" name="' . $field_name .'">';
	return ($HTML);
}

function HOAM_formSelectEnable ($field_name, $field_desc) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<select disabled="disabled" id="' . $field_name . '" name="' . $field_name .'" field="enable">';
	return ($HTML);
}

function HOAM_formSelectGroupEnable ($field_name, $field_desc, $session_default, $default) {
	$group_class = new HOAM_group ('group_list');
	
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<select disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" field="enable">';
	$group_list = $group_class -> listAll (NULL, 'name ASC');
	if (is_array ($group_list)) {
		if (isset ($session_default)) {
			$default_group = $session_default;
		} else {
			$default_group = $default;
		}
		foreach ($group_list as $group) {
			$HTML .= '<option ';
			if ($default_group == $group['id']) {
				$HTML .= 'selected="selected" ';
			}
			$HTML .= 'value="' . $group['id'] . '">' . $group['name'] . '</option>';
		}
	}
	$HTML .= '</select>';
	return ($HTML);
}

function HOAM_formSelectHidden ($field_name, $field_desc) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<select id="' . $field_name . '" name="' . $field_name .'" style="display: none">';
	return ($HTML);
}

function HOAM_formSelectMultiple ($field_name, $field_desc, $size = FALSE) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	if ($size) {
		$HTML .= '<select multiple="multiple" id="' . $field_name . '" name="' . $field_name .'[]" size="' . $size . '">';
	} else {
		$HTML .= '<select multiple="multiple" id="' . $field_name . '" name="' . $field_name .'[]">';
	}
	return ($HTML);
}

function HOAM_formSelectMultipleDisabled ($field_name, $field_desc, $size = FALSE) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	if ($size) {
		$HTML .= '<select disabled="disabled" multiple="multiple" id="' . $field_name . '" name="' . $field_name .'[]" size="' . $size . '">';
	} else {
		$HTML .= '<select disabled="disabled" multiple="multiple" id="' . $field_name . '" name="' . $field_name .'[]">';
	}
	return ($HTML);
}

function HOAM_formTextarea ($field_name, $field_desc, $session_default, $default, $rows, $max, $min = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<textarea name="' . $field_name . '" id="' . $field_name . '" cols="' . $_SESSION['current_user']['edit']['col'] . '" rows="' . $rows . '" maxlength="' . $max . '" ';
	if ($min != NULL) {
		$HTML .= 'minlength="' . $min . '" ';
	}
	$HTML .= '>';
	// Processes \r\n's first so they aren't converted twice.
	if ($session_default) {
		$HTML .= html_encode (stripslashes (str_replace ('\r\n', "\r\n", $session_default)));
	} else {
		$HTML .= html_encode (stripslashes (str_replace ('\r\n', "\r\n", $default)));
	}
	$HTML .= '</textarea>';
	return ($HTML);
}

function HOAM_formTextareaDisabled ($field_name, $field_desc, $default, $rows) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<textarea disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" cols="' . $_SESSION['current_user']['edit']['col'] . '" rows="' . $rows . '">';
	// Process \r\n's first so they aren't converted twice.
	$HTML .= html_encode (stripslashes (str_replace ('\r\n', "\r\n", $default)));
	$HTML .= '</textarea>';
	return ($HTML);
}

function HOAM_formTextareaEnable ($field_name, $field_desc, $session_default, $default, $rows, $max, $min = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<textarea disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" field="enable" cols="' . $_SESSION['current_user']['edit']['col'] . '" rows="' . $rows . '" maxlength="' . $max . '"  ';
	if ($min != NULL) {
		$HTML .= 'minlength="' . $min . '" ';
	}
	$HTML .= '>';
	// Processes \r\n's first so they aren't converted twice.
	if ($session_default) {
		$HTML .= html_encode (stripslashes (str_replace ('\r\n', "\r\n", $session_default)));
	} else {
		$HTML .= html_encode (stripslashes (str_replace ('\r\n', "\r\n", $default)));
	}
	$HTML .= '</textarea>';
	return ($HTML);
}

function HOAM_formTextareaReadonly ($field_name, $field_desc, $default, $rows, $max, $min = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= '</label>';
	$HTML .= '<textarea readonly="readonly" name="' . $field_name . '" id="' . $field_name . '" cols="' . $_SESSION['current_user']['edit']['col'] . '" rows="' . $rows . '" maxlength="' . $max . '" ';
	if ($min != NULL) {
		$HTML .= 'minlength="' . $min . '" ';
	}
	$HTML .= '>';
	// Processes \r\n's first so they aren't converted twice.
	$HTML .= html_encode (stripslashes (str_replace ('\r\n', "\r\n", $default)));
	$HTML .= '</textarea>';
	return ($HTML);
}

function HOAM_formText ($field_name, $field_desc, $session_default, $default, $maxlength = 255, $minlength = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input name="' . $field_name . '" id="' . $field_name . '" class="text" maxlength="' . $maxlength . '" size="' . $maxlength . '"';
	if ($minlength) {
		$HTML .= ' minlength="' . $minlength . '"';
	}
	$HTML .= ' value="';
	if ($session_default) {
		$HTML .= html_encode (stripslashes ($session_default));
	} else {
		$HTML .= html_encode (stripslashes ($default));
	}
	$HTML .= '" />';
	return ($HTML);
}

function HOAM_formTextDisabled ($field_name, $field_desc, $default) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" class="text" value="' . html_encode (stripslashes ($default)) . '" />';
	return ($HTML);
}

function HOAM_formTextEnable ($field_name, $field_desc, $session_default, $default, $maxlength = 255, $minlength = NULL) {
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<input disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" class="text" maxlength="' . $maxlength . '" size="' . $maxlength . '"';
	if ($minlength) {
		$HTML .= ' minlength="' . $minlength . '"';
	}
	$HTML .= ' value="';
	if ($session_default) {
		$HTML .= html_encode (stripslashes ($session_default));
	} else {
		$HTML .= html_encode (stripslashes ($default));
	}
	$HTML .= '" field="enable" />';
	return ($HTML);
}

function HOAM_timezone ($field_name, $field_desc, $session_default, $default) {
	// Adapted from http://us1.php.net/manual/en/function.timezone-identifiers-list.php#79284
	$HTML = '<label for="' . $field_name. '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= '</label>';
	$HTML .= '<select name="' . $field_name . '">';
	
	function timezonechoice ($selectedzone) {
		$all = timezone_identifiers_list();

		$i = 0;
		foreach ($all AS $zone) {
			$zone = explode ('/', $zone);
			$zonen[$i]['continent'] = isset($zone[0]) ? $zone[0] : '';
			$zonen[$i]['city'] = isset($zone[1]) ? $zone[1] : '';
			$zonen[$i]['subcity'] = isset($zone[2]) ? $zone[2] : '';
			$i++;
		}

		asort ($zonen);
		$structure = '';
		foreach ($zonen AS $zone) {
			extract ($zone);
			if ($continent == 'Africa' OR $continent == 'America' OR $continent == 'Antarctica' OR $continent == 'Arctic' OR $continent == 'Asia' OR $continent == 'Atlantic' OR $continent == 'Australia' OR $continent == 'Europe' OR $continent == 'Indian' OR $continent == 'Pacific') {
				if (!isset ($selectcontinent)) {
					$structure .= '<optgroup label="' . $continent . '">';
				} elseif ($selectcontinent != $continent) {
					$structure .= '</optgroup><optgroup label="' . $continent . '">';
				}

				if (isset ($city) != '') {
					if (!empty ($subcity) != '') {
						$city = $city . '/' . $subcity;
					}
					$structure .= "<option " . ((($continent . '/' . $city) == $selectedzone) ? 'selected="selected "' : '') . " value=\"" . ($continent . '/' . $city) . "\">" . str_replace ('_', ' ', $city) . "</option>";
				} else {
					if (!empty ($subcity) != '') {
						$city = $city . '/' . $subcity;
					}
					$structure .= "<option " . (($continent == $selectedzone) ? 'selected="selected "' : '') . " value=\"" . $continent . "\">" . $continent . "</option>";
				}

				$selectcontinent = $continent;
			}
		}
		$structure .= '</optgroup>';
		return $structure;
	}

	if (isset ($session_default)) {
		$HTML .= timezonechoice ($session_default);
	} else {
		$HTML .= timezonechoice ($default);
	}
	$HTML .= '</select>';
  
	return ($HTML);
}

function HOAM_timezoneEnable ($field_name, $field_desc, $session_default, $default) {
	$HTML = '<label for="' . $field_name. '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= ':</label>';
	$HTML .= '<select disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" field="enable">';
	
	function timezonechoice ($selectedzone) {
		$all = timezone_identifiers_list();

		$i = 0;
		foreach ($all AS $zone) {
			$zone = explode ('/', $zone);
			$zonen[$i]['continent'] = isset($zone[0]) ? $zone[0] : '';
			$zonen[$i]['city'] = isset($zone[1]) ? $zone[1] : '';
			$zonen[$i]['subcity'] = isset($zone[2]) ? $zone[2] : '';
			$i++;
		}

		asort ($zonen);
		$structure = '';
		foreach ($zonen AS $zone) {
			extract ($zone);
			if ($continent == 'Africa' OR $continent == 'America' OR $continent == 'Antarctica' OR $continent == 'Arctic' OR $continent == 'Asia' OR $continent == 'Atlantic' OR $continent == 'Australia' OR $continent == 'Europe' OR $continent == 'Indian' OR $continent == 'Pacific') {
				if (!isset ($selectcontinent)) {
					$structure .= '<optgroup label="' . $continent . '">';
				} elseif ($selectcontinent != $continent) {
					$structure .= '</optgroup><optgroup label="' . $continent . '">';
				}

				if (isset ($city) != '') {
					if (!empty ($subcity) != '') {
						$city = $city . '/' . $subcity;
					}
					$structure .= "<option " . ((($continent . '/' . $city) == $selectedzone) ? 'selected="selected "' : '') . " value=\"" . ($continent . '/' . $city) . "\">" . str_replace ('_', ' ', $city) . "</option>";
				} else {
					if (!empty ($subcity) != '') {
						$city = $city . '/' . $subcity;
					}
					$structure .= "<option " . (($continent == $selectedzone) ? 'selected="selected "' : '') . " value=\"" . $continent . "\">" . $continent . "</option>";
				}

				$selectcontinent = $continent;
			}
		}
		$structure .= '</optgroup>';
		return $structure;
	}

	if (isset ($session_default)) {
		$HTML .= timezonechoice ($session_default);
	} else {
		$HTML .= timezonechoice ($default);
	}
	$HTML .= '</select>';
  
	return ($HTML);
}

function HOAM_formUser ($field_name, $field_desc, $session_default, $default) {
	$user_class = new HOAM_user ('users');
	
	$HTML = '<label for="' . $field_name . '">' . $field_desc;
	$HTML .= HOAM_formDisplayHelp ($field_name);
	$HTML .= HOAM_formDisplayError ($field_name);
	$HTML .= '</label>';
	$HTML .= '<select disabled="disabled" name="' . $field_name . '" id="' . $field_name . '" field="enable">';
	$user_list = $user_class -> listAll (NULL, 'accountname DESC');
	if (is_array ($user_list)) {
		if (isset ($session_default)) {
			$default_user = $session_default;
		} else {
			$default_user = $default;
		}
		foreach ($user_list as $user) {
			$HTML .= '<option ';
			if ($default_user == $user['id']) {
				$HTML .= 'selected="selected" ';
			}
			$HTML .= 'value="' . $user['id'] . '">' . html_encode ($user['accountname']) . '</option>';
		}
	}
	$HTML .= '</select>';
	return ($HTML);
}

function HOAM_formValidateFlagValue ($field_name, $value, &$errors) {
	// Essentially just combined all the common code into this function to
	// save typing and reduce errors.
	global $_HOAM_language;
	if ($value < 0) {
		// The value is too small.
		$errors -> addError ($field_name, sprintf ($_HOAM_language ['errors']['generic']['value-too-small'], 0));
	} elseif ($value > 9223372036854775808) {
		// The maximum size of the default flags field (an unsigned bigint)
		// is 18446744073709551615, but the largest power of 2 that won't roll
		// over is 9223372036854775808.
		$errors -> addError ($field_name, sprintf ($_HOAM_language ['errors']['generic']['value-too-large'], 9223372036854775808));
	}
	if (!HOAM_isPowerOf2 ($value)) {
		$errors -> addError ($field_name, $_HOAM_language ['errors']['config']['flag']['invalid']);
	}
	return ($value);
}

function HOAM_formValidateValue ($field_name, $value, &$errors, $min, $max) {
	// Essentially just combined all the common code into this function to
	// save typing and reduce errors.
	global $_HOAM_language;
	if ($value < $min) {
		// The value is too small.
		$errors -> addError ($field_name, sprintf ($_HOAM_language ['errors']['generic']['value-too-small'], $min));
	} elseif ($value > $max) {
		// The value is too large.
		$errors -> addError ($field_name, sprintf ($_HOAM_language ['errors']['generic']['value-too-large'], $max));
	}
	return ($value);
}

?>