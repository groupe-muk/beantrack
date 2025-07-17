package com.groupe.beantrackserver.service;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.groupe.beantrackserver.models.VendorApplications;
import jakarta.mail.MessagingException;
import jakarta.mail.internet.MimeMessage;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.security.cert.X509Certificate;
import java.time.Duration;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;
import javax.net.ssl.SSLContext;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.mail.javamail.JavaMailSender;
import org.springframework.mail.javamail.MimeMessageHelper;
import org.springframework.scheduling.annotation.Async;
import org.springframework.stereotype.Service;
import org.thymeleaf.TemplateEngine;
import org.thymeleaf.context.Context;

@Service
public class EmailService {

    @Autowired
    private JavaMailSender mailSender;

    @Autowired
    private TemplateEngine templateEngine;

    @Autowired
    private VisitSchedulingService visitSchedulingService;

    @Value("${vendor.email.from}")
    private String fromEmail;

    @Value("${vendor.email.from-name}")
    private String fromName;

    @Value("${vendor.visit.location}")
    private String visitLocation;

    @Value("${vendor.visit.address}")
    private String visitAddress;

    @Value("${vendor.visit.contact}")
    private String visitContact;

    @Value("${laravel.api.base-url}")
    private String laravelApiBaseUrl;

    public void sendApprovalEmailWithVisit(VendorApplications application) {
        try {
            System.out.println("Starting to send approval email for application: " + application.getId());
            
            // Schedule visit date
            LocalDateTime visitDate = visitSchedulingService.scheduleVisit(application.getId());
            System.out.println("Visit scheduled for: " + visitDate);
            
            // Update application with visit date (convert LocalDateTime to LocalDate for existing field)
            application.setVisitScheduled(visitDate.toLocalDate());
            System.out.println("Visit date set on application: " + application.getVisitScheduled());
            
            // Send email
            sendApprovalEmail(application, visitDate);
            
            System.out.println("Approval email sent successfully to: " + application.getApplicantEmail());
        } catch (Exception e) {
            System.err.println("Failed to send approval email to: " + application.getApplicantEmail());
            e.printStackTrace();
        }
    }

    @Async
    public void sendRejectionEmail(VendorApplications application) {
        try {
            sendRejectionEmailInternal(application);
            System.out.println("Rejection email sent successfully to: " + application.getApplicantEmail());
        } catch (Exception e) {
            System.err.println("Failed to send rejection email to: " + application.getApplicantEmail());
            e.printStackTrace();
        }
    }

    private void sendApprovalEmail(VendorApplications application, LocalDateTime visitDate) throws MessagingException {
        try {
            MimeMessage message = mailSender.createMimeMessage();
            MimeMessageHelper helper = new MimeMessageHelper(message, true, "UTF-8");

            // Set email details
            helper.setFrom(fromEmail, fromName);
            helper.setTo(application.getApplicantEmail());
            helper.setSubject("🎉 Document Validation Successful - Visit Scheduled");

            // Create email context
            Context context = new Context();
            context.setVariable("applicantName", application.getApplicantName());
            context.setVariable("businessName", application.getBusinessName());
            context.setVariable("visitDate", visitDate.format(DateTimeFormatter.ofPattern("EEEE, MMMM d, yyyy")));
            context.setVariable("visitTime", visitDate.format(DateTimeFormatter.ofPattern("h:mm a")));
            context.setVariable("visitLocation", visitLocation);
            context.setVariable("visitAddress", visitAddress);
            context.setVariable("visitContact", visitContact);
            context.setVariable("applicationId", application.getId());

            // Process HTML template
            String htmlContent = templateEngine.process("email/approval", context);
            helper.setText(htmlContent, true);

            // Send email
            mailSender.send(message);
        } catch (Exception e) {
            throw new MessagingException("Failed to send approval email", e);
        }
    }

    private void sendRejectionEmailInternal(VendorApplications application) throws MessagingException {
        try {
            MimeMessage message = mailSender.createMimeMessage();
            MimeMessageHelper helper = new MimeMessageHelper(message, true, "UTF-8");

            // Set email details
            helper.setFrom(fromEmail, fromName);
            helper.setTo(application.getApplicantEmail());
            helper.setSubject("Vendor Application Status Update");

            // Create email context
            Context context = new Context();
            context.setVariable("applicantName", application.getApplicantName());
            context.setVariable("businessName", application.getBusinessName());
            context.setVariable("rejectionReasons", application.getValidationMessage());
            context.setVariable("applicationId", application.getId());
            context.setVariable("supportContact", visitContact);

            // Process HTML template
            String htmlContent = templateEngine.process("email/rejection", context);
            helper.setText(htmlContent, true);

            // Send email
            mailSender.send(message);
        } catch (Exception e) {
            throw new MessagingException("Failed to send rejection email", e);
        }
    }

    /**
     * Send rejection email directly with provided parameters
     */
    @Async
    public void sendRejectionEmailDirect(String email, String applicantName, String businessName, String reason) {
        try {
            MimeMessage message = mailSender.createMimeMessage();
            MimeMessageHelper helper = new MimeMessageHelper(message, true, "UTF-8");

            // Set email details
            helper.setFrom(fromEmail, fromName);
            helper.setTo(email);
            helper.setSubject("Vendor Application Rejected - BeanTrack");

            // Create simple HTML content
            String htmlContent = String.format("""
                <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                        <h2 style="color: #8B4513;">Application Status Update</h2>
                        
                        <p>Dear %s,</p>
                        
                        <p>Thank you for your interest in becoming a vendor with BeanTrack. After careful review of your application for <strong>%s</strong>, we regret to inform you that we cannot proceed with your application at this time.</p>
                        
                        <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;">
                            <h4 style="margin: 0 0 10px 0; color: #dc3545;">Reason for Rejection:</h4>
                            <p style="margin: 0;">%s</p>
                        </div>
                        
                        <p>You are welcome to reapply in the future if you believe you can address the concerns mentioned above.</p>
                        
                        <p>If you have any questions, please don't hesitate to contact us at <a href="mailto:%s">%s</a>.</p>
                        
                        <p>Best regards,<br>
                        The BeanTrack Team</p>
                    </div>
                </body>
                </html>
                """, applicantName, businessName, reason, fromEmail, fromEmail);

            helper.setText(htmlContent, true);

            // Send email
            mailSender.send(message);
            System.out.println("Rejection email sent successfully to: " + email);
            
        } catch (Exception e) {
            System.err.println("Failed to send rejection email to: " + email + " - " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Send welcome email with login credentials
     */
    public void sendWelcomeEmailDirect(String email, String applicantName, String businessName, String userId, String password, String loginUrl) {
        System.out.println("=== SENDING WELCOME EMAIL ===");
        System.out.println("To: " + email);
        System.out.println("Applicant Name: " + applicantName);
        System.out.println("Business Name: " + businessName);
        System.out.println("User ID: " + userId);
        System.out.println("Login URL: " + loginUrl);
        
        try {
            MimeMessage message = mailSender.createMimeMessage();
            MimeMessageHelper helper = new MimeMessageHelper(message, true, "UTF-8");

            // Set email details
            helper.setFrom(fromEmail, fromName);
            helper.setTo(email);
            helper.setSubject("Welcome to BeanTrack - Your Account is Ready!");

            System.out.println("Email configured with from: " + fromEmail + ", to: " + email);

            // Create HTML content
            String htmlContent = String.format("""
                <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                        <h2 style="color: #8B4513;">Welcome to BeanTrack!</h2>
                        
                        <p>Dear %s,</p>
                        
                        <p>Congratulations! Your vendor application for <strong>%s</strong> has been approved and you have been successfully added to the BeanTrack system.</p>
                        
                        <div style="background-color: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;">
                            <h4 style="margin: 0 0 15px 0; color: #155724;">Your Login Credentials:</h4>
                            <p style="margin: 5px 0;"><strong>User ID:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Email:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Password:</strong> %s</p>
                            <p style="margin: 15px 0 5px 0;"><strong>Login URL:</strong> <a href="%s" style="color: #8B4513;">%s</a></p>
                        </div>
                        
                        <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                            <h4 style="margin: 0 0 10px 0; color: #856404;">Important Security Notice:</h4>
                            <p style="margin: 0;">For your security, please change your password after your first login. You can do this from your account settings page.</p>
                        </div>
                        
                        <p>You can now access the BeanTrack vendor portal to:</p>
                        <ul>
                            <li>Manage your business profile</li>
                            <li>View and respond to orders</li>
                            <li>Track your sales and performance</li>
                            <li>Access reports and analytics</li>
                        </ul>
                        
                        <p>If you have any questions or need assistance, please contact our support team at <a href="mailto:%s">%s</a>.</p>
                        
                        <p>We look forward to a successful partnership!</p>
                        
                        <p>Best regards,<br>
                        The BeanTrack Team</p>
                    </div>
                </body>
                </html>
                """, applicantName, businessName, userId, email, password, loginUrl, loginUrl, fromEmail, fromEmail);

            helper.setText(htmlContent, true);

            System.out.println("Attempting to send email...");
            // Send email
            mailSender.send(message);
            System.out.println("Welcome email sent successfully to: " + email);
            
        } catch (Exception e) {
            System.err.println("Failed to send welcome email to: " + email + " - " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Send supplier welcome email with login credentials and supply center information
     */
    public void sendSupplierWelcomeEmailDirect(String email, String applicantName, String businessName, String userId, String password, String loginUrl, String supplyCenterName, String supplyCenterLocation) {
        System.out.println("=== SENDING SUPPLIER WELCOME EMAIL ===");
        System.out.println("To: " + email);
        System.out.println("Applicant Name: " + applicantName);
        System.out.println("Business Name: " + businessName);
        System.out.println("User ID: " + userId);
        System.out.println("Supply Center: " + supplyCenterName + " at " + supplyCenterLocation);
        System.out.println("Login URL: " + loginUrl);
        
        try {
            MimeMessage message = mailSender.createMimeMessage();
            MimeMessageHelper helper = new MimeMessageHelper(message, true, "UTF-8");

            // Set email details
            helper.setFrom(fromEmail, fromName);
            helper.setTo(email);
            helper.setSubject("Welcome to BeanTrack - Your Supplier Account is Ready!");

            System.out.println("Email configured with from: " + fromEmail + ", to: " + email);

            // Create HTML content with supply center information
            String supplyCenterSection = "";
            if (supplyCenterName != null && !supplyCenterName.isEmpty()) {
                supplyCenterSection = String.format("""
                    <div style="background-color: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff;">
                        <h4 style="margin: 0 0 15px 0; color: #004085;">📍 Your Assigned Supply Center:</h4>
                        <p style="margin: 5px 0;"><strong>Supply Center Name:</strong> %s</p>
                        <p style="margin: 5px 0;"><strong>Location:</strong> %s</p>
                        <p style="margin: 10px 0 0 0; font-style: italic; color: #6c757d;">This is where you will be delivering your coffee supplies. Please save this information for your records.</p>
                    </div>
                    """, supplyCenterName, supplyCenterLocation != null ? supplyCenterLocation : "Location information will be provided separately");
            }

            String htmlContent = String.format("""
                <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                        <h2 style="color: #8B4513;">Welcome to BeanTrack Supplier Portal!</h2>
                        
                        <p>Dear %s,</p>
                        
                        <p>Congratulations! Your supplier application for <strong>%s</strong> has been approved and you have been successfully added to the BeanTrack system as an authorized supplier.</p>
                        
                        <div style="background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
                            <h4 style="margin: 0 0 15px 0; color: #155724;">🔐 Your Login Credentials:</h4>
                            <p style="margin: 5px 0;"><strong>User ID:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Email:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Password:</strong> %s</p>
                            <p style="margin: 15px 0 5px 0;"><strong>Login URL:</strong> <a href="%s" style="color: #8B4513;">%s</a></p>
                        </div>
                        
                        %s
                        
                        <div style="background-color: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
                            <h4 style="margin: 0 0 10px 0; color: #856404;">🔒 Security Notice:</h4>
                            <p style="margin: 0; color: #856404;"><strong>Please change your password after your first login for security purposes.</strong></p>
                        </div>
                        
                        <h3 style="color: #8B4513;">🚀 What You Can Do Now:</h3>
                        <p>You can now access the BeanTrack supplier portal to:</p>
                        <ul style="color: #555;">
                            <li><strong>Manage your supplier profile</strong> and business information</li>
                            <li><strong>View and manage orders</strong> from our coffee processing facility</li>
                            <li><strong>Track your deliveries</strong> and supply schedule</li>
                            <li><strong>Monitor your inventory</strong> and stock levels</li>
                            <li><strong>Access reports</strong> on your supply performance</li>
                            <li><strong>Communicate with our procurement team</strong></li>
                        </ul>
                        
                        <h3 style="color: #8B4513;">📞 Need Help?</h3>
                        <p>If you have any questions or need assistance getting started, please contact our supplier support team:</p>
                        <ul style="color: #555;">
                            <li><strong>Email:</strong> <a href="mailto:%s" style="color: #8B4513;">%s</a></li>
                            <li><strong>Phone:</strong> +256 700 123456</li>
                        </ul>
                        
                        <p style="margin-top: 30px;">We're excited to partner with you and look forward to a successful business relationship!</p>
                        
                        <p>Best regards,<br>
                        <strong>The BeanTrack Supplier Relations Team</strong></p>
                    </div>
                    
                    <div style="background-color: #f8f9fa; padding: 15px; margin-top: 30px; border-top: 1px solid #dee2e6; text-align: center;">
                        <p style="margin: 0; font-size: 12px; color: #6c757d;">
                            This is an automated message from BeanTrack. Please do not reply directly to this email.
                        </p>
                    </div>
                </body>
                </html>
                """, 
                applicantName, businessName, userId, email, password, loginUrl, loginUrl, supplyCenterSection, fromEmail, fromEmail);

            helper.setText(htmlContent, true);

            System.out.println("Attempting to send supplier welcome email...");
            // Send email
            mailSender.send(message);
            System.out.println("Supplier welcome email sent successfully to: " + email);

        } catch (Exception e) {
            System.err.println("Failed to send supplier welcome email to: " + email + " - " + e.getMessage());
            e.printStackTrace();
        }
    }

    @Async
    public void sendSupplierVisitScheduledEmail(String email, String applicantName, String businessName, String visitDate) {
        try {
            Context context = new Context();
            context.setVariable("applicantName", applicantName);
            context.setVariable("businessName", businessName);
            context.setVariable("visitDate", visitDate);
            context.setVariable("visitLocation", visitLocation);
            context.setVariable("visitAddress", visitAddress);
            context.setVariable("visitContact", visitContact);

            String htmlContent = templateEngine.process("email/supplier-visit-scheduled", context);

            MimeMessage message = mailSender.createMimeMessage();
            MimeMessageHelper helper = new MimeMessageHelper(message, true, "UTF-8");

            helper.setFrom(fromEmail, fromName);
            helper.setTo(email);
            helper.setSubject("Supplier Visit Scheduled - " + businessName);
            helper.setText(htmlContent, true);

            mailSender.send(message);
            System.out.println("Supplier visit scheduled email sent successfully to: " + email);

        } catch (Exception e) {
            System.err.println("Failed to send supplier visit scheduled email to: " + email + " - " + e.getMessage());
            e.printStackTrace();
        }
    }

    @Async
    public void sendSupplierRejectionEmail(String email, String applicantName, String businessName, String reason) {
        try {
            Context context = new Context();
            context.setVariable("applicantName", applicantName);
            context.setVariable("businessName", businessName);
            context.setVariable("rejectionReason", reason != null ? reason : "Application did not meet our requirements.");

            String htmlContent = templateEngine.process("supplier-rejection", context);

            MimeMessage message = mailSender.createMimeMessage();
            MimeMessageHelper helper = new MimeMessageHelper(message, true, "UTF-8");

            helper.setFrom(fromEmail, fromName);
            helper.setTo(email);
            helper.setSubject("Supplier Application Update - " + businessName);
            helper.setText(htmlContent, true);

            mailSender.send(message);
            System.out.println("Supplier rejection email sent successfully to: " + email);

        } catch (Exception e) {
            System.err.println("Failed to send supplier rejection email to: " + email + " - " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Send notification to all administrators about vendor verification and visit scheduling
     */
    @Async
    public void sendVendorVisitNotificationToAdmins(VendorApplications application, LocalDateTime visitDate) {
        try {
            // Get all admin emails
            java.util.List<String> adminEmails = getAdminEmails();
            
            if (adminEmails.isEmpty()) {
                System.out.println("No admin emails found for vendor visit notification");
                return;
            }

            String formattedDate = visitDate.format(DateTimeFormatter.ofPattern("EEEE, MMMM d, yyyy 'at' h:mm a"));
            
            for (String adminEmail : adminEmails) {
                sendVendorVisitNotificationToAdmin(adminEmail, application, formattedDate);
            }
            
            System.out.println("Vendor visit notification sent to " + adminEmails.size() + " administrators");
            
        } catch (Exception e) {
            System.err.println("Failed to send vendor visit notifications to admins: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Send notification to all administrators about supplier verification and visit scheduling
     */
    @Async
    public void sendSupplierVisitNotificationToAdmins(String applicantName, String businessName, String email, String visitDate) {
        try {
            // Get all admin emails
            java.util.List<String> adminEmails = getAdminEmails();
            
            if (adminEmails.isEmpty()) {
                System.out.println("No admin emails found for supplier visit notification");
                return;
            }
            
            for (String adminEmail : adminEmails) {
                sendSupplierVisitNotificationToAdmin(adminEmail, applicantName, businessName, email, visitDate);
            }
            
            System.out.println("Supplier visit notification sent to " + adminEmails.size() + " administrators");
            
        } catch (Exception e) {
            System.err.println("Failed to send supplier visit notifications to admins: " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Send vendor visit notification to a specific admin
     */
    private void sendVendorVisitNotificationToAdmin(String adminEmail, VendorApplications application, String formattedDate) {
        try {
            MimeMessage message = mailSender.createMimeMessage();
            MimeMessageHelper helper = new MimeMessageHelper(message, true, "UTF-8");

            helper.setFrom(fromEmail, fromName);
            helper.setTo(adminEmail);
            helper.setSubject("🏢 Vendor Visit Scheduled - " + application.getBusinessName());

            String htmlContent = String.format("""
                <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                        <h2 style="color: #8B4513;">📋 Vendor Visit Scheduled</h2>
                        
                        <p>Dear Administrator,</p>
                        
                        <p>A vendor application has been <strong>verified and approved</strong>. A visitation date has been scheduled and you need to coordinate the site visit.</p>
                        
                        <div style="background-color: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
                            <h3 style="margin: 0 0 15px 0; color: #28a745;">📊 Vendor Details</h3>
                            <p style="margin: 5px 0;"><strong>Business Name:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Applicant Name:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Email:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Phone:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Application ID:</strong> %s</p>
                        </div>
                        
                        <div style="background-color: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
                            <h3 style="margin: 0 0 15px 0; color: #856404;">📅 Visit Schedule</h3>
                            <p style="margin: 5px 0;"><strong>Scheduled Date & Time:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Location:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Address:</strong> %s</p>
                        </div>
                        
                        <div style="background-color: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;">
                            <h3 style="margin: 0 0 15px 0; color: #721c24;">⚠️ Action Required</h3>
                            <p style="margin: 0;"><strong>Please reach out to the vendor to confirm the specific location for the site visit.</strong> The vendor has been notified of the scheduled date and is expecting your contact.</p>
                        </div>
                        
                        <p>Contact the vendor at <a href="mailto:%s">%s</a> or %s to finalize visit details.</p>
                        
                        <p>Best regards,<br>
                        BeanTrack System</p>
                    </div>
                </body>
                </html>
                """, 
                application.getBusinessName(),
                application.getApplicantName(),
                application.getApplicantEmail(),
                application.getPhoneNumber() != null ? application.getPhoneNumber() : "Not provided",
                application.getId(),
                formattedDate,
                visitLocation,
                visitAddress,
                application.getApplicantEmail(),
                application.getApplicantEmail(),
                application.getPhoneNumber() != null ? application.getPhoneNumber() : "Contact via email"
            );

            helper.setText(htmlContent, true);
            mailSender.send(message);

        } catch (Exception e) {
            System.err.println("Failed to send vendor visit notification to admin: " + adminEmail + " - " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Send supplier visit notification to a specific admin
     */
    private void sendSupplierVisitNotificationToAdmin(String adminEmail, String applicantName, String businessName, String supplierEmail, String visitDate) {
        try {
            MimeMessage message = mailSender.createMimeMessage();
            MimeMessageHelper helper = new MimeMessageHelper(message, true, "UTF-8");

            helper.setFrom(fromEmail, fromName);
            helper.setTo(adminEmail);
            helper.setSubject("🏭 Supplier Visit Scheduled - " + businessName);

            String htmlContent = String.format("""
                <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                        <h2 style="color: #8B4513;">📋 Supplier Visit Scheduled</h2>
                        
                        <p>Dear Administrator,</p>
                        
                        <p>A supplier application has been <strong>verified and approved</strong>. A visitation date has been scheduled and you need to coordinate the site visit.</p>
                        
                        <div style="background-color: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
                            <h3 style="margin: 0 0 15px 0; color: #28a745;">🏭 Supplier Details</h3>
                            <p style="margin: 5px 0;"><strong>Business Name:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Applicant Name:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Email:</strong> %s</p>
                        </div>
                        
                        <div style="background-color: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid: #ffc107;">
                            <h3 style="margin: 0 0 15px 0; color: #856404;">📅 Visit Schedule</h3>
                            <p style="margin: 5px 0;"><strong>Scheduled Date:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Meeting Location:</strong> %s</p>
                            <p style="margin: 5px 0;"><strong>Address:</strong> %s</p>
                        </div>
                        
                        <div style="background-color: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;">
                            <h3 style="margin: 0 0 15px 0; color: #721c24;">⚠️ Action Required</h3>
                            <p style="margin: 0;"><strong>Please reach out to the supplier to confirm the specific location for the site visit.</strong> The supplier has been notified of the scheduled date and is expecting your contact.</p>
                        </div>
                        
                        <p>Contact the supplier at <a href="mailto:%s">%s</a> to finalize visit details and location.</p>
                        
                        <p>Best regards,<br>
                        BeanTrack System</p>
                    </div>
                </body>
                </html>
                """, 
                businessName,
                applicantName,
                supplierEmail,
                visitDate,
                visitLocation,
                visitAddress,
                supplierEmail,
                supplierEmail
            );

            helper.setText(htmlContent, true);
            mailSender.send(message);

        } catch (Exception e) {
            System.err.println("Failed to send supplier visit notification to admin: " + adminEmail + " - " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Get all admin emails from the Laravel API
     */
    private List<String> getAdminEmails() {
        try {
            // Make HTTP request to Laravel API to get admin users
            String apiUrl = laravelApiBaseUrl + "/api/admin/emails";
            System.out.println("Attempting to fetch admin emails from: " + apiUrl);
            
            // Create a trust manager that accepts all certificates (for local development)
            TrustManager[] trustAllCerts = new TrustManager[] {
                new X509TrustManager() {
                    public X509Certificate[] getAcceptedIssuers() { return null; }
                    public void checkClientTrusted(X509Certificate[] certs, String authType) { }
                    public void checkServerTrusted(X509Certificate[] certs, String authType) { }
                }
            };
            
            SSLContext sslContext = SSLContext.getInstance("SSL");
            sslContext.init(null, trustAllCerts, new java.security.SecureRandom());
            
            HttpClient client = HttpClient.newBuilder()
                .connectTimeout(Duration.ofSeconds(5))
                .sslContext(sslContext)
                .build();
                
            HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(apiUrl))
                .timeout(Duration.ofSeconds(10)) // Shorter timeout
                .header("Accept", "application/json")
                .GET()
                .build();
                
            System.out.println("Sending HTTP request to Laravel API...");
            HttpResponse<String> response = client.send(request, HttpResponse.BodyHandlers.ofString());
            
            System.out.println("API Response - Status: " + response.statusCode() + ", Body: " + response.body());
            
            if (response.statusCode() == 200) {
                // Parse JSON response
                ObjectMapper mapper = new ObjectMapper();
                JsonNode jsonNode = mapper.readTree(response.body());
                
                List<String> adminEmails = new ArrayList<>();
                
                // Check if response has 'data' field (Laravel API format)
                if (jsonNode.has("data") && jsonNode.get("data").isArray()) {
                    for (JsonNode emailNode : jsonNode.get("data")) {
                        if (emailNode.isTextual()) {
                            adminEmails.add(emailNode.asText());
                        }
                    }
                } else if (jsonNode.isArray()) {
                    // Direct array response
                    for (JsonNode emailNode : jsonNode) {
                        if (emailNode.isTextual()) {
                            adminEmails.add(emailNode.asText());
                        }
                    }
                }
                
                if (!adminEmails.isEmpty()) {
                    System.out.println("Successfully fetched " + adminEmails.size() + " admin emails from API: " + adminEmails);
                    return adminEmails;
                } else {
                    System.err.println("API returned empty admin emails list");
                }
            } else {
                System.err.println("API request failed with status: " + response.statusCode() + ", body: " + response.body());
            }
            
        } catch (Exception e) {
            System.err.println("Failed to fetch admin emails from API. Exception type: " + e.getClass().getSimpleName());
            System.err.println("Exception message: " + (e.getMessage() != null ? e.getMessage() : "null"));
            System.err.println("Will use fallback admin emails");
        }
        
        // Return realistic fallback admin emails (replace with actual admin emails)
        System.out.println("Using fallback admin email list");
        List<String> fallbackEmails = new ArrayList<>();
        fallbackEmails.add("admin@beantrack.com");
        fallbackEmails.add("eunicjasmine84@gmail.com"); // Add the actual admin email
        return fallbackEmails;
    }
}
