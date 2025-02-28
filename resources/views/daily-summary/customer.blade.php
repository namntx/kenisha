@extends('layouts.app')
@section('content')
<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-800">Chi tiết khách hàng: {{ $customer->name }}</h1>
            <div>
                <a href="{{ route('daily-summary.index', ['date' => $date->format('Y-m-d')]) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Quay lại
                </a>
            </div>
        </div>
        <p class="mt-1 text-sm text-gray-600">Tổng kết hoạt động đặt cược ngày {{ $date->format('d/m/Y') }}</p>
    </div>
<!-- Thông tin khách hàng -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-medium text-gray-700 mb-4">Thông tin khách hàng</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Thông tin liên hệ</h3>
            <dl class="mt-2 space-y-1">
                <div>
                    <dt class="inline text-sm font-medium text-gray-500">Tên:</dt>
                    <dd class="inline text-sm text-gray-900 ml-1">{{ $customer->name }}</dd>
                </div>
                <div>
                    <dt class="inline text-sm font-medium text-gray-500">Email:</dt>
                    <dd class="inline text-sm text-gray-900 ml-1">{{ $customer->email }}</dd>
                </div>
                <div>
                    <dt class="inline text-sm font-medium text-gray-500">Số điện thoại:</dt>
                    <dd class="inline text-sm text-gray-900 ml-1">{{ $customer->phone ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>
        <div>
            <h3 class="text-sm font-medium text-gray-500">Lịch sử hoạt động</h3>
            <dl class="mt-2 space-y-1">
                <div>
                    <dt class="inline text-sm font-medium text-gray-500">Ngày tạo:</dt>
                    <dd class="inline text-sm text-gray-900 ml-1">{{ $customer->created_at->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="inline text-sm font-medium text-gray-500">Tổng số vé cược:</dt>
                    <dd class="inline text-sm text-gray-900 ml-1">{{ $customer->bets()->count() }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<!-- Tổng quan ngày -->
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

<!-- Lịch sử giao dịch tháng -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-700">Lịch sử trong tháng</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng đặt</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng thắng</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lãi/Lỗ</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi tiết</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($monthlyHistory as $day)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ Carbon\Carbon::parse($day->date)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($day->total_bet, 0, ',', '.') }} đ
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                            {{ number_format($day->total_won, 0, ',', '.') }} đ
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium {{ $day->net_profit < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($day->net_profit, 0, ',', '.') }} đ
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('daily-summary.customer', ['customer' => $customer->id, 'date' => $day->date]) }}" class="text-blue-600 hover:text-blue-900">
                                Xem chi tiết
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($monthlyHistory->isEmpty())
        <div class="p-6 text-center text-gray-500">
            Không có dữ liệu trong tháng này
        </div>
    @endif
</div>

<!-- Danh sách vé cược -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-700">Danh sách vé cược trong ngày</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
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
            Không có vé cược nào trong ngày này
        </div>
    @endif
</div>
</div>
@endsection