<?php
namespace local_mailchimpsync\task;

defined('MOODLE_INTERNAL') || die();

class sync_users extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('task_sync_users', 'local_mailchimpsync');
    }

    public function execute() {
        mtrace('Starting MailChimp sync task...');
        $sync = new \local_mailchimpsync\sync();
        $sync->sync_all_users();
        mtrace('MailChimp sync task completed.');
    }
}