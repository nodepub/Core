<?php

namespace NodePub\Core\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugController
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function indexAction()
    {
        return new Response($this->app['twig']->render('@np-admin/debug_layout.twig'));
    }

    public function testEmail(Request $request)
    {
        $emailMessageBody = $request->get('messageBody', 'This is an email test');

        try {
            $emailMessage = $this->app['error_message'];
            $emailMessage->setBody($emailMessageBody, 'text/plain');

            if ($status = $this->app['mailer']->send($emailMessage, $failures)) {
                $statusMessage = 'Message was successfully sent';
            } else {
                $statusMessage = 'Message could not sent to: '.implode(', ', $failures);
            }
        } catch (Exception $e) {
            $statusMessage = 'Error while attempting to send email';
            $this->app['monolog']->addError(array(
                'message'    => $statusMessage,
                'error'      => $e->getMessage(),
            ));
        }
    }
}