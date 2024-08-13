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
$string['sync_page_title'] = 'MailChimp Sync';
$string['sync_page_heading'] = 'MailChimp Synchronization';
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