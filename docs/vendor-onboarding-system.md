# Vendor Onboarding System

## Overview

The vendor onboarding system implements a secure, automated process for reviewing and approving vendor applications. The system includes document upload, validation server integration, and administrative oversight.

## Architecture

### Database Schema
- **vendor_applications** table stores all application data
- Applications have UUID primary keys for security
- Status token system for public status checking
- File paths for uploaded documents
- Validation data from external server

### Key Components

1. **VendorApplication Model** (`app/Models/VendorApplication.php`)
   - Handles file storage and naming
   - Status management and validation
   - Relationships with User model

2. **VendorValidationService** (`app/Services/VendorValidationService.php`)
   - Application submission workflow
   - HTTP client for Java validation server communication
   - Stores validation results returned by Java server (no validation logic)
   - User account creation after approval

3. **VendorApplicationController** (`app/Http/Controllers/VendorApplicationController.php`)
   - Public application endpoints
   - Admin management interface
   - Document download functionality

4. **VendorApplicationPolicy** (`app/Policies/VendorApplicationPolicy.php`)
   - Authorization logic for different user types
   - Public access for application submission
   - Admin-only access for management functions

## Workflow

### 1. Application Submission
1. Applicant fills out form with personal/business information
2. Uploads bank statement and trading license (PDF only)
3. System generates unique application ID and status token
4. Files are stored in organized directory structure
5. Application is sent to validation server for automated review

### 2. Validation Process
1. Application and documents are sent to Java validation server
2. **Java server performs all validation logic** (document analysis, financial checks, etc.)
3. Java server returns status and validation results
4. Laravel application simply stores the response from Java server
5. No validation logic is implemented in Laravel - it's purely a client

### 3. Admin Review (if needed)
1. Admin can view all applications in dashboard
2. Download and review uploaded documents
3. Approve/reject applications manually
4. Schedule site visits if required
5. Retry validation for failed submissions

### 4. User Account Creation
1. Only approved applications can have user accounts created
2. Temporary password is generated
3. User is assigned 'supplier' role
4. Email notification sent (TODO)

## API Endpoints

### Public Endpoints
- `POST /apply` - Submit new application
- `GET /application/status` - Check application status by token

### Admin Endpoints (requires authentication)
- `GET /admin/vendor-applications` - List all applications
- `GET /admin/vendor-applications/{id}` - View specific application
- `POST /admin/vendor-applications/{id}/approve` - Approve application
- `POST /admin/vendor-applications/{id}/reject` - Reject application
- `POST /admin/vendor-applications/{id}/schedule-visit` - Schedule site visit
- `GET /admin/vendor-applications/{id}/download/{type}` - Download documents
- `POST /admin/vendor-applications/{id}/retry-validation` - Retry validation

## File Storage

Files are stored in `storage/app/applications/` with organized structure:
```
applications/
├── {application_id}/
│   ├── bank-statement.pdf
│   └── trading-license.pdf
```

## Configuration

Add these environment variables to your `.env`:

```bash
# Validation Server Configuration
VALIDATION_SERVER_URL=http://localhost:8080
VALIDATION_SERVER_TIMEOUT=30
```

## Status Flow

```
pending → (validation server) → approved/rejected/under_review
                                      ↓
                              (admin review if needed)
                                      ↓
                                 approved/rejected
                                      ↓
                               (user account created)
```

## Security Features

1. **File Validation**: Only PDF files accepted, size limits enforced
2. **Authorization**: Policy-based access control
3. **Unique Tokens**: Status checking requires unique token
4. **File Isolation**: Each application has isolated storage directory
5. **Audit Trail**: All actions are logged

## Installation

1. Run migration: `php artisan migrate`
2. Create storage directory: `mkdir storage/app/applications`
3. Configure validation server URL in `.env`
4. Register policy in `AuthServiceProvider` if not auto-discovered

## Testing

The system includes comprehensive validation and error handling:
- File upload validation
- Database integrity checks
- Validation server communication
- Error logging and recovery

## Future Enhancements

- [ ] Email notifications for status changes
- [ ] Frontend application form
- [ ] Admin dashboard interface
- [ ] Document preview functionality
- [ ] Bulk application management
- [ ] Integration with external CRM systems

## Troubleshooting

### Common Issues

1. **File Upload Errors**: Check file permissions on `storage/app/applications`
2. **Validation Server Timeout**: Increase `VALIDATION_SERVER_TIMEOUT`
3. **Policy Not Found**: Ensure policy is registered in `AuthServiceProvider`
4. **Route Issues**: Run `php artisan route:cache` to clear route cache

### Debug Commands

```bash
# Check routes
php artisan route:list --name=vendor

# Test configuration
php artisan tinker --execute="dd(config('services.validation_server'));"

# Check file permissions
ls -la storage/app/applications/
```

## Logging

All operations are logged with context:
- Application submissions
- Validation server communication
- Admin actions (approve, reject, etc.)
- Error conditions

Check `storage/logs/laravel.log` for detailed information.
