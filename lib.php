<?php
defined('MOODLE_INTERNAL') || die();

function local_mailchimpsync_extend_navigation_user_settings($navigation, $user, $usercontext) {
    $node = $navigation->add(get_string('mailchimpnotifications', 'local_mailchimpsync'));
    $node->add(
        get_string('optoutmailchimp', 'local_mailchimpsync'),
        new moodle_url('/local/mailchimpsync/optout.php'),
        navigation_node::TYPE_SETTING
    );
}

function local_mailchimpsync_user_preferences() {
    $preferences['local_mailchimpsync_optout'] = array(
        'type' => PARAM_BOOL,
        'null' => NULL_NOT_ALLOWED,
        'default' => 0,
        'choices' => array(0, 1)
    );
    return $preferences;
}

function local_mailchimpsync_message_provider() {
    return [
        'mailchimp_notifications' => [
            'capability' => 'local/mailchimpsync:receivemessages'
        ],
    ];
}