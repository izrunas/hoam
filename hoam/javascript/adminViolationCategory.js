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

function HOAM_violationCategoryInit () {
	if ($('violation|category|parent_category')) {
		Element.observe ($('violation|category|parent_category'), 'change', HOAM_violationCategorySeverityUpdate);
		// parent_category exists, but the toggle doesn't when deleting.
		if ($('violation|category|parent_category-other_enable')) {
			Element.observe ($('violation|category|parent_category-other_enable'), 'change', HOAM_violationCategoryOtherToggle);
		}
	}
	if ($('violation|category|change')) {
		Element.observe ($('violation|category|change'), 'change', HOAM_violationCategoryChangeToggle);
		HOAM_violationCategoryChangeToggle ();
	}
	Element.observe ($('violation|category|show|name'), 'change', HOAM_violationCategoryShowExisting);

	HOAM_violationCategoryShowExisting ();
	HOAM_violationCategorySeverityUpdate ();
}

function HOAM_violationCategoryChangeToggle () {
	if ($('violation|category|change')) {
		if ($('violation|category|change').checked) {
			$('parent_change').show ();
		} else {
			$('parent_change').hide ();
		}
	}
}

function HOAM_violationCategoryOtherToggle () {
	if ($('violation|category|parent_category-other_enable').checked) {
		$('violation|category|parent_category').disable ();
		$('violation|category|parent_category-other').enable ();
	} else {
		$('violation|category|parent_category').enable ();
		$('violation|category|parent_category-other').disable ();
	}
}

function HOAM_violationCategorySeverityUpdate () {
	var id = $F('violation|category|parent_category');
	var url = '/hoam/scripts/violation/xml_categories.php';
	var pars = 'category=' + id;

	var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		// Update the default severity with the specified default severity for
		// the selected parent category
		var items = originalRequest.responseXML.getElementsByTagName ("item");
		setSelectIndex ('violation|category|initial_severity', getElementTextNS ("", 'initialseverity_id', items[0], 0));
	}	
	});
}

function HOAM_violationCategoryShowExisting () {
	// Update the show_description and show_detail textareas.
	var id = $F('violation|category|show|name');
	var url = '/hoam/scripts/violation/xml_categories.php';
	var pars = 'category=' + id;

	var myAjax = new Ajax.Request (url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		// put returned XML in the textarea
		var items = originalRequest.responseXML.getElementsByTagName ("item");
		$('violation|category|show|severity').value = getElementTextNS ("", 'initialseverity', items[0], 0);
		$('violation|category|show|description').value = getElementTextNS ("", 'description', items[0], 0);
		$('violation|category|show|detail').value = getElementTextNS ("", 'detail', items[0], 0);
	}	
	});
}

Event.observe (window, 'load', HOAM_violationCategoryInit);
