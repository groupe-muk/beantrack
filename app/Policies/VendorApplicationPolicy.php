<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VendorApplication;
use Illuminate\Auth\Access\Response;

class VendorApplicationPolicy
{
    /**
     * Determine whether the user can view any vendor applications.
     * Only admins can view all applications.
     */
    public function viewAny(?User $user): bool
    {
        return $user && $user->role === 'admin';
    }

    /**
     * Determine whether the user can view a specific vendor application.
     */
    public function view(?User $user, VendorApplication $vendorApplication): bool
    {
        // Admin can view all applications
        if ($user && $user->role === 'admin') {
            return true;
        }
        
        // Applicant can view their own application (if they have an account)
        if ($user && $vendorApplication->created_user_id === $user->id) {
            return true;
        }
        
        // Public status check with email/token (no user authentication needed)
        if (!$user && request()->has(['email', 'token'])) {
            return $vendorApplication->email === request('email') && 
                   $vendorApplication->status_token === request('token');
        }
        
        return false;
    }

    /**
     * Determine whether anyone can create vendor applications.
     * This is a public endpoint, so no authentication required.
     */
    public function create(?User $user): bool
    {
        return true; // Public endpoint - anyone can apply
    }

    /**
     * Determine whether the user can update the vendor application.
     * Only admins can update applications (for status changes, visit scheduling, etc.)
     */
    public function update(?User $user, VendorApplication $vendorApplication): bool
    {
        return $user && $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the vendor application.
     * Only admins can delete applications.
     */
    public function delete(?User $user, VendorApplication $vendorApplication): bool
    {
        return $user && $user->role === 'admin';
    }

    /**
     * Determine whether the user can approve vendor applications.
     * Only admins can approve applications that are pending or under review.
     */
    public function approve(?User $user, VendorApplication $vendorApplication): bool
    {
        return $user && 
               $user->role === 'admin' && 
               in_array($vendorApplication->status, ['pending', 'under_review']);
    }

    /**
     * Determine whether the user can reject vendor applications.
     * Only admins can reject applications that are pending or under review.
     */
    public function reject(?User $user, VendorApplication $vendorApplication): bool
    {
        return $user && 
               $user->role === 'admin' && 
               in_array($vendorApplication->status, ['pending', 'under_review']);
    }

    /**
     * Determine whether the user can schedule visits for vendor applications.
     * Only admins can schedule visits for approved applications.
     */
    public function scheduleVisit(?User $user, VendorApplication $vendorApplication): bool
    {
        return $user && 
               $user->role === 'admin' && 
               $vendorApplication->status === 'approved';
    }

    /**
     * Determine whether the user can download application documents.
     * Only admins can download uploaded documents.
     */
    public function downloadDocuments(?User $user, VendorApplication $vendorApplication): bool
    {
        return $user && $user->role === 'admin';
    }
}
