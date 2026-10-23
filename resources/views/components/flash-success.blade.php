@props(['message' => session('success')])

@if($message)
    <x-ui-alert type="success" {{ $attributes->class(['p-4']) }}>{{ $message }}</x-ui-alert>
@endif
