<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('mailchimpmanualssync');

$PAGE->set_title(get_string('manualsync', 'local_mailchimpsync'));
$PAGE->set_heading(get_string('manualsync', 'local_mailchimpsync'));

$form = new \local_mailchimpsync\form\manual_sync_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php', ['section' => 'local_mailchimpsync']));
} else if ($fromform = $form->get_data()) {
    // Perform manual sync
    $task = new \local_mailchimpsync\task\sync_users();
    $task->set_custom_data(['manual' => true]);
    \core\task\manager::queue_adhoc_task($task);
    
    redirect(new moodle_url('/admin/settings.php', ['section' => 'local_mailchimpsync']), get_string('synctaskqueued', 'local_mailchimpsync'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manualsync', 'local_mailchimpsync'));

$form->display();

echo $OUTPUT->footer();