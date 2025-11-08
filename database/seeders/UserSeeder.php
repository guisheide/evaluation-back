<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\Address;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $profiles = [
            Profile::create(['name' => 'Administrador', 'description' => 'Acesso total ao sistema.']),
            Profile::create(['name' => 'Gestor', 'description' => 'Gerencia usuários e dados intermediários.']),
            Profile::create(['name' => 'Usuário', 'description' => 'Acesso limitado às suas próprias informações.']),
        ];

        foreach (range(1, 10) as $i) {

            $profile = $faker->randomElement($profiles);

            $user = User::create([
                'profile_id' => $profile->id,
                'name'       => $faker->name,
                'email'      => $faker->unique()->safeEmail,
                'cpf'        => $faker->unique()->cpf(false),
            ]);

            foreach (range(1, rand(1, 3)) as $j) {
                $address = Address::firstOrCreate([
                    'zip_code'     => $faker->postcode,
                    'street'       => $faker->streetName,
                ]);

                $user->addresses()->syncWithoutDetaching([$address->id]);
            }
        }
    }
}
