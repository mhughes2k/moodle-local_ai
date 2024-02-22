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
        'dir' => false,
        'answerfile' => false,
        'clear' => false
    ], [
        'h' => 'help',
        'd' => 'dir',
        'a' => 'answerfile',
        'c' => 'clear'
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
 -d, --dir                  Root "verification" directory.
 -a, --answerfile           This is the artefact that has the 1st line as the prompt 2 new lines and then the AI's response
                            Default: "answer.txt"
 -c, --clear                Clears out --dir
                            
EOT;

$answerfile = "answer.txt"; // This is the artefact that has the 1st line as the prompt 2 new lines and then the AI's response.

if ($options['help']) {
    echo $help;
    exit(0);
}

if (!$options['dir']) {
    echo "--dir is required";
    echo $help;
    exit(0);
}

if ($options['clear']) {
    echo "Clearing out {$options['dir']}\n";
    exit(0);
}

if ($options['answerfile'] !== false) {
    $answerfile = $options['answerfile'];
}

$collation = collate($options['dir'], $answerfile);
if ($collation) {
    $qi = 1;

    foreach($collation as $question => $answers) {
        $ai = 1;
        echo "# Question {$qi}: $question\n";
        
        foreach($answers as $answer) {
            echo "## Answer {$ai}\n";
            echo $answer;
            echo "\n";
            $ai++;
        }
    }
}

function collate($directory, $answerfile) {
    $dir = realpath($directory);
    if (!$dir) {
        echo "{$directory} can't be found\n";
        return ;
    }
    echo "Collating from {$dir}\n";
    $files = array_filter(scandir($dir), function ($file) {
        //echo $file . is_dir($file). "\n";
        return !is_dir($file);
    });
    $collation = [];
    foreach ($files as $file) {
        // Do something with the file or directory
        $fp = $dir. DIRECTORY_SEPARATOR. $file . DIRECTORY_SEPARATOR . $answerfile;
        echo $fp ."\n";
        if (!file_exists($fp)) {
            continue;
        }
        list($question, $answer) = parse_answerfile($fp);
        if (!isset($collation[$question])) {
            $collation[$question] = [];
        }
        $collation[$question][] = $answer;
    }
    return $collation;
}

function parse_answerfile($file) {

    $h = fopen($file, 'r');
    $prompt = fgets($h);
    fgets($h);  // Skip blank line
    $content = fgets($h);
    while (($line = fgets($h)) !== false) {
        $content .= fgets($h);
    }
    fclose($h);
    return [
        $prompt,
        $content
    ];
}
