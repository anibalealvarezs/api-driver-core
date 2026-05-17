<?php

    namespace Anibalealvarezs\ApiDriverCore\Enums;

    enum InstanceTier: int
    {
        case RESERVATION = 0;
        case MINIMAL = 1;
        case BASIC = 2;
        case MASTER = 3;
        case POWERED = 4;
        case OVERPOWERED = 5;
    }
