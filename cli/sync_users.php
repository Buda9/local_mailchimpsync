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

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'verbose' => false,
    ],
    [
        'h' => 'help',
        'v' => 'verbose',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Synchronize users with MailChimp.

Options:
-h, --help            Print out this help
-v, --verbose         Print verbose progress information

Example:
\$ sudo -u www-data /usr/bin/php local/mailchimpsync/cli/sync_users.php
";

    echo $help;
    die;
}

$verbose = !empty($options['verbose']);

// Create an instance of the sync class and run the sync
$sync = new \local_mailchimpsync\sync();
$api = new \local_mailchimpsync\api();

$default_list_id = get_config('local_mailchimpsync', 'default_list_id');
if (!$api->validate_list($default_list_id)) {
    die("Error: Invalid default list ID. Please check your settings.\n");
}

exit(0);