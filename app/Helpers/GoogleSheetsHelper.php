<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleSheetsHelper
{
    /**
     * Validate if URL is a valid Google Sheets URL
     */
    public static function isValidGoogleSheetsUrl(string $url): bool
    {
        $pattern = '/^https:\/\/docs\.google\.com\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/';

        return preg_match($pattern, $url) === 1;
    }

    /**
     * Extract spreadsheet ID from Google Sheets URL
     */
    public static function extractSpreadsheetId(string $url): ?string
    {
        $pattern = '/\/d\/([a-zA-Z0-9-_]+)/';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract GID (sheet ID) from URL if present
     */
    public static function extractGid(string $url): ?string
    {
        $pattern = '/[#&]gid=([0-9]+)/';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Convert Google Sheets URL to CSV export URL
     */
    public static function convertToCsvUrl(string $url): string
    {
        $spreadsheetId = self::extractSpreadsheetId($url);
        $gid = self::extractGid($url);

        if (! $spreadsheetId) {
            throw new \InvalidArgumentException('Invalid Google Sheets URL');
        }

        $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";

        if ($gid) {
            $csvUrl .= "&gid={$gid}";
        }

        return $csvUrl;
    }

    /**
     * Download Google Sheets as CSV and save to storage
     *
     * @return string Path to saved file
     */
    public static function downloadAsCsv(string $url, string $tenantId): string
    {
        $csvUrl = self::convertToCsvUrl($url);

        try {
            $response = Http::timeout(30)->get($csvUrl);

            if (! $response->successful()) {
                throw new \Exception('Failed to download Google Sheets. Make sure the sheet is published as public.');
            }

            $content = $response->body();

            if (empty($content)) {
                throw new \Exception('Downloaded file is empty');
            }

            // Generate filename
            $spreadsheetId = self::extractSpreadsheetId($url);
            $filename = "imports/google-sheets-{$spreadsheetId}-{$tenantId}-".time().'.csv';

            // Save to storage
            Storage::disk('local')->put($filename, $content);

            Log::info('Google Sheets downloaded successfully', [
                'url' => $url,
                'filename' => $filename,
                'size' => strlen($content),
            ]);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Error downloading Google Sheets', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get instructions for making a Google Sheet public
     */
    public static function getPublicInstructions(): string
    {
        return 'To import from Google Sheets, you need to publish your sheet:
1. Open your Google Sheet
2. Click "File" → "Share" → "Publish to web"
3. Choose the sheet you want to import
4. Click "Publish"
5. Copy the URL and paste it here';
    }
}
