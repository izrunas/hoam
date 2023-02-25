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

function HOAM_userInit () {
	// Set up monitoring for user history
	if ($('user_history')) {
		Element.observe ($('user_history').parentNode.getElementsByTagName ('li')[3], 'click', HOAM_userHistory);
	}
	Element.observe ($('user|accountname'), 'blur', HOAM_userAccountNameCheck);
	// These elements only exists when editing an existing account, not
	// creating a new account.
	if ($('user|password|show-change')) {
		// Old password only exists for users editing their own account.
		if ($('user|password|old')) {
			Element.observe ($('user|password|old'), 'blur', HOAM_userPasswordValidate);
		}
		Element.observe ($('user|password|show-change'), 'click', function () {
			Element.toggle ($('user|change-password'));
		});
	}
	Element.observe ($('user|password|new'), 'blur', HOAM_userPasswordCheck);
	var language_strings = new Array ();
		language_strings[0] = HOAM_languageLookup ('common|very_weak');
		language_strings[1] = HOAM_languageLookup ('common|weak');
		language_strings[2] = HOAM_languageLookup ('common|mediocre');
		language_strings[3] = HOAM_languageLookup ('common|fair');
		language_strings[4] = HOAM_languageLookup ('common|strong');
		language_strings[5] = HOAM_languageLookup ('common|very_strong');
	Element.observe ($('user|password|new'), 'keyup', function () {
		HOAM_passwordStrength ($('user|password|new'), language_strings);
	});

	Element.observe ($('user|password|verify'), 'blur', HOAM_userPasswordCompare);
	Element.observe ($('user|resident'), 'click', function () {
		Element.toggle ($('user|show-address'));
	});
	if ($('user|groups')) {
		if ($('user_new')) {
			form = $('user_new');
		} else {
			form = $('user_modify');
		}
		Element.observe ($('user|group|add'), 'click', function () {
			HOAM_moveOption (form,  'user|groups_list', 'user|groups');
		});
		Element.observe ($('user|group|remove'), 'click', function () {
			HOAM_moveOption (form, 'user|groups', 'user|groups_list');
		});
		// If the groups aren't selected, they won't be transferred as part of
		// the form submit.
		Event.observe (form, 'submit', function () { setSelectAll ('user|groups'); });
	}
	// Run at page load:
	HOAM_passwordStrength ($('user|password|new'), language_strings);
}

function HOAM_userHistory () {
	HOAM_tabLoading ($('user_history'));
	var url = '/hoam/scripts/user/userHistory.php';
	var pars = 'id=' + $F('user|id');
	var myAjax = new Ajax.Updater ($('user_history'), url, {method: 'get', parameters: pars, onComplete: function (originalRequest) {
		ZebraTable.stripe (($('user_history').getElementsByTagName ('TBODY'))[0]);
	}
	});
}

function HOAM_userAccountNameCheck () {
	// Verify that the given account name is valid.
	// Remove any text (if present)
	var minimumLength = $('user|accountname').getAttribute ('minlength');
	var name = $F('user|accountname');
	if (name.length >= minimumLength) {
		var url = '/hoam/scripts/user/userAccountExists.php';
		var pars = 'account=' + name;
		var myAjax = new Ajax.Request (url, { method: 'get', parameters: pars, onComplete: function (originalRequest) {
			if (originalRequest.responseText == "1") {
				HOAM_formErrorMessage ('user|accountname', HOAM_languageLookup ('errors|newuser|accountname|in-use', $F('user|accountname')));
			} else {
				url = '/hoam/scripts/user/userAccountBanned.php';
				pars = 'account=' + name;
				var myAjax = new Ajax.Request (url, { method: 'get', parameters: pars, onComplete: function (originalRequest) {
					if (originalRequest.responseText == "1") {
						HOAM_formErrorMessage ('user|accountname', HOAM_languageLookup ('errors|newuser|accountname|banned'));
					} else {
						HOAM_formErrorClear ('user|accountname');
					}
				}
				});
			}
		}
		});
	} else if (name.length > 0) {
		HOAM_formErrorMessage ('user|accountname', HOAM_languageLookup ('errors|newuser|accountname|too-short', $('user|accountname').getAttribute ('minlength')));
	}
}

function HOAM_userPasswordCheck () {
	// Verify that the given password is valid
	password = $F('user|password|new');
	if ((password.length > 0) && (password.length < 5)) {
		HOAM_formErrorMessage ('user|password|new', HOAM_languageLookup ('errors|user|password|too-short', $('user|password|new').getAttribute ('minlength')));
	} else {
		HOAM_formErrorClear ('user|password|new');
	}
}

function HOAM_userPasswordCompare () {
	// Verify that the two passwords are identical
	if ($F('user|password|new') != $F('user|password|verify')) {
		HOAM_formErrorMessage ('user|password|verify', HOAM_languageLookup ('errors|user|password|not-match'));
	} else {
		HOAM_formErrorClear ('user|password|verify');
	}
}

function HOAM_userPasswordValidate () {
	// Verify that the given password is valid.
	// This function calls a script, and assumes / requires that a valid session
	// exists, and gets the current user's accountname from it. We're doing this
	// to reduce the likelyhood of password cracks.
	var url = '/hoam/scripts/user/userPasswordValidate.php';
	var pars = 'password=' + $F('user|password|old');
	var myAjax = new Ajax.Request (url, { method: 'get', parameters: pars, onComplete: function (originalRequest) {
		if (originalRequest.responseText != "1") {
			HOAM_formErrorMessage ('user|password|old', HOAM_languageLookup ('errors|user|password|incorrect'));
		} else {
			HOAM_formErrorClear ('user|password|old');
		}
	}
	});
}

Event.observe (window, 'load', HOAM_userInit);
