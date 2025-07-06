<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-test-email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a simple test email to verify mail configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Sending test email to: {$email}");
        
        try {
            Mail::raw('This is a test email from BeanTrack. If you received this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                       ->subject('BeanTrack Test Email')
                       ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info('✅ Test email sent successfully!');
            $this->info('Check your inbox for the email.');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email.');
            $this->error('Error: ' . $e->getMessage());
            
            // Check if it's a configuration issue
            if (str_contains($e->getMessage(), 'Connection refused') || str_contains($e->getMessage(), 'Connection could not be established')) {
                $this->error('');
                $this->error('This looks like a connection issue. Please check:');
                $this->error('1. Your MAIL_HOST setting');
                $this->error('2. Your MAIL_PORT setting');
                $this->error('3. Your internet connection');
                $this->error('4. If using Gmail, make sure you have generated an App Password');
            }
            
            return 1;
        }
        
        return 0;
    }
}
