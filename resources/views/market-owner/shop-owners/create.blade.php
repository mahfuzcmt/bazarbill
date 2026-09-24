<x-app-layout>
    <x-slot name="header">{{ __('shop_owners.add') }}</x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('market-owner.shop-owners.store') }}" method="POST" class="glass-card p-6 space-y-6">
            @csrf
            @include('market-owner.shop-owners._form')

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t">
                <a href="{{ route('market-owner.shop-owners.index') }}" class="w-full sm:w-auto text-center px-4 py-2 btn-secondary transition">{{ __('messages.cancel') }}</a>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">{{ __('shop_owners.create_button') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
