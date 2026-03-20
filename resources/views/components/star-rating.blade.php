@props(['rating', 'size' => 'text-sm'])

<span role="img" aria-label="{{ round($rating) }} out of 5 stars">
@for($i = 1; $i <= 5; $i++)
    <span class="{{ $size }}" aria-hidden="true">{{ $i <= round($rating) ? '★' : '☆' }}</span>
@endfor
</span>
