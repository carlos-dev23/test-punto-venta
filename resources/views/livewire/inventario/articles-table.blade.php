<div>
    {{-- Mensajes de éxito --}}
    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-100 dark:bg-green-900/30 px-4 py-3 text-base text-green-800 dark:text-green-200" role="alert">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1">
            <label for="search" class="sr-only">Buscar por nombre</label>
            <input
                id="search"
                type="text"
                wire:model.live="search"
                placeholder="Buscar por nombre…"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base sm:text-lg py-3"
            />
        </div>
        <div class="shrink-0">
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Registrar nuevo artículo
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-base font-semibold text-gray-700 dark:text-gray-300">Nombre</th>
                        <th scope="col" class="px-4 py-3 text-left text-base font-semibold text-gray-700 dark:text-gray-300">Unidad de medida</th>
                        <th scope="col" class="px-4 py-3 text-right text-base font-semibold text-gray-700 dark:text-gray-300">Stock</th>
                        <th scope="col" class="px-4 py-3 text-right text-base font-semibold text-gray-700 dark:text-gray-300">Precio adquisición<br><span class="font-normal text-sm">(por unidad de medida)</span></th>
                        <th scope="col" class="px-4 py-3 text-right text-base font-semibold text-gray-700 dark:text-gray-300">Precio venta<br><span class="font-normal text-sm">(por unidad de medida)</span></th>
                        <th scope="col" class="px-4 py-3 text-right text-base font-semibold text-gray-700 dark:text-gray-300">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($this->articles as $article)
                        <tr class="bg-white dark:bg-gray-800">
                            <td class="whitespace-nowrap px-4 py-3 text-base text-gray-900 dark:text-gray-100">{{ $article->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-base text-gray-700 dark:text-gray-300">{{ $article->unit }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-base text-gray-700 dark:text-gray-300">{{ number_format($article->stock, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-base text-gray-700 dark:text-gray-300">${{ number_format($article->purchase_price, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-base text-gray-700 dark:text-gray-300">${{ number_format($article->sale_price, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <button
                                    type="button"
                                    wire:click="openEditModal({{ $article->id }})"
                                    class="inline-flex items-center gap-1 rounded-md bg-gray-100 dark:bg-gray-700 px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600"
                                >
                                    Editar
                                </button>
                                <button
                                    type="button"
                                    wire:click="confirmDelete({{ $article->id }})"
                                    class="inline-flex items-center gap-1 rounded-md bg-red-100 dark:bg-red-900/30 px-3 py-2 text-base font-medium text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50"
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-base text-gray-500 dark:text-gray-400">
                                @if ($search)
                                    No se encontraron artículos con ese nombre.
                                @else
                                    No hay artículos. Use el botón «Registrar nuevo artículo» para agregar uno.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal crear/editar --}}
    <x-modal wire:model="showModal" maxWidth="xl">
        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ $editingArticleId ? 'Editar artículo' : 'Registrar nuevo artículo' }}
            </h3>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div>
                    <x-label for="name" value="Nombre del artículo" class="text-base" />
                    <input
                        id="name"
                        type="text"
                        wire:model="name"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-2"
                    />
                    <x-input-error for="name" class="mt-1" />
                </div>

                <div>
                    <x-label for="unit" value="Unidad de medida" class="text-base" />
                    <select
                        id="unit"
                        wire:model="unit"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-2"
                    >
                        @foreach($this->getUnits() as $u)
                            <option value="{{ $u }}">{{ $u }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="unit" class="mt-1" />
                </div>

                <div>
                    <x-label for="stock" value="Stock (cantidad en la unidad de medida elegida)" class="text-base" />
                    <input
                        id="stock"
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model="stock"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-2"
                    />
                    <x-input-error for="stock" class="mt-1" />
                </div>

                <p class="rounded-md bg-gray-100 dark:bg-gray-700/50 px-3 py-2 text-sm text-gray-700 dark:text-gray-300">
                    Los precios siguientes son <strong>por unidad de medida</strong>. Por ejemplo: si vende por kilogramo, escriba el precio por cada kg; si vende por pieza, el precio por cada pieza.
                </p>

                <div>
                    <x-label for="purchase_price" value="Precio de adquisición por unidad" class="text-base" />
                    <input
                        id="purchase_price"
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model="purchase_price"
                        placeholder="Ej. 10.50 (por cada kg, pieza, etc.)"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-2"
                    />
                    <x-input-error for="purchase_price" class="mt-1" />
                </div>

                <div>
                    <x-label for="sale_price" value="Precio de venta por unidad" class="text-base" />
                    <input
                        id="sale_price"
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model="sale_price"
                        placeholder="Ej. 15.00 (por cada kg, pieza, etc.)"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base py-2"
                    />
                    <x-input-error for="sale_price" class="mt-1" />
                </div>

                <div class="flex flex-row justify-end gap-3 pt-4">
                    <x-secondary-button type="button" wire:click="closeModals" class="!text-base !py-2.5 !px-4">
                        Cancelar
                    </x-secondary-button>
                    <x-button type="submit" class="!text-base !py-2.5 !px-4">
                        Guardar
                    </x-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Modal confirmar eliminar --}}
    <x-confirmation-modal wire:model="showDeleteModal">
        <x-slot name="title">
            Eliminar artículo
        </x-slot>

        <x-slot name="content">
            ¿Está seguro de que desea eliminar el artículo «{{ $this->articleToDeleteName ?? 'este artículo' }}»? Esta acción no se puede deshacer.
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button type="button" wire:click="$set('showDeleteModal', false)" class="!text-base !py-2.5 !px-4">
                Cancelar
            </x-secondary-button>
            <x-danger-button type="button" wire:click="deleteArticle" class="!text-base !py-2.5 !px-4">
                Sí, eliminar
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
