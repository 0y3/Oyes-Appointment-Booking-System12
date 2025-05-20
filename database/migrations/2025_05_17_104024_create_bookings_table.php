<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     protected $tbl = 'bookings';
    public function up()
    {
        if(!Schema::hasTable($this->tbl)){
            Schema::create($this->tbl, function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->nullable()->constrained();
                $table->string('visitor_name');
                $table->string('visitor_email');
                $table->text('description')->nullable();
                $table->datetime('start_time');
                $table->datetime('end_time');
                $table->string('timezone')->nullable();
                $table->string('google_event_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->integer('created_by')->nullable()->constrained('users');
                $table->integer('updated_by')->nullable()->constrained('users');
                $table->integer('deleted_by')->nullable()->constrained('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->tbl);
    }
};
