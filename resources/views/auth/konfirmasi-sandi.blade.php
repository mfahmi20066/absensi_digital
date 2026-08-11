<x-tata-letak-tamu title="Konfirmasi Kata Sandi" subtitle="Ini adalah area aman aplikasi. Konfirmasi kata sandi Anda sebelum melanjutkan.">
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5" x-data="{ show: false }">
        @csrf

        <div>
            <label for="password" class="block font-medium text-sm text-blue-100">{{ __('Password') }}</label>
            <div class="relative mt-1.5">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="Masukkan password"
                    class="w-full pr-11 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-blue-200/40 shadow-sm focus:border-blue-300 focus:ring-blue-300/50 transition">
                <button type="button" @click="show = !show" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-full text-blue-300/70 hover:text-blue-100 hover:bg-white/10 transition">
                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            <x-kesalahan-input :messages="$errors->get('password')" class="mt-2" />
        </div>

        <button type="submit"
            class="w-full py-2.5 rounded-xl bg-white hover:bg-blue-50 text-blue-950 font-semibold shadow-sm transition">
            {{ __('Konfirmasi') }}
        </button>
    </form>
</x-tata-letak-tamu>
