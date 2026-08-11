<x-tata-letak-tamu title="Verifikasi Email" :subtitle="'Masukkan kode OTP 6 digit yang dikirim ke ' . (auth()->user()->email ?? 'email Anda')">
    <div x-data="otpBoxes()" x-cloak>
        @if (session('status'))
            <div class="mb-4 text-sm font-medium text-emerald-300">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('verifikasi-otp.proses') }}" class="space-y-6" x-ref="form">
            @csrf

            <div x-show="!loading">
                <label for="kode" class="block font-medium text-sm text-blue-100">{{ __('Kode OTP (6 digit)') }}</label>
                <div class="mt-2 flex justify-center gap-2.5" x-ref="boxes">
                    <template x-for="(v, i) in boxes" :key="i">
                        <input
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            autocomplete="one-time-code"
                            x-model="boxes[i]"
                            @input="onInput(i, $event)"
                            @keydown.backspace="onBackspace(i)"
                            @keydown.left.prevent="focusBox(i - 1)"
                            @keydown.right.prevent="focusBox(i + 1)"
                            @paste.prevent="onPaste($event)"
                            :class="boxes[i] ? 'border-blue-300 bg-blue-400/10 text-white' : 'border-white/10 bg-white/5 text-white'"
                            class="w-11 h-12 sm:w-12 text-center text-lg font-bold rounded-xl border shadow-sm focus:border-blue-300 focus:ring-blue-300/50 transition"
                        >
                    </template>
                </div>
                <x-kesalahan-input :messages="$errors->get('kode')" class="mt-2" />
                <input type="hidden" name="kode" :value="boxes.join('')">

                <button type="submit" :disabled="!filled"
                    class="mt-6 w-full py-2.5 rounded-xl bg-white hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed text-blue-950 font-semibold shadow-sm transition inline-flex items-center justify-center gap-2">
                    <span>Verifikasi</span>
                </button>
            </div>

            <div x-show="loading" class="flex flex-col items-center justify-center py-10">
                <svg class="w-8 h-8 text-blue-300 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                <p class="mt-3 text-sm text-blue-200">Memverifikasi kode...</p>
            </div>
        </form>

        <div class="mt-6 flex items-center justify-between border-t border-white/10 pt-4">
            <form method="POST" action="{{ route('verifikasi-otp.kirim-ulang') }}">
                @csrf
                <button type="submit" :disabled="countdown > 0"
                    class="text-sm font-medium text-blue-300 hover:text-blue-100 disabled:text-blue-200/40 disabled:cursor-not-allowed transition">
                    <span x-show="countdown > 0" x-text="'Kirim ulang dalam ' + countdown + ' detik'"></span>
                    <span x-show="countdown === 0">Kirim ulang kode</span>
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-medium text-blue-300 hover:text-red-400 transition">Keluar</button>
            </form>
        </div>
    </div>
</x-tata-letak-tamu>

