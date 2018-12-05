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

    protected $bcc;

    protected $actions = [];

    protected $subject;

    protected $body;

    protected $renderedView;

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

    protected function buildView(): array
    {
        $contents = $this->view->make($this->layout, $this->buildViewData())->render();

        $html = (new CssToInlineStyles)->convert(
            $contents, $this->view->make('mail.html.themes.'.$this->theme)->render()
        );

        $this->renderedView = new HtmlString($html);

        return [
            'html' => $this->renderedView,
        ];
    }

    protected function buildViewData()
    {
        return array_filter([
            'body'    => $this->body,
            'actions' => $this->actions,
        ]);
    }

    protected function buildMessage(): \Closure
    {
        return function (Message $mailMessage) {
            $mailMessage->to($this->to);
            $mailMessage->subject($this->subject);

            if ($this->bcc) {
                $mailMessage->bcc($this->bcc);
            }
        };
    }

    public function setAction($text, $url)
    {
        return $this->addAction($text, $url);
    }

    public function addAction($text, $url, $color = 'blue')
    {
        $this->actions[] = compact('text', 'url', 'color');

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

    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @param mixed $bcc
     * @return MailSender
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

    public function getRenderedView()
    {
        return $this->renderedView;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public static function make()
    {
        return resolve(self::class);
    }
}