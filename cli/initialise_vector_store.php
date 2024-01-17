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

mtrace("Attempting to initialise vector store");

$manager = new \local_ai\local\manager('test');
$keepalive = 0;
$taskslimit = null;
$checklimits = true;
$classname = '\local_ai\task\run_python_command';

\core\cron::run_adhoc_tasks(
    time(),
    $keepalive,
    $checklimits,
    null,
    $taskslimit,
    $classname
);
