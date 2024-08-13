<?php
require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/mailchimpsync/admin/sync.php');
$PAGE->set_title(get_string('sync_page_title', 'local_mailchimpsync'));
$PAGE->set_heading(get_string('sync_page_heading', 'local_mailchimpsync'));

echo $OUTPUT->header();

$action = optional_param('action', '', PARAM_ALPHA);

if ($action === 'sync_all') {
    $sync = new \local_mailchimpsync\sync();
    $sync->sync_all_users();
    echo $OUTPUT->notification(get_string('sync_all_completed', 'local_mailchimpsync'), 'success');
}

// Display sync options
echo html_writer::start_tag('div', array('class' => 'sync-options'));
echo html_writer::link(new moodle_url($PAGE->url, array('action' => 'sync_all')), 
                       get_string('sync_all_users', 'local_mailchimpsync'), 
                       array('class' => 'btn btn-primary'));
echo html_writer::end_tag('div');

// Display recent logs
$logs = $DB->get_records('local_mailchimpsync_log', null, 'timecreated DESC', '*', 0, 50);

echo html_writer::start_tag('div', array('class' => 'sync-logs mt-4'));
echo html_writer::tag('h3', get_string('recent_logs', 'local_mailchimpsync'));
echo html_writer::start_tag('table', array('class' => 'table'));
echo html_writer::start_tag('thead');
echo html_writer::start_tag('tr');
echo html_writer::tag('th', get_string('user'));
echo html_writer::tag('th', get_string('list', 'local_mailchimpsync'));
echo html_writer::tag('th', get_string('action'));
echo html_writer::tag('th', get_string('status'));
echo html_writer::tag('th', get_string('time'));
echo html_writer::end_tag('tr');
echo html_writer::end_tag('thead');
echo html_writer::start_tag('tbody');

foreach ($logs as $log) {
    $user = $DB->get_record('user', array('id' => $log->userid));
    echo html_writer::start_tag('tr');
    echo html_writer::tag('td', fullname($user));
    echo html_writer::tag('td', $log->listid);
    echo html_writer::tag('td', $log->action);
    echo html_writer::tag('td', $log->status);
    echo html_writer::tag('td', userdate($log->timecreated));
    echo html_writer::end_tag('tr');
}

echo html_writer::end_tag('tbody');
echo html_writer::end_tag('table');
echo html_writer::end_tag('div');

echo $OUTPUT->footer();