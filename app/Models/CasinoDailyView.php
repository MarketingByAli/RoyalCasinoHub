<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasinoDailyView extends Model
{
    protected $fillable = ['casino_id', 'day', 'views'];

    protected function casts(): array
    {
        return [
            'day' => 'date',
        ];
    }

    public function casino(): BelongsTo
    {
        return $this->belongsTo(Casino::class);
    }
}
