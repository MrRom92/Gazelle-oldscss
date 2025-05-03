<?php

declare(strict_types=1);

namespace Gazelle\DB;

abstract class AbstractTable extends \Gazelle\BaseObject {
    public function __construct(
        public readonly string $name,
    ) {}

    public function link(): string {
        return "<a href=\"{$this->url()}\">{$this->name}</a>";
    }

    /* The usual design pattern would be to have a Table manager
     * and look up the table by name. But that would add a fair
     * amount of effort for little gain, so instead an exists()
     * method can be used to validate the object does in fact
     * point a real table.
     */
    abstract public function exists(): bool;

    /* The CREATE TABLE string */
    abstract public function definition(): string;

    /* metadata about index reads */
    abstract public function indexRead(): array;

    /* metadata about table reads */
    abstract public function tableRead(): array;

    /* general statistics of the table */
    abstract public function stats(): array;

    /* none of the derived classes perform any caching */
    public function flush(): static {
        return $this;
    }
}
