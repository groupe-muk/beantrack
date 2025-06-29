package com.groupe.beantrackserver.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import com.groupe.beantrackserver.models.VendorValidationResponse;
import com.groupe.beantrackserver.service.VendorValidationService;

@RestController
@RequestMapping("/api/vendors")
public class VendorController {

    @Autowired
    private VendorValidationService validationService;

    @PostMapping("/apply")
    public ResponseEntity<VendorValidationResponse> applyVendor(
            @RequestParam("name") String name,
            @RequestParam("email") String email,
            @RequestParam("bankStatement") MultipartFile bankStatement,
            @RequestParam("tradingLicense") MultipartFile tradingLicense) {

        try {
            VendorValidationResponse result = validationService.validateAndStore(name, email, bankStatement, tradingLicense);
            return ResponseEntity.status(result.getStatus().equals("approved") ? HttpStatus.OK : HttpStatus.BAD_REQUEST).body(result);
        } catch (Exception e) {
            e.printStackTrace();
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body(
                    new VendorValidationResponse("error", "Internal server error: " + e.getMessage(), "", "")
            );
        }
    }
}
