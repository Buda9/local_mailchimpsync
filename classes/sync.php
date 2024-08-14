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
    
        $users = $DB->get_records_sql(
            "SELECT u.*, m.timemodified as mailchimp_sync_time
             FROM {user} u
             LEFT JOIN {local_mailchimpsync_users} m ON u.id = m.userid
             WHERE u.deleted = 0 AND u.suspended = 0 AND 
             (m.timemodified IS NULL OR u.timemodified > m.timemodified)"
        );
    
        foreach ($users as $user) {
            $this->sync_single_user($user);
        }
    
        // Check for deleted users
        $deleted_users = $DB->get_records_sql(
            "SELECT m.* 
             FROM {local_mailchimpsync_users} m
             LEFT JOIN {user} u ON m.userid = u.id
             WHERE u.id IS NULL OR u.deleted = 1 OR u.suspended = 1"
        );
    
        foreach ($deleted_users as $deleted_user) {
            $this->remove_user_from_mailchimp($deleted_user);
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
            $this->update_sync_field($user->id, false);
            return;
        }
    
        $merge_fields = $this->get_user_merge_fields($user);
    
        try {
            $result = $this->api->add_or_update_list_member($list_id, $user->email, $merge_fields);
            mtrace("User {$user->id} synced to list {$list_id}");
            $this->log_sync($user->id, $list_id, 'synced');
            $this->update_sync_field($user->id, true);
        } catch (\moodle_exception $e) {
            mtrace("Error syncing user {$user->id} to list {$list_id}: " . $e->getMessage());
            $this->log_sync($user->id, $list_id, 'failed', 'error', $e->getMessage());
            $this->update_sync_field($user->id, false);
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
    
        // Mapiranje Moodle polja na MailChimp polja
        $field_mapping = [
            'address' => 'ADDRESS',
            'city' => 'CITY',
            'country' => 'COUNTRY',
            'phone1' => 'PHONE'
        ];
    
        foreach ($field_mapping as $moodle_field => $mailchimp_field) {
            if (strpos($moodle_field, 'profile_field_') === 0) {
                // Prilagođeno polje profila
                $field_name = substr($moodle_field, 14);
                $value = $this->get_profile_field_value($user->id, $field_name);
            } elseif (isset($user->$moodle_field)) {
                $value = $user->$moodle_field;
            } else {
                $value = null;
            }
    
            if ($value !== null) {
                $merge_fields->$mailchimp_field = $value;
            }
        }
    
        // Osigurajte da su FNAME i LNAME uvijek postavljeni
        $merge_fields->FNAME = $user->firstname;
        $merge_fields->LNAME = $user->lastname;
    
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
            $synced = true;
        }
    
        if ($synced) {
            $this->update_sync_field($user->id, true);
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
                mtrace("Removed user {$user->id} from MailChimp list {$list_id}");
                $removed = true;
            } catch (\moodle_exception $e) {
                mtrace("Error removing user {$user->id} from MailChimp list {$list_id}: " . $e->getMessage());
            }
        }

        // After removal:
        global $DB;
        $DB->delete_records('local_mailchimpsync_users', ['userid' => $user->userid]);

        // Update sync field
        $this->update_sync_field($user->id, false);
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