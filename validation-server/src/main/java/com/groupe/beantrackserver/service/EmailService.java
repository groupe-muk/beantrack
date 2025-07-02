package com.groupe.beantrackserver.service;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.mail.javamail.JavaMailSender;
import org.springframework.mail.javamail.MimeMessageHelper;
import org.springframework.scheduling.annotation.Async;
import org.springframework.stereotype.Service;
import org.thymeleaf.TemplateEngine;
import org.thymeleaf.context.Context;

import com.groupe.beantrackserver.models.VendorApplications;

import jakarta.mail.MessagingException;
import jakarta.mail.internet.MimeMessage;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

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
}
