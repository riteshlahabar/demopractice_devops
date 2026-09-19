<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns for the homepage "Video Section": either a YouTube/Vimeo/MP4 link or
 * an uploaded MP4, plus whether the player starts on its own. The poster image
 * and the text beside a video reuse the item's existing columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_homepage_section_items', function (Blueprint $table): void {
            $table->string('video_url', 2048)->nullable()->after('offer_image_path');
            $table->string('video_file_path', 2048)->nullable()->after('video_url');
            $table->boolean('video_autoplay')->default(false)->after('video_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('product_homepage_section_items', function (Blueprint $table): void {
            $table->dropColumn(['video_url', 'video_file_path', 'video_autoplay']);
        });
    }
};
