<?php

/**
 * A lot of this is based on the Oauth2 issuers.php file.
 */
require_once("../config.php");

if (!debugging("Debug Mode", DEBUG_DEVELOPER)) {
    throw new moodle_exception('notpermitted');
    exit();
}
echo \html_writer::start_tag("pre");
use core_ai\api;
$providers = api::get_providers(11);

var_dump($providers);


echo \html_writer::end_tag("pre");
