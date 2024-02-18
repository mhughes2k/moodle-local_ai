<?php
namespace local_ai;

use core\files\curl_security_helper_base;

class security_helper extends curl_security_helper_base {   
    function url_is_blocked($url) {
        // TODO this needs to check the AI API Url is legitimate.
        return false;
    }

    function get_blocked_url_string() {
        return "URL is blocked";
    }
}