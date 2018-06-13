<?php

namespace Jkujawski\SareMailer;

use Illuminate\Mail\TransportManager as BaseTransportManager;
use SoapClient;

class TransportManager extends BaseTransportManager
{
    protected function createSareDriver()
    {
        $config = [
            'trace' => 1,
            'exceptions' => config('saremailer.exceptions'),
//            'cache_wsdl'=>WSDL_CACHE_NONE,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'local_pk' => config('saremailer.private_key'),
                    'local_cert' => config('saremailer.certificate'),
//                    'allow_self_signed' => true,
//                    'verify_peer' => false,
//                    'verify_peer_name' => false,
                ]
            ])
        ];

        $client = new SoapClient(config('saremailer.wsdl'), $config);

        return new SareTransport(
            $client
        );
    }
}