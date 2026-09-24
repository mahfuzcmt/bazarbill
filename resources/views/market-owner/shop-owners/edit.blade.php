<x-app-layout>
    <x-slot name="header">{{ __('shop_owners.edit') }}: {{ $shopOwner->getLocalizedName() }}</x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('market-owner.shop-owners.update', $shopOwner) }}" method="POST" class="glass-card p-6 space-y-6">
            @csrf
            @method('PUT')
            @include('market-owner.shop-owners._form')

            <p class="text-xs text-gray-500">{{ __('shop_owners.inactive_description') }}</p>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t">
                <a href="{{ route('market-owner.shop-owners.index') }}" class="w-full sm:w-auto text-center px-4 py-2 btn-secondary transition">{{ __('messages.cancel') }}</a>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">{{ __('messages.save_changes') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
