package com.groupe.beantrackserver.service;

import com.groupe.beantrackserver.models.VendorApplications;
import jakarta.mail.MessagingException;
import jakarta.mail.internet.MimeMessage;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
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

    @Async
    public void sendApprovalEmailWithVisit(VendorApplications application) {
        try {
            // Schedule visit date
            LocalDateTime visitDate = visitSchedulingService.scheduleVisit(application.getId());
            
            // Update application with visit date (convert LocalDateTime to LocalDate for existing field)
            application.setVisitScheduled(visitDate.toLocalDate());
            
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
}
