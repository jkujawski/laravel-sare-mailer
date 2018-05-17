<?php

namespace Jkujawski\SareMailer;

use Illuminate\Mail\Transport\Transport;
use SoapClient;
use Swift_Mime_SimpleMessage;

class SareTransport extends Transport
{

    /**
     * Guzzle HTTP client.
     *
     * @var SoapClient
     */
    protected $client;

    /**
     * Create a new SareClient transport instance.
     *
     * @param  SoapClient $client
     */
    public function __construct(SoapClient $client)
    {
        $this->client = $client;
    }

    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        try {
            $script = '
            <!--sare
                $data["subject"] = "'.addslashes($message->getSubject()).'";
                $data["from"] = "'.collect($message->getFrom())->keys()->first().'";
                $data["replyto"] = "'.collect($message->getReplyTo())->keys()->first().'";
                $ret = mail_send("'.collect($message->getTo())->keys()->first().'", $data, "'.$this->inline(addslashes($message->getBody())).'");
                if ($ret) {
                    print("ok");
                } else {
                    print("error");
                }
            sare-->';

            //  $contentHTML = "<html><head></head><body><h1>Treść wiadomości</h1></body></html>";
            //  $contentTXT = "Treść wiadomości";
            //
            //  $ret = mail_send($email, $parametry, $contentHTML, $contentTXT);
            //
            $result = $this->client->Execute($script, false);
            dd($result);
        } catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
        }
    }

    protected function inline($content)
    {
        // Strip newline characters.
        $content = str_replace(chr(10), " ", $content);
        $content = str_replace(chr(13), " ", $content);
        // Replace single quotes.
        $content = str_replace(chr(145), chr(39), $content);
        $content = str_replace(chr(146), chr(39), $content);
        // Return the result.
        return $content;
    }

}