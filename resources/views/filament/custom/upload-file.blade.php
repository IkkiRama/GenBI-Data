<div class="p-6">

    <!-- Breadcrumb -->
    <x-filament::breadcrumbs :breadcrumbs="[
        '/admin/strukturs' => 'Struktur',
        '' => 'List',
    ]" />

    <!-- Header -->
    <div class="flex justify-between items-center mt-4 mb-6">
        <h1 class="text-2xl font-semibold">Struktur</h1>
        <div class="text-sm text-gray-500">
            {{ $data }}
        </div>
    </div>

    <!-- 2 Kolom FIX -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- KIRI -->
        <form wire:submit="save"
            class="rounded-lg bg-white dark:bg-gray-800">

            <h2 class="font-semibold mb-4 pt-4">Upload Struktur</h2>

            <input type="file"
                wire:model="fileStruktur"
                class="w-full rounded-md p-3 mb-4
                bg-gray-50 dark:bg-gray-700">

            <button type="submit"
                class="w-full bg-gray-900 dark:bg-gray-700 text-white py-2 rounded-md">
                Unggah
            </button>
        </form>

        <!-- KANAN -->
        <form wire:submit="uploadMember p-5"
            class="rounded-lg bg-white dark:bg-gray-800">

            <h2 class="font-semibold mb-4 pt-4">Upload Member</h2>

            <input type="file"
                wire:model="fileMember"
                class="w-full rounded-md p-3 mb-4
                bg-gray-50 dark:bg-gray-700">

            <button type="submit"
                class="w-full bg-gray-900 dark:bg-gray-700 text-white py-2 rounded-md">
                Unggah
            </button>
        </form>

    </div>
</div>
