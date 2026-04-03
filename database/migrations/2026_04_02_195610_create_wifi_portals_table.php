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
        Schema::create('wifi_portals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('portal_token')->unique();
            $table->string('welcome_title')->default('Welcome to our guest WiFi');
            $table->text('welcome_text')->nullable();
            $table->text('terms_text')->nullable();
            $table->string('logo_override_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('require_marketing_consent')->default(false);
            $table->unsignedInteger('session_ttl_minutes')->default(120);
            $table->string('network_vendor')->default('custom');
            $table->string('post_login_redirect_url')->nullable();
            $table->string('integration_key')->unique();
            $table->text('integration_secret');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wifi_portals');
    }
};
