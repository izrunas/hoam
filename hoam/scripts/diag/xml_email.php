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

session_start();

$group_members_class = new HOAM_group_members ('group_members');
 
if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id'])) {
	if (isset ($_POST['address'])) {
		$wiki = new HOAM_wiki_parser ();
		$xw = xmlwriter_open_memory ();
		xmlwriter_set_indent ($xw, 1);
		xmlwriter_set_indent_string ($xw, ' ');
		xmlwriter_start_document ($xw, '1.0', 'UTF-8');
		// A first element
		xmlwriter_start_element ($xw, 'ajax-response');
		xmlwriter_start_element ($xw, 'item');
		if (filter_var ($_POST['address'], FILTER_VALIDATE_EMAIL)) {
			global $_HOAM_language, $_HOAM_log, $_HOAM_setting;

			require_once (HOAM_PATH . '3rdparty/phpmailer/class.phpmailer.php');
			require_once (HOAM_PATH . '3rdparty/phpmailer/PHPMailerAutoload.php');
			require_once (HOAM_PATH . '3rdparty/phpmailer/class.smtp.php');
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
			$mail -> AddAddress ($_POST['address']);
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
			$mail -> Subject = date ($_HOAM_country ['date']['format_mysql']) . ' ' . $title . ' :: ' . $_HOAM_language ['field_names']['diag']['email'];
			$mail -> MsgHTML ($wiki -> parse ($_HOAM_language ['help_pages']['email']['test_html'] . $_HOAM_language ['help_pages']['generic']['do_not_reply']));
			$mail -> AltBody = $_HOAM_language ['field_names']['email']['text_only'];
			
			if (!$mail -> Send ()) {
  				$_HOAM_log -> add ("PHPMailer Error: " . $mail -> ErrorInfo, HOAM_MESSAGE_WARNING);
				xmlwriter_write_element ($xw, 'status', '1');
				xmlwriter_start_element ($xw, 'error');
				xmlwriter_write_cdata ($xw, $mail -> ErrorInfo);
				xmlwriter_end_element ($xw); // error
			} else {
				xmlwriter_write_element ($xw, 'status', '0');
				xmlwriter_start_element ($xw, 'error');
				xmlwriter_write_cdata ($xw, $mail -> ErrorInfo);
				xmlwriter_end_element ($xw); // error
			}
		} else {
			xmlwriter_write_element ($xw, 'status', '1');
				xmlwriter_start_element ($xw, 'error');
				xmlwriter_write_cdata ($xw, $_HOAM_language ['errors']['generic']['invalid-email']);
				xmlwriter_end_element ($xw); // error
		}
		// Generate the XML file and echo it back to the client
		xmlwriter_end_element ($xw); // item
		xmlwriter_end_element ($xw); // ajax-response
		xmlwriter_end_document ($xw);
		header ('Content-type: text/xml');
		echo xmlwriter_output_memory ($xw);
	} else {
		// We don't know what action we're being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	header ('Location: /error/403/');
}

exit ();

?>
