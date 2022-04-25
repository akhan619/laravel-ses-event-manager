<?php

namespace Akhan619\LaravelSesEventManager\Traits;

use Illuminate\Container\Container;
use Illuminate\Contracts\Mail\Factory as MailFactory;

trait QueueForCustomMailer
{
    /**
     * Send the message using the given mailer.
     *
     * @param  \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer  $mailer
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($mailer)
    {
        return $this->withLocale($this->locale, function () use ($mailer) {
            Container::getInstance()->call([$this, 'build']);

            $mailer = $mailer instanceof MailFactory
                            ? app()->make('SesMailer')
                            : $mailer;

            return $mailer->send($this->buildView(), $this->buildViewData(), function ($message) {
                $this->buildFrom($message)
                     ->buildRecipients($message)
                     ->buildSubject($message)
                     ->buildTags($message)
                     ->buildMetadata($message)
                     ->runCallbacks($message)
                     ->buildAttachments($message);
            });
        });
    }
}