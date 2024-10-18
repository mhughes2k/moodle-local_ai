<?php

/**
 * A lot of this is based on the Oauth2 issuers.php file.
 */
require_once("../../config.php");


require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

use local_ai\api;

$PAGE->set_url('/ai/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout("admin");

$renderer = $PAGE->get_renderer('local_ai');

$action = optional_param('action', '', PARAM_ALPHAEXT);
// We're using pid as "id" is used to specify contextids.
$providerid = optional_param('pid', '', PARAM_RAW);
$incontextid = optional_param('contextid', null, PARAM_RAW);
//var_dump($incontextid);
$context = !empty($incontextid) ? \context::instance_by_id($incontextid) : null;

if (empty($context)) {
    $strheading = get_string('pluginname', 'local_ai');
} else {
    $strheading = get_string('aiprovidersin', 'local_ai', $context->get_context_name());
}
$PAGE->set_heading($strheading);
$PAGE->set_title($strheading);

$provider = null;
$mform = null;

if ($providerid) {
    $provider = api::get_provider($providerid);
    if (!$provider) {
        throw new moodle_exception('invaliddata');
    }
}

if ($action == api::ACTION_EDIT_PROVIDER) {

    if ($provider) {
        // Edit
        $type = "openaipi";// Should store in and read from provider.
    } else {
        // Create new
        $type = required_param('type', PARAM_RAW);
    }
    $mform = new \local_ai\form\openaiapiprovider(null, [
        'persistent' => $provider,
        'type' => $type,
        'contextid' => $incontextid,
        'enabled' => true,
    ]);
}


if ($mform && $mform->is_cancelled()) {
    redirect(new moodle_url('/admin/tool/oauth2/issuers.php'));
} else if ($action == api::ACTION_EDIT_PROVIDER) {
    // Handle edit.
    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/ai/index.php'));
    }

    if ($data = $mform->get_data()) {
//        var_dump($data);
        try {
            if (!empty($data->id)) {
                api::update_provider($data);
            } else {
                api::create_provider($data);
            }
            redirect(new moodle_url('/local/ai/index.php'));
        }
        catch (moodle_exception $e) {
            throw $e;
        }


        exit();
    } else {
        echo $OUTPUT->header();
        $mform->display();
        echo $OUTPUT->footer();
    }
    exit;
} else if ($action == api::ACTION_REMOVE_PROVIDER) {
    // Handle remove.
} else {
    // Display list of providers.
    $indexpage = new \local_ai\output\index_page(
        api::get_providers($incontextid)
    );
    echo $OUTPUT->header();

    echo $renderer->render_index_page($indexpage);
    echo $OUTPUT->footer();
}
