<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('posts')->insert([
            [
                'date' => '2014-01-01',
                'place' => 'YouTube',
                'title' => 'Fondazione del Trio (poi Mates)',
                'description' => 'Surry, St3pNy e Vegas fondano il gruppo "Trio", che diventerà successivamente Mates, includendo anche Anima.',
                'links' => json_encode([
                    'https://www.youtube.com/watch?v=EK3giW9vZmk', // Mates - Fondazione
                    'https://www.youtube.com/watch?v=GTt8ap24M2Y'  // Mates - Primo Video
                ]),
                'order' => 1,
            ],
            [
                'date' => '2016-05-19',
                'place' => 'Italia',
                'title' => 'Pubblicazione del libro "Veri Amici"',
                'description' => 'I Mates pubblicano il loro libro "Veri Amici" che racconta la loro storia e il dietro le quinte.',
                'links' => json_encode([
                    'https://www.youtube.com/watch?v=8i5f5lz7dQs', // Presentazione Libro "Veri Amici"
                    'https://www.youtube.com/watch?v=jI93OUmeWFM'  // Video Evento Book Launch
                ]),
                'order' => 2,
            ],
            [
                'date' => '2017-04-17',
                'place' => 'Sky Uno',
                'title' => 'Partecipazione a Social Face',
                'description' => 'Surry partecipa alla seconda edizione di Social Face, un programma che mette alla prova i social influencer.',
                'links' => json_encode([
                    'https://www.youtube.com/watch?v=Z8z1hBYzMck', // Social Face - Introduzione
                    'https://www.youtube.com/watch?v=k4VZse6X-3A'  // Social Face - Surry nel programma
                ]),
                'order' => 3,
            ],
            [
                'date' => '2019-11-23',
                'place' => 'Canale 5',
                'title' => 'Partecipazione ad All Together Now',
                'description' => 'Surry prende parte come giudice al programma musicale "All Together Now", condotto da Michelle Hunziker.',
                'links' => json_encode([
                    'https://www.youtube.com/watch?v=2ndsf1Wpn6E', // All Together Now - Trailer
                    'https://www.youtube.com/watch?v=Jl0VgJXcV7g'  // Video con Surry come giudice
                ]),
                'order' => 4,
            ],
            [
                'date' => '2020-07-10',
                'place' => 'Cinema',
                'title' => 'Uscita del film Social Dream',
                'description' => 'Surry recita nel film "Social Dream", una pellicola che racconta la vita degli influencer in Italia.',
                'links' => json_encode([
                    'https://www.youtube.com/watch?v=7UZZsZwFmDQ', // Social Dream - Trailer ufficiale
                    'https://www.youtube.com/watch?v=0VA6m53ln8Y'  // Intervista con gli attori di Social Dream
                ]),
                'order' => 5,
            ],
            [
                'date' => '2021-03-15',
                'place' => 'YouTube',
                'title' => 'Collaborazione con altri Youtuber per un progetto comune',
                'description' => 'Surry ha partecipato a un progetto di collaborazione con altri youtuber, creando contenuti esclusivi insieme ad altri creator.',
                'links' => json_encode([
                    'https://www.youtube.com/watch?v=mi2f5vF1shs', // Video progetto collettivo
                    'https://www.youtube.com/watch?v=L_JGOCQzDkc'  // Video Collaborazione YouTuber
                ]),
                'order' => 6,
            ],
            [
                'date' => '2022-06-30',
                'place' => 'YouTube',
                'title' => 'Partecipazione a un evento di beneficenza con i Mates',
                'description' => 'I Mates organizzano un evento di beneficenza con la partecipazione di numerosi influencer e youtuber.',
                'links' => json_encode([
                    'https://www.youtube.com/watch?v=FwO-5gpfBOo', // Video Beneficenza Mates
                    'https://www.youtube.com/watch?v=ntK9l2_FF1g'  // Evento di Beneficenza
                ]),
                'order' => 7,
            ]
        ]);
    }
}
