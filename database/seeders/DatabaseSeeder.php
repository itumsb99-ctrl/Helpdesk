<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SlaSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email'=>'admin@helpdesk.local'],[
            'name'=>'Administrator IT','password'=>'password','role'=>'admin','unit'=>'IT','is_active'=>true,
        ]);
        User::updateOrCreate(['email'=>'teknisi@helpdesk.local'],[
            'name'=>'Teknisi IT','password'=>'password','role'=>'teknisi','unit'=>'IT','is_active'=>true,
        ]);
        User::updateOrCreate(['email'=>'pegawai@helpdesk.local'],[
            'name'=>'Pegawai Demo','password'=>'password','role'=>'pegawai','unit'=>'Rawat Jalan','is_active'=>true,
        ]);

        foreach (['Komputer dan Laptop','Jaringan dan Internet','Printer dan Scanner','Aplikasi SIMRS','Hardware','Email dan Akun','CCTV dan Perangkat Lain'] as $name) {
            Category::firstOrCreate(['name'=>$name],['is_active'=>true]);
        }

        foreach ([
            ['critical',10,60],
            ['high',30,240],
            ['medium',240,480],
            ['low',480,1440],
        ] as [$priority,$response,$resolution]) {
            SlaSetting::updateOrCreate(['priority'=>$priority],[
                'response_minutes'=>$response,'resolution_minutes'=>$resolution,'is_active'=>true,
            ]);
        }
    }
}
