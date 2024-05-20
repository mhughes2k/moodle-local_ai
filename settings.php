<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_ai', new lang_string('pluginname', 'local_ai'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext(
        'local_ai/systemprompt',
        new lang_string('systemprompt', 'local_ai'),
        new lang_string('systemprompt_help', 'local_ai'),
        new lang_string('defaultsystemprompt', 'local_ai')
    ));
}

