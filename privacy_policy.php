<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_mailchimpsync_privacy');

$PAGE->set_title(get_string('privacypolicy', 'local_mailchimpsync'));
$PAGE->set_heading(get_string('privacypolicy', 'local_mailchimpsync'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('privacypolicy', 'local_mailchimpsync'));

echo html_writer::tag('p', get_string('privacypolicy_intro', 'local_mailchimpsync'));

echo html_writer::tag('h3', get_string('datashared', 'local_mailchimpsync'));
echo html_writer::tag('p', get_string('datashared_desc', 'local_mailchimpsync'));

echo html_writer::tag('h3', get_string('dataretention', 'local_mailchimpsync'));
echo html_writer::tag('p', get_string('dataretention_desc', 'local_mailchimpsync'));

echo html_writer::tag('h3', get_string('datarights', 'local_mailchimpsync'));
echo html_writer::tag('p', get_string('datarights_desc', 'local_mailchimpsync'));

echo $OUTPUT->footer();