@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-gray-800">Bảng điều khiển</h1>
        
        <!-- Nút nhanh -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @if(Auth::user()->isAgent())
                <a href="{{ route('customers.index') }}" class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:bg-blue-50 transition-colors">
                    <h2 class="text-lg font-medium text-gray-700 mb-2">Quản lý khách hàng</h2>
                    <p class="text-sm text-gray-500">Xem và quản lý danh sách khách hàng của bạn</p>
                </a>
                <a href="{{ route('customers.create') }}" class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:bg-blue-50 transition-colors">
                    <h2 class="text-lg font-medium text-gray-700 mb-2">Thêm khách hàng</h2>
                    <p class="text-sm text-gray-500">Tạo tài khoản mới cho khách hàng</p>
                </a>
            @endif
            
            <a href="{{ route('bets.index') }}" class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:bg-blue-50 transition-colors">
                <h2 class="text-lg font-medium text-gray-700 mb-2">Danh sách vé cược</h2>
                <p class="text-sm text-gray-500">Xem danh sách vé cược đã đặt</p>
            </a>
            
            <a href="{{ route('reports.daily') }}" class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:bg-blue-50 transition-colors">
                <h2 class="text-lg font-medium text-gray-700 mb-2">Báo cáo</h2>
                <p class="text-sm text-gray-500">Xem các báo cáo tài chính và thống kê</p>
            </a>
        </div>
        
        <!-- Tổng quan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Số dư -->
            <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                <h2 class="text-lg font-medium text-gray-700 mb-2">Số dư hiện tại</h2>
                <p class="text-3xl font-bold text-blue-600">{{ number_format($user->balance, 0, ',', '.') }} đ</p>
            </div>
            
            @if(Auth::user()->isAgent())
                <!-- Số lượng khách hàng -->
                <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                    <h2 class="text-lg font-medium text-gray-700 mb-2">Tổng khách hàng</h2>
                    <p class="text-3xl font-bold text-purple-600">{{ Auth::user()->customers()->count() }}</p>
                </div>
            @endif
            
            <!-- Tổng vé cược -->
            <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                <h2 class="text-lg font-medium text-gray-700 mb-2">Tổng vé cược</h2>
                @if(Auth::user()->isAgent())
                    <p class="text-3xl font-bold text-yellow-600">{{ App\Models\Bet::whereIn('user_id', Auth::user()->customers()->pluck('id'))->count() }}</p>
                @else
                    <p class="text-3xl font-bold text-yellow-600">{{ Auth::user()->bets()->count() }}</p>
                @endif
            </div>
        </div>
        
        <!-- Tỉnh đài hôm nay -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-medium text-gray-700">Tỉnh đài mở thưởng hôm nay</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    @php
                        $provincesByRegion = $todayProvinces->groupBy('region_id');
                    @endphp
                    
                    @foreach($provincesByRegion as $regionId => $provinces)
                        <div>
                            <h3 class="font-medium text-gray-700 mb-2">
                                @if($regionId == 1)
                                    Miền Bắc
                                @elseif($regionId == 2)
                                    Miền Trung
                                @else
                                    Miền Nam
                                @endif
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($provinces as $province)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $province->name }} ({{ $province->code }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        @if(Auth::user()->isAgent())
            <!-- Khách hàng mới nhất -->
            <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-700">Khách hàng mới nhất</h2>
                    <a href="{{ route('customers.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Xem tất cả</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số dư</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(Auth::user()->customers()->latest()->take(5)->get() as $customer)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $customer->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $customer->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($customer->balance, 0, ',', '.') }} đ
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $customer->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:text-blue-900 mr-3">Chi tiết</a>
                                        <a href="{{ route('customers.bet', $customer) }}" class="text-green-600 hover:text-green-900">Đặt cược</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(Auth::user()->customers()->count() === 0)
                    <div class="p-6 text-center text-gray-500">
                        Bạn chưa có khách hàng nào. <a href="{{ route('customers.create') }}" class="text-blue-600 hover:text-blue-800">Thêm khách hàng mới</a>
                    </div>
                @endif
            </div>
        @endif
        
        <!-- Vé cược gần đây -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-700">Vé cược gần đây</h2>
                <a href="{{ route('bets.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Xem tất cả</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày</th>
                            @if(Auth::user()->isAgent())
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách hàng</th>
                            @endif
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tỉnh/Khu vực</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kiểu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiền cược</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kết quả</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            // Nếu là Agent, lấy vé cược của khách hàng
                            if(Auth::user()->isAgent()) {
                                $customerIds = Auth::user()->customers()->pluck('id');
                                $recentBets = App\Models\Bet::whereIn('user_id', $customerIds)
                                    ->with(['user', 'betType', 'region', 'province'])
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)
                                    ->get();
                            } else {
                                $recentBets = Auth::user()->bets()
                                    ->with(['betType', 'region', 'province'])
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)
                                    ->get();
                            }
                        @endphp
                        
                        @foreach($recentBets as $bet)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $bet->bet_date->format('d/m/Y') }}
                                </td>
                                @if(Auth::user()->isAgent())
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $bet->user->name }}
                                    </td>
                                @endif
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
            @if($recentBets->isEmpty())
                <div class="p-6 text-center text-gray-500">
                    Chưa có vé cược nào
                </div>
            @endif
        </div>
    </div>
@endsection