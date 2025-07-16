package com.groupe.beantrackserver.models;

import java.util.Date;
import javax.persistence.*;

@Entity
@Table(name = "supplier_applications")
public class SupplierApplications {
    
    @Id
    @Column(name = "id")
    private String id;
    
    @Column(name = "applicant_name")
    private String applicantName;
    
    @Column(name = "business_name")
    private String businessName;
    
    @Column(name = "phone_number")
    private String phoneNumber;
    
    @Column(name = "email")
    private String email;
    
    @Column(name = "trading_license_path")
    private String tradingLicensePath;
    
    @Column(name = "bank_statement_path")
    private String bankStatementPath;
    
    @Column(name = "status")
    private String status;
    
    @Column(name = "visit_scheduled")
    private Date visitScheduled;
    
    @Column(name = "financial_data", columnDefinition = "TEXT")
    private String financialData;
    
    @Column(name = "references", columnDefinition = "TEXT")
    private String references;
    
    @Column(name = "license_data", columnDefinition = "TEXT")
    private String licenseData;
    
    @Column(name = "validation_message", columnDefinition = "TEXT")
    private String validationMessage;
    
    @Column(name = "validated_at")
    private Date validatedAt;
    
    @Column(name = "created_user_id")
    private String createdUserId;
    
    @Column(name = "status_token")
    private String statusToken;
    
    @Column(name = "created_at")
    private Date createdAt;
    
    @Column(name = "updated_at")
    private Date updatedAt;
    
    // Default constructor
    public SupplierApplications() {}
    
    // Getters and setters
    public String getId() { return id; }
    public void setId(String id) { this.id = id; }
    
    public String getApplicantName() { return applicantName; }
    public void setApplicantName(String applicantName) { this.applicantName = applicantName; }
    
    public String getBusinessName() { return businessName; }
    public void setBusinessName(String businessName) { this.businessName = businessName; }
    
    public String getPhoneNumber() { return phoneNumber; }
    public void setPhoneNumber(String phoneNumber) { this.phoneNumber = phoneNumber; }
    
    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }
    
    public String getTradingLicensePath() { return tradingLicensePath; }
    public void setTradingLicensePath(String tradingLicensePath) { this.tradingLicensePath = tradingLicensePath; }
    
    public String getBankStatementPath() { return bankStatementPath; }
    public void setBankStatementPath(String bankStatementPath) { this.bankStatementPath = bankStatementPath; }
    
    public String getStatus() { return status; }
    public void setStatus(String status) { this.status = status; }
    
    public Date getVisitScheduled() { return visitScheduled; }
    public void setVisitScheduled(Date visitScheduled) { this.visitScheduled = visitScheduled; }
    
    public String getFinancialData() { return financialData; }
    public void setFinancialData(String financialData) { this.financialData = financialData; }
    
    public String getReferences() { return references; }
    public void setReferences(String references) { this.references = references; }
    
    public String getLicenseData() { return licenseData; }
    public void setLicenseData(String licenseData) { this.licenseData = licenseData; }
    
    public String getValidationMessage() { return validationMessage; }
    public void setValidationMessage(String validationMessage) { this.validationMessage = validationMessage; }
    
    public Date getValidatedAt() { return validatedAt; }
    public void setValidatedAt(Date validatedAt) { this.validatedAt = validatedAt; }
    
    public String getCreatedUserId() { return createdUserId; }
    public void setCreatedUserId(String createdUserId) { this.createdUserId = createdUserId; }
    
    public String getStatusToken() { return statusToken; }
    public void setStatusToken(String statusToken) { this.statusToken = statusToken; }
    
    public Date getCreatedAt() { return createdAt; }
    public void setCreatedAt(Date createdAt) { this.createdAt = createdAt; }
    
    public Date getUpdatedAt() { return updatedAt; }
    public void setUpdatedAt(Date updatedAt) { this.updatedAt = updatedAt; }
    
    @PrePersist
    protected void onCreate() {
        createdAt = new Date();
        updatedAt = new Date();
    }
    
    @PreUpdate
    protected void onUpdate() {
        updatedAt = new Date();
    }
}
