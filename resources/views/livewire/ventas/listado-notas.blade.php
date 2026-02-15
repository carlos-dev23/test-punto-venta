<div class="text-lg">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <label for="fecha-notas" class="block text-base font-medium text-gray-700 dark:text-gray-300">Ver notas del día</label>
            <input
                id="fecha-notas"
                type="date"
                wire:model.live="fecha"
                class="mt-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xl py-2"
            />
        </div>
    </div>

    {{-- Resumen del día (para comparar con la caja) --}}
    <div class="mb-8 rounded-xl border-2 border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 p-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Resumen del día</h2>
        <p class="mt-1 text-base text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($fecha)->locale('es')->translatedFormat('l d \d\e F \d\e Y') }}</p>
        <div class="mt-4 flex flex-wrap gap-6">
            <div>
                <span class="block text-base font-medium text-gray-600 dark:text-gray-400">Cantidad de ventas</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $this->cantidadVentas }}</span>
            </div>
            <div>
                <span class="block text-base font-medium text-gray-600 dark:text-gray-400">Total vendido</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($this->totalDia, 2) }}</span>
            </div>
        </div>
        <div class="mt-4 rounded-lg bg-white dark:bg-gray-800 p-4">
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">La caja debería tener: <span class="text-indigo-600 dark:text-indigo-400">${{ number_format($this->totalDia, 2) }}</span></p>
            <p class="mt-1 text-base text-gray-600 dark:text-gray-400">Compare este monto con el dinero físico en caja para verificar que coincidan.</p>
        </div>
    </div>

    {{-- Listado de notas --}}
    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Notas del día</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 text-lg">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-base font-semibold text-gray-700 dark:text-gray-300">Hora</th>
                        <th scope="col" class="px-4 py-3 text-left text-base font-semibold text-gray-700 dark:text-gray-300">Tipo</th>
                        <th scope="col" class="px-4 py-3 text-left text-base font-semibold text-gray-700 dark:text-gray-300">Cliente</th>
                        <th scope="col" class="px-4 py-3 text-right text-base font-semibold text-gray-700 dark:text-gray-300">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($this->sales as $sale)
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-gray-100">{{ $sale->created_at->format('H:i') }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($sale->note_type === 'venta_unica')
                                    <span class="rounded-full bg-gray-200 dark:bg-gray-600 px-3 py-1 text-base font-medium text-gray-800 dark:text-gray-200">Venta única</span>
                                @else
                                    <span class="rounded-full bg-indigo-100 dark:bg-indigo-900/50 px-3 py-1 text-base font-medium text-indigo-800 dark:text-indigo-200">Nota de venta</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                @if($sale->client)
                                    {{ $sale->client->name }}
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">${{ number_format($sale->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No hay notas para esta fecha.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
