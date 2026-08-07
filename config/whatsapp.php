<?php

return [
    'enabled'    => env('WHATSAPP_GATEWAY_ENABLED', true),
    'url'        => env('WHATSAPP_GATEWAY_URL', 'https://wagateway.surakana.my.id'),
    'auth'       => env('WHATSAPP_GATEWAY_AUTH', 'admin:admin'),
    'device_id'  => env('WHATSAPP_DEVICE_ID', '8a744703-b90a-4690-b911-b1b8f2523963'),
    'verify_ssl' => env('WHATSAPP_GATEWAY_VERIFY_SSL', true),
    'timeout'    => env('WHATSAPP_GATEWAY_TIMEOUT', 30),
];
