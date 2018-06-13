<?php

namespace Jkujawski\SareMailer;

use Illuminate\Mail\Transport\Transport;
use SoapClient;
use SoapFault;
use Swift_Mime_SimpleMessage;

class SareTransport extends Transport
{

    /**
     * Guzzle HTTP client.
     *
     * @var SoapClient
     */
    protected $client;

    protected $loggedIn = false;

    /**
     * Create a new SareClient transport instance.
     *
     * @param  SoapClient $client
     */
    public function __construct(SoapClient $client)
    {
        $this->client = $client;
    }

    public function login()
    {
        if ( false === $this->loggedIn ) {
            if ($this->client->Login()->login !== 1) {
                throw new SoapFault('0000', 'Login failure');
            }
        }
    }

    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->login();

        $from = $this->getFrom($message);

        try {
            $script = '
            <!--sare
                $data["subject"] = "'.addslashes($message->getSubject()).'";
                $data["from"] = \''.$from.'\';
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
        } catch (SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
        }
    }

    /**
     * Get the "from" payload field for the SOAP request.
     *
     * @param  \Swift_Mime_SimpleMessage  $message
     * @return string
     */
    protected function getFrom(Swift_Mime_SimpleMessage $message)
    {
        return collect($message->getFrom())->map(function ($display, $address) {
            return $display ? '"'.$display.'"'." <{$address}>" : $address;
        })->values()->implode(',');
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