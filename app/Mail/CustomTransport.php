<?php

namespace App\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Illuminate\Support\Facades\Http;
 
class CustomTransport extends AbstractTransport
{
    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $jwt = config('mail.mailers.dgtitAPI.jwt');
        $host = config('mail.mailers.dgtitAPI.host');

        Http::withToken($jwt)->post($host, [
            'from' => $email->getFrom()[0]->getAddress() ,
            'to' => $email->getTo()[0]->getAddress(),
            'subject' => $email->getSubject(),
            'message' => $email->getHtmlBody(),
        ]);
    }
 
    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'dgtitAPI';
    }
}