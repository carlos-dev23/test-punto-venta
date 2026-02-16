<div class="text-lg">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <label for="fecha-notas" class="block text-base font-medium text-gray-700 dark:text-gray-300">Ver notas del
                día</label>
            <input id="fecha-notas" type="date" wire:model.live="fecha"
                class="mt-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xl py-2" />
        </div>
    </div>

    {{-- Resumen del día (para comparar con la caja) --}}
    <div
        class="mb-8 rounded-xl border-2 border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 p-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Resumen del día</h2>
        <p class="mt-1 text-base text-gray-600 dark:text-gray-400">
            {{ \Carbon\Carbon::parse($fecha)->locale('es')->translatedFormat('l d \d\e F \d\e Y') }}</p>
        <div class="mt-4 flex flex-wrap gap-6">
            <div>
                <span class="block text-base font-medium text-gray-600 dark:text-gray-400">Cantidad de ventas</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $this->cantidadVentas }}</span>
            </div>
            <div>
                <span class="block text-base font-medium text-gray-600 dark:text-gray-400">Total vendido</span>
                <span
                    class="text-2xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($this->totalDia, 2) }}</span>
            </div>
        </div>
        <div class="mt-4 rounded-lg bg-white dark:bg-gray-800 p-4">
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">La caja debería tener: <span
                    class="text-indigo-600 dark:text-indigo-400">${{ number_format($this->totalDia, 2) }}</span></p>
            <p class="mt-1 text-base text-gray-600 dark:text-gray-400">Compare este monto con el dinero físico en caja
                para verificar que coincidan.</p>
        </div>
    </div>

    {{-- Listado de notas --}}
    <div
        class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-600">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Notas del día</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 text-lg">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col"
                            class="px-4 py-3 text-left text-base font-semibold text-gray-700 dark:text-gray-300">Hora
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-base font-semibold text-gray-700 dark:text-gray-300">Tipo
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-base font-semibold text-gray-700 dark:text-gray-300">Cliente
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-right text-base font-semibold text-gray-700 dark:text-gray-300">Total
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-right text-base font-semibold text-gray-700 dark:text-gray-300"><span
                                class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($this->sales as $sale)
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-gray-100">
                                {{ $sale->created_at->format('H:i') }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($sale->note_type === 'venta_unica')
                                    <span
                                        class="rounded-full bg-gray-200 dark:bg-gray-600 px-3 py-1 text-base font-medium text-gray-800 dark:text-gray-200">Venta
                                        única</span>
                                @else
                                    <span
                                        class="rounded-full bg-indigo-100 dark:bg-indigo-900/50 px-3 py-1 text-base font-medium text-indigo-800 dark:text-indigo-200">Nota
                                        de venta</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                @if ($sale->client)
                                    {{ $sale->client->name }}
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td
                                class="whitespace-nowrap px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                                ${{ number_format($sale->total, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <button type="button" wire:click="reprint({{ $sale->id }})"
                                    class="rounded-lg bg-indigo-100 dark:bg-indigo-900/50 px-3 py-1 text-sm font-medium text-indigo-700 dark:text-indigo-300 hover:bg-indigo-200 dark:hover:bg-indigo-900/70">Reimprimir</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No hay
                                notas para esta fecha.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Ticket (copiado de Pdv para reusar estilos de impresión) --}}
    @if ($showTicket && $this->ticketSale)
        <div id="ticket-modal-wrapper"
            class="ticket-modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            role="dialog" aria-modal="true" aria-labelledby="ticket-title">
            <div
                class="ticket-modal-card flex max-h-[90vh] w-full max-w-lg flex-col rounded-lg bg-white dark:bg-gray-800 shadow-xl">
                <div
                    class="ticket-modal-header flex items-center justify-between border-b border-gray-200 dark:border-gray-600 px-4 py-3">
                    <h2 id="ticket-title" class="text-xl font-semibold text-gray-900 dark:text-gray-100">Reimpresión de
                        Ticket</h2>
                    <button type="button" wire:click="closeTicket"
                        class="rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-1.5 text-lg font-medium text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600">Cerrar</button>
                </div>
                <div class="ticket-modal-body overflow-y-auto p-4 space-y-4">
                    <p class="ticket-modal-description text-base text-gray-600 dark:text-gray-400">Vista previa de
                        reimpresión:</p>
                    <div id="ticket-print-area"
                        class="ticket-body mx-auto w-[80mm] max-w-[80mm] border border-dashed border-gray-400 bg-white p-2 font-mono text-xs text-black"
                        style="width: 80mm;">
                        <div class="text-center font-semibold">TOLTECA Y CRUZ AZUL SEGURA</div>
                        @if (auth()->user()->branch_name)
                            <div class="text-center text-[10px]">{{ auth()->user()->branch_name }}</div>
                        @endif
                        @if (auth()->user()->address)
                            <div class="text-center text-[10px]">{{ auth()->user()->address }}</div>
                        @endif
                        <div class="text-center text-[10px] mt-0.5">
                            {{ $this->ticketSale->note_type === 'nota_venta' ? 'NOTA DE VENTA' : 'VENTA ÚNICA' }}
                            #{{ $this->ticketSale->id }}
                        </div>
                        <div class="text-center text-[10px]">{{ $this->ticketSale->created_at->format('d/m/Y H:i') }}
                            reimp.</div>
                        @if ($this->ticketSale->client)
                            <div class="mt-1 text-[10px]">Cliente: {{ $this->ticketSale->client->name }}</div>
                        @endif
                        <div class="my-2 border-t border-black border-dashed"></div>
                        @foreach ($this->ticketSale->saleItems as $item)
                            <div class="flex justify-between text-[10px] leading-tight">
                                <span class="flex-1 truncate">{{ $item->article->name ?? 'Artículo' }}</span>
                                <span class="shrink-0 pl-1">{{ (int) $item->quantity }} x
                                    ${{ number_format($item->unit_price, 2) }}</span>
                            </div>
                            <div class="text-right text-[10px]">${{ number_format($item->subtotal, 2) }}</div>
                        @endforeach
                        <div class="my-2 border-t border-black border-dashed"></div>
                        <div class="flex justify-between text-[10px] font-semibold">
                            <span>Subtotal:</span>
                            <span>${{ number_format($this->ticketSale->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-bold mt-0.5">
                            <span>TOTAL:</span>
                            <span>${{ number_format($this->ticketSale->total, 2) }}</span>
                        </div>
                        <div class="my-2 border-t border-black border-dashed"></div>
                        <div class="text-center text-[10px]">Gracias por su compra</div>
                    </div>
                </div>
                <div
                    class="ticket-modal-actions flex justify-end gap-3 border-t border-gray-200 dark:border-gray-600 px-4 py-3">
                    <button type="button" wire:click="closeTicket"
                        class="rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-lg font-medium text-gray-800 dark:text-gray-200">Cerrar</button>
                    <button type="button" onclick="window.print()"
                        class="rounded-lg bg-indigo-600 px-5 py-2 text-lg font-semibold text-white hover:bg-indigo-500">Imprimir</button>
                </div>
            </div>
        </div>
        <style>
            @media print {

                .ticket-modal-header,
                .ticket-modal-actions,
                .ticket-modal-description {
                    display: none !important;
                }

                .ticket-modal-overlay {
                    background: white !important;
                    padding: 0 !important;
                    align-items: flex-start !important;
                }

                .ticket-modal-card {
                    box-shadow: none !important;
                    border: none !important;
                    max-height: none !important;
                }

                .ticket-modal-body {
                    padding: 0 !important;
                }

                #ticket-print-area {
                    border: none !important;
                    box-shadow: none !important;
                }
            }
        </style>
    @endif
</div>
