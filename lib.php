<?php

use local_ai\api;
use local_ai\cmconfig;

/**
 * Extend categories with option to define an AI Provider for all enclosed
 * subcategories and courses.
 * @param $categorynode
 * @param $catcontext
 * @return void
 */
// TODO again

function local_ai_extend_navigation(global_navigation $nav) {
//    debugging("Extending global nav");
}


function local_ai_extend_settings_navigation(settings_navigation $nav, context $context) {

}

function local_ai_extend_navigation_course($navigation, $course, $context) {
//    debugging("Extending course nav");
    if (has_capability('local/ai:manageproviders', $context)) {
        $navigation->add(
            get_string('manageproviders', 'local_ai'),
            new moodle_url(
                '/local/ai/index.php',
                [
                    'id' => $context->id,
                    'action' => api::ACTION_MANAGE_PROVIDERS
                ]),
            navigation_node::TYPE_SETTING
        );
    }
}

function local_ai_coursemodule_edit_post_actions($moduleinfo, $course) {
    global $DB;
    //debugging('local_ai_coursemodule_edit_post_actions', DEBUG_DEVELOPER);
    $skip = ['id', 'cmid'];
    // Ideally we need a lock.
    $tx = $DB->start_delegated_transaction();
    $cmconfig = local_ai_get_cmconfig($moduleinfo->coursemodule);
    $cmproperties = $cmconfig->properties_definition();
    foreach($cmproperties as $property => $value) {
        if (in_array($property, $skip)) {
            continue;
        }
        if (isset($moduleinfo->$property)) {
            debugging("Setting {$property} to {$moduleinfo->$property}", DEBUG_DEVELOPER);
            $cmconfig->set($property, $moduleinfo->$property);
        }

    }
    $cmconfig->save();
    $tx->allow_commit();
    var_dump($moduleinfo);
    //exit("local_ai_coursemodule_edit_post_actions");
    return $moduleinfo;
}

/**
 * Gets the Course Module configuration or a blank one
 * with CMID set if it didn't exist.
 * @param $cmid
 * @return cmconfig
 */
function local_ai_get_cmconfig($cmid) {
    global $DB;
    return cmconfig::get_by_cmid($cmid ?? -1);
}

/**
 * @param moodleform_mod $modform
 * @param MoodleQuickForm $mform
 * @return void
 * @throws coding_exception
 */
function local_ai_coursemodule_standard_elements($modform, $mform) {
    global $DB;
    $cm = $modform->get_coursemodule();
    if (is_null($cm)) {
        $cmconfig = local_ai_get_cmconfig(null);
    } else {
        $cmconfig = local_ai_get_cmconfig($cm->id);
    }

    // Adding the rest of mod_xaichat settings, spreading all them into this fieldset
    // ... or adding more fieldsets ('header' elements) if needed for better logic.
    $mform->addElement('header', 'aiprovider', get_string('aiprovider', 'local_ai'));

    $mform->addElement('static', "aiproviderfeatures",
        get_string("aiproviderfeatures", 'local_ai'),
        get_string("aiproviderfeatures_desc", 'local_ai')
    );
    $contextconstraint = "Context id {$modform->get_context()->id} or above";
    $allowchat = true;
    $allowembedding = true;
    $providers = api::get_providers(
        $modform->get_context()->id,
        $allowchat,
        $allowembedding
    );
    $optproviders = [];

    $optproviders['-1'] = get_string('disable', 'local_ai');
    foreach($providers as $provider) {
        $optproviders[$provider->get('id')] = $provider->get('name');
    }
    $mform->addElement('select', 'aiproviderid',
        'Choose Provider',
        $optproviders
    );

    $mform->addHelpButton('aiproviderid', 'aiproviderid', 'local_ai');
    $mform->setDefault('aiproviderid', $cmconfig->get('aiproviderid'));

    $mform->addElement('header', 'cmcontexthdr', get_string('aicontext', 'local_ai'));
    $mform->setExpanded('cmcontexthdr', true);
    $mform->addElement('static', 'config', print_r($cmconfig, true));
    $mform->addElement('advcheckbox', 'allowindex', get_string('allowindex', 'local_ai'));
    $mform->addHelpButton('allowindex', 'allowindex', 'local_ai');
//    $mform->setDefault('allowindex', $cmconfig->get('allowindex'));

    $mform->addElement('textarea', 'aicontext', get_string('aicontext', 'local_ai'));
    $mform->setDefault('aicontext', $cmconfig->get('aicontext'));
    $mform->disabledIf('aicontext', 'allowindex', 'notchecked');

    $mform->addElement('advcheckbox', 'allowsummarise', get_string('allowsummarise', 'local_ai'));
    $mform->addHelpButton('allowsummarise', 'allowsummarise', 'local_ai');

    $mform->addElement('advcheckbox', 'allowexplain', get_string('allowexplain', 'local_ai'));
    $mform->addHelpButton('allowexplain', 'allowexplain', 'local_ai');

    $mform->addElement('advcheckbox', 'allowask', get_string('allowask', 'local_ai'));
    $mform->addHelpButton('allowask', 'allowask', 'local_ai');

    $mform->addElement('advcheckbox', 'allowanswer', get_string('allowanswer', 'local_ai'));
    $mform->addHelpButton('allowanswer', 'allowanswer', 'local_ai');

    $mform->addElement('advcheckbox', 'allowchat', get_string('allowchat', 'local_ai'));
    $mform->addHelpButton('allowchat', 'allowanswer', 'local_ai');

    $mform->addElement('advcheckbox', 'allowtranslate', get_string('allowtranslate', 'local_ai'));
    $mform->addHelpButton('allowanswer', 'allowanswer', 'local_ai');
    $skip = ['id', 'cmid'];
    // Ideally we need a lock.
    $cmproperties = $cmconfig->properties_definition();
    foreach($cmproperties as $property => $value) {
        if (in_array($property, $skip)) {
            continue;
        }
        if ($mform->elementExists($property)) {
            $mform->setDefault($property, $cmconfig->get($property));
        }

    }

}
