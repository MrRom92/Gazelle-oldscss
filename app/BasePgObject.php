<?php

namespace Gazelle;

abstract class BasePgObject extends BaseObject {
    public function modify(): bool {
        if (!$this->dirty()) {
            return false;
        }
        $set = implode(
            ', ',
            [...array_map(fn($f) => "$f = ?", array_keys($this->updateField))]
        );
        $args = [...array_values($this->updateField)];
        $args[] = $this->id();
        $rowCount = $this->pg()->prepared_query(
            "update /* BasePgObject */ " . static::tableName
                . " set $set where " . static::pkName . " = ?",
            ...$args
        );
        $success = ($rowCount === 1);
        if ($success) {
            $this->flush();
        }
        return $success;
    }
}
