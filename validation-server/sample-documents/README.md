# Sample Document Testing Instructions

## Overview
This folder contains sample documents that will PASS all validation checks in the BeanTrack Vendor Validation Server.

## Files Created
1. `TEST001_bank-statement.txt` - Sample bank statement content
2. `TEST001_trading-license.txt` - Sample trading license content

## Validation Requirements Met

### Bank Statement (`TEST001_bank-statement.txt`)
✅ **Name Validation**: Contains "John Doe Coffee Enterprise" (matches applicant name)
✅ **Available Balance**: 8,500,000 UGX (exceeds minimum 5,000,000)
✅ **Total Credits**: 15,000,000 UGX (exceeds minimum 10,000,000)
✅ **Format**: Contains exact patterns the code searches for:
   - `AVAILABLE BALANCE: 8500000.00`
   - `TOTAL CREDITS: 15000000.00`

### Trading License (`TEST001_trading-license.txt`)
✅ **Registration Number**: CM123456789 (matches pattern "CM" + 6+ digits)
✅ **Business Name**: Contains "John Doe Coffee Enterprise" (matches business name)
✅ **Expiry Date**: January 15, 2027 (future date, still valid)
✅ **Format**: Contains exact pattern the code searches for:
   - `License Expiry Date: 15th January 2027`

## How to Use for Testing

### Step 1: Convert to PDF
Since the validation service expects PDF files, convert these text files to PDF:

**Using Microsoft Word/Google Docs:**
1. Open each .txt file
2. Copy the content
3. Paste into Word/Google Docs
4. Save/Export as PDF with the same filename but .pdf extension

**Using Online Converters:**
1. Use any text-to-PDF converter
2. Upload the .txt files
3. Download as PDFs

### Step 2: Rename Files
Ensure the PDF files follow the expected naming convention:
- `TEST001_bank-statement.pdf`
- `TEST001_trading-license.pdf`

### Step 3: Place Files in Correct Location
The validation service expects files to be accessible at the paths you provide in the API call. Make sure the PDFs are in a location your server can access.

### Step 4: Test API Call
Start the java server using the command in the terminal: cd "your project file path"\validation-server; ./mvnw spring-boot:run  
eg. cd c:\Users\USER\Desktop\Coding\beantrack\validation-server;./mvnw spring-boot:run

Use these parameters for testing:

```bash
curl -X POST http://localhost:8080/api/vendors/apply \
  -d "applicantId=TEST001" \
  -d "name=John Doe Coffee Enterprise" \
  -d "email=john@coffeenterprise.ug" \
  -d "phoneNumber=+256700123456" \
  -d "bankStatement=/path/to/TEST001_bank-statement.pdf" \
  -d "tradingLicense=/path/to/TEST001_trading-license.pdf" \
  -d "businessName=John Doe Coffee Enterprise"
```

### Step 5: Expected Results
With these sample documents, you should get:
1. Initial response: `"status": "under_review"`
2. After async validation: `"status": "pending"` (for manual HR approval)
3. Email notification about successful validation and visit scheduling

## Testing Variations

### To Test Rejection Scenarios:
1. **Low Balance**: Change `AVAILABLE BALANCE: 3000000.00` (below 5M minimum)
2. **Low Credits**: Change `TOTAL CREDITS: 8000000.00` (below 10M minimum)
3. **Wrong Name**: Change account holder name to something different
4. **Invalid Registration**: Change `CM123456789` to `XX123456`
5. **Expired License**: Change expiry date to `15th January 2023`
6. **Wrong Business Name**: Change business name in license

### To Test Different Applicants:
Create new versions with different:
- Applicant IDs (VA00002, VA00003, etc.)
- Names and business names
- But keep the same validation patterns and thresholds

## Notes
- The validation service removes commas from numbers, so both "8,500,000" and "8500000" work
- Date format must be exact: "15th January 2027" (with ordinal suffix)
- Registration number is case-insensitive
- Business name matching is case-insensitive
