<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Report;

class ReportDelivered extends Mailable
{
    use Queueable, SerializesModels;

    public $report;
    public $recipient;
    public $filePath;

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report, $recipient, $filePath = null)
    {
        $this->report = $report;
        $this->recipient = $recipient;
        $this->filePath = $filePath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Generate role-based title for email subject
        $userId = $this->report->created_by ?? null;
        $user = $userId ? \App\Models\User::find($userId) : null;
        $roleBasedTitle = $this->generateRoleBasedTitle($this->report->name, $user);
        
        return new Envelope(
            subject: 'BeanTrack Report: ' . $roleBasedTitle,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Generate role-based title for email content
        $userId = $this->report->created_by ?? null;
        $user = $userId ? \App\Models\User::find($userId) : null;
        $roleBasedTitle = $this->generateRoleBasedTitle($this->report->name, $user);
        
        return new Content(
            view: 'emails.report-delivered',
            with: [
                'report' => $this->report,
                'recipient' => $this->recipient,
                'reportName' => $roleBasedTitle,
                'generatedAt' => $this->report->last_sent ? $this->report->last_sent->format('F j, Y g:i A') : 'Recently',
                'format' => strtoupper($this->report->format ?? 'PDF'),
                'fileSize' => $this->report->file_size ?? 'N/A'
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        \Log::info('Processing attachments for ReportDelivered email', [
            'report_id' => $this->report->id,
            'file_path' => $this->filePath,
            'file_exists' => $this->filePath ? file_exists($this->filePath) : false,
            'file_size' => $this->filePath && file_exists($this->filePath) ? filesize($this->filePath) : 0
        ]);
        
        if ($this->filePath && file_exists($this->filePath)) {
            $attachment = Attachment::fromPath($this->filePath)
                ->as($this->getAttachmentName())
                ->withMime($this->getMimeType());
                
            $attachments[] = $attachment;
            
            \Log::info('Attachment added to ReportDelivered email', [
                'report_id' => $this->report->id,
                'attachment_name' => $this->getAttachmentName(),
                'mime_type' => $this->getMimeType(),
                'file_size' => filesize($this->filePath)
            ]);
        } else {
            \Log::warning('No attachment added to ReportDelivered email - file not found', [
                'report_id' => $this->report->id,
                'file_path' => $this->filePath
            ]);
        }
        
        return $attachments;
    }

    /**
     * Get the attachment filename
     */
    private function getAttachmentName(): string
    {
        $name = str_replace(' ', '_', $this->report->name);
        $extension = $this->report->format === 'excel' ? 'xlsx' : ($this->report->format === 'csv' ? 'csv' : 'pdf');
        $date = $this->report->last_sent ? $this->report->last_sent->format('Y-m-d') : date('Y-m-d');
        
        return "{$name}_{$date}.{$extension}";
    }

    /**
     * Get the MIME type for the attachment
     */
    private function getMimeType(): string
    {
        switch ($this->report->format) {
            case 'excel':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            case 'csv':
                return 'text/csv';
            case 'pdf':
            default:
                return 'application/pdf';
        }
    }
    
    /**
     * Generate role-based report title
     */
    private function generateRoleBasedTitle($baseTitle, ?\App\Models\User $user = null)
    {
        if (!$user) {
            return $baseTitle;
        }

        switch ($user->role) {
            case 'admin':
                return 'Factory ' . $baseTitle;
            case 'supplier':
                return 'Supplier ' . $baseTitle;
            case 'vendor':
                return 'Vendor ' . $baseTitle;
            default:
                return $baseTitle;
        }
    }
}
