<?php

namespace Gazelle;

use PHPUnit\Framework\TestCase;

class LogfileSummaryTest extends TestCase {
    public function testSummary(): void {
        $logfileSummary = new LogfileSummary([
            'error'    => [UPLOAD_ERR_OK],
            'name'     => ['valid_log_eac.log'],
            'tmp_name' => [__DIR__ . '/../fixture/valid_log_eac.log'],
        ]);

        $this->assertTrue($logfileSummary->checksum(), 'logfilesummary-checksum');
        $this->assertEquals('1', $logfileSummary->checksumStatus(), 'logfilesummary-checksum-status');
        $this->assertEquals(100, $logfileSummary->overallScore(), 'logfilesummary-overall-score');
        $this->assertCount(1, $logfileSummary->all(), 'logfilesummary-all');
        $this->assertEquals(1, $logfileSummary->total(), 'logfilesummary-total');
    }

    public function testDuplicateFilesIgnored(): void {
        $logfileSummary = new LogfileSummary([
            'error'    => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            'name'     => ['valid_log_eac.log', 'valid_log_eac.log'],
            'tmp_name' => [__DIR__ . '/../fixture/valid_log_eac.log', __DIR__ . '/../fixture/valid_log_eac.log'],
        ]);

        $this->assertTrue($logfileSummary->checksum(), 'logfilesummary-checksum');
        $this->assertEquals('1', $logfileSummary->checksumStatus(), 'logfilesummary-checksum-status');
        $this->assertEquals(100, $logfileSummary->overallScore(), 'logfilesummary-overall-score');
        $this->assertCount(1, $logfileSummary->all(), 'logfilesummary-all');
        $this->assertEquals(1, $logfileSummary->total(), 'logfilesummary-total');
    }

    public function testDuplicateHashesIgnored(): void {
        $logfileSummary = new LogfileSummary([
            'error'    => [UPLOAD_ERR_OK],
            'name'     => ['valid_log_eac.log'],
            'tmp_name' => [__DIR__ . '/../fixture/valid_log_eac.log'],
        ], [hash_file(DIGEST_ALGO, __DIR__ . '/../fixture/valid_log_eac.log')]);

        $this->assertCount(0, $logfileSummary->all(), 'logfilesummary-all');
        $this->assertEquals(0, $logfileSummary->total(), 'logfilesummary-total');
    }
}
