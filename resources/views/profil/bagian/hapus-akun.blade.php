<section class="space-y-5">
    <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3.5 text-sm text-red-700 flex items-start gap-2.5">
        <x-ikon name="alert" class="w-4 h-4 shrink-0 mt-0.5" />
        <span>Setelah akun dihapus, seluruh data Anda di dalam sistem akan terhapus permanen dan tidak dapat dipulihkan. Harap pastikan Anda tidak memerlukan data tersebut lagi.</span>
    </div>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition"
    >
        <x-ikon name="trash" class="w-4 h-4" /> Hapus Akun
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profil.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <x-ikon name="trash" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Yakin ingin menghapus akun?</h2>
                    <p class="text-sm text-gray-500">Seluruh data Anda akan terhapus permanen.</p>
                </div>
            </div>

            <div class="mt-5">
                <x-label-input for="password" :value="__('Masukkan kata sandi untuk konfirmasi')" />
                <x-input-teks
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 w-full"
                    placeholder="Kata sandi Anda"
                    autocomplete="current-password"
                />
                <x-kesalahan-input :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition">
                    <x-ikon name="trash" class="w-4 h-4" /> Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>
