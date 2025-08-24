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
        Schema::create('categories', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary();
            $table->string('name', 128)->nullable(false)->comment("Name Of Category");
            $table->string('description', 512)->nullable()->comment("Description of the category");
            $table->tinyInteger('position')->nullable(false)->default(0)->comment('Ordering Position Of Category');
            $table->timestamps();
            $table->unique("name");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
