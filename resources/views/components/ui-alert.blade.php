@props(['type' => 'info'])

@php
    $type = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
@endphp

<div {{ $attributes->class(['gym-alert', 'gym-alert--' . $type, 'rounded-xl border px-4 py-3 text-sm font-semibold']) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    {{ $slot }}
</div>
