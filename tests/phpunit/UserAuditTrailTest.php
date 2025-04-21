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

    public function testAuditTrailStaffNote(): void {
        $this->user = Helper::makeUser('uat.' . randomString(10), 'uat');
        $auditTrail = $this->user->auditTrail();
        $this->assertFalse($this->user->auditTrail()->hasEvent(UserAuditEvent::historical), 'uat-not-staff-note-migrated');

        $this->user->addStaffNote('admin comment')->modify();

        // one for the creation, one for the staff note
        $eventList = $auditTrail->fullEventList();
        $this->assertCount(2, $eventList, 'uat-migrated-staff-note-list');
        $this->assertCount(
            2,
            $auditTrail->eventList([
                $eventList[0]['id_user_audit_trail'],
                $eventList[1]['id_user_audit_trail'],
            ]),
            'uat-partial-event-list'
        );
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
        Helper::sleepTick(); // ensure created != updated
        $this->user->setField('Username', $this->user->username() . 'x')->modify();
        $this->assertNotEquals($this->user->updated(), $this->user->created(), 'user-updated-after-created');
        $this->assertNotEquals($checkpoint, $this->user->checkpoint(), 'user-new-checkpoint');

        $checkpoint = $this->user->checkpoint();
        $this->user->auditTrail()->addEvent(UserAuditEvent::staffNote, 'update');
        $this->assertNotEquals($checkpoint, $this->user->checkpoint(), 'user-newer-checkpoint');
    }
}
