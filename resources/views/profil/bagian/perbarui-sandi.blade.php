<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <x-label-input for="update_password_current_password" :value="__('Kata Sandi Saat Ini')" />
            <x-input-teks id="update_password_current_password" name="current_password" type="password" class="mt-1 w-full" autocomplete="current-password" />
            <x-kesalahan-input :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-label-input for="update_password_password" :value="__('Kata Sandi Baru')" />
            <x-input-teks id="update_password_password" name="password" type="password" class="mt-1 w-full" autocomplete="new-password" />
            <x-kesalahan-input :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-label-input for="update_password_password_confirmation" :value="__('Ulangi Kata Sandi Baru')" />
            <x-input-teks id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full" autocomplete="new-password" />
            <x-kesalahan-input :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white text-sm font-semibold transition inline-flex items-center gap-2">
                <x-ikon name="key" class="w-4 h-4" /> Perbarui Kata Sandi
            </button>
            @if (session('status') === 'password-updated')
                <span x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm text-emerald-600 font-medium">Kata sandi diperbarui.</span>
            @endif
        </div>
    </form>
</section>
