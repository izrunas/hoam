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

function HOAM_violationPrintInit () {
	Element.observe ($('violation|letter|date'), 'change', HOAM_violationShowLettersOnDate);
}

function HOAM_violationShowLettersOnDate () {
	var date = $F('violation|letter|date');
	var url = '/hoam/scripts/violation/xml_violation_print.php';
	var pars = 'date=' + date;
	var myAjax = new Ajax.Request (url, { method: 'get', parameters: pars, onComplete: HOAM_violationShowLetters });
}

function HOAM_violationShowLetters (originalRequest) {
    // Clear out any previous load
    var items = originalRequest.responseXML.getElementsByTagName ('violation');
    // loop through <item> elements, and add each nested
    // <title> element to Topics select element
    for (var i = 0; i < items.length; i++) {
		var div = document.createElement ('div');
		div.innerHTML = getElementTextNS ('', 'original_letter', items[i], 0);
		$('page_content').appendChild (div);
    }
}

Event.observe (window, 'load', HOAM_violationPrintInit);
