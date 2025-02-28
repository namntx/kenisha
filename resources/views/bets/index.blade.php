<!-- resources/views/bets/index.blade.php -->
@extends('layouts.app')

@section('content')
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Danh sách cược</h1>
            <a href="{{ route('bets.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Đặt cược mới
            </a>
        </div>
        
        <!-- Bộ lọc -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form action="{{ route('bets.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="from_date" class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
                    <input type="date" id="from_date" name="from_date" value="{{ request('from_date', now()->subDays(7)->format('Y-m-d')) }}" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="to_date" class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
                    <input type="date" id="to_date" name="to_date" value="{{ request('to_date', now()->format('Y-m-d')) }}" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select id="status" name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">Tất cả</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ kết quả</option>
                        <option value="won" {{ request('status') === 'won' ? 'selected' : '' }}>Trúng thưởng</option>
                        <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Thua</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Lọc
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Danh sách vé cược -->
        <div class="bg-white shadow overflow-hidden rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tỉnh/Khu vực</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kiểu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiền cược</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kết quả</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($bets as $bet)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $bet->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $bet->bet_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $bet->locationName }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $bet->betType->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $bet->numbers }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($bet->amount, 0, ',', '.') }} đ
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($bet->is_processed)
                                        @if($bet->is_won)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Thắng {{ number_format($bet->win_amount, 0, ',', '.') }} đ
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Thua
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Chờ kết quả
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($bets->isEmpty())
                <div class="p-6 text-center text-gray-500">
                    Không có vé cược nào
                </div>
            @endif
            
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
                {{ $bets->links() }}
            </div>
        </div>
    </div>
@endsection