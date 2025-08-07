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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('address');
            $table->string('city');
            $table->string('postal_code');
            $table->decimal('price', 12, 2);
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->integer('square_meters');
            $table->enum('property_type', ['house', 'apartment', 'condo', 'other']);
            $table->enum('status', ['draft', 'active', 'under_offer', 'sold']);
            $table->json('features')->nullable();
            $table->json('images')->nullable();
            $table->string('virtual_tour_url')->nullable();
            $table->timestamps();
            
            $table->fullText(['title', 'description', 'address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};