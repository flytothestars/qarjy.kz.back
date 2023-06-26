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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->string("url");
            $table->timestamps();
        });

        Schema::table("transactions", function (Blueprint $table) {
            $table->foreignId("bill_id")->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table("transactions", function (Blueprint $table) {
            $table->dropConstrainedForeignId("bill_id");
        });
        Schema::dropIfExists('bills');
    }
};
