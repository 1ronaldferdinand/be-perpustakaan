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
        Schema::create('borrows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('customer_id');
            $table->timestamp('borrow_start');
            $table->timestamp('borrow_end')->nullable();
            $table->integer('borrow_status')->default(1); // 1 => borrow, 2 => returned
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books');
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrows');
    }
};
