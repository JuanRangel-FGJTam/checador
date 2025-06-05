<?php

namespace App\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\MessageConverter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
 
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

        try {
            $response = Http::withToken($jwt)->post($host, [
                'from' => $email->getFrom()[0]->getAddress(),
                'to' => $email->getTo()[0]->getAddress(),
                'subject' => $email->getSubject(),
                'message' => $email->getHtmlBody(),
            ]);

            if ($response->failed()) {
                throw new TransportException(
                    sprintf('API request failed with status %d: %s', 
                        $response->status(), 
                        $response->body()
                    )
                );
            }
        } catch (RequestException $e) {
            Log::error('Email API request failed', [
                'error' => $e->getMessage(),
                'to' => $email->getTo()[0]->getAddress(),
                'subject' => $email->getSubject()
            ]);
            
            throw new TransportException('Failed to send email via API: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            Log::error('Unexpected error sending email', [
                'error' => $e->getMessage(),
                'to' => $email->getTo()[0]->getAddress(),
                'subject' => $email->getSubject()
            ]);
            
            throw new TransportException('Unexpected error occurred while sending email: ' . $e->getMessage(), 0, $e);
        }
    }
 
    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'dgtitAPI';
    }
}