<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Superseded — faculty/staff now request counseling through the same
 * Appointment booking flow students use, rather than a separate
 * lightweight request form. See StudentReferral for the (separate,
 * still-active) faculty-refers-a-student feature.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('counseling_requests');
    }

    public function down(): void
    {
        // Intentionally not recreated — superseded, see class docblock.
    }
};
