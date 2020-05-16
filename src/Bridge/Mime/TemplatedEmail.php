<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Bridge\Mime;

class TemplatedEmail extends \Symfony\Bridge\Twig\Mime\TemplatedEmail
{
    public function getHtmlTemplate(): ?string
    {
        return '@GbereSimpleAuth/emails/template.html.twig';
    }

    public function getTextTemplate(): ?string
    {
        return '@GbereSimpleAuth/emails/template.txt.twig';
    }
}
