<?php
namespace local_mailchimpsync;

defined('MOODLE_INTERNAL') || die();

class observer {
    public static function user_created(\core\event\user_created $event) {
        $user = \core_user::get_user($event->objectid);
        $sync = new sync();
        $sync->sync_single_user($user);

        // Send notification
        $message = new \core\message\message();
        $message->component = 'local_mailchimpsync';
        $message->name = 'mailchimp_notifications';
        $message->userfrom = \core_user::get_support_user();
        $message->userto = $user->id;
        $message->subject = get_string('sync_notification_subject', 'local_mailchimpsync');
        $message->fullmessage = get_string('sync_notification_message', 'local_mailchimpsync');
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '';
        $message->smallmessage = get_string('sync_notification_small', 'local_mailchimpsync');
        $message->notification = 1;

        message_send($message);
    }

    public static function user_updated(\core\event\user_updated $event) {
        $user = \core_user::get_user($event->objectid);
        $sync = new sync();
        $sync->sync_single_user($user);
    }
}