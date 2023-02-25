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

function HOAM_configInit () {
	if ($j('#config\\|export_button')) {
		$j('#config\\|export_button').on ('click', function (event) {
			event.preventDefault();
			HOAM_configCreateBackup ();
		});
	}

	if ($('configuration_form')) {
		var messageboard_counter = 0;
		
		Event.observe ($('configuration_form'), 'submit', function () {
				setSelectAll ('config|wiki|default_groups');
		});
		Element.observe ($('attachment|preview|enable'), 'change', HOAM_configAttachmentPreviewEnable);
		Element.observe ($('budget|assessment|time'), 'change', HOAM_configBudgetAssessmentUpdate);
		Element.observe ($('config|budget|insurance|enable'), 'change', HOAM_configBudgetInsuranceEnable);
		Element.observe ($('config|budget|paypal|enable'), 'change', HOAM_configBudgetPayPalEnable);
		Element.observe ($('config|email|auth|enable'), 'change', HOAM_configEmailAuthEnable);
		Element.observe ($('config|email|automated'), 'change', HOAM_configEmailAutomatedEnable);
		Element.observe ($('log|destination|file'), 'change', HOAM_configLogFileEnable);
		Element.observe ($('log|destination|syslog'), 'change', HOAM_configLogSyslogEnable);
		Element.observe ($('config|messageboard|add'), 'click', function () {
			HOAM_configMessageboardAdd (messageboard_counter++);
		});
		Element.observe ($('config|violation|require_approval'), 'change', HOAM_configViolationRequireApproval);
		Element.observe ($('config|wiki|group|remove'), 'click', function () {
			HOAM_moveOption ('configuration_form', 'config|wiki|all_groups', 'config|wiki|default_groups');
		});
		Element.observe ($('config|wiki|group|add'), 'click', function () {
			HOAM_moveOption ('configuration_form', 'config|wiki|default_groups', 'config|wiki|all_groups');
		});
		Element.observe ($('config|work_request|require_approval'), 'change', HOAM_configWorkRequestRequireApproval);

		if ($j('#attachment\\|preview\\|delete')) {
			$j('#attachment\\|preview\\|delete').on ('click', function (event) {
				event.preventDefault();
				HOAM_configPreviewDelete ();
				});
			HOAM_configPreviewUpdate ();
		}

		HOAM_configAttachmentPreviewEnable ();
		HOAM_configBudgetAssessmentUpdate ();
		HOAM_configBudgetInsuranceEnable ();
		HOAM_configBudgetPayPalEnable ();
		HOAM_configEmailAutomatedEnable ();
		HOAM_configLogFileEnable ();
		HOAM_configLogSyslogEnable ();
		HOAM_configMessageboardButton ()
		HOAM_configViolationRequireApproval ();
		HOAM_configWorkRequestRequireApproval ();
	}
}

function HOAM_configPreviewDelete () {
	$j.ajax ({
    type: "GET",
    url: '/hoam/scripts/attachment/xml_delete_previews.php',
    data: 'delete',
    dataType: 'xml',
    success: function (xml) {
    	$j('#attachment\\|preview\\|delete').prop('disabled', true);
    	$j('#attachment\\|preview\\|count').html ($j('count', xml).text());
    	$j('#attachment\\|preview\\|size').html ($j('size', xml).text());
		$j('#attachment\\|preview\\|delete').html ($j('status', xml).text());
	}
	});
}

function HOAM_configAttachmentPreviewEnable () {
	if ($('attachment|preview|enable').checked) {
		$('preview_enable').show();
	} else {
		$('preview_enable').hide();
	}
}

function HOAM_configCreateBackup () {
	$j('#config\\|export_button').prop('disabled', true);
	$j('#config\\|export_attachments').prop('disabled', true);
	if ($j('#config\\|export_attachments').prop('checked') == true) {
		$j('#config\\|export_button').html ('Creating Backup');
		$j.ajax ({
    	url: '/hoam/scripts/upgrade/xml_export.php',
    	data: 'attachments',
    	dataType: 'xml',
    	success: function (xml) {
   			var filename = $j('filename', xml).text();
    		if (filename != '') {
    			var size = $j('size', xml).text();
				$j('#config\\|export\\|fieldset').append('<br /><br /><a href="/backup/' + filename + '">Download (' + size + ')</a>');
			} else {
				$j('#config\\|export_button').html ('Export failed, please check system logs.');
			}
		}
		});
	} else {
		$j('#config\\|export_button').html ('Creating Backup');
		$j.ajax ({
    	url: '/hoam/scripts/upgrade/xml_export.php',
    	dataType: 'xml',
    	success: function (xml) {
   			var filename = $j('filename', xml).text();
    		if (filename != '') {
    			var size = $j('size', xml).text();
				$j('#config\\|export\\|fieldset').append('<br /><br /><a href="/backup/' + filename + '">Download (' + size + ')</a>');
			} else {
				$j('#config\\|export_button').html ('Export failed, please check system logs.');
			}
		}
		});
	}
}

function HOAM_configPreviewUpdate () {
	$j.ajax ({
    type: "GET",
	url: '/hoam/scripts/attachment/xml_delete_previews.php',
	dataType: 'xml',
	success: function (xml) {
		$j('#attachment\\|preview\\|count').html($j('count', xml).text());
		if ($j('count', xml).text() == '0') {
			// If there aren't any previews to delete, just disable the button.
			$j('#attachment\\|preview\\|delete').prop('disabled', true);
		}
		$j('#attachment\\|preview\\|size').html($j('size', xml).text());
	}
	});
}

function HOAM_configBudgetAssessmentUpdate () {
	switch ($F('budget|assessment|time')) {
		case 'days':
			$('budget|assessment|days_due').show ();
			$('budget|assessment|days_due').previousSibling.show ();
			$('budget|assessment|days_due').nextSibling.show ();
			$('budget|assessment|days_late').show ();
			$('budget|assessment|days_late').previousSibling.show ();
			$('budget|assessment|days_late').nextSibling.show ();
			$('budget|assessment|months_due').hide ();
			$('budget|assessment|months_due').previousSibling.hide ();
			$('budget|assessment|months_due').nextSibling.hide ();
			$('budget|assessment|months_late').hide ();
			$('budget|assessment|months_late').previousSibling.hide ();
			$('budget|assessment|months_late').nextSibling.hide ();
			break;
		case 'months':
			$('budget|assessment|days_due').hide ();
			$('budget|assessment|days_due').previousSibling.hide ();
			$('budget|assessment|days_due').nextSibling.hide ();
			$('budget|assessment|days_late').hide ();
			$('budget|assessment|days_late').previousSibling.hide ();
			$('budget|assessment|days_late').nextSibling.hide ();
			$('budget|assessment|months_due').show ();
			$('budget|assessment|months_due').previousSibling.show ();
			$('budget|assessment|months_due').nextSibling.show ();
			$('budget|assessment|months_late').show ();
			$('budget|assessment|months_late').previousSibling.show ();
			$('budget|assessment|months_late').nextSibling.show ();
			break;
	}
}

function HOAM_configBudgetInsuranceEnable () {
	if ($('config|budget|insurance|enable').checked) {
		$('insurance_enable').show();
	} else {
		$('insurance_enable').hide();
	}
}

function HOAM_configBudgetPayPalEnable () {
	if ($('config|budget|paypal|enable').checked) {
		$('paypal_enable').show();
	} else {
		$('paypal_enable').hide();
	}
}

function HOAM_configEmailAuthEnable () {
	if ($('config|email|auth|enable').checked) {
		$('email_auth_enable').show();
	} else {
		$('email_auth_enable').hide();
	}
}

function HOAM_configEmailAutomatedEnable () {
	if ($('config|email|automated').checked) {
		$('email_automated_enable').show();
	} else {
		$('email_automated_enable').hide();
	}
}

function HOAM_configLogFileEnable () {
	if ($('log|destination|file').checked) {
		$('log_file_enable').show();
	} else {
		$('log_file_enable').hide();
	}
}

function HOAM_configLogSyslogEnable () {
	if ($('log|destination|syslog').checked) {
		$('log_syslog_enable').show();
	} else {
		$('log_syslog_enable').hide();
	}
}

function HOAM_configMessageboardAdd (messageboard_counter) {
	// Clone the attachment code, so we don't have to kepe reloading it.
	var fieldset = document.createElement ('FIELDSET');
	var name = document.createElement ('INPUT');
	name.setAttribute ('name', 'messageboard' + messageboard_counter);
	name.setAttribute ('type', 'text');
	var button = document.createElement ('BUTTON');
	button.setAttribute ('type', 'button');
	button.innerHTML = HOAM_languageLookup ('form_buttons|delete');
	Element.observe (button, 'click', function () {
		this.parentNode.parentNode.removeChild(this.parentNode);
	});
	fieldset.appendChild (name);
	fieldset.appendChild (button);
	$('config|messageboard|add').parentNode.appendChild (fieldset);
}

function HOAM_configMessageboardButton () {
	var button_fields = $('page_content').getElementsByTagName ('BUTTON');
    if (button_fields.length > 0) {
    	for (var i = 0; i < button_fields.length; i++) {
    		field_type = button_fields[i].getAttribute ('field');
    	    // Look for specific fields and apply validation where applicable
    		switch (field_type) {
    			case 'delete':
    				// If found, create an event tracker for the button
    				// If it's currently a delete button, on click show a popup
    				// asking if they're sure they want to delete the board,
    				// then change the input to text-decoration: line-through
    				// and this button to an undelete button in case they want
    				// to go back before submitting the changes.
    				break;
    			case 'undelete':
    				// Essentially do the reverse of he above, except do not
    				// need to show any type of popup asking for confirmation.
    				break;
    			default:
    				// No default action.
    				break;
    		}
    	}
    }
}

function HOAM_configViolationRequireApproval () {
	if ($('config|violation|require_approval').checked) {
		$('violation|approval').show();
		$('violation|approval|flags').show();
	} else {
		$('violation|approval').hide();
		$('violation|approval|flags').hide();
	}
}

function HOAM_configWorkRequestRequireApproval () {
	if ($('config|work_request|require_approval').checked) {
		$('work_request|approval').show();
		$('work_request|approval|flags').show();
	} else {
		$('work_request|approval').hide();
		$('work_request|approval|flags').hide();
	}
}

Event.observe (window, 'load', HOAM_configInit);
