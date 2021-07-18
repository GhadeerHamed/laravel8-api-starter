<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->foreignId("country_id")->constrained()->cascadeOnDelete();
            $table->foreignId("city_id")->nullable()->constrained()->nullOnDelete();
            $table->string("state")->nullable();
            $table->string("address1");
            $table->string("address2")->nullable();
            $table->string("postal_code")->nullable();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE addresses ADD FULLTEXT idx_full_state_address_postal (state, address1, address2, postal_code)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
