@extends('layouts.app')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Tổng kết ngày {{ $date->format('d/m/Y') }}</h1>
        <p class="mt-1 text-sm text-gray-600">Tổng hợp hoạt động đặt cược của khách hàng</p>
    </div>

    <!-- Bộ lọc -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form action="{{ route('daily-summary.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Ngày</label>
                <input type="date" id="date" name="date" value="{{ $date->format('Y-m-d') }}" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Khách hàng</label>
                <select id="customer_id" name="customer_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">Tất cả khách hàng</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ $customerId == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Lọc
                </button>
            </div>
        </form>
    </div>

    <!-- Tổng quan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-700 mb-4">Tổng tiền đặt cược</h2>
            <p class="text-3xl font-bold text-blue-600">{{ number_format($totalBetAmount, 0, ',', '.') }} đ</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-700 mb-4">Tổng tiền trả thưởng</h2>
            <p class="text-3xl font-bold text-green-600">{{ number_format($totalWonAmount, 0, ',', '.') }} đ</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-700 mb-4">Lãi/Lỗ</h2>
            <p class="text-3xl font-bold {{ $netProfit < 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format($netProfit, 0, ',', '.') }} đ
            </p>
        </div>
    </div>

    <!-- Tổng kết theo khách hàng -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-700">Tổng kết theo khách hàng</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số vé</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng đặt</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng thắng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lãi/Lỗ</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($summaryByCustomer as $summary)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $summary['customer']->name }}</div>
                                <div class="text-sm text-gray-500">{{ $summary['customer']->phone ?? 'Không có SĐT' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $summary['bets_count'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($summary['bet_amount'], 0, ',', '.') }} đ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                                {{ number_format($summary['won_amount'], 0, ',', '.') }} đ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium {{ $summary['profit'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($summary['profit'], 0, ',', '.') }} đ
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('daily-summary.customer', ['customer' => $summary['customer']->id, 'date' => $date->format('Y-m-d')]) }}" class="text-blue-600 hover:text-blue-900">
                                    Xem chi tiết
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(empty($summaryByCustomer))
            <div class="p-6 text-center text-gray-500">
                Không có dữ liệu đặt cược nào cho ngày này
            </div>
        @endif
    </div>

    <!-- Danh sách vé cược -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-700">Danh sách vé cược</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đài</th>
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
                                {{ $bet->created_at->format('H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $bet->user->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $bet->province ? $bet->province->name : $bet->region->name }}
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
                Không có vé cược nào cho ngày này
            </div>
        @endif
    </div>
</div>
@endsection