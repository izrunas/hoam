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

// tasklist_editing is true (i.e. set to an object)/false if we're editing an object
var tasklist_editing = false;
// tasklist_temp_li is !null if we have made a copy of an li during editing
var tasklist_temp_li = document.createElement ("LI");

function tasklist_catchAdd () {
	// If we're already editing an item, return
	if (tasklist_editing) return;
	// Get the tasklist ul object
	var tasklist = $("mini-tasklist");
	var ul = tasklist.getElementsByTagName ("UL");
	editbox = tasklist_editbox ();

	// Get rid of any textual information inside the tasklist.
	var text = tasklist.lastChild;
	try {
		// 1 is an element node, 2 attribute and 3 text
		if (text.nodeType == 3) {
			//tasklist.removeChild (text);
			text.nodeValue = "";
		}
	}
	catch (e) {
		// Nothing. Just leave it.
	}

	// If there aren't any previous tasks, then the list will be empty, and there is no <ul>
	try {
		// 1 is an element node, 2 attribute and 3 text
		if (ul[0].nodeType == 1) {
			// Insert the editbox
			ul[0].insertBefore (editbox, ul[0].firstChild);
		}
	}
	catch (error) {
		// Create the <ul> and add the editbox.
		ul = document.createElement ("UL");
		ul.appendChild (editbox);
		tasklist.appendChild (ul);
	}

	// We're done setting it all up, now let the user edit.
	editbox.lastChild.focus ();
	tasklist_editing = editbox;
}

function tasklist_catchCancel (e) {
	// Revert any changes and return
	// !! This will break if the textarea item is not the last child of the editbox
	// Originally this function was passed the textarea.onclick event, however there is some type of issue
	// (probably standard JS behavior that I don't understand) that causes the function to be called
	// on initialization / display, rather than onclick as expected. Removing parameters to the .onclick
	// assignment resolves the issue, and thus we now need to determine which element triggered us.
	if (!e) {
		var obj = window.event.srcElement;
	} else {
		var obj = e.target;
	}
	// If the contents of the textarea are the same as the original LI, just copy it back.
	if (obj.parentNode.lastChild.value == tasklist_temp_li.innerHTML) {
		tasklist_revertChanges (obj);
	} else {
		tasklist_NotSaved (obj);
		tasklist_none (obj.parentNode);
	}
	
	tasklist_editing = false;
}

function tasklist_catchComplete (e) {
	// Delete the specified task
	if (!e) {
		var obj = window.event.srcElement;
	} else {
		var obj = e.target;
	}
	// Find the textarea. This will break if the textarea item is not
	// the last child of the editbox
	obj = obj.parentNode.lastChild;
	if (obj.id) {
		// In order for the data to appear to PHP as a standard form submit, we need to set the content type and format it appropriately
		url = "/hoam/plugins/tasklist/edit_script.php";
		pars = "&complete=1&id=" + obj.id;
		var myAjax = new Ajax.Request (url, {method: 'post', parameters: pars, onComplete: tasklist_update});
	}
}

function tasklist_catchDelete (e) {
	// Delete the specified task
	if (!e) {
		var obj = window.event.srcElement;
	} else {
		var obj = e.target;
	}
	// Find the textarea. This will break if the textarea item is not
	// the last child of the editbox
	obj = obj.parentNode.lastChild;
	if (obj.id) {
		// In order for the data to appear to PHP as a standard form submit, we need to set the content type and format it appropriately
		url = "/hoam/plugins/tasklist/edit_script.php";
		pars = "&delete=1&id=" + obj.id;
		var myAjax = new Ajax.Request (url, {method: 'post', parameters: pars, onComplete: tasklist_update});
	}
}

function tasklist_catchEdit (e) {
	// If we're already editing an item, return
	if (tasklist_editing) return;
	// I /think/ based on the browser, determine if the event came from an element or a target?
	if (!e) {
		var obj = window.event.srcElement;
	} else {
		var obj = e.target;
	}
	// Until we have an element as our target, move up the tree
	// 1 is an element node, 2 attribute and 3 text
	while (obj.nodeType != 1) {
		obj = obj.parentNode;
	}
	// If the target is
	if (obj.tagName == "TEXTAREA" || obj.tagName == "A") return;
	while (obj.nodeName != "LI" && obj.nodeName != "HTML") {
		obj = obj.parentNode;
	}
	if (obj.nodeName == "HTML") return;
	// Walk the tree until we have an LI or hit the top
	while (obj.nodeName != "LI" && obj.nodeName != "HTML") {
		obj = obj.parentNode;
	}
	// Get the tasklist ul object
	var tasklist = obj.parentNode;
	// Save the contents of the LI in a temp variable.
	tasklist_temp_li.id = obj.id;
	tasklist_temp_li.innerHTML = obj.innerHTML;
	tasklist_temp_li.className = obj.className;

	editbox = tasklist_editbox (obj.id, obj.innerHTML);

	// Insert the editbox and remove the old LI
	tasklist.insertBefore (editbox, obj);
	tasklist.removeChild (obj);

	// We're done setting it all up, now let the user edit.
	editbox.lastChild.focus ();
	tasklist_editing = editbox;
}

function tasklist_NotSaved (obj) {
	// Delete the specified task
	if (obj.parentNode.lastChild.value != tasklist_temp_li.innerHTML) {
		if (confirm (HOAM_languageLookup ('errors|plugin|tasklist|unsaved'))) {
			if (obj) {
				tasklist_saveChanges (obj);
			} else {
				tasklist_saveChanges (tasklist_editing);
			}
		} else {
			tasklist_revertChanges ();
		}
	} else {
		tasklist_revertChanges ();
	}
}

function tasklist_catchSave (e) {
	// Save changes made to the text of the task
	if (!e) {
		var obj = window.event.srcElement;
	} else {
		var obj = e.target;
	}
	// Find the textarea. This will break if the textarea item is not
	// the last child of the editbox
	obj = obj.parentNode.lastChild;
	tasklist_saveChanges (obj);
	tasklist_editing = false;
}

function tasklist_catchUnload () {
	if (!tasklist_editing) return;
	var obj = $("editbox");
	tasklist_NotSaved (obj);
}

function tasklist_editbox (id, text) {
	// Create a container object for our editing.
	var editbox = document.createElement ("DIV");
	editbox.id = "editbox";
	editbox.className = "editbox";
	// Create the textarea so we can edit the task
	var textarea = document.createElement("TEXTAREA");
	// Save the old id from the LI
	if (id) {
		textarea.id = id;
	}
	// Get the text of the item on the tasklist
	if (text) {
		textarea.value = text;
	}

	// Create a button for modifying the duedate
	var buttonCalendar = document.createElement ("IMG");
	buttonCalendar.className = "tool-center";
	buttonCalendar.setAttribute ("alt", HOAM_languageLookup ('titles|plugin|tasklist|mini|calendar'));
	buttonCalendar.setAttribute ("src", "/hoam/images/icons/calendar.png");
	buttonCalendar.title = HOAM_languageLookup ('titles|plugin|tasklist|mini|calendar');

	// Create a button for canceling all modifications
	var buttonCancel = document.createElement ("SPAN");
	buttonCancel.className = "cancel tool-center";
	buttonCancel.innerHTML = HOAM_languageLookup ('field_names|plugin|tasklist|mini|cancel');
	buttonCancel.setAttribute ("alt", HOAM_languageLookup ('titles|plugin|tasklist|mini|cancel'));
	buttonCancel.title = HOAM_languageLookup ('titles|plugin|tasklist|mini|cancel');
	buttonCancel.onclick = tasklist_catchCancel;

	// Create a button for marking the item complete
	// For some reason, creating a text node and setting the value to
	// the checkmark resulted in firefox displaying the text of the string,
	// rather than decoding the &#10003;
	var buttonComplete = document.createElement ("SPAN");
	buttonComplete.innerHTML = HOAM_languageLookup ('field_names|plugin|tasklist|mini|complete');
	buttonComplete.className = "complete tool-left";
	buttonComplete.title = HOAM_languageLookup ('titles|plugin|tasklist|mini|complete');
	buttonComplete.onclick = tasklist_catchComplete;

	// Create a button for Deleting the task
	var buttonDelete = document.createElement ("SPAN");
	buttonDelete.innerHTML = HOAM_languageLookup ('field_names|plugin|tasklist|mini|delete');
	buttonDelete.className = "delete tool-right";
	buttonDelete.title = HOAM_languageLookup ('titles|plugin|tasklist|mini|delete');
	buttonDelete.onclick = tasklist_catchDelete;

	// Create a button for modifying the priority
	var buttonPriority = document.createElement ("IMG");
	buttonPriority.className = "tool-center";
	buttonPriority.setAttribute ("alt", HOAM_languageLookup ('titles|plugin|tasklist|mini|priority'));
	buttonPriority.setAttribute ("src", "/hoam/images/icons/arrow_switch.png");
	buttonPriority.title = HOAM_languageLookup ('titles|plugin|tasklist|mini|priority');

	// Create a button for saving the modified the task
	var buttonSave = document.createElement ("SPAN");
	buttonSave.innerHTML =  HOAM_languageLookup ('field_names|plugin|tasklist|mini|save');
	buttonSave.className = "tool-center";
	buttonSave.title = HOAM_languageLookup ('titles|plugin|tasklist|mini|save');
	// Not sure why, but using the eventworker.addhandler function to do this
	// (instead of assigning the value) doesn't work.
	buttonSave.onclick = tasklist_catchSave;

	// insert the task modify buttons. These are in the order they are because the default classes
	// (which are floats, etc) and formatting issues
	if (id) {
		// Only insert the Complete and Delete buttons if the task already exists (i.e. we're not creating a new task)
		editbox.appendChild (buttonDelete);
		editbox.appendChild (buttonComplete);
	}
	// Not implemented yet.
	// editbox.appendChild (buttonPriority);
	editbox.appendChild (buttonSave);
	editbox.appendChild (buttonCancel);
	// Not implemented yet.
	// editbox.appendChild (buttonCalendar);
	// !! This script will break if the textarea item is not the last child of the editbox
	editbox.appendChild (textarea);

	return (editbox);
}

function tasklist_new () {
	// Add a button to allow for creation of new tasks
	var tasklist = $("mini-tasklist");
	var buttonNew = document.createElement ("SPAN");
	buttonNew.className = "tool-left new";
	buttonNew.innerHTML = HOAM_languageLookup ('field_names|plugin|tasklist|mini|add');
	buttonNew.title = HOAM_languageLookup ('titles|plugin|tasklist|mini|add');
	buttonNew.onclick = tasklist_catchAdd;
	tasklist.insertBefore (buttonNew, tasklist.firstChild);
}

function tasklist_none (obj) {
	// Are there any LI elements (tasks) in the list?
	var li = obj.getElementsByTagName ("LI");
	if (li.length == 0) {
		var text = obj.parentNode;
		text.lastChild.nodeValue = HOAM_languageLookup ('errors|plugin|tasklist|none');
	} else {
	}
}

function tasklist_revertChanges (obj) {
	// Revert Changes made to a task
	var editbox = $("editbox");
	var tasklist = editbox.parentNode;
	tasklist.insertBefore (tasklist_temp_li, editbox);
	tasklist.removeChild (editbox);
}

function tasklist_saveChanges (obj) {
	// Save changes made to the text of the task
	// Find the textarea. This will break if the textarea item is not
	// the last child of the editbox
	obj = obj.parentNode.lastChild;
	// In order for the data to appear to PHP as a standard form submit, we need to set the content type and format it appropriately
	url = "/hoam/plugins/tasklist/edit_script.php";
	if (obj.id) {
		pars = "&modify=1&id=" + obj.id + "&text=" + encodeURIComponent (obj.value);
	} else {
		pars = "&add=1&text=" + encodeURIComponent (obj.value);
	}
	var myAjax = new Ajax.Request (url, {method: 'post', parameters: pars, onComplete: tasklist_update});
}

function tasklist_update (originalRequest) {
	// Take the response from the server, and insert all of the tasks returned from the server.
	// Then remove the editbox.
	var editbox = $("editbox");
	var tasklist = editbox.parentNode;
	if (originalRequest.responseXML) {
		var tasks = originalRequest.responseXML.getElementsByTagName ("task");
		for (var i = 0; i < tasks.length; i++) {
			var newli = document.createElement ("LI");
			newli.id = getElementTextNS ("", "id", tasks[i], 0);
			newli.className = getElementTextNS ("", "class", tasks[i], 0);
			newli.innerHTML = getElementTextNS ("", "text", tasks[i], 0);
			tasklist.insertBefore (newli, editbox);
		}
	}
	// Remove the editbox.
	tasklist.removeChild (editbox);
	// Doublecheck whether there are any tasks listed;
	tasklist_none (tasklist);
	
	// Done
	tasklist_editing = false;
}

function HOAM_tasklistInit () {
	if ($('mini-tasklist')) {
		tasklist_new ();
		Element.observe ($('mini-tasklist'), 'dblclick', tasklist_catchEdit, false);
		Element.observe (window, 'beforeunload', tasklist_catchUnload, false);
	}
}

Event.observe (window, 'load', HOAM_tasklistInit);
