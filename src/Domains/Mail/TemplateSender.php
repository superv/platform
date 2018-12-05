<?php

namespace SuperV\Platform\Domains\Mail;

class TemplateSender
{
    protected $params;

    protected $to;

    protected $bcc;

    /** @var \SuperV\Platform\Domains\Mail\MailTemplate */
    protected $template;

    public function __construct(MailTemplate $template)
    {
        $this->template = $template;
    }

    public function send()
    {
        return $this->sender()->send();
    }

    /**
     * @param mixed $params
     * @return TemplateSender
     */
    public function params($params)
    {
        $this->template->params($this->params = $params);

        return $this;
    }

    /**
     * @param mixed $to
     * @return TemplateSender
     */
    public function to($to)
    {
        $this->to = $to;

        return $this;
    }

    public function getTemplate(): MailTemplate
    {
        return $this->template;
    }

    /**
     * @return mixed|\SuperV\Platform\Domains\Mail\MailSender
     */
    public function sender()
    {
        return MailSender::make()
                         ->setTo($this->getTo())
                         ->setBcc($this->getBcc())
                         ->setSubject($this->template->parseSubject())
                         ->setBody($this->template->parseBody());
    }

    protected function getTo()
    {
        if ($this->to) {
            return $this->to;
        }

        if ($this->template->to) {
            return $this->template->to;
        }

        throw new \Exception('Mail recipient not sent');
    }

    /**
     * @param mixed $bcc
     * @return TemplateSender
     */
    public function bcc($bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBcc()
    {
        return $this->bcc ?: $this->template->bcc;
    }

    /**
     * @param $slug
     * @return static
     */
    public static function template($slug)
    {
        if (! $template = app(MailTemplate::class)->query()->where('slug', $slug)->first()) {
            throw new \Exception("Template with slug [{$slug}] could not be found");
        }

        return new static($template);
    }
}