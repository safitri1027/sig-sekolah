
@extends('layouts.app')

@section('title', 'Data Pengguna')

@section('content')

<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Data Pengguna</h1>

    <a href="{{ route('admin.users.create') }}"
        class="px-4 py-2 bg-blue-700 text-white rounded">
        + Tambah Pengguna
    </a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-200 text-left">
            <tr>
                <th class="p-3">No</th>
                <th class="p-3">Nama</th>
                <th class="p-3">Email</th>
                <th class="p-3">Role</th>
                <th class="p-3">Sekolah</th>
                <th class="p-3">Status</th>
                <th class="p-3">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($users as $user)
                <tr class="border-t">
                    <td class="p-3">
                        {{ $users->firstItem() + $loop->index }}
                    </td>

                    <td class="p-3">{{ $user->name }}</td>

                    <td class="p-3">{{ $user->email }}</td>

                    <td class="p-3">{{ $user->roleLabel() }}</td>

                    <td class="p-3">
                        {{ $user->sekolah->nama ?? '-' }}
                    </td>

                    <td class="p-3">
                        @if ($user->is_active)
                            <span class="text-green-700 font-semibold">
                                Aktif
                            </span>
                        @else
                            <span class="text-red-600 font-semibold">
                                Nonaktif
                            </span>
                        @endif
                    </td>

                    <td class="p-3">
                        <div class="flex gap-3 items-center">

                            <a href="{{ route('admin.users.edit', $user) }}"
                                class="text-blue-700">
                                Ubah
                            </a>

                            @if (!$user->is(auth()->user()))
                                <form method="POST"
                                    action="{{ route('admin.users.toggle-status', $user) }}"
                                    onsubmit="return confirm('Yakin ingin mengubah status akun ini?')">

                                    @csrf
                                    @method('PATCH')

                                    @if ($user->is_active)
                                        <button type="submit"
                                            class="text-orange-600">
                                            Nonaktifkan
                                        </button>
                                    @else
                                        <button type="submit"
                                            class="text-green-700">
                                            Aktifkan
                                        </button>
                                    @endif
                                </form>

                                <form method="POST"
                                    action="{{ route('admin.users.destroy', $user) }}"
                                    onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="text-red-600">
                                        Hapus
                                    </button>
                                </form>
                            @endif

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-4 text-center">
                        Belum ada data pengguna.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>

@endsection
