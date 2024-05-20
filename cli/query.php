<?php
define('CLI_SCRIPT', true);

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/clilib.php');
// Load the "testable" version so we can get some of the internals back out.
require_once($CFG->dirroot .'/search/tests/fixtures/testable_core_search.php');
use core_ai\api;
use core_ai\aiclient;
use core_ai\aiprovider;

[$options, $unrecognized] = cli_get_params(
    [
        'help' => false,
        'prompt' => false,
        'providerid' => 1,
        'courses' => [2],
        'contexts' => [],
        'limit' => 3,
        'userid' => false
    ],[
        'h' => 'help',
        'p' => 'providerid',
        'l' => 'limit'
    ]

);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
$help = <<<EOT
This script will perform a RAG search using the given prompt and provider.

It will output the contents of the documents identified as similar to the prompt.

Options:
 -h, --help                Print out this help
 -p, --providerid          ID of provider to use
 --courses                 IDs of courses to use
 --userid                  ID of user to search as
 
Run all queued tasks:
\$sudo -u www-data /usr/bin/php admin/cli/adhoc_task.php --execute
EOT;
if ($options['help']) {
    echo $help;
    exit(0);
}

var_dump($options);
var_dump($unrecognized);

$providerid = $options['providerid'];
$filters = [];
$accessinfo = [];
$limit = $options['limit'];

if (empty($options['prompt'])) {
    throw new \moodle_exception("Prompt is required");
}

execute($options);
function execute( $options) {
    global $USER;
    $humantimenow = date('r', time());
    mtrace("Server Time: {$humantimenow}\n");
    $user = $USER;
    if (!empty($options['userid'])) {
        $user = \core_user::get_user($options['userid']);
    }
//    var_dump($user);
    mtrace("Searching as ". fullname($user));
    \core\session\manager::init_empty_session();
    \core\session\manager::set_user($user);
    $providerid = $options['providerid'];
    $provider = api::get_provider($providerid);
    $client = new aiclient($provider);

    $courseids = $options['courses'];
    if (is_string($courseids)) {
        $courseids = explode(",", $courseids);
    }

    $engine = \core_search\manager::search_engine_instance();
    $manager = testable_core_search::instance($engine);
        //\core_search\manager::instance();
    $formdata = new \stdClass();
    $formdata->userprompt = $options['prompt'];
    $formdata->contextids = [];
    $formdata->mycoursesonly = false;
    $formdata->courseids = $courseids;

    $settings = $provider->get_settings_for_user($user);
    $settings['userquery'] = $formdata->userprompt;
    $settings['courseids'] = $courseids;

    $limitcourseids = $manager->build_limitcourseids($formdata);
    $limitcontextids = $formdata->contextids;
    $contextinfo = $manager->get_areas_user_accesses($limitcourseids, $limitcontextids);

    mtrace("Manager settings");
    mtrace("Restrict to courses: ");
    var_dump($limitcourseids);
    mtrace("Contextinfo returned: ");
    var_dump($contextinfo);

    mtrace('Performing search');
    $docs = $manager->search((object)$settings);
    var_dump($docs);

}
