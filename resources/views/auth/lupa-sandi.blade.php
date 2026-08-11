<x-tata-letak-tamu title="Lupa Kata Sandi?" subtitle="Tenang saja. Cukup beri tahu kami alamat email Anda, kami akan mengirimkan kode OTP untuk mengatur ulang kata sandi.">
    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-emerald-300">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div>
            <label for="email" class="block font-medium text-sm text-blue-100">{{ __('Email') }}</label>
            <div class="relative mt-1.5">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-blue-300/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com"
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-blue-200/40 shadow-sm focus:border-blue-300 focus:ring-blue-300/50 transition">
            </div>
            <x-kesalahan-input :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" :disabled="submitting"
            class="w-full py-2.5 rounded-xl bg-white hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed text-blue-950 font-semibold shadow-sm transition inline-flex items-center justify-center gap-2">
            <svg x-show="submitting" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            <span>Kirim Kode OTP</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-blue-200">
        Ingat password?
        <a href="{{ route('login') }}" class="font-semibold text-blue-300 hover:text-blue-100 transition">Kembali ke Halaman Login</a>
    </p>
</x-tata-letak-tamu>
