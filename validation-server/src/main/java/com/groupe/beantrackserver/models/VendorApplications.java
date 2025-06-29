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
@Table(name = "VendorApplications")
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

    @ManyToOne
    @JoinColumn(name = "applicant_id", nullable = false)
    private Users applicant;

    @Column(name = "financial_data", columnDefinition = "JSON")
    private String financialData; // Storing JSON as String

    @Column(name = "references", columnDefinition = "JSON")
    private String references; // Storing JSON as String

    @Column(name = "license_data", columnDefinition = "JSON")
    private String licenseData; // Storing JSON as String

    @Enumerated(EnumType.STRING)
    @Column(name = "status", nullable = false)
    private ApplicationStatus status;

    @Column(name = "visit_scheduled")
    private LocalDate visitScheduled;

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

    public Users getApplicant() {
        return applicant;
    }

    public void setApplicant(Users applicant) {
        this.applicant = applicant;
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
