<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    protected $tbl = 'google_accounts';
    public function up()
    {
        if(!Schema::hasTable($this->tbl)){
            Schema::create($this->tbl, function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->nullable()->constrained();
                $table->integer('google_id')->nullable();
                $table->string('email')->nullable();
                $table->text('token')->nullable();
                $table->text('refresh_token')->nullable();
                $table->text('token_expires_at')->nullable();
                $table->string('calendar_id')->default('primary');
                $table->string('timezone')->nullable();
                $table->boolean('is_active')->default(true);
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
