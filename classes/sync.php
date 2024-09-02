<?php
namespace local_mailchimpsync;

defined('MOODLE_INTERNAL') || die();

class sync {
    private $api;
    private $batch_size = 500;

    public function __construct() {
        $this->api = new api();
    }

    public function sync_all_users() {
        global $DB;

        //mtrace("Starting MailChimp synchronization...");

        $cohort_list_mapping = $this->get_cohort_list_mapping();
        $default_list_id = get_config('local_mailchimpsync', 'default_list_id');

        if (empty($cohort_list_mapping) && empty($default_list_id)) {
            //mtrace("Error: No cohort mapping or default list configured. Please check your settings.");
            return;
        }

        //mtrace("Default list ID: " . $default_list_id);
        //mtrace("Cohort list mapping: " . print_r($cohort_list_mapping, true));

        // Get the timestamp of the last sync
        $last_sync_time = get_config('local_mailchimpsync', 'last_sync_time');
        if (empty($last_sync_time)) {
            $last_sync_time = 0;
        }

        $current_time = time();

        // Get users modified since last sync
        $users = $DB->get_records_select('user', 
            "deleted = 0 AND suspended = 0 AND timemodified > ?", 
            array($last_sync_time), 
            'id',
            '*',
            0,
            $this->batch_size
        );

        $synced_count = 0;
        $error_count = 0;

        foreach ($users as $user) {
            if ($this->sync_user($user, $cohort_list_mapping, $default_list_id)) {
                $synced_count++;
            } else {
                $error_count++;
            }
        }

        // Update last sync time
        set_config('last_sync_time', $current_time, 'local_mailchimpsync');

        //mtrace("MailChimp synchronization completed. Synced: $synced_count, Errors: $error_count");

        return array('synced' => $synced_count, 'errors' => $error_count);
    }

    private function sync_user_batch($users) {
        foreach ($users as $user) {
            $this->sync_single_user($user);
        }
    }

    private function sync_user($user, $cohort_list_mapping, $default_list_id) {
        global $DB;

        $user_cohorts = $DB->get_records_sql(
            "SELECT c.id, c.name
             FROM {cohort} c
             JOIN {cohort_members} cm ON c.id = cm.cohortid
             WHERE cm.userid = ?",
            array($user->id)
        );

        $synced = false;
        foreach ($user_cohorts as $cohort) {
            if (isset($cohort_list_mapping[$cohort->id])) {
                $list_id = $cohort_list_mapping[$cohort->id];
                if ($this->sync_user_to_list($user, $list_id)) {
                    $synced = true;
                }
            }
        }

        if (!$synced && $default_list_id) {
            $synced = $this->sync_user_to_list($user, $default_list_id);
        }

        return $synced;
    }

        private function sync_user_to_list($user, $list_id) {
            if (!$this->is_valid_email($user->email)) {
                //mtrace("Skipping user {$user->id} due to invalid email: {$user->email}");
                $this->log_sync($user->id, $list_id, 'skipped', 'error', 'Invalid email address');
                return false;
            }

            $merge_fields = $this->get_user_merge_fields($user);

            try {
                $result = $this->api->add_or_update_list_member($list_id, $user->email, $merge_fields);
                //mtrace("User {$user->id} synced to list {$list_id}");
                $this->log_sync($user->id, $list_id, 'synced');
                $this->update_sync_field($user->id, true);
                return true;
            } catch (\moodle_exception $e) {
                //mtrace("Error syncing user {$user->id} to list {$list_id}: " . $e->getMessage());
                $this->log_sync($user->id, $list_id, 'failed', 'error', $e->getMessage());
                $this->update_sync_field($user->id, false);
                return false;
            }
        }

    private function get_cohort_list_mapping() {
        $mapping = array();
        $cohorts = get_config('local_mailchimpsync', 'cohort_list_mapping');
        if ($cohorts) {
            $cohorts = json_decode($cohorts, true);
            if (is_array($cohorts)) {
                foreach ($cohorts as $cohort_id => $enabled) {
                    if ($enabled) {
                        $list_id = get_config('local_mailchimpsync', "cohort_{$cohort_id}_list");
                        if ($list_id) {
                            $mapping[$cohort_id] = $list_id;
                        }
                    }
                }
            }
        }
        return $mapping;
    }

    private function is_valid_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function get_user_merge_fields($user) {
        $merge_fields = new \stdClass();
        $field_mapping = get_config('local_mailchimpsync', 'field_mapping');

        if ($field_mapping) {
            $field_mapping = json_decode($field_mapping, true);
            if (is_array($field_mapping)) {
                foreach ($field_mapping as $moodle_field => $mailchimp_field) {
                    if (isset($user->$moodle_field)) {
                        $merge_fields->$mailchimp_field = $user->$moodle_field;
                    }
                }
            }
        }

        // Ensure FNAME and LNAME are always set
        if (!isset($merge_fields->FNAME)) $merge_fields->FNAME = $user->firstname;
        if (!isset($merge_fields->LNAME)) $merge_fields->LNAME = $user->lastname;

        //mtrace("Merge fields for user {$user->id}: " . print_r($merge_fields, true));
        return $merge_fields;
    }

    private function get_profile_field_value($userid, $field_shortname) {
        global $DB;
        $sql = "SELECT d.data
                FROM {user_info_data} d
                JOIN {user_info_field} f ON d.fieldid = f.id
                WHERE d.userid = :userid AND f.shortname = :shortname";
        return $DB->get_field_sql($sql, ['userid' => $userid, 'shortname' => $field_shortname]);
    }

    private function log_sync($user_id, $list_id, $action, $status = 'success', $message = '') {
        global $DB;

        $log = new \stdClass();
        $log->userid = $user_id;
        $log->listid = $list_id;
        $log->action = $action;
        $log->status = $status;
        $log->message = $message;
        $log->timecreated = time();

        $DB->insert_record('local_mailchimpsync_log', $log);
    }

    private function update_sync_record($user_id) {
        global $DB;

        $record = $DB->get_record('local_mailchimpsync_users', array('userid' => $user_id));

        if ($record) {
            $record->timemodified = time();
            $DB->update_record('local_mailchimpsync_users', $record);
        } else {
            $record = new \stdClass();
            $record->userid = $user_id;
            $record->timemodified = time();
            $DB->insert_record('local_mailchimpsync_users', $record);
        }
    }

    private function update_sync_time($user_id) {
        global $DB;
        $record = $DB->get_record('local_mailchimpsync_users', ['userid' => $user_id]);
        if ($record) {
            $record->timemodified = time();
            $DB->update_record('local_mailchimpsync_users', $record);
        } else {
            $record = new \stdClass();
            $record->userid = $user_id;
            $record->timemodified = time();
            $DB->insert_record('local_mailchimpsync_users', $record);
        }
    }

    private function update_sync_field($user_id, $is_synced) {
        global $DB;

        $use_sync_field = get_config('local_mailchimpsync', 'use_sync_field');
        $sync_field_id = get_config('local_mailchimpsync', 'sync_field');

        if ($use_sync_field && $sync_field_id) {
            $field_data = $DB->get_record('user_info_data', ['userid' => $user_id, 'fieldid' => $sync_field_id]);

            if ($field_data) {
                $field_data->data = $is_synced ? 1 : 0;
                $DB->update_record('user_info_data', $field_data);
            } else {
                $field_data = new \stdClass();
                $field_data->userid = $user_id;
                $field_data->fieldid = $sync_field_id;
                $field_data->data = $is_synced ? 1 : 0;
                $DB->insert_record('user_info_data', $field_data);
            }
        }
    }

    private function remove_user_from_mailchimp($user) {
        $api = new \local_mailchimpsync\api();
        $default_list_id = get_config('local_mailchimpsync', 'default_list_id');
        $cohort_list_mapping = $this->get_cohort_list_mapping();

        $lists_to_check = array_unique(array_merge([$default_list_id], array_values($cohort_list_mapping)));

        $removed = false;
        foreach ($lists_to_check as $list_id) {
            try {
                $api->delete_list_member($list_id, $user->email);
                //mtrace("Removed user {$user->id} from MailChimp list {$list_id}");
                $removed = true;
            } catch (\moodle_exception $e) {
                //mtrace("Error removing user {$user->id} from MailChimp list {$list_id}: " . $e->getMessage());
            }
        }

        // After removal:
        global $DB;
        $DB->delete_records('local_mailchimpsync_users', ['userid' => $user->userid]);

        // Update sync field
        $this->update_sync_field($user->id, false);
    }

    public function sync_single_user($user) {
        $cohort_list_mapping = $this->get_cohort_list_mapping();
        $default_list_id = get_config('local_mailchimpsync', 'default_list_id');

        $user_cohorts = $this->get_user_cohorts($user->id);
        $synced = false;

        foreach ($user_cohorts as $cohort) {
            if (isset($cohort_list_mapping[$cohort->id])) {
                $list_id = $cohort_list_mapping[$cohort->id];
                $this->sync_user_to_list($user, $list_id);
                $synced = true;
            }
        }

        if (!$synced && $default_list_id) {
            $this->sync_user_to_list($user, $default_list_id);
        }
    }

    private function get_user_cohorts($user_id) {
        global $DB;
        return $DB->get_records_sql(
            "SELECT c.id, c.name
             FROM {cohort} c
             JOIN {cohort_members} cm ON c.id = cm.cohortid
             WHERE cm.userid = ?",
            array($user_id)
        );
    }
}