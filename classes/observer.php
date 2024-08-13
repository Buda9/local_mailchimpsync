<?php
namespace local_mailchimpsync;

defined('MOODLE_INTERNAL') || die();

class observer {
    public static function user_created(\core\event\user_created $event) {
        $user = \core_user::get_user($event->objectid);
        $sync = new sync();
        $sync->sync_single_user($user);
    }

    public static function user_updated(\core\event\user_updated $event) {
        $user = \core_user::get_user($event->objectid);
        $sync = new sync();
        $sync->sync_single_user($user);
    }
}