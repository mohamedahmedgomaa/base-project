<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bundle_installment_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->integer('reference')->default(1);
            $table->integer('quantity')->default(1);

            $table->string('duration_type')->default("week")->comment('day, week, month, year');
            $table->tinyInteger('duration_value')->comment("number of days or months or years");

            $table->unsignedDecimal('subtotal_without_discount', 10, 2)->default(0.0);
            $table->unsignedDecimal('subtotal', 10, 2)->default(0.0);
            $table->unsignedDecimal('vat_percentage', 10, 2)->default(0.0);
            $table->unsignedDecimal('vat_amount', 10, 2)->default(0.0);
            $table->unsignedDecimal('total', 10, 2)->default(0.0);

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bundle_installment_schedules');
    }
};
