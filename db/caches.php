<?php
defined('MOODLE_INTERNAL') || die();

$definitions = [
    'apicalls' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'ttl' => 3600, // Time to live - 1 hour
    ],
];