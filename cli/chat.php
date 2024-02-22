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
define("CLI_SCRIPT", true);

require_once(__DIR__ . '/../../../config.php');
require_once("{$CFG->libdir}/clilib.php");

list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'message' => false,
        'course' => false, // Set to a course to limit context to that course.
        'cmid' => false, // Set to limit context to a specific activity.
        'user' => false // Set to limit to a specific user.
    ], [
        'h' => 'help',
        'm' => 'message'
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

$help = <<<EOT
Ad hoc cron tasks.

Options:
 -h, --help                 Print out this help
 -m, --message              The prompt to pass to the LLM
 --course                   Limit to specific Course ID
 --cmid                     Limit to a specific activity (cmid)
 --user                     Limit to a specific user
 
EOT;

if ($options['help']) {
    echo $help;
    exit(0);
}

$message = $options['message'];
$courseid = $options['course'];
$cmid = $options['cmid'];
$userid = $options['user'];
if (empty($message)) {
    cli_error("You need to provide some information to pass to the Model");
    exit();
}
$course = null;
$cm = null;
$user = null;
if ($cmid) {
    list($course, $cm) = get_course_and_cm_from_cmid($cmid);
} else if ($courseid) {
    $course = get_course($courseid);
}
if ($userid) {
    $user = core_user::get_user($userid);
}

$manager = new \local_ai\local\manager('global');
    // 'test');

$manager->chat($message);
