package com.groupe.beantrackserver.repository;

import com.groupe.beantrackserver.models.SupplierApplications;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface SupplierApplicationsRepository extends JpaRepository<SupplierApplications, String> {
    
    // Find by email
    Optional<SupplierApplications> findByEmail(String email);
    
    // Find by status
    List<SupplierApplications> findByStatus(String status);
    
    // Find by status token
    Optional<SupplierApplications> findByStatusToken(String statusToken);
    
    // Find by business name
    List<SupplierApplications> findByBusinessNameContainingIgnoreCase(String businessName);
    
    // Find by applicant name
    List<SupplierApplications> findByApplicantNameContainingIgnoreCase(String applicantName);
    
    // Find pending applications
    @Query("SELECT s FROM SupplierApplications s WHERE s.status = 'pending' OR s.status = 'submitted'")
    List<SupplierApplications> findPendingApplications();
    
    // Find applications that need validation
    @Query("SELECT s FROM SupplierApplications s WHERE s.status = 'submitted' AND s.validatedAt IS NULL")
    List<SupplierApplications> findApplicationsNeedingValidation();
    
    // Find applications with scheduled visits
    @Query("SELECT s FROM SupplierApplications s WHERE s.visitScheduled IS NOT NULL AND s.status = 'under_review'")
    List<SupplierApplications> findApplicationsWithScheduledVisits();
    
    // Count applications by status
    @Query("SELECT COUNT(s) FROM SupplierApplications s WHERE s.status = :status")
    long countByStatus(@Param("status") String status);
    
    // Find recent applications (last 30 days)
    @Query(value = "SELECT * FROM supplier_applications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY created_at DESC", 
           nativeQuery = true)
    List<SupplierApplications> findRecentApplications();
    
    // Find applications by date range
    @Query("SELECT s FROM SupplierApplications s WHERE s.createdAt BETWEEN :startDate AND :endDate ORDER BY s.createdAt DESC")
    List<SupplierApplications> findByDateRange(@Param("startDate") java.util.Date startDate, 
                                             @Param("endDate") java.util.Date endDate);
}
