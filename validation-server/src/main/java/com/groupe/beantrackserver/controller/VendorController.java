package com.groupe.beantrackserver.controller;

import com.groupe.beantrackserver.models.VendorValidationResponse;
import com.groupe.beantrackserver.models.VendorApplications;
import com.groupe.beantrackserver.service.VendorValidationService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.Map;

@RestController
@RequestMapping("/api/vendors")
public class VendorController {

    @Autowired
    private VendorValidationService validationService;

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
}
