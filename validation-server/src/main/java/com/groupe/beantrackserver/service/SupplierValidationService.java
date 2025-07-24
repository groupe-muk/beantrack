package com.groupe.beantrackserver.service;

import com.groupe.beantrackserver.models.SupplierApplications;
import com.groupe.beantrackserver.models.SupplierValidationResponse;
import com.groupe.beantrackserver.repository.SupplierApplicationsRepository;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;
import java.util.HashMap;
import java.util.Map;
import java.util.Optional;
import java.util.concurrent.CompletableFuture;
import java.util.concurrent.TimeUnit;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import org.apache.pdfbox.pdmodel.PDDocument;
import org.apache.pdfbox.text.PDFTextStripper;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

@Service
public class SupplierValidationService {

    @Autowired
    private SupplierApplicationsRepository supplierRepository;

    @Autowired
    private EmailService emailService;


    @Autowired
    private VisitSchedulingService visitSchedulingService;


    /**
     * Submit a supplier application for validation
     * This method provides immediate response and starts async validation
     */
    public SupplierValidationResponse submitApplication(String applicantId, String name, String email, 
                                                      String phoneNumber, String bankStatementPath, 
                                                      String tradingLicensePath, String businessName) {
        
        System.out.println("Processing supplier application for: " + name + " (" + email + ")");
        
        try {
            // Create application record
            SupplierApplications application = new SupplierApplications();
            application.setId(applicantId);
            application.setApplicantName(name);
            application.setEmail(email);
            application.setPhoneNumber(phoneNumber);
            application.setBankStatementPath(bankStatementPath);
            application.setTradingLicensePath(tradingLicensePath);
            application.setBusinessName(businessName);
            application.setStatus("submitted");
            application.setCreatedAt(new java.util.Date());
            
            // Save to database
            supplierRepository.save(application);
            
            // Start async validation process
            CompletableFuture.runAsync(() -> {
                try {
                    // Simulate processing delay
                    Thread.sleep(2000);
                    performValidation(applicantId);
                } catch (Exception e) {
                    System.err.println("Error in async validation for supplier " + applicantId + ": " + e.getMessage());
                    e.printStackTrace();
                }
            });
            
            // Return immediate response
            SupplierValidationResponse response = new SupplierValidationResponse();
            response.setStatus("submitted");
            response.setMessage("Supplier application submitted successfully and is being processed");
            return response;
            
        } catch (Exception e) {
            System.err.println("Error submitting supplier application: " + e.getMessage());
            e.printStackTrace();
            
            SupplierValidationResponse response = new SupplierValidationResponse();
            response.setStatus("error");
            response.setMessage("Failed to submit application: " + e.getMessage());
            return response;
        }
    }

    /**
     * Perform the actual validation process
     * This method handles the business logic for supplier validation
     */
    private void performValidation(String applicantId) {
        try {
            System.out.println("Starting validation for supplier application: " + applicantId);
            
            Optional<SupplierApplications> appOpt = supplierRepository.findById(applicantId);
            if (!appOpt.isPresent()) {
                System.err.println("Application not found for validation: " + applicantId);
                return;
            }
            
            SupplierApplications application = appOpt.get();
            
            // Validate documents
            ValidationResult docValidation = validateDocuments(application);
            if (!docValidation.isValid()) {
                failValidation(application, docValidation.getMessage());
                return;
            }
            
            // Validate financial status (lighter requirements than vendor)
            ValidationResult financialValidation = validateFinancialStatus(application);
            if (!financialValidation.isValid()) {
                failValidation(application, financialValidation.getMessage());
                return;
            }
            
            // Validate business license
            ValidationResult licenseValidation = validateBusinessLicense(application);
            if (!licenseValidation.isValid()) {
                failValidation(application, licenseValidation.getMessage());
                return;
            }
            
            // All validations passed - approve and schedule visit
            approveApplicationInternal(application);
            
        } catch (Exception e) {
            System.err.println("Error during supplier validation: " + e.getMessage());
            e.printStackTrace();
            
            // Mark as failed
            try {
                Optional<SupplierApplications> appOpt = supplierRepository.findById(applicantId);
                if (appOpt.isPresent()) {
                    failValidation(appOpt.get(), "Internal validation error: " + e.getMessage());
                }
            } catch (Exception saveError) {
                System.err.println("Error saving failure state: " + saveError.getMessage());
            }
        }
    }

    /**
     * Validate supplier documents
     */
    private ValidationResult validateDocuments(SupplierApplications application) {
        try {
            // Validate bank statement with real content analysis
            String bankValidationResult = validateBankStatementContent(application.getBankStatementPath(), application.getApplicantName());
            if (!bankValidationResult.equals("valid")) {
                return new ValidationResult(false, "Bank statement validation failed: " + bankValidationResult);
            }
            
            // Validate trading license with real content analysis
            String licenseValidationResult = validateTradingLicenseContent(application.getTradingLicensePath(), application.getBusinessName());
            if (!licenseValidationResult.equals("valid")) {
                return new ValidationResult(false, "Trading license validation failed: " + licenseValidationResult);
            }
            
            return new ValidationResult(true, "Documents validated successfully");
            
        } catch (Exception e) {
            return new ValidationResult(false, "Document validation failed: " + e.getMessage());
        }
    }

    /**
     * Validate financial status (lighter requirements than vendor)
     * Supplier requirements: Min balance 2M UGX, Min credits 5M UGX (vs Vendor: 5M balance, 10M credits)
     */
    private ValidationResult validateFinancialStatus(SupplierApplications application) {
        try {
            System.out.println("Validating financial status for supplier: " + application.getBusinessName());
            
            // Supplier financial requirements (lighter than vendor)
            double minBalance = 2000000; // 2M UGX vs 5M for vendors
            double minCredits = 5000000;  // 5M UGX vs 10M for vendors
            
            // Validate financial data from bank statement
            FinancialValidationResult finResult = validateFinancialStatusFromBankStatement(
                application.getBankStatementPath(), minBalance, minCredits);
            
            if (!finResult.isValid()) {
                return new ValidationResult(false, finResult.getMessage());
            }
            
            // Create financial data with actual values
            Map<String, Object> financialData = new HashMap<>();
            financialData.put("balance_check", "passed");
            financialData.put("actual_balance", finResult.getBalance());
            financialData.put("minimum_balance_required", minBalance);
            financialData.put("total_credits", finResult.getCredits());
            financialData.put("minimum_credits_required", minCredits);
            financialData.put("validation_type", "supplier_light");
            
            return new ValidationResult(true, "Financial status validation passed (supplier requirements)", financialData);
            
        } catch (Exception e) {
            return new ValidationResult(false, "Financial validation failed: " + e.getMessage());
        }
    }

    /**
     * Validate business license with real content analysis
     */
    private ValidationResult validateBusinessLicense(SupplierApplications application) {
        try {
            System.out.println("Validating business license for: " + application.getBusinessName());
            
            // Validate license content
            String licenseValidationResult = validateTradingLicenseContent(
                application.getTradingLicensePath(), application.getBusinessName());
            
            if (!licenseValidationResult.equals("valid")) {
                return new ValidationResult(false, licenseValidationResult);
            }
            
            // Extract license data (this would be from actual PDF parsing)
            Map<String, Object> licenseData = new HashMap<>();
            licenseData.put("license_valid", true);
            licenseData.put("content_validated", true);
            licenseData.put("business_name_matches", true);
            licenseData.put("registration_format_valid", true);
            licenseData.put("not_expired", true);
            licenseData.put("business_type", "supplier");
            
            return new ValidationResult(true, "Business license validated successfully", licenseData);
            
        } catch (Exception e) {
            return new ValidationResult(false, "License validation failed: " + e.getMessage());
        }
    }

    /**
     * Approve application and schedule visit
     */
    private void approveApplicationInternal(SupplierApplications application) {
        try {
            // Schedule visit (1 week from now)
            LocalDate visitDate = LocalDate.now().plusWeeks(1);
            String visitDateStr = visitDate.format(DateTimeFormatter.ISO_LOCAL_DATE);
            
            // Update application status
            application.setStatus("under_review");
            application.setValidationMessage("Supplier application approved. Visit scheduled for quality assessment.");
            application.setValidatedAt(new java.util.Date());
            application.setVisitScheduled(java.sql.Date.valueOf(visitDate));
            
            // Add validation results
            Map<String, Object> financialData = new HashMap<>();
            financialData.put("status", "approved");
            financialData.put("validation_type", "supplier_light");
            financialData.put("balance_check", "passed");
            
            Map<String, Object> licenseData = new HashMap<>();
            licenseData.put("license_valid", true);
            licenseData.put("business_type", "supplier");
            
            supplierRepository.save(application);
            
            // Send email notification about visit
            try {
                emailService.sendSupplierVisitScheduledEmail(
                    application.getEmail(),
                    application.getApplicantName(),
                    application.getBusinessName(),
                    visitDateStr
                );
      
                // Notify administrators about the scheduled visit
                try {
                    emailService.sendSupplierVisitNotificationToAdmins(
                        application.getApplicantName(),
                        application.getBusinessName(),
                        application.getEmail(),
                        visitDateStr
                    );
                } catch (Exception adminEmailError) {
                    System.err.println("Failed to send admin notification for supplier visit: " + adminEmailError.getMessage());
                }

            } catch (Exception emailError) {
                System.err.println("Failed to send visit notification email: " + emailError.getMessage());
            }
            
            System.out.println("Supplier application approved and visit scheduled: " + application.getId());
            
        } catch (Exception e) {
            System.err.println("Error approving supplier application: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Fail validation with reason
     */
    private void failValidation(SupplierApplications application, String reason) {
        try {
            application.setStatus("rejected");
            application.setValidationMessage(reason);
            application.setValidatedAt(new java.util.Date());
            
            supplierRepository.save(application);
            
            // Send rejection email
            try {
                emailService.sendSupplierRejectionEmail(
                    application.getEmail(),
                    application.getApplicantName(),
                    application.getBusinessName(),
                    reason
                );
            } catch (Exception emailError) {
                System.err.println("Failed to send rejection email: " + emailError.getMessage());
            }
            
            System.out.println("Supplier application rejected: " + application.getId() + " - " + reason);
            
        } catch (Exception e) {
            System.err.println("Error failing supplier validation: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Validate individual file
     */
    /**
     * Validate bank statement content with PDF analysis
     */
    private String validateBankStatementContent(String path, String expectedName) {
        if (path == null || path.trim().isEmpty()) {
            return "bank statement file path is missing";
        }
        
        if (!path.endsWith(".pdf")) {
            return "bank statement must be a PDF file";
        }
        
        try (PDDocument document = PDDocument.load(new File(path))) {
            PDFTextStripper stripper = new PDFTextStripper();
            String text = stripper.getText(document);

            // Check if the expected name appears in the PDF
            if (!text.toLowerCase().contains(expectedName.toLowerCase())) {
                return "expected account holder name '" + expectedName + "' not found in bank statement";
            }

            return "valid";
           
        } catch (IOException e) {
            e.printStackTrace();
            return "unable to read or process bank statement PDF file";
        }
    }

    /**
     * Validate trading license content with PDF analysis
     */
    private String validateTradingLicenseContent(String path, String expectedBusinessName) {
        if (path == null || path.trim().isEmpty()) {
            return "trading license file path is missing";
        }
        
        if (!path.endsWith(".pdf")) {
            return "trading license must be a PDF file";
        }

        try (PDDocument document = PDDocument.load(new File(path))) {
            PDFTextStripper stripper = new PDFTextStripper();
            String text = stripper.getText(document);
            
            // Check for registration number (CM followed by 6+ digits)
            Pattern regPattern = Pattern.compile("CM\\d{6,}", Pattern.CASE_INSENSITIVE);
            Matcher matcher = regPattern.matcher(text);
            
            boolean hasValidRegistration = matcher.find();
            if (!hasValidRegistration) {
                return "valid registration number (CM followed by 6+ digits) not found in license";
            }

            // Check if license is still valid by comparing expiry date with today
            String dateValidationResult = validateLicenseExpiryDate(text);
            if (!dateValidationResult.equals("valid")) {
                return dateValidationResult;
            }

            // Check if business name matches (optional for suppliers, unlike vendors)
            if (expectedBusinessName != null && !expectedBusinessName.trim().isEmpty()) {
                boolean nameMatches = text.toLowerCase().contains(expectedBusinessName.toLowerCase());
                if (!nameMatches) {
                    return "business name '" + expectedBusinessName + "' not found in license document";
                }
            }
            
            return "valid";
            
        } catch (IOException e) {
            System.out.println("VALIDATION LICENSE ERROR");
            System.out.println(e);
            e.printStackTrace();
            return "unable to read or process trading license PDF file";
        }
    }

    /**
     * Validate license expiry date from PDF content
     */
    private String validateLicenseExpiryDate(String text) {
        try {
            // Look for license expiry date pattern (e.g., "6th October 2022")
            Pattern expiryPattern = Pattern.compile(
                "License Expiry Date:\\s*(\\d{1,2})(st|nd|rd|th)\\s+(\\w+)\\s+(\\d{4})", 
                Pattern.CASE_INSENSITIVE
            );
            Matcher expiryMatcher = expiryPattern.matcher(text);
            
            if (expiryMatcher.find()) {
                String day = expiryMatcher.group(1);
                String month = expiryMatcher.group(3);
                String year = expiryMatcher.group(4);
                
                System.out.println("Found expiry date: " + day + " " + month + " " + year);
                
                // Parse the date
                String dateString = day + " " + month + " " + year;
                DateTimeFormatter formatter = DateTimeFormatter.ofPattern("d MMMM yyyy");
                LocalDate expiryDate = LocalDate.parse(dateString, formatter);
                LocalDate today = LocalDate.now();
                
                System.out.println("Expiry date: " + expiryDate);
                System.out.println("Today's date: " + today);
                
                // License is valid if expiry date is after or equal to today
                if (expiryDate.isBefore(today)) {
                    return "license expired on " + expiryDate.format(DateTimeFormatter.ofPattern("MMMM d, yyyy"));
                } else {
                    return "valid";
                }
            } else {
                return "license expiry date not found in document";
            }
        } catch (DateTimeParseException e) {
            System.out.println("Error parsing license expiry date: " + e.getMessage());
            return "unable to parse license expiry date format";
        }
    }

    /**
     * Validate financial status from bank statement PDF with lighter requirements for suppliers
     */
    private FinancialValidationResult validateFinancialStatusFromBankStatement(String path, double minBalance, double minCredits) {
        try (PDDocument document = PDDocument.load(new File(path))) {
            PDFTextStripper stripper = new PDFTextStripper();
            String text = stripper.getText(document).replaceAll(",", "");

            Pattern balancePattern = Pattern.compile("AVAILABLE BALANCE:\\s+(\\d+(\\.\\d{1,2})?)");
            Pattern creditsPattern = Pattern.compile("TOTAL CREDITS:\\s+(\\d+(\\.\\d{1,2})?)");

            Matcher balanceMatcher = balancePattern.matcher(text);
            Matcher creditsMatcher = creditsPattern.matcher(text);

            double balance = 0;
            double credits = 0;

            if (balanceMatcher.find()) {
                balance = Double.parseDouble(balanceMatcher.group(1));
            } else {
                return new FinancialValidationResult(false, "Available balance not found in bank statement", 0, 0);
            }

            if (creditsMatcher.find()) {
                credits = Double.parseDouble(creditsMatcher.group(1));
            } else {
                return new FinancialValidationResult(false, "Total credits not found in bank statement", balance, 0);
            }

            System.out.println("Parsed Balance: " + balance + " (required: " + minBalance + ")");
            System.out.println("Parsed Credits: " + credits + " (required: " + minCredits + ")");

            if (balance < minBalance) {
                return new FinancialValidationResult(false, 
                    String.format("Available balance %.2f is below minimum requirement of %.2f", balance, minBalance), 
                    balance, credits);
            }

            if (credits < minCredits) {
                return new FinancialValidationResult(false, 
                    String.format("Total credits %.2f is below minimum requirement of %.2f", credits, minCredits), 
                    balance, credits);
            }

            return new FinancialValidationResult(true, "Financial requirements met", balance, credits);
            
        } catch (IOException e) {
            e.printStackTrace();
            return new FinancialValidationResult(false, "Unable to read bank statement file", 0, 0);
        } catch (NumberFormatException e) {
            e.printStackTrace();
            return new FinancialValidationResult(false, "Unable to parse financial amounts from bank statement", 0, 0);
        }
    }

    /**
     * Helper class for financial validation results
     */
    private static class FinancialValidationResult {
        private boolean valid;
        private String message;
        private double balance;
        private double credits;

        public FinancialValidationResult(boolean valid, String message, double balance, double credits) {
            this.valid = valid;
            this.message = message;
            this.balance = balance;
            this.credits = credits;
        }

        public boolean isValid() { return valid; }
        public String getMessage() { return message; }
        public double getBalance() { return balance; }
        public double getCredits() { return credits; }
    }

    /**
     * Get application status
     */
    public Map<String, Object> getApplicationStatus(String applicationId) {
        Optional<SupplierApplications> appOpt = supplierRepository.findById(applicationId);
        if (!appOpt.isPresent()) {
            throw new RuntimeException("Application not found: " + applicationId);
        }
        
        SupplierApplications application = appOpt.get();
        Map<String, Object> status = new HashMap<>();
        status.put("id", application.getId());
        status.put("status", application.getStatus());
        status.put("message", application.getValidationMessage());
        status.put("created_at", application.getCreatedAt());
        status.put("validated_at", application.getValidatedAt());
        status.put("visit_scheduled", application.getVisitScheduled());
        
        return status;
    }

    /**
     * Validate application (manual trigger)
     */
    public SupplierValidationResponse validateApplication(String applicationId) {
        CompletableFuture.runAsync(() -> performValidation(applicationId));
        
        SupplierValidationResponse response = new SupplierValidationResponse();
        response.setStatus("submitted");
        response.setMessage("Validation started for supplier application");
        return response;
    }

    /**
     * Approve application (manual)
     */
    public void approveApplication(String applicationId) {
        Optional<SupplierApplications> appOpt = supplierRepository.findById(applicationId);
        if (!appOpt.isPresent()) {
            throw new RuntimeException("Application not found: " + applicationId);
        }
        
        approveApplicationInternal(appOpt.get());
    }

    /**
     * Reject application (manual)
     */
    public void rejectApplication(String applicationId, String reason) {
        Optional<SupplierApplications> appOpt = supplierRepository.findById(applicationId);
        if (!appOpt.isPresent()) {
            throw new RuntimeException("Application not found: " + applicationId);
        }
        
        failValidation(appOpt.get(), reason != null ? reason : "Application rejected by administrator");
    }

    /**
     * Schedule visit (manual)
     */
    public void scheduleVisit(String applicationId, String visitDate) {
        Optional<SupplierApplications> appOpt = supplierRepository.findById(applicationId);
        if (!appOpt.isPresent()) {
            throw new RuntimeException("Application not found: " + applicationId);
        }
        
        SupplierApplications application = appOpt.get();
        application.setVisitScheduled(java.sql.Date.valueOf(LocalDate.parse(visitDate)));
        supplierRepository.save(application);
        
        // Send email notification
        try {
            emailService.sendSupplierVisitScheduledEmail(
                application.getEmail(),
                application.getApplicantName(),
                application.getBusinessName(),
                visitDate
            );
        } catch (Exception e) {
            System.err.println("Failed to send visit notification: " + e.getMessage());
        }
    }

    /**
     * Helper class for validation results
     */
    private static class ValidationResult {
        private final boolean valid;
        private final String message;
        private final Map<String, Object> data;

        public ValidationResult(boolean valid, String message) {
            this.valid = valid;
            this.message = message;
            this.data = new HashMap<>();
        }

        public ValidationResult(boolean valid, String message, Map<String, Object> data) {
            this.valid = valid;
            this.message = message;
            this.data = data != null ? data : new HashMap<>();
        }

        public boolean isValid() { return valid; }
        public String getMessage() { return message; }
        public Map<String, Object> getData() { return data; }
    }
}
