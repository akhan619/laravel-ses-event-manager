<?php

namespace Akhan619\LaravelSesEventManager\App;

use Akhan619\LaravelSesEventManager\Exceptions\MultipleRecipientsInEmailException;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\SentMessage;
use Illuminate\Mail\Mailables\Address;

class CustomMailer extends Mailer
{
    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param mixed $users
     *
     * @return Akhan619\LaravelSesEventManager\App\CustomPendingMail
     */
    public function to($users, $name = null)
    {
        if (!is_null($name) && is_string($users)) {
            $users = new Address($users, $name);
        }

        return (new CustomPendingMail($this))->to($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param mixed $users
     *
     * @return Akhan619\LaravelSesEventManager\App\CustomPendingMail
     */
    public function cc($users, $name = null)
    {
        if (!is_null($name) && is_string($users)) {
            $users = new Address($users, $name);
        }

        return (new CustomPendingMail($this))->cc($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param mixed $users
     *
     * @return Akhan619\LaravelSesEventManager\App\CustomPendingMail
     */
    public function bcc($users, $name = null)
    {
        if (!is_null($name) && is_string($users)) {
            $users = new Address($users, $name);
        }

        return (new CustomPendingMail($this))->bcc($users);
    }

    /**
     * Send a new message using a view.
     *
     * @param \Illuminate\Contracts\Mail\Mailable|string|array $view
     * @param array                                            $data
     * @param \Closure|string|null                             $callback
     *
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($view, array $data = [], $callback = null)
    {
        if ($view instanceof MailableContract) {
            return $this->sendMailable($view);
        }

        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        [$view, $plain, $raw] = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        if (!is_null($callback)) {
            $callback($message);
        }

        $this->addContent($message, $view, $plain, $raw, $data);

        // If a global "to" address has been set, we will set that address on the mail
        // message. This is primarily useful during local development in which each
        // message should be delivered into a single mail address for inspection.
        if (isset($this->to['address'])) {
            $this->setGlobalToAndRemoveCcAndBcc($message);
        }

        // Next we will determine if the message should be sent. We give the developer
        // one final chance to stop this message and then we will send it to all of
        // its recipients. We will then fire the sent event for the sent message.
        $symfonyMessage = $message->getSymfonyMessage();

        // Check if multiple recipients exist.
        if (count($symfonyMessage->getTo()) > 1 || !empty($symfonyMessage->getCc()) || !empty($symfonyMessage->getBcc())) {
            throw new MultipleRecipientsInEmailException('Multiple recipients present. Please ensure there is only one recipient. Make sure Cc and Bcc fields are empty.');
        }

        if ($this->shouldSendMessage($symfonyMessage, $data)) {
            $symfonySentMessage = $this->sendSymfonyMessage($symfonyMessage);

            if ($symfonySentMessage) {
                $sentMessage = new SentMessage($symfonySentMessage);

                $this->dispatchSentEvent($sentMessage, $data);

                return $sentMessage;
            }
        }
    }
}
