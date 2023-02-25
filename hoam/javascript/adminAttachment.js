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

function HOAM_attachmentInit () {
	// Preload the page with existing attachments
	// This check can be removed as soon as HOAM_attachmentYear is removed.
	if ($j('#fillme').length) {
		HOAM_attachmentListLoad ();
		HOAM_tableStripe ();
	}

	// DEPRECATED.
	// Can be removed as soon as the website/attachment/list_old/ link is gone.
	//
	HOAM_attachmentYear ();
	
	$j(document).bind ('scroll', HOAM_attachmentScroll);
}

function HOAM_attachmentListLoad () {
	// In most cases the lastChild is the last <tr> that was appended to the
	// tbody. If it's null, then we're retrieving the initial list. 
	var lc = $('fillme').lastChild;
	if (lc) {
		var id = lc.id;
	} else {
		var id = '';	
	}
	var url = '/hoam/scripts/attachment/xml_attachment_list.php';
	// By default we're going to load 10; the script allows between 2 and 50.
	var pars = 'id=' + id + '&num=10';
	var myAjax = new Ajax.Request (url, { asynchronous: false, method: 'get', parameters: pars, onComplete: HOAM_attachmentListLoaded });
}

function HOAM_attachmentListLoaded (originalRequest) {
	var items = originalRequest.responseXML.getElementsByTagName ('attachment');
	var text_delete = HOAM_languageLookup ('action|generic|delete');
	var text_edit = HOAM_languageLookup ('action|generic|edit');

	if (getElementTextNS ('', 'id', items[0], 0) != null) {
	    for (var i = 0; i < items.length; i++) {
			var tr = document.createElement ('tr');
			tr.id = getElementTextNS ('', 'id', items[i], 0);

			var td = document.createElement ('td');
			td.innerHTML = getElementTextNS ('', 'html', items[i], 0);
			tr.appendChild (td);

			var td = document.createElement ('td');
			td.innerHTML = getElementTextNS ('', 'description', items[i], 0);
			tr.appendChild (td);

			var td = document.createElement ('td');
			td.innerHTML = getElementTextNS ('', 'owner', items[i], 0);
			tr.appendChild (td);

			var td = document.createElement ('td');
			td.innerHTML = getElementTextNS ('', 'creator', items[i], 0);
			tr.appendChild (td);

			var td = document.createElement ('td');
			var id = getElementTextNS ('', 'id', items[i], 0);
			td.innerHTML = '<small><a href="/website/attachment/edit/' + id + '/">' + text_edit + '</a></small>';
			td.innerHTML += '&nbsp; ';
			td.innerHTML += '<small><a class="error" href="/website/attachment/delete/' + id + '/">' + text_delete + '</a></small>';
			tr.appendChild (td);

			$('fillme').appendChild (tr);
		}
	} else {
		var tr = document.createElement ('tr');
		var td = document.createElement ('td');
		td.setAttribute ('colspan', '5');
		td.innerHTML = HOAM_languageLookup ('errors|attachment|none-found');
		tr.appendChild (td);
		$('fillme').appendChild (tr);
	}
}

function HOAM_attachmentScroll (event) {
	Event.stopObserving(Event.element(event));
	
	// Compare the total height of the document to the location of the scrollbar
	// and load additional items if we need to.
	var ar = document.viewport.getScrollOffsets();
	var htmlheight = document.body.parentNode.scrollHeight; 
	if ((htmlheight - ar.top) < 1200) {
		HOAM_attachmentListLoad ();
	}
	// For now, just restriping the whole page.
	HOAM_tableStripe ();
	Element.observe (document, 'scroll', HOAM_attachmentScroll);
}

function HOAM_attachmentYear () {
	//
	// DEPRECATED.
	//
	// Can be removed as soon as the website/attachment/list_old/ link is gone.
	//
	var span_fields = $('page_content').getElementsByTagName ('SPAN');
	for (var i = 0; i < span_fields.length; i++) {
		field_type = span_fields[i].getAttribute ('field');
	    // Look for specific fields and apply validation where applicable
		switch (field_type) {
			case 'year':
				span_fields[i].style.cursor = 'pointer';
				Element.observe (span_fields[i], 'click', function (event) {
					if (Event.element(event).nextSibling.visible()) {
						Event.element(event).innerHTML = '&#x0229E; ' + Event.element(event).innerHTML.substr(2);
						Event.element(event).nextSibling.hide();
					} else {
						Event.element(event).innerHTML = '&#x0229F; ' + Event.element(event).innerHTML.substr(2);
						Event.element(event).nextSibling.show();
					}
				});
				break;
			default:
				// No default action.
				break;
		}
	}
}

Event.observe (window, 'load', HOAM_attachmentInit);
