<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une pièce jointe est une image (capture d'écran) ou un document (PDF, Word, etc.).
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('kind', 10)->default('image')->after('comment_id');
            $table->string('original_name', 160)->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn(['kind', 'original_name']);
        });
    }
};
