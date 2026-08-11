<x-tata-letak-tamu title="Verifikasi Email" subtitle="Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi email Anda dengan mengklik tautan yang kami kirimkan. Jika tidak menerima email, kami akan dengan senang hati mengirimkannya lagi.">
    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-xl bg-emerald-400/10 border border-emerald-400/20 px-4 py-3 text-sm text-emerald-300 font-medium">
            {{ __('Tautan verifikasi baru telah dikirim ke email yang Anda daftarkan.') }}
        </div>
    @endif

    <div class="space-y-5">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                class="w-full py-2.5 rounded-xl bg-white hover:bg-blue-50 text-blue-950 font-semibold shadow-sm transition">
                {{ __('Kirim Ulang Email Verifikasi') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center border-t border-white/10 pt-4">
            @csrf
            <button type="submit" class="text-sm font-medium text-blue-300 hover:text-red-400 transition">
                {{ __('Keluar') }}
            </button>
        </form>
    </div>
</x-tata-letak-tamu>
