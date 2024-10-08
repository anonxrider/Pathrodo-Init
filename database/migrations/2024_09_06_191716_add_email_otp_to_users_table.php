<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_otp')->nullable();
            $table->string('password_reset_otp')->nullable();
            $table->timestamp('password_reset_otp_expires_at')->nullable();
            $table->timestamp('email_otp_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_otp','password_reset_otp','password_reset_otp_expires_at','email_otp_expires_at']);
        });
    }
};
