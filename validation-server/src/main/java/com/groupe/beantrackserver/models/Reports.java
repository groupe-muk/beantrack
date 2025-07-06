package com.groupe.beantrackserver.models;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;
import java.time.LocalDateTime;

@Entity
@Table(name = "Reports")
public class Reports {

    public enum ReportType {
        inventory,
        order_summary,
        performance
    }

    public enum ReportFrequency {
        weekly,
        monthly
    }

    @Id
    @Column(name = "id", length = 6)
    private String id;

    @Enumerated(EnumType.STRING)
    @Column(name = "type", nullable = false)
    private ReportType type;

    @ManyToOne
    @JoinColumn(name = "recipient_id", nullable = false)
    private Users recipient;

    @Enumerated(EnumType.STRING)
    @Column(name = "frequency", nullable = false)
    private ReportFrequency frequency;

    @Column(name = "content", nullable = false, columnDefinition = "JSON") // Maps JSON as String
    private String content; // Storing JSON as String, you'd typically handle JSON parsing/serialization in service layer

    @Column(name = "last_sent")
    private LocalDateTime lastSent;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    public Reports() {
    }

    // Getters and Setters
    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public ReportType getType() {
        return type;
    }

    public void setType(ReportType type) {
        this.type = type;
    }

    public Users getRecipient() {
        return recipient;
    }

    public void setRecipient(Users recipient) {
        this.recipient = recipient;
    }

    public ReportFrequency getFrequency() {
        return frequency;
    }

    public void setFrequency(ReportFrequency frequency) {
        this.frequency = frequency;
    }

    public String getContent() {
        return content;
    }

    public void setContent(String content) {
        this.content = content;
    }

    public LocalDateTime getLastSent() {
        return lastSent;
    }

    public void setLastSent(LocalDateTime lastSent) {
        this.lastSent = lastSent;
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