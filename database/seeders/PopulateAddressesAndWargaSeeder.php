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

class PopulateAddressesAndWargaSeeder extends Seeder
{
    public function run(): void
    {
        $desa = Village::first() ?? Village::create([
            'id' => Str::uuid(),
            'name' => 'Desa Sukamaju',
            'code' => '12345',
        ]);

        $rw1 = Rw::where('village_id', $desa->id)->first() ?? Rw::create([
            'id' => Str::uuid(),
            'number' => '01',
            'village_id' => $desa->id,
        ]);

        $rt1 = Rt::where('rw_id', $rw1->id)->where('number', '01')->first() ?? Rt::create([
            'id' => Str::uuid(),
            'number' => '01',
            'rw_id' => $rw1->id,
        ]);

        $rt2 = Rt::where('rw_id', $rw1->id)->where('number', '02')->first() ?? Rt::create([
            'id' => Str::uuid(),
            'number' => '02',
            'rw_id' => $rw1->id,
        ]);

        // 1. Update Existing Officer / Admin Addresses
        $officerAddresses = [
            'superadmin' => 'Jl. Merdeka No. 1, Kantor Kecamatan Sukamaju',
            '3201016666666666' => 'Jl. Raya Sukamaju No. 10, RT 01 / RW 01, Desa Sukamaju',
            '3201017777777777' => 'Jl. Melati Raya No. 15, RT 01 / RW 01, Desa Sukamaju',
            '3201018888888888' => 'Jl. Melati 1 No. 05, RT 01 / RW 01, Desa Sukamaju',
            '3201019999999999' => 'Jl. Melati 2 No. 12, RT 02 / RW 01, Desa Sukamaju',
            '3201011111111111' => 'Jl. Melati 1 No. 08, RT 01 / RW 01, Desa Sukamaju',
            '3201013333333333' => 'Jl. Melati 2 No. 04, RT 02 / RW 01, Desa Sukamaju',
        ];

        foreach ($officerAddresses as $username => $address) {
            User::whereRaw('BINARY username = ?', [(string) $username])->update(['address' => $address]);
        }

        // 2. 4 Warga for RT 01
        $wargaRt1 = [
            [
                'nik' => '3201011111111111',
                'name' => 'Budi Santoso',
                'email' => 'budisantoso@siwil.com',
                'phone' => '081234567894',
                'address' => 'Jl. Melati 1 No. 08, RT 01 / RW 01, Desa Sukamaju',
                'kk_number' => '3201010000000001',
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
                'kk_number' => '3201010000000003',
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
                'kk_number' => '3201010000000004',
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
                'kk_number' => '3201010000000005',
                'birth_place' => 'Jakarta',
                'birth_date' => '1995-12-03',
                'gender' => 'PEREMPUAN',
                'occupation' => 'Karyawan Swasta',
                'education' => 'S1',
            ],
        ];

        foreach ($wargaRt1 as $item) {
            $family = Family::firstOrCreate(
                ['kk_number' => $item['kk_number']],
                [
                    'id' => Str::uuid(),
                    'address' => $item['address'],
                    'rt_id' => $rt1->id,
                ]
            );

            Resident::updateOrCreate(
                ['nik' => $item['nik']],
                [
                    'full_name' => $item['name'],
                    'family_id' => $family->id,
                    'is_head_of_family' => true,
                    'birth_place' => $item['birth_place'],
                    'birth_date' => $item['birth_date'],
                    'gender' => $item['gender'],
                    'religion' => 'ISLAM',
                    'marital_status' => 'KAWIN',
                    'education' => $item['education'],
                    'occupation' => $item['occupation'],
                    'rt_id' => $rt1->id,
                ]
            );

            $user = User::whereRaw('BINARY username = ?', [(string) $item['nik']])->first();
            if (! $user) {
                $user = User::create([
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
                $user->assignRole('warga');
            } else {
                $user->update([
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'phone_number' => $item['phone'],
                    'address' => $item['address'],
                    'village_id' => $desa->id,
                    'rw_id' => $rw1->id,
                    'rt_id' => $rt1->id,
                    'status' => 'active',
                ]);
            }
        }

        // 3. 4 Warga for RT 02
        $wargaRt2 = [
            [
                'nik' => '3201013333333333',
                'name' => 'Agus Rahman',
                'email' => 'agusrahman@siwil.com',
                'phone' => '081234567895',
                'address' => 'Jl. Melati 2 No. 04, RT 02 / RW 01, Desa Sukamaju',
                'kk_number' => '3201010000000002',
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
                'kk_number' => '3201010000000006',
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
                'kk_number' => '3201010000000007',
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
                'kk_number' => '3201010000000008',
                'birth_place' => 'Bogor',
                'birth_date' => '1996-06-22',
                'gender' => 'PEREMPUAN',
                'occupation' => 'Akuntan',
                'education' => 'S1',
            ],
        ];

        foreach ($wargaRt2 as $item) {
            $family = Family::firstOrCreate(
                ['kk_number' => $item['kk_number']],
                [
                    'id' => Str::uuid(),
                    'address' => $item['address'],
                    'rt_id' => $rt2->id,
                ]
            );

            Resident::updateOrCreate(
                ['nik' => $item['nik']],
                [
                    'full_name' => $item['name'],
                    'family_id' => $family->id,
                    'is_head_of_family' => true,
                    'birth_place' => $item['birth_place'],
                    'birth_date' => $item['birth_date'],
                    'gender' => $item['gender'],
                    'religion' => 'ISLAM',
                    'marital_status' => 'KAWIN',
                    'education' => $item['education'],
                    'occupation' => $item['occupation'],
                    'rt_id' => $rt2->id,
                ]
            );

            $user = User::whereRaw('BINARY username = ?', [(string) $item['nik']])->first();
            if (! $user) {
                $user = User::create([
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
                $user->assignRole('warga');
            } else {
                $user->update([
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'phone_number' => $item['phone'],
                    'address' => $item['address'],
                    'village_id' => $desa->id,
                    'rw_id' => $rw1->id,
                    'rt_id' => $rt2->id,
                    'status' => 'active',
                ]);
            }
        }

        // 4. Fallback for any other remaining user accounts without address
        $remainingUsers = User::whereNull('address')->orWhere('address', '')->get();
        foreach ($remainingUsers as $u) {
            if ($u->resident && $u->resident->family && ! empty($u->resident->family->address)) {
                $u->update(['address' => $u->resident->family->address]);
            } else {
                $rtPart = $u->rt ? 'RT '.$u->rt->number : '';
                $rwPart = $u->rw ? 'RW '.$u->rw->number : '';
                $desaPart = $u->village ? 'Desa '.$u->village->name : 'Desa Sukamaju';
                $wilayah = array_filter([$rtPart, $rwPart, $desaPart]);
                $u->update(['address' => 'Jl. Sukamaju Raya, '.implode(' / ', $wilayah)]);
            }
        }
    }
}
