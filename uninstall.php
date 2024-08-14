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
 * Uninstall script for local_mailchimpsync.
 *
 * @package    local_mailchimpsync
 * @copyright  2023 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom uninstallation procedure.
 */
function xmldb_local_mailchimpsync_uninstall() {
    global $DB;

    $dbman = $DB->get_manager();

    // Remove tables if they exist.
    $tables = ['local_mailchimpsync_users', 'local_mailchimpsync_log'];
    foreach ($tables as $tablename) {
        $table = new xmldb_table($tablename);
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
    }

    // Remove all plugin configuration.
    $DB->delete_records('config_plugins', ['plugin' => 'local_mailchimpsync']);

    // If you've created any custom user profile fields, you might want to remove them as well.
    // Be careful with this as it will delete user data!
    $fieldname = 'newsletter'; // Replace with your actual field name
    $field = $DB->get_record('user_info_field', ['shortname' => $fieldname]);
    if ($field) {
        $DB->delete_records('user_info_data', ['fieldid' => $field->id]);
        $DB->delete_records('user_info_field', ['id' => $field->id]);
    }

    // Clean up any other data or settings your plugin might have created
    // For example, if you've added any capabilities:
    // capabilities_cleanup('local/mailchimpsync');

    return true;
}

/**
 * Function to clean up capabilities.
 * @param string $component Component name.
 */
function capabilities_cleanup($component) {
    global $DB;

    $like = $DB->sql_like('name', ':cap');
    $params = array('component' => $component, 'cap' => $DB->sql_like_escape($component . ':') . '%');
    $DB->delete_records_select('role_capabilities', "component = :component OR $like", $params);
    $DB->delete_records_select('capabilities', "component = :component OR $like", $params);
}