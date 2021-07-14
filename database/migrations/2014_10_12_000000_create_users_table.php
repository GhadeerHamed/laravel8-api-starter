<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('phone_number');
            $table->text('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('app_notification_status')->default(true);
            $table->string('code')->nullable();
            $table->string('facebook_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE users ADD FULLTEXT idx_full_name (name, email, phone_number)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
