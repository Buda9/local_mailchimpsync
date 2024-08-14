<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for the MailChimp Sync plugin.
 *
 * @package    local_mailchimpsync
 * @copyright  2024 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'MailChimp Sync';
$string['settings'] = 'MailChimp Sync Settings';
$string['task_sync_users'] = 'Synchronize users with MailChimp';
$string['apikey'] = 'MailChimp API Key';
$string['apikey_desc'] = 'Enter your MailChimp API key';
$string['default_list_id'] = 'Default MailChimp List ID';
$string['default_list_id_desc'] = 'The MailChimp list ID to use for users not in any mapped cohort';
$string['cohort_list_mapping'] = 'Cohort to MailChimp list mapping';
$string['cohort_list_mapping_desc'] = 'Configure which MailChimp list each cohort should be synced to';
$string['field_mapping'] = 'User field to MailChimp merge field mapping';
$string['field_mapping_desc'] = 'Configure how Moodle user fields map to MailChimp merge fields';
$string['sync_cohort'] = 'Sync cohort';
$string['sync_all_users'] = 'Sync all users';
$string['sync_success'] = 'Synchronization completed successfully';
$string['sync_error'] = 'Error occurred during synchronization';
$string['privacy:metadata:local_mailchimpsync_log'] = 'Information about MailChimp synchronization actions for users';
$string['privacy:metadata:local_mailchimpsync_log:userid'] = 'The ID of the user';
$string['privacy:metadata:local_mailchimpsync_log:listid'] = 'The ID of the MailChimp list';
$string['privacy:metadata:local_mailchimpsync_log:action'] = 'The action performed (subscribed/unsubscribed)';
$string['privacy:metadata:local_mailchimpsync_log:timecreated'] = 'The time when the action was performed';
$string['privacy:metadata:mailchimp'] = 'User information sent to MailChimp';
$string['privacy:metadata:mailchimp:email'] = 'User\'s email address';
$string['privacy:metadata:mailchimp:firstname'] = 'User\'s first name';
$string['privacy:metadata:mailchimp:lastname'] = 'User\'s last name';

$string['cohortlistmapping'] = 'Cohort to MailChimp list mapping';
$string['cohortlistmappingdesc'] = 'Configure which MailChimp list each cohort should be synced to';
$string['managecohortmapping'] = 'Manage cohort to MailChimp list mapping';
$string['cohortmappingupdated'] = 'Cohort to MailChimp list mapping updated successfully';
$string['mailchimplist'] = 'MailChimp list';
$string['savemapping'] = 'Save mapping';
$string['nolistselected'] = 'No list selected';
$string['cohortmappingaccessdenied'] = 'You do not have permission to manage cohort to MailChimp list mapping';
$string['syncstatus'] = 'Sync status';
$string['lastsynced'] = 'Last synced';
$string['neverssynced'] = 'Never synced';
$string['cohortssyncsummary'] = 'Cohorts sync summary';

$string['user_filter'] = 'User filter';
$string['user_filter_desc'] = 'User filter desc...';
$string['sync_page_title'] = 'MailChimp Manual Sync';
$string['sync_page_heading'] = 'MailChimp Manual Synchronization';
$string['sync_all_users'] = 'Sync All Users';
$string['sync_all_completed'] = 'Synchronization of all users completed';
$string['recent_logs'] = 'Recent Synchronization Logs';
$string['list'] = 'MailChimp List';
$string['recent_logs'] = 'Recent logs';

$string['cohort_list_mapping'] = 'Cohort to MailChimp List Mapping';
$string['cohort_list_mapping_desc'] = 'Select which cohorts should be mapped to MailChimp lists';
$string['cohort_list'] = 'MailChimp List for Cohort {$a}';
$string['cohort_list_desc'] = 'Select the MailChimp list for this cohort';
$string['recent_logs'] = 'Recent Synchronization Logs';
$string['sync_all_completed'] = 'Synchronization of all users completed';

$string['mailchimpnotifications'] = 'MailChimp Notifications';
$string['optoutmailchimp'] = 'Opt out of MailChimp notifications';
$string['optoutdesc'] = 'Opt out of MailChimp notifications?';

$string['mailchimpnotifications'] = 'MailChimp Notifications';
$string['use_sync_field'] = 'Use sync field';
$string['use_sync_field_desc'] = 'Enable this to use a custom profile field to indicate MailChimp sync status (if the user is synced or not)';
$string['sync_field'] = 'Sync field';
$string['sync_field_desc'] = 'Select the custom profile field to use for MailChimp user sync status (first, create toggle user custom field)';
$string['stats'] = 'MailChimp Sync Statistics';
$string['privacy_policy_page_title'] = 'Privacy Policy Page';
$string['privacy_policy_page_heading'] = 'Privacy Policy Page';
$string['stats_page_title'] = 'MailChimp Sync Stats';
$string['stats_page_heading'] = 'MailChimp Sync Statistics';
$string['sync_statistics'] = 'Sync Statistics';
$string['total_synced_users'] = 'Total synced users: {$a}';
$string['total_moodle_users'] = 'Total Moodle users: {$a}';
$string['sync_percentage'] = 'Sync percentage: {$a}%';
$string['recent_sync_logs'] = 'Recent Sync Logs';
$string['action'] = 'Action';
$string['status'] = 'Status';
$string['time'] = 'Time';
$string['sync_notification_subject'] = 'MailChimp Sync Notification';
$string['sync_notification_message'] = 'Your account has been synchronized with MailChimp.';
$string['sync_notification_small'] = 'MailChimp sync completed';
$string['privacy:metadata:local_mailchimpsync_users'] = 'Stores synchronization status for users';
$string['privacy:metadata:local_mailchimpsync_users:userid'] = 'The ID of the user';
$string['privacy:metadata:local_mailchimpsync_users:timemodified'] = 'The time when the synchronization was last modified';
$string['privacy:metadata:local_mailchimpsync_log'] = 'Logs of MailChimp sync operations';
$string['privacy:metadata:local_mailchimpsync_log:userid'] = 'The ID of the user';
$string['privacy:metadata:local_mailchimpsync_log:listid'] = 'The ID of the MailChimp list';
$string['privacy:metadata:local_mailchimpsync_log:action'] = 'The action performed';
$string['privacy:metadata:local_mailchimpsync_log:status'] = 'The status of the action';
$string['privacy:metadata:local_mailchimpsync_log:message'] = 'Any message associated with the action';
$string['privacy:metadata:local_mailchimpsync_log:timecreated'] = 'The time when the log was created';

$string['privacy_policy'] = 'MailChimp Sync Privacy Policy';
$string['privacy_policy_content'] = 'The MailChimp Sync plugin is designed to synchronize user data between Moodle and MailChimp. This policy outlines how we collect, use, and protect your data.';

$string['datashared'] = 'Data Shared with MailChimp';
$string['datashared_desc'] = 'The following user data may be shared with MailChimp:
<ul>
<li>First Name</li>
<li>Last Name</li>
<li>Email Address</li>
<li>User ID (for internal tracking)</li>
<li>Any additional fields configured by the site administrator</li>
</ul>
This data is used to maintain your subscription status and personalize your MailChimp experience.';

$string['dataretention'] = 'Data Retention';
$string['dataretention_desc'] = 'Your data is retained in our Moodle database for as long as you have an active account. When you delete your Moodle account, your synchronization data will be removed from our system. However, please note that MailChimp may retain your data according to their own privacy policy.';

$string['datarights'] = 'Your Data Rights';
$string['datarights_desc'] = 'You have the right to:
<ul>
<li>View the data we hold about you</li>
<li>Request corrections to your data</li>
<li>Opt-out of MailChimp synchronization</li>
<li>Request deletion of your data from our systems</li>
</ul>
To exercise these rights, please contact the site administrator.';

$string['sync_success'] = 'User synchronization with MailChimp was successful.';
$string['sync_failure'] = 'There was an error during user synchronization with MailChimp. Please check the logs for more details.';