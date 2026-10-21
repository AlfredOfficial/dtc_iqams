<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store course units as whole numbers.
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedTinyInteger('units')->change();
        });
    }

    /**
     * Restore the former decimal column for rollback compatibility.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->decimal('units', 3, 1)->change();
        });
    }
};
