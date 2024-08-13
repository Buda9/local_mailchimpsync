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
    
        mtrace("Starting MailChimp synchronization...");
    
        $cohort_list_mapping = $this->get_cohort_list_mapping();
        $default_list_id = get_config('local_mailchimpsync', 'default_list_id');
    
        if (empty($cohort_list_mapping) && empty($default_list_id)) {
            mtrace("Error: No cohort mapping or default list configured. Please check your settings.");
            return;
        }
    
        mtrace("Default list ID: " . $default_list_id);
        mtrace("Cohort list mapping: " . print_r($cohort_list_mapping, true));
    
        $total_users = $DB->count_records('user', ['deleted' => 0, 'suspended' => 0]);
        $total_batches = ceil($total_users / $this->batch_size);
    
        for ($batch = 0; $batch < $total_batches; $batch++) {
            $users = $DB->get_records('user', ['deleted' => 0, 'suspended' => 0], 'id', '*', $batch * $this->batch_size, $this->batch_size);
            $this->sync_user_batch($users, $cohort_list_mapping, $default_list_id);
        }
    
        mtrace("MailChimp synchronization completed.");
    }

    private function sync_user_batch($users, $cohort_list_mapping, $default_list_id) {
        global $DB;
    
        foreach ($users as $user) {
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
                    $this->sync_user_to_list($user, $list_id);
                    $synced = true;
                }
            }
    
            if (!$synced && $default_list_id) {
                $this->sync_user_to_list($user, $default_list_id);
            }
        }
    }

    private function sync_user_to_list($user, $list_id) {
        if (!$this->is_valid_email($user->email)) {
            mtrace("Skipping user {$user->id} due to invalid email: {$user->email}");
            $this->log_sync($user->id, $list_id, 'skipped', 'error', 'Invalid email address');
            return;
        }
    
        $merge_fields = $this->get_user_merge_fields($user);
    
        try {
            $result = $this->api->add_or_update_list_member($list_id, $user->email, $merge_fields);
            mtrace("User {$user->id} synced to list {$list_id}");
            $this->log_sync($user->id, $list_id, 'synced');
        } catch (\moodle_exception $e) {
            mtrace("Error syncing user {$user->id} to list {$list_id}: " . $e->getMessage());
            $this->log_sync($user->id, $list_id, 'failed', 'error', $e->getMessage());
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
    
        mtrace("Merge fields for user {$user->id}: " . print_r($merge_fields, true));
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

}