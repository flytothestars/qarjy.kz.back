<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('income_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('expense_root_category_id')->nullable()->constrained()->references('id')->on('expense_categories')->nullOnDelete();
            $table->foreignId('expense_secondary_category_id')->nullable()->constrained()->references('id')->on('expense_categories')->nullOnDelete();
            $table->foreignId('expense_final_category_id')->nullable()->constrained()->references('id')->on('expense_categories')->nullOnDelete();
            $table->text('title');
            $table->float('price')->default(0);
            $table->integer("quantity")->default(0);
            $table->float("amount")->default(0);
            $table->text("company")->nullable();
            $table->dateTime('transaction_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
