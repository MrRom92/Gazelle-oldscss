<?php

namespace Gazelle;

trait Pg {
    protected static \Gazelle\DB\Pg $pgro; // R/O access
    protected static \Gazelle\DB\Pg $pg;   // R/W access

    public function pgro(): \Gazelle\DB\Pg {
        return self::$pgro ??= new \Gazelle\DB\Pg(PG_RO_DSN);
    }

    public function pg(): \Gazelle\DB\Pg {
        return self::pgStatic();
    }

    // disgusting hack required for \View class
    public static function pgStatic(): \Gazelle\DB\Pg {
        return self::$pg ??= new \Gazelle\DB\Pg(PG_RW_DSN);
    }
}
