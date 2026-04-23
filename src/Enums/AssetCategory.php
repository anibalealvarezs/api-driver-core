<?php

namespace Anibalealvarezs\ApiDriverCore\Enums;

enum AssetCategory: string
{
    case IDENTITY = 'identity';   // Top-level owner/connection (Ad Account, Store, Connection)
    case PAGEABLE = 'pageable';   // Assets with URL/Slug identity (FB Page, GSC Domain, Site)
    case CAMPAIGN = 'campaign';   // High-level marketing container (Campaign, Email Flow)
    case GROUPING = 'grouping';   // Mid-level organizational unit (AdSet, Folder)
    case UNIT = 'unit';           // Granular data units (Post, Ad, Media item)
    case RESOURCE = 'resource';   // Shared assets (Creative, Audience)
}
