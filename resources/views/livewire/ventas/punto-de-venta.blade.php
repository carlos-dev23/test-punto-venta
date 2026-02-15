<div class="text-lg" x-data="{ showNotify: false, notifyMsg: '' }" x-on:notify.window="notifyMsg = $event.detail?.message || ''; showNotify = true; setTimeout(() => showNotify = false, 3500)">
    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-100 dark:bg-green-900/30 px-4 py-3 text-lg text-green-800 dark:text-green-200">
            {{ session('message') }}
        </div>
    @endif

    <div x-show="showNotify" x-cloak class="fixed top-4 right-4 z-50 rounded-lg bg-amber-100 dark:bg-amber-900/50 px-4 py-3 text-lg text-amber-800 dark:text-amber-200 shadow-lg" x-transition x-text="notifyMsg"></div>

    {{-- Indicador de pasos --}}
    <nav class="mb-8 flex items-center justify-center gap-2 sm:gap-4" aria-label="Pasos">
        <button type="button" wire:click="goToStep(1)" class="rounded-lg px-4 py-2 text-lg font-medium {{ $step === 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">1. Armar nota</button>
        <span class="text-gray-400">→</span>
        <button type="button" wire:click="goToStep(2)" class="rounded-lg px-4 py-2 text-lg font-medium {{ $step === 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">2. Revisar</button>
        <span class="text-gray-400">→</span>
        <button type="button" wire:click="goToStep(3)" class="rounded-lg px-4 py-2 text-lg font-medium {{ $step === 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">3. Tipo de nota</button>
    </nav>

    @if ($step === 1)
        {{-- Paso 1: Búsqueda + inventario + carrito lateral --}}
        <div class="flex flex-col gap-6 lg:flex-row">
            <div class="flex-1">
                <label for="search-ventas" class="sr-only">Buscar artículo</label>
                <input
                    id="search-ventas"
                    type="text"
                    wire:model.live="search"
                    placeholder="Buscar por nombre del artículo…"
                    class="mb-4 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-2xl py-4"
                />
                <div class="rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                    <h3 class="mb-4 text-2xl font-semibold text-gray-900 dark:text-gray-100">Artículos disponibles</h3>
                    <ul class="space-y-3">
                        @forelse($this->articles as $article)
                            <li class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 px-4 py-4">
                                <div class="min-w-0 flex-1">
                                    <span class="block text-xl font-medium text-gray-900 dark:text-gray-100">{{ $article->name }}</span>
                                    <span class="mt-1 block text-lg text-gray-600 dark:text-gray-400">Unidad: {{ $article->unit }} · Stock: {{ (int) $article->stock }} · ${{ number_format($article->sale_price, 2) }}</span>
                                </div>
                                <button type="button" wire:click="addToCart({{ $article->id }}, 1)" class="shrink-0 rounded-lg bg-indigo-600 px-6 py-3 text-xl font-medium text-white hover:bg-indigo-500">Agregar</button>
                            </li>
                        @empty
                            <li class="py-8 text-center text-xl text-gray-500 dark:text-gray-400">No hay artículos o no coincide la búsqueda.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="w-full lg:w-96 shrink-0">
                <div class="sticky top-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-4 shadow">
                    <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-gray-100">Artículos en la nota</h3>
                    @if (count($cart) === 0)
                        <p class="text-base text-gray-500 dark:text-gray-400">Aún no hay artículos. Use la lista de la izquierda para agregar.</p>
                    @else
                        <ul class="mb-4 space-y-2">
                            @foreach($cart as $index => $item)
                                <li class="flex flex-wrap items-center justify-between gap-2 rounded bg-gray-100 dark:bg-gray-700/50 px-2 py-2 text-base">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $item['name'] }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">${{ number_format($item['quantity'] * $item['unit_price'], 2) }}</span>
                                    <div class="flex w-full items-center justify-end gap-1 sm:w-auto">
                                        <button type="button" wire:click="updateCartItem({{ $index }}, {{ max(1, (int)$item['quantity'] - 1) }})" class="rounded bg-gray-300 dark:bg-gray-600 px-2 py-1 text-lg font-bold">−</button>
                                        <input type="number" step="1" min="1" value="{{ (int) $item['quantity'] }}" class="w-16 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-center text-lg" wire:change="updateCartItem({{ $index }}, $event.target.value)">
                                        <button type="button" wire:click="updateCartItem({{ $index }}, {{ (int)$item['quantity'] + 1 }})" class="rounded bg-gray-300 dark:bg-gray-600 px-2 py-1 text-lg font-bold">+</button>
                                        <button type="button" wire:click="removeFromCart({{ $index }})" class="rounded bg-red-100 dark:bg-red-900/30 px-2 py-1 text-sm text-red-700 dark:text-red-300">Quitar</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mb-3 text-xl font-semibold text-gray-900 dark:text-gray-100">Total: ${{ number_format($this->cartTotal, 2) }}</p>
                        <button type="button" wire:click="goToStep(2)" class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-xl font-semibold text-white hover:bg-indigo-500">Continuar al resumen</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($step === 2)
        {{-- Paso 2: Solo ítems de la nota + resumen --}}
        <div class="mx-auto max-w-4xl">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden shadow">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 text-lg">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Artículo</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Unidad</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Cantidad</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">P. unit.</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($cart as $index => $item)
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $item['name'] }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $item['unit'] }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $item['quantity'] }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($item['unit_price'], 2) }}</td>
                                <td class="px-4 py-3 text-right font-medium">${{ number_format($item['quantity'] * $item['unit_price'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/30 px-4 py-4 flex justify-end">
                    <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">Total: ${{ number_format($this->cartTotal, 2) }}</p>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-4">
                <button type="button" wire:click="goToStep(1)" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-5 py-3 text-xl font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600">Agregar más artículos</button>
                <button type="button" wire:click="goToStep(3)" class="rounded-lg bg-indigo-600 px-5 py-3 text-xl font-semibold text-white hover:bg-indigo-500">Confirmar y elegir tipo de nota</button>
            </div>
        </div>
    @endif

    @if ($step === 3)
        {{-- Paso 3: Solo 2 opciones — Venta única o Nota de venta --}}
        <div class="mx-auto max-w-3xl space-y-8">
            @if ($noteType === null)
                <p class="text-xl text-gray-700 dark:text-gray-300">Elija el tipo de nota para esta venta.</p>
                <div class="grid gap-6 sm:grid-cols-2">
                    <button type="button" wire:click="selectVentaUnica" class="flex flex-col rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-6 text-left shadow-sm hover:border-indigo-500 hover:shadow-md transition">
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">Venta única</span>
                        <span class="mt-2 text-lg text-gray-600 dark:text-gray-400">Cliente ocasional. Solo registro de venta, no se requiere factura.</span>
                    </button>
                    <button type="button" wire:click="selectNotaDeVenta" class="flex flex-col rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-6 text-left shadow-sm hover:border-indigo-500 hover:shadow-md transition">
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">Nota de venta</span>
                        <span class="mt-2 text-lg text-gray-600 dark:text-gray-400">Nota vinculada a un cliente. Podrá facturarse cuando lo necesite.</span>
                    </button>
                </div>
            @endif

            @if ($noteType === 'venta_unica')
                <div class="rounded-lg border-2 border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Venta única</h3>
                    <p class="mt-2 text-lg text-gray-700 dark:text-gray-300">Se registrará la venta sin cliente. Total: ${{ number_format($this->cartTotal, 2) }}</p>
                    <div class="mt-4 flex gap-4">
                        <button type="button" wire:click="backToNoteTypeChoice" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-lg font-medium text-gray-800 dark:text-gray-200">Volver</button>
                        <button type="button" wire:click="confirmVentaUnica" class="rounded-lg bg-indigo-600 px-5 py-3 text-lg font-semibold text-white hover:bg-indigo-500">Registrar venta única</button>
                    </div>
                </div>
            @endif

            @if ($noteType === 'nota_venta')
                <div class="rounded-lg border-2 border-indigo-200 dark:border-indigo-800 bg-white dark:bg-gray-800 p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Nota de venta — Seleccione un cliente</h3>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">Elija un cliente de la lista o registre uno nuevo. Total: ${{ number_format($this->cartTotal, 2) }}</p>

                    <div class="mt-4">
                        <button type="button" wire:click="openNewClientModal" class="mb-4 rounded-lg bg-green-600 px-5 py-3 text-lg font-semibold text-white hover:bg-green-500">Nuevo cliente</button>

                        <ul class="space-y-2">
                            @forelse($this->clients as $client)
                                <li>
                                    <button type="button" wire:click="selectClient({{ $client->id }})" class="w-full rounded-lg px-4 py-3 text-left text-lg {{ $selectedClientId === $client->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                        <span class="font-medium">{{ $client->name }}</span>
                                        @if($client->phone)
                                            <span class="ml-2 text-base opacity-80">· {{ $client->phone }}</span>
                                        @endif
                                        @if($client->rfc)
                                            <span class="ml-2 text-base opacity-80">· RFC {{ $client->rfc }}</span>
                                        @endif
                                    </button>
                                </li>
                            @empty
                                <li class="py-4 text-lg text-gray-500 dark:text-gray-400">No hay clientes registrados. Use el botón «Nuevo cliente».</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-4">
                        <button type="button" wire:click="backToNoteTypeChoice" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-lg font-medium text-gray-800 dark:text-gray-200">Volver</button>
                        <button type="button" wire:click="confirmNotaDeVenta" class="rounded-lg bg-indigo-600 px-5 py-3 text-lg font-semibold text-white hover:bg-indigo-500 disabled:opacity-50" @if(!$selectedClientId) disabled @endif>Crear nota de venta</button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Modal Ticket (vista previa e imprimir) --}}
    @if ($showTicket && $this->ticketSale)
        <div id="ticket-modal-wrapper" class="ticket-modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="ticket-title">
            <div class="ticket-modal-card flex max-h-[90vh] w-full max-w-lg flex-col rounded-lg bg-white dark:bg-gray-800 shadow-xl">
                <div class="ticket-modal-header flex items-center justify-between border-b border-gray-200 dark:border-gray-600 px-4 py-3">
                    <h2 id="ticket-title" class="text-xl font-semibold text-gray-900 dark:text-gray-100">Ticket de venta</h2>
                    <button type="button" wire:click="closeTicket" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-1.5 text-lg font-medium text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600">Cerrar</button>
                </div>
                <div class="ticket-modal-body overflow-y-auto p-4 space-y-4">
                    <p class="ticket-modal-description text-base text-gray-600 dark:text-gray-400">Vista previa del ticket (compatible con impresoras de tickets). Al imprimir verá exactamente este diseño:</p>
                    <div id="ticket-print-area" class="ticket-body mx-auto w-[80mm] max-w-[80mm] border border-dashed border-gray-400 bg-white p-2 font-mono text-xs text-black" style="width: 80mm;">
                        <div class="text-center font-semibold">{{ config('app.name') }}</div>
                        <div class="text-center text-[10px] mt-0.5">
                            {{ $this->ticketSale->note_type === 'nota_venta' ? 'NOTA DE VENTA' : 'VENTA ÚNICA' }} #{{ $this->ticketSale->id }}
                        </div>
                        <div class="text-center text-[10px]">{{ $this->ticketSale->created_at->format('d/m/Y H:i') }}</div>
                        @if ($this->ticketSale->client)
                            <div class="mt-1 text-[10px]">Cliente: {{ $this->ticketSale->client->name }}</div>
                        @endif
                        <div class="my-2 border-t border-black border-dashed"></div>
                        @foreach ($this->ticketSale->saleItems as $item)
                            <div class="flex justify-between text-[10px] leading-tight">
                                <span class="flex-1 truncate">{{ $item->article->name ?? 'Artículo' }}</span>
                                <span class="shrink-0 pl-1">{{ (int) $item->quantity }} x ${{ number_format($item->unit_price, 2) }}</span>
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
                <div class="ticket-modal-actions flex justify-end gap-3 border-t border-gray-200 dark:border-gray-600 px-4 py-3">
                    <button type="button" wire:click="closeTicket" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-lg font-medium text-gray-800 dark:text-gray-200">Cerrar</button>
                    <button type="button" onclick="window.print()" class="rounded-lg bg-indigo-600 px-5 py-2 text-lg font-semibold text-white hover:bg-indigo-500">Imprimir</button>
                </div>
            </div>
        </div>
        <style>
            @media print {
                .ticket-modal-header,
                .ticket-modal-actions,
                .ticket-modal-description { display: none !important; }
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
                .ticket-modal-body { padding: 0 !important; }
                #ticket-print-area {
                    border: none !important;
                    box-shadow: none !important;
                }
            }
        </style>
    @endif

    {{-- Modal Nuevo cliente --}}
    <x-modal wire:model="showNewClientModal" maxWidth="xl">
        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Nuevo cliente</h3>
            <form wire:submit="saveNewClient" class="mt-4 space-y-4">
                <div>
                    <x-label for="clientName" value="Nombre" class="text-base" />
                    <input id="clientName" type="text" wire:model="clientName" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 text-lg py-2" required />
                    <x-input-error for="clientName" class="mt-1" />
                </div>
                <div>
                    <x-label for="clientRfc" value="RFC (opcional)" class="text-base" />
                    <input id="clientRfc" type="text" wire:model="clientRfc" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 text-lg py-2" />
                    <x-input-error for="clientRfc" class="mt-1" />
                </div>
                <div>
                    <x-label for="clientPhone" value="Teléfono de contacto" class="text-base" />
                    <input id="clientPhone" type="text" wire:model="clientPhone" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 text-lg py-2" />
                    <x-input-error for="clientPhone" class="mt-1" />
                </div>
                <div>
                    <x-label for="clientAddress" value="Dirección" class="text-base" />
                    <textarea id="clientAddress" wire:model="clientAddress" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 text-lg py-2"></textarea>
                    <x-input-error for="clientAddress" class="mt-1" />
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" wire:click="closeNewClientModal" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-lg font-medium text-gray-800 dark:text-gray-200">Cancelar</button>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-lg font-semibold text-white hover:bg-indigo-500">Guardar</button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
