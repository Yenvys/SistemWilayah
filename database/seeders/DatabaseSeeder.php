<?php

namespace Database\Seeders;

use App\Models\Family;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Call RoleSeeder
        $this->call(RoleSeeder::class);

        // 1. Buat Desa
        $desa = Village::create([
            'id' => Str::uuid(),
            'name' => 'Desa Sukamaju',
            'code' => '12345',
        ]);

        // 2. Buat RW (1 RW)
        $rw1 = Rw::create([
            'id' => Str::uuid(),
            'number' => '01',
            'village_id' => $desa->id,
        ]);

        // 3. Buat RT (2 RT)
        $rt1 = Rt::create([
            'id' => Str::uuid(),
            'number' => '01',
            'rw_id' => $rw1->id,
        ]);

        $rt2 = Rt::create([
            'id' => Str::uuid(),
            'number' => '02',
            'rw_id' => $rw1->id,
        ]);

        // 4. Buat Akun Superadmin
        $superadmin = User::create([
            'id' => Str::uuid(),
            'name' => 'Administrator Utama',
            'username' => 'superadmin',
            'email' => 'superadmin@siwil.com',
            'address' => 'Jl. Merdeka No. 1, Kantor Kecamatan Sukamaju',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
            'status' => 'active',
        ]);
        $superadmin->assignRole('superadmin');

        // 5. Buat Akun Admin Desa — Hj. Sri Mulyani
        $admindesa = User::create([
            'id' => Str::uuid(),
            'name' => 'Hj. Sri Mulyani',
            'username' => '3201016666666666',
            'email' => 'srimulyani@siwil.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Raya Sukamaju No. 10, RT 01 / RW 01, Desa Sukamaju',
            'password' => Hash::make('password123'),
            'role' => 'ADMIN_DESA',
            'village_id' => $desa->id,
            'status' => 'active',
        ]);
        $admindesa->assignRole('desa');

        // 6. Buat Akun Admin RW — Pak Surya Darma
        $adminrw = User::create([
            'id' => Str::uuid(),
            'name' => 'Surya Darma',
            'username' => '3201017777777777',
            'email' => 'suryadarma@siwil.com',
            'phone_number' => '081234567891',
            'address' => 'Jl. Melati Raya No. 15, RT 01 / RW 01, Desa Sukamaju',
            'password' => Hash::make('password123'),
            'role' => 'RW',
            'village_id' => $desa->id,
            'rw_id' => $rw1->id,
            'status' => 'active',
        ]);
        $adminrw->assignRole('rw');

        // 7. Buat Akun Admin RT (2 RT)
        // RT 01 — Pak Hendra Wijaya
        $adminrt1 = User::create([
            'id' => Str::uuid(),
            'name' => 'Hendra Wijaya',
            'username' => '3201018888888888',
            'email' => 'hendrawijaya@siwil.com',
            'phone_number' => '081234567892',
            'address' => 'Jl. Melati 1 No. 05, RT 01 / RW 01, Desa Sukamaju',
            'password' => Hash::make('password123'),
            'role' => 'RT',
            'village_id' => $desa->id,
            'rw_id' => $rw1->id,
            'rt_id' => $rt1->id,
            'status' => 'active',
        ]);
        $adminrt1->assignRole('rt');

        // RT 02 — Pak Dodi Prasetyo
        $adminrt2 = User::create([
            'id' => Str::uuid(),
            'name' => 'Dodi Prasetyo',
            'username' => '3201019999999999',
            'email' => 'dodiprasetyo@siwil.com',
            'phone_number' => '081234567893',
            'address' => 'Jl. Melati 2 No. 12, RT 02 / RW 01, Desa Sukamaju',
            'password' => Hash::make('password123'),
            'role' => 'RT',
            'village_id' => $desa->id,
            'rw_id' => $rw1->id,
            'rt_id' => $rt2->id,
            'status' => 'active',
        ]);
        $adminrt2->assignRole('rt');

        // 8. Buat Keluarga (Families)
        // RT 01 Families
        $family1 = Family::create([
            'id' => Str::uuid(),
            'kk_number' => '3201010000000001',
            'address' => 'Jl. Melati 1 No. 08, RT 01 / RW 01, Desa Sukamaju',
            'rt_id' => $rt1->id,
        ]);

        $family3 = Family::create([
            'id' => Str::uuid(),
            'kk_number' => '3201010000000003',
            'address' => 'Jl. Melati 1 No. 10, RT 01 / RW 01, Desa Sukamaju',
            'rt_id' => $rt1->id,
        ]);

        $family4 = Family::create([
            'id' => Str::uuid(),
            'kk_number' => '3201010000000004',
            'address' => 'Jl. Melati 1 No. 14, RT 01 / RW 01, Desa Sukamaju',
            'rt_id' => $rt1->id,
        ]);

        $family5 = Family::create([
            'id' => Str::uuid(),
            'kk_number' => '3201010000000005',
            'address' => 'Jl. Melati 1 No. 18, RT 01 / RW 01, Desa Sukamaju',
            'rt_id' => $rt1->id,
        ]);

        // RT 02 Families
        $family2 = Family::create([
            'id' => Str::uuid(),
            'kk_number' => '3201010000000002',
            'address' => 'Jl. Melati 2 No. 04, RT 02 / RW 01, Desa Sukamaju',
            'rt_id' => $rt2->id,
        ]);

        $family6 = Family::create([
            'id' => Str::uuid(),
            'kk_number' => '3201010000000006',
            'address' => 'Jl. Melati 2 No. 07, RT 02 / RW 01, Desa Sukamaju',
            'rt_id' => $rt2->id,
        ]);

        $family7 = Family::create([
            'id' => Str::uuid(),
            'kk_number' => '3201010000000007',
            'address' => 'Jl. Melati 2 No. 11, RT 02 / RW 01, Desa Sukamaju',
            'rt_id' => $rt2->id,
        ]);

        $family8 = Family::create([
            'id' => Str::uuid(),
            'kk_number' => '3201010000000008',
            'address' => 'Jl. Melati 2 No. 15, RT 02 / RW 01, Desa Sukamaju',
            'rt_id' => $rt2->id,
        ]);

        // 9. Data Pengurus sebagai Residents
        Resident::create([
            'id' => Str::uuid(),
            'nik' => '3201016666666666',
            'full_name' => 'Hj. Sri Mulyani',
            'family_id' => $family1->id,
            'is_head_of_family' => false,
            'birth_place' => 'Bandung',
            'birth_date' => '1975-08-17',
            'gender' => 'PEREMPUAN',
            'religion' => 'ISLAM',
            'marital_status' => 'KAWIN',
            'education' => 'S1',
            'occupation' => 'Kepala Desa',
            'rt_id' => $rt1->id,
        ]);

        Resident::create([
            'id' => Str::uuid(),
            'nik' => '3201017777777777',
            'full_name' => 'Surya Darma',
            'family_id' => $family1->id,
            'is_head_of_family' => false,
            'birth_place' => 'Jakarta',
            'birth_date' => '1980-05-12',
            'gender' => 'LAKI_LAKI',
            'religion' => 'ISLAM',
            'marital_status' => 'KAWIN',
            'education' => 'SMA',
            'occupation' => 'Wiraswasta',
            'rt_id' => $rt1->id,
        ]);

        Resident::create([
            'id' => Str::uuid(),
            'nik' => '3201018888888888',
            'full_name' => 'Hendra Wijaya',
            'family_id' => $family1->id,
            'is_head_of_family' => false,
            'birth_place' => 'Semarang',
            'birth_date' => '1982-11-20',
            'gender' => 'LAKI_LAKI',
            'religion' => 'ISLAM',
            'marital_status' => 'KAWIN',
            'education' => 'SMA',
            'occupation' => 'Karyawan Swasta',
            'rt_id' => $rt1->id,
        ]);

        Resident::create([
            'id' => Str::uuid(),
            'nik' => '3201019999999999',
            'full_name' => 'Dodi Prasetyo',
            'family_id' => $family2->id,
            'is_head_of_family' => false,
            'birth_place' => 'Surabaya',
            'birth_date' => '1985-02-15',
            'gender' => 'LAKI_LAKI',
            'religion' => 'ISLAM',
            'marital_status' => 'KAWIN',
            'education' => 'D3',
            'occupation' => 'Pedagang',
            'rt_id' => $rt2->id,
        ]);

        // 10. Data Residents & Akun Warga RT 01 (4 Warga)
        $wargaRt1 = [
            [
                'nik' => '3201011111111111',
                'name' => 'Budi Santoso',
                'email' => 'budisantoso@siwil.com',
                'phone' => '081234567894',
                'address' => 'Jl. Melati 1 No. 08, RT 01 / RW 01, Desa Sukamaju',
                'family_id' => $family1->id,
                'birth_place' => 'Bogor',
                'birth_date' => '1990-01-10',
                'gender' => 'LAKI_LAKI',
                'occupation' => 'Pegawai Negeri Sipil',
                'education' => 'S1',
            ],
            [
                'nik' => '3201011111111112',
                'name' => 'Siti Aminah',
                'email' => 'sitiaminah@siwil.com',
                'phone' => '081234567801',
                'address' => 'Jl. Melati 1 No. 10, RT 01 / RW 01, Desa Sukamaju',
                'family_id' => $family3->id,
                'birth_place' => 'Bandung',
                'birth_date' => '1993-04-12',
                'gender' => 'PEREMPUAN',
                'occupation' => 'Guru',
                'education' => 'S1',
            ],
            [
                'nik' => '3201011111111113',
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmadfauzi@siwil.com',
                'phone' => '081234567802',
                'address' => 'Jl. Melati 1 No. 14, RT 01 / RW 01, Desa Sukamaju',
                'family_id' => $family4->id,
                'birth_place' => 'Bogor',
                'birth_date' => '1988-07-25',
                'gender' => 'LAKI_LAKI',
                'occupation' => 'Wirausaha',
                'education' => 'SMA',
            ],
            [
                'nik' => '3201011111111114',
                'name' => 'Rina Kartika',
                'email' => 'rinakartika@siwil.com',
                'phone' => '081234567803',
                'address' => 'Jl. Melati 1 No. 18, RT 01 / RW 01, Desa Sukamaju',
                'family_id' => $family5->id,
                'birth_place' => 'Jakarta',
                'birth_date' => '1995-12-03',
                'gender' => 'PEREMPUAN',
                'occupation' => 'Karyawan Swasta',
                'education' => 'S1',
            ],
        ];

        foreach ($wargaRt1 as $item) {
            Resident::create([
                'id' => Str::uuid(),
                'nik' => $item['nik'],
                'full_name' => $item['name'],
                'family_id' => $item['family_id'],
                'is_head_of_family' => true,
                'birth_place' => $item['birth_place'],
                'birth_date' => $item['birth_date'],
                'gender' => $item['gender'],
                'religion' => 'ISLAM',
                'marital_status' => 'KAWIN',
                'education' => $item['education'],
                'occupation' => $item['occupation'],
                'rt_id' => $rt1->id,
            ]);

            $w = User::create([
                'id' => Str::uuid(),
                'name' => $item['name'],
                'username' => $item['nik'],
                'email' => $item['email'],
                'phone_number' => $item['phone'],
                'address' => $item['address'],
                'password' => Hash::make('password123'),
                'role' => 'WARGA',
                'village_id' => $desa->id,
                'rw_id' => $rw1->id,
                'rt_id' => $rt1->id,
                'status' => 'active',
            ]);
            $w->assignRole('warga');
        }

        // 11. Data Residents & Akun Warga RT 02 (4 Warga)
        $wargaRt2 = [
            [
                'nik' => '3201013333333333',
                'name' => 'Agus Rahman',
                'email' => 'agusrahman@siwil.com',
                'phone' => '081234567895',
                'address' => 'Jl. Melati 2 No. 04, RT 02 / RW 01, Desa Sukamaju',
                'family_id' => $family2->id,
                'birth_place' => 'Depok',
                'birth_date' => '1992-09-05',
                'gender' => 'LAKI_LAKI',
                'occupation' => 'Programmer',
                'education' => 'S1',
            ],
            [
                'nik' => '3201013333333334',
                'name' => 'Dewi Lestari',
                'email' => 'dewilestari@siwil.com',
                'phone' => '081234567804',
                'address' => 'Jl. Melati 2 No. 07, RT 02 / RW 01, Desa Sukamaju',
                'family_id' => $family6->id,
                'birth_place' => 'Depok',
                'birth_date' => '1991-03-18',
                'gender' => 'PEREMPUAN',
                'occupation' => 'Dokter',
                'education' => 'S1',
            ],
            [
                'nik' => '3201013333333335',
                'name' => 'Joko Widodo',
                'email' => 'jokowidodo@siwil.com',
                'phone' => '081234567805',
                'address' => 'Jl. Melati 2 No. 11, RT 02 / RW 01, Desa Sukamaju',
                'family_id' => $family7->id,
                'birth_place' => 'Solo',
                'birth_date' => '1987-10-10',
                'gender' => 'LAKI_LAKI',
                'occupation' => 'Wiraswasta',
                'education' => 'SMA',
            ],
            [
                'nik' => '3201013333333336',
                'name' => 'Maya Safitri',
                'email' => 'mayasafitri@siwil.com',
                'phone' => '081234567806',
                'address' => 'Jl. Melati 2 No. 15, RT 02 / RW 01, Desa Sukamaju',
                'family_id' => $family8->id,
                'birth_place' => 'Bogor',
                'birth_date' => '1996-06-22',
                'gender' => 'PEREMPUAN',
                'occupation' => 'Akuntan',
                'education' => 'S1',
            ],
        ];

        foreach ($wargaRt2 as $item) {
            Resident::create([
                'id' => Str::uuid(),
                'nik' => $item['nik'],
                'full_name' => $item['name'],
                'family_id' => $item['family_id'],
                'is_head_of_family' => true,
                'birth_place' => $item['birth_place'],
                'birth_date' => $item['birth_date'],
                'gender' => $item['gender'],
                'religion' => 'ISLAM',
                'marital_status' => 'KAWIN',
                'education' => $item['education'],
                'occupation' => $item['occupation'],
                'rt_id' => $rt2->id,
            ]);

            $w = User::create([
                'id' => Str::uuid(),
                'name' => $item['name'],
                'username' => $item['nik'],
                'email' => $item['email'],
                'phone_number' => $item['phone'],
                'address' => $item['address'],
                'password' => Hash::make('password123'),
                'role' => 'WARGA',
                'village_id' => $desa->id,
                'rw_id' => $rw1->id,
                'rt_id' => $rt2->id,
                'status' => 'active',
            ]);
            $w->assignRole('warga');
        }
    }
}
