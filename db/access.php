<?php
$capabilities = [
    // Allow user to add AI Provider to contexts.
    'local/ai:addprovider' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ]
    ],
    // Allow user to remove AI Provider from contexts.
    'local/ai:removeprovider' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ]
    ],
    // Allow user to modify AI Providers in contexts.
    'local/ai:manageproviders' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ]
    ],
    'local/ai:selectcategory' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ]
    ],
];
