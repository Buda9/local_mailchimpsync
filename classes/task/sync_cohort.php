<?php
namespace local_mailchimpsync\task;

defined('MOODLE_INTERNAL') || die();

class sync_cohort extends \core\task\adhoc_task {
    const BATCH_SIZE = 500;

    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        $cohort_id = $data->cohort_id;

        $api = new \local_mailchimpsync\api();
        $cohort_list_mapping = json_decode(get_config('local_mailchimpsync', 'cohort_list_mapping'), true) ?: array();

        if (!isset($cohort_list_mapping[$cohort_id])) {
            //mtrace("No MailChimp list mapped for cohort ID: $cohort_id");
            return;
        }

        $list_id = $cohort_list_mapping[$cohort_id];

        $total_users = $DB->count_records_sql(
            "SELECT COUNT(u.id)
             FROM {user} u
             JOIN {cohort_members} cm ON u.id = cm.userid
             WHERE cm.cohortid = ? AND u.deleted = 0 AND u.suspended = 0",
            array($cohort_id)
        );

        $total_batches = ceil($total_users / self::BATCH_SIZE);

        $synced_count = 0;

        for ($batch = 0; $batch < $total_batches; $batch++) {
            $users = $DB->get_records_sql(
                "SELECT u.*
                 FROM {user} u
                 JOIN {cohort_members} cm ON u.id = cm.userid
                 WHERE cm.cohortid = ? AND u.deleted = 0 AND u.suspended = 0
                 ORDER BY u.id",
                array($cohort_id),
                $batch * self::BATCH_SIZE,
                self::BATCH_SIZE
            );

            foreach ($users as $user) {
                if ($this->should_sync_user($user)) {
                    $api->sync_user_to_list($user, $list_id);
                    $synced_count++;
                }
            }

            // Allow for some breathing room between batches
            sleep(1);
        }

        // Log the sync operation
        $log = new \stdClass();
        $log->cohortid = $cohort_id;
        $log->listid = $list_id;
        $log->usercount = $synced_count;
        $log->timecreated = time();
        $DB->insert_record('local_mailchimpsync_log', $log);
    }

    private function should_sync_user($user) {
        $user_preferences = get_user_preferences(null, null, $user->id);
        return !isset($user_preferences['local_mailchimpsync_optout']) || $user_preferences['local_mailchimpsync_optout'] != 1;
    }
}