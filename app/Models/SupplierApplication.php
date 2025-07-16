<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class SupplierApplication extends Model
{
    use HasFactory;

    protected $table = 'supplier_applications';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'applicant_name',
        'business_name', 
        'phone_number',
        'email',
        'trading_license_path',
        'bank_statement_path',
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
                $model->id = 'SA' . strtoupper(Str::random(5)); // e.g., SAAB123
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
        return "supplier-applications/{$this->id}";
    }

    /**
     * Store trading license file
     */
    public function storeTradingLicense(UploadedFile $file): string
    {
        $filename = "{$this->id}_trading-license.pdf";
        $path = $this->getApplicationDirectoryPath() . '/' . $filename;
        
        Log::info('Storing supplier trading license', [
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
            
            Log::info('Supplier trading license stored successfully', ['path' => $path]);
        } catch (Exception $e) {
            Log::error('Failed to store supplier trading license', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            throw $e;
        }
        
        return $path;
    }

    /**
     * Store bank statement file
     */
    public function storeBankStatement(UploadedFile $file): string
    {
        $filename = "{$this->id}_bank-statement.pdf";
        $path = $this->getApplicationDirectoryPath() . '/' . $filename;
        
        Log::info('Storing supplier bank statement', [
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
            
            Log::info('Supplier bank statement stored successfully', ['path' => $path]);
        } catch (Exception $e) {
            Log::error('Failed to store supplier bank statement', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);
            throw $e;
        }
        
        return $path;
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
     * Check if all required documents are uploaded
     */
    public function hasAllDocuments(): bool
    {
        return !empty($this->trading_license_path) && !empty($this->bank_statement_path);
    }

    /**
     * Get the status badge color class
     */
    public function getStatusColorClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'under_review' => 'bg-blue-100 text-blue-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Check if application can be edited
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['pending', 'rejected']);
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
     * Check if application is under review
     */
    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    /**
     * Get the created user relationship
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
     * Scope for recent applications
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
