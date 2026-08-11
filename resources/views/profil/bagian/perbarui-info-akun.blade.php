<section>
    <form method="post" action="{{ route('profil.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-label-input for="name" :value="__('Nama Lengkap')" />
            <x-input-teks id="name" name="name" type="text" class="mt-1 w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-kesalahan-input class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-label-input for="email" :value="__('Email')" />
            <x-input-teks id="email" name="email" type="email" class="mt-1 w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-kesalahan-input class="mt-2" :messages="$errors->get('email')" />
            <p class="mt-1.5 text-xs text-gray-400">Email dipakai untuk login ke sistem.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white text-sm font-semibold transition inline-flex items-center gap-2">
                <x-ikon name="check" class="w-4 h-4" /> Simpan Perubahan
            </button>
            @if (session('status') === 'profile-updated')
                <span x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm text-emerald-600 font-medium">Tersimpan.</span>
            @endif
        </div>
    </form>
</section>
