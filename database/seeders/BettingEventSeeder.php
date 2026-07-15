<?php

namespace Database\Seeders;

use App\Betting\Models\BettingEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BettingEventSeeder extends Seeder
{
    public function run(): void
    {
        if (BettingEvent::exists()) {
            return;
        }

        $events = [
            ['title' => 'Real Madrid vs Barcelona', 'category' => 'football', 'organiser' => 'La Liga', 'location' => 'Spain'],
            ['title' => 'Lakers vs Celtics', 'category' => 'basketball', 'organiser' => 'NBA', 'location' => 'USA'],
            ['title' => 'Djokovic vs Alcaraz', 'category' => 'tennis', 'organiser' => 'ATP', 'location' => 'International'],
        ];

        foreach ($events as $i => $data) {
            $start = now()->addDays($i + 2)->setTime(20, 0);
            BettingEvent::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']).'-'.Str::random(5),
                'category' => $data['category'],
                'organiser' => $data['organiser'],
                'location' => $data['location'],
                'start_at' => $start,
                'completes_at' => $start->copy()->addHours(3),
                'betting_close_at' => $start->copy()->subHour(),
                'status' => 'scheduled',
                'settlement_source' => 'Official league results',
            ]);
        }
    }
}
