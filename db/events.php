<?

use core\event\course_module_created;
use core\event\course_module_updated;


$observers = [
    [
        'eventname' => course_module_updated::class,
        'callback' => "local_ai_observer::course_module_updated"
    ],
    [
        'eventname' => course_module_created::class,
        'callback' => "local_ai_observer::course_module_updated"
    ]
];