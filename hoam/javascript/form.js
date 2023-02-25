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

function HOAM_formInit () {
	// Look for LABELs and append help text, if any found.
//    HOAM_formHelpLookup ();
    HOAM_formFieldTags ();
}

function HOAM_formFieldTags () {
	var checkbox_fields = $('page_content').getElementsByTagName ('CHECKBOX');
	var input_fields = $('page_content').getElementsByTagName ('INPUT');
	var select_fields = $('page_content').getElementsByTagName ('SELECT');
	var textarea_fields = $('page_content').getElementsByTagName ('TEXTAREA');
	var admin_enable_str = HOAM_languageLookup ('field_names|admin|enable');
	var admin_preview_str = HOAM_languageLookup ('field_names|admin|preview');
	var calendar_show_str = HOAM_languageLookup ('field_names|calendar|show');
	// Check checkbox tags for any special cases.
    if (checkbox_fields.length > 0) {
    	for (var i = 0; i < checkbox_fields.length; i++) {
    		field_type = checkbox_fields[i].getAttribute ('field');
    	    // Look for specific fields and apply validation where applicable
    		switch (field_type) {
    			case 'enable':
    				var enable_checkbox = document.createElement ('INPUT');
    				enable_checkbox.id = input_fields[i].id + '_enable';
    				enable_checkbox.name = input_fields[i].id + '_enable';
    				enable_checkbox.setAttribute ('title', admin_enable_str);
    				enable_checkbox.type = 'checkbox';
    				enable_checkbox.value = 1;
    				checkbox_fields[i].parentNode.insertBefore (enable_checkbox, checkbox_fields[i].nextSibling);
    				Element.observe (enable_checkbox, 'click', function (event) {
    					var input_name = Event.element(event).id.replace (/_enable/, '');
    					$(input_name)[$(input_name).disabled ? 'enable' : 'disable']();
    				});
    				break;
    			default:
    				// No default action.
    				break;
    		}
    	}
    }
	// Check input tags for any special cases.
    if (input_fields.length > 0) {
    	for (var i = 0; i < input_fields.length; i++) {
    		field_type = input_fields[i].getAttribute ('field');
    	    // Look for specific fields and apply validation where applicable
    		switch (field_type) {
    			case 'date':
    				// This still uses Matt Kruse's calendar function, so we
    				// need to work within those constraints.
    				var calendar = new CalendarPopup();
    			    calendar.showNavigationDropdowns();
    			    var button = document.createElement ('IMG');
    			    button.id = input_fields[i].name + '_calendar';
    				button.setAttribute ('src', '/hoam/images/icons/calendar.png');
    				button.setAttribute ('title', calendar_show_str);
    			    button.style.cursor = 'pointer';
    				input_fields[i].parentNode.insertBefore (button, input_fields[i].nextSibling);
    				Event.observe (button, 'click', function () { calendar.select (this.previousSibling, this.id, 'MM/dd/yyyy') });
    				break;
    			case 'enable':
    				var enable_checkbox = document.createElement ('INPUT');
    				enable_checkbox.id = input_fields[i].id + '_enable';
    				enable_checkbox.name = input_fields[i].id + '_enable';
    				enable_checkbox.setAttribute ('title', admin_enable_str);
    				enable_checkbox.type = 'checkbox';
    				enable_checkbox.value = 1;
    				input_fields[i].parentNode.insertBefore (enable_checkbox, input_fields[i].nextSibling);
    				Element.observe (enable_checkbox, 'click', function (event) {
    					var input_name = Event.element(event).id.replace (/_enable/, '');
    					$(input_name)[$(input_name).disabled ? 'enable' : 'disable']();
    				});
    				break;
    			default:
    				// No default action.
    				break;
    		}
    	}
    }
    // Run through the same routine for selects.
    if (select_fields.length > 0) {
    	for (var i = 0; i < select_fields.length; i++) {
    		field_type = select_fields[i].getAttribute ('field');
    	    // Look for specific fields and apply validation where applicable
    		switch (field_type) {
    			case 'enable':
    				var enable_checkbox = document.createElement ('INPUT');
    				enable_checkbox.id = select_fields[i].id + '_enable';
    				enable_checkbox.name = select_fields[i].id + '_enable';
    				enable_checkbox.setAttribute ('title', admin_enable_str);
    				enable_checkbox.type = 'checkbox';
    				enable_checkbox.value = 1;
    				select_fields[i].parentNode.insertBefore (enable_checkbox, select_fields[i]);
    				select_fields[i].disabled = 1;
    				Element.observe ($(enable_checkbox), 'click', function (event) {
    					var select_name = Event.element(event).id.replace (/_enable/, '');
    					$(select_name)[$(select_name).disabled ? 'enable' : 'disable']();
    				});
    				break;
    			default:
    				// No default action.
    				break;
    		}
    	}
    }
    // Run through the same routine for textareas.
    if (textarea_fields.length > 0) {
    	for (var i = 0; i < textarea_fields.length; i++) {
    		field_type = textarea_fields[i].getAttribute ('field');
    	    // Look for specific fields and apply validation where applicable
    		switch (field_type) {
    			case 'enable':
    				var enable_checkbox = document.createElement ('INPUT');
    				enable_checkbox.id = textarea_fields[i].id + '_enable';
    				enable_checkbox.name = textarea_fields[i].id + '_enable';
    				enable_checkbox.setAttribute ('title', admin_enable_str);
    				enable_checkbox.type = 'checkbox';
    				enable_checkbox.value = 1;
    				var preview = document.createElement ('IMG');
    			    preview.id = textarea_fields[i].name + '_preview';
    				preview.setAttribute ('src', '/hoam/images/icons/layout.png');
    				preview.setAttribute ('title', admin_preview_str);
    			    preview.style.cursor = 'pointer';
    				textarea_fields[i].parentNode.insertBefore (enable_checkbox, textarea_fields[i]);
    				textarea_fields[i].disabled = 1;
    				textarea_fields[i].parentNode.insertBefore (preview, textarea_fields[i]);
    				Element.observe ($(enable_checkbox), 'click', function (event) {
    					var textarea_name = Event.element(event).id.replace (/_enable/, '');
    					$(textarea_name)[$(textarea_name).disabled ? 'enable' : 'disable']();
    				});
    				Element.observe ($(preview), 'click', function (event) {
    					var textarea_name = Event.element(event).id.replace (/_preview/, '');
    					HOAM_formPreview ($F(textarea_name));
    				});
    				break;
    			default:
    				// No default action.
    				break;
    		}
    	}
    }
}

function HOAM_formPreview (html) {
	var div = document.createElement ('DIV');
	div.setAttribute ('style', 'background: white; border: 1px solid black; font-size: 50%; margin: 0 auto; padding: 5px; position: absolute; width: 50%');
	div.innerHTML = html;
	$('page_content').appendChild (div);
//	Element.observe (div, 'click', function (event) {
//		document.removeChild (Event.element(event));
//		Event.stopObserving (Event.element(event), 'click');
//	});
}

function HOAM_formErrorMessage (label_for, message) {
	// Display the given message in a child span of the specified label
	var labels = $('page_content').getElementsByTagName ('LABEL');
	if (labels.length > 0) {
		var i = 0;
		var label_found = false;
		while (i < labels.length && !label_found) {
			if (labels[i].getAttribute ('for') == label_for) {
				label_found = true;
			} else {
				i++;
			}
		}
		if (label_found) {
			var spans = $(labels[i]).getElementsByTagName ('SPAN');
			var j = 0;
			for (; j < spans.length; j++) {
				if (spans[j].className == 'error_img') {
					spans[j].innerHTML = message;
					break;
				}
			}
			// OK, here's where it's weird: if j == 2, but spans.length is
			// also == 2, it means we've not found an existing error span
			// because j starts at 0, then 1, then we're no longer < length.
			// Same thing if there are 3 spans, 4 spans, etc., if we can't
			// find it, then j will still equal the array size. If it's !=,
			// then that means we've found and already updated the existing
			// span.
			if (j == spans.length) {
				span = document.createElement ('SPAN');
				span.className = 'error_img';
				span.innerHTML = message;
				labels[i].appendChild (span);
			}
		} else {
			alert (HOAM_languageLookup ('errors|hoam|form|label-not-found', label_for, message));
		}
	} else {
		alert (HOAM_languageLookup ('errors|hoam|form|label-not-found', label_for, message));
	}
}

function HOAM_formErrorClear (label_for) {
	// Remove any existing error message span.
	var labels = $('page_content').getElementsByTagName ('LABEL');
	if (labels.length > 0) {
		var i = 0;
		var label_found = false;
		while (i < labels.length && !label_found) {
			if (labels[i].getAttribute ('for') == label_for) {
				label_found = true;
			} else {
				i++;
			}
		}
		if (label_found) {
			var spans = $(labels[i]).getElementsByClassName ('error_img');
			for (j = 0; j < spans.length; j++) {
				if (spans[j].className == 'error_img') {
					labels[i].removeChild (spans[j]);		
				}
			}
		}
	}
}

function HOAM_formHelpLookup () {
	// Find all <label>s on the page, and add a help bubble if there is a
	// corresponding for= attribute

	var labels = $('page_content').getElementsByTagName ('LABEL');
	var divider = '|';
	if (labels.length > 0) {
		for (var i = 0; i < labels.length; i++) {
			var helpImage = document.createElement ('IMG');
			helpImage.setAttribute ('alt', 'Help Icon');
			helpImage.setAttribute ('src', '/hoam/images/icons/help.png');
			helpImage.setAttribute ('style', 'margin-top: 1px;');
			helpImage.style.cursor = 'help';
			// Try to find any help embedded on the form tag
			var label_for = labels[i].getAttribute ('for');
			pars = 'string=help_popups' + divider + label_for;
			// Don't try and look for additional parameters if the form field
			// doesn't have an id assigned.
			if ($(label_for)) {
				var param1 = $(label_for).getAttribute ('minlength');
				pars = pars + '&param1=' + param1;
			}
			TooltipManager.addAjax (helpImage, {url: '/hoam/scripts/help/languageLookup.php', options: {method: 'get', parameters: pars}});
			labels[i].insertBefore (helpImage, labels[i].firstChild.nextSibling);
		}
	}
}

Event.observe (window, 'load', HOAM_formInit, false);
