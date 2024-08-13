<?php
namespace local_mailchimpsync\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection) : collection {
        $collection->add_external_location_link(
            'mailchimp',
            [
                'email' => 'privacy:metadata:mailchimp:email',
                'firstname' => 'privacy:metadata:mailchimp:firstname',
                'lastname' => 'privacy:metadata:mailchimp:lastname',
            ],
            'privacy:metadata:mailchimp'
        );

        $collection->add_database_table(
            'local_mailchimpsync_log',
            [
                'userid' => 'privacy:metadata:local_mailchimpsync_log:userid',
                'listid' => 'privacy:metadata:local_mailchimpsync_log:listid',
                'action' => 'privacy:metadata:local_mailchimpsync_log:action',
                'timecreated' => 'privacy:metadata:local_mailchimpsync_log:timecreated',
            ],
            'privacy:metadata:local_mailchimpsync_log'
        );

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();
        $contextlist->add_user_context($userid);
        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        $context = \context_user::instance($user->id);

        $data = [];
        $logs = $DB->get_records('local_mailchimpsync_log', ['userid' => $user->id]);
        foreach ($logs as $log) {
            $data[] = [
                'listid' => $log->listid,
                'action' => $log->action,
                'timecreated' => \core_privacy\local\request\transform::datetime($log->timecreated),
            ];
        }

        \core_privacy\local\request\writer::with_context($context)->export_data(
            ['local_mailchimpsync'],
            (object) ['sync_logs' => $data]
        );
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context instanceof \context_user) {
            $DB->delete_records('local_mailchimpsync_log', ['userid' => $context->instanceid]);
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        $DB->delete_records('local_mailchimpsync_log', ['userid' => $userid]);

        $sync = new \local_mailchimpsync\sync();
        $lists = $sync->get_configured_lists();
        foreach ($lists as $list_id => $list_config) {
            $sync->unsubscribe_user($contextlist->get_user(), $list_id);
        }
    }

    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context instanceof \context_user) {
            $userlist->add_user($context->instanceid);
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context instanceof \context_user) {
            $DB->delete_records('local_mailchimpsync_log', ['userid' => $context->instanceid]);

            $user = \core_user::get_user($context->instanceid);
            if ($user) {
                $sync = new \local_mailchimpsync\sync();
                $lists = $sync->get_configured_lists();
                foreach ($lists as $list_id => $list_config) {
                    $sync->unsubscribe_user($user, $list_id);
                }
            }
        }
    }
}