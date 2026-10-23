{{-- Shared primary submit control. Per-page sizing and spacing remain customizable. --}}
<button type="submit" {{ $attributes->class(['bg-blue-600', 'hover:bg-blue-500', 'text-white', 'font-bold', 'rounded-xl']) }}>
    {{ $slot }}
</button>
