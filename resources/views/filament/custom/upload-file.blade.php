<div class="">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                colors: {
                    clifford: '#da373d',
                }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer utilities {
        .content-auto {
            content-visibility: auto;
        }
        }
    </style>
    <x-filament::breadcrumbs :breadcrumbs="[
        '/users' => 'Users',
        '' => 'List',
        ]" />
    <div class="flex justify-between mt-1">
        <h1 class="font-bold text-3xl">Users</h1>
        <div class="">
            {{ $data }}
        </div>
    </div>
    <div class="">
        <form wire:submit="save" class="w-full max-w-sm flex mt-2">
            <div class="mb-4">
                <label for="fileInput" class="block text-gray-700 text-sm font-bold mb-2">
                    Pilih Berkas
                </label>
                <input
                class="shadow appearance—none border rounded w—full py—2 px—3 text—gray—700 leading—tight focus:outline—none focus: shadow—outline"
                id="filelnput" type="file" wire:model="file">
            </div>
            <div class="flex items-center justify-between mt-3">
                <button class="bg-blue-500 hover:bg-blue-700 ml-3 text-white font—bold py-2 px-4 rounded focus:outline—none focus:shadow—outline" type="submit">
                    Unggah
                </button>
            </div>
        </form>
    </div>
</div>
