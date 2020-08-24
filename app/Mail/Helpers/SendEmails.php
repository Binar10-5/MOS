<?php

namespace App\Mail\Helpers;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEmails extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    protected $new_view;
    protected $new_subject;
    protected $from_send;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($new_view, $new_subject , $from_send, $data)
    {
        $this->new_view     = $new_view;
        $this->new_subject  = $new_subject;
        $this->from_send     = $from_send;
        $this->data     = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view($this->new_view)
        ->from($this->from_send, 'MOS')
        ->subject($this->new_subject)
        ->with('data', $this->data);
    }
}
