<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Check for valid admin user
admin_externalpage_setup('local_mailchimpsync_cohort_mapping');

// Additional capability check
require_capability('local/mailchimpsync:managecohortmapping', context_system::instance());

$PAGE->set_title(get_string('managecohortmapping', 'local_mailchimpsync'));
$PAGE->set_heading(get_string('managecohortmapping', 'local_mailchimpsync'));

$action = optional_param('action', '', PARAM_ALPHA);

echo $OUTPUT->header();

// Handle form submission
if ($data = data_submitted() && confirm_sesskey()) {
    $mappings = array();
    foreach ($data as $key => $value) {
        if (strpos($key, 'cohort_') === 0) {
            $cohort_id = substr($key, 7);
            $mappings[$cohort_id] = $value;
        }
    }
    set_config('cohort_list_mapping', json_encode($mappings), 'local_mailchimpsync');
    redirect($PAGE->url, get_string('cohortmappingupdated', 'local_mailchimpsync'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Get existing mappings
$existing_mappings = json_decode(get_config('local_mailchimpsync', 'cohort_list_mapping'), true) ?: array();

// Get all cohorts
$cohorts = $DB->get_records('cohort');

// Get MailChimp lists
$api = new \local_mailchimpsync\api();
$lists = $api->get_lists();

echo '<h2>' . get_string('managecohortmapping', 'local_mailchimpsync') . '</h2>';

// Display form
echo '<form action="' . $PAGE->url . '" method="post">';
echo '<table class="generaltable">';
echo '<tr><th>' . get_string('cohort', 'cohort') . '</th><th>' . get_string('mailchimplist', 'local_mailchimpsync') . '</th></tr>';

foreach ($cohorts as $cohort) {
    echo '<tr>';
    echo '<td>' . $cohort->name . '</td>';
    echo '<td>';
    echo '<select name="cohort_' . $cohort->id . '">';
    echo '<option value="">' . get_string('none') . '</option>';
    foreach ($lists as $list_id => $list_name) {
        $selected = (isset($existing_mappings[$cohort->id]) && $existing_mappings[$cohort->id] == $list_id) ? 'selected' : '';
        echo '<option value="' . $list_id . '" ' . $selected . '>' . $list_name . '</option>';
    }
    echo '</select>';
    echo '</td>';
    echo '</tr>';
}

echo '</table>';
echo '<input type="submit" value="' . get_string('savemapping', 'local_mailchimpsync') . '" />';
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
echo '</form>';

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

// Add sync button for each cohort
echo '<h3>' . get_string('syncactions', 'local_mailchimpsync') . '</h3>';
foreach ($cohorts as $cohort) {
    echo '<p>';
    echo $cohort->name . ': ';
    echo '<a href="' . new moodle_url('/local/mailchimpsync/sync.php', array('cohort' => $cohort->id, 'sesskey' => sesskey())) . '" class="btn btn-secondary">';
    echo get_string('synccohort', 'local_mailchimpsync');
    echo '</a>';
    echo '</p>';
}

echo $OUTPUT->footer();