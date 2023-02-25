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

// This isn't needed when manually running from within HOAM, but is needed if
// running scheduled.

// NOTE!
// Setting the below to require_once causes PHP to hang/crash for some reason.
// include_once ('../../hoam-startup.php');
/* This is a HOAM maintenance script that should be scheduled to run nightly
 * via cron or similar scheduling service.
 *
 * Ideally, long-term, this script is planned to perform several tasks, several
 * of which will require yet-to-be-built calendaring support. The plan is to
 * have a report emailed nightly to interested parties (selectable).
 *   1) Budget
 *     **DONE** a) check for delinquent assessments, and handle them accordingly (apply
 *        interest, late fees, etc.)
 *     **DONE** b) create new dues assessments on the appropriate schedule.
 *     c) Create late notices / letters as needed and have them queued for
 *        approval to be mailed.
 *     d) Look for expected payments (via a payment plan) from delinquent
 *        homeowners and make a note.
 *     e) Create a list of bills that are due and not yet paid.
 *  2) Groups
 *     a) Report any new groups since the last maintenance run.
 *     b) Report new group members since the last maintenance run.
 *  3) Homeowners
 *     **DONE** a) Report on any homeowner changes
 *     b) If any changes have happened to an individual's account (such as a
 *        violation, fine, etc., send an email to that homeowner asking them to
 *        login and review the changes.
 *        1) This is a good candidate for including on the 'events since last
 *        login' notice to users. 
 *  4) Messageboard
 *     a) If new messages were posted to the site, provide a brief summary
 *        (date, time, subject, user id), or just a generic count of new
 *        messages for each board if it's excessive.
 *        1) This is a good candidate for including on the 'events since last
 *        login' notice to users. 
 *  5) News
 *     a) If any news was posted to the site, send an email to the appropriate
 *        address (residents@) including it.
 *        1) This is a good candidate for including on the 'events since last
 *        login' notice to users. 
 *  6) Users
 *     a) Check for blank or otherwise invalid user names.
 *     **DONE** b) Disable or delete accounts that haven't been logged into in 'x' days.
 *     **DONE** c) Check for accounts with homeowner privileges where the home has
 *        subsequently been sold.
 *     **DONE** d) Report back on users requesting validation.
 *     **DONE** e) Report on all of the users who accessed the site that day.
 *  7) Violations
 *     a) Report on any violations that should be checked up on (see whether
 *        they have been resolved).
 *     b) Look for violations that haven't been resolved, and apply fines if
 *        necessary.
 *  8) General housekeeping
 *     a) Run a sanity check on the database:
 *       **DONE** 1) Make sure there are no homeowners missing a purchase date or other
 *          critial information
 *       2) Make sure there are no budget categories with an unknown parent
 *       3) Make sure there are no budget entries without a valid account,
 *          category, customer, invoice, parent, user, or vendor ids. 
 *       4) Check attachments, report back where files exists but no matching
 *          db entry, or a db entry and no matching files.
 *       **DONE** 5) Report on any gaps in the logs (missing sequence numbers).
 *       **DONE** 6) Have mysql check the database tables themselves for errors, ie
 *          "CHECK TABLE budget EXTENDED".
 *       7) etc...
 *     b) Create a general report on the site itself (# of users logged in
 *        yesterday, new users, new messages, new news articles, changes to
 *        articles (edits / post_start / post_end), votes, log errors, etc.)
 *     c) Group maintenance:
 *        1) Check for valid groups names, flags, other attributes (ownership,
 *        etc.)
 *        2) Check for valid group members, make sure that all members listed
 *        actually exist (user accounts and groups weren't deleted and
 *        membership entries not cleaned up).
 *        3) Check all of the groups listed, and note any that are /not/ part of
 *        the default configuration. This wouldn't be to indiciate an error of
 *        any kind, but could show as a potential tampering / security issue.
 *     ** DONE ** 4) Check for group_members with a NULL member_id. 
 * 
 */
 
// If the script is being called from the cli (eg, a scheduled task via cron),
// then skip the permissions check.
$permitted = FALSE;
// We'd normally call isCLI() here, but since it doesn't exist yet if we're
// running the script from cron, we have to check manually. 
if (php_sapi_name () == 'cli' && empty ($_SERVER['REMOTE_ADDR'])) {
	// Because pretty much everything in HOAM is based on the web server
	// accessing it, we're going to force the script to the working dir when
	// running CLI.
	chdir (realpath (dirname (__FILE__)));
	require ('../../hoam-startup.php');
	$permitted = TRUE;
} else {
	if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
		($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
		global $_HOAM_config, $_HOAM_log;
		
		$permitted = TRUE;
	}
}

if ($permitted) {
	// Do a test to see if system is in maintenance mode 
	$sleep_count = 0;
	while (($_HOAM_setting ['hoam']['updating'] == 1) AND $sleep_count < 12) {
		echo 'System currently in maintenance mode, waiting 5 minutes before trying again.<br />';
		sleep (360);
		$sleep_count++;
	}
	if ($sleep_count == 12) {
		echo 'Could not perform daily system maintenance routine, system was in maintenance mode (tried for 1 hour).<br />';
		exit ();
	}
	require_once (HOAM_PATH_FUNCTION . 'maintenance.php');
	$maintenance_report = '';
	$_HOAM_log -> add ('Beginning system maintenance.', HOAM_MESSAGE_NOTICE);
	$maintenance_report .= '<h2>System Maintenance</h2>';
	$maintenance_report .= HOAM_maintenanceBudget ();
	$maintenance_report .= HOAM_maintenanceHomeowners ();
	if ($_HOAM_setting ['budget']['insurance']['enable']) {
		$_HOAM_log -> add ('Budget maintenance: checking insurance policies.', HOAM_MESSAGE_NOTICE);
		$maintenance_report .= HOAM_maintenanceInsurance ();
	} else {
		$_HOAM_log -> add ('Budget maintenance: tracking of insurance policies is not enabled.', HOAM_MESSAGE_NOTICE);
	}
	$maintenance_report .= HOAM_maintenanceNews ();
	$maintenance_report .= HOAM_maintenanceUsers ();
	$maintenance_report .= HOAM_maintenanceViolations ();
	$_HOAM_log -> add ('System maintenance complete.', HOAM_MESSAGE_NOTICE);

	require_once (HOAM_PATH_FUNCTION . 'housekeeping.php');
	$_HOAM_log -> add ('Beginning system housekeeping.', HOAM_MESSAGE_NOTICE);
	$maintenance_report .= '<h2>System Housekeeping</h2>';
	$maintenance_report .= HOAM_housekeepingAttachments ();
	$maintenance_report .= HOAM_housekeepingGroups ();
	$maintenance_report .= HOAM_housekeepingHomeowners ();
	$maintenance_report .= HOAM_housekeepingViolations ();
	$maintenance_report .= HOAM_housekeepingWiki ();
	$maintenance_report .= HOAM_housekeepingLog ();
	$maintenance_report .= HOAM_housekeepingDatabase ();
	$maintenance_report .= HOAM_housekeepingInstall ();
	$_HOAM_log -> add ('System housekeeping complete.', HOAM_MESSAGE_NOTICE);

	$maintenance_report .= '{{END_REPORT}}';

	$_HOAM_log -> add ('Updating configuration to reflect last maintenance date: ' . date ($_HOAM_country ['date']['format_mysql']), HOAM_MESSAGE_NOTICE);
	$_HOAM_config -> setValue ('hoam/last_maintenance_run', date ($_HOAM_country ['date']['format_mysql']));

	// If we're viewing the report from inside the website (ie, running it
	// manually, then the output will just be appended to the internal $HTML)
	if (isCLI ()) {
		$wiki = new HOAM_wiki_parser ();
	} else {
		global $wiki;
	}
	$maintenance_report = $wiki -> parse ($maintenance_report);
	if (isCLI ()) {
		// Only send an email if the feature is enabled.
		if ($_HOAM_setting ['email']['automated']) {
			require_once (HOAM_PATH . '3rdparty/phpmailer/class.phpmailer.php');
			$mail = new PHPMailer ();

			$mail -> IsSMTP (); // telling the class to use SMTP
			$mail -> SMTPDebug = HOAM_DEBUG; // Use the same setting as HOAM
        			                    // 1 = errors and messages
		        	                    // 2 = messages only
			if ($_HOAM_setting ['email']['auth']) { // enable SMTP authentication
				$mail -> SMTPAuth = TRUE;
				$mail -> Username = $wiki -> parse ($_HOAM_setting ['email']['username']);
				$mail -> Password = $_HOAM_setting ['email']['password'];
				// If we're authenticating, then we know we have a username.
				$mail -> SetFrom ($wiki -> parse ($_HOAM_setting ['email']['username']));
			} else {
				// Since we don't know that we have a username configured (one
				// would only be there if authentication was previously
				// configured and then disabled), use the address of the
				// management company.
				$mail -> SetFrom ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
			}
			$mail -> Host = $_HOAM_setting ['email']['server'];
			$mail -> Port = $_HOAM_setting ['email']['port'];

			$mail -> AddReplyTo ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));
			$mail -> AddAddress ($wiki -> parse ($_HOAM_setting ['organization']['email']['management']));

			// Determine the subject line to use on the message
			if (empty ($_HOAM_setting ['website']['title'])) {
				if (!empty ($_HOAM_setting ['organization']['name'])) {
					$title = $_HOAM_setting ['organization']['name'];
				} else {
					$title = 'HOAM';
				}
			} else {
				$title = $_HOAM_setting ['website']['title'];
			}
			$mail -> Subject = date ($_HOAM_country ['date']['format_mysql']) . ' ' . $title . ' :: ' . $_HOAM_language ['field_names']['reports']['automated_maintenance'];
			unset ($title);

			if ($_HOAM_setting ['email']['do_not_reply']) {
				$maintenance_report .= $_HOAM_language ['help_pages']['generic']['do_not_reply'];
			}
			$mail -> MsgHTML ($wiki -> parse ($maintenance_report));
			$mail -> AltBody = $_HOAM_language ['field_names']['email']['text_only'];

			if (!$mail -> Send ()) {
	  			$_HOAM_log -> add ("PHPMailer Error: " . $mail -> ErrorInfo, HOAM_MESSAGE_WARNING);
				exit;
			}
		}
	}
} else {
	header ('Location: /error/403/');
	exit ();
}

?>
