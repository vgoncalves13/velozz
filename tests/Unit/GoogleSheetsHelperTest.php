<?php

namespace Tests\Unit;

use App\Helpers\GoogleSheetsHelper;
use PHPUnit\Framework\TestCase;

class GoogleSheetsHelperTest extends TestCase
{
    public function test_validates_correct_google_sheets_url(): void
    {
        $validUrls = [
            'https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit',
            'https://docs.google.com/spreadsheets/d/abc123xyz/edit#gid=0',
            'https://docs.google.com/spreadsheets/d/test-id-123/pubhtml',
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue(
                GoogleSheetsHelper::isValidGoogleSheetsUrl($url),
                "Failed to validate: {$url}"
            );
        }
    }

    public function test_rejects_invalid_urls(): void
    {
        $invalidUrls = [
            'https://google.com',
            'https://docs.google.com/document/d/123/edit',
            'not-a-url',
            'ftp://docs.google.com/spreadsheets/d/123/edit',
        ];

        foreach ($invalidUrls as $url) {
            $this->assertFalse(
                GoogleSheetsHelper::isValidGoogleSheetsUrl($url),
                "Should reject: {$url}"
            );
        }
    }

    public function test_extracts_spreadsheet_id(): void
    {
        $url = 'https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit';
        $id = GoogleSheetsHelper::extractSpreadsheetId($url);

        $this->assertEquals('1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms', $id);
    }

    public function test_returns_null_for_invalid_spreadsheet_url(): void
    {
        $url = 'https://google.com/invalid';
        $id = GoogleSheetsHelper::extractSpreadsheetId($url);

        $this->assertNull($id);
    }

    public function test_extracts_gid_when_present(): void
    {
        $url = 'https://docs.google.com/spreadsheets/d/abc123/edit#gid=123456';
        $gid = GoogleSheetsHelper::extractGid($url);

        $this->assertEquals('123456', $gid);
    }

    public function test_returns_null_when_gid_not_present(): void
    {
        $url = 'https://docs.google.com/spreadsheets/d/abc123/edit';
        $gid = GoogleSheetsHelper::extractGid($url);

        $this->assertNull($gid);
    }

    public function test_converts_to_csv_url_without_gid(): void
    {
        $url = 'https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit';
        $csvUrl = GoogleSheetsHelper::convertToCsvUrl($url);

        $this->assertEquals(
            'https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/export?format=csv',
            $csvUrl
        );
    }

    public function test_converts_to_csv_url_with_gid(): void
    {
        $url = 'https://docs.google.com/spreadsheets/d/abc123/edit#gid=456';
        $csvUrl = GoogleSheetsHelper::convertToCsvUrl($url);

        $this->assertEquals(
            'https://docs.google.com/spreadsheets/d/abc123/export?format=csv&gid=456',
            $csvUrl
        );
    }

    public function test_throws_exception_for_invalid_url_conversion(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        GoogleSheetsHelper::convertToCsvUrl('https://google.com/invalid');
    }
}
