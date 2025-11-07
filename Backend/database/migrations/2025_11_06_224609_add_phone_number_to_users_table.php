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
        // No changes needed as phone_number is already added in the create migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No changes needed
    }
};
