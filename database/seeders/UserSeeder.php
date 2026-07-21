<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $password = Hash::make('password');

        DB::table('users')->upsert([[
            'name' => 'Super Admin PERPAMSI',
            'email' => 'super@perpamsi.local',
            'password' => $password,
            'role' => 'super_admin',
            'account_status' => 'verified',
            'regional_committee_id' => null,
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]], ['email'], ['name', 'password', 'role', 'account_status', 'regional_committee_id', 'updated_at']);

        $committees = DB::table('regional_committees')
            ->leftJoin('provinces', 'regional_committees.province_id', '=', 'provinces.id')
            ->select('regional_committees.id', 'regional_committees.name', 'provinces.name as province_name', 'provinces.slug as province_slug')
            ->orderBy('regional_committees.id')
            ->where('provinces.slug', 'kalimantan-timur')
            ->get();

        $rows = $committees->map(fn ($c) => [
            'name' => 'Admin '.$c->name,
            'email' => 'pd-'.($c->province_slug ?: Str::slug($c->province_name)).'@perpamsi.local',
            'password' => $password,
            'role' => 'pd_admin',
            'account_status' => 'verified',
            'regional_committee_id' => $c->id,
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('users')->upsert(
                $chunk,
                ['email'],
                ['name', 'password', 'role', 'account_status', 'regional_committee_id', 'updated_at']
            );
        }
    }
}
