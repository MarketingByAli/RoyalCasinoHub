<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino;
use App\Models\ClaimedListing;
use App\Models\Review;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function users(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'name', 'email', 'role', 'created_at']);

            User::query()->orderBy('id')->chunk(500, function ($users) use ($out) {
                foreach ($users as $u) {
                    fputcsv($out, [$u->id, $u->name, $u->email, $u->role, $u->created_at?->toIso8601String()]);
                }
            });

            fclose($out);
        }, 'users-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function casinos(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'name', 'slug', 'country', 'status', 'average_rating', 'reviews_count', 'website']);

            Casino::query()->orderBy('id')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $c) {
                    fputcsv($out, [
                        $c->id,
                        $c->name,
                        $c->slug,
                        $c->country,
                        $c->status,
                        $c->average_rating,
                        $c->reviews_count,
                        $c->website,
                    ]);
                }
            });

            fclose($out);
        }, 'casinos-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function reviews(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'casino_id', 'user_id', 'rating', 'status', 'created_at']);

            Review::query()->orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        $r->casino_id,
                        $r->user_id,
                        $r->rating,
                        $r->status,
                        $r->created_at?->toIso8601String(),
                    ]);
                }
            });

            fclose($out);
        }, 'reviews-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function claims(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'casino_id', 'user_id', 'status', 'submitted_at']);

            ClaimedListing::query()->orderBy('id')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $cl) {
                    fputcsv($out, [
                        $cl->id,
                        $cl->casino_id,
                        $cl->user_id,
                        $cl->status,
                        $cl->submitted_at?->toIso8601String(),
                    ]);
                }
            });

            fclose($out);
        }, 'claims-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
