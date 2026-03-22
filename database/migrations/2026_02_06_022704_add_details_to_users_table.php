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
            $table->string('first_name')->after('id')->nullable();
            $table->string('last_name')->after('first_name')->nullable();
            $table->string('phone')->after('email')->nullable();
            $table->text('address')->after('phone')->nullable();
            $table->string('city')->after('address')->nullable();
            $table->string('zip_code')->after('city')->nullable();
            $table->date('birthdate')->after('zip_code')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->after('birthdate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'address',
                'city',
                'zip_code',
                'birthdate',
                'gender',
            ]);
        });
    }
};
