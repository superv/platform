<?php

namespace SuperV\Platform\Domains\Mail;

interface MailTemplate
{
    public function parseBody();

    public function parseSubject();

    public function parse($target);

    public function params($params);
}