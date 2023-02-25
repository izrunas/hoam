-- MySQL dump 10.13  Distrib 5.7.30, for Linux (x86_64)
--
-- Host: localhost    Database: hoam_xyz
-- ------------------------------------------------------
-- Server version	5.7.30-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `advertising`
--

DROP TABLE IF EXISTS `advertising`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advertising` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  `datepoststart` date DEFAULT '1970-01-01',
  `datepostend` date DEFAULT '1970-01-01',
  `url` varchar(255) DEFAULT NULL,
  `impressions` bigint(20) unsigned DEFAULT '0',
  `clicks` bigint(20) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advertising`
--

LOCK TABLES `advertising` WRITE;
/*!40000 ALTER TABLE `advertising` DISABLE KEYS */;
/*!40000 ALTER TABLE `advertising` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `root_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) DEFAULT NULL,
  `section_id` varchar(32) DEFAULT NULL,
  `childcount` smallint(5) unsigned NOT NULL DEFAULT '0',
  `urlname` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `leadin` text,
  `summary` text,
  `article` mediumtext,
  `word_count` smallint(5) unsigned DEFAULT NULL,
  `datecreated` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `datemodified` datetime DEFAULT NULL,
  `datepoststart` date DEFAULT NULL,
  `datepostend` date DEFAULT NULL,
  `group_membership` blob,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`root_id`),
  UNIQUE KEY `id_2` (`id`),
  KEY `id` (`id`),
  KEY `root_id` (`root_id`),
  KEY `keywords` (`keywords`),
  FULLTEXT KEY `article` (`article`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `title_2` (`title`,`article`),
  FULLTEXT KEY `title_3` (`title`,`article`,`keywords`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES ('f978cd35e6e3d30ccec5cd80c2edc310','1fcbed66aad681e22d2a63da2a6e678d','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'documents','HOA Documents','','','','<p>The information in the below documents range from the initial legal formation of the HOA, through useful information for homeowners and residents living in the neighborhood. Several of the below documents would have been provided to you during the purchase and closing on your residence. Others are less common, and usually require a visit to the county courthouse for viewing.</p>\\r\\n\\r\\n<h3>Legal Documents</h3>\\r\\n<ul>\\r\\n\\r\\n  <li><a href=\\\"[[url:3a48a0ec95a990ca2a0e3550f83da27e]]\\\">HOA Articles of Incorporation</a></li>\\r\\n  <li><a href=\\\"[[url:e1f74c42072a5968deafd209dd6f266a]]\\\">HOA Bylaws</a></li>\\r\\n  <li><a href=\\\"[[url:5dfb52f0d3c267f24719af160e0faa13]]\\\">HOA Declaration of Covenants, Conditions, &amp; Restrictions</a>\\r\\n</ul>\\r\\n\\r\\n<h3>Additional HOA Documentation</h3>\\r\\n<ul>\\r\\n  <li><a href=\\\"[[url:17d902521e30af06afc953c31d2c2705]]\\\">Board Member Roles and Responsibilities</a></li>\\r\\n  <li><a href=\\\"[[url:74070cf1b101ab84092845e05f86d84f]]\\\">HOA Frequently Asked Questions</a></li>\\r\\n  <li><a href=\\\"[[url:812c06c9ff112bb4af80ef74507d7ad0]]\\\">HOA Timeline &amp; Historical Dues Rates</a></li>\\r\\n  <li><a href=\\\"[[url:cd3c58aeb536db5dcd5fe0cb3a7d0f38]]\\\">Common Residential Maintenance Tasks</a></li>\\r\\n  <li><a href=\\\"[[url:a5a66e73df638f139caa1cd2df92c239]]\\\">Emergency Preparedness Kit</a></li>\\r\\n</ul>\\r\\n\\r\\n<h3>Drawings / Diagrams</h3>\\r\\n<ul>\\r\\n  <li><a href=\\\"/hoam/scripts/attachment/view.php?id=46e92ce342d7a92c0aca579b1aae3d49\\\">Drawing of Neighborhood, Phase I [Large Image]</a></li>\\r\\n</ul>',125,'2003-07-13 17:41:53','2020-04-20 17:18:13',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('2cdfbfe19d0356142db499696ea53a05','419f1b3c2b75d0e72b7bae31b072fbfe','684ab9dafd44c57dd85a08dd18813024','a0d0773aa18f44e75b85d8a565dd464f',0,'privacy','Privacy Policy',NULL,NULL,NULL,'<p>Thank you for visiting our web site. {{ORG_NAME}} (the \"HOA\") respects and protects the privacy of the individuals that use the HOA\'s web site and it\'s content (the \"Service\"). We hope that the information provided below addresses any questions or concerns you may have about privacy issues. Individually identifiable information about you is not willfully disclosed to any third party without first receiving your permission, as explained in this privacy policy (\"Privacy Policy\").</p>\r\n\r\n<h3>Using this Web Site</h3>\r\n<p>Generally, you may use the Service anonymously without providing any personal information. However, you may be asked to register and provide information that identifies you in certain circumstances, such as posting messages or downloading software. Supplying such information is optional, but you may be unable to access certain areas of the web site without doing so.</p>\r\n\r\n<h3>What Information Do We Collect?</h3>\r\n<p>The HOA does not collect any unique information about you (such as your name, email address, et cetera) except when you specifically and knowingly provide such information. The HOA notes and saves information such as time of day, browser type, browser language, and IP address with each visit you make to the site. That information is used for diagnostics, performance monitoring, and to provide more relevant services to users. For example, the HOA may use your IP address or browser language to determine which language to use when displaying articles or advertisements.</p>\r\n\r\n<h3>Personal Information Related to Children</h3>\r\n<p>The HOA does not intend to collect personal information from children who identify themselves as being less than 18 years of age. Children should not provide personal information on this Web site, and should ask their parents to submit a request on their behalf if they want to receive information related to this site. The HOA encourages parents to take an active interest in their children\'s use of the Internet.</p>\r\n\r\n<h3>With Whom Does the HOA Share Information?</h3>\r\n<p>The HOA may share information about you with advertisers, business partners, sponsors, and other third parties. However, we only divulge aggregate information about our users and will not share personally identifiable information with any third party without your express consent. For example, we may disclose how frequently the average user accesses the Service.  Please be aware, however, that we will release specific personal information about you if required to do so in order to comply with any valid legal process such as a search warrant, subpoena, statute, or court order.</p>\r\n\r\n<h3>Updating Personal Information and \"Opting Out\"</h3>\r\n<p>You can help the HOA maintain the accuracy of your information by notifying us of any change to your address, phone number, e-mail address, or other information.  You may do this either by using the online form provided, or by sending an e-mail message to <a href=\"mailto:{{ORG_EMAIL_MANAGEMENT}}\">{{ORG_EMAIL_MANAGEMENT}}</a>.</p>\r\n<p>If you did not \"opt out\" of receiving information from the HOA, its partners, other companies, or organizations at the time you provided your personal information and you want to stop receiving information, notify us any time by using the online form provided, or sending an e-mail to <a href=\"mailto:{{ORG_EMAIL_MANAGEMENT}}\">{{ORG_EMAIL_MANAGEMENT}}</a>. Please include your name, address, and / or e-mail address when you contact us. If you notify us that you want to \"opt out\" after you initially provide us with personal information, we will no longer share your personal information with other companies and organizations subsequent to your \"opt out\" notification. However, any information we provided to other companies or organizations prior to receiving your \"opt out\" request will not be recalled.</p>\r\n\r\n<h3>Cookies</h3>\r\n<p>The HOA may occasionally use technology known as a \"cookie\" to store information on your computer. A cookie is a piece of data that identifies you as a unique user. The HOA uses cookies to improve the quality of our service and to understand our user base more. The HOA does this by storing a unique tracking number in the cookie, and cross referencing that with previous information that you have provided (if you chose to provide the HOA with personally identifying information, and are not accessing the Service anonymously).</p>\r\n<p>Most browsers are initially set up to accept cookies. You can reset your browser to refuse all cookies or to indicate when a cookie is being sent. Be aware, however, that some parts of the the Service may not function properly if you refuse cookies.</p>\r\n\r\n<h3>Links to Other Sites</h3>\r\n<p>The HOA is not responsible for the privacy practices of sites to which this Web site may link. We encourage you to become familiar with the privacy practices of web sites before you provide them with personal information.</p>\r\n\r\n<h3>Your consent and changes to the Privacy Policy</h3>\r\n<p>By using the Service, you consent to the collection and use of your information as we have outlined in this policy and to our <a href=\"/legal/disclaimer/\">Copyright and Legal Disclaimer policy</a>. The HOA may decide to change this Privacy Policy from time to time. Although we will attempt to notify you via your email address when major changes are made, you should visit this page periodically to review the terms.</p>\r\n\r\n<h3>Who can I ask if I have additional questions?</h3>\r\n<p>Feel free to contact us any time and we\'ll answer any additional questions you may have. Our email address is <a href=\"mailto:{{ORG_EMAIL_MANAGEMENT}}\">{{ORG_EMAIL_MANAGEMENT}}</a>.</p>',845,'2003-05-30 22:49:03','2011-09-16 22:51:22',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('183e7279d65e682fe0c05aef452c4cfd','419f1b3c2b75d0e72b7bae31b072fbfe','684ab9dafd44c57dd85a08dd18813024','a0d0773aa18f44e75b85d8a565dd464f',0,'disclaimer','Legal Disclaimer and Copyright Information',NULL,NULL,NULL,'<h2>Terms and Conditions of Use</h2>\\r\\n\\r\\n<h3>Introduction</h3>\\r\\n\\r\\n<p>Thank you for your interest in using {{ORG_NAME}} (the \\\"HOA\\\") web site and content (the \\\"Service\\\"). By using the Service, you agree to be bound by the following terms and conditions. If you wish not to be bound by the following terms and conditions, your exclusive remedy is not to use the Service.</p>\\r\\n\\r\\n<p>The HOA may, in its sole discretion, modify or revise these terms and conditions at any time by updating this web page, and you agree to be bound by these modifications or revisions. Although we will attempt to notify you via your email address when major changes are made, you should visit this page periodically to review the terms.</p>\\r\\n\\r\\n<h3>Use of Material</h3>\\r\\n\\r\\n<p>The contents within the Service are protected by copyright and other laws in both the United States and elsewhere. The Service includes both content owned or controlled by the HOA as well as content owned or controlled by third parties and licensed to the HOA (collectively, the \\\"Materials\\\").</p>\\r\\n\\r\\n<p>The HOA authorizes you to view and download a single copy of the Materials solely for your personal, non-commercial use. You may not sell or modify the Materials or reproduce, display, publicly perform, distribute, or otherwise use the Materials in any way for any public or commercial purpose without the written permission of the HOA. Special rules may apply to the use of certain software and other items provided via the Services, and are noted where appropriate.</p>\\r\\n\\r\\n<p>If you would like information about obtaining the HOA\\\'s permission to use any of the Material on your Web site, please send an e-mail to <a href=\\\"mailto:{{ORG_EMAIL_MANAGEMENT}}\\\">{{ORG_EMAIL_MANAGEMENT}}</a>.</p>\\r\\n\\r\\n<h2>Discussion Groups</h2>\\r\\n\\r\\n<p>The Service contains certain discussion forums and news groups (collectively, the \\\"Groups\\\").</p>\\r\\n\\r\\n<p>MUCH OF THE CONTENT OF THE GROUPS <u>INCLUDING THE CONTENTS OF SPECIFIC POSTINGS</u> IS PROVIDED BY AND IS THE RESPONSIBILITY OF THE PERSON POSTING IN THAT GROUP. THE HOA DOES NOT MONITOR THE CONTENT OF THE GROUPS AND TAKES NO RESPONSIBILITY FOR SUCH CONTENT. INSTEAD, THE HOA MERELY PROVIDES ACCESS TO SUCH CONTENT AS A SERVICE TO YOU.</p>\\r\\n\\r\\n<p>By their very nature, Groups may carry offensive, harmful, inaccurate or otherwise inappropriate material, or in some cases, postings that have been mislabeled or are otherwise deceptive. We expect that you will use caution and common sense and exercise proper judgment when using Groups.</p>\\r\\n\\r\\n<p><u>No Endorsement</u>. The HOA does not endorse, support, represent or guarantee the truthfulness, accuracy, or reliability of any communications Posted in the Groups or endorse any opinions expressed in the Groups. You acknowledge that any reliance on material Posted in the Groups will be at your own risk.</p>\\r\\n\\r\\n<p><u>No Obligation to Monitor</u>. The HOA does not control the information delivered to the Groups, and the HOA has no obligation to monitor the Groups. However, the HOA reserves the right at all times to disclose any information as necessary to satisfy any applicable law, regulation, legal process or governmental request, or to edit, refuse to post or to remove any information or materials, in whole or in part, for any reason whatsoever, in the HOA\\\'s sole discretion.</p>\\r\\n\\r\\n<p><u>The HOA\\\'s Rights</u>. If the HOA discovers communications that allegedly do not conform to any term of this Agreement, the HOA may investigate the allegation and determine in good faith and in its sole discretion whether to remove or request the removal of the communication. The HOA will have no liability or responsibility for performance or non-performance of such activities. The HOA reserves the right to terminate or restrict your access to any or all of the Groups at any time without notice for any reason whatsoever.</p>\\r\\n\\r\\n<p><u>Privacy</u>. You acknowledge that all Groups are public and not private communications, and that therefore others may read your communications without your knowledge. Always use caution when giving out any personally identifying information about yourself or your children in any Group. Generally, any communication that you post to the Service is considered to be non-confidential. If particular Web pages permit the submission of communications that will be treated by the HOA as confidential, that fact will be explicitly stated on those pages. For more information on the HOA\\\'s approach to privacy in general, see the HOA\\\'s <a href=\\\"/legal/privacy/\\\">Privacy Policy</a>.</p>\\r\\n\\r\\n<p><u>Permitted Uses</u>. You agree that you are responsible for your own communications and for any consequences thereof. You agree to use the Groups only to send and receive messages and material that are legal, proper and related to the particular Group. By way of example, and not as a limitation, you agree that when using a Group, you will not:\\r\\n  <ul>\\r\\n    <li>Defame, abuse, harass, stalk, threaten or otherwise violate the legal rights (such as rights of privacy and publicity) of others.</li>\\r\\n    <li>Publish, post, upload, distribute or disseminate or offer to do the same (hereinafter \\\"Post\\\") any inappropriate, defamatory, infringing, obscene, or unlawful material or information.</li>\\r\\n    <li>Post any material that infringes any patent, trademark, copyright, trade secret or other proprietary right of any party (the \\\"Rights\\\"), unless you are the owner of the Rights or have the permission of the owner to post or transmit such material.</li>\\r\\n    <li>Post any files that contain viruses, corrupted files, or any other similar software or programs that may damage the operation of another\\\'s computer.</li>\\r\\n    <li>Advertise or offer to sell any goods or services for any commercial purpose, other than in Groups intended for such uses.</li>\\r\\n    <li>Conduct or forward surveys, contests, pyramid schemes or chain letters, other than in Groups intended for such uses.</li>\\r\\n    <li>Download any file Posted by another user of a Group that you know, or reasonably should know, that cannot be legally distributed in such manner.</li>\\r\\n    <li>Impersonate another person or entity, or falsify or delete any author attributions, legal or other proper notices or proprietary designations or labels of the origin or source of software or other material contained in a file that is Posted.</li>\\r\\n    <li>Restrict or inhibit any other user from using and enjoying the Groups.</li>\\r\\n  </ul>\\r\\n\\r\\n<p><u>License Grant</u>. By posting communications on or through the Service, you automatically grant the HOA a royalty-free, perpetual, irrevocable, non-exclusive license to use, reproduce, modify, publish, edit, translate, distribute, perform, and display the communication alone or as part of other works in any form, media, or technology whether now known or hereafter developed, and to sublicense such rights through multiple tiers of sublicensees.</p>\\r\\n\\r\\n<h2>Copyright Infringement and Copyright Agent</h2>\\r\\n\\r\\n<p>The HOA may, in appropriate circumstances and at its discretion, remove, or disable access to, material on the Web Site that infringes on the rights of others.</p>\\r\\n\\r\\n<h3>Links to Other Sites</h3>\\r\\n\\r\\n<p>The Service contains links to third party web sites that are maintained by others. These links are provided solely as a convenience to you and not as an endorsement by the HOA of the contents on such third-party Web sites. The HOA is not responsible for the content of linked third-party sites and does not make any representations regarding the content or accuracy of materials on such third-party Web sites. If you decide to access linked third-party Web sites, you do so at your own risk.</p>\\r\\n\\r\\n<h3>No Warranties</h3>\\r\\n\\r\\n<p>MUCH OF THE MATERIAL ON THE SERVICE IS PROVIDED BY THIRD PARTIES AND THE HOA SHALL NOT BE HELD RESPONSIBLE FOR ANY SUCH THIRD PARTY MATERIAL. THE SERVICE AND MATERIAL ARE PROVIDED ON AN \\\"AS IS\\\" BASIS WITHOUT ANY WARRANTIES OF ANY KIND, WHETHER EXPRESS OR IMPLIED. THE HOA AND ITS SUPPLIERS, TO THE FULLEST EXTENT PERMITTED BY LAW, DISCLAIM ALL WARRANTIES, INCLUDING BUT NOT LIMITED TO WARRANTIES OF TITLE, FITNESS FOR A PARTICULAR PURPOSE, MERCHANTABILITY AND NON-INFRINGEMENT OF PROPRIETARY OR THIRD PARTY RIGHTS. THE HOA AND ITS SUPPLIERS MAKE NO WARRANTIES ABOUT THE ACCURACY, RELIABILITY, COMPLETENESS, OR TIMELINESS OF THE MATERIAL, SERVICES, SOFTWARE, TEXT, GRAPHICS, AND LINKS.</p>\\r\\n\\r\\n<p>THE HOA DOES NOT WARRANT THAT THE SERVICE WILL OPERATE ERROR-FREE OR THAT THE SERVICE OR ITS SERVER ARE FREE OF COMPUTER VIRUSES OR OTHER HARMFUL ITEMS. IF YOUR USE OF THE SERVICE OR THE MATERIAL RESULTS IN THE NEED FOR SERVICING OR REPLACING EQUIPMENT OR DATA, THE HOA IS NOT RESPONSIBLE FOR THOSE COSTS.</p>\\r\\n\\r\\n<h3>Limitation of Liability / Disclaimer of Damages</h3>\\r\\n\\r\\n<p>Your use of the Service is at your own risk. If you are dissatisfied with any of the Materials or other contents of the Service or with these Terms and Conditions, The HOA\\\'s Privacy Policy, or other policies, your sole remedy is to discontinue use of the Service.</p>\\r\\n\\r\\n<p>IN NO EVENT SHALL THE HOA OR ITS SUPPLIERS BE LIABLE TO ANY USER OR ANY THIRD PARTY FOR ANY DAMAGES WHATSOEVER (INCLUDING, WITHOUT LIMITATION, DIRECT, INDIRECT, INCIDENTAL, CONSEQUENTIAL, SPECIAL, EXEMPLARY OR LOST PROFITS) RESULTING FROM THE USE OR INABILITY TO USE THE SERVICE OR THE MATERIAL, WHETHER BASED ON WARRANTY, CONTRACT, TORT, OR ANY OTHER LEGAL THEORY, AND WHETHER OR NOT THE HOA IS ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.</p>\\r\\n\\r\\n<h3>Indemnity</h3>\\r\\n\\r\\n<p>You agree to defend, indemnify, and hold harmless the HOA, its officers, directors, employees and agents, from and against any claims, actions or demands, including without limitation reasonable legal and accounting fees, alleging or resulting from your use or posting of Material or your breach of the terms of this Agreement (a \\\"Claim\\\"). In the event a Claim is brought against the HOA, the HOA shall provide notice to you and shall have the right to retain counsel of its choosing to defend against such a Claim. You agree to reimburse the HOA for any costs (including reasonable attorney fees) or damages awards incurred in association with such defense. Furthermore, you agree to cooperate in good faith to assist the HOA in such defense and any settlement negotiations related thereto, and to reimburse the HOA for reasonable settlement amounts if any.</p>\\r\\n\\r\\n<h3>Export Control</h3>\\r\\n\\r\\n<p>The United States controls the export of products and information. You agree to comply with such restrictions and not to export or re-export the Materials to countries or persons prohibited under the export control laws. By downloading the Materials, you are agreeing that you are not in a country where such export is prohibited and that you are not on the U.S. Commerce Department\\\'s Table of Denial Orders or the U.S. Treasury Department\\\'s list of Specially Designated Nationals. You are responsible for compliance with the laws of your local jurisdiction regarding the import, export, or re-export of the Materials.</p>\\r\\n\\r\\n<h3>General</h3>\\r\\n\\r\\n<p>Access to the Materials may not be legal by certain persons or in certain countries. If you access the Service from outside of the United States, you are responsible for compliance with the laws of your jurisdiction.</p>\\r\\n\\r\\n<p>All legal issues arising from or related to the use of the Service shall be construed in accordance with and determined by the laws of the State of Texas, United States of America, without regard to it conflict of laws principles. By using this Service, you agree that the exclusive forum for the bringing of any claims or causes of action arising out of or relating to your use of this Service is the United States District Court for Texas, or if such court lacks subject matter jurisdiction, the state courts of Texas encompassed within the geographic scope of Texas. You hereby accept and submit to the jurisdiction of such court in any such proceeding or action, and irrevocably waive, to the fullest extent permitted by law, any objection which you may now or hereafter have to the laying of the venue of any such action or proceeding brought in such a court and any claim that any such action or proceeding brought in such a court has been brought in an inconvenient forum.</p>\\r\\n\\r\\n<p>If any provision of these terms and conditions is found to be invalid by any court having competent jurisdiction, the invalidity of such provision shall not affect the validity of the remaining provisions of this agreement, which shall remain in full force and effect. No waiver of any term of this agreement shall be deemed a further or continuing waiver of such term or any other term. Except as expressly provided in a particular \\\"Legal Notice\\\" on particular web pages, this agreement and its referenced parts constitutes the entire agreement between you and the HOA with respect to the use of Service.</p>',2014,'2003-05-30 22:56:43','2011-08-21 15:45:11',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('1fcbed66aad681e22d2a63da2a6e678d','7de98536ec03c26ef838041a1d940149','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'hoa','The HOA (Home Owners Association)',NULL,NULL,NULL,'<p>All homeowners in the {{ORG_NAME}} are automatically granted and become Members of the Homeowners Association (HOA). The HOA is a non-profit corporation that governs much of the \\\"look and feel\\\" of the community.</p>\\r\\n<p>While some homeowners believe that the HOA has the power to remedy any and all problems in the community, our authority is limited by jurisdiction and the law as it pertains to individual homesteads. The HOA responds to complaints from residents, but also relies on regular inspections of the property by the management company, board members, and the various committees. Regular drive-bys, walk-bys, and greenbelt patrols include inspection of the community property, front and rear neighborhood entrances, and all homes in the neighborhood.</p>\\r\\n<p>You should familiarize yourself with the <a href=\\\"[[url:e1f74c42072a5968deafd209dd6f266a]]\\\">Bylaws</a> and <a href=\\\"[[url:5dfb52f0d3c267f24719af160e0faa13]]\\\">Covenants</a> of the neighborhood, which detail the responsibilities you have as a Member of the Association.  All of the details that concern how the HOA operates, including the Bylaws and Covenants, are available on the <a href=\\\"[[url:f978cd35e6e3d30ccec5cd80c2edc310]]\\\">Documents</a> page.</p>\\r\\n<p>The HOA is managed by a Board of Directors, which consists of five homeowners elected each year from the neighborhood. The Board may always be contacted via email at <a href=\\\"mailto:{{ORG_EMAIL_BOARD}}\\\">{{ORG_EMAIL_BOARD}}</a>, as well as by each individual\\\'s email address below. Additionally, many of your neighbors graciously volunteer their time and efforts towards the amenities and activities that make the neighborhood such a unique place to live. We are deeply grateful for the time and effort these Board Members and all volunteers have devoted to our neighborhood.</p>\\r\\n<div class=\\\"article_note RHS\\\">Please familiarize yourself with the <a href=\\\"[[url:5dfb52f0d3c267f24719af160e0faa13]]\\\">Covenants</a> and <a href=\\\"[[url:e1f74c42072a5968deafd209dd6f266a]]\\\">Bylaws</a> of the Association.</div>\\r\\n\\r\\n<dl>\\r\\n  <dt><a href=\\\"[[url:f978cd35e6e3d30ccec5cd80c2edc310]]\\\">Documents</a></dt>\\r\\n  <dd>HOA legal documents, as well as new homeowner information and drawings of the neighborhood.</dd>\\r\\n  <dt><a href=\\\"[[url:cd4c6ea492fa118ffb64ed267f415084]]\\\">Forms</a></dt>\\r\\n  <dd>Various homeowner forms for voting by proxy, email notification, and the neighborhood directory.</dd>\\r\\n  <dt><a href=\\\"[[url:90e93e6ad8a2079a76fad6c6a31a8336]]\\\">Meeting Minutes</a></dt>\\r\\n  <dd>Meeting minutes for all of the HOA Board meetings.</dd>\\r\\n  <dt><a href=\\\"[[url:9bc4dc862070e4a5c7fafeac067084ef]]\\\">Notifications</a></dt>\\r\\n  <dd>Various notifications that have been sent to homeowners regarding Association issues.</dd>\\r\\n</dl>',326,'2003-07-13 16:16:29','2012-06-26 16:26:15',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('5dfb52f0d3c267f24719af160e0faa13','f978cd35e6e3d30ccec5cd80c2edc310','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'covenants','Covenants, Conditions, &amp; Restrictions','','','','',1,'2003-08-17 19:33:34','2020-04-20 17:16:49',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('d6f007ddae950194bf15242555319e64','4be7b23ef05b04264e96637df1a1c70c','684ab9dafd44c57dd85a08dd18813024','2590154b01970fc9d2800ce57064b843',0,'contact','Contact the HOA',NULL,NULL,NULL,'<p>We welcome any questions, comments, or concerns you may have. You may want to review the list of <a href=\\\"[[url:74070cf1b101ab84092845e05f86d84f]]\\\">Frequently Asked Questions</a>, it\\\'s possible that your question may have been answered previously.</p>\\r\\n\\r\\n<p>The {{ORG_NAME}} and its agents may be contacted in several ways:</p>\\r\\n<ul>\\r\\n  <li>By emailing the officers of the HOA, at [[email:{{ORG_EMAIL_OFFICERS}}]]</li>\\r\\n  <li>By emailing the management company of the HOA, at [[email:{{ORG_EMAIL_MANAGEMENT}}]],</li>\\r\\n  <li>By calling the HOA at {{ORG_PHONE}},</li>\\r\\n  <li>By faxing the HOA at {{ORG_PHONE_FAX}},</li>\\r\\n  <li>Through U.S. postal mail at:<br/>{{ORG_ADDRESS}}</li>\\r\\n</ul>\\r\\n\\r\\n<p><strong>Please include your name and address on all correspondence in order to expedite a response</strong>. Although responses are usually same-day, please allow up to two business days for your call or message to be returned.</p>',117,'2003-07-15 11:25:31','2012-10-16 20:05:34',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('419f1b3c2b75d0e72b7bae31b072fbfe','4be7b23ef05b04264e96637df1a1c70c','684ab9dafd44c57dd85a08dd18813024','a0d0773aa18f44e75b85d8a565dd464f',0,'legal','Legal Information',NULL,NULL,NULL,'[[tree:419f1b3c2b75d0e72b7bae31b072fbfe]]',1,'2003-09-17 13:02:15','2012-06-26 15:43:42',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('4be7b23ef05b04264e96637df1a1c70c','4be7b23ef05b04264e96637df1a1c70c','684ab9dafd44c57dd85a08dd18813024','16eb2a66fa6908522dcc40dc7cdcb02b',0,'','Recent News','','','','<p>Congratulations on installing HOAM! Several default pages have been created and configuration options have been set for you already. Please review the information below for next steps.</p>\\r\\n\\r\\n<dl>\\r\\n  <dt>A default administration account has been configured</dt>\\r\\n  <dd>Username \\\"Admin\\\", password \\\"admin\\\"</dd>\\r\\n  <dd>This account provides complete access to the site to add/remove homeowners, budget entries, user accounts, new properties, run reports, etc.</dd>\\r\\n</dl>\\r\\n\\r\\nIt\\\'s recommended you start by performing the following steps:\\r\\n<ol>\\r\\n  <li><a href=\\\"/user/settings/\\\">Change the default password</a> for the Admin account.</li>\\r\\n  <li>Navigate to Website Administration &rArr; <a href=\\\"/website/config/modify/\\\">System Configuration</a>\\r\\n    <ul>\\r\\n      <li>The vast majority of the settings can probably be left at their default value. However, you will need to change items on at least the <var>Budget</var>, <var>Email</var>, <var>Homeowners</var>, <var>Letters</var>, <var>Organization</var>, and <var>Website</var> tabs.</li>\\r\\n      <li><strong>Make sure</strong> the important settings (such as the Association assessment amount, due dates, and appropriate accounts, email addresses, categories, groups, users and vendors) are updated.</li>\\r\\n    </ul>\\r\\n  </li>\\r\\n  <li><a href=\\\"/admin/financial/vendor/\\\">Enter vendors</a> used by the Association; at a minimum an account needs to be created for the Association itself as well as the management company.\\r\\n  <ul>\\r\\n    <li>If there is not a management company, simply enter the Association\\\'s information everywhere it says management company.</li>\\r\\n    <li>After doing this, you\\\'ll need to make sure you update the vendor information.</li>\\r\\n  </ul></li>\\r\\n  <li><a href=\\\"/admin/financial/account/\\\">Review the default financial accounts</a> and add or remove any as necessary; at a minimum Income and Expense accounts need to exist.</li>\\r\\n  <li><a href=\\\"/admin/lot/\\\">Add new streets and lot numbers</a> for all of the residences in your neighborhood.</li>\\r\\n  <li><a href=\\\"/admin/homeowner/\\\">Add homeowners</a> for each of the newly created addresses.</li>\\r\\n  <li><a href=\\\"/admin/financial/category/\\\">Review the default budget categories</a> and add or remove any as necessary.</li>\\r\\n  <li><a href=\\\"/admin/financial/budget/\\\">Create a Budget</a> to track your expenses and income against.</li>\\r\\n  <li><a href=\\\"/admin/violation/category/\\\">Review and modify the default violation categories</a> to match the specifics of your governing documents. <strong>The default categories use specific language that will not match your governing documents.</strong>.</li>\\r\\n  <li>Edit this page (click the <img src=\\\"/hoam/images/icons/page_white_edit.png\\\" /> in the upper-right corner) to remove this notice.</li>\\r\\n</ol>\\r\\n\\r\\n{{WEBSITE_BLURB}}\\r\\n\\r\\n{{NEWS_RECENT}}',357,'2003-09-17 17:45:48','2020-05-05 23:56:37',NULL,NULL,_binary 'a:2:{i:0;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";i:1;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";}',0),('90e93e6ad8a2079a76fad6c6a31a8336','1fcbed66aad681e22d2a63da2a6e678d','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'minutes','Meeting Minutes',NULL,NULL,NULL,'[[tree:90e93e6ad8a2079a76fad6c6a31a8336]]',1,'2003-10-06 15:22:12','2012-06-26 15:17:33',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('17d902521e30af06afc953c31d2c2705','f978cd35e6e3d30ccec5cd80c2edc310','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'roles','Board Member Roles and Responsibilities',NULL,NULL,NULL,'<p>Serving on the board of directors of a neighborhood association is a big job. And, serving also as an officer usually means more work. To help the officers of the board work more effectively, each officer needs to understand their respective roles and responsibilities and how they relate to the other officers, the other directors, and members of the association.</p>\r\n<p>The three key officer positions in the association are President, Secretary, and Treasurer. While most associations will have a Vice President to service in the President\'s absence and may appoint Assistant Secretaries or Treasurers to help out, the key officers usually fill the most demanding roles. In an effort to help volunteer directors filling those positions, the following recap of key duties for each officer is provided.</p>\r\n\r\n<dl>\r\n  <dt>President</dt>\r\n  <dd>As the chief elected officer of the corporation, the homeowner association president serves as the CEO of the association. As such, a president\'s actions as a leader and not a dictator, sets the stage on the association\'s performance. The President should always work not only to protect the assets of the association, but also to enhance the quality of the life within the community.</dd>\r\n  <dd>Some of the key duties of the President include:\r\n    <dl>\r\n      <dt>Understanding the Role and Authority of the Position.</dt>\r\n      <dd>The president must have a basic understanding of the laws affecting homeowner associations, the governing documents of the association and any related documents such as contracts, agreements, and insurance policies. By being familiar with these items, the president will know the organizational structure, specific duties of the presidency, requirements of the association, and the authority by which to act.</dd>\r\n      <dt>Knowing the Association\'s Budget and Financial Condition.</dt>\r\n      <dd>The president, just like all other board members, must exercise a fiduciary duty to protect the association. Fiduciaries must always execute their duties and make decisions in good faith and in the best interests of the entire community. As the CEO, the president must fully understand all items in the budget and know the financial condition of the corporation.</dd>\r\n      <dt>Preparing for and Conducting Effective Meetings.</dt>\r\n      <dd>The president is the presiding officer at all meetings and is responsible for conducting all association meetings. This means that the president must come prepared, know the items on the agenda, follow the agenda, and keep control throughout the meeting.</dd>\r\n      <dt>Being a Great Communicator.</dt>\r\n      <dd>The president must constantly communicate with everyone regarding the community. That includes other board members, association members, management staff, professionals such as the association attorney, accountant or engineer and other members of the greater community. The president should use every opportunity &mdash; newsletters, notices, letters, meetings, telephone conversations, email, web sites and personal conversations &mdash; to help everyone better understand the goals of the association.</dd>\r\n      <dt>Developing Goals and Priorities.</dt>\r\n      <dd>A good president will help define the association goals to serve as targets to direct the leadership\'s efforts. And once those goals are put in place, the president must ensure everyone works to reach their achievement.</dd>\r\n      <dt>Developing Others Within the Community.</dt>\r\n      <dd>The president should always be searching for other community members to help out, whether that is a volunteer worker, committee member or board member. It is always in the association\'s best interest to recruit and train members who want to serve the community. The president must attempt to identify people with good leadership skills to join in helping the association\'s leadership.</dd>\r\n      <dt>Working with the Manager.</dt>\r\n      <dd>To help avoid miscommunication, the president serves as the designated liaison between the board and the manager. The president and the manager should understand and agree to the specific expectations of the manager. It is difficult to hold someone accountable for expectations that they do not know exist. And, these expectations should be used to evaluate the manager\'s performance.</dd>\r\n    </dl>\r\n  </dd>\r\n  <dt>Secretary</dt>\r\n  <dd>As has been stated time and again, the association is a business. And like all businesses, it must record and preserve its history and maintain its records. As the Chief Information Officer, it is the association secretary who is assigned this responsibility.</dd>\r\n  <dd>Some of the key duties of the Secretary include:\r\n    <dl>\r\n      <dt>Being Responsible for Accurate Minutes of all Meetings.</dt>\r\n      <dd>The secretary is responsible for all minutes of the corporation. The secretary is responsible for accurate minutes to be taken, transcribed, and distributed to the board members. In addition, the secretary must ensure that all approved minutes are appropriately filed in the permanent records of the association. In many cases, some of these duties may be delegated to the manager, management staff or other paid professionals. However, the secretary is still responsible for ensuring that accurate minutes of all meetings are maintained.</dd>\r\n      <dt>Being Responsible for Giving Notices and Accepting Proxies.</dt>\r\n      <dd>The secretary is responsible for sending all notices to board members and to association members as required by law and / or the governing documents of the association. This icludes notices of board meetings and annual membership meetings. Additionally, at the time of the annual membership meeting, it is the secretary who is to receive and verify any proxies returned by the members. Just like being responsible for the minutes, some duties of the secretary may be assigned to paid professionals. However, the secretary remains responsible for their action to ensure notices are properly given and proxies correctly recorded.</dd>\r\n      <dt>Acting as the Official Custodian of All Records and Files.</dt>\r\n      <dd>The secretary of the corporation is responsible for the maintenance of all records and files of the association. That includes not only meeting notices and minutes, but also the governing documents, tax returns, contracts, insurance policies, warranties, legal files, and correspondence. And just one more reminder: if these duties of the secretary are assigned to the manager, management staff or other paid professional, the secretary remains responsible for their appropriate and correct filing and storage.</dd>\r\n    </dl>\r\n  </dd>\r\n  <dt>Treasurer</dt>\r\n  <dd>The treasurer is the financial voice of the association and serves as the Chief Financial Officer of the corporation. It might seem that being treasurer is a difficult and technically challenging postition; however, the association\'s accountant and manager can lend a great deal of assistance to helping a board member fulfill the treasurer\'s duties.</dd>\r\n  <dd>Some of the key duties of the Treasurer include:\r\n    <dl>\r\n       <dt>Understanding the Association\'s Financial Condition.</dt>\r\n       <dd>The treasurer must be intimately familiar with the association\'s financial condition and be able to explain it to other directors, and association members as well. That includes knowing that funds are being invested in accordance with adopted board policy, that appropriate action is being taken on all delinquent member accounts, and that the association liabilities are being paid timely.</dd>\r\n       <dt>Reviewing Association Financial Statements.</dt>\r\n       <dd>The treasurer needs to carefully review the financial statements prepared by the management company and obtain answers to any questions. At the board meetings, the treasurer should make a brief presentation about any significant items in the financial statement and, with the help of the manage, answer questions from the other directors. The treasurer, with the aid of the auditor if necessary, should report on the financial condition of the association at the annual membership meeting.</dd>\r\n       <dt>Overseeing the Budget Process.</dt>\r\n       <dd>One of the most important roles of the treasurer is overseeing the preparation of the annual budget. While some tasks may be delegated to a committee or to the manager, the treasurer remains the responsible person for ensuring everyone completes their assigned tasks and that the proposed budget is presented to the board of directors in a timely manner.</dd>\r\n       <dt>Ensuring an Adequate Replacement Reserve Program.</dt>\r\n       <dd>As part of the role as CFO, the treasurer is responsible to making sure the replacement reserves are adequate to meeting the future needs of the community. With the assistance of an appropriate engineering study, the amount of funds for future major expenditures can be determined. The treasurer must present this information to the board as part of the budgeting process and include the needed amounts in the proposed budget.</dd>\r\n       <dt>Protecting Association Assets.</dt>\r\n       <dd>The treasurer is responsible for safeguarding the assets of the association. To do so, the treasurer will need to work directly with the association\'s insurance agent to determine the types and amount of appropriate coverage needed by the association. In addition, the treasurer should review all internal controls with the association\'s auditor and manager to ensure the highest level of protection against fraud or embezzelment.</dd>\r\n       <dt>Working with the Association\'s Accountant.</dt>\r\n       <dd>The treasurer is the liason between the board of directors and its accounting firm or CPA. The treasurer is responsible for monitoring the progress of the audit report, meeting with the auditor to understand the report as well as reviewing and understanding the association\'s tax returns.</dd>\r\n    </dl>\r\n  </dd>\r\n</dl>',1628,'2004-02-02 15:08:24','2009-04-14 21:43:02',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('cd4c6ea492fa118ffb64ed267f415084','1fcbed66aad681e22d2a63da2a6e678d','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'forms','Notification and Authorization Forms',NULL,NULL,NULL,'<p>Prior to completing these forms, please be sure you have read and understood the relevant sections of the <a href=\\\"[[url:e1f74c42072a5968deafd209dd6f266a]]\\\">Bylaws</a> and <a href=\\\"[[url:5dfb52f0d3c267f24719af160e0faa13]]\\\">Covenants</a>.</p>\\r\\n\\r\\n[[tree:cd4c6ea492fa118ffb64ed267f415084]]',23,'2004-02-10 22:43:21','2012-06-26 15:15:34',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('9bc4dc862070e4a5c7fafeac067084ef','1fcbed66aad681e22d2a63da2a6e678d','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'notifications','Homeowner Notifications',NULL,NULL,NULL,'<p>Below are various notifications that have been sent to homeowners regarding Association issues.</p>\\r\\n\\r\\n[[tree:9bc4dc862070e4a5c7fafeac067084ef]]',13,'2004-02-20 10:58:24','2012-06-26 15:06:59',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('b0b5abe1851eaf46567beb1cfbab9fc4','be692e558d693d71efa98ec0617e4e64','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'candidate','Candidate Information Form',NULL,NULL,NULL,'<p>I would like to submit my name as a candidate for a position on the Board of Directors of the Association, which consists of three (3) Members. I understand that being a Director requires a commitment of my time, and that if I am elected, I agree to the following minimum conditions:</p>\\r\\n<ul>\\r\\n  <li>Two hours per week working Association issues;</li>\\r\\n  <li>Responding to telephone or email messages within one (1) business day;</li>\\r\\n  <li>Attending monthly two-hour Board of Directors meetings;</li>\\r\\n  <li>Attending all other meetings of the Association.</li>\\r\\n</ul>\\r\\n\\r\\n<div style=\\\"border-top: 1px solid black; margin: 3em 50% 0em 0em; text-align: left\\\">Print Name</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 50% 0em 0em; text-align: left\\\">Signature</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 50% 0em 0em; text-align: left\\\">Street Address</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 50% 0em 0em; text-align: left\\\">Homeowner Since</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 50% 0em 0em; text-align: left\\\">Evening / Home Telephone Number</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 50% 3em 0em; text-align: left\\\">Email Address</div>\\r\\n\\r\\nI submit the following information about myself, relevant experience, and my candidacy for the Board of Directors to be published for the purpose of the upcoming election. If I desire, I understand that I may also provide a photograph of myself to be displayed online and in the Annual Meeting notice. (Please limit to a maximum of 250 words.)\\r\\n\\r\\n<div style=\\\"border-top: 1px solid black; margin: 3em 1em 0em 0em; text-align: left\\\">Personal Information / Running platform</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 1em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 1em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 1em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 1em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 1em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 1em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 1em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 1em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 1em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2em 1em 1em 0em; text-align: left\\\">&nbsp;</div>\\r\\n\\r\\nReturn to:\\r\\n<ul>\\r\\n  <li>{{ORG_ADDRESS}}</li>\\r\\n  <li>{{ORG_PHONE_FAX}} Fax</li>\\r\\n</ul>\\r\\nPlease submit no less than 14 days prior to the 1st Calling of the Annual Meeting. Announcement for the Annual Meeting must be distributed to all homeowners 10 days prior to the election.\\r\\n<p>Thank you for your time and interest in the Association. Please contact any of the current Board members or the HOA\\\'s management company if you have questions at {{ORG_EMAIL_BOARD}} or {{ORG_EMAIL_MANAGEMENT}}.</p>',413,'2004-02-24 11:07:48','2012-02-20 11:21:55',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('3a48a0ec95a990ca2a0e3550f83da27e','f978cd35e6e3d30ccec5cd80c2edc310','684ab9dafd44c57dd85a08dd18813024',NULL,0,'incorporation','Articles of Incorporation','','','','',1,'2011-09-15 11:22:10','2020-04-20 17:14:49',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('d2c542a57f3815cae59afdf855bbf11f','be692e558d693d71efa98ec0617e4e64','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'appeal','Homeowner Right of Appeal',NULL,NULL,NULL,'<h2 style=\\\"text-align: center\\\">{{ORG_NAME}}<br/>Homeowner Right of Appeal</h2>\\r\\nYou have the right to dispute the validity of your debt or ruling of a Committee by an appeal to the Board of Directors. Convincing evidence must be presented with this request for any reversal of prior decisions.\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 40% 0em 0em; text-align: left\\\">Print Full Name</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 40% 0em 0em; text-align: left\\\">Address</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\\\">Telephone Number</div>\\r\\n<h4>Briefly describe your reason for this appeal:</h4>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n\\r\\n<h4>Include copies of all evidence relevant to your request. (ex: copies of cancelled checks, letters received and sent, etc.)</h4>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 40% 0em 0em; text-align: left\\\">Signature</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\\\">Date of Appeal</div>\\r\\n\\r\\n<br />\\r\\nPlease send this completed form to:<br /><br />\\r\\n{{ORG_ADDRESS}}<br />\\r\\n<br />\\r\\n{{ORG_PHONE_FAX}} Fax<br />\\r\\n<br />\\r\\nThis Form Will Be Reviewed By The Board of Directors. Please Allow 10-14 Business Days For A Response.<br />\\r\\n<br />',225,'2004-04-23 21:45:46','2012-04-12 14:10:25',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('6d08153f2b887390cd2f6870e9345867','be692e558d693d71efa98ec0617e4e64','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'paymentplan','Payment Plan Request',NULL,NULL,NULL,'{{LETTER_NOPRINT}}\r\n{{LETTER_HEAD}}\r\n\r\n<h2 style=\"text-align: center;\">Payment Plan Request</h2>\r\n<p>Homeowners that are unable to pay their annual assessment or other fees in full due to financial hardship may request a payment plan.</p>\r\n<p>Please note, interest shall accrue on the unpaid balance at the rate of {{BUDGET_INTEREST_RATE}} percent per annum, compounded monthly. Accrued interest shall be added to the unpaid balance.</p>\r\n<p>Your first payment must be submitted along with this plan in order for it to be valid.</p>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 40% 0em 0em; text-align: left\">Print Full Name</div>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 40% 0em 0em; text-align: left\">Address</div>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\">Telephone Number</div>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\">Email Address</div>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\">Today\'s Date</div>\r\n\r\n<h4>Briefly describe your reason for this request:</h4>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\">&nbsp;</div>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\">&nbsp;</div>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\">&nbsp;</div>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\">&nbsp;</div>\r\n\r\n<h4>Payment Plan (Payment amount and term requested, eg. \"$50 the first of each month.\"):</h4>\r\n<p>Payments must be no less than $25.00 per month. If a payment is late, a penalty of {{BUDGET_FEE_COLLECTION}} shall be added to the unpaid balance. If a check tendered is returned or declined a charge of {{BUDGET_FEE_RETURNED}} shall be added to the unpaid balance of the loan, and all future payments shall be accepted only in guaranteed funds (money order or certified check). Failure to follow the payment plan will result in immediate collection action.</p>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\">Payment Amount and Frequency</div>\r\n<h4>Payment plans may not be for terms longer than 18 months.</h4>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\">Final Payment Date</div>\r\n<div style=\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\">Signature</div>\r\n\r\n</div>',315,'2004-04-23 22:29:29','2011-09-16 22:26:55',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('c917d559f595f047669e52baec5c30fe','f978cd35e6e3d30ccec5cd80c2edc310','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'collection','HOA Dues and Collection Policy',NULL,NULL,NULL,'<p>Your Board of Directors has taken a firm stance on collection of late assessments. This policy is not intended to punish homeowners, but to be fair to all homeowners who pay their assessments on time. If late assessments were allowed to accrue, homeowners who paid on time and in good faith would be forced to subsidize those who do not pay: either assessments would have to be raised, or reserves would dwindle. The Association as a whole would suffer financially because of the irresponsibility of a few.</p>\\r\\n\\r\\n<p>No owner may, for any reason, exempt himself from liability for such assessments. The invoiced annual homeowner\\\'s Association dues must be paid as indicated and on time. Should a homeowner disapprove of the way the Association is being managed, the homeowner does not have the right to withhold the dues in protest; the Covenants and Bylaws specifically prohibit such action. All homeowners have a legal right to appeal any decision or action, financial or otherwise, of the Association <em>in writing</em>.</p>\\r\\n\\r\\n<p><strong>If, for any reason, you are unable to pay assessments by the due date, please contact the Association. We would prefer to amicably work out a payment plan rather than begin legal proceedings.</strong></p>\\r\\n\\r\\n<p>An unpaid $200.00 assessment can become a $2,000.00 obligation within one year with interest, late charges, and legal expenses if there is no attempt from the homeowner to rectify the situation. It is important that you completely understand the collection procedures. Therefore, please review the following collection policy:</p>\\r\\n\\r\\n<ul>\\r\\n  <li>The HOA retains a company to collect assessments (current and delinquent). They will not accept cash delivered by mail or in person. Personal checks and/or money orders will be accepted if payable to the Association. Assessments may also be paid by credit card, but include an additional 4% processing fee.</li>\\r\\n  <li>A homeowner may request and the Association shall provide a receipt for the payment of assessments if the owner requests in writing such a receipt.</li>\\r\\n  <li>It is the owner\\\'s responsibility to allow ample time for the mailing of payments, and payments must be received by 5:00PM on the due date &mdash; <strong>postmarks on or after the due date are considered late</strong>. In all cases, every owner shall be responsible for payment within the given time limit regardless of circumstances.</li>\\r\\n  <li>An owner may dispute any Notice if the owner submits to the Board a written explanation (\\\"Explanation Letter\\\") of the reason for his or her dispute within fifteen days of the postmark of any Notice. If the owner submits an Explanation Letter in a timely fashion, the Association, by and through its Board, shall respond, in writing, to the owner within fifteen days of the date of the postmark of the owner\\\'s Explanation Letter.</li>\\r\\n  <li>Any owner who is unable to pay assessments is entitled to make a written request for a payment plan to be considered by the Board of Directors. An owner may also request to meet with the Board in executive session to discuss a payment plan. The Board will consider payment plan requests on a case-by-case basis and is under no obligation to grant payment plan requests.</li>\\r\\n  <li>The total amount due the Association MUST be paid in full in order to reinstate the delinquent account and prevent legal action. Payments will be applied in the following order:\\r\\n    <ol>\\r\\n      <li>Fines</li>\\r\\n      <li>Legal Fees and Costs</li>\\r\\n      <li>Interest</li>\\r\\n      <li>Late Charges</li>\\r\\n      <li>Special Assessments</li>\\r\\n      <li>Assessments</li>\\r\\n    </ol>\\r\\n  </li>\\r\\n  <li>The HOA uses the provided registered address for all correspondence with the homeowner, as specified in the Association Bylaws. It is the homeowner\\\'s responsibility to provide correct or updated addresses.</li>\\r\\n</ul>\\r\\n\\r\\nThe Association intends to enforce collection of all amounts due by any and all methods available for enforcement of contractual obligations or liens, including judicial and non-judicial foreclosure of lien and legal action in court against the person or persons responsible for the amounts owed. The Board reserves the right to use any other lawful means which may now or hereafter be available for the collection of amounts due the Association.\\r\\n\\r\\n<h4>Schedule of Actions</h4>\\r\\n<p>Please review the below schedule regarding payment of dues and approximate dates of policy enforcement actions:</p>\\r\\n<ul>\\r\\n  <li>On December 1<sup>st</sup> or each preceding year, an annual HOA dues assessment invoice is sent to each homeowner of the Association.</li>\\r\\n  <li>Assessments are due in full on January 1<sup>st</sup> of the following year.</li>\\r\\n  <li>On February 1<sup>st</sup>, a second invoice is sent to those owners who have not paid with additional penalties and retroactive interest from January 1<sup>st</sup>.</li>\\r\\n  <li>On March 1<sup>st</sup>, a Late Notice is sent and charged to the account.</li>\\r\\n  <li>On April 1<sup>st</sup>, a Delinquent Notice is sent and charged to the account.</li>\\r\\n  <li>On May 1<sup>st</sup>, a Default Notice is sent and charged to the account. Request for Lien Notice is sent to the Board for action.</li>\\r\\n  <li>On June 1<sup>st</sup>, if approved, title search is performed, a property lien is prepared, and all necessary paperwork is filed with the courts. Legal expenses are added to the account; a copy of the lien paperwork is sent to the homeowner. A Collection By Attorney Notice is sent to the homeowner.</li>\\r\\n  <li>On July 1<sup>st</sup>, all further collection efforts are handled by the Association\\\'s attorney, including property foreclosure.</li>\\r\\n</ul>\\r\\n\\r\\nThis process is designed to collect the annual assessment, not to own real estate. At numerous steps, the homeowner is afforded the opportunity to bring their account into balance. Throughout the process, there is opportunity for exceptions due to unusual or unfortunate circumstances.\\r\\n\\r\\n<p>Beyond this, there are numerous avenues for homeowners to regain ownership of a residence. It is important to note that the Association does not have any additional legal or liability exposure if we foreclose or evict. The HOA does not become responsible for the mortgage, taxes, insurance, care or upkeep. These are the responsibilities of the mortgage holder. We are not allowed to cut off electricity, water or telephone services to the residence. In fact, the HOA is advised not to have any direct contact with the defendants.</p>\\r\\n\\r\\n<h4>Late Charges and Legal Fee Amounts</h4>\\r\\nPlease review the below listing of late charges and legal fees that will be applied to delinquent accounts:\\r\\n<ul>\\r\\n  <li>The Association applies an 18% annual interest rate to all accounts and assessments past due as specified in the Covenants.</li>\\r\\n  <li>A charge of $35.00, in addition to late fees if applicable, will be assessed to any account whose payment has been returned or rejected for any reason.</li>\\r\\n  <li>A debt collection services charge of $25.00 will be added to the account for each Late, Delinquent, Demand, Default, Lien, Intent to Foreclose, Foreclosure or other similar Notices sent to an owner.</li>\\r\\n  <li>The cost to perform a deed / title search is $100.00</li>\\r\\n  <li>The cost to file a property lien is $195.00.</li>\\r\\n  <li>The cost to file a notice of payment on a property lien is $75.00.</li>\\r\\n  <li>The cost for each contact with / letter from the Association attorney is $150.00</li>\\r\\n  <li>The cost to file a non-judicial foreclosure is $950.00.</li>\\r\\n  <li>Any additional expenses incurred by the Association during the collection process will be added to the account.</li>\\r\\n</ul>\\r\\n<em>The costs above are estimates and may in fact be greater than those stated and are subject to change without notice.</em>',1234,'2004-06-22 14:54:47','2012-06-26 23:00:54',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('74070cf1b101ab84092845e05f86d84f','f978cd35e6e3d30ccec5cd80c2edc310','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'faq','Frequently Asked Questions',NULL,NULL,'Below is a list of common questions that have been asked of the HOA. This document will change periodically as it is revised.','<h3>General Questions</h3>\r\n<ul>\r\n  <li><a href=\"#\"></a></li>\r\n</ul>\r\n\r\n<hr/>\r\n\r\n<h3>General</h3>\r\n<dl>\r\n  <dt><a id=\"\"></a></dt>\r\n  <dd></dd>\r\n</dl>',10,'2004-07-19 22:54:27','2011-09-15 11:38:29',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('7de98536ec03c26ef838041a1d940149','4be7b23ef05b04264e96637df1a1c70c','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'community','Community Information','','','','<p>Please see one of the topics below for information about :</p>\\r\\n\\r\\n<ul>\\r\\n  <li><a href=\\\"[[url:1fcbed66aad681e22d2a63da2a6e678d]]\\\">The HOA (Home Owners Association)</a>\\r\\n    <ul>\\r\\n      <li><a href=\\\"[[url:f978cd35e6e3d30ccec5cd80c2edc310]]\\\">Documents</a><br /><a href=\\\"[[url:e1f74c42072a5968deafd209dd6f266a]]\\\">Bylaws</a>, <a href=\\\"[[url:5dfb52f0d3c267f24719af160e0faa13]]\\\">Covenants</a>, and other legal documents, as well as new homeowner information and drawings of the neighborhood.</li>\\r\\n      <li><a href=\\\"[[url:cd4c6ea492fa118ffb64ed267f415084]]\\\">Forms</a><br />Various homeowner forms for voting by proxy, dispute resolution, and creating a payment plan.</li>\\r\\n      <li><a href=\\\"[[url:90e93e6ad8a2079a76fad6c6a31a8336]]\\\">Meeting Minutes</a><br />Details of all the HOA Board meetings.</li>\\r\\n      <li><a href=\\\"[[url:9bc4dc862070e4a5c7fafeac067084ef]]\\\">Notifications</a><br />Various notifications that have been sent to homeowners regarding Association issues.</li>\\r\\n    </ul>\\r\\n  </li>\\r\\n</ul>',108,'2005-03-15 15:08:38','2020-04-20 17:16:14',NULL,NULL,_binary 'a:2:{i:0;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";i:1;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";}',0),('be692e558d693d71efa98ec0617e4e64','cd4c6ea492fa118ffb64ed267f415084','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'general','General Forms',NULL,NULL,NULL,'[[tree:be692e558d693d71efa98ec0617e4e64]]',1,'2005-07-07 15:08:38','2012-06-26 15:15:56',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('812c06c9ff112bb4af80ef74507d7ad0','f978cd35e6e3d30ccec5cd80c2edc310','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'timeline','HOA Time Line &amp; Historical Dues Rates',NULL,NULL,NULL,'<p>This document is a rough time line and list of historical changes the HOA has gone through during its history. Additionally, HOA dues rates have fluctuated numerous times, and this document is an attempt to correlate related events.</p>\r\n\r\n<table>\r\n  <colgroup>\r\n    <col class=\"legend\" />\r\n    <col span=\"4\" />\r\n    <col class=\"notes\" />\r\n  </colgroup>\r\n  <thead>\r\n    <tr>\r\n      <th>Year</th>\r\n      <th>Annual Dues<a href=\"#legend_annual\"><sup>*</sup></a></th>\r\n      <th>Percent Increase &plusmn;</th>\r\n      <th>Authorized Dues<a href=\"#legend_authorized\"><sup>*</sup></a></th>\r\n      <th>Percent of Authorized</th>\r\n      <th>Notes / Major Events</th>\r\n    </tr>\r\n  </thead>\r\n  <tfoot>\r\n    <tr>\r\n      <th>Year</th>\r\n      <th>Annual Dues<a href=\"#legend_annual\"><sup>*</sup></a></th>\r\n      <th>Percent Increase &plusmn;</th>\r\n      <th>Authorized Dues<a href=\"#legend_authorized\"><sup>*</sup></a></th>\r\n      <th>Percent of Authorized</th>\r\n      <th>Notes / Major Events</th>\r\n    </tr>\r\n  </tfoot>\r\n  <tbody>\r\n    <tr>\r\n      <td>2009</td>\r\n      <td class=\"currency\">$180.00</td>\r\n      <td class=\"center\">n/a</td>\r\n      <td class=\"currency\">$180.00</td>\r\n      <td class=\"center\">100%</td>\r\n      <td>Association turned over to homeowners from Developer; Board Members: .</td>\r\n    </tr>\r\n    <tr>\r\n      <td>2010</td>\r\n      <td class=\"currency\">$198.00</td>\r\n      <td class=\"center\">+10%</td>\r\n      <td class=\"currency\">$198.00</td>\r\n      <td class=\"center\">100%</td>\r\n      <td>HOA Management hired in February as the management company for the Association; Board Members: .</td>\r\n    </tr>\r\n    <tr>\r\n      <td>2011</td>\r\n      <td class=\"currency\">$180.00</td>\r\n      <td class=\"center\">-18%</td>\r\n      <td class=\"currency\">$217.80</td>\r\n      <td class=\"center\">82%</td>\r\n      <td>Cost savings allowed lowering of dues rate; Board Members: .</td>\r\n    </tr>\r\n  </tbody>\r\n</table>\r\n\r\n<p>Legend:</p>\r\n<dl>\r\n  <dt id=\"legend_annual\">Annual Dues</dt>\r\n  <dd>The actual dollar rate set for HOA annual dues during the specified year.</dd>\r\n  <dt id=\"legend_authorized\">Authorized Dues</dt>\r\n  <dd>The authorized dollar rate as defined by the Covenants.</dd>\r\n</dl>',390,'2005-12-28 13:29:48','2011-09-23 12:48:16',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('86c1c16e00648b4c9ddf54cb7086a928','1fcbed66aad681e22d2a63da2a6e678d','684ab9dafd44c57dd85a08dd18813024','6d4296469afc023ee66251364204ba48',0,'incorporation','Articles of Incorporation',NULL,NULL,NULL,NULL,1,'2009-07-15 21:17:54','2010-07-08 16:25:05',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('e4f8ecb0cbe2ed8dcfac5ffd1536df19','90e93e6ad8a2079a76fad6c6a31a8336','684ab9dafd44c57dd85a08dd18813024',NULL,0,'2020','2020 Meeting Minutes',NULL,NULL,NULL,'[[tree:e4f8ecb0cbe2ed8dcfac5ffd1536df19]]',1,'2010-01-29 22:16:56','2020-04-14 17:58:34',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('a464f62a3517891542b61431ec424936','9bc4dc862070e4a5c7fafeac067084ef','684ab9dafd44c57dd85a08dd18813024',NULL,0,'2020','2020 Notifications',NULL,NULL,NULL,'[[tree:a464f62a3517891542b61431ec424936]]',1,'2010-03-23 17:04:58','2020-04-14 19:52:19',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('b80d8187d963380376db1d2e1d864873','cd4c6ea492fa118ffb64ed267f415084','684ab9dafd44c57dd85a08dd18813024',NULL,0,'2020','2020 Forms',NULL,NULL,NULL,'[[tree:b80d8187d963380376db1d2e1d864873]]',1,'2010-03-23 17:06:37','2020-04-14 19:52:58',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('e1f74c42072a5968deafd209dd6f266a','f978cd35e6e3d30ccec5cd80c2edc310','684ab9dafd44c57dd85a08dd18813024',NULL,0,'bylaws','Bylaws','','','','',1,'2010-12-21 16:24:29','2020-04-20 17:15:05',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('aff47206689dfa723f832f5215bd940b','be692e558d693d71efa98ec0617e4e64','684ab9dafd44c57dd85a08dd18813024',NULL,0,'architecture_change','Architecture Change Request',NULL,NULL,NULL,'{{LETTER_NOPRINT}}\\r\\n{{LETTER_HEAD}}\\r\\n\\r\\n<h2 style=\\\"text-align: center;\\\">Architectural Alteration / Change Request Form</h2>\\r\\n\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 40% 0em 0em; text-align: left\\\">Print Full Name</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 40% 0em 0em; text-align: left\\\">Address</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\\\">Telephone Number</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\\\">Email Address</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\\\">Today\\\'s Date</div>\\r\\n\\r\\n==== Description of Proposed Alteration / Change: ====\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\\\">Planned Date for Work to Begin</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 70% 0em 0em; text-align: left\\\">Estimated Completion Date</div>\\r\\n<br />\\r\\nPlans Attached? __Yes__ __ No__<br />\\r\\n\\r\\n==== Proposed Location: ====\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n\\r\\n==== Proposed Elevation / Shape / Dimensions / Exterior Color Plans: ====\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n\\r\\n==== Type and Color of Material Used: ====\\r\\n<div style=\\\"border-top: 1px solid black; margin: 2.5em 0em 0em 0em; text-align: left\\\">&nbsp;</div>\\r\\n\\r\\n<br />\\r\\nPlease send this completed form to:<br /><br />\\r\\n{{ORG_ADDRESS}}<br />\\r\\n<br />\\r\\n{{ORG_PHONE_FAX}} Fax<br />\\r\\n<br />\\r\\nThis Form Will Be Reviewed By The Architecture Committee. Please Allow 10-14 Business Days For A Response.<br />\\r\\n<br />',250,'2012-01-09 12:44:25','2012-04-12 14:12:17',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('cd3c58aeb536db5dcd5fe0cb3a7d0f38','f978cd35e6e3d30ccec5cd80c2edc310','684ab9dafd44c57dd85a08dd18813024',NULL,0,'maintenance','Common Residential Maintenance Tasks',NULL,NULL,NULL,'<dl>\\r\\n<dt>Weekly</dt>\\r\\n<dd>Pool Maintenance\\r\\n  <ul>\\r\\n    <li>Empty all baskets as needed.</li>\\r\\n    <li>Clean pool as needed.</li>\\r\\n  </ul>\\r\\n</dd>\\r\\n\\r\\n<dt>Every month</dt>\\r\\n<dd>Check all plumbing (flush unused toilets, turn on unused water faucets).</dd>\\r\\n<dd>Inspect foundation, bricks, masonry, and other structural elements for cracking, heaving, or separation.</dd>\\r\\n<dd>Pool maintenance:\\r\\n  <ul>\\r\\n    <li>Backwash filter once per month.</li>\\r\\n    <li>Replace air filters as needed.</li>\\r\\n    <li>Test and reset GFCI outlets.</li>\\r\\n  </ul>\\r\\n</dd>\\r\\n\\r\\n<dt>Every three months</dt>\\r\\n<dd>Check lawn sprinkler operation and adjust nozzles for proper coverage and minimum over-spray.</dd>\\r\\n<dd>Clean and vacuum dust from air conditioning vents and returns.</dd>\\r\\n<dd>Fertilize and feed landscaping.</dd>\\r\\n<dd>Inspect the attic and ceilings for water damage / leaks.</dd>\\r\\n<dd>Inspect walls, grout, and paint for damage and wear and repair as necessary.</dd>\\r\\n<dd>Prune trees and shrubs.</dd>\\r\\n\\r\\n<dt>Every six months</dt>\\r\\n<dd>Pool maintenance\\r\\n  <ul>\\r\\n    <li>A <em>Diatomaceous Earth</em> filter should be taken apart and cleaned twice a year.</li>\\r\\n  </ul>\\r\\n</dd>\\r\\n\\r\\n<dt>Annually</dt>\\r\\n<dd>Check your chimney before use; creosote can build up inside the walls and cause a fire\\r\\n  <ul>\\r\\n    <li>Remember, open the flue before lighting.</li>\\r\\n    <li>Extinguish all flames and embers completely when the fire is finished.</li>\\r\\n  </ul>\\r\\n</dd>\\r\\n<dd>Check your electric panel for any overloaded circuits; this can be as simple as feeling the breakers to see if they\\\'re warm to the touch.</dd>\\r\\n<dd>Check window and door frames for cracks, leaks or shifting and replace caulk and weather sealing as necessary.</dd>\\r\\n<dd>Clean all lawn and garden tools.</dd>\\r\\n<dd>Clean gutters and downspouts.</dd>\\r\\n<dd>Dispose of old batteries, chemicals, and prescriptions by contacting your city\\\'s hazardous waste department.</dd>\\r\\n<dd>Have your heating and air conditioning equipment serviced.</dd>\\r\\n<dd>Inspect and flush water heater. Test the pressure release valve.</dd>\\r\\n<dd>Paint and reseal exterior as needed.</dd>\\r\\n\\r\\n<dt>Spring</dt>\\r\\n<dd>Apply crabgrass preventer by April 1. Sunny slopes and lawns near sidewalks warm up quicker than the rest of the lawn and may need early treatment.</dd>\\r\\n<dd>Crabgrass thrives in hot weather and continues to germinate until late summer. Treat your lawn again with crabgrass preventer six to eight weeks after the spring treatment, or according to the manufacturer\\\'s directions.</dd>\\r\\n\\r\\n<dt>Autumn</dt>\\r\\n<dd>Autumn weed pre-emergent applications for those in North Texas should be done Sept. 10-20. Two different products should be applied for complete winter annual weed control: products with isoxaben as their active ingredient for broadleafed weeds and for winter grasses use Betasan, Dimension, Team, Pre-M or other recommended products.</dd>\\r\\n\\r\\n<dt>Holidays / Christmas</dt>\\r\\n<dd>Keep trees moist in lots of water.</dd>\\r\\n<dd>When trees dry out, discard them. Dry trees are fire hazards.</dd>\\r\\n<dd>Artificial trees must be fire resistant!</dd>\\r\\n<dd>All holiday lights should bear the UL (Underwriters Labroratories) seal.</dd>\\r\\n<dd>Do not overload outlets.</dd>\\r\\n<dd>No wires under carpets.</dd>\\r\\n<dd>Do not leave unattended candles burning.</dd>\\r\\n</dl>',419,'2012-02-27 19:48:06','2012-10-26 13:42:41',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0),('a5a66e73df638f139caa1cd2df92c239','f978cd35e6e3d30ccec5cd80c2edc310','684ab9dafd44c57dd85a08dd18813024',NULL,0,'emergency_kit','Emergency Preparedness Kit',NULL,NULL,NULL,'In the event of an emergency, it\\\'s recommended you have the following items ready in case you need to evacuate your residence or make due without electricity or water service for an extended period of time.\\r\\n\\r\\n* Water: one gallon of water per person per day for at least three days, for drinking and sanitation.\\r\\n* Food: at least a three-day supply of non-perishable food\\r\\n* Battery-powered or hand crank radio and a NOAA Weather Radio with tone alert and extra batteries for both\\r\\n* Flashlight and extra batteries\\r\\n* First aid kit\\r\\n* Whistle to signal for help\\r\\n* Dust mask, to help filter contaminated air and plastic sheeting and duct tape\\r\\n* Wrench or pliers to turn off utilities\\r\\n* Manual can opener if kit contains canned food\\r\\n* Local maps\\r\\n* Charger for mobile phones\\r\\n* Cash, travelers checks and change in case ATMs are unavailable or something prevents the use of credit/debit cards\\r\\n\\r\\nAdditional items to consider:\\r\\n* Prescription medicine and glasses\\r\\n* Infants: formula, diapers, bottles\\r\\n* Pets: food, additional water, leash, medications\\r\\n* Important family documents such as copies of insurance policies, identification and bank account records in a waterproof, portable container\\r\\n* Sleeping bag or warm blanket for each person\\r\\n* Complete change of clothing including sturdy shoes, long sleeved shirt and long pants\\r\\n* Household chlorine bleach and medicine dropper\\r\\n* Fire extinguisher\\r\\n* Matches in a waterproof container\\r\\n* Feminine supplies and personal hygiene items\\r\\n* Mess kits, paper cups, plates and plastic utensils, paper towels\\r\\n* Paper and pencil\\r\\n* Books, games, puzzles and other activities for children\\r\\n\\r\\nEmergency Items for your Car\\r\\n* Hazard triangles (or flares)\\r\\n* Jumper cables (at least 6-gauge)\\r\\n* Flashlight with spare batteries\\r\\n* Tow rope\\r\\n* Duct tape\\r\\n* Small bag of sand or kitty litter\\r\\n* Windshield scraper, emergency blanket, and hand warmers',277,'2012-04-11 11:53:27','2014-01-29 17:06:14',NULL,NULL,_binary 'a:2:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"93dc7abc06d1c91879cea7f30d6b8705\";}',0);
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachments` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) DEFAULT NULL,
  `owners` blob,
  `datecreated` datetime DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `mimetype` varchar(255) DEFAULT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `height` smallint(5) unsigned DEFAULT NULL,
  `width` smallint(5) unsigned DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget`
--

DROP TABLE IF EXISTS `budget`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget` (
  `id` varchar(32) DEFAULT NULL,
  `parent_entry_id` varchar(32) DEFAULT NULL,
  `account_id` varchar(32) DEFAULT NULL,
  `invoice_id` varchar(32) DEFAULT NULL,
  `checknum` varchar(32) DEFAULT NULL,
  `amount` decimal(9,2) DEFAULT NULL,
  `interest_rate` float DEFAULT '0',
  `memo` varchar(255) DEFAULT NULL,
  `description` text,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `datedue` date DEFAULT NULL,
  `datelate` date DEFAULT NULL,
  `dateposted` date DEFAULT NULL,
  `customer_id` varchar(32) DEFAULT NULL,
  `vendor_id` varchar(32) DEFAULT NULL,
  `category_id` varchar(32) DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `account_id` (`account_id`,`vendor_id`,`user_id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `user_id` (`user_id`),
  KEY `customer_id_index` (`customer_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget`
--

LOCK TABLES `budget` WRITE;
/*!40000 ALTER TABLE `budget` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_account`
--

DROP TABLE IF EXISTS `budget_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget_account` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) DEFAULT NULL,
  `account_num` varchar(32) DEFAULT NULL,
  `contact_id` varchar(32) DEFAULT NULL,
  `description` text,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_account`
--

LOCK TABLES `budget_account` WRITE;
/*!40000 ALTER TABLE `budget_account` DISABLE KEYS */;
INSERT INTO `budget_account` VALUES ('249a3f3e548b747c86b4ec934cbf7ef9','Income Account','',NULL,'Default income account','2020-05-01 22:27:27','2020-05-01 22:27:27','684ab9dafd44c57dd85a08dd18813024',0),('e1ecefdb6ff1977ddfc2c0a807745b67','Expense Account','',NULL,'Default expense account','2020-05-01 22:27:42','2020-05-01 22:27:42','684ab9dafd44c57dd85a08dd18813024',0);
/*!40000 ALTER TABLE `budget_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_budget`
--

DROP TABLE IF EXISTS `budget_budget`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget_budget` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) DEFAULT NULL,
  `datestart` date DEFAULT NULL,
  `dateend` date DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_budget`
--

LOCK TABLES `budget_budget` WRITE;
/*!40000 ALTER TABLE `budget_budget` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_budget` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_category`
--

DROP TABLE IF EXISTS `budget_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget_category` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(64) DEFAULT NULL,
  `parent_category` varchar(32) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_category`
--

LOCK TABLES `budget_category` WRITE;
/*!40000 ALTER TABLE `budget_category` DISABLE KEYS */;
INSERT INTO `budget_category` VALUES ('d78063fbef447a00436158fd0d9fdf97','Deed Restriction Violations','a9c7d52cc6263d7a54b1e9020ac576d0','Fees involved in fining and/or resolving deed restriction violations on owners\\\' property','2012-06-30 12:17:26','2020-04-14 23:59:21','684ab9dafd44c57dd85a08dd18813024',17179885568),('cfb65a4d257b12999004c2e235d8b3d2','Utilities',NULL,NULL,'2006-10-03 11:37:35','2011-10-21 15:09:07','684ab9dafd44c57dd85a08dd18813024',0),('84ddea8b84faf8d7fd4a4e3810cc9ca4','Water / Sewer','cfb65a4d257b12999004c2e235d8b3d2','','2006-10-03 11:38:17','2020-04-15 00:08:50','684ab9dafd44c57dd85a08dd18813024',2048),('901d91f866e3b914c89052000f9af4ef','Trash / Refuse','cfb65a4d257b12999004c2e235d8b3d2','','2006-10-03 11:51:42','2020-04-15 00:08:44','684ab9dafd44c57dd85a08dd18813024',2048),('f05922c80520e02fd84981e5001e5f82','Legal Fees',NULL,NULL,'2006-10-03 12:22:19','2009-05-03 21:00:59','684ab9dafd44c57dd85a08dd18813024',0),('e819d6bde6b151e8676a662d0df14a39','Lien Filing','f05922c80520e02fd84981e5001e5f82','','2006-10-03 12:23:07','2020-04-15 00:01:00','684ab9dafd44c57dd85a08dd18813024',8388608),('87c9f60ea254ef62d79bdc59c8de027b','Foreclosure Filing','f05922c80520e02fd84981e5001e5f82','','2006-10-03 12:23:27','2020-04-15 00:00:52','684ab9dafd44c57dd85a08dd18813024',8388608),('a9c7d52cc6263d7a54b1e9020ac576d0','HOA Membership Dues',NULL,NULL,'2006-11-13 11:50:19','2012-06-30 12:18:36','684ab9dafd44c57dd85a08dd18813024',0),('97f6bda19e992d99603720b9509a101c','Interest On Unpaid Assessment','a9c7d52cc6263d7a54b1e9020ac576d0','','2006-11-13 11:52:55','2020-04-14 23:59:38','684ab9dafd44c57dd85a08dd18813024',17180000256),('f50e4be262d9a2c04a5ecf717f05f702','Administrative Costs',NULL,'Administrative costs incurred by the Association.','2007-07-29 12:21:45','2012-06-30 12:16:08','684ab9dafd44c57dd85a08dd18813024',0),('2ad3e11969fef3f9f6c9917ce96e3f10','Late Fee','a9c7d52cc6263d7a54b1e9020ac576d0','Late fee for delinquent assessments.','2007-07-29 12:09:12','2020-04-14 23:59:59','684ab9dafd44c57dd85a08dd18813024',17180917760),('435529fe8c4a7def318b6b6113ff6c37','Collection / Administrative Services Fee','f50e4be262d9a2c04a5ecf717f05f702','Costs involved / incurred in collection of delinquent assessments.','2006-11-13 11:55:15','2020-04-14 23:54:26','684ab9dafd44c57dd85a08dd18813024',2048),('62a64938c05a287cf28a98b6057b7da6','Postage','f50e4be262d9a2c04a5ecf717f05f702','Cost of postage / mailing fee for letters or packages.','2007-07-29 12:26:28','2020-04-14 23:54:46','684ab9dafd44c57dd85a08dd18813024',2048),('801ebb4d4f46339b2d24b49db9c35ed2','Title Report','f05922c80520e02fd84981e5001e5f82','','2007-07-29 12:40:11','2020-04-15 00:02:28','684ab9dafd44c57dd85a08dd18813024',8388608),('fb563c18e0004ff0b50ed5449d936c1a','Lien Release','f05922c80520e02fd84981e5001e5f82','Fee to release a previously filed lien.','2007-07-29 12:40:54','2020-04-15 00:02:19','684ab9dafd44c57dd85a08dd18813024',8388608),('00d2e92a99376eef24efcaa7241a3a58','Attorney Fees','f05922c80520e02fd84981e5001e5f82','Various costs charged by the attorney.','2007-07-29 12:45:06','2020-04-15 00:00:32','684ab9dafd44c57dd85a08dd18813024',8388608),('c1e60ceea63d115b746da8a21b870858','Fines',NULL,'Fines for non-compliance with Association governing documents.','2008-09-06 15:38:40','2011-10-21 15:09:23','684ab9dafd44c57dd85a08dd18813024',0),('9f6c4e28427cdf8cac99daf23aa1351e','Failure to resolve deed restriction violation','c1e60ceea63d115b746da8a21b870858','A violation notice was sent but not resolved by the required date.','2008-09-06 15:41:03','2020-04-14 23:58:57','684ab9dafd44c57dd85a08dd18813024',17179885568),('d81106afc2b73099480a89109eb146b3','Demand Letter','f05922c80520e02fd84981e5001e5f82','Demand for payment letter','2008-09-19 13:57:28','2020-04-15 00:00:43','684ab9dafd44c57dd85a08dd18813024',8388608),('de0e450818e929a09aeaa31546e864f0','Storage / Rental Fees','f50e4be262d9a2c04a5ecf717f05f702','Storage of documents, equipment, and other items on behalf of the Association','2009-05-03 10:38:43','2020-04-14 23:54:51','684ab9dafd44c57dd85a08dd18813024',2048),('b11c91df6ad343558a7074c379dff63b','Office Supplies','f50e4be262d9a2c04a5ecf717f05f702','Miscellaneous office supplies including paper, envelopes, binders, et cetera.','2009-05-03 16:31:05','2020-04-14 23:54:41','684ab9dafd44c57dd85a08dd18813024',2048),('7335d988bdc2147efacae63acf1170f8','Special Assessments','a9c7d52cc6263d7a54b1e9020ac576d0','','2009-05-03 16:31:56','2020-04-15 00:00:17','684ab9dafd44c57dd85a08dd18813024',18522046528),('4334c17372b22e69513c43aa8b84cb9e','Answering Service','f50e4be262d9a2c04a5ecf717f05f702','Costs involved in receiving and answering homeowner, resident, and vendor inquiries','2009-05-03 20:24:13','2020-04-14 23:54:21','684ab9dafd44c57dd85a08dd18813024',2048),('fde57692169762202723396575c15fb2','Data Processing','f50e4be262d9a2c04a5ecf717f05f702','Entering information and generating and processing reports','2009-05-03 20:24:45','2020-04-14 23:54:31','684ab9dafd44c57dd85a08dd18813024',2048),('8386b9c944feb4823bab0e9296f55d5a','Repairs and Maintenance',NULL,NULL,'2009-05-03 20:26:29','2009-05-03 20:26:29','684ab9dafd44c57dd85a08dd18813024',0),('42ebcfa91d740266e25ac13ba9590b29','Air Conditioning / Heating','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:26:46','2020-04-15 00:02:55','684ab9dafd44c57dd85a08dd18813024',2048),('906e996b511ca637722df1a3ed52427b','Balcony Repairs','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:26:58','2020-04-15 00:03:04','684ab9dafd44c57dd85a08dd18813024',2048),('cc51e0b99da79071f1d56be429c1041c','Building Maintenance','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:27:11','2020-04-15 00:03:14','684ab9dafd44c57dd85a08dd18813024',2048),('bca7f4060fbbd988cf3716c7c4a422c4','Common Area Maintenance','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:27:23','2020-04-15 00:03:29','684ab9dafd44c57dd85a08dd18813024',2048),('ae85c70393d3efc76c7e381f53e4e0fa','Doors / Gates','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:27:33','2020-04-15 00:03:39','684ab9dafd44c57dd85a08dd18813024',2048),('64efc57c000b6c2442dd5688c8ab794e','Drainage / Gutters','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:27:44','2020-04-15 00:03:45','684ab9dafd44c57dd85a08dd18813024',2048),('56a5af218056cd20a6eb3a4f6ab4bd1d','Electrical','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:27:54','2020-04-15 00:03:50','684ab9dafd44c57dd85a08dd18813024',2048),('2e111d19c791dfcb1ca713fa8973de0c','Fences / Walls','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:28:04','2020-04-15 00:03:56','684ab9dafd44c57dd85a08dd18813024',2048),('cb26ca1377fb3e48216576bfb4bce8c1','Interior Plumbing / Exterior Faucets','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:28:17','2020-04-15 00:04:01','684ab9dafd44c57dd85a08dd18813024',2048),('91fa9f77f20be853e433773aaa419b4d','Landscape / Trees / Flowers','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:28:29','2020-04-15 00:04:09','684ab9dafd44c57dd85a08dd18813024',2048),('0be468dad85bf38719fa4176be51462d','Lights / Bulbs','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:28:39','2020-04-15 00:04:14','684ab9dafd44c57dd85a08dd18813024',2048),('1681b276e9713763ea3f3240ff55fca5','Locks / Keys / Access Codes','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:28:50','2020-04-15 00:05:04','684ab9dafd44c57dd85a08dd18813024',2048),('b85fa704d7a0b3dcb786431403ecbd65','Pavement / Parking Areas','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:29:01','2020-04-15 00:05:10','684ab9dafd44c57dd85a08dd18813024',2048),('5de60d2c27c7dbdd952db40e55f5d299','Pest Control','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:29:11','2020-04-15 00:05:14','684ab9dafd44c57dd85a08dd18813024',2048),('57d11e2cc01533e56a24810881bff10e','Pool / Spa / Water Park','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:29:26','2020-04-15 00:05:43','684ab9dafd44c57dd85a08dd18813024',2048),('5e449fa77b43d02e18b31e0bc9eef512','Roof / Shingles','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:29:38','2020-04-15 00:05:49','684ab9dafd44c57dd85a08dd18813024',2048),('1b6797da6c5e50296ef7a135b223740d','Sprinkler / Irrigation','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:29:49','2020-04-15 00:05:54','684ab9dafd44c57dd85a08dd18813024',2048),('7611e05a610e3334737f9cdd5f58ddb6','Windows / Screens / Glass','8386b9c944feb4823bab0e9296f55d5a','','2009-05-03 20:30:03','2020-04-15 00:05:59','684ab9dafd44c57dd85a08dd18813024',2048),('4675309de5568396fe4bc5c898ede4e3','Taxes and Insurance',NULL,NULL,'2009-05-03 20:30:14','2009-05-03 20:30:14','684ab9dafd44c57dd85a08dd18813024',0),('a2d97b0c9123e1676a7aaac6c4840794','Directors and Officers Liability','4675309de5568396fe4bc5c898ede4e3','','2009-05-03 20:30:35','2020-04-15 00:06:05','684ab9dafd44c57dd85a08dd18813024',2048),('01ae8a3e17154b379e95726ccacdd75e','General Liability Insurance','4675309de5568396fe4bc5c898ede4e3','','2009-05-03 20:30:46','2020-04-15 00:06:10','684ab9dafd44c57dd85a08dd18813024',2048),('f3e1c78eea5030285df5797c658d15cf','Income Taxes','4675309de5568396fe4bc5c898ede4e3','','2009-05-03 20:30:58','2020-04-15 00:06:15','684ab9dafd44c57dd85a08dd18813024',2048),('7651e21b1c9124b99dd66689038b8294','Property / Contents Insurance','4675309de5568396fe4bc5c898ede4e3','','2009-05-03 20:31:09','2020-04-15 00:06:19','684ab9dafd44c57dd85a08dd18813024',2048),('567c59552e6064ab2a73eaafa737e598','Taxes (Other)','4675309de5568396fe4bc5c898ede4e3','Property taxes, city, county, and state taxes and filings','2009-05-03 20:31:30','2020-04-15 00:06:24','684ab9dafd44c57dd85a08dd18813024',2048),('d8ead32b09bddcb1b291ba48263237b4','Umbrella Coverage Policy','4675309de5568396fe4bc5c898ede4e3','','2009-05-03 20:31:47','2020-04-15 00:06:30','684ab9dafd44c57dd85a08dd18813024',2048),('55a4d5d3214e791f3c647b9b4c74b771','Electrical','cfb65a4d257b12999004c2e235d8b3d2','','2009-10-13 22:11:08','2020-04-15 00:08:29','684ab9dafd44c57dd85a08dd18813024',2048),('89e8564c2bd281d12ca0e2d4a39e10e0','Association Assessments','a9c7d52cc6263d7a54b1e9020ac576d0','Regular Association assessment','2012-06-30 12:14:45','2020-04-14 23:58:46','684ab9dafd44c57dd85a08dd18813024',18522046465),('30efcccead195c017cd1d952d323e9e8','Management company','f50e4be262d9a2c04a5ecf717f05f702','Fees charged by the management company for services','2012-06-30 12:15:48','2020-04-14 23:54:35','684ab9dafd44c57dd85a08dd18813024',2048),('650024143b0fc3f867aa3576906225fb','Natural Gas','cfb65a4d257b12999004c2e235d8b3d2','','2012-06-30 12:18:10','2020-04-15 00:08:40','684ab9dafd44c57dd85a08dd18813024',2048),('247f618009af5511fc2b05615e15e108','Credits / Overpayments','a9c7d52cc6263d7a54b1e9020ac576d0','','2012-07-01 13:21:14','2020-04-14 23:58:39','684ab9dafd44c57dd85a08dd18813024',17179871232),('27b1f163a11ae88186e09582d48ba0ba','Sales Tax','4675309de5568396fe4bc5c898ede4e3','Sales tax paid by the association, if chosen to track it separately','2020-04-15 00:08:16','2020-04-15 00:08:16','684ab9dafd44c57dd85a08dd18813024',2048);
/*!40000 ALTER TABLE `budget_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_customer`
--

DROP TABLE IF EXISTS `budget_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget_customer` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `customer` varchar(64) DEFAULT NULL,
  `contact_id` varchar(32) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_customer`
--

LOCK TABLES `budget_customer` WRITE;
/*!40000 ALTER TABLE `budget_customer` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_invoice`
--

DROP TABLE IF EXISTS `budget_invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget_invoice` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `customer_id` varchar(32) DEFAULT NULL,
  `vendor_id` varchar(32) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `dateinvoice` date DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_invoice`
--

LOCK TABLES `budget_invoice` WRITE;
/*!40000 ALTER TABLE `budget_invoice` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_invoice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_track`
--

DROP TABLE IF EXISTS `budget_track`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget_track` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `category_id` varchar(32) DEFAULT NULL,
  `amount` decimal(9,2) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  `budget_id` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_track`
--

LOCK TABLES `budget_track` WRITE;
/*!40000 ALTER TABLE `budget_track` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_track` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_vendor`
--

DROP TABLE IF EXISTS `budget_vendor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget_vendor` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(64) DEFAULT NULL,
  `address1` varchar(128) DEFAULT NULL,
  `address2` varchar(128) DEFAULT NULL,
  `address3` varchar(128) DEFAULT NULL,
  `city` varchar(128) DEFAULT NULL,
  `state` char(2) DEFAULT NULL,
  `postalcode` varchar(10) DEFAULT NULL,
  `telephone_fax` varchar(14) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `telephone_work` varchar(14) DEFAULT NULL,
  `contact_id` varchar(32) DEFAULT NULL,
  `category_id` varchar(32) DEFAULT NULL,
  `federal_id` varchar(16) DEFAULT NULL,
  `state_id` varchar(16) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_vendor`
--

LOCK TABLES `budget_vendor` WRITE;
/*!40000 ALTER TABLE `budget_vendor` DISABLE KEYS */;
/*!40000 ALTER TABLE `budget_vendor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration` (
  `config_option` varchar(255) NOT NULL DEFAULT '',
  `config_value` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`config_option`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuration`
--

LOCK TABLES `configuration` WRITE;
/*!40000 ALTER TABLE `configuration` DISABLE KEYS */;
INSERT INTO `configuration` VALUES ('organization/mailing_address/state','TX'),('files/banned_username','banned_username.txt'),('files/censored_words','censored_words.txt'),('organization/letter/head','<div id=\"letter_head\"><h1>{{ORG_NAME}}</h1><h2>123 Anystreet &bull; Anytown, Texas 12345 &bull; {{ORG_PHONE}}</h2><h3>{{WEBSITE_URL}}</h3></div>'),('organization/letter/salutation','Dear Homeowner,'),('organization/letter/signature','Sincerely,<br />{{ORG_NAME}}'),('organization/mailing_address/line1','HOAM Demo HOA'),('organization/mailing_address/line2',''),('organization/mailing_address/line3','123 Anystreet'),('organization/mailing_address/city','Anytown'),('organization/mailing_address/postalcode','12345'),('organization/telephone','2145551212'),('organization/fax',''),('organization/name','HOAM Demonstration Site'),('user/default/censor','Yes'),('website/hostname','example.com'),('website/url','http://www.example.com/'),('website/mask_character','*'),('website/email/abuse','{{ORG_EMAIL_MANAGEMENT}}'),('website/email/root','{{ORG_EMAIL_MANAGEMENT}}'),('website/email/webmaster','{{ORG_EMAIL_MANAGEMENT}}'),('website/title','HOAM Demonstration Site'),('user/flags/residence_primary','32'),('homeowner/flag/residence_mailing','2'),('user/ids/root','684ab9dafd44c57dd85a08dd18813024'),('user/ids/system','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b'),('group/minimum_name_length','3'),('group/minimum_description_length','3'),('group/ids/anonymous','886c57ffc02b4c36a0e33822516d6854'),('group/ids/admin','f0d6c78a36bcec4024e0cd69444cf4cd'),('group/ids/board','d46ef1ab6b8c9d8b8d682614b960b3b7'),('group/ids/homeowner','7aa9a3db42211343c3267d4078f668ad'),('group/ids/officer','cbbbda9d1b7a4ee86e24a9195ab874e2'),('group/ids/registered','c5dd2dd866b5885eea92c0b8902136dc'),('group/ids/resident','ec695008e4c07f6f4e98b06cfc9060e6'),('group/ids/user_delete','a51ab63841aecf4d6aa5479d2a1342eb'),('group/ids/user_edit','1f4331e3a0d8bed99ee20ca4be666b6f'),('group/ids/user_add','cdac2f9def83d1f5dbac40236bb3f76e'),('group/ids/article_add','b3accac18f2ae3922b2c0f0f03ad002b'),('group/ids/article_delete','efde2d9c994c043b8a8713e71a30b8f4'),('group/ids/article_edit','457e60898dcc1282dd3bc7e0efb18e65'),('group/ids/attachment_add','512adb1af78f909268ca341d237ee1d8'),('group/ids/attachment_delete','87f240de2b702ee8e1a5c4f544ef6c37'),('group/ids/attachment_edit','c2ecc7a9cf60aab22ecb7425fdbbe08e'),('group/ids/group_add','509073d184bb736fd23441f1d2e49ff8'),('group/ids/group_delete','bece93d554ef766ac5fbf1c3c1d1109a'),('group/ids/group_edit','8c25d092851c113784352986e713c469'),('group/ids/homeowner_add','d4a5e68f6036d9ca55f3e51fe76a87e7'),('group/ids/homeowner_delete','1a5aabf4820d77d2aa67b8f0e049b598'),('group/ids/homeowner_edit','5b7fd1e597071930d03b8b1c1d3b2063'),('group/ids/messageboard_post','ac631ddcaf3e23dd186391537f31fde0'),('group/ids/news_edit','6843d53b9f9c6ac5190b9dac0e89d30e'),('group/ids/tasklist_personal','236cd151bbc3b5c623e2c634c2b2474c'),('group/ids/violation_add','c1bbea30bf2b2c07b8857d50db8b6ad6'),('group/ids/violation_approve','d4fad35282693aae1edc23173ed797be'),('group/ids/violation_delete','db20bd43d429199749cbcbad9857993c'),('group/ids/violation_edit','46904d8263f449c9a7060b4405c32cd8'),('group/ids/vote_add','f0d6c78a36bcec4024e0cd69444cf4cd'),('group/ids/vote_delete','f0d6c78a36bcec4024e0cd69444cf4cd'),('group/ids/vote_edit','8f5b8f6300e2e1922cc3c3b30404b0f3'),('group/ids/attachment_view','c62214faf64a01fb003deda32ad3ef70'),('user/minimum_accountname_length','5'),('user/minimum_password_length','5'),('user/minimum_firstname_length','2'),('user/minimum_lastname_length','2'),('user/minimum_password_hint_length','2'),('user/flags/acronyms','1'),('user/flags/censor','8'),('user/flags/convert_links','64'),('user/flags/holidays','32768'),('user/flags/smileys','2097152'),('group/flags/individual','1'),('budget/flags/apply_current_homeowner','131072'),('budget/flags/apply_fee_late','268435456'),('budget/flags/late_payment_plan','33554432'),('budget/flags/late_notice','524288'),('budget/flags/late_delinquent','1048576'),('budget/flags/late_default','2097152'),('budget/flags/late_attorney','4194304'),('budget/flags/assessment','1'),('budget/flags/assessment_other','8'),('budget/flags/assessment_special','64'),('budget/flags/credit','256'),('budget/flags/payment','33554432'),('budget/flags/fee_administration','2048'),('budget/flags/fee_fine','16384'),('budget/flags/fee_interest','131072'),('budget/flags/fee_legal','8388608'),('budget/flags/fee_late','1048576'),('budget/flags/sales_tax','134217728'),('budget/flags/account_closed','1'),('budget/flags/account_frozen','2'),('budget/flags/account_expense','4'),('budget/flags/account_homeowner','64'),('budget/flags/apply_interest','1073741824'),('budget/flags/apply_sales_tax','4294967296'),('group/flags/group','2'),('budget/sales_tax_rate','8.25'),('budget/interest_rate','18.00'),('homeowner/flags/ignore_violations','64'),('homeowner/flags/ignore_violations_temporary','512'),('homeowner/flags/ignore_budget','8'),('homeowner/flags/residence_mailing','32768'),('homeowner/flags/resident','1'),('article/flags/redirect','64'),('article/flags/draft','8'),('article/flags/comments','1'),('user/flags/disabled','4096'),('group/ids/everyone','93dc7abc06d1c91879cea7f30d6b8705'),('homeowner/minimum_name_length','2'),('homeowner/minimum_address1_length','5'),('homeowner/minimum_address2_length','0'),('homeowner/minimum_address3_length','5'),('homeowner/minimum_city_length','2'),('homeowner/default/city','Anytown'),('homeowner/default/state','TX'),('homeowner/default/postalcode','12345'),('lot/minimum_street_length','2'),('lot/minimum_address_length','1'),('lot/minimum_building_length','1'),('lot/minimum_suite_length','1'),('news/minimum_title_length','5'),('news/minimum_article_length','10'),('violation/category/minimum_detail_length','32'),('violation/category/minimum_description_length','3'),('violation/category/minimum_category_length','3'),('violation/severity/minimum_preamble_length','32'),('violation/severity/minimum_closing_length','32'),('violation/severity/minimum_days_resolution','3'),('violation/severity/minimum_name_length','1'),('violation/flags/assume_resolved','512'),('violation/days_to_pad_resolveby','2'),('violation/grace_after_purchase','31'),('attachment/maximum_file_size','104857600'),('organization/locale','en_US'),('organization/countrycode','us'),('budget/minimum_name_length','3'),('budget/minimum_description_length','0'),('attachment/flags/homeowner','512'),('attachment/flags/lot','32768'),('attachment/flags/violation','2097152'),('attachment/flags/vote','16777216'),('attachment/minimum_description_length','0'),('attachment/flags/budget','8'),('budget/flags/due_receipt','32'),('budget/flags/due_30','64'),('budget/flags/due_45','256'),('budget/flags/payment_plan','33554432'),('user/default/edit/row','8'),('article/minimum_article_length','0'),('user/default/edit/col','40'),('user/default/items_per_page','10'),('budget/account/income','249a3f3e548b747c86b4ec934cbf7ef9'),('budget/account/expense','e1ecefdb6ff1977ddfc2c0a807745b67'),('budget/account/reserve','1b01b5cab4a0f9c66244c680a6a2c60b'),('budget/account/operating','24457d4f28e240b1509546e8f9ac3b17'),('budget/account/saving','76f17df0f09ac3cad692fcd65c89246f'),('budget/fee/late','45.00'),('budget/fee/collection','15.00'),('budget/assessment/frequency','annual'),('budget/assessment/amount','150'),('organization/physical_address/line1','HOAM Demo HOA'),('organization/physical_address/line2',''),('organization/physical_address/line3','123 Anystreet'),('organization/physical_address/state','TX'),('organization/physical_address/city','Anytown'),('organization/physical_address/postalcode','12345'),('budget/flags/due_15','4'),('budget/ids/organization',''),('budget/ids/management',''),('user/flags/log','64'),('budget/paypal/email','paypal@{{WEBSITE_DOMAIN}}'),('budget/paypal/surcharge/amount','0'),('budget/paypal/surcharge/percent','4'),('article/ids/root','4be7b23ef05b04264e96637df1a1c70c'),('homeowner/flags/payment_received','4'),('homeowner/flags/packet_ready','1'),('homeowner/flags/packet_delivered','2'),('budget/flags/late_lien','67108864'),('budget/flags/late_attorney_demand','134217728'),('budget/flags/late_attorney_foreclosure','268435456'),('budget/flags/late_attorney_eviction','536870912'),('article/minimum_title_length','2'),('article/minimum_urlname_length','1'),('article/minimum_redirect_length','10'),('user/default/acronyms','Yes'),('user/default/convert_links','Yes'),('user/default/smileys','Yes'),('user/default/holiday','Yes'),('user/default/log','Yes'),('messageboard/default/time_to_edit','172800'),('messageboard/minimum_message_length','2'),('messageboard/minimum_subject_length','2'),('budget/invoice/fineprint','<dl>\r\n<dt>Collection Policy</dt>\r\n<dd>For more information about and to view the Association\'s collection policy, please see {{WEBSITE_URL}}community/hoa/documents/</dd>\r\n<dt>Payment Information</dt>\r\n<dd>Payments may be made by personal or cashier\'s check payable to <em>{{ORG_NAME}}</em>, or electronically using PayPal (http://www.paypal.com/). Please ensure that the property address referenced on this invoice is noted on your check or PayPal memo.</dd>\r\n<dd>Credit card payments are accepted ONLINE ONLY via PayPal. Cash is not accepted. Post-dated checks are not accepted. All returned and NSF payments will be charged a {{BUDGET_FEE_RETURNED}} processing fee in addition to any applicable late or collection fees.</dd>\r\n<dt>Payment Plan</dt>\r\n<dd>If you are currently unable to pay this debt, the Board of Directors has authorized us to offer you a payment plan. For more information, please see the Payment Plan form available on the Association\'s web site ({{WEBSITE_DOMAIN}}). Additionally, if you or your spouse is an active military service member you may have additional rights under the Servicemember\'s Civil Relief Act.</dd>\r\n<dt>PayPal Payments</dt>\r\n<dd>PayPal payments should be made to <em>{{PAYPAL_EMAIL}}</em> and must include an additional {{PAYPAL_SURCHARGE_PERCENT}}% surcharge of the Total Amount Due to cover fees charged to the Association. The calculated surcharge amount is noted on the front of this invoice. Please ensure that the property address referenced on this invoice is noted on your PayPal memo. Payments made via PayPal will be credited on the date the payment clears. It is not necessary to have a PayPal account to use PayPal. For instructions on paying your assessments using PayPal, see http://hoa-management.com/paypal/.</dd>\r\n<dt>Total Amount Due</dt>\r\n<dd>The total amount of all funds requiring payment to the Association.</dd>\r\n<dt>Total Due Date</dt>\r\n<dd>The expected payment date for the <em>Total Amount Due</em>.</dd>\r\n</dl>'),('website/smtp/password',NULL),('website/smtp/port',NULL),('website/smtp/server',NULL),('website/smtp/username',NULL),('budget/minimum_memo_length',NULL),('group/ids/budget_add','1c8483f11edb5273749b50aef71f802d'),('group/ids/budget_delete','eccef449c739f857757260eaa9e7ec4c'),('group/ids/budget_edit','9ded86aae579a9d3fde8a6a07210e650'),('group/ids/budget_view','206b88ed0d9eb5b3126d487177b39394'),('group/ids/lot_add','ccfcf56114e9e8a2b9becc6a33b419aa'),('group/ids/lot_delete','0a2855edcb165cf58257fdedc2b26fea'),('group/ids/lot_edit','a2650edfc1fa36d9956a8362a6b8ca74'),('group/ids/messageboard_view','eaeeff75a9f1b49df92c0a20a84ed837'),('group/ids/news_add','55243529ae58f57d672527ce2b666a09'),('group/ids/news_delete','5c6da80583761452ff618410c5496ff1'),('group/ids/report_view',NULL),('homeowner/flags/bankrupt','1'),('homeowner/flags/service_member','64'),('attachment/preview/enable','1'),('attachment/preview/width','120'),('attachment/preview/height','160'),('homeowner/flags/residence_sold','8'),('homeowner/flags/residence_off_market','16'),('attachment/flags/advertising','1'),('advertising/flags/position1','1'),('advertising/flags/position2','2'),('advertising/flags/position3','4'),('advertising/flags/position4','8'),('violation/days_to_reset','365'),('violation/default_severity','2eb49f4caa96003053928aaec53fcd65'),('organization/email/board','board@{{WEBSITE_DOMAIN}}'),('organization/email/officers','officers@{{WEBSITE_DOMAIN}}'),('organization/email/management','management@{{WEBSITE_DOMAIN}}'),('organization/fee/refinance','50'),('organization/fee/resale','100'),('organization/fee/expedite','50'),('lot/minimum_sqft_size','0'),('log/destinations','1'),('log/levels','127'),('budget/assessment/date','01/01/2020'),('budget/assessment/days_due','61'),('budget/assessment/days_late','61'),('budget/assessment/unit','single'),('plugin/ga/enabled','0'),('plugin/ga/code',NULL),('plugin/tasklist/enabled','1'),('plugin/tasklist/minimum_text_length','2'),('plugin/tasklist/flag/status_circumvented','64'),('plugin/tasklist/flag/status_complete','524288'),('plugin/tasklist/flag/status_hold','4'),('plugin/tasklist/flag/status_in_progress','128'),('plugin/tasklist/flag/status_not_started','0'),('plugin/tasklist/flag/status_postponed','32'),('user/days_before_delete','1096'),('user/days_before_disable','365'),('budget/flags/payment_plan_default','134217728'),('organization/physical_address/county','Dallas'),('organization/property_name','HOAM Demonstration Site'),('homeowner/send_current_resident','1'),('letter/envelope','1'),('letter/paper','0'),('budget/assessment/months_due','2'),('budget/assessment/time','months'),('user/flags/password_old','262144'),('website/locale','en_US.UTF-8'),('budget/assessment/months_late','2'),('work_request/flags/low','1'),('work_request/flags/normal','8'),('work_request/flags/high','64'),('work_request/flags/urgent','512'),('work_request/flags/new','1'),('work_request/flags/assigned','8'),('work_request/flags/in_progress','512'),('work_request/flags/on_hold','4096'),('work_request/flags/complete','2097152'),('work_request/allow_user_requests','1'),('budget/fee/returned','35.00'),('website/blurb',''),('budget/flags/category_income','17179869184'),('hoam/last_maintenance_run','2020-05-04'),('attachment/flags/work_request','134217728'),('log/browscap','/hoam/php_browscap.ini'),('log/browser','1'),('log/flags/advertisement','1'),('log/flags/article','8'),('log/flags/attachment','64'),('log/flags/budget_category','512'),('log/flags/budget_invoice','4096'),('log/flags/budget_vendor','32768'),('log/flags/group','262144'),('log/flags/homeowner','2097152'),('log/flags/lot','134217728'),('log/flags/user','68719476736'),('log/flags/violation','549755813888'),('log/flags/work_request','281474976710656'),('user/days_password_warn','600'),('user/days_password_age','720'),('lot/property','1'),('property/flags/rented','8'),('log/flags/property','8589934592'),('log/flags/violation_category','4398046511104'),('log/flags/violation_severity','35184372088832'),('property/flags/renew','1'),('property/flags/annual','64'),('property/flags/monthly','512'),('property/flags/weekly','4096'),('property/flags/daily','32768'),('lot/common/area','0'),('budget/category/dues','89e8564c2bd281d12ca0e2d4a39e10e0'),('budget/category/interest','97f6bda19e992d99603720b9509a101c'),('budget/category/late','2ad3e11969fef3f9f6c9917ce96e3f10'),('budget/category/property','89e8564c2bd281d12ca0e2d4a39e10e0'),('budget/paypal/enable','0'),('messageboard/flags/display_top','1'),('messageboard/flags/do_not_delete','64'),('messageboard/flags/no_more_comments','4096'),('attachment/flags/budget_vendor','64'),('attachment/flags/property','262144'),('budget/category/administrative','435529fe8c4a7def318b6b6113ff6c37'),('log/flags/messageboard','1073741824'),('wiki/default_groups','a:3:{i:0;s:32:\"f0d6c78a36bcec4024e0cd69444cf4cd\";i:1;s:32:\"d46ef1ab6b8c9d8b8d682614b960b3b7\";i:2;s:32:\"cbbbda9d1b7a4ee86e24a9195ab874e2\";}'),('website/online_time','5'),('website/idle_time','60'),('budget/flags/public','64'),('budget/flags/ignore_average','1024'),('work_request/flags/require_approval','64'),('violation/require_approval','1'),('violation/flags/approved','16'),('attachment/flags/homeowner_sale','1024'),('work_request/approval_group','0e77eeab91f4df2a270b036b600930bb'),('work_request/flags/approved','16'),('violation/approval_group','cbbbda9d1b7a4ee86e24a9195ab874e2'),('budget/payment_plan/interest','1'),('hoam/updating','0'),('budget/category/credit','247f618009af5511fc2b05615e15e108'),('work_request/flags/planning','64'),('work_request/flags/cancelled','32768'),('work_request/flags/rejected','262144'),('plugin/tasklist/flag/status_assigned','8'),('plugin/tasklist/flag/status_cancelled','8192'),('plugin/tasklist/flag/status_new','1'),('plugin/tasklist/flag/status_on_hold','1024'),('plugin/tasklist/flag/status_planning','32'),('plugin/tasklist/flag/status_rejected','65536'),('organization/letter/footer',''),('email/server',''),('email/username',''),('email/password',''),('email/auth','1'),('email/port',''),('email/automated','1'),('email/do_not_reply','1'),('website/timezone','America/Chicago'),('budget/zero_amount/enable','0'),('homeowner/flags/no_fees','4096'),('budget/flags/annual','16'),('attachment/flags/insurance','4096'),('budget/insurance/enable','1'),('log/flags/insurance','16777216'),('user/flags/email_validated','512'),('website/record','1'),('group/ids/messageboard_add','e90538d6f98e338f439e6793a7892fd8'),('group/ids/messageboard_delete','d521adafbdb3a5a4ca38c5ad455ed498'),('group/ids/messageboard_edit','9f40e5f132cb602d243c4a411a576103'),('group/ids/work_request_add','790d42899ecba76449952684528a8470'),('group/ids/work_request_delete','197dbd29cba404678d0acc50bcea3d02'),('group/ids/work_request_edit','0767cb5c9bb57cfdb58c359a31bc32a8'),('group/ids/budget_approve','02b4dc9e41f654e4b6f84358ad3eb278'),('group/ids/insurance_add','92b61e4778fdc6ea398375b8aecc7160'),('group/ids/insurance_delete','d86c53c98c55886dd384c9bfcda38e81'),('group/ids/insurance_edit','43695a66df03926cd9fab7ab702940f0'),('group/ids/insurance_view','ed7bce923c51ef8fad9e6dfd29ecf189'),('group/ids/advertising_add','95e86ac88b31522527f275a8914511d5'),('group/ids/advertising_delete','c1bd968bbd21dae976128fa935c7cdad'),('group/ids/advertising_edit','7e614efb5e01fbb984d20723511c112f'),('budget/insurance/days','90'),('budget/insurance/minimum_policy_length','5'),('budget/insurance/reminders','1'),('budget/insurance/flags/replaced','1'),('article/minimum_keywords_length','0'),('article/minimum_summary_length','0'),('article/minimum_leadin_length','0'),('work_request/require_approval','1'),('log/flags/news','2147483648'),('advertising/minimum_description_length','0'),('advertising/minimum_url_length','5'),('budget/flags/due_20','16'),('work_request/flags/needs_approval','1'),('work_request/flags/more_information','4'),('work_request/flags/not_approved','64'),('violation/flags/more_information','4'),('violation/flags/not_approved','64'),('violation/flags/needs_approval','1'),('work_request/minimum_title_length','5'),('work_request/minimum_description_length','25'),('work_request/minimum_notes_length','0'),('hoam/latest','0.883'),('homeowner/minimum_comments_length','0'),('lot/minimum_block_length','0'),('lot/minimum_lot_length','0'),('lot/minimum_latitude_length','0'),('lot/minimum_longitude_length','0'),('lot/minimum_plat_length','0'),('attachment/flags/budget_invoice','16'),('log/flags/upgrade','17179869184');
/*!40000 ALTER TABLE `configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) DEFAULT NULL,
  `owner_id` varchar(32) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `firstname` varchar(32) DEFAULT NULL,
  `middlename` varchar(32) DEFAULT NULL,
  `lastname` varchar(32) DEFAULT NULL,
  `suffixname` varchar(5) DEFAULT NULL,
  `nickname` varchar(16) DEFAULT NULL,
  `spousename` varchar(32) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `anniversary` date DEFAULT NULL,
  `address1name` varchar(15) DEFAULT NULL,
  `address1a` varchar(64) DEFAULT NULL,
  `address1b` varchar(64) DEFAULT NULL,
  `address1c` varchar(64) DEFAULT NULL,
  `address1city` varchar(32) DEFAULT NULL,
  `address1state` varchar(32) DEFAULT NULL,
  `address1zipcode` varchar(9) DEFAULT NULL,
  `address2name` varchar(15) DEFAULT NULL,
  `address2a` varchar(64) DEFAULT NULL,
  `address2b` varchar(64) DEFAULT NULL,
  `address2c` varchar(64) DEFAULT NULL,
  `address2city` varchar(32) DEFAULT NULL,
  `address2state` varchar(32) DEFAULT NULL,
  `address2zipcode` varchar(9) DEFAULT NULL,
  `address3name` varchar(15) DEFAULT NULL,
  `address3a` varchar(64) DEFAULT NULL,
  `address3b` varchar(64) DEFAULT NULL,
  `address3c` varchar(64) DEFAULT NULL,
  `address3city` varchar(32) DEFAULT NULL,
  `address3state` varchar(32) DEFAULT NULL,
  `address3zipcode` varchar(9) DEFAULT NULL,
  `email1` varchar(128) DEFAULT NULL,
  `email1name` varchar(15) DEFAULT NULL,
  `email2` varchar(128) DEFAULT NULL,
  `email2name` varchar(15) DEFAULT NULL,
  `email3` varchar(128) DEFAULT NULL,
  `email3name` varchar(15) DEFAULT NULL,
  `email4` varchar(128) DEFAULT NULL,
  `email4name` varchar(15) DEFAULT NULL,
  `phone1name` varchar(15) DEFAULT NULL,
  `phone1` varchar(10) DEFAULT NULL,
  `phone2name` varchar(15) DEFAULT NULL,
  `phone2` varchar(10) DEFAULT NULL,
  `phone3name` varchar(15) DEFAULT NULL,
  `phone3` varchar(10) DEFAULT NULL,
  `phone4name` varchar(15) DEFAULT NULL,
  `phone4` varchar(10) DEFAULT NULL,
  `instantmsg1name` varchar(15) DEFAULT NULL,
  `instantmsg1` varchar(64) DEFAULT NULL,
  `instantmsg2name` varchar(15) DEFAULT NULL,
  `instantmsg2` varchar(64) DEFAULT NULL,
  `instantmsg3name` varchar(15) DEFAULT NULL,
  `instantmsg3` varchar(64) DEFAULT NULL,
  `instantmsg4name` varchar(15) DEFAULT NULL,
  `instantmsg4` varchar(64) DEFAULT NULL,
  `web_business` varchar(128) DEFAULT NULL,
  `web_calendar` varchar(128) DEFAULT NULL,
  `web_personal` varchar(128) DEFAULT NULL,
  `profession` varchar(64) DEFAULT NULL,
  `company` varchar(64) DEFAULT NULL,
  `department` varchar(64) DEFAULT NULL,
  `manager` varchar(64) DEFAULT NULL,
  `assistant` varchar(64) DEFAULT NULL,
  `jobtitle` varchar(64) DEFAULT NULL,
  `comments` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crime_reports`
--

DROP TABLE IF EXISTS `crime_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crime_reports` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `report_date` date DEFAULT NULL,
  `lot_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `category` varchar(32) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`,`lot_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crime_reports`
--

LOCK TABLES `crime_reports` WRITE;
/*!40000 ALTER TABLE `crime_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `crime_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_list`
--

DROP TABLE IF EXISTS `group_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_list` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(64) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_list`
--

LOCK TABLES `group_list` WRITE;
/*!40000 ALTER TABLE `group_list` DISABLE KEYS */;
INSERT INTO `group_list` VALUES ('93dc7abc06d1c91879cea7f30d6b8705','Everyone','Everyone, all users.','2005-04-13 09:45:27','2006-08-25 14:44:20','0afccadaefb0d4ae560edd8a68a5595a',0),('886c57ffc02b4c36a0e33822516d6854','Anonymous Users','Essentially the same as the \'Everyone\' group, but requires that users are NOT logged in.','2005-04-13 09:47:59','2005-04-13 09:47:59','0afccadaefb0d4ae560edd8a68a5595a',0),('c5dd2dd866b5885eea92c0b8902136dc','Registered Users','Members of this group have registered / created an user account on the system.','2005-04-13 09:49:02','2015-09-19 20:51:06','0afccadaefb0d4ae560edd8a68a5595a',0),('d46ef1ab6b8c9d8b8d682614b960b3b7','HOA Board Members','Members of the HOA Board of Directors','2005-04-13 11:45:42','2015-09-19 20:48:39','0afccadaefb0d4ae560edd8a68a5595a',0),('cbbbda9d1b7a4ee86e24a9195ab874e2','HOA Officers','Officers of the HOA','2005-04-13 11:46:22','2005-04-13 11:46:22','0afccadaefb0d4ae560edd8a68a5595a',0),('05b6f36360877bf30f789dc76043e61b','HOA All/Any Committee Members','Members of one or more HOA Committees','2005-04-13 11:47:18','2005-04-13 11:47:18','0afccadaefb0d4ae560edd8a68a5595a',0),('58c8ccf35d4125f7b2cf08ffd3dd8d31','HOA Social Committee Members','Members of the HOA Social Committee','2005-04-13 11:47:53','2005-04-13 11:47:53','0afccadaefb0d4ae560edd8a68a5595a',0),('001c41a2fe47e52026984daf9e1b8735','HOA Landscape Committee Members','Members of the HOA Landscape Committee','2005-04-13 15:34:27','2005-04-13 15:34:27',NULL,0),('8c599ddaf97090e63e8e60778d6ecb30','HOA Architecture Committee Members','Members of the HOA Architecture Committee','2005-04-13 15:34:49','2005-04-13 15:34:49',NULL,0),('7aa9a3db42211343c3267d4078f668ad','Homeowners','People or companies that own individual homes in the association.','2005-04-13 15:36:05','2015-09-19 20:49:31',NULL,0),('ec695008e4c07f6f4e98b06cfc9060e6','Residents','People living in the neighborhood, but they don\'t own the home (renting / leasing / etc).','2005-04-13 15:37:03','2005-04-13 15:37:03',NULL,0),('f0d6c78a36bcec4024e0cd69444cf4cd','System Administrators','Members of this group are have administrative rights / privileges to update and modify the entire web site and all configuration options. Access should be restricted to only those individuals truly needing it.','2005-04-13 15:38:49','2015-09-19 20:52:26',NULL,0),('457e60898dcc1282dd3bc7e0efb18e65','Articles - Edit','Members of this group are allowed to edit existing articles on the site.',NULL,'2015-09-19 20:46:39',NULL,0),('eaeeff75a9f1b49df92c0a20a84ed837','Messageboard - View','Members of this group can view messages posted in the messageboard.','2005-04-21 16:11:32','2015-09-19 20:50:10',NULL,0),('ac631ddcaf3e23dd186391537f31fde0','Messageboard - Post','Members of this group can post messages in the messageboard.','2005-04-22 15:53:51','2015-09-19 20:49:56',NULL,0),('6843d53b9f9c6ac5190b9dac0e89d30e','News - Edit','Members of this group are allowed to edit or create new news articles on the site.',NULL,'2015-09-19 20:50:35',NULL,0),('236cd151bbc3b5c623e2c634c2b2474c','Tasklist - Personal','Members of this group may have a personal tasklist.',NULL,'2015-09-19 20:51:33',NULL,0),('46904d8263f449c9a7060b4405c32cd8','Violations - Edit','Members of this group are allowed to edit existing deed restriction violations.',NULL,'2015-09-19 20:45:47',NULL,0),('f1c1124564e7485a111c55ae851d5f31','HOA Volunteer Coordinators','People that assist with volunteer assignment, tasks, and committee duties.','2005-06-17 10:48:13','2005-06-17 10:48:20',NULL,0),('8f5b8f6300e2e1922cc3c3b30404b0f3','Vote - Edit Questions / Options','Members of this group are allowed to edit voting booth questions and answer options.','2005-06-22 11:56:28','2015-09-19 20:53:00',NULL,0),('c62214faf64a01fb003deda32ad3ef70','Binary Attachments - View','Members of this group are allowed to view file attachments stored in the system.','2006-04-17 10:01:04','2015-09-19 20:47:56','0afccadaefb0d4ae560edd8a68a5595a',0),('5b7fd1e597071930d03b8b1c1d3b2063','Homeowners - Edit','Members of this group are permitted to edit existing homeowners.','2009-07-22 22:02:07','2009-07-22 22:02:07','0afccadaefb0d4ae560edd8a68a5595a',0),('d4a5e68f6036d9ca55f3e51fe76a87e7','Homeowners - Add','Members of this group are permitted to add new homeowners.','2009-07-22 22:02:30','2009-07-22 22:02:30','0afccadaefb0d4ae560edd8a68a5595a',0),('1a5aabf4820d77d2aa67b8f0e049b598','Homeowners - Delete','Members of this group are permitted to delete existing homeowners.','2009-07-22 22:03:03','2009-07-22 22:03:03','0afccadaefb0d4ae560edd8a68a5595a',0),('1c8483f11edb5273749b50aef71f802d','Budget - Add','Members of this group are permitted to add new items to the budget (payments, invoices, credits, etc.)','2009-07-24 23:12:02','2009-07-24 23:12:02','0afccadaefb0d4ae560edd8a68a5595a',0),('eccef449c739f857757260eaa9e7ec4c','Budget - Delete','Members of this group are permitted to delete existing budget items.','2009-07-24 23:12:21','2009-07-24 23:12:21','0afccadaefb0d4ae560edd8a68a5595a',0),('9ded86aae579a9d3fde8a6a07210e650','Budget - Edit','Members of this group are permitted to edit existing budget items.','2009-07-24 23:12:42','2009-07-24 23:12:42','0afccadaefb0d4ae560edd8a68a5595a',0),('206b88ed0d9eb5b3126d487177b39394','Budget - View','Members of this group are permitted to view budget entries but may not make any changes.','2009-07-24 23:13:07','2009-07-24 23:13:07','0afccadaefb0d4ae560edd8a68a5595a',0),('ccfcf56114e9e8a2b9becc6a33b419aa','Lot - Add','Members of this group are permitted to add new lots (property addresses) into the system.','2009-07-24 23:14:58','2009-07-24 23:14:58','0afccadaefb0d4ae560edd8a68a5595a',0),('0a2855edcb165cf58257fdedc2b26fea','Lot - Delete','Members of this group are permitted to delete lots (property addresses) from the system.','2009-07-24 23:15:15','2009-07-24 23:15:15','0afccadaefb0d4ae560edd8a68a5595a',0),('a2650edfc1fa36d9956a8362a6b8ca74','Lot - Edit','Members of this group are permitted to edit existing lots (property addresses) in the system.','2009-07-24 23:15:34','2009-07-24 23:15:34','0afccadaefb0d4ae560edd8a68a5595a',0),('55243529ae58f57d672527ce2b666a09','News - Add','Members of this group are permitted to add new news articles to the website.','2009-07-24 23:16:19','2009-07-24 23:16:19','0afccadaefb0d4ae560edd8a68a5595a',0),('5c6da80583761452ff618410c5496ff1','News - Delete','Members of this group are permitted to delete news articles from the website.','2009-07-24 23:16:38','2009-07-24 23:16:38','0afccadaefb0d4ae560edd8a68a5595a',0),('c1bbea30bf2b2c07b8857d50db8b6ad6','Violations - Add','Members of this group are permitted to add create/issue deed restriction violation notices.','2009-07-24 23:18:12','2009-07-24 23:18:12','0afccadaefb0d4ae560edd8a68a5595a',0),('db20bd43d429199749cbcbad9857993c','Violations - Delete','Members of this group are permitted to delete existing deed restriction violations.','2009-07-24 23:18:32','2009-07-24 23:18:32','0afccadaefb0d4ae560edd8a68a5595a',0),('512adb1af78f909268ca341d237ee1d8','Binary Attachments - Add','Members of this group are permitted to add/import new attachments to the website.','2009-07-24 23:20:26','2009-07-24 23:20:26','0afccadaefb0d4ae560edd8a68a5595a',0),('87f240de2b702ee8e1a5c4f544ef6c37','Binary Attachments - Delete','Members of this group are permitted to delete existing attachments from the website.','2009-07-24 23:20:46','2009-07-24 23:20:46','0afccadaefb0d4ae560edd8a68a5595a',0),('509073d184bb736fd23441f1d2e49ff8','Group - Add','Members of this group are permitted to add new user groups to the system.','2009-07-24 23:21:58','2009-07-24 23:21:58','0afccadaefb0d4ae560edd8a68a5595a',0),('bece93d554ef766ac5fbf1c3c1d1109a','Group - Delete','Members of this group are permitted to delete existing user groups from the system.','2009-07-24 23:22:18','2009-07-24 23:22:18','0afccadaefb0d4ae560edd8a68a5595a',0),('8c25d092851c113784352986e713c469','Group - Edit','Members of this group are permitted to modify existing user groups in the system.','2009-07-24 23:22:36','2009-07-24 23:22:36','0afccadaefb0d4ae560edd8a68a5595a',0),('cdac2f9def83d1f5dbac40236bb3f76e','User - Add','Members of this group are permitted to add new user accounts to the system.','2009-07-24 23:23:51','2009-07-24 23:23:51','0afccadaefb0d4ae560edd8a68a5595a',0),('a51ab63841aecf4d6aa5479d2a1342eb','User - Delete','Members of this group are permitted to delete user accounts from the system.','2009-07-24 23:24:10','2009-07-24 23:24:10','0afccadaefb0d4ae560edd8a68a5595a',0),('1f4331e3a0d8bed99ee20ca4be666b6f','User - Edit','Members of this group are permitted to modify existing user accounts in the system.','2009-07-24 23:24:29','2009-07-24 23:24:29','0afccadaefb0d4ae560edd8a68a5595a',0),('b3accac18f2ae3922b2c0f0f03ad002b','Articles - Add','Members of this group are allowed to create new articles on the site.','2015-09-10 21:11:58','2015-09-19 20:46:22','684ab9dafd44c57dd85a08dd18813024',0),('efde2d9c994c043b8a8713e71a30b8f4','Articles - Delete','Members of this group are allowed to delete articles on the site.','2015-09-10 21:12:28','2015-09-19 20:46:12','684ab9dafd44c57dd85a08dd18813024',0),('c2ecc7a9cf60aab22ecb7425fdbbe08e','Binary Attachments - Edit','Members of this group are allowed to edit existing file attachments.','2015-09-10 21:13:24','2015-09-19 20:47:29','684ab9dafd44c57dd85a08dd18813024',0),('02b4dc9e41f654e4b6f84358ad3eb278','Budget - Approve','Members of this group are permitted to approve pending expenses.','2015-09-10 21:14:24','2015-09-10 21:14:24','684ab9dafd44c57dd85a08dd18813024',0),('92b61e4778fdc6ea398375b8aecc7160','Insurance - Add','Members of this group are permitted to add new insurance policies.','2015-09-10 21:15:03','2015-09-10 21:15:03','684ab9dafd44c57dd85a08dd18813024',0),('d86c53c98c55886dd384c9bfcda38e81','Insurance - Delete','Members of this group are permitted to delete existing insurance policies.','2015-09-10 21:15:19','2015-09-10 21:15:19','684ab9dafd44c57dd85a08dd18813024',0),('43695a66df03926cd9fab7ab702940f0','Insurance - Edit','Members of this group are permitted to modify existing insurance policies.','2015-09-10 21:15:36','2015-09-10 21:15:36','684ab9dafd44c57dd85a08dd18813024',0),('ed7bce923c51ef8fad9e6dfd29ecf189','Insurance - View','Members of this group are permitted to view existing insurance policies.','2015-09-10 21:15:54','2015-09-10 21:15:54','684ab9dafd44c57dd85a08dd18813024',0),('e90538d6f98e338f439e6793a7892fd8','Messageboard - Add','Members of this group are permitted to add new messageboards.','2015-09-10 21:16:32','2015-09-10 21:16:32','684ab9dafd44c57dd85a08dd18813024',0),('d521adafbdb3a5a4ca38c5ad455ed498','Messageboard - Delete','Members of this group are permitted to delete existing messageboards.','2015-09-10 21:16:47','2015-09-10 21:16:47','684ab9dafd44c57dd85a08dd18813024',0),('9f40e5f132cb602d243c4a411a576103','Messageboard - Edit','Members of this group are permitted to modify existing messageboards and messages.','2015-09-10 21:17:14','2015-09-10 21:17:14','684ab9dafd44c57dd85a08dd18813024',0),('d4fad35282693aae1edc23173ed797be','Violations - Approve','Members of this group are permitted to approve submitted deed restriction violations.','2015-09-10 21:18:08','2015-09-10 21:18:08','684ab9dafd44c57dd85a08dd18813024',0),('790d42899ecba76449952684528a8470','Work Request - Add','Members of this group are permitted to add new work requests.','2015-09-10 21:18:38','2015-09-10 21:18:38','684ab9dafd44c57dd85a08dd18813024',0),('197dbd29cba404678d0acc50bcea3d02','Work Request - Delete','Members of this group are permitted to delete existing work requests.','2015-09-10 21:18:55','2015-09-10 21:18:55','684ab9dafd44c57dd85a08dd18813024',0),('0767cb5c9bb57cfdb58c359a31bc32a8','Work Request - Edit','Members of this group are permitted to edit existing work requests.','2015-09-10 21:19:11','2015-09-10 21:19:11','684ab9dafd44c57dd85a08dd18813024',0),('95e86ac88b31522527f275a8914511d5','Advertising - Add','Users that may add new advertisements to the website.','2020-04-10 15:33:53','2020-04-10 15:33:53','684ab9dafd44c57dd85a08dd18813024',0),('c1bd968bbd21dae976128fa935c7cdad','Advertising - Delete','Users that may delete existing advertisements from the system.','2020-04-10 15:34:13','2020-04-10 15:34:13','684ab9dafd44c57dd85a08dd18813024',0),('7e614efb5e01fbb984d20723511c112f','Advertising - Edit','Users that may edit existing advertisements in the system.','2020-04-10 15:34:32','2020-04-10 15:34:32','684ab9dafd44c57dd85a08dd18813024',0),('0e77eeab91f4df2a270b036b600930bb','Work Request - Approve','Members of this group are able to approve new work requests.','2020-04-14 20:14:13','2020-04-14 20:14:13','684ab9dafd44c57dd85a08dd18813024',0);
/*!40000 ALTER TABLE `group_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_members` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `owner_id` varchar(32) DEFAULT NULL,
  `member_id` varchar(32) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `flags` (`flags`),
  KEY `group_id` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_members`
--

LOCK TABLES `group_members` WRITE;
/*!40000 ALTER TABLE `group_members` DISABLE KEYS */;
INSERT INTO `group_members` VALUES ('f64791edc4dd68fdac7375a5e08321d7','93dc7abc06d1c91879cea7f30d6b8705','886c57ffc02b4c36a0e33822516d6854','2010-04-05 13:40:17',NULL,'',2),('4d9e1c300fdb5142660b19ed5f87b1ad','cbbbda9d1b7a4ee86e24a9195ab874e2','d46ef1ab6b8c9d8b8d682614b960b3b7',NULL,NULL,NULL,2),('98f7164eb0b7d9a2d781544c5cbcf22f','05b6f36360877bf30f789dc76043e61b','f1c1124564e7485a111c55ae851d5f31','2020-04-17 23:17:24',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('b89ff3f3a7d7c6ff52e24d5ec8c30ab6','f0d6c78a36bcec4024e0cd69444cf4cd','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:24:26',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('27161f363eb251025fbd691250cdccbe','d4a5e68f6036d9ca55f3e51fe76a87e7','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:18:10',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('9c50b7c46abc0b3124edf7d5e9fcaae3','58c8ccf35d4125f7b2cf08ffd3dd8d31','cbbbda9d1b7a4ee86e24a9195ab874e2',NULL,NULL,NULL,2),('72feae52e089be7c60bdfbed2952e774','001c41a2fe47e52026984daf9e1b8735','cbbbda9d1b7a4ee86e24a9195ab874e2',NULL,NULL,NULL,2),('23a78e2f5fcb2dbee7a54efcab45f28d','8c599ddaf97090e63e8e60778d6ecb30','cbbbda9d1b7a4ee86e24a9195ab874e2',NULL,NULL,NULL,2),('4f5ac3bdd6b61df674d7330e1615a1bd','05b6f36360877bf30f789dc76043e61b','58c8ccf35d4125f7b2cf08ffd3dd8d31','2020-04-17 23:17:24',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('ce210011d1f8941fab59a1a4fa6a8b78','457e60898dcc1282dd3bc7e0efb18e65','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:10:58',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('5e79d37f568d06f1bf98e8f3f34aaf8a','55243529ae58f57d672527ce2b666a09','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:22:46',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('ef28bb4052633d600f8d8f7a0641a4dd','6843d53b9f9c6ac5190b9dac0e89d30e','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:23:30',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('491e3815594df03e9460c6c823e1a564','236cd151bbc3b5c623e2c634c2b2474c','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:24:44',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('bc62a3d211d339a42550afe3b067bf8b','46904d8263f449c9a7060b4405c32cd8','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:26:59',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('a580ac49cb925edeb8dca99498954a39','05b6f36360877bf30f789dc76043e61b','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:17:24',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('984c5f771991471088079922da9fb973','f1c1124564e7485a111c55ae851d5f31','cbbbda9d1b7a4ee86e24a9195ab874e2','2005-06-21 21:26:54',NULL,NULL,2),('ec0567b48c3eb7492ff124fe746fa3ba','8f5b8f6300e2e1922cc3c3b30404b0f3','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:27:19',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('bacdd1d66729f13506e1eab982ddfafd','886c57ffc02b4c36a0e33822516d6854','c62214faf64a01fb003deda32ad3ef70','2009-10-31 13:09:32',NULL,'',2),('8cd59397ef715fa78e51b2e37627e30d','c62214faf64a01fb003deda32ad3ef70','f0d6c78a36bcec4024e0cd69444cf4cd','2010-11-04 16:01:54',NULL,'',2),('2bd7a9e20b74ab08094635ba675ffa6c','93dc7abc06d1c91879cea7f30d6b8705','c5dd2dd866b5885eea92c0b8902136dc','2010-04-05 13:40:17',NULL,'',2),('34b95c967852a437feac4d5bd29fcd15','c62214faf64a01fb003deda32ad3ef70','93dc7abc06d1c91879cea7f30d6b8705','2010-11-04 16:01:54',NULL,'',2),('4079e69ab6e61f1e0143cd639065aab5','eaeeff75a9f1b49df92c0a20a84ed837','93dc7abc06d1c91879cea7f30d6b8705','2010-11-10 15:54:06',NULL,'0afccadaefb0d4ae560edd8a68a5595a',2),('f788e8d2b45830201ba525b1e9ff562d','c5dd2dd866b5885eea92c0b8902136dc','684ab9dafd44c57dd85a08dd18813024','2020-04-20 20:00:56',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('71bdbd2eefc7c4612fcfe3e4ce66e820','f0d6c78a36bcec4024e0cd69444cf4cd','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:24:26',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('4e631423ba072e6817f262761dcabbe4','7aa9a3db42211343c3267d4078f668ad','4a3eb34beb86a161b500c9111b9a6347','2013-01-16 14:29:54',NULL,'684ab9dafd44c57dd85a08dd18813024',1),('e42b491ff5f345b9c50bed0bb344bf07','c5dd2dd866b5885eea92c0b8902136dc','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-20 20:00:56',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('315fc3fce2cbab13524dbec8b11d010d','206b88ed0d9eb5b3126d487177b39394','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-23 13:23:59',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('caf5e51da3f781cde72ed3d0ba2086ef','0e77eeab91f4df2a270b036b600930bb','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-14 20:14:56',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('8956e6179ccbca030707984062890cbe','0e77eeab91f4df2a270b036b600930bb','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-14 20:14:56',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('38d1f0003ebfc1d1146985cb5a423fcd','95e86ac88b31522527f275a8914511d5','684ab9dafd44c57dd85a08dd18813024','2020-04-23 17:28:34',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('59cb19888b43aa632d3048fde11950c2','95e86ac88b31522527f275a8914511d5','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-23 17:28:34',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('aaa374f0d67aebf6c5b51380b5cb05c9','95e86ac88b31522527f275a8914511d5','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-23 17:28:34',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('643688d5d9960944a776018553640b6d','c1bd968bbd21dae976128fa935c7cdad','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:12:21',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('6f4e7ad1e6b6bb279e1d4be058929639','c1bd968bbd21dae976128fa935c7cdad','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:12:21',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('055c0a3d0df038ac52641c2ef7f5fb4b','c1bd968bbd21dae976128fa935c7cdad','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:12:21',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('117a6e70c2b9033a2142db8b03cfa313','c1bd968bbd21dae976128fa935c7cdad','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:12:21',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('03001a919e8d49bc3f5345af4fff7343','7e614efb5e01fbb984d20723511c112f','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:12:29',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('1ea740acbf2a4217d5dbb552a38d5ba0','7e614efb5e01fbb984d20723511c112f','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:12:29',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('e584bcc1ac15912b71c636865fed826e','7e614efb5e01fbb984d20723511c112f','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:12:29',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('f2d07b803f0b17075eaa43628b1c473d','7e614efb5e01fbb984d20723511c112f','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:12:29',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('64d8961391665f69e24ac0ef25c9a798','b3accac18f2ae3922b2c0f0f03ad002b','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:12:40',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('4861418fd2686e3b581557806ab2b46d','b3accac18f2ae3922b2c0f0f03ad002b','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:12:40',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('46ff6a25e4b7e49c8663ef4d5c8b9ab1','b3accac18f2ae3922b2c0f0f03ad002b','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:12:40',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('939d79f0e6e890d42e170fa415052cac','b3accac18f2ae3922b2c0f0f03ad002b','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:12:40',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('dc0084206e830ea01e53b1d969c9d260','efde2d9c994c043b8a8713e71a30b8f4','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:12:49',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('dc268783bd233f535a4355bcd3820560','efde2d9c994c043b8a8713e71a30b8f4','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:12:49',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('daefca48fefc2808691f9f57040306bb','efde2d9c994c043b8a8713e71a30b8f4','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:12:49',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('0aca85c7c5178007d44f48aa592751ce','efde2d9c994c043b8a8713e71a30b8f4','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:12:49',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('2ebec498c3d603f72f58c65e231a8003','457e60898dcc1282dd3bc7e0efb18e65','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:10:58',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('71dba92d2e372750fd4a19901e1c0a9e','457e60898dcc1282dd3bc7e0efb18e65','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:10:58',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('98852a61c0d8ea0174174b0b7006d342','457e60898dcc1282dd3bc7e0efb18e65','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:10:58',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('ea74e2b490190daedfaa14e71abb41b9','457e60898dcc1282dd3bc7e0efb18e65','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:10:58',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('7d7880aaecb0cfbbbfc145a49165bb40','512adb1af78f909268ca341d237ee1d8','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:13:18',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('e833e4c2ee8b13e1df8895743b6935ce','512adb1af78f909268ca341d237ee1d8','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:13:18',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('29b1cd44acfab91fde0ab2a3074a138e','512adb1af78f909268ca341d237ee1d8','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:13:18',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('1f51cd081a173b91e4800255c6a875f8','512adb1af78f909268ca341d237ee1d8','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:13:18',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('5ec5d914cf8e8ba67b31aef26c4016a2','87f240de2b702ee8e1a5c4f544ef6c37','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:13:25',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('974554c5d7972a646d34f3ad0c063b52','87f240de2b702ee8e1a5c4f544ef6c37','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:13:25',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('3bef530c154de6dc7a41144413fc2442','87f240de2b702ee8e1a5c4f544ef6c37','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:13:25',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('f8bb2a554d86ab8b15b45af05a16774a','87f240de2b702ee8e1a5c4f544ef6c37','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:13:25',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('b1f25d50d44092181e63d5b71f394cfd','c2ecc7a9cf60aab22ecb7425fdbbe08e','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:13:34',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('99957d6a4f2cd15996a1b82db1f649c6','c2ecc7a9cf60aab22ecb7425fdbbe08e','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:13:34',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('bbe6b3283cfc0f2f86f563b3dff317c7','c2ecc7a9cf60aab22ecb7425fdbbe08e','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:13:34',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('525fd21bec927ae57a0283509ef586f2','c2ecc7a9cf60aab22ecb7425fdbbe08e','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:13:34',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('4f92ef55334df43fe3ef476099503d5e','95e86ac88b31522527f275a8914511d5','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-23 17:28:34',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('378f9c5f690b2347ff5f321a73c3f642','c1bd968bbd21dae976128fa935c7cdad','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:12:21',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('cf641b1ccc72b1118c7d4aca3ea98804','7e614efb5e01fbb984d20723511c112f','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:12:29',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('78a9801b9c6bdcba353248c132b41546','b3accac18f2ae3922b2c0f0f03ad002b','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:12:40',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('1fccd65525a4ebe048f3abd177a3eff0','efde2d9c994c043b8a8713e71a30b8f4','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:12:49',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('359f2ef1442dd62b7a8324311dbc8763','512adb1af78f909268ca341d237ee1d8','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:13:18',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('c7062b8053ef82b4b0f7f646ac268ca1','87f240de2b702ee8e1a5c4f544ef6c37','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:13:25',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('bb08f656a9cc240858b46a2cefa156c0','c2ecc7a9cf60aab22ecb7425fdbbe08e','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:13:34',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('d2e0f5fa1cb3abd39152f00d0d8df87a','1c8483f11edb5273749b50aef71f802d','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:14:11',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('99b35a9e62217d948ae88f48f3048d32','1c8483f11edb5273749b50aef71f802d','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:14:11',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('40a4c0720fad58167e9712b55b1cb454','1c8483f11edb5273749b50aef71f802d','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:14:11',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('8ddfb17f7f8acaf91a7a2decd066b87a','1c8483f11edb5273749b50aef71f802d','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:14:11',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('e5b199495221220b7bf7d38eeee79118','1c8483f11edb5273749b50aef71f802d','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:14:11',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('d2ab23ca610e56335f2af717aa0fb5e1','02b4dc9e41f654e4b6f84358ad3eb278','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:14:35',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('3adac79a9e90908e4932c000cfdc7aae','02b4dc9e41f654e4b6f84358ad3eb278','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:14:35',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('fa8939b655721ba7c4c8d3cbdf1b087d','02b4dc9e41f654e4b6f84358ad3eb278','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:14:35',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('78b5933569298a95243e513c70237cf1','02b4dc9e41f654e4b6f84358ad3eb278','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:14:35',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('6e421baa8f955e841db69f493fb98e83','02b4dc9e41f654e4b6f84358ad3eb278','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:14:35',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('93cc4a323233f7f1c2ba6e995bd95a05','eccef449c739f857757260eaa9e7ec4c','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:14:53',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('83600ee27f3c9ab9fa909e742da2a51d','eccef449c739f857757260eaa9e7ec4c','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:14:53',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('862dbc636b016120eb1ee07ca4f46b7d','eccef449c739f857757260eaa9e7ec4c','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:14:53',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('e159df773eadde23c63ca688717b4694','eccef449c739f857757260eaa9e7ec4c','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:14:53',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('0b3bb58dce9dafb38094c76c4954bdcb','eccef449c739f857757260eaa9e7ec4c','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:14:53',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('65d619a9b52560d3746a04ec3f2aa461','9ded86aae579a9d3fde8a6a07210e650','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:15:12',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('974121e634d69470538ea82062c56ab0','9ded86aae579a9d3fde8a6a07210e650','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:15:12',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('467b49b69e99236a3517b5027c11a5e0','9ded86aae579a9d3fde8a6a07210e650','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:15:12',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('aa1820c97cdb866da76e25123034859c','9ded86aae579a9d3fde8a6a07210e650','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:15:12',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('5cfb7b4cf992dbeff9f3bf0f1199484c','9ded86aae579a9d3fde8a6a07210e650','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:15:12',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('ab47ca4d0ea6dd6550eb0e39f3282818','206b88ed0d9eb5b3126d487177b39394','684ab9dafd44c57dd85a08dd18813024','2020-04-23 13:23:59',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('d163b4a9cbfaa07af7f75c593cebfe93','206b88ed0d9eb5b3126d487177b39394','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-23 13:23:59',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('9207f211a9e7c285da17d5e580fd7b7b','206b88ed0d9eb5b3126d487177b39394','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-23 13:23:59',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('e8305bd051004605b2e65f472d00e339','206b88ed0d9eb5b3126d487177b39394','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-23 13:23:59',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('5e3d7e07e5cb7ac4f3eaa6fe85262754','509073d184bb736fd23441f1d2e49ff8','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:15:59',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('b05c2750eda71dd0575da1bdd2e4a328','509073d184bb736fd23441f1d2e49ff8','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:15:59',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('7764f9810773fa766f7c156f912558fe','509073d184bb736fd23441f1d2e49ff8','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:15:59',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('1211863e6038dce8124b81ec63541a21','509073d184bb736fd23441f1d2e49ff8','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:15:59',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('60d4f871aa8208f415125436c53b3cfc','509073d184bb736fd23441f1d2e49ff8','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:15:59',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('2dcf878bd6433b6b8fe40e7997936d57','bece93d554ef766ac5fbf1c3c1d1109a','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:16:15',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('7db1068c264f67ded9bc75ae9932fc56','bece93d554ef766ac5fbf1c3c1d1109a','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:16:15',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('e0a5cf7f5ee593973808f963e9c3a9f8','bece93d554ef766ac5fbf1c3c1d1109a','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:16:15',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('fcc6e10c4b36f42e90f6b6bcbeb2636e','bece93d554ef766ac5fbf1c3c1d1109a','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:16:15',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('6052e5cc1fb9c062d80b108f03925376','bece93d554ef766ac5fbf1c3c1d1109a','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:16:15',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('a867aabf05ed74150c047fb66d954d48','8c25d092851c113784352986e713c469','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:16:33',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('262a17bc77d6bfe88bc04113c88ad25b','8c25d092851c113784352986e713c469','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:16:33',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('7a6abf60a6c814cae187e6c5c3b27bcf','8c25d092851c113784352986e713c469','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:16:33',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('b11181a87ffc21a48c0f86e1debaec30','8c25d092851c113784352986e713c469','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:16:33',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('6ff908bafeab4faa08e764736f04c8eb','8c25d092851c113784352986e713c469','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:16:33',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('bb628025ba1f7c564257aecfcf478190','05b6f36360877bf30f789dc76043e61b','001c41a2fe47e52026984daf9e1b8735','2020-04-17 23:17:24',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('04f58f5384d940d3eb0391cbc8d74201','05b6f36360877bf30f789dc76043e61b','8c599ddaf97090e63e8e60778d6ecb30','2020-04-17 23:17:24',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('b180f8f9e79e8249fb485e82d857df84','d4a5e68f6036d9ca55f3e51fe76a87e7','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:18:10',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('ab8fd5c1756773744935920e4a2cc710','d4a5e68f6036d9ca55f3e51fe76a87e7','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:18:10',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('39a5e434a415ea35b5ef0fe10fd6d6e9','d4a5e68f6036d9ca55f3e51fe76a87e7','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:18:10',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('7ca73bbb54eedc1a43a74a24f9b53913','d4a5e68f6036d9ca55f3e51fe76a87e7','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:18:10',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('b5b03c8517ce3a46331c06ffb9eb9231','1a5aabf4820d77d2aa67b8f0e049b598','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:18:28',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('d0b589bd354cd4ebfcf47b65e7ffe5d6','1a5aabf4820d77d2aa67b8f0e049b598','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:18:28',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('a5ee9adcf783a770c3e513c7dfd2e428','1a5aabf4820d77d2aa67b8f0e049b598','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:18:28',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('4e12abff9d41be4dfbcde278949db766','1a5aabf4820d77d2aa67b8f0e049b598','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:18:28',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('b788733e1bdac5492b4169083c1e5c66','1a5aabf4820d77d2aa67b8f0e049b598','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:18:28',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('58e1869fb33a76f7bf27a79cb3825bcd','5b7fd1e597071930d03b8b1c1d3b2063','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:18:50',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('f1da3579ee1e7527f6983d9099599611','5b7fd1e597071930d03b8b1c1d3b2063','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:18:50',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('16151578bd0519a85ff7bf94704d45e4','5b7fd1e597071930d03b8b1c1d3b2063','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:18:50',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('e9e15dd8e1a38d0d195379a99d3cd906','5b7fd1e597071930d03b8b1c1d3b2063','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:18:50',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('af03efe0505f0eb0b6bff86c471c24f8','92b61e4778fdc6ea398375b8aecc7160','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:19:08',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('4d85d852eb43c18956ced05af005e8b3','92b61e4778fdc6ea398375b8aecc7160','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:19:08',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('8dba8657bec8ecd17fb63252ddc265c2','92b61e4778fdc6ea398375b8aecc7160','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:19:08',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('738b676e53ecaeb4117f302c48c1c16c','92b61e4778fdc6ea398375b8aecc7160','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:19:08',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('9961a7e308c8b8f8bcb9feaa1dc38045','92b61e4778fdc6ea398375b8aecc7160','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:19:08',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('1e7063f98a849f9e0e820b9160efa744','d86c53c98c55886dd384c9bfcda38e81','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:19:28',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('85d54c8f89d295c877548ae42efdd450','d86c53c98c55886dd384c9bfcda38e81','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:19:28',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('7123af573c4ba26c75e8b95986bc9749','d86c53c98c55886dd384c9bfcda38e81','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:19:28',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('901e334c5528eda9c407218883016d0d','d86c53c98c55886dd384c9bfcda38e81','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:19:28',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('aa544d6fb9e998b40df4c2ac6b30c114','d86c53c98c55886dd384c9bfcda38e81','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:19:28',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('85ea4e6e8304742ced0b61cd6c18c452','43695a66df03926cd9fab7ab702940f0','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:19:45',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('8095a945dfd7b4d85bfc84689e21dd36','43695a66df03926cd9fab7ab702940f0','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:19:45',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('f1ebf9b79064b623f16e1029892dd96d','43695a66df03926cd9fab7ab702940f0','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:19:45',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('1534d06d657553cfdd6c6acb7f44b417','43695a66df03926cd9fab7ab702940f0','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:19:45',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('1b2fab86d1dcdd886f60b2b1945f7243','43695a66df03926cd9fab7ab702940f0','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:19:45',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('5e90197d058654b33f222437c57dca04','ed7bce923c51ef8fad9e6dfd29ecf189','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:20:06',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('2e6c63374aabe1b0e0738596529bdbad','ed7bce923c51ef8fad9e6dfd29ecf189','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:20:06',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('aed8f4347f6117569cfcd565e5bc0365','ed7bce923c51ef8fad9e6dfd29ecf189','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:20:06',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('2f6f541c27cf308474a4c60fa7c97d25','ed7bce923c51ef8fad9e6dfd29ecf189','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:20:06',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('389cd56ef245fad78602896acf5b5d71','ed7bce923c51ef8fad9e6dfd29ecf189','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:20:06',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('efc17c5045561578dec90b48c1fb2d6b','ccfcf56114e9e8a2b9becc6a33b419aa','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:20:22',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('5e5e4285006c7e0cf7ed30ea72c27c6d','ccfcf56114e9e8a2b9becc6a33b419aa','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:20:22',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('808d2f611cb67b47c9fdc6b56093d074','ccfcf56114e9e8a2b9becc6a33b419aa','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:20:22',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('a23b4a82a0fd6a5f4afef3e821f0b1f2','ccfcf56114e9e8a2b9becc6a33b419aa','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:20:22',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('8a0989b98f961d5ada826d8f27b89a7c','ccfcf56114e9e8a2b9becc6a33b419aa','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:20:22',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('f1aa4d7c0e754a28fc41d4dddd453e4e','0a2855edcb165cf58257fdedc2b26fea','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:20:37',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('d2855c1c396e438b670f5de6952a8c2c','0a2855edcb165cf58257fdedc2b26fea','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:20:37',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('a0e7ccfaf56f3793a204d5b556287f54','0a2855edcb165cf58257fdedc2b26fea','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:20:37',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('a630b71ea0119f5f36e7ad4f091cb0e2','0a2855edcb165cf58257fdedc2b26fea','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:20:37',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('7d2e3e05a097a526828effeca2f192ba','0a2855edcb165cf58257fdedc2b26fea','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:20:37',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('9c58fb39c88727b9913040ad6354b8e4','a2650edfc1fa36d9956a8362a6b8ca74','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:20:52',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('3141d1653878ed08e67e54ebeb890a7a','a2650edfc1fa36d9956a8362a6b8ca74','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:20:52',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('351f3a3ae57bea8c329cd0dda3ee1231','a2650edfc1fa36d9956a8362a6b8ca74','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:20:52',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('34bba5a4f66cd258d3d0b4476294848d','a2650edfc1fa36d9956a8362a6b8ca74','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:20:52',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('2e3f73af84f961599cf94f2f967786b8','a2650edfc1fa36d9956a8362a6b8ca74','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:20:52',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('2d782ed1c34b6fcc74ac4bfde49e22fa','e90538d6f98e338f439e6793a7892fd8','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:21:09',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('78ea01b518bacdc31fd9b5f3af389733','e90538d6f98e338f439e6793a7892fd8','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:21:09',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('8027f719ce63a6905223c3a8956bed78','e90538d6f98e338f439e6793a7892fd8','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:21:09',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('693216dc3660a080efddd1322eca55c4','e90538d6f98e338f439e6793a7892fd8','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:21:09',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('540f2a346afea3611c4e2833a537d55b','e90538d6f98e338f439e6793a7892fd8','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:21:09',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('03f5b080c2d216a392b745e1abc661e8','d521adafbdb3a5a4ca38c5ad455ed498','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:21:26',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('2609b08ea79cb3c19860d34a8c194351','d521adafbdb3a5a4ca38c5ad455ed498','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:21:26',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('606ee10e5697c043a2a43f4790bc19cc','d521adafbdb3a5a4ca38c5ad455ed498','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:21:26',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('503b606e11802c9c8233c986bcc7fa0b','d521adafbdb3a5a4ca38c5ad455ed498','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:21:26',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('8b3618ff77f10583a0026f588458714f','d521adafbdb3a5a4ca38c5ad455ed498','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:21:26',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('cf0eef93ab86821e8f3bf1d40ea4b401','9f40e5f132cb602d243c4a411a576103','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:21:45',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('692f5c6432acd8585a53a93798b76f0a','9f40e5f132cb602d243c4a411a576103','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:21:45',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('512f6c2a4f1599f4be89dba0a7a311cd','9f40e5f132cb602d243c4a411a576103','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:21:45',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('75103537440471431c9cd257532ead43','9f40e5f132cb602d243c4a411a576103','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:21:45',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('70c2692efbca71347763c981b7c6634b','9f40e5f132cb602d243c4a411a576103','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:21:45',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('5f0abbfe719208dcfd7d575eff83bbec','55243529ae58f57d672527ce2b666a09','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:22:46',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('3f4450fe7355454aa535c2aa57dca6f7','55243529ae58f57d672527ce2b666a09','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:22:46',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('1fc201d2ff19b20ba9b3a62f55c7dc78','55243529ae58f57d672527ce2b666a09','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:22:46',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('b096dfe994b1003a470b20ea4cf14f4d','55243529ae58f57d672527ce2b666a09','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:22:46',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('97e2124c0fe091b615df63aec5fe88ed','ac631ddcaf3e23dd186391537f31fde0','c5dd2dd866b5885eea92c0b8902136dc','2020-04-17 23:22:20',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('4498dcdb465f65a9167902e69ef00153','5c6da80583761452ff618410c5496ff1','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:23:02',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('7fd41fa9361f06edb856f78f37856e1b','5c6da80583761452ff618410c5496ff1','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:23:02',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('b314995ef9ba13e12f023b7343038f18','5c6da80583761452ff618410c5496ff1','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:23:02',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('f5e2f4ba94654037dbe527d9c62f94d8','5c6da80583761452ff618410c5496ff1','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:23:02',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('664b5254ff3dd4e2d92fb1e836050d01','5c6da80583761452ff618410c5496ff1','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:23:02',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('46f4021bb336442447bd5addb747d225','6843d53b9f9c6ac5190b9dac0e89d30e','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:23:30',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('8b886d77f4d52cd137a4ef762e186ea5','6843d53b9f9c6ac5190b9dac0e89d30e','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:23:30',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('6c8caf8ff4bc279f182c6125ea4415c0','6843d53b9f9c6ac5190b9dac0e89d30e','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:23:30',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('57bd9449009875348d5c313012d2ae45','6843d53b9f9c6ac5190b9dac0e89d30e','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:23:30',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('1b3bc4e1093e45fdeb008e31ff15309d','236cd151bbc3b5c623e2c634c2b2474c','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:24:44',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('af1216aa5abad51c5add5f509438a753','236cd151bbc3b5c623e2c634c2b2474c','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:24:44',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('3c36f807e3f285baa42b06f50c4a7209','cdac2f9def83d1f5dbac40236bb3f76e','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:25:08',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('41f3644706671d58768671609ffbb26a','cdac2f9def83d1f5dbac40236bb3f76e','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:25:08',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('54a7ab0d4414ea972a1f779cec2c35e4','cdac2f9def83d1f5dbac40236bb3f76e','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:25:08',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('5d029d768cd83bfcea10445bac7b5b7d','cdac2f9def83d1f5dbac40236bb3f76e','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:25:08',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('c4b39d361906d4c3b6198311c1a81d1a','cdac2f9def83d1f5dbac40236bb3f76e','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:25:08',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('01e5bac5549d4ec7713d227547022a57','a51ab63841aecf4d6aa5479d2a1342eb','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:25:27',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('b7037b902b5f1494296ef35c415bed36','a51ab63841aecf4d6aa5479d2a1342eb','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:25:27',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('fb7186986b21e5f495d965ac80eede7e','a51ab63841aecf4d6aa5479d2a1342eb','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:25:27',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('4ea6020be5305847d2890cf6e00229f8','a51ab63841aecf4d6aa5479d2a1342eb','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:25:27',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('fa30eeb61eb981d1efb0cc46055b709d','a51ab63841aecf4d6aa5479d2a1342eb','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:25:27',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('c5ba5aed479085581128f4045858075f','1f4331e3a0d8bed99ee20ca4be666b6f','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:25:42',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('82918c190cbb6c0a1732a60638158441','1f4331e3a0d8bed99ee20ca4be666b6f','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:25:42',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('87a28e9f1cd4e451ead360b80a9d0ea6','1f4331e3a0d8bed99ee20ca4be666b6f','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:25:42',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('9a1e36350925085cc26515e84fa957c2','1f4331e3a0d8bed99ee20ca4be666b6f','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:25:42',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('7db4943238391a2969c61b1002e8c621','c1bbea30bf2b2c07b8857d50db8b6ad6','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:26:00',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('8bf879ff4516ed51f8ef3bd12b8710a6','c1bbea30bf2b2c07b8857d50db8b6ad6','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:26:00',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('a35331f2bd9d5e6620d1f1315df72fd7','c1bbea30bf2b2c07b8857d50db8b6ad6','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:26:00',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('810eb7adb0fe913d9770b3968d81d9ab','c1bbea30bf2b2c07b8857d50db8b6ad6','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:26:00',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('c63f1bb54d014c43a250f8049d067bf7','c1bbea30bf2b2c07b8857d50db8b6ad6','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:26:00',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('145673c93404995881a9614b3543fe5a','d4fad35282693aae1edc23173ed797be','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:26:18',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('8b360790a5ae21f2784677c9e7e16780','d4fad35282693aae1edc23173ed797be','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:26:18',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('c6d9e726154d8d270ad8eaa56d0c2b21','d4fad35282693aae1edc23173ed797be','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:26:18',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('f0ed739e2a77d98c2f293774a1c5d111','d4fad35282693aae1edc23173ed797be','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:26:18',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('a5db5fedb87060900f339469c5024aca','d4fad35282693aae1edc23173ed797be','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:26:18',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('7d3e55e9f6917dedca0d362185ac4750','db20bd43d429199749cbcbad9857993c','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:26:33',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('e35825b09afa82fe6e753e44f49419a1','db20bd43d429199749cbcbad9857993c','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:26:33',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('4585f2059e7275d1b4763467c037ec8a','db20bd43d429199749cbcbad9857993c','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:26:33',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('f13bb8dfe4bbb82ae879e38d729d2a79','db20bd43d429199749cbcbad9857993c','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:26:33',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('ed5214a552c1e46335025920262755c1','db20bd43d429199749cbcbad9857993c','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:26:33',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('9cc174252f2637b946f48f46b986af95','46904d8263f449c9a7060b4405c32cd8','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:26:59',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('d8031fea98f60d6de7549799f565e3a1','46904d8263f449c9a7060b4405c32cd8','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:26:59',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('e84c769cc1d492ae720f37786bd2fe4a','46904d8263f449c9a7060b4405c32cd8','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:26:59',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('003c712c8811f5c70e69e9026af62519','46904d8263f449c9a7060b4405c32cd8','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:26:59',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('c00928c462ecb0637d094257d19d6678','8f5b8f6300e2e1922cc3c3b30404b0f3','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:27:19',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('9c241b5d47ebf5310fd463321de08469','8f5b8f6300e2e1922cc3c3b30404b0f3','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:27:19',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('417f229b01cc9c378087f995317222a8','8f5b8f6300e2e1922cc3c3b30404b0f3','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:27:19',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('e50333ecce06e981ecccdf088a263f35','8f5b8f6300e2e1922cc3c3b30404b0f3','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:27:19',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('c1d2e1bc9995f3e05f6e2447db08b319','790d42899ecba76449952684528a8470','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:27:38',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('06b89e4f1957384a1cc5e89b78f3b4ee','790d42899ecba76449952684528a8470','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:27:38',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('eda030ba1ed774dc7c6499b1cef6f637','790d42899ecba76449952684528a8470','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:27:38',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('c68fc238f9e55ce76e0bd2540346135c','790d42899ecba76449952684528a8470','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:27:38',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('e839e1cffe00c30c83359a73de6a57d3','790d42899ecba76449952684528a8470','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:27:38',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('7d166ae2b9f783425e4da48f30710233','197dbd29cba404678d0acc50bcea3d02','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:28:01',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('29c4cd048d81dbc2100213ae817edddb','197dbd29cba404678d0acc50bcea3d02','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:28:01',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('71ddec33b8165ed56296b105e7a6d1d9','197dbd29cba404678d0acc50bcea3d02','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:28:01',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('6ec5adc89d94bbbfed39eea9c436e048','197dbd29cba404678d0acc50bcea3d02','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:28:01',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('826edffbb3431ceabe3ef00da6ffb5f0','197dbd29cba404678d0acc50bcea3d02','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:28:01',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('b7ab2f707db22bc8b6da4ef231bcc4d3','0767cb5c9bb57cfdb58c359a31bc32a8','d46ef1ab6b8c9d8b8d682614b960b3b7','2020-04-17 23:28:28',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('487344d65bd96d800b581375b50d7308','0767cb5c9bb57cfdb58c359a31bc32a8','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-17 23:28:28',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('b97465e006db0b3dce1c750da99f0b16','0767cb5c9bb57cfdb58c359a31bc32a8','f0d6c78a36bcec4024e0cd69444cf4cd','2020-04-17 23:28:28',NULL,'684ab9dafd44c57dd85a08dd18813024',2),('46dbb121f380db2e5d4b001b0789a827','0767cb5c9bb57cfdb58c359a31bc32a8','684ab9dafd44c57dd85a08dd18813024','2020-04-17 23:28:28',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('f856d028f88e845dccc3c5b8aa7192d7','0767cb5c9bb57cfdb58c359a31bc32a8','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','2020-04-17 23:28:28',NULL,'684ab9dafd44c57dd85a08dd18813024',0),('d9a2d825b4912b315d26fdd173ef217f','95e86ac88b31522527f275a8914511d5','cbbbda9d1b7a4ee86e24a9195ab874e2','2020-04-23 17:28:34',NULL,'684ab9dafd44c57dd85a08dd18813024',2);
/*!40000 ALTER TABLE `group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(64) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES ('93dc7abc06d1c91879cea7f30d6b8705','Everyone','Everyone, all users.','2005-04-13 09:45:27','2006-08-25 14:44:20','0afccadaefb0d4ae560edd8a68a5595a',0),('886c57ffc02b4c36a0e33822516d6854','Anonymous Users','Essentially the same as the \'Everyone\' group, but requires that users are NOT logged in.','2005-04-13 09:47:59','2005-04-13 09:47:59','0afccadaefb0d4ae560edd8a68a5595a',0),('c5dd2dd866b5885eea92c0b8902136dc','Registered Users','Members of this group have registered / created an user account on the system.','2005-04-13 09:49:02','2015-09-19 20:51:06','0afccadaefb0d4ae560edd8a68a5595a',0),('d46ef1ab6b8c9d8b8d682614b960b3b7','HOA Board Members','Members of the HOA Board of Directors','2005-04-13 11:45:42','2015-09-19 20:48:39','0afccadaefb0d4ae560edd8a68a5595a',0),('cbbbda9d1b7a4ee86e24a9195ab874e2','HOA Officers','Officers of the HOA','2005-04-13 11:46:22','2005-04-13 11:46:22','0afccadaefb0d4ae560edd8a68a5595a',0),('05b6f36360877bf30f789dc76043e61b','HOA All/Any Committee Members','Members of one or more HOA Committees','2005-04-13 11:47:18','2005-04-13 11:47:18','0afccadaefb0d4ae560edd8a68a5595a',0),('58c8ccf35d4125f7b2cf08ffd3dd8d31','HOA Social Committee Members','Members of the HOA Social Committee','2005-04-13 11:47:53','2005-04-13 11:47:53','0afccadaefb0d4ae560edd8a68a5595a',0),('001c41a2fe47e52026984daf9e1b8735','HOA Landscape Committee Members','Members of the HOA Landscape Committee','2005-04-13 15:34:27','2005-04-13 15:34:27',NULL,0),('8c599ddaf97090e63e8e60778d6ecb30','HOA Architecture Committee Members','Members of the HOA Architecture Committee','2005-04-13 15:34:49','2005-04-13 15:34:49',NULL,0),('7aa9a3db42211343c3267d4078f668ad','Homeowners','People or companies that own individual homes in the association.','2005-04-13 15:36:05','2015-09-19 20:49:31',NULL,0),('ec695008e4c07f6f4e98b06cfc9060e6','Residents','People living in the neighborhood, but they don\'t own the home (renting / leasing / etc).','2005-04-13 15:37:03','2005-04-13 15:37:03',NULL,0),('f0d6c78a36bcec4024e0cd69444cf4cd','System Administrators','Members of this group are have administrative rights / privileges to update and modify the entire web site and all configuration options. Access should be restricted to only those individuals truly needing it.','2005-04-13 15:38:49','2015-09-19 20:52:26',NULL,0),('457e60898dcc1282dd3bc7e0efb18e65','Articles - Edit','Members of this group are allowed to edit existing articles on the site.',NULL,'2015-09-19 20:46:39',NULL,0),('eaeeff75a9f1b49df92c0a20a84ed837','Messageboard - View','Members of this group can view messages posted in the messageboard.','2005-04-21 16:11:32','2015-09-19 20:50:10',NULL,0),('ac631ddcaf3e23dd186391537f31fde0','Messageboard - Post','Members of this group can post messages in the messageboard.','2005-04-22 15:53:51','2015-09-19 20:49:56',NULL,0),('6843d53b9f9c6ac5190b9dac0e89d30e','News - Edit','Members of this group are allowed to edit or create new news articles on the site.',NULL,'2015-09-19 20:50:35',NULL,0),('236cd151bbc3b5c623e2c634c2b2474c','Tasklist - Personal','Members of this group may have a personal tasklist.',NULL,'2015-09-19 20:51:33',NULL,0),('46904d8263f449c9a7060b4405c32cd8','Violations - Edit','Members of this group are allowed to edit existing deed restriction violations.',NULL,'2015-09-19 20:45:47',NULL,0),('f1c1124564e7485a111c55ae851d5f31','HOA Volunteer Coordinators','People that assist with volunteer assignment, tasks, and committee duties.','2005-06-17 10:48:13','2005-06-17 10:48:20',NULL,0),('8f5b8f6300e2e1922cc3c3b30404b0f3','Vote - Edit Questions / Options','Members of this group are allowed to edit voting booth questions and answer options.','2005-06-22 11:56:28','2015-09-19 20:53:00',NULL,0),('c62214faf64a01fb003deda32ad3ef70','Binary Attachments - View','Members of this group are allowed to view file attachments stored in the system.','2006-04-17 10:01:04','2015-09-19 20:47:56','0afccadaefb0d4ae560edd8a68a5595a',0),('5b7fd1e597071930d03b8b1c1d3b2063','Homeowners - Edit','Members of this group are permitted to edit existing homeowners.','2009-07-22 22:02:07','2009-07-22 22:02:07','0afccadaefb0d4ae560edd8a68a5595a',0),('d4a5e68f6036d9ca55f3e51fe76a87e7','Homeowners - Add','Members of this group are permitted to add new homeowners.','2009-07-22 22:02:30','2009-07-22 22:02:30','0afccadaefb0d4ae560edd8a68a5595a',0),('1a5aabf4820d77d2aa67b8f0e049b598','Homeowners - Delete','Members of this group are permitted to delete existing homeowners.','2009-07-22 22:03:03','2009-07-22 22:03:03','0afccadaefb0d4ae560edd8a68a5595a',0),('1c8483f11edb5273749b50aef71f802d','Budget - Add','Members of this group are permitted to add new items to the budget (payments, invoices, credits, etc.)','2009-07-24 23:12:02','2009-07-24 23:12:02','0afccadaefb0d4ae560edd8a68a5595a',0),('eccef449c739f857757260eaa9e7ec4c','Budget - Delete','Members of this group are permitted to delete existing budget items.','2009-07-24 23:12:21','2009-07-24 23:12:21','0afccadaefb0d4ae560edd8a68a5595a',0),('9ded86aae579a9d3fde8a6a07210e650','Budget - Edit','Members of this group are permitted to edit existing budget items.','2009-07-24 23:12:42','2009-07-24 23:12:42','0afccadaefb0d4ae560edd8a68a5595a',0),('206b88ed0d9eb5b3126d487177b39394','Budget - View','Members of this group are permitted to view budget entries but may not make any changes.','2009-07-24 23:13:07','2009-07-24 23:13:07','0afccadaefb0d4ae560edd8a68a5595a',0),('ccfcf56114e9e8a2b9becc6a33b419aa','Lot - Add','Members of this group are permitted to add new lots (property addresses) into the system.','2009-07-24 23:14:58','2009-07-24 23:14:58','0afccadaefb0d4ae560edd8a68a5595a',0),('0a2855edcb165cf58257fdedc2b26fea','Lot - Delete','Members of this group are permitted to delete lots (property addresses) from the system.','2009-07-24 23:15:15','2009-07-24 23:15:15','0afccadaefb0d4ae560edd8a68a5595a',0),('a2650edfc1fa36d9956a8362a6b8ca74','Lot - Edit','Members of this group are permitted to edit existing lots (property addresses) in the system.','2009-07-24 23:15:34','2009-07-24 23:15:34','0afccadaefb0d4ae560edd8a68a5595a',0),('55243529ae58f57d672527ce2b666a09','News - Add','Members of this group are permitted to add new news articles to the website.','2009-07-24 23:16:19','2009-07-24 23:16:19','0afccadaefb0d4ae560edd8a68a5595a',0),('5c6da80583761452ff618410c5496ff1','News - Delete','Members of this group are permitted to delete news articles from the website.','2009-07-24 23:16:38','2009-07-24 23:16:38','0afccadaefb0d4ae560edd8a68a5595a',0),('c1bbea30bf2b2c07b8857d50db8b6ad6','Violations - Add','Members of this group are permitted to add create/issue deed restriction violation notices.','2009-07-24 23:18:12','2009-07-24 23:18:12','0afccadaefb0d4ae560edd8a68a5595a',0),('db20bd43d429199749cbcbad9857993c','Violations - Delete','Members of this group are permitted to delete existing deed restriction violations.','2009-07-24 23:18:32','2009-07-24 23:18:32','0afccadaefb0d4ae560edd8a68a5595a',0),('512adb1af78f909268ca341d237ee1d8','Binary Attachments - Add','Members of this group are permitted to add/import new attachments to the website.','2009-07-24 23:20:26','2009-07-24 23:20:26','0afccadaefb0d4ae560edd8a68a5595a',0),('87f240de2b702ee8e1a5c4f544ef6c37','Binary Attachments - Delete','Members of this group are permitted to delete existing attachments from the website.','2009-07-24 23:20:46','2009-07-24 23:20:46','0afccadaefb0d4ae560edd8a68a5595a',0),('509073d184bb736fd23441f1d2e49ff8','Group - Add','Members of this group are permitted to add new user groups to the system.','2009-07-24 23:21:58','2009-07-24 23:21:58','0afccadaefb0d4ae560edd8a68a5595a',0),('bece93d554ef766ac5fbf1c3c1d1109a','Group - Delete','Members of this group are permitted to delete existing user groups from the system.','2009-07-24 23:22:18','2009-07-24 23:22:18','0afccadaefb0d4ae560edd8a68a5595a',0),('8c25d092851c113784352986e713c469','Group - Edit','Members of this group are permitted to modify existing user groups in the system.','2009-07-24 23:22:36','2009-07-24 23:22:36','0afccadaefb0d4ae560edd8a68a5595a',0),('cdac2f9def83d1f5dbac40236bb3f76e','User - Add','Members of this group are permitted to add new user accounts to the system.','2009-07-24 23:23:51','2009-07-24 23:23:51','0afccadaefb0d4ae560edd8a68a5595a',0),('a51ab63841aecf4d6aa5479d2a1342eb','User - Delete','Members of this group are permitted to delete user accounts from the system.','2009-07-24 23:24:10','2009-07-24 23:24:10','0afccadaefb0d4ae560edd8a68a5595a',0),('1f4331e3a0d8bed99ee20ca4be666b6f','User - Edit','Members of this group are permitted to modify existing user accounts in the system.','2009-07-24 23:24:29','2009-07-24 23:24:29','0afccadaefb0d4ae560edd8a68a5595a',0),('b3accac18f2ae3922b2c0f0f03ad002b','Articles - Add','Members of this group are allowed to create new articles on the site.','2015-09-10 21:11:58','2015-09-19 20:46:22','684ab9dafd44c57dd85a08dd18813024',0),('efde2d9c994c043b8a8713e71a30b8f4','Articles - Delete','Members of this group are allowed to delete articles on the site.','2015-09-10 21:12:28','2015-09-19 20:46:12','684ab9dafd44c57dd85a08dd18813024',0),('c2ecc7a9cf60aab22ecb7425fdbbe08e','Binary Attachments - Edit','Members of this group are allowed to edit existing file attachments.','2015-09-10 21:13:24','2015-09-19 20:47:29','684ab9dafd44c57dd85a08dd18813024',0),('02b4dc9e41f654e4b6f84358ad3eb278','Budget - Approve','Members of this group are permitted to approve pending expenses.','2015-09-10 21:14:24','2015-09-10 21:14:24','684ab9dafd44c57dd85a08dd18813024',0),('92b61e4778fdc6ea398375b8aecc7160','Insurance - Add','Members of this group are permitted to add new insurance policies.','2015-09-10 21:15:03','2015-09-10 21:15:03','684ab9dafd44c57dd85a08dd18813024',0),('d86c53c98c55886dd384c9bfcda38e81','Insurance - Delete','Members of this group are permitted to delete existing insurance policies.','2015-09-10 21:15:19','2015-09-10 21:15:19','684ab9dafd44c57dd85a08dd18813024',0),('43695a66df03926cd9fab7ab702940f0','Insurance - Edit','Members of this group are permitted to modify existing insurance policies.','2015-09-10 21:15:36','2015-09-10 21:15:36','684ab9dafd44c57dd85a08dd18813024',0),('ed7bce923c51ef8fad9e6dfd29ecf189','Insurance - View','Members of this group are permitted to view existing insurance policies.','2015-09-10 21:15:54','2015-09-10 21:15:54','684ab9dafd44c57dd85a08dd18813024',0),('e90538d6f98e338f439e6793a7892fd8','Messageboard - Add','Members of this group are permitted to add new messageboards.','2015-09-10 21:16:32','2015-09-10 21:16:32','684ab9dafd44c57dd85a08dd18813024',0),('d521adafbdb3a5a4ca38c5ad455ed498','Messageboard - Delete','Members of this group are permitted to delete existing messageboards.','2015-09-10 21:16:47','2015-09-10 21:16:47','684ab9dafd44c57dd85a08dd18813024',0),('9f40e5f132cb602d243c4a411a576103','Messageboard - Edit','Members of this group are permitted to modify existing messageboards and messages.','2015-09-10 21:17:14','2015-09-10 21:17:14','684ab9dafd44c57dd85a08dd18813024',0),('d4fad35282693aae1edc23173ed797be','Violations - Approve','Members of this group are permitted to approve submitted deed restriction violations.','2015-09-10 21:18:08','2015-09-10 21:18:08','684ab9dafd44c57dd85a08dd18813024',0),('790d42899ecba76449952684528a8470','Work Request - Add','Members of this group are permitted to add new work requests.','2015-09-10 21:18:38','2015-09-10 21:18:38','684ab9dafd44c57dd85a08dd18813024',0),('197dbd29cba404678d0acc50bcea3d02','Work Request - Delete','Members of this group are permitted to delete existing work requests.','2015-09-10 21:18:55','2015-09-10 21:18:55','684ab9dafd44c57dd85a08dd18813024',0),('0767cb5c9bb57cfdb58c359a31bc32a8','Work Request - Edit','Members of this group are permitted to edit existing work requests.','2015-09-10 21:19:11','2015-09-10 21:19:11','684ab9dafd44c57dd85a08dd18813024',0);
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `heard_tracker`
--

DROP TABLE IF EXISTS `heard_tracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `heard_tracker` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `text` varchar(255) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `count` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `heard_tracker`
--

LOCK TABLES `heard_tracker` WRITE;
/*!40000 ALTER TABLE `heard_tracker` DISABLE KEYS */;
/*!40000 ALTER TABLE `heard_tracker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homeowner_sale`
--

DROP TABLE IF EXISTS `homeowner_sale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homeowner_sale` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `lot_id` varchar(32) DEFAULT NULL,
  `saledate` date DEFAULT '1970-01-01',
  `title_company` varchar(128) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `comments` text,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homeowner_sale`
--

LOCK TABLES `homeowner_sale` WRITE;
/*!40000 ALTER TABLE `homeowner_sale` DISABLE KEYS */;
/*!40000 ALTER TABLE `homeowner_sale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homeowners`
--

DROP TABLE IF EXISTS `homeowners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homeowners` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `lot_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` varchar(32) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `address1` varchar(128) DEFAULT NULL,
  `address2` varchar(128) DEFAULT NULL,
  `address3` varchar(128) DEFAULT NULL,
  `city` varchar(128) DEFAULT NULL,
  `state` char(2) DEFAULT NULL,
  `zipcode` varchar(10) DEFAULT NULL,
  `telephone_fax` varchar(14) DEFAULT NULL,
  `telephone_home` varchar(14) DEFAULT NULL,
  `telephone_mobile` varchar(14) DEFAULT NULL,
  `telephone_work` varchar(14) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `purchasedate` date DEFAULT '1970-01-01',
  `saledate` date DEFAULT '1970-01-01',
  `comments` text,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  `budget_flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  `bankruptcy_date` date DEFAULT '1970-01-01',
  `payment_plan_date` date DEFAULT '1970-01-01',
  `payment_plan_details` text,
  `access_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`,`lot_id`),
  UNIQUE KEY `id` (`id`),
  KEY `purchasedate_index` (`purchasedate`),
  KEY `saledate_index` (`saledate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homeowners`
--

LOCK TABLES `homeowners` WRITE;
/*!40000 ALTER TABLE `homeowners` DISABLE KEYS */;
/*!40000 ALTER TABLE `homeowners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `insurance`
--

DROP TABLE IF EXISTS `insurance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insurance` (
  `id` varchar(32) NOT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `vendor_id` varchar(32) DEFAULT NULL,
  `policy_num` varchar(64) DEFAULT NULL,
  `policy_type` smallint(5) unsigned DEFAULT NULL,
  `lot_id` varchar(32) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `dateexpiration` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `daterenewal` datetime DEFAULT NULL,
  `comments` text,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insurance`
--

LOCK TABLES `insurance` WRITE;
/*!40000 ALTER TABLE `insurance` DISABLE KEYS */;
/*!40000 ALTER TABLE `insurance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `sequence` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` char(32) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `level` tinyint(3) unsigned DEFAULT NULL,
  `message` text,
  `source_ip` varchar(39) DEFAULT NULL,
  `owner_id` varchar(32) DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `sequence` (`sequence`),
  KEY `owner_id_key` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lots`
--

DROP TABLE IF EXISTS `lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lots` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `block` char(1) DEFAULT NULL,
  `building` varchar(5) DEFAULT NULL,
  `lot` tinyint(3) unsigned DEFAULT NULL,
  `plat` smallint(6) DEFAULT NULL,
  `suite` varchar(5) DEFAULT NULL,
  `sqft` smallint(5) unsigned DEFAULT NULL,
  `address` smallint(5) unsigned DEFAULT NULL,
  `street` varchar(64) DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `comment` text,
  `user_id` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `street_index` (`street`),
  KEY `address_index` (`address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lots`
--

LOCK TABLES `lots` WRITE;
/*!40000 ALTER TABLE `lots` DISABLE KEYS */;
/*!40000 ALTER TABLE `lots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messageboard`
--

DROP TABLE IF EXISTS `messageboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messageboard` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `root_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) DEFAULT NULL,
  `tree_id` varchar(32) NOT NULL DEFAULT '0',
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `subject` varchar(128) DEFAULT NULL,
  `article` text,
  `replies` smallint(5) unsigned NOT NULL DEFAULT '0',
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`root_id`),
  UNIQUE KEY `id_2` (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messageboard`
--

LOCK TABLES `messageboard` WRITE;
/*!40000 ALTER TABLE `messageboard` DISABLE KEYS */;
INSERT INTO `messageboard` VALUES ('for-sale','0','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','','2003-05-31 18:13:36','2007-01-08 11:14:37','For-Sale and Help-Wanted Advertisements','<center><h2>For-Sale and Help-Wanted Advertisements</h2></center>',0,0),('hoa','0','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','','2003-06-10 16:45:23','2012-06-23 18:47:57','Homeowner and Resident Questions for the HOA','<center><h2>Homeowner and Resident Questions for the HOA</h2></center>',0,0),('general','0','0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b','','2003-06-10 16:46:54','2020-04-22 23:20:32','General Discussions','<center><h2>General Discussions</h2></center>',2,0);
/*!40000 ALTER TABLE `messageboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `misc_property`
--

DROP TABLE IF EXISTS `misc_property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `misc_property` (
  `id` char(32) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  `comments` text,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `deposit_amount` float DEFAULT NULL,
  `fee_amount` float DEFAULT NULL,
  `rental_amount` float DEFAULT NULL,
  `owner_id` varchar(32) DEFAULT NULL,
  `vendor_id` varchar(32) DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `misc_property`
--

LOCK TABLES `misc_property` WRITE;
/*!40000 ALTER TABLE `misc_property` DISABLE KEYS */;
/*!40000 ALTER TABLE `misc_property` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `title` varchar(255) DEFAULT NULL,
  `article` text,
  `datecreated` datetime NOT NULL DEFAULT '1970-01-01 00:00:01',
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `privacy` bigint(20) unsigned DEFAULT NULL,
  `group_membership` blob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_2` (`id`),
  KEY `id` (`id`),
  KEY `datecreated` (`datecreated`),
  KEY `title` (`title`),
  FULLTEXT KEY `headline` (`article`),
  FULLTEXT KEY `title_2` (`title`,`article`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_tracker`
--

DROP TABLE IF EXISTS `search_tracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_tracker` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `text` varchar(255) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `count` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_tracker`
--

LOCK TABLES `search_tracker` WRITE;
/*!40000 ALTER TABLE `search_tracker` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_tracker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` varchar(32) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `datedue` datetime DEFAULT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_id` varchar(32) DEFAULT NULL,
  `item` text,
  `assigned_user` varchar(32) DEFAULT NULL,
  `assigned_group` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `id_2` (`id`),
  KEY `id` (`id`,`user_id`),
  KEY `id_3` (`id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topics` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `shortname` varchar(30) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_2` (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `topics`
--

LOCK TABLES `topics` WRITE;
/*!40000 ALTER TABLE `topics` DISABLE KEYS */;
/*!40000 ALTER TABLE `topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tracker_browser`
--

DROP TABLE IF EXISTS `tracker_browser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracker_browser` (
  `id` char(32) NOT NULL DEFAULT '',
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `browser` varchar(64) DEFAULT NULL,
  `platform` varchar(16) DEFAULT NULL,
  `version` varchar(8) DEFAULT NULL,
  `count` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `browser` (`browser`),
  KEY `platform` (`platform`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tracker_browser`
--

LOCK TABLES `tracker_browser` WRITE;
/*!40000 ALTER TABLE `tracker_browser` DISABLE KEYS */;
/*!40000 ALTER TABLE `tracker_browser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tracker_language`
--

DROP TABLE IF EXISTS `tracker_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracker_language` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `text` varchar(255) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `count` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tracker_language`
--

LOCK TABLES `tracker_language` WRITE;
/*!40000 ALTER TABLE `tracker_language` DISABLE KEYS */;
/*!40000 ALTER TABLE `tracker_language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_validation`
--

DROP TABLE IF EXISTS `user_validation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_validation` (
  `id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `datecreated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_validation`
--

LOCK TABLES `user_validation` WRITE;
/*!40000 ALTER TABLE `user_validation` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_validation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `user_id` varchar(32) DEFAULT NULL,
  `accountname` varchar(30) NOT NULL DEFAULT '',
  `firstname` varchar(40) NOT NULL DEFAULT '',
  `lastname` varchar(40) NOT NULL DEFAULT '',
  `password` varchar(15) NOT NULL DEFAULT '',
  `password_age` date DEFAULT NULL,
  `password_hint_name` varchar(32) DEFAULT NULL,
  `password_hint` varchar(32) DEFAULT NULL,
  `homeowner_id` varchar(32) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `datelastlogin` datetime DEFAULT NULL,
  `preference` bigint(20) unsigned DEFAULT NULL,
  `preference_edit_row` tinyint(4) DEFAULT '6',
  `preference_edit_col` tinyint(4) DEFAULT '80',
  `preference_items` tinyint(4) DEFAULT '5',
  `personal_privacy` bigint(20) unsigned DEFAULT NULL,
  `comments` text,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`accountname`),
  UNIQUE KEY `id_2` (`id`),
  KEY `id` (`id`,`accountname`),
  KEY `homeowner_id_index` (`homeowner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('0a1b2c3d4e5f6g7a8b9c0d1e2f3g4a5b',NULL,'HOAM System Account','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,6,80,5,NULL,NULL,0),('684ab9dafd44c57dd85a08dd18813024','0afccadaefb0d4ae560edd8a68a5595a','Admin','','','inS4UGgjBAP.I','2017-01-01','cityborn','Anytown',NULL,NULL,'2011-09-28 16:55:45','2020-04-18 00:17:05','2020-05-08 23:48:15',31,10,80,5,NULL,NULL,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `violation_category`
--

DROP TABLE IF EXISTS `violation_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `violation_category` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `parent_category` varchar(32) DEFAULT NULL,
  `initialseverity` varchar(32) DEFAULT NULL,
  `description` text,
  `detail` text,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `category` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_2` (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `violation_category`
--

LOCK TABLES `violation_category` WRITE;
/*!40000 ALTER TABLE `violation_category` DISABLE KEYS */;
INSERT INTO `violation_category` VALUES ('dd1a7f1008c0fa5bebc7900adfe4226f','bfb09b0ce6fdc08c40039be11a8dca16','2eb49f4caa96003053928aaec53fcd65','<p>Your boat or other watercraft is not parked or stored in compliance with HOA deed restrictions.</p>',NULL,'2004-10-08 14:58:13','2012-07-12 21:23:34','684ab9dafd44c57dd85a08dd18813024','Boat'),('24d2c8befb3a672b75fb1fbb649772d7','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>Several areas of your yard require maintenance and general upkeep.</p>',NULL,NULL,'2012-10-19 20:34:03','684ab9dafd44c57dd85a08dd18813024','General Maintenance'),('cfd2ab3fbd7faf94bbb4ca303693189f','335be995477a68dac3c1fafd1312dfbf','2eb49f4caa96003053928aaec53fcd65','<p>We have received complaints of loud and excessive noise / dog barking from your residence.</p>',NULL,NULL,'2012-07-12 21:21:04','684ab9dafd44c57dd85a08dd18813024','Barking Dog'),('7c8ba01e704eb234c425dc9cffc80631','bfb09b0ce6fdc08c40039be11a8dca16','2eb49f4caa96003053928aaec53fcd65','<p>Your trailer is not being parked or stored in accordance with HOA guidelines.</p>',NULL,NULL,'2012-07-12 21:24:31','684ab9dafd44c57dd85a08dd18813024','Trailer'),('a695e169b583f46fecf1aa5683de20b2','335be995477a68dac3c1fafd1312dfbf','2eb49f4caa96003053928aaec53fcd65','Actions are being carried out at your property that are causing a general nuisance to your neighbors.',NULL,NULL,'2012-07-12 21:22:23','684ab9dafd44c57dd85a08dd18813024','General'),('84f8630e0b125f5c8534b2cf73454c6e','bfb09b0ce6fdc08c40039be11a8dca16','2eb49f4caa96003053928aaec53fcd65','<p>There is a vehicle at your residence parked on the yard.</p>',NULL,'2004-10-08 15:54:32','2012-07-12 21:24:08','684ab9dafd44c57dd85a08dd18813024','Parked On Grass'),('56a7a7211a6ec9aea9266b33f6747182','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>There are several large weeds growing in your yard that need to be removed.</p>',NULL,'2004-10-08 16:06:24','2010-04-10 15:04:13','684ab9dafd44c57dd85a08dd18813024','Weeds in Yard'),('12e280e87267758a1d9708535e27dc85','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>There are weeds and/or grass growing in your flowerbeds.</p>',NULL,'2004-10-08 16:12:34','2009-10-16 21:08:03','684ab9dafd44c57dd85a08dd18813024','Weeds / Grass in Flowerbeds'),('247ce0b1ebecd1e324fe26a579c88d56','b9685fa02a2dec248f31e239fe6df27b','2eb49f4caa96003053928aaec53fcd65','<p>There are building materials on your lot that need to be relocated and stored in accordance with the Covenants of the Association.</p>',NULL,'2004-10-11 15:22:20','2012-07-12 21:14:59','684ab9dafd44c57dd85a08dd18813024','Building Materials'),('1469eaef4c3317bfac0cea08586549d1','82f9a5ce2d23eeed3590eaee73df0d4f','2eb49f4caa96003053928aaec53fcd65','<p>There are toys / fitness equipment on your lot that need to be relocated and stored in accordance with the Covenants of the Association.</p>',NULL,'2004-10-11 15:39:38','2009-10-16 21:09:55','684ab9dafd44c57dd85a08dd18813024','Toys / Fitness Equipment'),('4d38ef3230a843fada11bb70c283ac2a','43bc86a5c0bfa40e1c0f19e4a7d4322d','2eb49f4caa96003053928aaec53fcd65','<p>There are damaged / deteriorating sections of your fence that require repair and/or replacement.</p>',NULL,'2004-10-11 16:02:37','2009-10-16 21:11:40','684ab9dafd44c57dd85a08dd18813024','Fence Repair'),('73970976b89573fdc4046943fa7e874e','43bc86a5c0bfa40e1c0f19e4a7d4322d','2eb49f4caa96003053928aaec53fcd65','<p>There are damaged windows on your residence that require repair and/or replacement.</p>',NULL,'2004-10-11 16:06:25','2012-07-12 20:59:51','684ab9dafd44c57dd85a08dd18813024','Window Repair'),('b682acb12ca2b0d3ca9da905750f3309','43bc86a5c0bfa40e1c0f19e4a7d4322d','2eb49f4caa96003053928aaec53fcd65','<p>Several sections of your residence have paint that is faded and peeling.</p>',NULL,'2004-10-11 16:13:07','2012-07-12 20:57:33','684ab9dafd44c57dd85a08dd18813024','Painting'),('af8bc4812ec43f7817c1c4df08a2db1b','b9685fa02a2dec248f31e239fe6df27b','2eb49f4caa96003053928aaec53fcd65','<p>An architectural modification has been made to your residence that has not received prior approval from the HOA.</p>',NULL,'2004-10-11 16:35:00','2012-07-12 21:20:31','684ab9dafd44c57dd85a08dd18813024','Unapproved Addition / Modification'),('ec3b78bb9e6a60f13a9bcb5e30a9eb1e','82f9a5ce2d23eeed3590eaee73df0d4f','2eb49f4caa96003053928aaec53fcd65','<p>There is a large sign in your lot that exceeds the guidelines allowed by the HOA.</p>',NULL,'2004-10-11 18:41:54','2009-10-16 21:12:25','684ab9dafd44c57dd85a08dd18813024','Excessive Signage'),('71edc746de381b01c1684fb434701aaf','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>A significant portion of your landscape beds are barren in front of your residence.</p>',NULL,'2004-10-13 12:55:45','2012-10-19 20:33:39','684ab9dafd44c57dd85a08dd18813024','Empty / Missing Landscape Beds'),('94f0eafcc9c91a2dd7685434922a4740','bfb09b0ce6fdc08c40039be11a8dca16','2eb49f4caa96003053928aaec53fcd65','<p>There is an RV / Motor Home parked at your residence.</p>',NULL,'2004-10-19 20:32:25','2012-07-12 21:24:20','684ab9dafd44c57dd85a08dd18813024','RV / Motor Home'),('6fdb63c5361c581aef2188706bbf6025','bfb09b0ce6fdc08c40039be11a8dca16','2eb49f4caa96003053928aaec53fcd65','<p>A vehicle at your residence has not been moved in a significant amount of time, and appears to be non-functional.</p>',NULL,'2004-10-19 20:43:15','2012-07-12 21:23:20','684ab9dafd44c57dd85a08dd18813024','Abandoned Vehicle'),('7277e571e7282cc1da9ba45441b2abec','b9685fa02a2dec248f31e239fe6df27b','2eb49f4caa96003053928aaec53fcd65','<p>The holiday decorations at your residence are being displayed in excess of the allowed time period.</p>',NULL,'2004-10-19 21:15:23','2012-07-12 21:15:07','684ab9dafd44c57dd85a08dd18813024','Holiday Decorations'),('8a5816bdfe09f5b272d25dd8706162b2','335be995477a68dac3c1fafd1312dfbf','2eb49f4caa96003053928aaec53fcd65','<p>We have received complaints of loud and excessive noise from your residence.</p>',NULL,'2004-10-29 11:34:00','2012-07-12 21:22:46','684ab9dafd44c57dd85a08dd18813024','Loud Noises'),('146c4a14d8eee8d402eabfb3023eb85b','335be995477a68dac3c1fafd1312dfbf','2eb49f4caa96003053928aaec53fcd65','<p>We have received complaints that a dog living at your residence has defecated on public or private property and the feces have not been collected and/or disposed of in a sanitary manner.</p>',NULL,'2005-05-09 15:56:27','2012-07-12 21:21:16','684ab9dafd44c57dd85a08dd18813024','Failure to Clean Up after Dog'),('b390ae9a1d8533a740f7cb885ab4c5ff','8ab34301483c3d21d76bc6d18154d312','2eb49f4caa96003053928aaec53fcd65','<p>The coverings of one or more windows at your residence do not match the guidelines allowed by the HOA.</p>',NULL,'2005-06-30 22:29:14','2012-07-12 21:23:04','684ab9dafd44c57dd85a08dd18813024','Window Coverings'),('006ddc8b72274e3fc3958cf4676adea9','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>Your property along the alley requires maintenance and general upkeep (tall grass, weeds, debris, etc.)</p>',NULL,'2006-10-31 13:44:57','2012-10-19 20:33:22','684ab9dafd44c57dd85a08dd18813024','Alley Maintenance'),('e9bc9816f3212f07e6ca84b0822a7999','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>The landscape beds at your residence are damaged / require repair.</p>',NULL,'2006-10-31 13:54:13','2012-10-19 20:33:52','684ab9dafd44c57dd85a08dd18813024','Landscape Beds Damaged / Need Repair'),('fca3321b656dd02582913fcaaf8a533a','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>Your property along the rear of your property requires maintenance and general upkeep (tall grass, weeds, debris, etc.)</p>',NULL,'2009-10-04 21:23:19',NULL,'684ab9dafd44c57dd85a08dd18813024','Rear Property Maintenance'),('2766a380b7b03accea597b944c893b81','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>There are plants / landscape improvement materials on your lot that need to be relocated and stored in accordance with the Covenants of the Association.</p>',NULL,'2007-02-01 14:43:23','2012-10-19 20:34:31','684ab9dafd44c57dd85a08dd18813024','Plants / Landscape Improvement Materials'),('8119a923ef42f03149dfd212dedf4df3','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>Your property along side of your residence requires maintenance and general upkeep (tall grass, weeds, debris, etc.)</p>',NULL,'2007-03-29 16:25:55','2009-10-16 21:13:33','684ab9dafd44c57dd85a08dd18813024','Side Yard Maintenance'),('5d7dd263c0d3bde3d2ce2f7591d5129d','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>One or more trees or shrubs on your property require trimming and/or pruning.</p>',NULL,'2007-03-29 16:30:54','2009-10-16 21:57:41','684ab9dafd44c57dd85a08dd18813024','Tree / Shrub Pruning'),('f7d5dfd86ca6675f6e411b9328e9b8d3','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>Artificial flora on your property is not permitted.</p>',NULL,'2007-03-29 16:36:34','2009-10-16 21:13:02','684ab9dafd44c57dd85a08dd18813024','Artificial Flora'),('709744a661f30fe3eddc1772741f7934','b9685fa02a2dec248f31e239fe6df27b','2eb49f4caa96003053928aaec53fcd65','<p>There are materials in your driveway that need to be relocated and stored in accordance with the Covenants of the Association.</p>',NULL,'2007-06-20 15:09:13','2012-07-12 21:20:08','684ab9dafd44c57dd85a08dd18813024','Materials in Driveway'),('7ebd2ab58203f4c60be8b0cc7af1c48d','43bc86a5c0bfa40e1c0f19e4a7d4322d','2eb49f4caa96003053928aaec53fcd65','<p>The paint on your home is not an earth-tone color as required by the Community Beautification Standards.</p>',NULL,'2007-07-03 15:52:21','2012-07-12 20:57:55','684ab9dafd44c57dd85a08dd18813024','Painting (Not Earth-Tone Color)'),('c382af0ab18a5223f277cdf034ae2774','b9685fa02a2dec248f31e239fe6df27b','2eb49f4caa96003053928aaec53fcd65','<p>There are materials on the side of your residence that need to be relocated and stored in accordance with the Covenants of the Association.</p>',NULL,'2008-03-24 14:11:00','2012-07-12 21:20:19','684ab9dafd44c57dd85a08dd18813024','Materials in Side Yard'),('a18be27e540ebe990b849b1963953323','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>There is debris from landscape maintenance on your lot that needs to be disposed of or relocated in accordance with the Covenants of the Association.</p>',NULL,'2008-05-01 13:15:11','2012-10-19 20:33:10','684ab9dafd44c57dd85a08dd18813024','Remove Debris'),('c5ed5024ea687ef7a9cd0af2d2ab95b5','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>One or more trees or shrubs on your property are dead and need to be removed and/or replaced.</p>',NULL,'2008-06-27 20:17:40','2010-03-26 13:40:19','684ab9dafd44c57dd85a08dd18813024','Tree / Shrub Dead'),('6d67e10e9cb560380a347e97e47350b1','43bc86a5c0bfa40e1c0f19e4a7d4322d','2eb49f4caa96003053928aaec53fcd65','<p>There is damage to the exterior of your residence that requires repair.</p>',NULL,'2008-07-25 11:30:38','2012-07-03 20:46:30','684ab9dafd44c57dd85a08dd18813024','Damage to Residence'),('43bc86a5c0bfa40e1c0f19e4a7d4322d',NULL,NULL,NULL,NULL,'2008-07-25 11:30:38','2008-09-12 10:41:38','684ab9dafd44c57dd85a08dd18813024','Exterior Maintenance'),('365fecdf40eeba1b6d55b87b82a0e843','43bc86a5c0bfa40e1c0f19e4a7d4322d','2eb49f4caa96003053928aaec53fcd65','<p>There is damage to the exterior of your residence (gutters) that requires repair.</p>',NULL,'2008-07-25 12:34:18',NULL,'684ab9dafd44c57dd85a08dd18813024','Damage to Residence (Gutters)'),('d3f3097dade3188bbd5e0740cb43de87','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>Your yard requires routine maintenance, including mowing, edging, and trimming where appropriate.</p>',NULL,'2008-07-28 20:17:22','2009-10-16 20:47:44','684ab9dafd44c57dd85a08dd18813024','Mow Yard'),('5057b51d7592fc74e6fca45b8dccfc59',NULL,NULL,'<p>Several areas of your yard require maintenance and general upkeep.</p>',NULL,'2008-07-28 20:17:22','2010-04-10 15:08:06','684ab9dafd44c57dd85a08dd18813024','Landscaping'),('3845728845afe9f3babe679791657e27','5057b51d7592fc74e6fca45b8dccfc59','2eb49f4caa96003053928aaec53fcd65','<p>Your yard and/or garden areas require watering.</p>',NULL,'2008-08-14 21:15:48',NULL,'684ab9dafd44c57dd85a08dd18813024','Watering'),('c599f3391949282b84f5c41a758d3a07','82f9a5ce2d23eeed3590eaee73df0d4f','2eb49f4caa96003053928aaec53fcd65','<p>The garbage/refuse containers at your residence must be put away and stored as specified in the Covenants of the Association.</p>',NULL,'2009-10-16 20:32:59',NULL,'684ab9dafd44c57dd85a08dd18813024','Trash Cans Must Be Put Away'),('335be995477a68dac3c1fafd1312dfbf',NULL,NULL,NULL,NULL,'2012-07-12 21:21:04',NULL,'684ab9dafd44c57dd85a08dd18813024','Nuisance'),('8ab34301483c3d21d76bc6d18154d312',NULL,NULL,NULL,NULL,'2012-07-12 21:23:04',NULL,'684ab9dafd44c57dd85a08dd18813024','Miscellaneous'),('bfb09b0ce6fdc08c40039be11a8dca16',NULL,NULL,NULL,NULL,'2012-07-12 21:23:20',NULL,'684ab9dafd44c57dd85a08dd18813024','Vehicle'),('77778d970efbfd3c686451a810dc0232','43bc86a5c0bfa40e1c0f19e4a7d4322d','2eb49f4caa96003053928aaec53fcd65','<p>The paint on your home is not a neutral earth-tone color.</p>',NULL,'2009-10-16 21:40:11',NULL,'684ab9dafd44c57dd85a08dd18813024','Paint color is not neutral / earth-tone'),('7f47fea941132dbc6e88e1795889910d','82f9a5ce2d23eeed3590eaee73df0d4f','2eb49f4caa96003053928aaec53fcd65','<p>There are materials at your residence that need to be relocated and stored in accordance with the Covenants of the Association.</p>',NULL,'2009-10-16 21:43:42',NULL,'684ab9dafd44c57dd85a08dd18813024','Recreational Equipment'),('e4021ec8d01bfe23ec71b239a439398d','43bc86a5c0bfa40e1c0f19e4a7d4322d','2eb49f4caa96003053928aaec53fcd65','<p>There is trash/refuse at your residence which must be disposed of as specified in the Covenants of the Association.</p>',NULL,'2009-10-16 21:47:30',NULL,'684ab9dafd44c57dd85a08dd18813024','Removal of Trash / Refuse'),('b9685fa02a2dec248f31e239fe6df27b',NULL,NULL,NULL,NULL,'2012-07-12 21:14:30',NULL,'684ab9dafd44c57dd85a08dd18813024','General');
/*!40000 ALTER TABLE `violation_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `violation_severity`
--

DROP TABLE IF EXISTS `violation_severity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `violation_severity` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `severity` tinyint(4) DEFAULT NULL,
  `escalate` varchar(32) DEFAULT NULL,
  `numdays` tinyint(4) DEFAULT NULL,
  `fine_interest` float DEFAULT '0',
  `fine_per_notice` float DEFAULT '0',
  `fine_per_day` float DEFAULT '0',
  `preamble` text,
  `closing` text,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `violation_severity`
--

LOCK TABLES `violation_severity` WRITE;
/*!40000 ALTER TABLE `violation_severity` DISABLE KEYS */;
INSERT INTO `violation_severity` VALUES ('2eb49f4caa96003053928aaec53fcd65',1,'1ba18dd2d62f1308ec20e417910a754c',15,0,0,0,'<p>Purchasing a home in a neighborhood with a Homeowner Association offers many advantages to a homeowner, but at the same time, imposes some obligations. These obligations are not intended as an inconvenience or an invasion of your freedom, but rather as a means of maintaining harmony in your community.</p>\\r\\n<p>Sometimes we find that members of the Association are unaware of the recorded deed restrictions on their property. Deed restrictions exist to maximize the property value and beauty of your neighborhood by improving the overall appearance of the community, thereby benefiting both you and your neighbors.</p>\\r\\n<p>In order to maintain the overall appearance, regular inspections of the neighborhood occur to ensure the Association\\\'s governing documents and/or its established rules and regulations are followed. In addition, we are often contacted by homeowners and other residents from the neighborhood with reports of rules violations.</p>\\r\\n<p>During a routine inspection of the neighborhood on [~date-violation~], the following item(s) were found in need of your attention:</p>','<p>If you need a new copy of the Association\\\'s governing documents (a copy was provided to you when you purchased your residence), you may either contact our office to purchase a new set, or you may view them online at the Association\\\'s web site.</p>\\r\\n<p>We make a considerable effort to assure that our notices are based on valid observation and fact. However, if you find this notice to be incorrect, unjustified, unfair, or improper in any way, please do not hesitate to being it to our immediate attention. Otherwise, we thank you for your cooperation and assistance in helping to keep this a neighborhood of which we may be proud, and request that you resolve the item(s) mentioned within the next two weeks.</p>','2006-07-28 15:46:18','2020-04-24 20:37:54','684ab9dafd44c57dd85a08dd18813024',520),('966ef09fe4ec25407b6fa9b3987f3ae9',4,'966ef09fe4ec25407b6fa9b3987f3ae9',3,0,100,5,'<p>The legal documents for the Association contains covenants. Legally these covenants are a part of the deed for each residence and are binding upon all homeowners regardless of whether or not the homeowner is familiar with such covenants.</p>\\r\\n<p>[~previous-notice~]During a property inspection on [~date-violation~], you were found to still be in violation of the following:</p>','<p>You were further notified that unless corrective action was taken relative to this infraction that you would be assessed a fine. Please be advised that as you did not correct this infraction, the fine described previously has been charged to your account with the Association. If payment of this fine is not received in our offices within thirty (30) days from the date of this letter, collection activity will commence.</p>\\r\\n<p>Additionally, because you have previously received notices of this violation, the normal grace period for resolution of [~numdays+5~] days has been reduced to [~numdays~] days. If this violation has not been resolved by [~date-resolveby~], you will be fined a minimum of $[~fine-notice~], and additionally you will be fined $[~fine-day~] for each day that the violation remains unresolved.</p>\\r\\n<p>Please note: per Article VI, Section 2 of the Covenants of the Association, the Association has the right to enter onto your property to resolve this issue and/or perform the required maintenance without any liability for damages for wrongful entry, trespass or otherwise. The cost of this maintenance will be billed back to you. <strong>We strongly encourage you to resolve the issues mentioned above before this is necessary.</strong></p>\\r\\n<p>If you have any questions, please contact an agent of the Association at the address above.</p>','2006-07-28 16:15:14','2020-04-17 22:46:09','684ab9dafd44c57dd85a08dd18813024',0),('36117b0c101a1039e433b0d7dab00ac5',3,'966ef09fe4ec25407b6fa9b3987f3ae9',5,0,50,0,'<p>The legal documents for the Association contains covenants. Legally these covenants are a part of the deed for each residence and are binding upon all homeowners regardless of whether or not the homeowner is familiar with such covenants.</p>\\r\\n<p>[~previous-notice~]During a property inspection on [~date-violation~], you were found to still be in violation of the following:</p>','<p>You were further notified that unless corrective action was taken relative to this infraction that you would be assessed a fine. Please be advised that as you did not correct this infraction, the fine described previously has been charged to your account with the Association. If payment of this fine is not received in our offices within thirty (30) days from the date of this letter, collection activity will commence.</p>\\r\\n<p>Additionally, because you have previously received notices of this violation, the normal grace period for resolution of [~numdays+5~] days has been reduced to [~numdays~] days. If this violation has not been resolved by [~date-resolveby~], you will be fined a minimum of $[~fine-notice~], and additionally you will be fined $[~fine-day~] for each day that the violation remains unresolved.</p>\\r\\n<p>Please note: per Article VI, Section 2 of the Covenants of the Association, the Association has the right to enter onto your property to resolve this issue and/or perform the required maintenance without any liability for damages for wrongful entry, trespass or otherwise. The cost of this maintenance will be billed back to you. <strong>We strongly encourage you to resolve the issues mentioned above before this is necessary.</strong></p>\\r\\n<p>If you have any questions, please contact an agent of the Association at the address above.</p>','2006-07-28 16:16:28','2020-04-17 22:46:00','684ab9dafd44c57dd85a08dd18813024',0),('1ba18dd2d62f1308ec20e417910a754c',2,'36117b0c101a1039e433b0d7dab00ac5',10,0,25,0,'<p>When the Homeowner Association (of which you are a member) was formed, several governing documents were established which regulate the operations and requirements of the Association. These documents contain certain covenants and obligations regarding the responsibility you have as a homeowner. These obligations are not intended as an inconvenience or an invasion of your freedom, but rather as a means of maintaining harmony within the community.</p>\\r\\n<p>In fulfilling the requirements of the governing documents, we regularly inspect your neighborhood in order to ensure that each owner is complying with these standards. In addition to our regular inspections, we are often contacted by owners and other residents from the community with reports of rules violations.</p>\\r\\n<p>During an inspection of the neighborhood on [~date-violation~], it was observed or reported that the following item(s) are in need of your attention:</p>','<p>These item(s) are a violation of the governing documents of the Association and require your attention to resolve. If you are unable to resolve this in the next [~numdays~] days, or if you have other questions, please contact an agent of the Association at the address above.[~previous-notice~]</p>\\r\\n<p>If corrective action is not taken by [~date-resolveby~], a fine of $[~fine-notice~] (which will be charged to your account with the Association) may be assessed for your failure to comply with the governing documents of the Association. If you have already taken care of this issue, please disregard this notice.</p>','2006-07-28 16:17:22','2020-04-17 22:53:32','684ab9dafd44c57dd85a08dd18813024',8);
/*!40000 ALTER TABLE `violation_severity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `violations`
--

DROP TABLE IF EXISTS `violations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `violations` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `lot_id` mediumint(8) unsigned DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `dateviolation` date DEFAULT NULL,
  `dateresolveby` date DEFAULT NULL,
  `dateresolution` date DEFAULT NULL,
  `description` text,
  `resolution` text,
  `user_id` varchar(32) DEFAULT NULL,
  `original_letter` text,
  `specifics` varchar(255) DEFAULT NULL,
  `category` text,
  `severity` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_2` (`id`),
  KEY `id` (`id`),
  KEY `lot_id` (`lot_id`),
  KEY `violation_date` (`dateviolation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `violations`
--

LOCK TABLES `violations` WRITE;
/*!40000 ALTER TABLE `violations` DISABLE KEYS */;
/*!40000 ALTER TABLE `violations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote`
--

DROP TABLE IF EXISTS `vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vote` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `root_id` varchar(32) NOT NULL DEFAULT '',
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `question` varchar(255) DEFAULT NULL,
  `count` smallint(5) unsigned DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`root_id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote`
--

LOCK TABLES `vote` WRITE;
/*!40000 ALTER TABLE `vote` DISABLE KEYS */;
/*!40000 ALTER TABLE `vote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote_group_members`
--

DROP TABLE IF EXISTS `vote_group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vote_group_members` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `owner_id` varchar(32) DEFAULT NULL,
  `member_id` varchar(32) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote_group_members`
--

LOCK TABLES `vote_group_members` WRITE;
/*!40000 ALTER TABLE `vote_group_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `vote_group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vote_log`
--

DROP TABLE IF EXISTS `vote_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vote_log` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `vote_id` varchar(32) DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vote_id` (`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote_log`
--

LOCK TABLES `vote_log` WRITE;
/*!40000 ALTER TABLE `vote_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `vote_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_requests`
--

DROP TABLE IF EXISTS `work_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_requests` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `datecreated` datetime DEFAULT NULL,
  `datemodified` datetime DEFAULT NULL,
  `datedue` date DEFAULT NULL,
  `datecompleted` date DEFAULT NULL,
  `priority` bigint(20) unsigned DEFAULT NULL,
  `status` bigint(20) unsigned DEFAULT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `requester_id` varchar(32) DEFAULT NULL,
  `lot_id` varchar(32) DEFAULT NULL,
  `parent_id` varchar(32) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `notes` text,
  `vendor_id` varchar(32) DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lot_id` (`lot_id`),
  KEY `user_id` (`user_id`),
  KEY `requester_id` (`requester_id`),
  KEY `vendor_id` (`vendor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_requests`
--

LOCK TABLES `work_requests` WRITE;
/*!40000 ALTER TABLE `work_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `work_requests` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-05-16 19:51:32
