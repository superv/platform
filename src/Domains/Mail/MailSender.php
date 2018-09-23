<?php

namespace SuperV\Platform\Domains\Mail;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Mail\Message;
use Illuminate\Support\HtmlString;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class MailSender
{
    protected $view;

    protected $layout = 'pof.layout';

    protected $theme = 'default';

    protected $componentPaths = [];

    protected $to;

    protected $action;

    protected $subject;

    protected $body;

    public function __construct(ViewFactory $view, array $options = [])
    {
        $this->view = $view;
        $this->theme = $options['theme'] ?? 'default';
    }

    public function send($to = null)
    {
        if ($to) {
            $this->setTo($to);
        }

        app(\Illuminate\Mail\Mailer::class)->send(
            $this->buildView(),
            $this->buildViewData(),
            $this->buildMessage()
        );
    }

    protected function buildViewData()
    {
        return [
            'body' => $this->body,
        ];
    }

    public function render($view, $inliner = null)
    {
        $contents = $this->view->make($view, $this->buildViewData())->render();

        $view = new HtmlString(($inliner ?: new CssToInlineStyles)->convert(
            $contents, $this->view->make('mail.html.themes.'.$this->theme)->render()
        ));

        return $view;
    }
    public function setAction($text, $url)
    {
        $this->action = compact('text', 'url');

        return $this;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public static function make()
    {
        return resolve(self::class);
    }

    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    protected function buildView(): array
    {
        return [
            'html' => $this->render($this->layout),
        ];
    }

    protected function buildMessage(): \Closure
    {
        return function (Message $mailMessage) {
            $mailMessage->to($this->to);
            $mailMessage->subject($this->subject);
        };
    }
}