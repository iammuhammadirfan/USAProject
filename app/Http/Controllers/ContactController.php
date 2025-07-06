<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactFormMessage;
use App\Mail\ContactFormAutoReply;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;


class ContactController extends Controller
{
    public function faq()
    {
        $faqs = DB::table('faqs')
        ->orderBy('id')
        ->get();
        return view('user.faq', compact('faqs'));
    }

    /**
     * Show the contact form page.
     */
    public function show()
    {
        return view('user.contact_us');
    }

    /**
     * Handle the contact form submission.
     */
    public function sendContactForm(Request $request)
    {
        // Validate form data
        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email:rfc,dns',
            'message'     => 'required|string|max:5000',
            'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        // Prepare form data
        $data = $request->only(['first_name', 'last_name', 'email', 'message']);
        $attachment = $request->file('attachment');

        try {
            // Send email to support
            Mail::to('support@thegiftofgroceries.com')
                ->send(new ContactFormMessage($data, $attachment));

            // Send auto-reply to sender (user who submitted the form)
            Mail::to($request->email)
                ->send(new ContactFormAutoReply($data));

            return back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            // Catch any exception and log the error
            \Log::error("Error sending email: " . $e->getMessage());
            return back()->with('error', 'Failed to send message. Please try again later.');
        }
    }
}
