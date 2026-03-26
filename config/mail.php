<?php

return [
    'default'  => env('MAIL_MAILER', 'smtp'),
    'mailers'  => [
        'smtp' => [
            'transport'  => 'smtp',
            'scheme'     => env('MAIL_SCHEME'),
            'url'        => env('MAIL_URL'),
            'host'       => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port'       => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username'   => env('MAIL_USERNAME'),
            'password'   => env('MAIL_PASSWORD'),
            'timeout'    => null,
        ],
        'ses'       => ['transport' => 'ses'],
        'postmark'  => ['transport' => 'postmark'],
        'sendmail'  => ['transport' => 'sendmail', 'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i')],
        'log'       => ['transport' => 'log', 'channel' => env('MAIL_LOG_CHANNEL')],
        'array'     => ['transport' => 'array'],
        'failover'  => ['transport' => 'failover', 'mailers' => ['smtp', 'log']],
        'roundrobin'=> ['transport' => 'roundrobin', 'mailers' => ['ses', 'postmark']],
    ],
    'from'     => ['address' => env('MAIL_FROM_ADDRESS', 'noreply@naukaridarpan.com'), 'name' => env('MAIL_FROM_NAME', 'Naukaridarpan')],
];
