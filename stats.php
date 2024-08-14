<?php
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_mailchimpsync_stats');

$PAGE->set_url('/local/mailchimpsync/stats.php');
$PAGE->set_title(get_string('stats_page_title', 'local_mailchimpsync'));
$PAGE->set_heading(get_string('stats_page_heading', 'local_mailchimpsync'));

echo $OUTPUT->header();

// Display statistics
$total_synced = $DB->count_records('local_mailchimpsync_users');
$total_users = $DB->count_records('user', ['deleted' => 0, 'suspended' => 0]);
$sync_logs = $DB->get_records('local_mailchimpsync_log', null, 'timecreated DESC', '*', 0, 10);

echo html_writer::tag('h3', get_string('sync_statistics', 'local_mailchimpsync'));
echo html_writer::tag('p', get_string('total_synced_users', 'local_mailchimpsync', $total_synced));
echo html_writer::tag('p', get_string('total_moodle_users', 'local_mailchimpsync', $total_users));
echo html_writer::tag('p', get_string('sync_percentage', 'local_mailchimpsync', round(($total_synced / $total_users) * 100, 2)));

echo html_writer::tag('h3', get_string('recent_sync_logs', 'local_mailchimpsync'));
$table = new html_table();
$table->head = [
    get_string('user'),
    get_string('action', 'local_mailchimpsync'),
    get_string('status', 'local_mailchimpsync'),
    get_string('time', 'local_mailchimpsync')
];
foreach ($sync_logs as $log) {
    $user = $DB->get_record('user', ['id' => $log->userid]);
    $table->data[] = [
        fullname($user),
        $log->action,
        $log->status,
        userdate($log->timecreated)
    ];
}
echo html_writer::table($table);

echo $OUTPUT->footer();