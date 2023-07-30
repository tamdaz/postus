<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('list_suggestion_users')]
final class ListSuggestionUsersComponent
{
    public array $suggestionUsers;
}
