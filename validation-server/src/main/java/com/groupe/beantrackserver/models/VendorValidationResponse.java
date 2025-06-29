package com.groupe.beantrackserver.models;

public class VendorValidationResponse {
    private String status;
    private String message;
    private String bankPath;
    private String tradingLicense;


    public VendorValidationResponse(String status, String message, String bankPath, String tradingLicense) {
        this.status = status;
        this.message = message;
        this.bankPath = bankPath;
        this.tradingLicense = tradingLicense;
        
    }

    public String getStatus() {
        return status;
    }

    public void setStatus(String status) {
        this.status = status;
    }

  

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

      public String getBankPath() {
        return bankPath;
    }

    public void setBankPath(String bankPath) {
        this.bankPath = bankPath;
    }

      public String getTradingLicense() {
        return tradingLicense;
    }

    public void setTradingLicense(String tradingLicense) {
        this.tradingLicense = tradingLicense;
    }
} 
