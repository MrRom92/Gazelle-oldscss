<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

final class BonusUploadRewardHistory extends AbstractMigration {
    public function up(): void {
        $this->query("
            create extension if not exists btree_gist
        ");
        $this->table('bonus_upload_reward', ['id' => false, 'primary_key' => 'id_bonus_upload_reward'])
            ->addColumn('id_bonus_upload_reward', 'integer', ['identity' => true])
            ->addColumn('id_category', 'integer')
            ->addColumn('low', 'integer')
            ->addColumn('standard', 'integer')
            ->addColumn('high', 'integer')
            ->addColumn('valid', Literal::from('tstzrange'), ['default' => Literal::from("tstzrange('-infinity', 'infinity')")])
            ->addForeignKey('id_category', 'category', 'id_category')
            ->create();
        $this->query("
            alter table bonus_upload_reward
                add constraint bur_valid_excl exclude using gist (id_category with =, valid with &&)
            ");

        $cutoff = '2017-06-18 22:01:44';
        $this->execute("
            insert into bonus_upload_reward
                (low, standard, high, valid, id_category)
            values
                (  0,   0,   0, tstzrange('-infinity', '$cutoff'), (select id_category from category where name = 'Music')),
                (  0,   0,   0, tstzrange('-infinity', '$cutoff'), (select id_category from category where name = 'Applications')),
                (  0,   0,   0, tstzrange('-infinity', '$cutoff'), (select id_category from category where name = 'E-Books')),
                (  0,   0,   0, tstzrange('-infinity', '$cutoff'), (select id_category from category where name = 'Audiobooks')),
                (  0,   0,   0, tstzrange('-infinity', '$cutoff'), (select id_category from category where name = 'E-Learning Videos')),
                (  0,   0,   0, tstzrange('-infinity', '$cutoff'), (select id_category from category where name = 'Comedy')),
                (  0,   0,   0, tstzrange('-infinity', '$cutoff'), (select id_category from category where name = 'Comics')),
                ( 30,  30, 400, tstzrange('$cutoff', 'infinity'), (select id_category from category where name = 'Music')),
                ( 10,  10,  10, tstzrange('$cutoff', 'infinity'), (select id_category from category where name = 'Applications')),
                ( 10,  10,  10, tstzrange('$cutoff', 'infinity'), (select id_category from category where name = 'E-Books')),
                ( 10,  10,  10, tstzrange('$cutoff', 'infinity'), (select id_category from category where name = 'Audiobooks')),
                ( 10,  10,  10, tstzrange('$cutoff', 'infinity'), (select id_category from category where name = 'E-Learning Videos')),
                ( 10,  10,  10, tstzrange('$cutoff', 'infinity'), (select id_category from category where name = 'Comedy')),
                ( 10,  10,  10, tstzrange('$cutoff', 'infinity'), (select id_category from category where name = 'Comics'))
        ");
    }

    public function down(): void {
        $this->table('bonus_upload_reward')->drop()->save();

        // It does no harm to keep btree_gist around.
    }
}
