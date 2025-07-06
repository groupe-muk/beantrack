package com.groupe.beantrackserver.service;

import org.springframework.stereotype.Service;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.time.DayOfWeek;

@Service
public class VisitSchedulingService {

    // Available visit time slots
    private final LocalTime[] timeSlots = {
        LocalTime.of(9, 0),   // 9:00 AM
        LocalTime.of(11, 0),  // 11:00 AM
        LocalTime.of(14, 0),  // 2:00 PM
        LocalTime.of(16, 0)   // 4:00 PM
    };

    /**
     * Schedules a visit for an approved vendor application
     * @param applicationId The application ID
     * @return The scheduled visit date and time
     */
    public LocalDateTime scheduleVisit(String applicationId) {
        // For now, we'll use a simple scheduling algorithm
        // In a production system, this would check existing appointments
        
        LocalDateTime now = LocalDateTime.now();
        LocalDateTime proposedDate = now.plusDays(3); // Schedule 3 days ahead
        
        // Ensure it's a business day (Monday-Friday)
        while (isWeekend(proposedDate)) {
            proposedDate = proposedDate.plusDays(1);
        }
        
        // Assign the first available time slot (9:00 AM for simplicity)
        // In a production system, this would check for conflicts
        LocalDateTime visitDateTime = proposedDate.toLocalDate().atTime(timeSlots[0]);
        
        System.out.println("Scheduled visit for application " + applicationId + 
                          " on " + visitDateTime.format(java.time.format.DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm")));
        
        return visitDateTime;
    }

    /**
     * Checks if the given date falls on a weekend
     * @param date The date to check
     * @return true if it's Saturday or Sunday
     */
    private boolean isWeekend(LocalDateTime date) {
        DayOfWeek dayOfWeek = date.getDayOfWeek();
        return dayOfWeek == DayOfWeek.SATURDAY || dayOfWeek == DayOfWeek.SUNDAY;
    }

    /**
     * Gets the next available time slot for a given date
     * This is a simplified version - in production, it would check existing bookings
     * @param date The date to check
     * @return The next available time slot
     */
    private LocalTime getNextAvailableTimeSlot(LocalDateTime date) {
        // For simplicity, always return the first slot
        // In production, this would query the database for existing appointments
        return timeSlots[0];
    }
}
