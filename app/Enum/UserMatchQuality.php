<?php

namespace Gazelle\Enum;

enum UserMatchQuality: int {
    case full    = 0;
    case partial = 1;
    case weak    = 2;
    case none    = 3;
}
