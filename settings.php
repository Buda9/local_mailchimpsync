<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_mailchimpsync', get_string('pluginname', 'local_mailchimpsync'));

    // API Key
    $settings->add(new admin_setting_configtext(
        'local_mailchimpsync/apikey',
        get_string('apikey', 'local_mailchimpsync'),
        get_string('apikey_desc', 'local_mailchimpsync'),
        '',
        PARAM_TEXT
    ));

    // Get MailChimp lists
    $api = new \local_mailchimpsync\api();
    $lists = $api->get_lists();

    // Default List
    $settings->add(new admin_setting_configselect(
        'local_mailchimpsync/default_list_id',
        get_string('default_list_id', 'local_mailchimpsync'),
        get_string('default_list_id_desc', 'local_mailchimpsync'),
        '',
        $lists
    ));

    // Cohort to List Mapping
    $cohorts = $DB->get_records_menu('cohort', null, 'name', 'id, name');

    if (!empty($cohorts) && !empty($lists)) {
        foreach ($cohorts as $cohort_id => $cohort_name) {
            $settings->add(new admin_setting_configselect(
                "local_mailchimpsync/cohort_{$cohort_id}_list",
                get_string('cohort_list', 'local_mailchimpsync', $cohort_name),
                get_string('cohort_list_desc', 'local_mailchimpsync'),
                '',
                $lists
            ));
        }
    }

    // Field Mapping
    $userfields = [
        'firstname' => get_string('firstname'),
        'lastname' => get_string('lastname'),
        'email' => get_string('email'),
        'city' => get_string('city'),
        'country' => get_string('country'),
        'lang' => get_string('language'),
        'timezone' => get_string('timezone'),
        'phone1' => get_string('phone'),
        'phone2' => get_string('phone2'),
    ];

    // Add custom profile fields
    $custom_fields = $DB->get_records('user_info_field', null, '', 'id, name, shortname');
    foreach ($custom_fields as $field) {
        $userfields['profile_field_'.$field->shortname] = $field->name;
    }

    // MailChimp merge fields
    $mailchimp_merge_fields = [];
    if (!empty($lists)) {
        $first_list_id = array_keys($lists)[0];
        $mailchimp_merge_fields = $api->get_merge_fields($first_list_id);
    }
    
    $merge_field_options = [];
    foreach ($mailchimp_merge_fields as $field) {
        $merge_field_options[$field['tag']] = $field['name'];
    }

    $settings->add(new admin_setting_configmultiselect(
        'local_mailchimpsync/field_mapping',
        get_string('field_mapping', 'local_mailchimpsync'),
        get_string('field_mapping_desc', 'local_mailchimpsync'),
        ['FNAME' => 'firstname', 'LNAME' => 'lastname', 'EMAIL' => 'email'],
        array_combine(array_keys($userfields), array_keys($userfields))
    ));

    $ADMIN->add('localplugins', new admin_externalpage(
        'local_mailchimpsync_sync',
        get_string('sync_page_title', 'local_mailchimpsync'),
        new moodle_url('/local/mailchimpsync/admin/sync.php')
    ));

    $ADMIN->add('localplugins', $settings);
}