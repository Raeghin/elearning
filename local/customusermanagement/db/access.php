<?php

defined('MOODLE_INTERNAL') || die;

$capabilities = array(
    'mod/customusermanagement:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'admin' => CAP_ALLOW,
        )
    ),
);

