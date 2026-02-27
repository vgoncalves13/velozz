# Google Sheets Import Feature

## Overview

The Google Sheets Import feature allows users to import leads directly from a published Google Sheet without downloading the file first.

## How It Works

### Architecture

1. **GoogleSheetsHelper** - Helper class that handles:
   - URL validation
   - Spreadsheet ID extraction
   - GID (sheet ID) extraction
   - Conversion to CSV export URL
   - File download

2. **ImportLeads Page** - Extended wizard with:
   - Radio button to choose between "Upload File" or "Google Sheets URL"
   - TextInput for Google Sheets URL
   - "Fetch Data" button to download and process
   - Same mapping and settings steps

3. **ProcessImport Job** - Reuses existing logic:
   - Processes downloaded CSV like any other file
   - No changes needed

### Flow

```
User enters Google Sheets URL
    ↓
System validates URL
    ↓
System extracts spreadsheet ID + GID
    ↓
System converts to CSV export URL
    ↓
System downloads CSV file
    ↓
System saves to storage/app/imports/
    ↓
System processes like regular CSV import
```

## User Instructions

### Making a Google Sheet Public

1. Open your Google Sheet
2. Click **File** → **Share** → **Publish to web**
3. Choose the specific sheet you want to import (or "Entire Document")
4. Select format: **Web page** (the system will convert to CSV)
5. Click **Publish**
6. Copy the URL

### Importing

1. Go to **Import Leads** page in the CRM
2. Click **Start Import** button
3. Select **Google Sheets URL** option
4. Paste the URL in the text field
5. Click **Fetch Data** button
6. Map columns to Lead fields (auto-mapping will suggest matches)
7. Configure deduplication rules and tags
8. Click **Start Import**

## URL Formats Supported

### Regular Share URL
```
https://docs.google.com/spreadsheets/d/{SPREADSHEET_ID}/edit
```

### Specific Sheet URL (with GID)
```
https://docs.google.com/spreadsheets/d/{SPREADSHEET_ID}/edit#gid={SHEET_ID}
```

### Published URL
```
https://docs.google.com/spreadsheets/d/{SPREADSHEET_ID}/pubhtml
```

All formats are automatically converted to CSV export URL:
```
https://docs.google.com/spreadsheets/d/{SPREADSHEET_ID}/export?format=csv&gid={SHEET_ID}
```

## Database

The `imports` table already supports this feature:
- `type` enum includes `'url'` option
- `filename` stores the path to downloaded CSV
- All other processing is identical to file uploads

## Security Considerations

### ✅ Safe

- Only public/published sheets can be imported
- No authentication tokens are stored
- Downloaded files are processed like any uploaded file
- Files are deleted after successful processing

### ⚠️ Limitations

- Sheet must be published publicly
- No private/authenticated sheets (would require OAuth)
- Users should be aware that published sheets are visible to anyone with the URL

## Future Enhancements

Possible improvements for future versions:

1. **OAuth Integration**
   - Allow importing from private sheets
   - User authenticates with Google account
   - Store refresh tokens securely

2. **Auto-Sync**
   - Schedule automatic imports from same URL
   - Detect changes and import only new rows
   - Webhook support from Google Sheets

3. **Multiple Sheets**
   - Import multiple sheets from same spreadsheet
   - Combine data from different tabs

4. **Column Mapping Presets**
   - Save mapping configurations
   - Reuse for repeated imports from similar sheets

## Error Handling

The system handles common errors:

- **Invalid URL**: Shows validation error with instructions
- **Sheet not public**: Error message explains how to publish
- **Empty sheet**: Warning that no data was found
- **Network errors**: Timeout or connection issues are logged
- **Malformed data**: Same validation as file uploads

## Code References

### Key Files

- `app/Helpers/GoogleSheetsHelper.php` - Core Google Sheets logic
- `app/Filament/Client/Pages/ImportLeads.php` - UI wizard
- `app/Jobs/ProcessImport.php` - Processing (unchanged)
- `resources/views/filament/client/pages/import-leads.blade.php` - Help text

### Helper Methods

```php
GoogleSheetsHelper::isValidGoogleSheetsUrl(string $url): bool
GoogleSheetsHelper::extractSpreadsheetId(string $url): ?string
GoogleSheetsHelper::extractGid(string $url): ?string
GoogleSheetsHelper::convertToCsvUrl(string $url): string
GoogleSheetsHelper::downloadAsCsv(string $url, string $tenantId): string
```

## Testing

To test the feature:

1. Create a Google Sheet with sample lead data
2. Publish it to web (File → Share → Publish to web)
3. Copy the URL
4. Go to Import Leads page in CRM
5. Choose "Google Sheets URL"
6. Paste URL and click "Fetch Data"
7. Verify preview shows correct data
8. Complete import and verify leads are created

### Test URLs

Use these public test spreadsheets:

- Simple test: `https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit`
- Multi-sheet: Include `#gid=0` for first sheet

## Troubleshooting

### "Failed to download Google Sheets"

**Cause**: Sheet is not published publicly

**Solution**:
1. Open sheet
2. File → Share → Publish to web
3. Click Publish
4. Try again

### "Invalid Google Sheets URL"

**Cause**: URL format not recognized

**Solution**:
- Use the URL from browser address bar when viewing the sheet
- Must be `docs.google.com/spreadsheets/d/...`

### "Empty file"

**Cause**: Selected sheet has no data or only headers

**Solution**:
- Verify sheet has data rows
- Check if correct sheet is selected (try adding `#gid=...`)
