<?php
namespace local_mailchimpsync;

defined('MOODLE_INTERNAL') || die();

class recovery_manager {
    private $db;

    public function __construct() {
        global $DB;
        $this->db = $DB;
    }

    public function log_sync_attempt($user_id, $status) {
        $record = new \stdClass();
        $record->userid = $user_id;
        $record->timecreated = time();
        $record->status = $status;
        
        $this->db->insert_record('local_mailchimpsync_log', $record);
    }

    public function get_failed_syncs($limit = 100) {
        return $this->db->get_records('local_mailchimpsync_log', ['status' => 'failed'], 'timecreated DESC', '*', 0, $limit);
    }

    public function retry_failed_syncs() {
        $failed_syncs = $this->get_failed_syncs();
        $api = new api();

        foreach ($failed_syncs as $sync) {
            $user = $this->db->get_record('user', ['id' => $sync->userid]);
            if ($user) {
                try {
                    $api->sync_user($user);
                    $this->log_sync_attempt($user->id, 'success');
                } catch (\Exception $e) {
                    $this->log_sync_attempt($user->id, 'failed');
                }
            }
        }
    }
}