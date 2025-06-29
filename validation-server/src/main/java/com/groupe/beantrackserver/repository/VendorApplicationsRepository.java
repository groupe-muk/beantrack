package com.groupe.beantrackserver.repository;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import com.groupe.beantrackserver.models.VendorApplications;

@Repository
public interface VendorApplicationsRepository extends JpaRepository<VendorApplications, String> {

}
