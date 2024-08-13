<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * MailChimp Sync opt-out page.
 *
 * @package    local_mailchimpsync
 * @copyright  2024 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$PAGE->set_url('/local/mailchimpsync/optout.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('optoutmailchimp', 'local_mailchimpsync'));
$PAGE->set_heading(get_string('optoutmailchimp', 'local_mailchimpsync'));

require_login();

$form = new \local_mailchimpsync\form\optout_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/user/preferences.php'));
} else if ($fromform = $form->get_data()) {
    set_user_preference('local_mailchimpsync_optout', $fromform->optout);
    
    if ($fromform->optout) {
        $api = new \local_mailchimpsync\api();
        $api->unsubscribe_user($USER->email);
    }
    
    redirect(new moodle_url('/user/preferences.php'), get_string('preferencessaved'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('optoutmailchimp', 'local_mailchimpsync'));

$form->display();

echo $OUTPUT->footer();