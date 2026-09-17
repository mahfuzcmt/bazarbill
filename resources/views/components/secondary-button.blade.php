<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-secondary px-4 py-2 text-sm disabled:opacity-40']) }}>
    {{ $slot }}
</button>
