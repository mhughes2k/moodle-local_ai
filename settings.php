<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_ai_settings', new lang_string('pluginname', 'local_ai')));
    $settings = new admin_settingpage('local_ai', new lang_string('aisettings', 'local_ai'));
    $ADMIN->add('local_ai_settings', $settings);
    $providerurl = new \moodle_url('/local/ai/index.php');
    $ADMIN->add('local_ai_settings',
        new admin_externalpage(
            'local_ai_provider',
            get_string('providers', 'local_ai'),
            $providerurl->out(),
            'moodle/site:config'
        ));
    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext(
            'local_ai/systemprompt',
            new lang_string('systemprompt', 'local_ai'),
            new lang_string('systemprompt_help', 'local_ai'),
            new lang_string('defaultsystemprompt', 'local_ai')
        ));


    }
}

