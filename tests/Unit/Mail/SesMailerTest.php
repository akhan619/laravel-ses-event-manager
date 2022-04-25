<?php

namespace Akhan619\LaravelSesEventManager\Tests\Unit\Mail;

use Akhan619\LaravelSesEventManager\App\CustomMailer;
use Akhan619\LaravelSesEventManager\Exceptions\MultipleRecipientsInEmailException;
use Akhan619\LaravelSesEventManager\Implementations\SesMailer;
use Akhan619\LaravelSesEventManager\Mocking\TestMailable;
use Akhan619\LaravelSesEventManager\Mocking\TestMailableWithMultipleRecipients;
use Akhan619\LaravelSesEventManager\Mocking\TestMailableWithRecipient;
use Akhan619\LaravelSesEventManager\Mocking\TestMailableWithTrait;
use Akhan619\LaravelSesEventManager\Mocking\TestQueuedMailable;
use Akhan619\LaravelSesEventManager\Tests\UnitTestCase;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Queue;
use \Mockery as m;

class SesMailerTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function sesMailerIsAvailableToUse()
    {
        $this->assertTrue(app()->bound('SesMailer'));
    }
    
    /**
     * @test
     */
    public function sesMailerIsSetToUseSesTransport()
    {
        $this->assertEquals('ses', app()->make('SesMailer')->getSymfonyTransport()->__toString());
    }

    protected function setExtraOptions($app)
    {
        $app['config']->set('laravel-ses-event-manager.ses_options.options', ['someKey' => 'someValue']);
        $app['config']->set('laravel-ses-event-manager.ses_options.region', 'Wadiya');
    }
    
    /**
     * @test
     * @define-env setExtraOptions
     */
    public function optionsAreLoadedIntoSesMailer()
    {
        $transport = app()->make('SesMailer')->getSymfonyTransport();
        $this->assertEquals(['someKey' => 'someValue'], $transport->getOptions());
        $this->assertEquals('Wadiya', $transport->ses()->getRegion());
    }    

    protected function setMailTestingEnvironment($app)
    {
        $app['config']->set('laravel-ses-event-manager.ses_options.key', 'key1');
        $app['config']->set('laravel-ses-event-manager.ses_options.secret', 'secret1');
        $app['config']->set('laravel-ses-event-manager.ses_options.region', 'region1');
        $app['config']->set('laravel-ses-event-manager.ses_options.options.ConfigurationSetName', 'non-existent-set');
    }
    
    /**
     * @test
     * @define-env setMailTestingEnvironment
     */
    public function sesMailerCanSendTestMailableWithToMethod()
    {
        $sesMailer = m::mock(SesMailer::class)->makePartial();
        $customMailer = m::mock(CustomMailer::class)->makePartial();
        $sentMessage = m::mock(SentMessage::class);
        $mailable = new TestMailable();

        $sesMailer->shouldReceive('getCustomMailer')->andReturn($customMailer);

        $customMailer->shouldReceive('send')->once()->with($mailable)->andReturnUsing(function() use ($customMailer, $mailable) {
            return $customMailer->sendMailable($mailable);
        });
        $customMailer->shouldReceive('send')->once()->andReturn($sentMessage);
        $sesMailer->shouldReceive('createEmailRecord')->once();

        $sesMailer->to('john@doe.com')->send($mailable);

        $this->assertTrue(true);
    }
    
    /**
     * @test
     * @define-env setMailTestingEnvironment
     */
    public function sesMailerCanSendTestMailableWithRecipientDeclaredInMailable()
    {
        $sesMailer = m::mock(SesMailer::class)->makePartial();
        $customMailer = m::mock(CustomMailer::class)->makePartial();
        $sentMessage = m::mock(SentMessage::class);
        $mailable = new TestMailableWithRecipient();

        $sesMailer->shouldReceive('getCustomMailer')->andReturn($customMailer);

        $customMailer->shouldReceive('send')->once()->with($mailable)->andReturnUsing(function() use ($customMailer, $mailable) {
            return $customMailer->sendMailable($mailable);
        });
        $customMailer->shouldReceive('send')->once()->andReturn($sentMessage);
        $sesMailer->shouldReceive('createEmailRecord')->once();

        $sesMailer->send($mailable);

        $this->assertTrue(true);
    }
    
    /**
     * @test
     * @define-env setMailTestingEnvironment
     */
    public function sesMailerCanSendMailsInSequence()
    {
        $sesMailer = m::mock(SesMailer::class)->makePartial();
        $customMailer = m::mock(CustomMailer::class)->makePartial();
        $sentMessage = m::mock(SentMessage::class);
        $mailable1 = new TestMailable();
        $mailable2 = new TestMailable();

        $sesMailer->shouldReceive('getCustomMailer')->andReturn($customMailer);

        $customMailer->shouldReceive('send')->once()->with($mailable1)->andReturnUsing(function() use ($customMailer, $mailable1) {
            return $customMailer->sendMailable($mailable1);
        });
        $customMailer->shouldReceive('send')->once()->with($mailable2)->andReturnUsing(function() use ($customMailer, $mailable2) {
            return $customMailer->sendMailable($mailable2);
        });
        $customMailer->shouldReceive('send')->twice()->andReturn($sentMessage);
        $sesMailer->shouldReceive('createEmailRecord')->twice();

        $sesMailer->to('john@doe.com')->send($mailable1);
        $sesMailer->to('jane@doe.com')->send($mailable2);

        $this->assertTrue(true);
    }
    
    /**
     * @test
     * @define-env setMailTestingEnvironment
     */
    public function sesMailerThrowsErrorOnMultipleRecipientsSetThroughCc()
    {
        $sesMailer = m::mock(SesMailer::class)->makePartial();
        $mailable = new TestMailable();

        $sesMailer->shouldReceive('isMessageSent')->twice()->andReturn(false);
        $sesMailer->shouldNotReceive('createEmailRecord');

        $this->expectException(MultipleRecipientsInEmailException::class);
        $sesMailer->to('john@doe.com')->cc('jane@doe.com')->send($mailable);
    }
    
    /**
     * @test
     * @define-env setMailTestingEnvironment
     */
    public function sesMailerThrowsErrorOnMultipleRecipientsSetThroughBcc()
    {
        $sesMailer = m::mock(SesMailer::class)->makePartial();
        $mailable = new TestMailable();

        $sesMailer->shouldReceive('isMessageSent')->twice()->andReturn(false);
        $sesMailer->shouldNotReceive('createEmailRecord');

        $this->expectException(MultipleRecipientsInEmailException::class);
        $sesMailer->to('john@doe.com')->bcc('jane@doe.com')->send($mailable);
    }
    
    /**
     * @test
     * @define-env setMailTestingEnvironment
     */
    public function sesMailerThrowsErrorOnMultipleRecipientsSetThroughMailable()
    {
        $sesMailer = m::mock(SesMailer::class)->makePartial();
        $mailable = new TestMailableWithMultipleRecipients();

        $sesMailer->shouldNotReceive('isMessageSent');
        $sesMailer->shouldNotReceive('createEmailRecord');

        $this->expectException(MultipleRecipientsInEmailException::class);
        $sesMailer->send($mailable);
    }
    
    /**
     * @test
     * @define-env setMailTestingEnvironment
     */
    public function sesMailerCanQueueAQueuedMailable()
    {
        Queue::fake();
        $sesMailer = app()->make('SesMailer');
        $mailable = new TestQueuedMailable();

        Queue::assertNothingPushed();
        $sesMailer->to('john@doe.com')->send($mailable);
        Queue::assertPushed(SendQueuedMailable::class, 1);
    }
    
    /**
     * @test
     * @define-env setMailTestingEnvironment
     */
    public function sesMailerCanQueueWithQueueMethod()
    {
        Queue::fake();
        $sesMailer = app()->make('SesMailer');
        $mailable = new TestMailable();

        Queue::assertNothingPushed();
        $sesMailer->to('john@doe.com')->queue($mailable);
        Queue::assertPushed(SendQueuedMailable::class, 1);
    }
    
    /**
     * @test
     * @define-env setMailTestingEnvironment
     */
    public function queuedMailWithTraitIsSentUsingSesMailer()
    {
        Queue::fake();
        $sesMailer = app()->make('SesMailer');
        $mailable = new TestMailableWithTrait();

        Queue::assertNothingPushed();
        $sesMailer->to('john@doe.com')->queue($mailable);
        Queue::assertPushed(function(SendQueuedMailable $job) {
            return ($job->displayName() === TestMailableWithTrait::class);
        });
    }
}