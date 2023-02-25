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

var upgrade_backup_completed = false;

function HOAM_adminUpgradeInit () {
	if ($j('#upgrade\\|cancel')) {
		$j('#upgrade\\|cancel').on ('click', function (event) {
			event.preventDefault ();
			HOAM_adminUpgradeMaintenanceToggle (0);
			window.location.href = '/website/';
		});
	}
	if ($j('#upgrade\\|cancel')) {
		$j('#upgrade\\|cancel').on ('click', function (event) {
			event.preventDefault ();
			HOAM_adminUpgradeMaintenanceToggle (0);
			window.location.href = '/website/';
		});
	}
	if ($j('#upgrade\\|custom_download')) {
		$j('#upgrade\\|custom_download').on ('click', function (event) {
			event.preventDefault ();
			HOAM_adminUpgradePrepare ($j('#upgrade\\|custom_url').val());
		});
	}
	if ($j('#upgrade\\|download')) {
		$j('#upgrade\\|download\\|yes').on ('click', function (event) {
			event.preventDefault ();
			HOAM_adminUpgradeMaintenanceToggle (1);
			HOAM_adminUpgradePrepare ();
		});
		$j('#upgrade\\|download\\|no').on ('click', function (event) {
			event.preventDefault ();
			HOAM_adminUpgradeMaintenanceToggle (0);
			window.location.href = '/website/';
		});
	}
	if ($j('#upgrade\\|export_button')) {
		$j('#upgrade\\|export_button').on ('click', function (event) {
			event.preventDefault ();
			HOAM_adminUpgradeExport ();
		});
	}
	if ($j('#upgrade\\|log')) {
		$j('#upgrade\\|log').on ('click', function (event) {
			event.preventDefault ();
			HOAM_adminUpgradeLog ();
		});
	}
	if ($j('#upgrade\\|start')) {
		$j('#upgrade\\|start').on ('click', function (event) {
			event.preventDefault ();
			HOAM_adminBackupCreated ();
			HOAM_adminUpgradeStart ();
		});
	}
}

function HOAM_adminBackupCreated () {
	if (!upgrade_backup_completed) {
		var answer = confirm ('Start upgrade without performing backup?');
		if (answer) {
			$j('#upgrade\\|status').append ('<li class="warning">Backup skipped.</li>');
		} else {
			$j('#upgrade\\|tabber').children(':first').children(':nth-child(4)').children(':first').click();
    		HOAM_adminUpgradeExport ();
		}
	}
}

function HOAM_adminUpgradeDecompress (filename) {
	var success = 0;
	return new Promise ((resolve, reject) => {
		$j.ajax ({
		async: false,
   		url: '/hoam/scripts/upgrade/xml_decompress.php',
		data: 'filename=' + filename,
    	dataType: 'xml',
		success: function (xml) {
   			success = $j('status', xml).text();
   		}});
		if (success) {
			$j('#upgrade\\|status').append ('<li>Decompressed upgrade package.</li>');
			resolve (true);
		} else {
			$j('#upgrade\\|status').append ('<li>Could not decompress upgrade package.</li>');
			reject (false);
		}
	});
}

function HOAM_adminUpgradeDownload (custom_package) {
	var filename = '';
	return new Promise ((resolve, reject) => {
		if (custom_package) {
			$j.ajax ({
			async: false,
			url: '/hoam/scripts/upgrade/xml_download.php',
			data: 'url=' + custom_package,
			dataType: 'xml',
			success: function (xml) {
   				filename = $j('filename', xml).text();
   			}});
   		} else {
			$j.ajax ({
			async: false,
			url: '/hoam/scripts/upgrade/xml_download.php',
			dataType: 'xml',
			success: function (xml) {
   				filename = $j('filename', xml).text();
   			}});
   		}
		if (filename) {
			$j('#upgrade\\|status').append ('<li>Downloaded upgrade package "' + filename + '".</li>');
			resolve (filename);
		} else {
			$j('#upgrade\\|status').append ('<li class="error">Could not download upgrade package.</li>');
			reject (false);
		}
	});
}

function HOAM_adminUpgradeExport () {
	$j('#upgrade\\|export_button').prop('disabled', true);
	$j('#upgrade\\|export_attachments').prop('disabled', true);
	$j('#upgrade\\|export_button').html ('Creating Backup');
	if ($j('#upgrade\\|export_attachments').prop('checked') == true) {
		$j.ajax ({
		type: "POST",
		url: '/hoam/scripts/upgrade/xml_export.php',
		data: 'attachments',
		dataType: 'xml',
		success: function (xml) {
			var filename = $j('filename', xml).text();
			if (filename != '') {
				var size = $j('size', xml).text();
				$j('#upgrade\\|export').children(':first').append('<br /><br /><a href="/backup/' + filename + '">Download (' + size + ')</a>');
				$j('#upgrade\\|status').append ('<li>Backup created (<a href="/backup/' + filename + '">Download (' + size + ')</a>.</li>');
				upgrade_backup_completed = true;
			} else {
				$j('#upgrade\\|export_button').html ('Export failed, please check system logs.');
			}
		}
		});
	} else {
		$j.ajax ({
		type: "POST",
		url: '/hoam/scripts/upgrade/xml_export.php',
		dataType: 'xml',
		success: function (xml) {
			var filename = $j('filename', xml).text();
			if (filename != '') {
				var size = $j('size', xml).text();
				$j('#upgrade\\|export').children(':first').append('<br /><br /><a href="/backup/' + filename + '">Download (' + size + ')</a>');
				$j('#upgrade\\|status').append ('<li>Backup created (<a href="/backup/' + filename + '">Download (' + size + ')</a>.</li>');
				upgrade_backup_completed = true;
			} else {
				$j('#upgrade\\|export_button').html ('Export failed, please check system logs.');
			}
		}
		});
	}
	HOAM_adminUpgradeLog ();
}

function HOAM_adminUpgradeLog (complete) {
	if (complete) {
		$j.ajax ({
	    url: '/hoam/scripts/upgrade/xml_log_update.php',
	    data: 'complete',
    	dataType: 'xml',
    	success: function (xml) {
			$j('#upgrade\\|log').children(':first').html ($j('log', xml).text());
			HOAM_tableStripe ();
		}
		});
	} else {
		$j.ajax ({
	    url: '/hoam/scripts/upgrade/xml_log_update.php',
    	dataType: 'xml',
    	success: function (xml) {
			$j('#upgrade\\|log').children(':first').html ($j('log', xml).text());
			HOAM_tableStripe ();
		}
		});
	}
}

function HOAM_adminUpgradeMaintenanceToggle (setting) {
	$j.ajax ({
	async: false,
	type: 'POST',
    url: '/hoam/scripts/diag/xml_maintenance.php',
    data: 'toggle=' + setting,
    dataType: 'xml',
    success: function (xml) {
		toggle = $j('toggle', xml).text();
   	}});
	if (toggle) {
		$j('#upgrade\\|status').append ('<li>Maintenance mode enabled.</li>');
	} else {
		$j('#upgrade\\|status').append ('<li>Maintenance mode disabled.</li>');
	}
}

function HOAM_adminUpgradeGetInfo (category) {
	$j.ajax ({
    url: '/hoam/scripts/upgrade/xml_return_info.php',
    data: category,
    dataType: 'xml',
    success: function (xml) {
		$j('#upgrade\\|' + category).children(':first').html ($j('info', xml).text());
	}
	});
}

async function HOAM_adminUpgradePrepare (custom_url) {
	try {
		$j('#upgrade\\|download\\|fieldset').hide();
		$j('#upgrade\\|custom_package').hide();
		$j('#upgrade\\|cancel\\|fieldset').show();
		$j('#upgrade\\|tabber').show();
		HOAM_tabLoading ($('upgrade|notice').firstChild);
		HOAM_tabLoading ($('upgrade|changes').firstChild);
		HOAM_tabLoading ($('upgrade|details').firstChild);
		HOAM_tabLoading ($('upgrade|log').firstChild);
		if (custom_url) {
			var filename = await HOAM_adminUpgradeDownload (custom_url);
		} else {
			var filename = await HOAM_adminUpgradeDownload ();
		}
		var success = await HOAM_adminUpgradeDecompress (filename);
		HOAM_adminUpgradeGetInfo ('changes');
		HOAM_adminUpgradeGetInfo ('details');
		HOAM_adminUpgradeGetInfo ('notice');
		var success = await HOAM_adminUpgradeVersionCheck ();
	} catch (error) {
		$j('#upgrade\\|start').prop('disabled', true);
		alert ('Upgrade package not available or invalid.');
	}
	HOAM_adminUpgradeLog ();
}

function HOAM_adminUpgradeProcessCode (check, stage) {
	var success = '';
	return new Promise ((resolve, reject) => {
		$j.ajax ({
		async: false,
		url: '/hoam/scripts/upgrade/xml_process_code.php',
		data: check + '=' + stage,
		dataType: 'xml',
		success: function (xml) {
   			success = $j('status', xml).text();
   		}});
		if (success) {
			$j('#upgrade\\|status').append ('<li>' + check + ' ' + stage + ' completed.</li>');
			resolve (success);
		} else {
			$j('#upgrade\\|status').append ('<li class="error">' + check + ' '+ stage + ' failed.</li>');
			reject (false);
		}
	});
}

function HOAM_adminUpgradeProcessFiles (stage) {
	var success = '';
	return new Promise ((resolve, reject) => {
		$j.ajax ({
		async: false,
		url: '/hoam/scripts/upgrade/xml_process_files.php',
		data: stage,
		dataType: 'xml',
		success: function (xml) {
   			success = $j('status', xml).text();
   		}});
		if (success) {
			$j('#upgrade\\|status').append ('<li>File ' + stage + ' completed.</li>');
			resolve (success);
		} else {
			$j('#upgrade\\|status').append ('<li class="error">File ' + stage + ' failed.</li>');
			reject (false);
		}
	});
}

async function HOAM_adminUpgradeStart () {
	try {
		$j('#upgrade\\|start').prop('disabled', true);
		var status = await HOAM_adminUpgradeProcessCode ('PHP', 'pre-check');
		var status = await HOAM_adminUpgradeProcessCode ('SQL', 'pre-check');
		HOAM_adminUpgradeLog ();
		var status = await HOAM_adminUpgradeProcessCode ('PHP', 'migration');
		var status = await HOAM_adminUpgradeProcessCode ('SQL', 'migration');
		HOAM_adminUpgradeLog ();
		var status = await HOAM_adminUpgradeProcessFiles ('copy');
		var status = await HOAM_adminUpgradeProcessFiles ('delete');
		HOAM_adminUpgradeLog ();
		var status = await HOAM_adminUpgradeProcessCode ('PHP', 'post-check');
		var status = await HOAM_adminUpgradeProcessCode ('SQL', 'post-check');
		HOAM_adminUpgradeLog ();
		var status = await HOAM_adminUpgradeProcessCode ('PHP', 'cleanup');
		var status = await HOAM_adminUpgradeProcessCode ('SQL', 'cleanup');
		HOAM_adminUpgradeLog (true);
		HOAM_adminUpgradeMaintenanceToggle (0);
	} catch (error) {
		alert ('Upgrade failed, please check system logs. System left in maintenance mode.');
	}
	HOAM_adminUpgradeLog ();
}

function HOAM_adminUpgradeVersionCheck () {
	var version;
	return new Promise ((resolve, reject) => {
		$j.ajax ({
		async: false,
	    url: '/hoam/scripts/upgrade/xml_version_check.php',
	    dataType: 'xml',
	    success: function (xml) {
			version = ($j('version', xml).text());
		}
		});
		if (version == 1) {
			resolve (true);
		} else {
			$j('#upgrade\\|status').append ('<li class="error">Upgrade package is not for this version of HOAM.</li>');
			reject (false);
		}
	});
}

$j(document).ready (HOAM_adminUpgradeInit);
