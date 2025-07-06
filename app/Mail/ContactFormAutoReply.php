<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ContactFormAutoReply extends Mailable
{
    public $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return \Illuminate\Mail\Mailable
     */
    public function build()
    {
        // Define the 'from' address for auto-reply (using values from the .env file or config)
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->view('emails.contact_auto_reply')  // Email view for the auto-reply message
                    ->subject('Thank You for Contacting Us') // Subject for the auto-reply email
                    ->with('data', $this->data); // Pass the form data to the view (optional for displaying in email)
    }
}
