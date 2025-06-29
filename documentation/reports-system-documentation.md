# BeanTrack Reports System Documentation

## Overview
The BeanTrack Reports System is a comprehensive reporting solution designed for coffee supply chain management. It provides three main functionalities: Report Library & Configuration, Historical Reports Archive, and Ad-Hoc Report Generator.

## Features

### 1. Report Library & Configuration
**Purpose:** Manage all available report templates and their scheduled deliveries.

**Key Features:**
- **Report Templates Table** with columns:
  - Report Name & Description
  - Type (PDF/Excel/Dashboard)
  - Frequency (Daily/Weekly/Monthly/Quarterly)
  - Recipients
  - Last Generated date
  - Status (Active/Paused/Failed)
  - Actions (Edit/Generate/View/Delete)

- **Search & Filter Capabilities:**
  - Search by report name or description
  - Filter by report type (PDF, Excel, Dashboard)
  - Filter by frequency (Daily, Weekly, Monthly, Quarterly)

- **Create New Report Schedule** - 5-step wizard:
  1. **Choose Report Template:** Pre-defined templates for various business needs
  2. **Configure Recipients:** Internal roles, specific suppliers, custom emails
  3. **Set Schedule:** Frequency, specific day/time
  4. **Choose Format:** PDF or Excel
  5. **Review & Save:** Final confirmation before creation

### 2. Historical Reports Archive
**Purpose:** Access previously generated reports and manage deliveries.

**Key Features:**
- **Generated Reports Table** with columns:
  - Report Name
  - Generated For (recipient information)
  - Date Generated
  - Format (PDF/Excel/CSV)
  - File Size
  - Status (Success/Failed)
  - Actions (Download/View Online/Resend)

- **Advanced Filtering:**
  - Search by report name or recipient
  - Filter by recipient type
  - Date range selection
  - Status filtering

### 3. Ad-Hoc Report Generator
**Purpose:** Generate custom, one-off reports for specific data analysis needs.

**Key Features:**
- **Report Type Selection:** 
  - Sales Data
  - Inventory Movements
  - Order History
  - Production Batches
  - Supplier Performance
  - Quality Metrics

- **Dynamic Filtering:** Context-aware filters based on selected report type
- **Date Range Selection:** Flexible from/to date picking
- **Multiple Output Formats:** PDF, CSV, Excel
- **Background Processing:** Reports are generated asynchronously with email notifications

## Technical Architecture

### Backend Components

#### Models
- **Report Model** (`App\Models\Report`)
  - Primary key: String ID with 'R' prefix (e.g., R00001)
  - Relationships: Belongs to User (recipient)
  - Attributes: name, description, type, frequency, format, recipients, schedule details, status, content

#### Controllers
- **ReportController** (`App\Http\Controllers\ReportController`)
  - Main dashboard view
  - Report library data management
  - Historical reports management
  - Ad-hoc report generation
  - CRUD operations for report schedules

#### Database Schema
```sql
CREATE TABLE reports (
    id VARCHAR(6) PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    type ENUM('inventory', 'order_summary', 'performance', 'adhoc'),
    recipient_id VARCHAR(6),
    frequency ENUM('daily', 'weekly', 'monthly', 'quarterly', 'once'),
    format ENUM('pdf', 'excel', 'csv', 'dashboard') DEFAULT 'pdf',
    recipients TEXT,
    schedule_time TIME,
    schedule_day VARCHAR(255),
    status ENUM('active', 'paused', 'failed', 'processing', 'completed') DEFAULT 'active',
    content JSON,
    last_sent TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES users(id)
);
```

### Frontend Components

#### User Interface
- **Responsive Design:** Built with Tailwind CSS for mobile and desktop compatibility
- **Interactive Tabs:** Three main sections with smooth transitions
- **Modern UI Elements:** Cards, badges, icons, and intuitive navigation
- **Real-time Updates:** Dynamic table loading and filtering

#### JavaScript Functionality
- **Tab Management:** Seamless switching between different report sections
- **Modal Wizard:** Step-by-step report creation process
- **Dynamic Filtering:** Real-time search and filter applications
- **AJAX Integration:** Asynchronous data loading and form submissions
- **Notification System:** User feedback for all actions

## API Endpoints

### Report Management
```
GET    /reports                    # Main dashboard
GET    /reports/library            # Get report library data
GET    /reports/historical         # Get historical reports
GET    /reports/templates          # Get available templates
GET    /reports/recipients         # Get available recipients
POST   /reports                    # Create new report schedule
POST   /reports/adhoc              # Generate ad-hoc report
POST   /reports/{id}/generate      # Generate report now
DELETE /reports/{id}               # Delete report schedule
GET    /reports/{id}/download      # Download report file
GET    /reports/{id}/view          # View report online
```

### Request/Response Examples

#### Create Report Schedule
```json
POST /reports
{
    "template": "Monthly Supplier Demand Forecast",
    "recipients": ["Finance Dept", "Logistics Team"],
    "frequency": "monthly",
    "format": "pdf",
    "schedule_time": "09:00",
    "schedule_day": "monday"
}
```

#### Ad-Hoc Report Generation
```json
POST /reports/adhoc
{
    "report_type": "sales_data",
    "from_date": "2025-06-01",
    "to_date": "2025-06-26",
    "format": "excel",
    "filters": {
        "product_category": "arabica",
        "sales_channel": "retail"
    }
}
```

## Available Report Templates

### 1. Monthly Supplier Demand Forecast
- **Category:** Supply Chain
- **Description:** Comprehensive analysis of supplier demand patterns
- **Frequency:** Monthly
- **Typical Recipients:** Finance Dept, Logistics Team

### 2. Weekly Production Efficiency
- **Category:** Production
- **Description:** Production metrics and efficiency analysis
- **Frequency:** Weekly
- **Typical Recipients:** Production Team, Management

### 3. Daily Retail Sales Summary
- **Category:** Sales
- **Description:** Daily sales performance across all outlets
- **Frequency:** Daily
- **Typical Recipients:** Sales Team, Management

### 4. Quarterly Quality Control Report
- **Category:** Quality
- **Description:** Quality metrics and compliance tracking
- **Frequency:** Quarterly
- **Typical Recipients:** Quality Team, Compliance

### 5. Inventory Movement Analysis
- **Category:** Inventory
- **Description:** Detailed inventory tracking and movement patterns
- **Frequency:** Weekly
- **Typical Recipients:** Warehouse Team

## Configuration Options

### Report Frequencies
- **Daily:** Generated every day at specified time
- **Weekly:** Generated on specified day of the week
- **Monthly:** Generated on specified day of the month
- **Quarterly:** Generated at the beginning of each quarter

### Output Formats
- **PDF:** Formatted documents ideal for sharing and printing
- **Excel:** Spreadsheet format perfect for data analysis
- **CSV:** Comma-separated values for data import/export
- **Dashboard:** Interactive online reports (future feature)

### Recipient Types
- **Internal Roles:** Pre-defined organizational roles
- **Specific Suppliers:** Individual supplier accounts
- **Custom Emails:** Ad-hoc email addresses

## User Interface Guide

### Navigation
The reports interface is organized into three main tabs:

1. **Report Library:** Manage scheduled reports and templates
2. **Historical Reports:** View and download previously generated reports
3. **Ad-Hoc Generator:** Create custom reports on-demand

### Creating a New Report Schedule

1. **Click "Create New Report Schedule"** from the Report Library tab
2. **Step 1 - Choose Template:** Select from available report templates
3. **Step 2 - Configure Recipients:** Choose who will receive the reports
4. **Step 3 - Set Schedule:** Define frequency and timing
5. **Step 4 - Choose Format:** Select output format (PDF/Excel)
6. **Step 5 - Review & Save:** Confirm all settings and create the schedule

### Generating Ad-Hoc Reports

1. **Switch to Ad-Hoc Generator tab**
2. **Select Report Type:** Choose the type of data you want to analyze
3. **Set Date Range:** Define the time period for the report
4. **Configure Filters:** Use dynamic filters based on report type
5. **Choose Output Format:** Select PDF, CSV, or Excel
6. **Generate Report:** Submit the request and receive email notification when ready

## Security & Permissions

### Authentication
- All report functionality requires user authentication
- Users must be logged in to access any report features

### Authorization
- Report access is controlled by user roles and permissions
- Sensitive reports may be restricted to specific user roles
- Supplier-specific reports are only available to authorized personnel

### Data Protection
- All report data is encrypted in transit and at rest
- Audit logs track all report generation and access activities
- Automatic data retention policies manage historical report storage

## Performance Considerations

### Optimization Features
- **Lazy Loading:** Tables load data on-demand to improve performance
- **Pagination:** Large datasets are paginated for better user experience
- **Caching:** Frequently accessed reports are cached for faster delivery
- **Background Processing:** Large reports are generated asynchronously

### Scalability
- Database indexing on frequently queried fields
- API rate limiting to prevent system overload
- Efficient query optimization for large datasets

## Troubleshooting

### Common Issues

#### Reports Not Generating
- Check report schedule status (Active/Paused)
- Verify recipient email addresses are valid
- Ensure sufficient system resources for processing

#### Performance Issues
- Clear browser cache and reload the page
- Check network connectivity
- Contact system administrator if issues persist

#### Permission Errors
- Verify user has appropriate role permissions
- Check if specific reports require elevated access
- Contact administrator for role updates

### Error Messages
The system provides clear error messages for common issues:
- "Please select a report template" - Template selection required
- "Please select at least one recipient" - Recipient configuration needed
- "Error generating report" - Processing issue occurred

## Future Enhancements

### Planned Features
- **Interactive Dashboards:** Real-time data visualization
- **Report Scheduling Wizard:** Enhanced step-by-step guidance
- **Advanced Analytics:** Machine learning insights
- **Mobile App:** Native mobile application
- **API Integration:** Third-party system connections

### Customization Options
- **Custom Report Templates:** User-defined report formats
- **Branding:** Company logo and color scheme customization
- **Localization:** Multi-language support
- **Advanced Filters:** Complex query builders

## Support & Maintenance

### Regular Maintenance
- **Database Cleanup:** Automated removal of old report files
- **Performance Monitoring:** System health checks and optimization
- **Security Updates:** Regular security patches and updates
- **Backup Procedures:** Daily backups of report data and configurations

### Support Channels
- **Documentation:** Comprehensive user guides and tutorials
- **Help Desk:** Technical support for system issues
- **Training:** User training sessions for new features
- **Community Forum:** User community for tips and best practices

---

## Quick Start Guide

### For Administrators
1. Review system requirements and installation
2. Configure user roles and permissions
3. Set up initial report templates
4. Test report generation and delivery
5. Train end users on system functionality

### For End Users
1. Log in to the BeanTrack system
2. Navigate to the Reports section
3. Explore the three main tabs (Library, Historical, Ad-Hoc)
4. Create your first report schedule using the wizard
5. Generate an ad-hoc report to familiarize yourself with the interface

### Best Practices
- **Regular Review:** Periodically review and update report schedules
- **Recipient Management:** Keep recipient lists current and accurate
- **Performance Monitoring:** Monitor system performance and report delivery
- **Data Quality:** Ensure source data quality for accurate reporting
- **User Training:** Provide ongoing training for new features and updates

This documentation provides a comprehensive overview of the BeanTrack Reports System. For additional support or questions, please contact the system administrator or refer to the online help resources.
