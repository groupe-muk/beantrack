<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class VendorApplication extends Model
{
    use HasFactory;

    protected $table = 'vendor_applications';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'applicant_name',
        'business_name', 
        'phone_number',
        'email',
        'bank_statement_path',
        'trading_license_path',
        'status',
        'visit_scheduled',
        'financial_data',
        'references',
        'license_data',
        'validation_message',
        'validated_at',
        'created_user_id',
        'status_token'
    ];

    protected $casts = [
        'financial_data' => 'array',
        'references' => 'array',
        'license_data' => 'array',
        'validated_at' => 'datetime',
        'visit_scheduled' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Generate unique ID for the application
            if (empty($model->id)) {
                $model->id = 'VA' . strtoupper(Str::random(5)); // e.g., VAAB123
            }
            
            // Generate status token for tracking application
            $model->status_token = Str::random(32);
        });
    }

    /**
     * Get the application directory path
     */
    public function getApplicationDirectoryPath(): string
    {
        return "applications/{$this->id}";
    }

    /**
     * Store bank statement file
     */
    public function storeBankStatement(UploadedFile $file): string
    {
        $filename = "{$this->id}_bank-statement.pdf";
        $path = $this->getApplicationDirectoryPath() . '/' . $filename;
        
        Log::info('Storing bank statement', [
            'id' => $this->id,
            'filename' => $filename,
            'path' => $path,
            'directory' => $this->getApplicationDirectoryPath()
        ]);
        
        try {
            Storage::disk('local')->putFileAs(
                $this->getApplicationDirectoryPath(),
                $file,
                $filename
            );
            
            Log::info('Bank statement stored successfully', ['path' => $path]);
        } catch (Exception $e) {
            Log::error('Failed to store bank statement', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            throw $e;
        }
        
        return $path;
    }

    /**
     * Store trading license file
     */
    public function storeTradingLicense(UploadedFile $file): string
    {
        $filename = "{$this->id}_trading-license.pdf";
        $path = $this->getApplicationDirectoryPath() . '/' . $filename;
        
        Log::info('Storing trading license', [
            'id' => $this->id,
            'filename' => $filename,
            'path' => $path,
            'directory' => $this->getApplicationDirectoryPath()
        ]);
        
        try {
            Storage::disk('local')->putFileAs(
                $this->getApplicationDirectoryPath(),
                $file,
                $filename
            );
            
            Log::info('Trading license stored successfully', ['path' => $path]);
        } catch (Exception $e) {
            Log::error('Failed to store trading license', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            throw $e;
        }
        
        return $path;
    }

    /**
     * Get full path to bank statement file
     */
    public function getBankStatementFullPath(): ?string
    {
        if (!$this->bank_statement_path) {
            return null;
        }
        
        return Storage::disk('local')->path($this->bank_statement_path);
    }

    /**
     * Get full path to trading license file
     */
    public function getTradingLicenseFullPath(): ?string
    {
        if (!$this->trading_license_path) {
            return null;
        }
        
        return Storage::disk('local')->path($this->trading_license_path);
    }

    /**
     * Check if application is pending validation
     */
    public function isPendingValidation(): bool
    {
        return $this->status === 'pending' && is_null($this->validated_at);
    }

    /**
     * Check if application is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if application is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Relationship to the created user account (after approval)
     */
    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending applications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved applications
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected applications
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get applications that need validation
     */
    public function scopeNeedsValidation($query)
    {
        return $query->where('status', 'pending')
                    ->whereNull('validated_at');
    }

    /**
     * Get human-readable status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown'
        };
    }

    /**
     * Get human-readable status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'under_review' => 'blue',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray'
        };
    }

    /**
     * Check if application has uploaded documents
     */
    public function hasDocuments(): bool
    {
        return !empty($this->bank_statement_path) && !empty($this->trading_license_path);
    }

    /**
     * Check if documents exist on disk
     */
    public function documentsExist(): bool
    {
        return $this->hasDocuments() &&
               Storage::disk('local')->exists($this->bank_statement_path) &&
               Storage::disk('local')->exists($this->trading_license_path);
    }

    /**
     * Get the validation age (how long since validation was attempted)
     */
    public function getValidationAgeAttribute(): ?int
    {
        return $this->validated_at ? $this->validated_at->diffInDays(now()) : null;
    }
}
