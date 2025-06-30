package com.groupe.beantrackserver.service;

import org.apache.pdfbox.pdmodel.PDDocument;
import org.apache.pdfbox.text.PDFTextStripper;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Async;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

import com.groupe.beantrackserver.models.VendorValidationResponse;
import com.groupe.beantrackserver.models.VendorApplications;
import com.groupe.beantrackserver.repository.VendorApplicationsRepository;

import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;
import java.util.UUID;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

@Service
public class VendorValidationService {

    private final String UPLOAD_DIR = "uploads/";
    
    @Autowired
    private VendorApplicationsRepository vendorApplicationsRepository;

    public VendorValidationResponse validateAndStore(String name, String email, MultipartFile bank, MultipartFile license, String businessName) throws IOException {
        // Save files
        String bankPath = saveFile(bank, "bank");
        String licensePath = saveFile(license, "license");

        // Perform detailed validation
        String bankValidationMessage = validateBankFileWithMessage(bankPath, name);
        String licenseValidationMessage = validateLicenseFileWithMessage(licensePath);

        // boolean bankValid = bankValidationMessage.equals("valid");
        // boolean licenseValid = licenseValidationMessage.equals("valid");

         boolean bankValid = validateBankFile(bankPath, name) &&
                validateFinancialStatusFromBankStatement(bankPath, 5000000.0, 10000000.0);
        boolean licenseValid = validateLicenseFile(licensePath, name);

        if (bankValid && licenseValid) {
            return new VendorValidationResponse("approved", "Vendor application approved.", bankPath, licensePath);
        } else {
            // Build detailed failure message
            StringBuilder failureMessage = new StringBuilder("Validation failed: ");
            if (!bankValid) {
                failureMessage.append("Bank statement - ").append(bankValidationMessage).append(". ");
            }
            if (!licenseValid) {
                failureMessage.append("Trading license - ").append(licenseValidationMessage).append(".");
            }
            
            return new VendorValidationResponse("rejected", failureMessage.toString().trim(), bankPath, licensePath);
        }
    }

    private String saveFile(MultipartFile file, String subDir) throws IOException {
        String dir = UPLOAD_DIR + subDir + "/";
        Files.createDirectories(Paths.get(dir));
        String filename = UUID.randomUUID() + "_" + file.getOriginalFilename();
        Path filepath = Paths.get(dir, filename);
        file.transferTo(filepath);
        return filepath.toString();
    }

    private boolean validateBankFile(String path, String expectedName) {
       try (PDDocument document = PDDocument.load(new File(path))) {
            PDFTextStripper stripper = new PDFTextStripper();
            String text = stripper.getText(document);

            // Check if the expected name appears in the PDF
            return text.toLowerCase().contains(expectedName.toLowerCase());
        } catch (IOException e) {
            e.printStackTrace();
            return false;
        }
    }

    private boolean validateLicenseFile(String path, String expectedBusinessName) {
         if (!path.endsWith(".pdf")) return false;

        try (PDDocument document = PDDocument.load(new File(path))) {
            PDFTextStripper stripper = new PDFTextStripper();
            String text = stripper.getText(document);
            System.out.println("LICENSE DOCUMENT>>>>>>");
            System.out.println(text);

            // Basic check: registration number should match pattern (e.g., CM123456)
            Pattern regPattern = Pattern.compile("CM\\d{6,}", Pattern.CASE_INSENSITIVE);
            Matcher matcher = regPattern.matcher(text);

            System.out.println("Pattern found: " + matcher.find());
            
            // Reset matcher for reuse
            matcher.reset();
            boolean hasValidRegistration = matcher.find();

            // Check if license is still valid by comparing expiry date with today
            boolean isLicenseValid = isLicenseStillValid(text);

            //Check if Business name exists
              boolean nameMatches = text.toLowerCase().contains(expectedBusinessName.toLowerCase());

            
            
            System.out.println("Has valid registration: " + hasValidRegistration);
            System.out.println("License is still valid: " + isLicenseValid);
            System.out.println("Business name exists: " + nameMatches);

            return hasValidRegistration && isLicenseValid && nameMatches;
        } catch (IOException e) {
            System.out.println("VALIDATION LICENSE ERROR");
            System.out.println(e);
            e.printStackTrace();
            return false;
        }
    }

    private boolean isLicenseStillValid(String text) {
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
                return !expiryDate.isBefore(today);
            } else {
                System.out.println("No expiry date found in license document");
                return false;
            }
        } catch (DateTimeParseException e) {
            System.out.println("Error parsing license expiry date: " + e.getMessage());
            return false;
        }
    }
    
    private String validateBankFileWithMessage(String path, String expectedName ) {
        try (PDDocument document = PDDocument.load(new File(path))) {
            PDFTextStripper stripper = new PDFTextStripper();
            String text = stripper.getText(document);

            // Check if the expected name appears in the PDF
            if (text.toLowerCase().contains(expectedName.toLowerCase())) {
                return "valid";
            } else {
                return "expected account holder name '" + expectedName + "' not found in bank statement";
            }

           
        } catch (IOException e) {
            e.printStackTrace();
            return "unable to read or process bank statement PDF file";
        }
    }

    private boolean validateFinancialStatusFromBankStatement(String path, double minBalance, double minCredits) {
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
            }

            if (creditsMatcher.find()) {
                credits = Double.parseDouble(creditsMatcher.group(1));
            }

            System.out.println("Parsed Balance: " + balance);
            System.out.println("Parsed Credits: " + credits);

            return balance >= minBalance && credits >= minCredits;
        } catch (IOException e) {
            e.printStackTrace();
            return false;
        }
    }

    private String validateLicenseFileWithMessage(String path) {
        if (!path.endsWith(".pdf")) {
            return "trading license must be a PDF file";
        }

        try (PDDocument document = PDDocument.load(new File(path))) {
            PDFTextStripper stripper = new PDFTextStripper();
            String text = stripper.getText(document);
            System.out.println("LICENSE DOCUMENT>>>>>>");
            System.out.println(text);

            // Check for registration number first
            Pattern regPattern = Pattern.compile("CM\\d{6,}", Pattern.CASE_INSENSITIVE);
            Matcher matcher = regPattern.matcher(text);
            
            boolean hasValidRegistration = matcher.find();
            if (!hasValidRegistration) {
                return "valid registration number (CM followed by 6+ digits) not found in license";
            }
            
            System.out.println("Has valid registration: " + hasValidRegistration);

            // Check if license is still valid by comparing expiry date with today
            String dateValidationResult = validateLicenseExpiryDate(text);
            if (!dateValidationResult.equals("valid")) {
                return dateValidationResult;
            }
            
            System.out.println("License date validation passed");
            return "valid";
            
        } catch (IOException e) {
            System.out.println("VALIDATION LICENSE ERROR");
            System.out.println(e);
            e.printStackTrace();
            return "unable to read or process trading license PDF file";
        }
    }

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

    // Method that accepts application data, saves it immediately, and validates asynchronously
    public VendorValidationResponse submitApplication(String applicantId, String name, String email, String phoneNumber, String bankPath, String licensePath, String businessName) throws IOException {
        // Validate that files exist
        if (!Files.exists(Paths.get(bankPath))) {
            throw new IOException("Bank statement file not found: " + bankPath);
        }
        if (!Files.exists(Paths.get(licensePath))) {
            throw new IOException("Trading license file not found: " + licensePath);
        }

        // Create vendor application record immediately with pending status
        VendorApplications application = new VendorApplications();
        application.setId(generateApplicationId());
        application.setApplicantName(name);
        application.setBusinessName(businessName);
        application.setEmail(email);
        application.setPhoneNumber(phoneNumber);
        application.setBankStatementPath(bankPath);
        application.setTradingLicensePath(licensePath);
        application.setCreatedAt(LocalDateTime.now());
        application.setUpdatedAt(LocalDateTime.now());
        application.setStatus(VendorApplications.ApplicationStatus.pending);
        application.setValidationMessage("Application submitted successfully. Validation in progress.");
        
        // Save the application immediately
        VendorApplications savedApplication = vendorApplicationsRepository.save(application);
        
        // Start async validation
        validateApplicationAsync(savedApplication.getId());
        
        return new VendorValidationResponse("submitted", 
            "Application submitted successfully. You will be notified once validation is complete.", 
            bankPath, licensePath);
    }

    @Async
    public void validateApplicationAsync(String applicationId) {
        try {
            // Retrieve the application
            VendorApplications application = vendorApplicationsRepository.findById(applicationId)
                .orElseThrow(() -> new RuntimeException("Application not found: " + applicationId));
            
            // Update status to under_review
            application.setStatus(VendorApplications.ApplicationStatus.under_review);
            application.setValidationMessage("Validation in progress...");
            application.setUpdatedAt(LocalDateTime.now());
            vendorApplicationsRepository.save(application);
            
            // Perform validation
            String bankValidationMessage = validateBankFileWithMessage(application.getBankStatementPath(), application.getApplicantName());
            String licenseValidationMessage = validateLicenseFileWithMessage(application.getTradingLicensePath());

            boolean bankValid = validateBankFile(application.getBankStatementPath(), application.getApplicantName()) &&
                    validateFinancialStatusFromBankStatement(application.getBankStatementPath(), 5000000.0, 10000000.0);
            boolean licenseValid = validateLicenseFile(application.getTradingLicensePath(), application.getBusinessName());

            // Update application with validation results
            if (bankValid && licenseValid) {
                application.setStatus(VendorApplications.ApplicationStatus.approved);
                application.setValidationMessage("Vendor application approved.");
            } else {
                // Build detailed failure message
                StringBuilder failureMessage = new StringBuilder("Validation failed: ");
                if (!bankValid) {
                    failureMessage.append("Bank statement - ").append(bankValidationMessage).append(". ");
                }
                if (!licenseValid) {
                    failureMessage.append("Trading license - ").append(licenseValidationMessage).append(". ");
                }
                application.setStatus(VendorApplications.ApplicationStatus.rejected);
                application.setValidationMessage(failureMessage.toString().trim());
            }
            
            application.setValidatedAt(LocalDateTime.now());
            application.setUpdatedAt(LocalDateTime.now());
            vendorApplicationsRepository.save(application);
            
            System.out.println("Validation completed for application: " + applicationId + 
                " - Status: " + application.getStatus());
                
        } catch (Exception e) {
            System.err.println("Error during async validation for application: " + applicationId);
            e.printStackTrace();
            
            // Update application with error status
            try {
                VendorApplications application = vendorApplicationsRepository.findById(applicationId).orElse(null);
                if (application != null) {
                    application.setStatus(VendorApplications.ApplicationStatus.rejected);
                    application.setValidationMessage("Validation failed due to system error: " + e.getMessage());
                    application.setValidatedAt(LocalDateTime.now());
                    application.setUpdatedAt(LocalDateTime.now());
                    vendorApplicationsRepository.save(application);
                }
            } catch (Exception saveException) {
                System.err.println("Failed to update application status after error: " + saveException.getMessage());
            }
        }
    }

    public VendorApplications getApplicationStatus(String applicationId) {
        return vendorApplicationsRepository.findById(applicationId).orElse(null);
    }

    private String generateApplicationId() {
        // Generate a 7-character application ID
        return "VA" + UUID.randomUUID().toString().substring(0, 5).toUpperCase();
    }
}

