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

if (isset ($_POST['username']) AND isset ($_POST['password']) AND isset ($_POST['answer'])) {
	$user_class = new HOAM_user ('users');
	
	$trusted_username = $user_class -> superClean ($_POST['username'], $user_class -> returnColumnSize ('accountname'));
	$trusted_password = $user_class -> superClean ($_POST['password'], $user_class -> returnColumnSize ('password'));
	$trusted_answer = $user_class -> superClean  ($_POST['answer'], $user_class -> returnColumnSize ('password_hint'));
	if ($user_class -> accountExists ($trusted_username)) {
		if ($user_class -> authenticateUser ($trusted_username, $trusted_password)) {
			$trusted_id = $user_class -> returnId ($trusted_username);
			if (mb_strtolower ($trusted_answer) === mb_strtolower ($user_class -> returnPasswordHint ($trusted_id))) {
				$_HOAM_log -> add ('User "' . $trusted_username . '" correctly entered answer to security question, removing disabled flag from account.', HOAM_MESSAGE_NOTICE);
				$user_class -> clearBit ($trusted_id, $_HOAM_setting ['user']['flags']['disabled']);
				header ('Location: /user/login/');
				exit ();
			} else {
				header ('Location: /user/disabled/');
				exit ();
			}
		} else {
			header ('Location: /error/password/');
			exit ();
		}
	} else {
		header ('Location: /error/unknown_account/');
		exit ();
	}
}

exit ();

?>
