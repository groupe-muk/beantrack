package com.groupe.beantrackserver.models;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;
import java.time.LocalDate;
import java.time.LocalDateTime;

@Entity
@Table(name = "vendor_applications")
public class VendorApplications {

    public enum ApplicationStatus {
        pending,
        under_review,
        approved,
        rejected
    }

    @Id
    @Column(name = "id", length = 7)
    private String id;

    @Column(name = "applicant_name", nullable = false)
    private String applicantName;

    @Column(name = "business_name", nullable = false)
    private String businessName;

    @Column(name = "phone_number", nullable = false)
    private String phoneNumber;

    @Column(name = "email", nullable = false)
    private String email;

    @Column(name = "bank_statement_path")
    private String bankStatementPath;

    @Column(name = "trading_license_path")
    private String tradingLicensePath;

    @ManyToOne
    @JoinColumn(name = "created_user_id")
    private Users createdUser;

    @Column(name = "financial_data", columnDefinition = "LONGTEXT")
    private String financialData;

    @Column(name = "`references`", columnDefinition = "LONGTEXT")
    private String references;

    @Column(name = "license_data", columnDefinition = "LONGTEXT")
    private String licenseData;

    @Enumerated(EnumType.STRING)
    @Column(name = "status", nullable = false)
    private ApplicationStatus status = ApplicationStatus.pending;

    @Column(name = "visit_scheduled")
    private LocalDate visitScheduled;

    @Column(name = "validation_message", columnDefinition = "TEXT")
    private String validationMessage;

    @Column(name = "validated_at")
    private LocalDateTime validatedAt;

    @Column(name = "status_token", length = 32)
    private String statusToken;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    public VendorApplications() {
    }

    // Getters and Setters
    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getApplicantName() {
        return applicantName;
    }

    public void setApplicantName(String applicantName) {
        this.applicantName = applicantName;
    }

    public String getBusinessName() {
        return businessName;
    }

    public void setBusinessName(String businessName) {
        this.businessName = businessName;
    }

    public String getPhoneNumber() {
        return phoneNumber;
    }

    public void setPhoneNumber(String phoneNumber) {
        this.phoneNumber = phoneNumber;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getApplicantEmail() {
        return email;
    }

    public String getBankStatementPath() {
        return bankStatementPath;
    }

    public void setBankStatementPath(String bankStatementPath) {
        this.bankStatementPath = bankStatementPath;
    }

    public String getTradingLicensePath() {
        return tradingLicensePath;
    }

    public void setTradingLicensePath(String tradingLicensePath) {
        this.tradingLicensePath = tradingLicensePath;
    }

    public Users getCreatedUser() {
        return createdUser;
    }

    public void setCreatedUser(Users createdUser) {
        this.createdUser = createdUser;
    }

    public String getValidationMessage() {
        return validationMessage;
    }

    public void setValidationMessage(String validationMessage) {
        this.validationMessage = validationMessage;
    }

    public LocalDateTime getValidatedAt() {
        return validatedAt;
    }

    public void setValidatedAt(LocalDateTime validatedAt) {
        this.validatedAt = validatedAt;
    }

    public String getStatusToken() {
        return statusToken;
    }

    public void setStatusToken(String statusToken) {
        this.statusToken = statusToken;
    }

    public String getFinancialData() {
        return financialData;
    }

    public void setFinancialData(String financialData) {
        this.financialData = financialData;
    }

    public String getReferences() {
        return references;
    }

    public void setReferences(String references) {
        this.references = references;
    }

    public String getLicenseData() {
        return licenseData;
    }

    public void setLicenseData(String licenseData) {
        this.licenseData = licenseData;
    }

    public ApplicationStatus getStatus() {
        return status;
    }

    public void setStatus(ApplicationStatus status) {
        this.status = status;
    }

    public LocalDate getVisitScheduled() {
        return visitScheduled;
    }

    public void setVisitScheduled(LocalDate visitScheduled) {
        this.visitScheduled = visitScheduled;
    }

    public LocalDateTime getCreatedAt() {
        return createdAt;
    }

    public void setCreatedAt(LocalDateTime createdAt) {
        this.createdAt = createdAt;
    }

    public LocalDateTime getUpdatedAt() {
        return updatedAt;
    }

    public void setUpdatedAt(LocalDateTime updatedAt) {
        this.updatedAt = updatedAt;
    }
}
