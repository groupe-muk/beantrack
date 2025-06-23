<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->string('email', 191)->unique();
            $table->string('password', 255);
            $table->enum('role', ['admin', 'supplier', 'vendor']);
            $table->string('name', 255);
            $table->string('phone', 255)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        DB::unprepared("CREATE TRIGGER before_users_insert BEFORE INSERT ON users FOR EACH ROW BEGIN DECLARE last_id INT; SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) INTO last_id FROM users ORDER BY id DESC LIMIT 1; SET NEW.id = CONCAT('U', LPAD(COALESCE(last_id + 1, 1), 5, '0')); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('users');
        DB::unprepared('DROP TRIGGER IF EXISTS before_users_insert');
    }
};

class CreateInventoriesTable extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('category');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->string('location');
            $table->string('status');
            $table->string('supplier');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
