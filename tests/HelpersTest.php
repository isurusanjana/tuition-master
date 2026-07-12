<?php

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    public function testEscapesHtmlSpecialCharacters(): void
    {
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', e('<script>alert(1)</script>'));
    }

    public function testEscapeHandlesNull(): void
    {
        $this->assertSame('', e(null));
    }

    public function testUrlBuildsAbsoluteUrl(): void
    {
        $this->assertStringStartsWith(APP_URL, url('/dashboard'));
        $this->assertStringEndsWith('/dashboard', url('/dashboard'));
    }

    public function testUrlTrimsLeadingSlash(): void
    {
        $this->assertSame(url('dashboard'), url('/dashboard'));
    }

    public function testAssetBuildsAssetPath(): void
    {
        $this->assertStringContainsString('/assets/css/custom.css', asset('css/custom.css'));
    }

    public function testFormatDateReturnsEmptyForNull(): void
    {
        $this->assertSame('', format_date(null));
    }

    public function testFormatDateFormatsCorrectly(): void
    {
        $this->assertSame('09 Jul 2026', format_date('2026-07-09'));
    }
}
