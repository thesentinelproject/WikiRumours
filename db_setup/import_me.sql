-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Host: mysql.wikirumours.org
-- Generation Time: Feb 26, 2014 at 10:30 AM
-- Server version: 5.1.56
-- PHP Version: 5.3.27

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `wikirumours_downloadable`
--

-- --------------------------------------------------------

--
-- Table structure for table `wr_api_calls_external`
--

CREATE TABLE IF NOT EXISTS `wr_api_calls_external` (
  `call_id` int(9) NOT NULL AUTO_INCREMENT,
  `destination` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `queried_on` datetime NOT NULL,
  PRIMARY KEY (`call_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `wr_api_calls_external`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_api_calls_internal`
--

CREATE TABLE IF NOT EXISTS `wr_api_calls_internal` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `api_key` char(32) CHARACTER SET utf8 NOT NULL,
  `queried_on` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `wr_api_calls_internal`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_cms`
--

CREATE TABLE IF NOT EXISTS `wr_cms` (
  `cms_id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `content_js` text COLLATE utf8_unicode_ci NOT NULL,
  `content_css` text COLLATE utf8_unicode_ci NOT NULL,
  `page_or_block` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `saved_on` datetime NOT NULL,
  `saved_by` int(9) NOT NULL,
  PRIMARY KEY (`cms_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

--
-- Dumping data for table `wr_cms`
--

INSERT INTO `wr_cms` (`cms_id`, `slug`, `title`, `content`, `content_js`, `content_css`, `page_or_block`, `saved_on`, `saved_by`) VALUES
(1, 'about', 'About WikiRumours', '<div class=''pageModule''>\r\n\r\n<h2>About WikiRumours</h2>\r\n\r\n<p>WikiRumours is a web- and mobile-based platform for moderating misinformation and disinformation. The software is free and open source under an <a href=''http://opensource.org/licenses/MIT'' target=''_blank''>MIT license</a>, which means that it can be used for open, commerical or proprietary use, without mandatory attribution. That said, we''re always eager to hear the uses for which people employ our software, so please <a href=''/contact''>let us know how you''re using WikiRumours</a>.</p>\r\n\r\n<p>WikiRumours is the brainchild of <a href=''http://www.thesentinelproject.org'' target=''_blank''>The Sentinel Project</a>. To find out what''s in the works for future releases of WikiRumours, check our <a href=''/roadmap''>product roadmap</a>.</p>\r\n\r\n</div>\r\n\r\n<div class=''pageModule''>\r\n\r\n<h2>How it works</h2>\r\n\r\n<p>WikiRumours is both a piece of software and an implied workflow for triaging and responding to misinformation and disinformation.</p>\r\n\r\n<div class=''row''>\r\n<div class=''col-md-10 col-md-offset-1''>\r\n<div class=''thumbnail''>\r\n  <img src=''/assets/cms_files/workflow_small.jpg'' alt=''WikiRumours workflow'' />\r\n  <div class=''caption text-center''><br /><a href=''/assets/cms_files/workflow_large.jpg'' target=''_blank''>Larger version</a></div>\r\n</div><!-- thumbnail -->\r\n</div><!-- column -->\r\n</div><!-- row -->\r\n\r\n<br />\r\n\r\n<p>There are several unique types of user in WikiRumours, which has conditional logic associated with each user type:</p>\r\n\r\n<table class=''table table-hover table-condensed''>\r\n<thead>\r\n<tr>\r\n<th>Role</th>\r\n<th>Responsibilities</th>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr>\r\n<td class=''nowrap''>Community member</td>\r\n<td>Enters and annotates rumours in the system</td>\r\n</tr>\r\n<tr>\r\n<td class=''nowrap''>Proxy</td>\r\n<td>Enters rumours on behalf of other users who may not have direct connectivity to the WikiRumours platform (i.e. intake from walk-ins, SMSes, voicemail)</td>\r\n</tr>\r\n<tr>\r\n<td class=''nowrap''>Moderator</td>\r\n<td>Triages new rumours and assigns to community liaisons; can update rumour status</td>\r\n</tr>\r\n<tr>\r\n<td class=''nowrap''>Community liaison</td>\r\n<td>Updates rumour status</td>\r\n</tr>\r\n<tr>\r\n<td class=''nowrap''>Administrator</td>\r\n<td>Varying levels of permission for modifying rumours, users, website content, etc.</td>\r\n</tr>\r\n</table>\r\n\r\n</div>', '', '', 'p', '2013-12-17 20:17:55', 1),
(3, 'footer nav', '', '<li><a href=''/about''>About</a></li>\r\n<li><a href=''/help''>Help</a></li>\r\n<li><a href=''/contact''>Contact</a></li>\r\n<li><a href=''/terms''>Terms of Use</a></li>\r\n<li><a href=''/privacy''>Privacy Policy</a></li>\r\n', '', '', 'b', '2014-01-03 13:55:24', 1),
(4, 'privacy', 'Privacy Policy', '<h2>Privacy Policy</h2>\r\n\r\n<p>Because we believe in clarity of information, we''ve written our privacy policy to be short and relevant to the specific use of WikiRumours. Note that this privacy policy applies to www.wikirumours.org, so if you''ve installed an open source copy of the downloadable WikiRumours software on your own domain, please adjust the privacy policy accordingly.</p>\r\n\r\n<div class=''row''>\r\n<div class=''col-md-5''><p class=''lead''>WikiRumours takes precautions to protect your personal information, and shares data with public website visitors or other parties only in anonymized or aggregate form.</p></div>\r\n<div class=''col-md-7''>WikiRumours collects personal information from users who register on the website or access our API or other services or request information about WikiRumours, such as name, address, phone number, and email address. We also automatically receive and record information from your browser, including your IP address and cookies. The personal information collected is used for identification, authentication, service improvement, research, and contact. We also reserve the right to capture usage data for diagnostic purposes including, but not limited to, IP addresses and geolocation data.<br /><br />We make reasonable efforts to protect your data from unnecessary or inappropriate use, as documented in this Privacy Policy. Sensitive data is encrypted in our database using a one-way algorithm, and measures are available to users of WikiRumours to anonymize their identity and authorship.<br /><br />Further, WikiRumours refuses to disclose user data to third parties except as we reasonably believe is required by law or regulation, or as necessary to enforce our Terms of Use or protect the rights, property, or safety of WikiRumours, its users, and the public.</div>\r\n</div>\r\n\r\n<br />\r\n<div class=''row''>\r\n<div class=''col-md-5''><p class=''lead''>Your personal information is, first and foremost, yours.</p></div>\r\n<div class=''col-md-7''>You have the right to access the personal information we hold about you in order to verify the accuracy of that information.  Upon receipt of your written request, we will provide you with a copy of your personal information and will endeavor to deal with all such requests for access in a timely manner. If you, or a duly authorized verified representative, request that personal information or a profile be removed from the website, we will comply within a reasonable amount of time following such request. We cannot, however, comply with requests for personal information relating to other instances of the WikiRumours software, since we do not have access to those databases.</div>\r\n</div>\r\n\r\n<br />\r\n<div class=''row''>\r\n<div class=''col-md-5''><p class=''lead''>WikiRumours uses "cookies" for authentication purposes only.</p></div>\r\n<div class=''col-md-7''>Cookies are used on the website both to ensure valid authentication of registered users and to maintain a logged in state for users who return to the website after closing the browser window or otherwise navigating away from the website.</div>\r\n</div>\r\n\r\n<br />\r\n<div class=''row''>\r\n<div class=''col-md-5''><p class=''lead''>WikiRumours discourages, but cannot prevent, links to third-party content. Please be cautious.</p></div>\r\n<div class=''col-md-7''>WikiRumours allows the posting of links to third-party websites.  We have no control over third-party websites and the provision of links in no way constitutes an endorsement, authorization, or representation of our connection to that third party.  If you access third-party websites using links from WikiRumours or our Services, you should read the privacy policies of those sites and be aware that they may follow different rules regarding the use or disclosure of your Personal Information.  Additionally, third-party web advertisers may also set cookies. These cookies allow the advertisement server operated by that third party to recognize your computer each time they send you an online advertisement. Accordingly, advertisement servers may compile information about where or whether you viewed their advertisements and which advertisements you clicked on. This information allows web advertisers to deliver targeted advertisements that they believe will be of most interest to you.  This Privacy Policy applies to cookies placed on your computer by us, but does not cover the use of cookies by any third-party web advertisers.  For the privacy practices of such third-party web advertisers, you should consult the applicable privacy policy for the relevant third-party web advertiser(s).</div>\r\n</div>\r\n\r\n<br />\r\n<div class=''row''>\r\n<div class=''col-md-5''><p class=''lead''>Your personal information will remain in the database indefinitely unless you request otherwise.</p></div>\r\n<div class=''col-md-7''>We will keep your personal information for as long as it remains necessary for the operation of the Services or as required by law, which may extend beyond the termination of our relationship with you. In general, once you inform us that you no longer wish to use the Services, we will delete your records from our systems as soon as practicable.</div>\r\n</div>\r\n\r\n<br />\r\n<div class=''row''>\r\n<div class=''col-md-5''><p class=''lead''>Circumstances change. So will this Privacy Policy.</p></div>\r\n<div class=''col-md-7''>We reserve the right to change this Privacy Policy at any time. Once a new Privacy Policy becomes effective, it will apply to all personal information submitted by you or collected about you both before and after the date such new Privacy Policy took effect.  The date on which the latest update was made is indicated at the bottom of this page.  We recommend that you revisit this page from time to time to ensure you are aware of any changes.  Your continued use of the Services signifies your acceptance of any changes.</div>\r\n</div>\r\n\r\n<br />\r\n<div class=''text-muted''>Last updated 6 January 2014.</div>', '', '', 'p', '2014-01-06 11:24:31', 1),
(5, 'home_page', '', '<p class=''lead''>Misinformation and disinformation pose a challenge to development, governance, public health and human security efforts around the world. WikiRumours is a workflow and technology platform designed to counter the spread of  bad information through transparency and early mitigation of conflict.</p>\r\n\r\n<p>As communications technology has become more widely distributed in the developing world, the spread of inaccurate, incomplete or fabricated information is an increasingly significant threat to peace and stability, particularly in regions with limited access to reliable third-party media.</p>\r\n\r\n<p>WikiRumours can be used to contextualize and mitigate misinformation through community involvement, crisis moderation and transparency. <a href=''/about''>Here''s how</a>.</p>\r\n\r\n<p>Want to help?</p>\r\n\r\n<ul>\r\n<li><span>If you''ve heard a rumour in your community which represents immediate risk of conflict, <a href=''/rumour_add''>report it now</a>.</span></li>\r\n<li><span>If you have verifiable information on an existing WikiRumours rumour, leave a comment on that rumour.</span></li>\r\n<li><span>If you want to implement a private, local instance of WikiRumours, download the open source version of the software. (Coming soon)</span></li>\r\n<li><span>Spread the word about WikiRumours in your community and beyond!</span></li>\r\n</ul>\r\n', '', '', 'b', '2013-11-26 19:55:20', 1),
(7, 'terms', 'Terms of Use', '<h2>Terms of Use</h2>\r\n	\r\n<br />\r\n<p><b>Acceptance of Terms.</b> WikiRumours ("WikiRumours" / "we" / "us") makes the content of the website www.wikirumours.org (the "Website") available as an online workflow for triaging misinformation through a web interface, an API, and other means (the "Services"). By accessing or using the Services you are agreeing to be bound by the following terms and conditions ("Terms of Use"), including any subsequent changes or modifications to them.</p>\r\n	\r\n<p><b>Content.</b> You acknowledge and agree that the content accessible through the Services is community-authored and -moderated, and that WikiRumours assumes no liability for any content posted by users. WikiRumours is a hosting service and may choose to moderate content to the extent of standardization of format, but is not responsible for the completeness, accuracy, appropriateness, or decency of content posted on its Services, and does not moderate content. The views expressed by content authors are not necessarily the views of the website or its agents.</p>\r\n	\r\n<p><b>License.</b> You acknowledge and agree that any content you submit to WikiRumours is automatically licensed under the Creative Commons Attribution-ShareAlike 3.0 Unported License ("CC BY-SA") and the GNU Free Documentation License ("GFDL") (unversioned, with no invariant sections, front-cover texts, or back-cover texts), and is yours to so license. You may not post copyrighted information to WikiRumours.</p>\r\n	\r\n<p><b>Third-Party Content.</b> WikiRumours allows the posting of links to third-party websites.  We have no control over third-party websites and the provision of links in no way constitutes an endorsement, authorization, or representation of our connection to that third party.</p>\r\n	\r\n<p><b>Security.</b> You are responsible for safeguarding your own password and should never disclose it to any third party. WikiRumours is unable to retrieve user passwords.</li>\r\n	\r\n<p><b>Privacy.</b> WikiRumours adheres to a Privacy Policy which is posted publicly at <a href=''/privacy''>http://www.wikirumours.org/privacy</a>.</p>\r\n\r\n<p>WikiRumours reserves the right to change this Privacy Policy at any time. We recommend that you revisit this policy from time to time to ensure that you are aware of any changes.</p>\r\n	\r\n<p><b>Acceptable Behavior.</b> WikiRumours reserves the right to deny Services to users who do not follow our standards of acceptable behavior, which are intended to ensure fair access and courteous treatment of all users. Prohibited behavior includes, but is not limited to:</p>\r\n\r\n<ul>\r\n<li><span>Providing false information, including impersonation or misrepresentation during registration or login to the Services or when contacting WikiRumours.</span></li>\r\n<li><span>Excessive or disruptive traffic to the website or API, whether wilfull or not, including any attempts to subvert the API query limit by any means.</span></li>\r\n<li><span>Harassment or disrespect of others through the Services.</span></li>\r\n<li><span>Solicitation for commercial purposes through the Services.</span></li>\r\n<li><span>Knowingly posting information which is false, defamatory, or libelous.</span></li>\r\n<li><span>Infringing copyrights, trademarks, patents, or other proprietary rights.</span></li>\r\n<li><span>Using the Services as an opportunity to proselytize or advocate political or religious opinion, or otherwise behave in a non-objective manner.</span></li>\r\n</ul>\r\n	\r\n<p><i>To reiterate,</i> although WikiRumours deals with misinformation, it is not a forum to defame, abuse, disrespect, preach to, politicize the contributions of, harass, or otherwise abuse other users or the Services.</p>\r\n	\r\n<p><b>No warranties.</b> All WikiRumours Services are provided "as is." WikiRumours disclaims all representations, warranties, and conditions, either express, implied, statutory, by usage of trade, course of dealing, or otherwise including but not limited to any implied warranties for a particular purpose, and any information or material downloaded or otherwise obtained through the use of the Services. The use of the Services is at your own discretion and risk and you will be held solely responsible for any damage to your computer system, loss of data, or any other loss that results from downloading or using any such material.</p>\r\n\r\n<p>Under no circumstances shall WikiRumours be liable for any direct, indirect, incidental, special, consequential, exemplary, or other damages whatsoever, including without limitation any damages that result from (1) your use or inability to use the Services or your liability relating to the use of the Services, (2) the cost of procurement of substitute goods, data, information or services, (3) errors, mistakes, or inaccuracies in the materials provided through the Services, (4) personal injury or property damage of any kind whatsoever arising from or relating to your use of the Services, any bugs, viruses, Trojan horses, or any other files or data that may be harmful to computer or communication equipment or data that may have been transmitted to or through the Services, or (5) any errors or omissions in any material provided through the Services or any other loss or damage of any kind arising from or relating to your use of the Services.</p>\r\n\r\n<p><b>Indemnity.</b> You shall indemnify and hold WikiRumours harmless from all claims, actions, proceedings, demands, damages, losses, costs, and expenses (including legal fees) incurred in connection with any user content submitted, posted, transmitted or made available through the Services and/or any violation by you of these Terms of Use, and any violation by you of any rights of another (including, without limitation, all intellectual property rights and rights of publicity, personality, or privacy). In no event shall our liability exceed one hundred US dollars (USD 100.00) in aggregate. In the case that applicable law may not allow the limitation or exclusion of liability or incidental or consequential damages, the above limitation or exclusion may not apply to you, although our liability will be limited to the fullest extent permitted by applicable law.</p>\r\n	\r\n<p><b>Law & Jurisdiction.</b> The Terms of Use stated herein shall be governed and construed in accordance with Canadian law. Any disputes relating to these Terms of Use shall be subject toe the exclusive jurisdiction of the courts of Canada and venue shall be proper where WikiRumours main office is located. Users are advised that they may be bound by the laws of the countries where they reside.</p>\r\n\r\n<p><b>Severability.</b> If any provision or part of a provision of these Terms of Use is found unlawful, void, or unenforceable, that provision or part of the provision is deemed severable from these Terms of Use and will be enforced to the maximum extent permissible, and all other provisions of these Terms of Use will remain in full force and effect.</p>\r\n	\r\n<p><b>Changes.</b> We reserve the right to change these Terms of Use at any time. The date on which the latest update was made is indicated at the bottom of this page.  We recommend that you revisit this page from time to time to ensure that you are aware of any changes.  Your continued use of the Services signifies your acceptance of any changes.</p>\r\n\r\n<p><b>Termination.</b> We reserve the right to terminate this agreement at any time. You (user) may terminate this agreement by seizing use of the website at anytime. We shall terminate this agreement without notice if in its our sole judgment, you breach by violating any of our terms of conditions. </p>\r\n	\r\n<p><b>Integration / Entire Agreement.</b> The Terms of Use stated herein constitutes the entire agreement between you (the user) and WikiRumours. These Terms of Use do not create an employment, agency, partnership, or joint venture relationship between you and us. If you have not signed a separate agreement with us, these Terms of Use are the entire agreement between you and us. If there is any conflict between these Terms of Use and a signed written agreement between you and us, the signed agreement will control. You agree that we may provide you with notices, including those regarding changes to the Terms of Use, by email, regular mail, or postings on this website. If in any circumstance, we do not apply or enforce any provision of these Terms of Use, it is not a waiver of that provision.</p>\r\n\r\n<p>You acknowledge and agree that, unless otherwise agreed to in writing by us, you have no expectation of compensation for any activity, contribution, or idea that you provide to us.</p>\r\n	\r\n<p>Last updated 6 January 2014.</p>', '', '', 'p', '2014-01-06 11:23:34', 1),
(8, 'download', 'Download the Software', '<h2>Download the software</h2>\r\n\r\n<p>Coming soon...</p>', '', '', 'p', '2013-12-17 13:55:47', 1),
(9, 'api_intro', '', '<p>The WikiRumours API allows authorized users to retrieve WikiRumours data asynchronously for use in their own applications. The API is a continual work in progress, so as new vesions of the API become available, old versions will be retired (after a suitable transition period). If you notice that your API calls have stopped returning data, please check the returned status messages to ensure your API URL hasn''t become deprecated. Still having problems? <a href=''/faqs''>Check our FAQs.</a></p>\r\n\r\n<p>In general, sub-incremental versions are forwards-compatible (e.g. v1.2 will accept the same parameters as v1.1), but incremental versions will not (e.g. v2 will require different configuration than v1).</p>\r\n\r\n<p>At this time, the WikiRumours API is read-only.</p>\r\n	\r\n<p>Accessing the WikiRumours API requires a private, unique, user-specific access key. You can obtain a key through your <a href=''/obtain_api_key''>profile / account settings</a>. <strong>Please keep this key secure</strong> (e.g. do not add it to browser-viewable code).</p>\r\n\r\n<p>A query limit is enforced on the WikiRumours API to ensure appropriate access by all users. For special applications, please <a href=''/contact''>let us know</a>.</p>', '', '', 'b', '2013-12-17 16:01:37', 1),
(10, 'api_v1', '', '<!-- v1.0 -->\r\n				\r\n<h4>Summary of Changes</h4>\r\n				\r\n<ul>\r\n<li><span>None</span></li>\r\n</ul>\r\n				\r\n<br />\r\n<h4>Input Parameters</h4>\r\n				\r\n<p>The high-level schema for the WikiRumours API is as follows:</p>\r\n				\r\n<pre>http://api.wikirumours.org/version/key/query-type/output/encoded-filters</pre>\r\n\r\n<br />\r\n<table class="table table-hover table-condensed">\r\n<tr>\r\n<th>URL Segment</th>\r\n<th>Format</th>\r\n<th>Options</th>\r\n</tr>\r\n<tr>\r\n<td>version</td>\r\n<td>v{increment}-{sub-increment}</td>\r\n<td></td>\r\n</tr>\r\n<tr>\r\n<td>key</td>\r\n<td>{32-digit key}</td>\r\n<td></td>\r\n</tr>\r\n<tr>\r\n<td>query type</td>\r\n<td>{query type}</td>\r\n<td>rumours</td>\r\n</tr>\r\n<tr>\r\n<td>output</td>\r\n<td>{output}</td>\r\n<td>xml; json</td>\r\n</tr>\r\n<tr>\r\n<td>(encoded) filters</td>\r\n<td>{key}%3D{value}%7C{key}%3D{value}</td>\r\n<td>(see filter table below)</td>\r\n</tr>\r\n</table>\r\n\r\n<p>Filters are key/value pairs which correspond to output parameters. So for instance, to retrieve all sightings of newly added Kenyan rumours, the filter segment would properly read <b>country%3DKE%7Cstatus%3DNU</b>.</p>\r\n\r\n<br />\r\n<table class="table table-hover table-condensed">\r\n<tr>\r\n<th>Filter</th>\r\n<th>Applicable query</th>\r\n<th>Format</th>\r\n<th>Useful for</th>\r\n</tr>\r\n<tr>\r\n<td>public_id</td>\r\n<td>rumours</td>\r\n<td>URL-encoded plaintext</td>\r\n<td>locating a specific rumour</td>\r\n</tr>\r\n<tr>\r\n<td>keywords</td>\r\n<td>rumours</td>\r\n<td>URL-encoded plaintext</td>\r\n<td>locating several rumours without having specific structured criteria</td>\r\n</tr>\r\n<tr>\r\n<td>country</td>\r\n<td>rumours</td>\r\n<td>2-character <a href=''https://www.iso.org/obp/ui/#search'' target=''_blank''>ISO 3166-2</a> code</td>\r\n<td></td>\r\n</tr>\r\n<tr>\r\n<td>region</td>\r\n<td>rumours</td>\r\n<td>URL-encoded plaintext</td>\r\n<td></td>\r\n</tr>\r\n<tr>\r\n<td>status</td>\r\n<td>rumours</td>\r\n<td>See status codes</td>\r\n<td></td>\r\n</tr>\r\n<tr>\r\n<td>page</td>\r\n<td>rumours</td>\r\n<td>any integer</td>\r\n<td>paginating through a large result</td>\r\n</tr>\r\n</table>\r\n				\r\n<br />\r\n<h4>Output Parameters</h4>\r\n				\r\n<p>For illustrative purposes, output has been displayed below as XML.</p>\r\n				\r\n<pre>\r\n[wikirumours]\r\n  [version]VARCHAR(6)[/status]\r\n  [status]TEXT[/status]\r\n  [page]TINYINT(3)[/page]\r\n  [number_of_results]INT[/number_of_results]\r\n  [number_of_results_on_this_page]INT[/number_of_results_on_this_page]\r\n  [warnings]\r\n    [warning_code]INT(4)[/warning_code]\r\n    [human_readable_warning]VARCHAR(255)[/human_readable_warning]\r\n  [/warnings]\r\n  [errors]\r\n    [error_code]INT(4)[/error_code]\r\n    [human_readable_error]VARCHAR(255)[/human_readable_error]\r\n  [/errors]\r\n  [number_of_queries_today]INT[/number_of_queries_today]\r\n  [data]\r\n\r\n    {data structure is dependent on requested query type}\r\n\r\n  [/data]\r\n[/wikirumours]\r\n</pre>\r\n\r\n<p>Rumour data is presented with this structure:</p>\r\n\r\n<pre>\r\n    [datapoint]\r\n        [rumour_id]VARCHAR(6)[/rumour_id]\r\n        [rumour]TEXT[/rumour]\r\n        [country_abbreviation]CHAR(2)[/country_abbreviation]\r\n        [country]VARCHAR(255)[/country]\r\n        [region]VARCHAR(255)[/region]\r\n        [occurred_on]DATETIME[/occurred_on]\r\n        [status_abbreviation]CHAR(2)[/status_abbreviation]\r\n        [status]VARCHAR(255)[/status]\r\n        [findings]TEXT[/findings]\r\n        [number_of_sightings]INT[/number_of_sightings]\r\n    [/datapoint]\r\n</pre>\r\n\r\n<br />\r\n<h4>Error Handling</h4>\r\n				\r\n<p>Incorrectly configured input parameters will result in an error being returned with the data. If the proper output format parameter cannot be determined, the default output for error messages is XML.</p>', '', '', 'b', '2013-12-17 16:03:51', 1),
(11, 'roadmap', 'Product Roadmap', '<h2>Roadmap</h2>\r\n\r\n<p>Here''s what we''ve got in the works for future development.</p>\r\n\r\n<div class=''container''>\r\n<div class=''row''>\r\n<div class=''col-md-4 col-xs-12''>\r\n<h3>In development</h3>\r\n<ul>\r\n<li><span>...</span></li>\r\n</ul>\r\n</div>\r\n<div class=''col-md-4 col-xs-12''>\r\n<h3>Queued</h3>\r\n<ul>\r\n<li><span>...</span></li>\r\n</ul>\r\n</div>\r\n<div class=''col-md-4 col-xs-12''>\r\n<h3>Under consideration</h3>\r\n<ul>\r\n<li><span>Country-based restrictions on registration and/or rumour reporting</span></li>\r\n</ul>\r\n</div>\r\n</div>\r\n</div>', '', '', 'p', '2013-12-17 20:20:33', 1);

-- --------------------------------------------------------

--
-- Table structure for table `wr_comments`
--

CREATE TABLE IF NOT EXISTS `wr_comments` (
  `comment_id` int(9) NOT NULL AUTO_INCREMENT,
  `rumour_id` int(9) NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(9) NOT NULL,
  `created_on` datetime NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `wr_comments`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_comment_flags`
--

CREATE TABLE IF NOT EXISTS `wr_comment_flags` (
  `comment_id` int(9) NOT NULL,
  `flagged_by` int(9) NOT NULL,
  `flagged_on` datetime NOT NULL,
  PRIMARY KEY (`comment_id`,`flagged_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `wr_comment_flags`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_faqs`
--

CREATE TABLE IF NOT EXISTS `wr_faqs` (
  `faq_id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `chapter_id` int(3) NOT NULL,
  `question` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `answer` text COLLATE utf8_unicode_ci NOT NULL,
  `faq_position` int(3) NOT NULL,
  PRIMARY KEY (`faq_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `wr_faqs`
--

INSERT INTO `wr_faqs` (`faq_id`, `chapter_id`, `question`, `answer`, `faq_position`) VALUES
(1, 1, 'I''ve registered but didn''t receive an authorizing email.', 'Email from the website may take several minutes to arrive. If you haven''t received an email after ten minutes, please check your junk mail folder. If you still haven''t received an authorizing email, please try registering again.', 1),
(2, 1, 'The registration link I received by email doesn''t take me anywhere.', 'If the link is broken, try copying it manually into your browser''s address bar.', 2),
(3, 2, 'I''m getting page not found errors', 'Make sure you''ve enabled ModRewrite on your server so that the software knows how to map URLs against files', 1);

-- --------------------------------------------------------

--
-- Table structure for table `wr_faq_chapters`
--

CREATE TABLE IF NOT EXISTS `wr_faq_chapters` (
  `chapter_id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `chapter_position` tinyint(3) NOT NULL,
  PRIMARY KEY (`chapter_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `wr_faq_chapters`
--

INSERT INTO `wr_faq_chapters` (`chapter_id`, `name`, `chapter_position`) VALUES
(1, 'Registration & Login', 1),
(2, 'Installing the downloadable software', 2);

-- --------------------------------------------------------

--
-- Table structure for table `wr_logs`
--

CREATE TABLE IF NOT EXISTS `wr_logs` (
  `log_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `connected_on` datetime NOT NULL,
  `connection_type` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `task_counter` int(2) NOT NULL,
  `activity` text COLLATE utf8_unicode_ci NOT NULL,
  `error_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `error` tinyint(1) NOT NULL,
  `resolved` tinyint(1) NOT NULL DEFAULT '1',
  `connection_released` tinyint(1) NOT NULL DEFAULT '1',
  `connection_length_in_seconds` int(3) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15053 ;

--
-- Dumping data for table `wr_logs`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_notifications`
--

CREATE TABLE IF NOT EXISTS `wr_notifications` (
  `notification_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `new_registrations` tinyint(1) NOT NULL,
  `contact_form` tinyint(1) NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `wr_notifications`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_preferences`
--

CREATE TABLE IF NOT EXISTS `wr_preferences` (
  `preference` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(9) NOT NULL,
  `value` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`preference`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `wr_preferences`
--

INSERT INTO `wr_preferences` (`preference`, `user_id`, `value`) VALUES
('appDescription', 0, 'Countering misinformation in Kenya''s Tana Delta'),
('appName', 0, 'Una Hakika?');

-- --------------------------------------------------------

--
-- Table structure for table `wr_registrations`
--

CREATE TABLE IF NOT EXISTS `wr_registrations` (
  `registration_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `secondary_phone` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `sms_notifications` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `province_state` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `other_province_state` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `region` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `registration_key` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `registered_on` datetime NOT NULL,
  `referred_by` int(9) NOT NULL,
  PRIMARY KEY (`registration_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `wr_registrations`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_rumours`
--

CREATE TABLE IF NOT EXISTS `wr_rumours` (
  `rumour_id` int(9) NOT NULL AUTO_INCREMENT,
  `public_id` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `region` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `occurred_on` date NOT NULL,
  `created_by` int(9) NOT NULL,
  `entered_by` int(9) NOT NULL,
  `created_on` datetime NOT NULL,
  `updated_on` datetime NOT NULL,
  `updated_by` int(9) NOT NULL,
  `status` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `findings` text COLLATE utf8_unicode_ci NOT NULL,
  `assigned_to` int(9) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`rumour_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `wr_rumours`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_rumours_x_tags`
--

CREATE TABLE IF NOT EXISTS `wr_rumours_x_tags` (
  `rumour_id` int(9) NOT NULL,
  `tag_id` int(9) NOT NULL,
  `added_by` int(9) NOT NULL,
  `added_on` datetime NOT NULL,
  PRIMARY KEY (`rumour_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `wr_rumours_x_tags`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_rumour_sightings`
--

CREATE TABLE IF NOT EXISTS `wr_rumour_sightings` (
  `created_by` int(9) NOT NULL,
  `rumour_id` int(9) NOT NULL,
  `entered_by` int(9) NOT NULL,
  `entered_on` datetime NOT NULL,
  `heard_on` date NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `region` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `source` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `ipv4` int(10) NOT NULL,
  `ipv6` binary(16) NOT NULL,
  PRIMARY KEY (`created_by`,`rumour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `wr_rumour_sightings`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_tags`
--

CREATE TABLE IF NOT EXISTS `wr_tags` (
  `tag_id` int(9) NOT NULL AUTO_INCREMENT,
  `tag` varchar(75) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(9) NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `wr_tags`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_users`
--

CREATE TABLE IF NOT EXISTS `wr_users` (
  `user_id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `secondary_phone` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `sms_notifications` varchar(12) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `province_state` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `other_province_state` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `region` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `is_proxy` tinyint(1) NOT NULL,
  `is_tester` tinyint(1) NOT NULL,
  `is_moderator` tinyint(1) NOT NULL,
  `is_community_liaison` tinyint(1) NOT NULL,
  `is_administrator` tinyint(1) NOT NULL,
  `registered_on` datetime NOT NULL,
  `registered_by` int(9) NOT NULL,
  `referred_by` int(9) NOT NULL,
  `last_login` datetime NOT NULL,
  `ok_to_contact` tinyint(1) NOT NULL,
  `ok_to_show_profile` tinyint(1) NOT NULL DEFAULT '1',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;

--
-- Dumping data for table `wr_users`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_user_keys`
--

CREATE TABLE IF NOT EXISTS `wr_user_keys` (
  `user_id` int(9) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `saved_on` datetime NOT NULL,
  `expiry` datetime NOT NULL,
  PRIMARY KEY (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `wr_user_keys`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_user_permissions`
--

CREATE TABLE IF NOT EXISTS `wr_user_permissions` (
  `user_id` int(9) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` tinyint(1) NOT NULL,
  `can_edit_content` tinyint(1) NOT NULL,
  `can_edit_settings` tinyint(1) NOT NULL,
  `can_edit_users` tinyint(1) NOT NULL,
  `can_send_email` tinyint(1) NOT NULL,
  `can_run_housekeeping` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `wr_user_permissions`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_user_terminations`
--

CREATE TABLE IF NOT EXISTS `wr_user_terminations` (
  `user_id` int(9) NOT NULL,
  `disabled_on` datetime NOT NULL,
  `disabled_by` int(9) NOT NULL,
  `reason` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `wr_user_terminations`
--


-- --------------------------------------------------------

--
-- Table structure for table `wr_watchlist`
--

CREATE TABLE IF NOT EXISTS `wr_watchlist` (
  `created_by` int(9) NOT NULL,
  `rumour_id` int(9) NOT NULL,
  `created_on` datetime NOT NULL,
  `notify_of_updates` tinyint(1) NOT NULL,
  PRIMARY KEY (`created_by`,`rumour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `wr_watchlist`
--

