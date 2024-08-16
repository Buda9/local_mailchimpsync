<?php
namespace local_mailchimpsync\task;

defined('MOODLE_INTERNAL') || die();

class sync_users extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('task_sync_users', 'local_mailchimpsync');
    }

    public function execute() {
        global $CFG, $DB;

        $lockfactory = \core\lock\lock_config::get_lock_factory('local_mailchimpsync_sync');

        if ($lock = $lockfactory->get_lock('local_mailchimpsync_sync', 30)) {
            try {
                mtrace('Starting MailChimp sync task...');
                $sync = new \local_mailchimpsync\sync();
                $sync->sync_all_users();
                mtrace('MailChimp sync task completed.');
            } finally {
                $lock->release();
            }
        } else {
            mtrace('Cannot obtain task lock');
        }
    }
}