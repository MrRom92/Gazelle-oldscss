<?php

namespace Gazelle\Enum;

enum UserMatchSort: int {
    case score     = 0;
    case firstDate = 1;
    case lastDate  = 2;
}
