<?php

namespace Anibalealvarezs\ApiDriverCore\Enums;

/**
 * Constants for the different types of digital accounts that can be associated with a ChanneledAccount.
 */
enum HierarchyType: string
{
    case MARKETING = 'marketing';
    case PAGE = 'page';
    case POST = 'post';
}
