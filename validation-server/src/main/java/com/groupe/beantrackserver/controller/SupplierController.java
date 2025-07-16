package com.groupe.beantrackserver.controller;

import com.groupe.beantrackserver.models.SupplierApplications;
import com.groupe.beantrackserver.models.SupplierValidationResponse;
import com.groupe.beantrackserver.service.EmailService;
import com.groupe.beantrackserver.service.SupplierValidationService;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.Map;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/suppliers")
public class SupplierController {

    @Autowired
    private SupplierValidationService validationService;

    @Autowired
    private EmailService emailService;

    @PostMapping("/apply")
    public ResponseEntity<SupplierValidationResponse> applySupplier(
            @RequestParam("applicantId") String applicantId,
            @RequestParam("name") String name,
            @RequestParam("email") String email,
            @RequestParam(value = "phoneNumber", required = false) String phoneNumber,
            @RequestParam(value = "phone_number", required = false) String phoneNumberAlt,
            @RequestParam("bankStatement") String bankStatementPath,
            @RequestParam("tradingLicense") String tradingLicensePath,
            @RequestParam("businessName") String businessName,
            @RequestParam(value = "applicationType", defaultValue = "supplier") String applicationType) {

        try {
            // Use phoneNumber if provided, otherwise use phone_number
            String finalPhoneNumber = phoneNumber != null ? phoneNumber : phoneNumberAlt;
            if (finalPhoneNumber == null) {
                throw new IllegalArgumentException("Phone number is required (either 'phoneNumber' or 'phone_number' parameter)");
            }
            
            // Validate that files exist and have correct naming pattern
            validateFilePath(bankStatementPath, applicantId + "_bank-statement");
            validateFilePath(tradingLicensePath, applicantId + "_trading-license");
            
            // Submit application and start async validation
            SupplierValidationResponse result = validationService.submitApplication(applicantId, name, email, finalPhoneNumber, bankStatementPath, tradingLicensePath, businessName);
            
            // Send immediate response
            return ResponseEntity.ok(result);
            
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error for supplier application: " + e.getMessage());
            SupplierValidationResponse errorResponse = new SupplierValidationResponse();
            errorResponse.setStatus("error");
            errorResponse.setMessage("Validation failed: " + e.getMessage());
            return ResponseEntity.badRequest().body(errorResponse);
            
        } catch (Exception e) {
            System.err.println("Error processing supplier application: " + e.getMessage());
            e.printStackTrace();
            
            SupplierValidationResponse errorResponse = new SupplierValidationResponse();
            errorResponse.setStatus("error");
            errorResponse.setMessage("Failed to process application: " + e.getMessage());
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(errorResponse);
        }
    }

    private void validateFilePath(String filePath, String expectedPattern) throws IllegalArgumentException {
        if (filePath == null || filePath.trim().isEmpty()) {
            throw new IllegalArgumentException("File path cannot be null or empty");
        }
        
        Path path = Paths.get(filePath);
        if (!Files.exists(path)) {
            throw new IllegalArgumentException("File does not exist: " + filePath);
        }
        
        String fileName = path.getFileName().toString().toLowerCase();
        if (!fileName.contains(expectedPattern.toLowerCase())) {
            throw new IllegalArgumentException("File name must contain '" + expectedPattern + "' but was: " + fileName);
        }
        
        if (!fileName.endsWith(".pdf")) {
            throw new IllegalArgumentException("File must be a PDF but was: " + fileName);
        }
    }

    @GetMapping("/status/{applicationId}")
    public ResponseEntity<Map<String, Object>> getApplicationStatus(@PathVariable String applicationId) {
        try {
            Map<String, Object> status = validationService.getApplicationStatus(applicationId);
            return ResponseEntity.ok(status);
        } catch (Exception e) {
            System.err.println("Error retrieving supplier application status: " + e.getMessage());
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                    .body(Map.of("error", "Failed to retrieve application status"));
        }
    }

    @PostMapping("/validate/{applicationId}")
    public ResponseEntity<SupplierValidationResponse> validateApplication(@PathVariable String applicationId) {
        try {
            SupplierValidationResponse result = validationService.validateApplication(applicationId);
            return ResponseEntity.ok(result);
        } catch (Exception e) {
            System.err.println("Error validating supplier application: " + e.getMessage());
            SupplierValidationResponse errorResponse = new SupplierValidationResponse();
            errorResponse.setStatus("error");
            errorResponse.setMessage("Validation failed: " + e.getMessage());
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(errorResponse);
        }
    }

    @PostMapping("/approve/{applicationId}")
    public ResponseEntity<Map<String, Object>> approveApplication(@PathVariable String applicationId) {
        try {
            validationService.approveApplication(applicationId);
            return ResponseEntity.ok(Map.of("message", "Application approved successfully"));
        } catch (Exception e) {
            System.err.println("Error approving supplier application: " + e.getMessage());
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                    .body(Map.of("error", "Failed to approve application"));
        }
    }

    @PostMapping("/reject/{applicationId}")
    public ResponseEntity<Map<String, Object>> rejectApplication(
            @PathVariable String applicationId,
            @RequestBody Map<String, String> request) {
        try {
            String reason = request.get("reason");
            validationService.rejectApplication(applicationId, reason);
            return ResponseEntity.ok(Map.of("message", "Application rejected successfully"));
        } catch (Exception e) {
            System.err.println("Error rejecting supplier application: " + e.getMessage());
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                    .body(Map.of("error", "Failed to reject application"));
        }
    }

    @PostMapping("/schedule-visit/{applicationId}")
    public ResponseEntity<Map<String, Object>> scheduleVisit(
            @PathVariable String applicationId,
            @RequestBody Map<String, String> request) {
        try {
            String visitDate = request.get("visitDate");
            validationService.scheduleVisit(applicationId, visitDate);
            return ResponseEntity.ok(Map.of("message", "Visit scheduled successfully"));
        } catch (Exception e) {
            System.err.println("Error scheduling visit for supplier application: " + e.getMessage());
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                    .body(Map.of("error", "Failed to schedule visit"));
        }
    }

    @GetMapping("/health")
    public ResponseEntity<Map<String, String>> health() {
        return ResponseEntity.ok(Map.of("status", "healthy", "service", "supplier-validation"));
    }
}
