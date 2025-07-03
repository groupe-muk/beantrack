# BeanTrack Vendor Validation Server

A Spring Boot-based REST API server for validating vendor applications in the BeanTrack Supply Chain Management System. This service processes vendor registration requests by validating bank statements and trading licenses through document analysis.

## 🚀 Features

- **Document Validation**: Automated validation of PDF bank statements and trading licenses
- **Smart Text Extraction**: Uses Apache PDFBox for extracting text from PDF documents
- **Registration Number Verification**: Validates trading license registration numbers (CM format)
- **License Expiry Checking**: Automatically checks if trading licenses are still valid
- **Account Holder Verification**: Validates bank statement account holder names
- **Detailed Error Messages**: Provides specific feedback on validation failures
- **File Storage**: Secure file upload and storage system
- **RESTful API**: Clean REST endpoints for vendor application processing

## 🛠️ Technology Stack

- **Java 17** - Programming language
- **Spring Boot 3.5.3** - Application framework
- **Spring Data JPA** - Database access layer
- **Apache PDFBox** - PDF document processing
- **MySQL** - Database for storing application data
- **Maven** - Dependency management and build tool

## 📋 Prerequisites

- Java 17 or higher
- Maven 3.6+
- MySQL 8.0+
- IDE (VS Code, IntelliJ IDEA, Eclipse)

## ⚙️ Installation & Setup

### 1. Clone the Repository
```bash
git clone <repository-url>
cd beantrackserver
```

### 2. Database Configuration
Create a MySQL database and update the connection details in `src/main/resources/application.properties`:

```properties
spring.datasource.url=jdbc:mysql://localhost:3306/your_database_name
spring.datasource.username=your_username
spring.datasource.password=your_password
spring.jpa.hibernate.ddl-auto=update
spring.jpa.database-platform=org.hibernate.dialect.MySQLDialect
```

### 3. Build the Project
```bash
mvn clean install
```

### 4. Run the Application
```bash
mvn spring-boot:run
```

The server will start on `http://localhost:8080`

## 📖 API Documentation

### Vendor Application Endpoint

**POST** `/api/vendors/apply`

Submit a vendor application with required documents for validation.

#### Request Parameters (Form Data)
- `name` (String, required) - Vendor name
- `email` (String, required) - Vendor email address
- `bankStatement` (File, required) - PDF bank statement
- `tradingLicense` (File, required) - PDF trading license

#### Response Format
```json
{
    "status": "approved|rejected|error",
    "message": "Detailed validation message",
    "bankPath": "path/to/uploaded/bank/statement",
    "tradingLicense": "path/to/uploaded/trading/license"
}
```

#### Example Success Response
```json
{
    "status": "approved",
    "message": "Vendor application approved.",
    "bankPath": "uploads/bank/uuid_bank-statement.pdf",
    "tradingLicense": "uploads/license/uuid_trading-license.pdf"
}
```

#### Example Error Response
```json
{
    "status": "rejected",
    "message": "Validation failed: Trading license - license expired on October 6, 2022.",
    "bankPath": "uploads/bank/uuid_bank-statement.pdf",
    "tradingLicense": "uploads/license/uuid_trading-license.pdf"
}
```

## 🔍 Validation Rules

### Bank Statement Validation
- ✅ Must be a PDF file
- ✅ Must contain the expected account holder name (configurable)
- ✅ Document must be readable and processable

### Trading License Validation
- ✅ Must be a PDF file
- ✅ Must contain a valid registration number (CM followed by 6+ digits)
- ✅ License must not be expired (checks against current date)
- ✅ Must have a readable expiry date in format: "License Expiry Date: DDth Month YYYY"
- ✅ Document must be readable and processable

## 📁 Project Structure

```
src/
├── main/
│   ├── java/
│   │   └── com/groupe/beantrackserver/
│   │       ├── controller/          # REST controllers
│   │       │   └── VendorController.java
│   │       ├── models/              # Data models
│   │       │   ├── VendorApplications.java
│   │       │   └── VendorValidationResponse.java
│   │       ├── repository/          # Data access layer
│   │       │   └── VendorApplicationsRepository.java
│   │       ├── service/             # Business logic
│   │       │   └── VendorValidationService.java
│   │       └── BeantrackserverApplication.java
│   └── resources/
│       └── application.properties   # Configuration
├── test/                           # Test files
└── uploads/                        # File storage directory
    ├── bank/                       # Bank statements
    └── license/                    # Trading licenses
```

## 🧪 Testing

### Using cURL
```bash
curl -X POST http://localhost:8080/api/vendors/apply \
  -F "name=John Doe" \
  -F "email=john@example.com" \
  -F "bankStatement=@path/to/bank-statement.pdf" \
  -F "tradingLicense=@path/to/trading-license.pdf"
```

### Using Postman
1. Set method to POST
2. URL: `http://localhost:8080/api/vendors/apply`
3. Body: form-data
4. Add the required fields and file uploads

## 🔧 Configuration

### Application Properties
Key configuration options in `application.properties`:

```properties
# Server port
server.port=8080

# Database configuration
spring.datasource.url=jdbc:mysql://localhost:3306/beantrack
spring.datasource.username=root
spring.datasource.password=password

# JPA/Hibernate settings
spring.jpa.hibernate.ddl-auto=update
spring.jpa.show-sql=true

# File upload limits
spring.servlet.multipart.max-file-size=10MB
spring.servlet.multipart.max-request-size=10MB
```

### Customizing Validation
To modify validation criteria, edit the `VendorValidationService.java`:

- **Account holder name**: Change the expected name in the `validateAndStore()` method
- **Registration pattern**: Modify the regex pattern in `validateLicenseFileWithMessage()`
- **Upload directory**: Change the `UPLOAD_DIR` constant

## 🚨 Error Handling

The API provides detailed error messages for various scenarios:

### Bank Statement Errors
- Invalid file format
- Account holder name not found
- PDF processing errors

### Trading License Errors
- Invalid file format
- Registration number not found
- License expired
- Expiry date not found or unreadable
- PDF processing errors

### System Errors
- File upload failures
- Database connection issues
- Internal server errors

## 📝 Logging

The application uses Spring Boot's default logging. Key validation steps are logged to the console for debugging purposes.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is part of the BeanTrack Supply Chain Management System.

## 📞 Support

For support and questions, please contact the development team or create an issue in the repository.

---

**BeanTrack** - Streamlining Supply Chain Management through Technology
