<?php

return [
    'app_env' => 'dev',
    'hosts' => [
        'vw' => 'vwn.local',
        'rc' => 'rc.local',
        'hc' => 'hc.local',
        'fb' => 'fb.local'
    ],
    'admin' => [
//        'host' => 'control.voyeurweb.com',
        'host' => 'admin.vwn.local',
    ],
    'db' => [
        'active_masters' => [1],
        'active_slaves' => [1,2],
        'masters' => [
            'master1' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'user' => 'root',
                'password' => '111111',
                'database' => 'vwlocaldb',
            ]
        ],
        'slaves' => [
            /*'slave1' => [
                'driver' => 'mysql',
                'host' => '172.16.24.78',
                'user' => 'mysqlrw',
                'password' => 'leAtkJlG3Erqb5J',
                'database' => 'voyeur',
            ],*//*
            'slave2' => [
                'driver' => 'mysql',
                'host' => '172.16.24.77',
                'user' => 'mysqlrw',
                'password' => 'leAtkJlG3Erqb5J',
                'database' => 'voyeur',
            ], */
            'slave1' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'user' => 'root',
                'password' => '111111',
                'database' => 'vwlocaldb',
            ],
            'slave2' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'user' => 'root',
                'password' => '111111',
                'database' => 'vwlocaldb',
            ]
        ],
    ],

    'redis' => [
        'host' => '127.0.0.1',
        'port' => '6379',
    ],
    'mail' => [
        'servers' => [
            'mail.voyeurweb.com' => [
                'username' => 'admin',
                'password' => 'PwBU4FkedNPD4r',
                'per_hour' => 300
            ],
            'mail2.voyeurweb.com' => [
                'username' => 'contris',
                'password' => 'ckL2LCzycnQiryiaSPZT',
                'per_hour' => 150
            ],
        ],
    ],
    'mediaPath' => '/home/www/_media/_sites/all/www/_modules/contrib/albums',
    'imagesPath' => 'images',
    'videosPath' => 'videos',
    'avatarPath' => '/home/www/newuploads',
    'messagesServer' => '67.215.232.134',
    'beanstalkd' => [
        'host' => '172.16.24.74'
    ],
    'onesignal' => [
        '1' => [
            'app_id' => 'e366b3dc-55c5-4560-8c46-04d3950246c8',
            'api_key' => 'ZDZiMWQ1NjgtNzY5YS00ODQ2LWFlNTItOTgzZDQ2ZWY1YTA5'
        ],
        '2' => [
            'app_id' => 'faf1ef7f-0f35-43a4-a809-c50d7aecf108',
            'api_key' => 'NzBjYzQyNTktYWJiYi00ZTRlLTg4MDMtNDRmYWM5NmNjNzA5'
        ],
        '3' => [
            'app_id' => '3dac934a-027c-4e84-828b-5779f34965e0',
            'api_key' => 'OTEyOGZjZmQtYmQ1Ni00MmJiLTk4NjAtZTU0MTgyYWJmNGIx'
        ],
        '4' => [
            'app_id' => '520e39e2-9847-4195-849c-9564ea95577d',
            'api_key' => 'YjU4MzczOTUtZGJjNC00OGNmLTgzNjktOGUyYzEyZGM3YTI5'
        ],
    ],
    'throttle' => [
        'reqNumber' => 10,
        'retryAfter' => 5
    ]
];
