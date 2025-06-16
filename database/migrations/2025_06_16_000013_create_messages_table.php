<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->string('sender_id', 6);
            $table->string('receiver_id', 6);
            $table->text('content');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
        DB::unprepared("CREATE TRIGGER before_messages_insert BEFORE INSERT ON messages FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM messages ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('M', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('messages');
        DB::unprepared('DROP TRIGGER IF EXISTS before_messages_insert');
    }
};
