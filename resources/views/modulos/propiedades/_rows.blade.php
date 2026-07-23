@forelse($properties as $property)
<tr class="hover:bg-slate-50 transition-colors">
    <td class="p-4">
        <div class="flex items-center gap-3">
            @php
                $showRoute = (isset($isAdmin) && $isAdmin)
                    ? route('admin.properties.show', $property)
                    : route('asesor.properties.show', $property);
            @endphp
            <a href="{{ $showRoute }}">
                <img src="{{ $property->primary_image_url }}"
                     class="w-12 h-12 rounded-lg object-cover border border-slate-200 hover:opacity-80 transition-opacity"
                     alt="{{ $property->title }}"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($property->title) }}&background=c5a059&color=fff&size=48'">
            </a>
            <div>
                <a href="{{ $showRoute }}"
                   class="font-bold text-slate-800 hover:text-mso-blue transition-colors truncate max-w-[200px] block">
                    {{ $property->title }}
                </a>
                <div class="flex gap-2 mt-1">
                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded
                        @if($property->type == 'venta') bg-blue-100 text-blue-700
                        @elseif($property->type == 'alquiler') bg-green-100 text-green-700
                        @else bg-purple-100 text-purple-700 @endif">
                        {{ $property->type }}
                    </span>
                    @if($property->category)
                        <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded">
                            {{ $property->category->name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </td>
    <td class="p-4 font-bold text-slate-700">
        {{ $property->formatted_price }}
    </td>
    <td class="p-4 text-slate-500">
        <div class="text-xs">
            {{ $property->full_location ?? 'Ubicación no especificada' }}
        </div>
    </td>
    <td class="p-4">
        @php
            $statusColors = [
                'publicada' => 'bg-green-100 text-green-700',
                'borrador' => 'bg-gray-100 text-gray-600',
                'vendida' => 'bg-blue-100 text-blue-700',
                'alquilada' => 'bg-indigo-100 text-indigo-700',
                'pendiente' => 'bg-yellow-100 text-yellow-700',
                'inactiva' => 'bg-red-100 text-red-700',
            ];
            $statusColor = $statusColors[$property->status] ?? 'bg-gray-100 text-gray-600';
        @endphp
        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
            {{ ucfirst($property->status) }}
        </span>
    </td>

    @can('ver usuarios')
        @if(isset($isAdmin) && $isAdmin)
            <td class="p-4 text-slate-600">
                {{ $property->user->name ?? 'N/A' }}
            </td>
        @endif
    @endcan

    <td class="p-4 text-right">
        <div class="flex justify-end gap-2">
            @can('ver propiedades')
                <a href="{{ $showRoute }}"
                   class="text-green-600 hover:text-green-800 font-medium p-1"
                   title="Ver detalle">
                    <i class="ph ph-eye text-lg"></i>
                </a>
            @else
                <span class="text-slate-300 cursor-not-allowed p-1" title="No tienes permiso para ver">
                    <i class="ph ph-eye text-lg"></i>
                </span>
            @endcan

            @can('editar propiedad')
                @php
                    $editRoute = (isset($isAdmin) && $isAdmin)
                        ? route('admin.properties.edit', $property)
                        : route('asesor.properties.edit', $property);
                @endphp
                <a href="{{ $editRoute }}"
                   class="text-blue-600 hover:text-blue-800 font-medium p-1"
                   title="Editar">
                    <i class="ph ph-pencil text-lg"></i>
                </a>
            @else
                <span class="text-slate-300 cursor-not-allowed p-1" title="No tienes permiso para editar">
                    <i class="ph ph-pencil text-lg"></i>
                </span>
            @endcan

            @can('eliminar propiedad')
                @php
                    $deleteRoute = (isset($isAdmin) && $isAdmin)
                        ? route('admin.properties.destroy', $property)
                        : route('asesor.properties.destroy', $property);
                @endphp
                <button onclick="openDeleteModal({{ $property->id }}, '{{ addslashes($property->title) }}', '{{ $deleteRoute }}')"
                        class="text-red-500 hover:text-red-700 font-medium p-1"
                        title="Eliminar">
                    <i class="ph ph-trash text-lg"></i>
                </button>
            @else
                <span class="text-slate-300 cursor-not-allowed p-1" title="No tienes permiso para eliminar">
                    <i class="ph ph-trash text-lg"></i>
                </span>
            @endcan
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="{{ (isset($isAdmin) && $isAdmin && auth()->user()->can('ver usuarios')) ? '6' : '5' }}"
        class="p-8 text-center text-slate-500">
        <i class="ph ph-house-line text-4xl mb-2 block"></i>
        No se encontraron propiedades.
    </td>
</tr>
@endforelse
