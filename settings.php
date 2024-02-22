<?php


defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/assign/adminlib.php');

// $name = new lang_string('generateverification', 'local_ai');
// $description = new lang_string('generateverificationdesc', 'local_ai');
// $settings->add(new admin_setting_configcheckbox('assign/showrecentsubmissions',
//                                                 $name,
//                                                 $description,
//                                                 0));
if ($hassiteconfig) {
    $settings = new admin_settingpage('local_ai', 'Local AI Settings');

    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtextarea(
        'local_ai/globalcontext',
        'Global AI Context',
        'Global AI Context will be provided to all LLM chats',
        'You are a helpful AI Assistant.',
        PARAM_TEXT
    ));
}