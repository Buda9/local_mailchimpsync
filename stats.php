<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('mailchimpsyncstats');
$PAGE->set_title(get_string('syncstats', 'local_mailchimpsync'));
$PAGE->set_heading(get_string('syncstats', 'local_mailchimpsync'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('syncstats', 'local_mailchimpsync'));
$recovery_manager = new \local_mailchimpsync\recovery_manager();
// Get sync statistics
$total_syncs = $DB->count_records('local_mailchimpsync_log');
$successful_syncs = $DB->count_records('local_mailchimpsync_log', ['status' => 'success']);
$failed_syncs = $DB->count_records('local_mailchimpsync_log', ['status' => 'failed']);
// Get recent sync attempts
$recent_syncs = $DB->get_records('local_mailchimpsync_log', null, 'timecreated DESC', '*', 0, 10);
// Display statistics
echo html_writer::tag('h3', get_string('syncoverview', 'local_mailchimpsync'));
echo html_writer::start_tag('ul');
echo html_writer::tag('li', get_string('totalsyncs', 'local_mailchimpsync') . ': ' . $total_syncs);
echo html_writer::tag('li', get_string('successfulsyncs', 'local_mailchimpsync') . ': ' . $successful_syncs);
echo html_writer::tag('li', get_string('failedsyncs', 'local_mailchimpsync') . ': ' . $failed_syncs);
echo html_writer::end_tag('ul');
// Display recent sync attempts
echo html_writer::tag('h3', get_string('recentsyncs', 'local_mailchimpsync'));
$table = new html_table();
$table->head = [
get_string('user'),
get_string('status'),
get_string('timecreated', 'local_mailchimpsync')
];
foreach ($recent_syncs as $sync) {
$user = $DB->get_record('user', ['id' => $sync->userid]);
$table->data[] = [
fullname($user),
$sync->status,
userdate($sync->timecreated)
];
}
echo html_writer::table($table);
echo $OUTPUT->footer();