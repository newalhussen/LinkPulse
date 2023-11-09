<?php

namespace Tests\Unit;

use App\Services\ClickTracker;
use App\Services\CodeGenerator;
use PHPUnit\Framework\TestCase;

class ClickTrackerTest extends TestCase
{
    public function test_device_classification(): void
    {
        $t = new ClickTracker;
        $this->assertSame('mobile', $t->device('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Mobile/15E148'));
        $this->assertSame('mobile', $t->device('Mozilla/5.0 (Linux; Android 14; Pixel 8) Mobile Safari'));
        $this->assertSame('tablet', $t->device('Mozilla/5.0 (iPad; CPU OS 17_0)'));
        $this->assertSame('tablet', $t->device('Mozilla/5.0 (Linux; Android 13; SM-X700) Safari'));
        $this->assertSame('desktop', $t->device('Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120'));
    }

    public function test_bot_detection(): void
    {
        $t = new ClickTracker;
        $this->assertTrue($t->isBot(null));
        $this->assertTrue($t->isBot('Mozilla/5.0 (compatible; bingbot/2.0)'));
        $this->assertFalse($t->isBot('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605'));
    }

    public function test_referrer_host_extraction(): void
    {
        $t = new ClickTracker;
        $this->assertSame('example.com', $t->referrerHost('https://Example.com/a/b?token=secret#x', 'short.test'));
        $this->assertNull($t->referrerHost('https://short.test/dash', 'short.test'));
        $this->assertNull($t->referrerHost(null, 'short.test'));
        $this->assertNull($t->referrerHost('not a url', 'short.test'));
    }

    public function test_reserved_words_are_recognised(): void
    {
        $g = new CodeGenerator;
        $this->assertTrue($g->isReserved('LOGIN'));
        $this->assertFalse($g->isReserved('promo'));
    }
}
