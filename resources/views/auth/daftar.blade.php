<x-tata-letak-tamu title="Buat Akun Baru" subtitle="Daftar untuk mulai menggunakan sistem absensi digital.">
    <div class="mb-4 rounded-xl bg-blue-400/10 border border-blue-400/20 px-4 py-3 text-xs text-blue-200 flex items-start gap-2">
        <x-ikon name="info" class="w-4 h-4 shrink-0 mt-0.5" />
        <span>Kode OTP verifikasi akan dikirim ke email Anda setelah mendaftar.</span>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-emerald-300">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-5" x-data="{ submitting: false, showPass: false, showConfirm: false, ...passwordStrength() }" @submit="submitting = true">
        @csrf

        <div>
            <label for="name" class="block font-medium text-sm text-blue-100">{{ __('Nama Lengkap') }}</label>
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-blue-300/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama lengkap"
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-blue-200/40 shadow-sm focus:border-blue-300 focus:ring-blue-300/50 transition">
            </div>
            <x-kesalahan-input :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="block font-medium text-sm text-blue-100">{{ __('Email') }}</label>
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-blue-300/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@email.com"
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-blue-200/40 shadow-sm focus:border-blue-300 focus:ring-blue-300/50 transition">
            </div>
            <x-kesalahan-input :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="block font-medium text-sm text-blue-100">{{ __('Kata Sandi') }}</label>
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-blue-300/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <input id="password" :type="showPass ? 'text' : 'password'" name="password" x-model="pass" required autocomplete="new-password" placeholder="Minimal 8 karakter"
                    class="w-full pl-10 pr-11 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-blue-200/40 shadow-sm focus:border-blue-300 focus:ring-blue-300/50 transition">
                <button type="button" @click="showPass = !showPass" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-full text-blue-300/70 hover:text-blue-100 hover:bg-white/10 transition">
                    <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPass" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            <x-kesalahan-input :messages="$errors->get('password')" class="mt-2" />

            {{-- Kekuatan sandi --}}
            <div class="mt-2" x-cloak>
                <div class="flex gap-1.5">
                    <template x-for="i in 4" :key="i">
                        <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                            :class="i <= score ? (score <= 1 ? 'bg-red-400' : score <= 2 ? 'bg-amber-400' : score === 3 ? 'bg-sky-400' : 'bg-emerald-400') : 'bg-white/10'"></div>
                    </template>
                </div>
                <div class="mt-1.5 text-xs font-medium"
                    :class="score <= 1 ? 'text-red-400' : score <= 2 ? 'text-amber-400' : score === 3 ? 'text-sky-300' : 'text-emerald-300'"
                    x-text="scoreLabel"></div>
            </div>
        </div>

        <div>
            <label for="password_confirmation" class="block font-medium text-sm text-blue-100">{{ __('Konfirmasi Kata Sandi') }}</label>
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-blue-300/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" x-model="pass2" required autocomplete="new-password" placeholder="Ulangi kata sandi"
                    class="w-full pl-10 pr-11 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-blue-200/40 shadow-sm focus:border-blue-300 focus:ring-blue-300/50 transition">
                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-full text-blue-300/70 hover:text-blue-100 hover:bg-white/10 transition">
                    <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showConfirm" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            <p class="mt-1.5 text-xs font-medium" x-cloak
                :class="pass2 && pass !== pass2 ? 'text-red-400' : 'text-emerald-300'"
                x-show="pass2 && pass !== pass2">Kata sandi tidak cocok.</p>
            <x-kesalahan-input :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" :disabled="submitting"
            class="w-full py-2.5 rounded-xl bg-white hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed text-blue-950 font-semibold shadow-sm transition inline-flex items-center justify-center gap-2">
            <svg x-show="submitting" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            <span>Daftar</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-blue-200">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-blue-300 hover:text-blue-100 transition">Masuk di sini</a>
    </p>

    <div class="mt-4 text-center border-t border-white/10 pt-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-300 hover:text-blue-100 transition">
            <x-ikon name="arrow-left" class="w-4 h-4" />
            {{ __('Kembali ke Beranda') }}
        </a>
    </div>
</x-tata-letak-tamu>

