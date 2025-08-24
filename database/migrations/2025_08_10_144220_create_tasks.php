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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 128)->nullable('false')->comment("Task Title");
            $table->string('description', 512)->nullable()->comment("Task Description");
            $table->integer('category_id')->nullable(false)->comment("Category Id Of Task");
            $table->tinyInteger("position")->default(0)->comment("Postion Of Task");
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->comment("Task Priority");
            $table->timestamp('completion_date')->nullable()->comment("Completion Date Of Task");
            $table->tinyInteger('status')->default(0)->comment('0-pending,1-completed');
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
