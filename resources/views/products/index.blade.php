<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestão de Produtos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex justify-between mb-6">
                        <h3 class="text-2xl font-bold">Lista de Produtos</h3>
                        <a href="{{ route('products.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Adicionar Produto
                        </a>
                    </div>

                    <!-- Mensagem de sucesso -->
                    @if ($message = Session::get('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ $message }}</span>
                        </div>
                    @endif

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nome</th>
                                    <th scope="col" class="px-6 py-3">Preço</th>
                                    <th scope="col" class="px-6 py-3" width="280px">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $product->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        R$ {{ number_format($product->price, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('products.destroy',$product->id) }}" method="POST">
                                            <a href="{{ route('products.edit',$product->id) }}" class="font-medium text-blue-600 hover:underline mr-4">Editar</a>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 hover:underline">Deletar</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr class="bg-white border-b">
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                        Nenhum produto encontrado.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Links de Paginação -->
                    <div class="mt-4">
                        {!! $products->links() !!}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
