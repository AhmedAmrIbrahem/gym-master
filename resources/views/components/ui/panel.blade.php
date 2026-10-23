{{-- Shared dark panel. Extra classes and HTML attributes are forwarded to the wrapper. --}}
<div {{ $attributes->class(['bg-gray-800', 'rounded-2xl', 'border', 'border-gray-700', 'shadow-xl']) }}>
    {{ $slot }}
</div>
