<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

final class BonusItem extends AbstractMigration {
    public function up(): void {
        // An earlier migration created this table as a mirror of the Mysql
        // table, but no code was ever written to make use of it. Therefore
        // delete if it exists. If this migration is rolled back, it does
        // not bother to recreate the table, which means that when it is
        // migrated a second time, the table will no longer be present.
        // Hence "if exists".
        $this->execute('drop table if exists bonus_item');

        $this->query("create type bonus_item_freq_t as enum ('one-time', 'recurring')");
        $this->table('bonus_item', ['id' => false, 'primary_key' => 'id_bonus_item'])
            ->addColumn('id_bonus_item', 'integer', ['identity' => true])
            ->addColumn('sequence', 'integer')
            ->addColumn('price', 'integer')
            ->addColumn('amount', 'integer')
            ->addColumn('userclass_min', 'integer')
            ->addColumn('userclass_free', 'integer')
            ->addColumn('frequency', Literal::from('bonus_item_freq_t'))
            ->addColumn('prepare', 'boolean')
            ->addColumn('label', 'string', ['length' => 32])
            ->addColumn('title', 'string', ['length' => 64])
            ->save();

        $this->execute('create unique index bi_l_uidx on bonus_item (label);');
        $this->execute('create unique index bi_s_uidx on bonus_item (sequence);');

        $this->query(<<<END_SQL
            insert into bonus_item (
                id_bonus_item,
                sequence,
                price,
                amount,
                userclass_min,
                userclass_free,
                frequency,
                label,
                title,
                prepare
            )
            select "ID",
                sequence,
                "Price",
                coalesce("Amount", 0),
                coalesce("MinClass", 1),
                "FreeClass",
                cast(
                    case when "Label" in ('seedbox', 'file-count')
                        then 'one-time'
                        else 'recurring'
                    end
                    as bonus_item_freq_t
                ),
                "Label",
                case
                    when "Title" = 'Buy an Invite'               then 'Personal Invite'
                    when "Title" = 'Buy a Personal Collage Slot' then 'Personal Collage'
                    when "Title" = 'Unlock the Seedbox viewer'   then 'Seedbox Viewer'
                    else "Title"
                end,
                case when "Label" ~ '^(other-|title-bb-[yn])'
                    then true
                    else false
                end
            from relay.bonus_item
            END_SQL
        );
    }

    public function down(): void {
        $this->table('bonus_item')->drop()->save();
        $this->query('drop type bonus_item_freq_t');
    }
}
