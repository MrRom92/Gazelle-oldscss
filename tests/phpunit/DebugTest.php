<?php

namespace Gazelle;

use PHPUnit\Framework\TestCase;
use GazelleUnitTest\Helper;

class DebugTest extends TestCase {
    public function testDebugGeneral(): void {
        global $Debug;
        $this->assertCount(6, $Debug->perfInfo(), 'debug-perf-info');
        $this->assertGreaterThan(0.0, $Debug->cpuElapsed(), 'debug-cpu-elapsed');
        $this->assertGreaterThan(0, $Debug->epochStart(), 'debug-epoch-start');
        $this->assertGreaterThan(350, count($Debug->includeList()), 'debug-include-list');
        $this->assertTrue(Helper::recentDate(date('Y-m-d H:i:s', (int)$Debug->epochStart()), 180), 'debug-recent-start');
    }

    public function testCreate(): void {
        $manager  = new Manager\ErrorLog();
        $uri      = '/phpunit.php';
        $errorLog = $manager->create(
            uri:       $uri,
            userId:    0,
            duration:  0.0,
            memory:    0,
            nrQuery:   0,
            nrCache:   0,
            trace:     "a\nb",
            request:   [],
            errorList: [],
        );

        $this->assertGreaterThan(0, $errorLog->id, 'errorlog-create');
        $case = $manager->findById($errorLog->id);
        $this->assertInstanceOf(ErrorLog::class, $case, 'errorlog-find');
        $this->assertEquals($uri, $case->uri(), 'errorlog-uri');
        $this->assertEquals([], $case->request(), 'errorlog-request');
        $this->assertEquals([], $case->errorList(), 'errorlog-errrolist');
        $this->assertEquals(["a", "b"], $case->trace(), 'errorlog-trace');
        $this->assertEquals(
            $case->id,
            $manager->findByDigest("a\nb", [])?->id,
            'errorlog-find-by-digest',
        );

        $this->assertEquals(1, $case->remove(), 'errorlog-remove');
    }

    public function testCase(): void {
        global $Cache;
        $key = 'phpunit_' . randomString();
        $Cache->cache_value($key, 'phpunit', 60);
        $Cache->get_value($key);
        DB::DB()->scalar('select now()');

        global $Debug;
        $case = $Debug->saveCase('phpunit-case-1');
        $this->assertGreaterThan(0, $case->id, 'php-case-is-saved');

        $trace = $case->trace();
        $this->assertCount(1, $trace, 'debug-case-nr-trace');
        $this->assertEquals('phpunit-case-1', $trace[0], 'debug-case-trace');
        // if the next assertion fails, try uncommenting the next line to reset
        // $case->remove(); return;
        $this->assertEquals(1, $case->seen(), 'debug-case-seen');
        $this->assertEquals('cli', $case->uri(), 'debug-case-uri');
        $this->assertEquals(0, $case->userId(), 'debug-case-user-id');
        $this->assertTrue(Helper::recentDate($case->created()), 'debug-case-created');
        $this->assertTrue(Helper::recentDate($case->updated()), 'debug-case-updated');
        $this->assertGreaterThan(0.0, $case->duration(), 'debug-case-duration');
        $this->assertGreaterThan(1000000, $case->memory(), 'debug-case-memory');
        $this->assertGreaterThan(0, $case->nrCache(), 'debug-case-nr-cache');
        $this->assertGreaterThan(0, $case->nrQuery(), 'debug-case-nr-query');
        $this->assertStringContainsString(
            "Case #{$case->id}",
            $case->link(),
            'debug-case-link',
        );
        $this->assertStringContainsString(
            "case={$case->id}",
            $case->location(),
            'debug-case-location',
        );
        $case->remove();
    }

    public function testMark(): void {
        global $Debug;
        $message = 'phpunit-' . randomString();
        $Debug->mark($message);
        $list = array_filter($Debug->markList(), fn ($m) => $m[0] === $message);
        $this->assertCount(1, $list, 'debug-marklist'); /** @phpstan-ignore-line */
        $event = current($list);
        $this->assertCount(4, $event, 'debug-mark-total');
        $this->assertEquals($message, $event[0], 'debug-mark-event');
    }
}
