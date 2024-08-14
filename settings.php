<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    // Kreiramo glavnu kategoriju za MailChimp Sync
    $ADMIN->add('localplugins', new admin_category('local_mailchimpsync_category', get_string('pluginname', 'local_mailchimpsync')));

    // Glavna stranica postavki
    $settings = new admin_settingpage('local_mailchimpsync_settings', get_string('settings', 'local_mailchimpsync'));

    if ($ADMIN->fulltree) {
        // API Key
        $settings->add(new admin_setting_configtext(
            'local_mailchimpsync/apikey',
            get_string('apikey', 'local_mailchimpsync'),
            get_string('apikey_desc', 'local_mailchimpsync'),
            '',
            PARAM_TEXT
        ));

        $api = new \local_mailchimpsync\api();
        $lists = $api->get_lists();

        // Only add these settings if API key is set and lists are retrieved
        if (!empty($lists)) {
            // Default List
            $settings->add(new admin_setting_configtext(
                'local_mailchimpsync/default_list_id',
                get_string('default_list_id', 'local_mailchimpsync'),
                get_string('default_list_id_desc', 'local_mailchimpsync'),
                '',
                PARAM_TEXT
            ));

            // Cohort to List Mapping
            $cohorts = $DB->get_records_menu('cohort', null, 'name', 'id, name');

            if (!empty($cohorts)) {
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

            $settings->add(new admin_setting_configcheckbox(
                'local_mailchimpsync/use_sync_field',
                get_string('use_sync_field', 'local_mailchimpsync'),
                get_string('use_sync_field_desc', 'local_mailchimpsync'),
                0
            ));

            $custom_fields = $DB->get_records_menu('user_info_field', null, '', 'id, name');
            $settings->add(new admin_setting_configselect(
                'local_mailchimpsync/sync_field',
                get_string('sync_field', 'local_mailchimpsync'),
                get_string('sync_field_desc', 'local_mailchimpsync'),
                '',
                $custom_fields
            ));
        }
    }

    // Dodajemo glavnu stranicu postavki u kategoriju
    $ADMIN->add('local_mailchimpsync_category', $settings);

    // Dodajemo ostale stranice u kategoriju
    $ADMIN->add('local_mailchimpsync_category', new admin_externalpage(
        'local_mailchimpsync_sync',
        get_string('sync_page_title', 'local_mailchimpsync'),
        new moodle_url('/local/mailchimpsync/admin/sync.php')
    ));

    $ADMIN->add('local_mailchimpsync_category', new admin_externalpage(
        'local_mailchimpsync_stats',
        get_string('stats_page_title', 'local_mailchimpsync'),
        new moodle_url('/local/mailchimpsync/stats.php')
    ));

    $ADMIN->add('local_mailchimpsync_category', new admin_externalpage(
        'local_mailchimpsync_privacy',
        get_string('privacy_policy', 'local_mailchimpsync'),
        new moodle_url('/local/mailchimpsync/privacy_policy.php')
    ));
}