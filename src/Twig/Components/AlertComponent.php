<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('alert')]
final class AlertComponent
{
    public string $type = "info";
    public string $message = "";
    public bool $dismissible = true;
}
