package com.groupe.beantrackserver.controller;

import com.groupe.beantrackserver.models.VendorApplications;
import com.groupe.beantrackserver.models.VendorValidationResponse;
import com.groupe.beantrackserver.service.EmailService;
import com.groupe.beantrackserver.service.VendorValidationService;
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
@RequestMapping("/api/vendors")
public class VendorController {

    @Autowired
    private VendorValidationService validationService;

    @Autowired
    private EmailService emailService;

    @PostMapping("/apply")
    public ResponseEntity<VendorValidationResponse> applyVendor(
            @RequestParam("applicantId") String applicantId,
            @RequestParam("name") String name,
            @RequestParam("email") String email,
            @RequestParam(value = "phoneNumber", required = false) String phoneNumber,
            @RequestParam(value = "phone_number", required = false) String phoneNumberAlt,
            @RequestParam("bankStatement") String bankStatementPath,
            @RequestParam("tradingLicense") String tradingLicensePath,
            @RequestParam("businessName") String businessName) {

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
            VendorValidationResponse result = validationService.submitApplication(applicantId, name, email, finalPhoneNumber, bankStatementPath, tradingLicensePath, businessName);
            return ResponseEntity.ok(result);
        } catch (Exception e) {
            e.printStackTrace();
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(
                    new VendorValidationResponse("error", "Internal server error: " + e.getMessage(), "", "")
            );
        }
    }

    private void validateFilePath(String filePath, String expectedPrefix) throws IOException {
        Path path = Paths.get(filePath);
        if (!Files.exists(path)) {
            throw new IOException("File not found: " + filePath);
        }
        
        String fileName = path.getFileName().toString();
        // Log the values for debugging
        System.out.println("Expected prefix: " + expectedPrefix);
        System.out.println("Actual file name: " + fileName);
        
        // The actual file names should start with the expected prefix
        if (!fileName.startsWith(expectedPrefix)) {
            throw new IOException("File name does not match expected pattern. Expected: " + expectedPrefix + "*, Found: " + fileName);
        }
    }

    @GetMapping("/status/{applicationId}")
    public ResponseEntity<?> getApplicationStatus(@PathVariable String applicationId) {
        try {
            VendorApplications application = validationService.getApplicationStatus(applicationId);
            if (application == null) {
                return ResponseEntity.notFound().build();
            }
            
            // Return application status information
            return ResponseEntity.ok(Map.of(
                "applicationId", application.getId(),
                "status", application.getStatus().toString(),
                "message", application.getValidationMessage() != null ? application.getValidationMessage() : "",
                "submittedAt", application.getCreatedAt(),
                "validatedAt", application.getValidatedAt()
            ));
        } catch (Exception e) {
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                .body(Map.of("error", "Internal server error: " + e.getMessage()));
        }
    }

    @PostMapping("/send-email")
    public ResponseEntity<?> sendEmail(@RequestParam Map<String, String> emailData) {
        try {
            System.out.println("Received email request with data: " + emailData);
            
            String type = emailData.get("type");
            String email = emailData.get("email");
            String applicantName = emailData.get("applicantName");
            String businessName = emailData.get("businessName");
            
            if (type == null || email == null || applicantName == null) {
                System.err.println("Missing required parameters: type=" + type + ", email=" + email + ", applicantName=" + applicantName);
                return ResponseEntity.badRequest()
                    .body(Map.of("error", "Missing required parameters: type, email, applicantName"));
            }

            if ("rejection".equals(type)) {
                String reason = emailData.getOrDefault("reason", "Your application did not meet our requirements.");
                System.out.println("Sending rejection email to: " + email);
                emailService.sendRejectionEmailDirect(email, applicantName, businessName, reason);
                
                return ResponseEntity.ok(Map.of(
                    "success", true,
                    "message", "Rejection email sent successfully"
                ));
                
            } else if ("welcome".equals(type)) {
                String userId = emailData.get("userId");
                String password = emailData.get("password");
                String loginUrl = emailData.get("loginUrl");
                
                if (userId == null || password == null) {
                    System.err.println("Missing required parameters for welcome email: userId=" + userId + ", password=" + (password != null ? "[PRESENT]" : "[MISSING]"));
                    return ResponseEntity.badRequest()
                        .body(Map.of("error", "Missing required parameters for welcome email: userId, password"));
                }
                
                System.out.println("Sending welcome email to: " + email);
                emailService.sendWelcomeEmailDirect(email, applicantName, businessName, userId, password, loginUrl);
                
                return ResponseEntity.ok(Map.of(
                    "success", true,
                    "message", "Welcome email sent successfully"
                ));
                
            } else {
                System.err.println("Invalid email type: " + type);
                return ResponseEntity.badRequest()
                    .body(Map.of("error", "Invalid email type. Supported types: rejection, welcome"));
            }
            
        } catch (Exception e) {
            System.err.println("Failed to send email: " + e.getMessage());
            e.printStackTrace();
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                .body(Map.of("error", "Failed to send email: " + e.getMessage()));
        }
    }

    @PostMapping("/test-email")
    public ResponseEntity<?> testEmail(@RequestParam String email, @RequestParam String type) {
        try {
            if ("welcome".equals(type)) {
                emailService.sendWelcomeEmailDirect(
                    email, 
                    "Test User", 
                    "Test Business", 
                    "U00001", 
                    "testpass123", 
                    "http://localhost:8000/login"
                );
                return ResponseEntity.ok(Map.of("success", true, "message", "Test welcome email sent"));
            } else if ("rejection".equals(type)) {
                emailService.sendRejectionEmailDirect(
                    email, 
                    "Test User", 
                    "Test Business", 
                    "This is a test rejection."
                );
                return ResponseEntity.ok(Map.of("success", true, "message", "Test rejection email sent"));
            }
            return ResponseEntity.badRequest().body(Map.of("error", "Invalid type"));
        } catch (Exception e) {
            e.printStackTrace();
            return ResponseEntity.status(500).body(Map.of("error", e.getMessage()));
        }
    }
}
