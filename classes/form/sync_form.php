<?php
namespace local_mailchimpsync\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class manual_sync_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('static', 'description', '', get_string('manualsyncdesch', 'local_mailchimpsync'));

        $this->add_action_buttons(true, get_string('startsync', 'local_mailchimpsync'));
    }
}