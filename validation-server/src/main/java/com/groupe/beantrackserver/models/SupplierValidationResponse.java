package com.groupe.beantrackserver.models;

import java.util.Map;

public class SupplierValidationResponse {
    private String status;
    private String message;
    private Map<String, Object> financial_data;
    private Map<String, Object> license_data;
    private Map<String, Object> references;
    private String visit_date;

    // Default constructor
    public SupplierValidationResponse() {}

    // Constructor with basic fields
    public SupplierValidationResponse(String status, String message) {
        this.status = status;
        this.message = message;
    }

    // Getters and setters
    public String getStatus() { return status; }
    public void setStatus(String status) { this.status = status; }

    public String getMessage() { return message; }
    public void setMessage(String message) { this.message = message; }

    public Map<String, Object> getFinancial_data() { return financial_data; }
    public void setFinancial_data(Map<String, Object> financial_data) { this.financial_data = financial_data; }

    public Map<String, Object> getLicense_data() { return license_data; }
    public void setLicense_data(Map<String, Object> license_data) { this.license_data = license_data; }

    public Map<String, Object> getReferences() { return references; }
    public void setReferences(Map<String, Object> references) { this.references = references; }

    public String getVisit_date() { return visit_date; }
    public void setVisit_date(String visit_date) { this.visit_date = visit_date; }

    @Override
    public String toString() {
        return "SupplierValidationResponse{" +
                "status='" + status + '\'' +
                ", message='" + message + '\'' +
                ", financial_data=" + financial_data +
                ", license_data=" + license_data +
                ", references=" + references +
                ", visit_date='" + visit_date + '\'' +
                '}';
    }
}
