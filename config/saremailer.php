<?php

return [
    'wsdl' => env('SARE_WSDL', 'https://www.enewsletter.pl/api/server.php?wsdl'),
    'private_key' => env('SARE_PRIVATE_KEY', storage_path('app/private.key')),
    'certificate' => env('SARE_CERTIFICATE', storage_path('app/certificate.pem')),
    'exceptions' => true,
];