<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Check for valid admin user
admin_externalpage_setup('local_mailchimpsync_cohort_mapping');

// Additional capability check
require_capability('local/mailchimpsync:managecohortmapping', context_system::instance());

$PAGE->set_title(get_string('managecohortmapping', 'local_mailchimpsync'));
$PAGE->set_heading(get_string('managecohortmapping', 'local_mailchimpsync'));

// Rest of the existing code...

// Add a summary of cohort sync status
echo '<h3>' . get_string('cohortssyncsummary', 'local_mailchimpsync') . '</h3>';
echo '<table class="generaltable">';
echo '<tr><th>' . get_string('cohort', 'cohort') . '</th><th>' . get_string('mailchimplist', 'local_mailchimpsync') . '</th><th>' . get_string('syncstatus', 'local_mailchimpsync') . '</th><th>' . get_string('lastsynced', 'local_mailchimpsync') . '</th></tr>';

foreach ($cohorts as $cohort) {
    $list_id = isset($existing_mappings[$cohort->id]) ? $existing_mappings[$cohort->id] : '';
    $list_name = isset($lists[$list_id]) ? $lists[$list_id] : get_string('nolistselected', 'local_mailchimpsync');
    
    // Get last sync time for this cohort
    $last_sync = $DB->get_field('local_mailchimpsync_log', 'MAX(timecreated)', ['cohortid' => $cohort->id]);
    $last_sync_str = $last_sync ? userdate($last_sync) : get_string('neverssynced', 'local_mailchimpsync');

    echo '<tr>';
    echo '<td>' . $cohort->name . '</td>';
    echo '<td>' . $list_name . '</td>';
    echo '<td>' . ($list_id ? get_string('synced', 'local_mailchimpsync') : get_string('notsynced', 'local_mailchimpsync')) . '</td>';
    echo '<td>' . $last_sync_str . '</td>';
    echo '</tr>';
}

echo '</table>';

echo $OUTPUT->footer();