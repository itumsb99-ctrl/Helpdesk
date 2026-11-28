<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id', 20)->nullable()->unique()->after('id');
        });

        $number = 521;
        DB::table('users')->orderBy('id')->select('id')->chunk(100, function ($users) use (&$number) {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'employee_id' => 'SBA' . $number,
                ]);
                $number++;
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
