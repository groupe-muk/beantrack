<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Message;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        // Check if we have users available
        if ($users->count() === 0) {
            Log::warning('Cannot create messages: No users available');
            return;
        }
          // Need at least 2 users to create messages between them
        if ($users->count() < 2) {
            Log::warning('Cannot create messages: Need at least 2 users');
            return;
        }
        
        // Create messages between random users
        foreach (range(1, 10) as $i) {
            try {
                $sender = $users->random();
                $recipientCollection = $users->where('id', '!=', $sender->id);
                
                // Check if we have any recipients available
                if ($recipientCollection->count() === 0) {
                    Log::warning('Cannot create message: No recipients available');
                    continue;
                }
                
                $recipient = $recipientCollection->random();
                
                Message::factory()->create([
                    'sender_id' => $sender->id,
                    'recipient_id' => $recipient->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create message: ' . $e->getMessage());
            }
        }
    }
}
