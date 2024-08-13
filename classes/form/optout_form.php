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

namespace local_mailchimpsync\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class optout_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('advcheckbox', 'optout', get_string('optoutmailchimp', 'local_mailchimpsync'),
            get_string('optoutdesc', 'local_mailchimpsync'));
        $mform->setDefault('optout', get_user_preferences('local_mailchimpsync_optout', 0));

        $this->add_action_buttons();
    }
}