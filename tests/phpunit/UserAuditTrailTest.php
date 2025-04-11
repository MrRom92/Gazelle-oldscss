<?php

namespace Gazelle;

use PHPUnit\Framework\TestCase;
use GazelleUnitTest\Helper;
use Gazelle\Enum\UserAuditEvent;

class UserAuditTrailTest extends TestCase {
    protected User $user;
    protected User $admin;

    public function tearDown(): void {
        $this->user->auditTrail()->resetAuditTrail();
        $this->user->remove();
        if (isset($this->admin)) {
            $this->admin->remove();
        }
    }

    public function testAuditTrailCreate(): void {
        $this->user = Helper::makeUser('uat.' . randomString(10), 'uat');
        $this->assertInstanceOf(
            \Gazelle\User\AuditTrail::class,
            $this->user->auditTrail(),
            'uat-instanceof',
        );

        $auditTrail = $this->user->auditTrail();
        $id1 = $auditTrail->addEvent(UserAuditEvent::staffNote, 'phpunit first');
        $this->assertGreaterThan(0, $id1, 'uat-insert');
        $this->assertEquals($id1, $auditTrail->lastEventId(), 'uat-last-event');
        $id2 = $auditTrail->addEvent(UserAuditEvent::staffNote, 'phpunit second');
        $this->assertEquals($id1 + 1, $id2, 'uat-second');

        $this->assertCount(3, $auditTrail->fullEventList(), 'uat-event-list');
        $this->assertEquals(1, $auditTrail->removeEvent($id1), 'uat-event-remove');
        $this->assertCount(2, $auditTrail->fullEventList(), 'uat-new-event-list');

        $this->assertEquals(2, $auditTrail->resetAuditTrail(), 'uat-remove');
        $this->assertCount(0, $auditTrail->fullEventList(), 'uat-reset');
        $this->assertEquals(0, $auditTrail->lastEventId(), 'uat-no-last-event');
    }

    public function testAuditTrailAbsent(): void {
        $this->user = Helper::makeUser('uat.' . randomString(10), 'uat');
        $this->assertFalse($this->user->auditTrail()->hasEvent(UserAuditEvent::mfa), 'uat-event-absent');
    }

    public function testAuditTrailMigrate(): void {
        $this->user = Helper::makeUser('uat.' . randomString(10), 'uat');
        $staffNoteList = [
            '2033-03-03 03:03:03 - three',
            '2022-02-02 02:02:02 - two',
            '2021-01-01 01:01:01 - one',
        ];
        $this->user->setField('AdminComment', implode("\n\n", $staffNoteList))->modify();

        $auditTrail = $this->user->auditTrail();
        $this->assertFalse($this->user->auditTrail()->hasEvent(UserAuditEvent::historical), 'uat-not-yet-migrated');
        $this->assertGreaterThan(0, $auditTrail->migrate(new \Gazelle\Manager\User()), 'uat-migrate');
        $this->assertTrue($this->user->auditTrail()->hasEvent(UserAuditEvent::historical), 'uat-migrated');

        $eventList = $auditTrail->fullEventList();
        $this->assertCount(4, $eventList, 'uat-migrated-event-list');
        $this->assertEquals('three', $eventList[0]['note'], 'uat-event-list-0-note');
        $this->assertEquals('two', $eventList[2]['note'], 'uat-event-list-1-note');
        $this->assertEquals('2022-02-02 02:02:02+00', $eventList[2]['created'], 'uat-event-list-r-created');
    }

    public function testAuditTrailStaffNote(): void {
        $this->user = Helper::makeUser('uat.' . randomString(10), 'uat');
        $auditTrail = $this->user->auditTrail();
        $this->assertFalse($this->user->auditTrail()->hasEvent(UserAuditEvent::historical), 'uat-not-staff-note-migrated');

        $this->user->addStaffNote('admin comment')->modify();
        $this->assertGreaterThan(0, $auditTrail->migrate(new \Gazelle\Manager\User()), 'uat-staff-note-migrated');
        $this->assertEquals(0, $auditTrail->migrate(new \Gazelle\Manager\User()), 'uat-already-migrated');

        // one for the creation, one for the staff note
        $eventList = $auditTrail->fullEventList();
        $this->assertCount(3, $eventList, 'uat-migrated-staff-note-list');
        $this->assertCount(
            2,
            $auditTrail->eventList([
                $eventList[1]['id_user_audit_trail'],
                $eventList[2]['id_user_audit_trail'],
            ]),
            'uat-partial-event-list'
        );
    }

    public function testAuditTrailCreatorStaffNote(): void {
        $this->user  = Helper::makeUser('uat.' . randomString(10), 'uat');
        $this->admin = Helper::makeUser('uat.adm.' . randomString(10), 'uat');
        $this->user->auditTrail()->resetAuditTrail();

        $this->user->setField('AdminComment', date('Y-m-d H:m:s') . " - One by {$this->admin->username()}")->modify();
        $this->assertGreaterThan(0, $this->user->auditTrail()->migrate(new \Gazelle\Manager\User()), 'uat-staff-note-migrated');
        $this->assertEquals("One.", $this->user->auditTrail()->fullEventList()[0]['note'], 'uat-staff-note-one');

        $this->user->auditTrail()->resetAuditTrail();
        $this->user->setField('AdminComment', date('Y-m-d H:m:s') . " - Two by {$this->admin->username()}\nReason: Out on the weekend")->modify();
        $this->assertGreaterThan(0, $this->user->auditTrail()->migrate(new \Gazelle\Manager\User()), 'uat-staff-multinote-migrated');
        $this->assertEquals("Two.\nReason: Out on the weekend", $this->user->auditTrail()->fullEventList()[0]['note'], 'uat-staff-note-two');
    }

    public function testAuditTrailModify(): void {
        $this->user = Helper::makeUser('uat.' . randomString(10), 'uat');
        $auditTrail = $this->user->auditTrail();
        $auditTrail->resetAuditTrail();
        $id1 = $auditTrail->addEvent(UserAuditEvent::staffNote, 'phpunit first');
        $id2 = $auditTrail->addEvent(UserAuditEvent::staffNote, 'phpunit second');
        $this->assertEquals(
            2,
            $auditTrail->modifyEventList([$id1, $id2], 'phpunit rewrite', $this->user),
            'uat-modify-history'
        );
        $eventList = $auditTrail->fullEventList();
        $this->assertCount(1, $eventList, 'uat-after-modify');
        $this->assertEquals($this->user->id, $eventList[0]['id_user_creator'], 'uat-creator-after-modify');
        $this->assertEquals(
            1,
            $auditTrail->modifyEventList([$id1], 'phpunit second rewrite', $this->user),
            'uat-remodify-history'
        );
        $this->assertEquals(
            1,
            $auditTrail->modifyEventList([$id1], '', $this->user),
            'uat-remove-history'
        );
        $this->assertCount(0, $auditTrail->fullEventList(), 'uat-all-gone');
    }

    public function testUserUpdate(): void {
        $this->user  = Helper::makeUser('uat.' . randomString(10), 'uat');
        $this->assertEquals($this->user->updated(), $this->user->created(), 'user-updated-is-created');
        $checkpoint = $this->user->checkpoint();
        sleep(1); // ensure created != updated
        $this->user->setField('Username', $this->user->username() . 'x')->modify();
        $this->assertNotEquals($this->user->updated(), $this->user->created(), 'user-updated-after-created');
        $this->assertNotEquals($checkpoint, $this->user->checkpoint(), 'user-new-checkpoint');

        $checkpoint = $this->user->checkpoint();
        $this->user->auditTrail()->addEvent(UserAuditEvent::staffNote, 'update');
        $this->assertNotEquals($checkpoint, $this->user->checkpoint(), 'user-newer-checkpoint');
    }
}
