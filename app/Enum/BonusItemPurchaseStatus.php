<?php

namespace Gazelle\Enum;

enum BonusItemPurchaseStatus {
    case success;
    case insufficientFunds;
    case declined;
    case forbidden;
    case alreadyPurchased;
    case incomplete;
}
