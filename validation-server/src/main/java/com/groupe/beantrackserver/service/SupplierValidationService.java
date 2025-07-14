package com.groupe.beantrackserver.service;

import com.groupe.beantrackserver.models.SupplierApplications;
import com.groupe.beantrackserver.models.SupplierValidationResponse;
import com.groupe.beantrackserver.repository.SupplierApplicationsRepository;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.Map;
import java.util.Optional;
import java.util.concurrent.CompletableFuture;
import java.util.concurrent.TimeUnit;
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
            // Validate bank statement
            if (!validateFile(application.getBankStatementPath(), "bank-statement")) {
                return new ValidationResult(false, "Bank statement file is invalid or missing");
            }
            
            // Validate trading license
            if (!validateFile(application.getTradingLicensePath(), "trading-license")) {
                return new ValidationResult(false, "Trading license file is invalid or missing");
            }
            
            return new ValidationResult(true, "Documents validated successfully");
            
        } catch (Exception e) {
            return new ValidationResult(false, "Document validation failed: " + e.getMessage());
        }
    }

    /**
     * Validate financial status (lighter requirements than vendor)
     */
    private ValidationResult validateFinancialStatus(SupplierApplications application) {
        try {
            // Simulate financial validation with lighter requirements
            System.out.println("Validating financial status for supplier: " + application.getBusinessName());
            
            // Lighter financial checks for suppliers
            // - Basic bank statement analysis
            // - Simplified credit check
            // - Lower minimum balance requirements
            
            // For demo purposes, we'll approve most applications
            // In real implementation, this would connect to financial services
            
            // Create financial data (lighter than vendor)
            Map<String, Object> financialData = new HashMap<>();
            financialData.put("balance_check", "passed");
            financialData.put("transaction_history", "adequate");
            financialData.put("credit_score", "acceptable");
            financialData.put("minimum_balance_met", true);
            financialData.put("validation_type", "supplier_light");
            
            return new ValidationResult(true, "Financial status validation passed (supplier requirements)", financialData);
            
        } catch (Exception e) {
            return new ValidationResult(false, "Financial validation failed: " + e.getMessage());
        }
    }

    /**
     * Validate business license
     */
    private ValidationResult validateBusinessLicense(SupplierApplications application) {
        try {
            // Simulate license validation
            System.out.println("Validating business license for: " + application.getBusinessName());
            
            // Create license data
            Map<String, Object> licenseData = new HashMap<>();
            licenseData.put("license_valid", true);
            licenseData.put("expiry_date", "2025-12-31");
            licenseData.put("registration_number", "SUP" + System.currentTimeMillis());
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
    private boolean validateFile(String filePath, String expectedType) throws IOException {
        if (filePath == null || filePath.trim().isEmpty()) {
            return false;
        }
        
        Path path = Paths.get(filePath);
        if (!Files.exists(path)) {
            return false;
        }
        
        String fileName = path.getFileName().toString().toLowerCase();
        return fileName.contains(expectedType.toLowerCase()) && fileName.endsWith(".pdf");
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
