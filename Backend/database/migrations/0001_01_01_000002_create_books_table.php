<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
       Schema::create('books', function (Blueprint $table) {
           $table->id();
           $table->string('title');
           $table->string('author');
           $table->decimal('price',10,2);
           $table->integer('stock')->default(0);
           $table->foreignId('book_category_id')->constrained('book_categories')->onDelete('cascade');
           $table->timestamps();
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('books');

    }
};
