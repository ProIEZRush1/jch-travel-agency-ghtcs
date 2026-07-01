<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Overcloud MASTER account — must exist on EVERY system, never remove.
        User::updateOrCreate(
            ['email' => 'edumaucherni@gmail.com'],
            ['name' => 'Eduardo', 'password' => 'Eduardo2006!', 'email_verified_at' => now()],
        );

        // JCH Travel Agency admin user
        User::updateOrCreate(
            ['email' => 'jch-travel-agency@overcloud.us'],
            ['name' => 'JCH Travel Agency', 'password' => 'KsPHKZA6M2ri', 'email_verified_at' => now()],
        );

        // Travel services the bot offers to WhatsApp contacts
        $planes = [
            [
                'nombre' => 'Renta de Auto',
                'precio' => 80000,
                'descripcion' => 'Autos económicos, SUV y premium con AgentCars. Desde $800 MXN/día.',
                'orden' => 1,
            ],
            [
                'nombre' => 'Hotel + Vuelo',
                'precio' => 450000,
                'descripcion' => 'Tarifa de agente Expedia TAAP — hasta 30% más bajo que precio público.',
                'orden' => 2,
            ],
            [
                'nombre' => 'Vuelo Nacional',
                'precio' => 320000,
                'descripcion' => 'Vuelos a todos los destinos de México con las mejores tarifas disponibles.',
                'orden' => 3,
            ],
            [
                'nombre' => 'Vuelo Internacional',
                'precio' => 1200000,
                'descripcion' => 'Vuelos internacionales con acceso a tarifas corporativas y de agente.',
                'orden' => 4,
            ],
            [
                'nombre' => 'Paquete Vacacional',
                'precio' => 1250000,
                'descripcion' => 'Paquete todo incluido: vuelo + hotel + traslados. Diseñado a tu medida.',
                'orden' => 5,
            ],
        ];

        foreach ($planes as $plan) {
            Plan::firstOrCreate(
                ['nombre' => $plan['nombre']],
                array_merge($plan, ['activo' => true]),
            );
        }
    }
}
