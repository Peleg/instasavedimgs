<?php
return [
    'commands' => [
        new \Psy\Command\ParseCommand,
    ],

    'defaultIncludes' => [
        __DIR__ . '/autoload.php',
    ],

    'startupMessage' => sprintf('<info>%s</info>', shell_exec('uptime')),
];