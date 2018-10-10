<?php

namespace SuperV\Platform\Domains\Mail;

use SuperV\Modules\Manage\Domains\Email\MailTemplate;

class TemplateSender
{
    protected $params;

    protected $to;

    /**
     * @var \SuperV\Modules\Manage\Domains\Email\MailTemplate
     */
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
     * @param $slug
     * @return static
     */
    public static function template($slug)
    {
        if (! $template = MailTemplate::withSlug($slug)) {
            throw new \Exception("Template with slug [{$slug}] could not be found");
        }

        return new static($template);
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

    /**
     * @return mixed|\SuperV\Platform\Domains\Mail\MailSender
     */
    public function sender()
    {
        return MailSender::make()
                         ->setTo($this->to)
                         ->setSubject($this->template->parseSubject())
                         ->setBody($this->template->parseBody());
    }
}