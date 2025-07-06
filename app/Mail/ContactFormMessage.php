<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ContactFormMessage extends Mailable
{
    public $data;
    public $attachment;

    public function __construct($data, $attachment = null)
    {
        $this->data = $data;
        $this->attachment = $attachment;
    }

    public function build()
    {
        $email = $this->from(config('mail.from.address'), config('mail.from.name'))  // Ensure "From" address
                      ->view('emails.contact_form')
                      ->subject('New Contact Form Submission')
                      ->with('data', $this->data);

        if ($this->attachment) {
            $email->attach($this->attachment->getPathname(), [
                'as' => $this->attachment->getClientOriginalName(),
                'mime' => $this->attachment->getMimeType(),
            ]);
        }

        return $email;
    }
}

