<?php

namespace App\Betting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DepositMethod extends Model
{
    protected $table = 'betting_deposit_methods';

    protected $fillable = [
        'coin_name',
        'network',
        'address',
        'instructions',
        'qr_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function qrUrl(): ?string
    {
        if (! $this->qr_path) {
            return null;
        }

        return Storage::disk('public')->url($this->qr_path);
    }

    public function displayLabel(): string
    {
        return $this->network
            ? $this->coin_name.' ('.$this->network.')'
            : $this->coin_name;
    }
}
