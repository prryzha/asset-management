@props([
'editRoute',
'deleteRoute',
'deleteMessage'=>'Yakin ingin menghapus data ini?'
])

<div class="flex justify-center gap-2">

    <a href="{{ $editRoute }}"
       class="px-3 py-1.5 text-xs rounded bg-yellow-500 hover:bg-yellow-600 text-white">

        Ubah

    </a>

    <form
        action="{{ $deleteRoute }}"
        method="POST">

        @csrf
        @method('DELETE')

        <button
            onclick="return confirm('{{ $deleteMessage }}')"
            class="px-3 py-1.5 text-xs rounded bg-red-600 hover:bg-red-700 text-white">

            Hapus

        </button>

    </form>

</div>