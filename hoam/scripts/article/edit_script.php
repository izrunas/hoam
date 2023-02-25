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
require_once (HOAM_PATH_FUNCTION . 'wiki.php');

session_start ();

$group_members_class = new HOAM_group_members ('group_members');

// Is the current user allowed to add / edit articles?
if (isset ($_SESSION['current_user']['id']) AND
	($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['article_add'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['article_delete'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['article_edit'], $_SESSION['current_user']['id']) OR
	$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
	($_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']))) {
	if (isset ($_POST['add']) || isset ($_POST['cancel']) || isset ($_POST['delete']) || isset ($_POST['modify'])) {

		$article_class = new HOAM_article ('articles');
		$group_class = new HOAM_group ('group_list');
		$user_class = new HOAM_user ('users');

		// If this is the second (or greater) time through the script (ie, there were
		// errors that had to be fixed before committing the changes) get rid of the old
		// cached information the user entered, we're going to populate it with
		// information just received from the form posting.
		if (isset ($_SESSION['article_information'])) {
			unset ($_SESSION['article_information']);
		}
		// Get rid of any old errors, and start over.
		if (isset ($_SESSION['error_class_item'])) {
			unset ($_SESSION['error_class_item']);
		}

		if (isset ($_POST['cancel'])) {
			// If the user canceled the modify, send them back to the main page
			// If we have an article id, send the user back to that.
			if (isset ($_POST['article|id'])) {
				header ('Location: ' . HOAM_wikiURL ((string) $article_class -> superClean ($_POST['article|id'], $article_class -> returnColumnSize ('id'))));
			} else {
				// Just send them back to the admin page
				header ('Location: /website/wiki/');
			}
		} elseif (isset ($_POST['delete'])) {
			// Make sure the current user is the owner of the item, OR has admin-level privileges.
			if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
				$group_members_class -> isMember ($_HOAM_setting ['group']['ids']['article_delete'], $_SESSION['current_user']['id']) OR
				($_HOAM_setting ['users']['ids']['root'] === $_SESSION['current_user']['id'])) {
				// Make sure we found the article
				$article_id = (string) $article_class -> superClean ($_POST['article|id'], $article_class -> returnColumnSize ('id'));
				$trusted_root_id = $article_class -> returnRootId ($article_id);
				if ($article_class -> idExists ($article_id)) {
					// Make sure we can't delete the root article
					if ($article_id == $_HOAM_setting ['article']['ids']['root']) {
						// We aren't allowed to delete the root article.
						header ('Location: /error/inuse/');
					} else {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['wiki']['deleted'], $article_class -> returnTitle ($article_id),
											HOAM_MESSAGE_NOTICE,
											array ('owner_id' => $article_id,
													'flags' => $_HOAM_setting ['log']['flags']['article'])));
						// !!!!
						// We need to add a check for whether there are any children of this article, and deal with them somehow...
						if ($article_class -> deleteId ($article_id)) {
							header ('Location: ' . HOAM_wikiURL ($trusted_root_id));
						} else {
							// Error Removing Item
							header ('Location: /error/remove/');
						}
					}
				} else {
					// The article couldn't be found
					header ('Location: /error/unknown_id/');
				}
			} else {
				// User does not have the necessary permissions.
				header ('Location: /error/403/');
			}
		} else {
			// If we're still here, then we must be adding or modifying an existing article.
			// Start populating the article_information array, and check for errors.
			$_SESSION['article_information'] = array ();
			$_SESSION['error_class_item'] = new HOAM_errors ('errors');

			if (isset ($_POST['article|id'])) {
				$_SESSION['article_information']['trusted_article|id'] = (string) $article_class -> superClean ($_POST['article|id'], $article_class -> returnColumnSize ('id'));
				if ($article_class -> idExists ($_SESSION['article_information']['trusted_article|id'])) {
				} else {
					// The article is unknown.
					$_SESSION ['error_class_item'] -> addError ('article|id', $_HOAM_language ['errors']['article']['unknown_id']);
				}
			}
			if (isset ($_POST['article|article'])) {
				$_SESSION['article_information']['trusted_article|article'] = (string) $article_class -> clean ($_POST['article|article'], $article_class -> returnColumnSize ('article'));
				// Make sure the content isn't too short.
				if (mb_strlen ($_SESSION['article_information']['trusted_article|article']) >= $_HOAM_setting ['article']['minimum_article_length']) {
				} else {
					// The content is too short.
					$_SESSION ['error_class_item'] -> addError ('article|article', sprintf ($_HOAM_language ['errors']['article']['too_short'], $_HOAM_setting ['article']['minimum_article_length']));
				}
				// use EXPLODE() to separate the words into an array
				// since they are separated by a ' ' (blank space)
				$_SESSION['article_information']['trusted_article|word_count'] = count (explode (' ', $_SESSION['article_information']['trusted_article|article']));
			}
			if (isset ($_POST['article|groups'])) {
				// if groups is set, then we need to verify the group membership.
				$_SESSION['article_information']['trusted_article|groups'] = array ();
				foreach ($_POST['article|groups'] as $group) {
					// First, find out if the group id is valid
					$group = (string) $group_class -> superClean ($group, $group_class -> returnColumnSize ('id'));
					if ($group_class -> idExists ($group)) {
						// Now, we need to make sure that the current user has access to a the group requested.
						if ($group_members_class -> isMember ($group, $_SESSION['current_user']['id'])) {
							// This is a valid group, and the current user has the appropriate group membership.
							array_push ($_SESSION['article_information']['trusted_article|groups'], $group);
						} else {
							// Users who are members of the system
							// administrators group and/or the root user are
							// exempt from this check.
							if ($group_members_class -> isMember ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['current_user']['id']) OR
								$_HOAM_setting ['user']['ids']['root'] === $_SESSION['current_user']['id']) {
								array_push ($_SESSION['article_information']['trusted_article|groups'], $group);
							} else {
								$_SESSION['error_class_item'] -> addError ('groups', $_HOAM_language ['errors']['group']['not_member']);
								$_HOAM_log -> add ('Validate: user "' . $user_class -> returnAccountName ($_SESSION['current_user']['id']) . '" attempted (but failed) to assign "' . $group_class -> returnName ($group) . '" group to an article. This is a security, not a system issue.', HOAM_MESSAGE_ALERT);
							}
						}
					}
				}
				// Make sure the Administrators group was selected to have access. If it's not a member of the group list, add it.
				if (!in_array ($_HOAM_setting ['group']['ids']['admin'], $_SESSION['article_information']['trusted_article|groups'])) {
					// Force the addition of the administrators group.
					array_push ($_SESSION['article_information']['trusted_article|groups'], $_HOAM_setting ['group']['ids']['admin']);
				}
			} else {
				// Force the addition of the administrators group.
				$_SESSION['article_information']['trusted_article|groups'] = array ($_HOAM_setting ['group']['ids']['admin']);
			}
			if (isset ($_POST['article|keywords'])) {
				$_SESSION['article_information']['trusted_article|keywords'] = (string) $article_class -> superClean ($_POST['article|keywords'], $article_class -> returnColumnSize ('keywords'));
				// Make sure the keywords aren't too short.
				if (mb_strlen ($_SESSION['article_information']['trusted_article|keywords']) >= $_HOAM_setting ['article']['minimum_keywords_length']) {
				} else {
					// The keywords are too short.
					$_SESSION ['error_class_item'] -> addError ('article|keywords', sprintf ($_HOAM_language ['errors']['article']['keywords_too_short'], $_HOAM_setting ['article']['minimum_keywords_length']));
				}
			}
			if (isset ($_POST['article|leadin'])) {
				$_SESSION['article_information']['trusted_article|leadin'] = (string) $article_class -> clean ($_POST['article|leadin'], $article_class -> returnColumnSize ('leadin'));
				// Make sure the leadin isn't too short.
				if (mb_strlen ($_SESSION['article_information']['trusted_article|leadin']) >= $_HOAM_setting ['article']['minimum_leadin_length']) {
				} else {
					// The leadin is too short.
					$_SESSION ['error_class_item'] -> addError ('article|leadin', sprintf ($_HOAM_language ['errors']['article']['leadin_too_short'], $_HOAM_setting ['article']['minimum_leadin_length']));
				}
			}
			if (isset ($_POST['article|root'])) {
				// This is one of the most important things to make sure is correct.  If
				// the root_id is wrong, we'll probably never find the article through an
				// url.
				$_SESSION['article_information']['trusted_article|root'] = (string) $article_class -> superClean ($_POST['article|root'], $article_class -> returnColumnSize ('id'));
				if ($article_class -> idExists ($_SESSION['article_information']['trusted_article|root'])) {
				} else {
					// There is a problem with the article root sent.
					$_SESSION ['error_class_item'] -> addError ('article|root', $_HOAM_language ['errors']['article']['unknown_root']);
				}
			}
			if (isset ($_POST['article|summary'])) {
				$_SESSION['article_information']['trusted_article|summary'] = (string) $article_class -> clean ($_POST['article|summary'], $article_class -> returnColumnSize ('summary'));
				// Make sure the summary isn't too short.
				if (mb_strlen ($_SESSION['article_information']['trusted_article|summary']) >= $_HOAM_setting ['article']['minimum_summary_length']) {
				} else {
					// The summary is too short.
					$_SESSION ['error_class_item'] -> addError ('article|summary', sprintf ($_HOAM_language ['errors']['article']['summary_too_short'], $_HOAM_setting ['article']['minimum_summary_length']));
				}
			}
			if (isset ($_POST['article|title'])) {
				$_SESSION['article_information']['trusted_article|title'] = (string) $article_class -> superClean ($_POST['article|title'], $article_class -> returnColumnSize ('title'));
				// Make sure the title isn't too short.
				if (mb_strlen ($_SESSION['article_information']['trusted_article|title']) >= $_HOAM_setting ['article']['minimum_title_length']) {
				} else {
					// The title is too short.
					$_SESSION ['error_class_item'] -> addError ('article|title', sprintf ($_HOAM_language ['errors']['article']['title_too_short'], $_HOAM_setting ['article']['minimum_title_length']));
				}
			}
			if (isset ($_POST['article|urlname'])) {
				// Perform checks against the article URLName.
				$_SESSION['article_information']['trusted_article|urlname'] = (string) $article_class -> superClean ($_POST['article|urlname'], $article_class -> returnColumnSize ('urlname'));
				// Don't check the urlname if this is the default, root article (which always has a blank urlname)
				if (mb_strlen ($_SESSION['article_information']['trusted_article|urlname']) >= $_HOAM_setting ['article']['minimum_urlname_length']) {
				} elseif ($_SESSION['article_information']['trusted_article|id'] == $_HOAM_setting ['article']['ids']['root']) {
					// ignore this issue, the root article is required to have a blank urlname.
				} else {
					$_SESSION['error_class_item'] -> addError ('article|urlname', sprintf ($_HOAM_language ['errors']['article']['urlname_too_short'], $_HOAM_setting ['article']['minimum_urlname_length']));
				}
				// Does the urlname contain any strange chars? (This check currently breaks / ignores i8n concerns)
				$matches = array();
				preg_match_all ('/[a-z0-9\-_]*/', $_SESSION['article_information']['trusted_article|urlname'], $matches);
				if ($_SESSION['article_information']['trusted_article|urlname'] == $matches[0][0]) {
				} else {
					$_SESSION['error_class_item'] -> addError ('article|urlname', $_HOAM_language ['errors']['article']['urlname_invalid_chars']);
				}
				unset ($matches);
				if (isset ($_POST['modify'])) {
					// Article_id will only be set if we're modifying, not adding, an article.
					// These tests are meaningless otherwise, and will fail.
					if (isset ($_POST['article|id'])) {
						if ($_SESSION['article_information']['trusted_article|id'] === $_HOAM_setting ['article']['ids']['root']) {
							if ($_SESSION['article_information']['trusted_urlname'] != '') {
								$_SESSION['error_class_item'] -> addError ('article|urlname', $_HOAM_language ['errors']['article']['urlname_root']);
							} else {
								// Force the urlname to blank (eg, an empty string, but NOT null);
								$_SESSION['article_information']['trusted_urlname'] = '';
							}
						}
						// Are there any siblings of this article?
						$article_siblings = $article_class -> returnChildren ($_SESSION['article_information']['trusted_article|root']);
						if (is_array ($article_siblings)) {
							foreach ($article_siblings as $sibling) {
								// We want to make sure that no other articles with the same root_id have the same urlname as the one we want to use.
								// Otherwise, we'd end up with two articles at the same URL.
								if (($article_class -> returnURLName ($sibling) == $_SESSION['article_information']['trusted_article|urlname']) AND
									($sibling != $_SESSION['article_information']['trusted_article|id'])) {
									$_SESSION['error_class_item'] -> addError ('article|urlname', $_HOAM_language ['errors']['article']['urlname_used_by_sibling']);
								}
							}
						}
					}
				}
			}
			if (isset ($_POST['article|postend'])) {
				$_SESSION['article_information']['trusted_article|postend'] = (string) $article_class -> superClean ($_POST['article|postend'], $article_class -> returnColumnSize ('datepostend'));
				if (!empty ($_SESSION['article_information']['trusted_article|postend'])) {
					// Make sure the date isn't too short.
					if (mb_strlen ($_SESSION['article_information']['trusted_article|postend']) < $article_class -> returnColumnSize ('datepostend')) {
						$_SESSION['error_class_item'] -> addError ('article|postend', $_HOAM_language ['errors']['generic']['date-too-short']);
					}
					// Make sure we have a valid date
					if (checkdate (date ('m', strtotime ($_SESSION['article_information']['trusted_article|postend'])), date ('d', strtotime ($_SESSION['article_information']['trusted_article|postend'])), date ('Y', strtotime ($_SESSION['article_information']['trusted_article|postend'])))) {
					} else {
						$_SESSION['error_class_item'] -> addError ('article|postend', $_HOAM_language ['errors']['generic']['invalid-date']);
					}
					// Make sure the date isn't in the past
					if (date ('m/d/Y') >= $_SESSION['article_information']['trusted_article|postend']) {
						$_SESSION['error_class_item'] -> addError ('article|postend', $_HOAM_language ['errors']['article']['postend_past']);
					}
				}
			}
			if (isset ($_POST['article|poststart'])) {
				$_SESSION['article_information']['trusted_article|poststart'] = (string) $article_class -> superClean ($_POST['article|poststart'], $article_class -> returnColumnSize ('datepoststart'));
				if (!empty ($_SESSION['article_information']['trusted_article|poststart'])) {
					// Make sure the date isn't too short.
					if (mb_strlen ($_SESSION['article_information']['trusted_article|poststart']) < $article_class -> returnColumnSize ('datepoststart')) {
						$_SESSION['error_class_item'] -> addError ('article|poststart', $_HOAM_language ['errors']['generic']['date-too-short']);
					}
					// Make sure we have a valid date
					if (checkdate (date ('m', strtotime ($_SESSION['article_information']['trusted_article|poststart'])), date ('d', strtotime ($_SESSION['article_information']['trusted_article|poststart'])), date ('Y', strtotime ($_SESSION['article_information']['trusted_article|poststart'])))) {
					} else {
						$_SESSION['error_class_item'] -> addError ('article|poststart', $_HOAM_language ['errors']['generic']['invalid-date']);
					}
					// Make sure the date isn't in the past
					if (date ('m/d/Y') >= $_SESSION['article_information']['trusted_article|poststart']) {
						$_SESSION['error_class_item'] -> addError ('article|poststart', $_HOAM_language ['errors']['article']['poststart_past']);
					}
				}
			}
			// OK, one final date check. Make sure the post end isn't before the post start, or visa versa.
			if (!empty ($_SESSION['article_information']['trusted_article|postend']) AND
				!empty($_SESSION['article_information']['trusted_article|poststart']) AND
				($_SESSION['article_information']['trusted_article|poststart'] <= $_SESSION['article_information']['trusted_article|postend'])) {
				$_SESSION['error_class_item'] -> addError ('article|postend', $_HOAM_language ['errors']['article']['postend_before_start']);
			}
			$_SESSION ['article_information']['trusted_article|flags'] = 0;
			if (isset ($_POST['article|comments'])) {
				$_SESSION['article_information']['trusted_article|flags'] += (int) $_HOAM_setting ['article']['flags']['comments'];
			}
			if (isset ($_POST['article|draft'])) {
				$_SESSION['article_information']['trusted_article|flags'] += (int) $_HOAM_setting ['article']['flags']['draft'];
			}
			if (isset ($_POST['article|redirect'])) {
				$_SESSION['article_information']['trusted_article|flags']  += (int) $_HOAM_setting ['article']['flags']['redirect'];
				// There is no test, just getting the information.
				if (isset ($_POST['article|redirect_location'])) {
					$_SESSION['article_information']['trusted_article|redirect_location'] = (string) $article_class -> superClean ($_POST['article|redirect_location'], $article_class -> returnColumnSize ('keywords'));
					if (mb_strlen ($_SESSION['article_information']['trusted_article|redirect_location']) < $_HOAM_setting ['article']['minimum_redirect_length']) {
						$_SESSION['error_class_item'] -> addError ('article|redirect_location', $_HOAM_language ['errors']['article']['redirect_too_short'], $_HOAM_setting ['articles']['minimum_redirect_length']);
					} elseif (mb_ereg ('^(ht|f)tp(s?)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$', $_SESSION['article_information']['trusted_article|redirect_location'])) {
					} else {
						$_SESSION['error_class_item'] -> addError ('article|redirect_location', $_HOAM_language ['errors']['generic']['invalid-url']);
					}

				}
			}

// print_r ($_POST); print_r ($_SESSION['article_information']); $_SESSION['error_class_item'] -> showErrors (); exit();

			// Are there any errors that the current user needs to fix before committing the changes?
			if ($_SESSION['error_class_item'] -> errorCount > 0) {
				// If we have an id, send the user back to that.
				if (isset ($_POST['add'])) {
					header ('location: /website/wiki/add/');
				} else {
					header ('location: /website/wiki/edit/' . $_SESSION['article_information']['trusted_article|id'] . '/');
				}
			} else {
				// Finally! We have good data and can start modifying the database.
				//
				// From below on out, we switch to $id from $article_id -- since $article_id may always go away.
				if (isset ($_POST['add'])) {
					$id = $article_class -> add ($_SESSION['current_user']['id']);
					if ($id == FALSE) {
						// Error Adding Item
						header ('Location: /error/add/');
					} else {
						$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['wiki']['created'], $_SESSION['article_information']['trusted_article|title'],
											HOAM_MESSAGE_NOTICE,
											array ('owner_id' => $id,
													'flags' => $_HOAM_setting ['log']['flags']['article'])));
						// When we're creating / adding a new article, just set the modified date the same as the created.
						$article_class -> changeDateModified ($id, $article_class -> returnDateCreated ($id));
					}
				} else {
					$id = $_SESSION['article_information']['trusted_article|id'];
					if ($article_class -> returnArticle ($id) == NULL) {
						$changes = HOAM_diff ('', $_SESSION['article_information']['trusted_article|article']);	
					} else {
						$changes = HOAM_diff ($article_class -> returnArticle ($id), $_SESSION['article_information']['trusted_article|article']);
					}
					$changes = sprintf ($_HOAM_language ['tables']['generic']['history'], ucfirst ($_HOAM_language ['common']['old-value']), ucfirst ($_HOAM_language ['common']['new-value']), $changes['old'], $changes['new']);
					$_HOAM_log -> add (sprintf ($_HOAM_language ['log']['wiki']['modified'], $changes),
										HOAM_MESSAGE_NOTICE,
										array ('clean' => FALSE,
												'owner_id' => $id,
												'flags' => $_HOAM_setting ['log']['flags']['article']));
					$article_class -> changeDateModified ($id);
				}
				// By now, we've already verified that all of our data is correct and valid, therefore we can just update the db.
				// Consider it a poor-man's transaction :)
				$article_class -> changeArticle ($id, $_SESSION['article_information']['trusted_article|article']);
				$article_class -> changeGroups ($id, serialize ($_SESSION['article_information']['trusted_article|groups']));
				$article_class -> changeKeywords ($id, $_SESSION['article_information']['trusted_article|keywords']);
				$article_class -> changeLeadin ($id, $_SESSION['article_information']['trusted_article|leadin']);
				if ($_SESSION['article_information']['trusted_article|postend'] != NULL) {
					$article_class -> changeDatePostEnd ($id, $_SESSION['article_information']['trusted_article|postend']);
				}
				if ($_SESSION['article_information']['trusted_article|poststart'] != NULL) {
					$article_class -> changeDatePostStart ($id, $_SESSION['article_information']['trusted_article|poststart']);
				}
				$article_class -> changeRootId ($id, $_SESSION['article_information']['trusted_article|root']);
				$article_class -> changeSummary ($id, $_SESSION['article_information']['trusted_article|summary']);
				$article_class -> changeTitle ($id, $_SESSION['article_information']['trusted_article|title']);
//				$article_class -> changeTopicId ($id, $_SESSION['article_information']['trusted_article|topic']);
				if ($_SESSION['article_information']['trusted_article|urlname']) {
					// This is a dirty hack because I don't want to spend the time figuring out an issue with editing the root article (and the urlname being forced into a null instead of '')
					$article_class -> changeURLName ($id, $_SESSION['article_information']['trusted_article|urlname']);
				}
				$article_class -> changeWordcount ($id, $_SESSION['article_information']['trusted_article|word_count']);
				$article_class -> changeFlags ($id, $_SESSION['article_information']['trusted_article|flags']);
				if (isset ($_SESSION['article_information']['trusted_article|redirect_location'])) {
					$article_class -> changeRedirectLocation ($id, $_SESSION['article_information']['trusted_article|redirect_location']);
				}

				// Clear the variables since everything worked OK.
				if (isset ($_SESSION['error_class_item'])) {
					unset ($_SESSION['error_class_item']);
				}
				if (isset ($_SESSION['article_information'])) {
					unset ($_SESSION['article_information']);
				}
				header ('Location: ' . HOAM_wikiURL ($id));
			}
		}
	} else {
		// We don't know what action (add / cancel / delete / modify) we're being asked to perform
		header ('Location: /error/unknown_action/');
	}
} else {
	// User does not have the necessary permissions.
	header ('Location: /error/403/');
}

exit ();

?>
