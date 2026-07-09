<div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full mx-4 text-center">
    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="ph ph-warning text-3xl"></i>
    </div>
    <h3 class="text-xl font-bold text-gray-800 mb-2">¿Eliminar Usuario?</h3>
    <p class="text-gray-600 mb-6">
        Estás a punto de eliminar a <strong>{{ $user->name }}</strong>. Esta acción no se puede deshacer.
    </p>

    <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="flex justify-center gap-3">
            <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancelar</button>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 shadow-sm">
                Sí, Eliminar
            </button>
        </div>
    </form>
</div>
