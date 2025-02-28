@extends('layouts.app')

@section('content')
    <div>
        <!-- Thêm tab vào phần trên trang -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex" aria-label="Tabs">
                    <a href="#overview" class="tab-link active border-blue-500 text-blue-600 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm" data-target="overview-tab">
                        Tổng quan
                    </a>
                    <a href="#settings" class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm" data-target="settings-tab">
                        Cài đặt tỷ lệ
                    </a>
                </nav>
            </div>
        </div>
        <div id="overview-tab" class="tab-content">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Chi tiết khách hàng</h1>
                <div class="flex space-x-3">
                    <a href="{{ route('customers.bet', $customer) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Đặt cược
                    </a>
                    <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Sửa thông tin
                    </a>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Thông tin cơ bản -->
                <div class="bg-white rounded-lg shadow p-6 col-span-1">
                    <h2 class="text-lg font-medium text-gray-700 mb-4">Thông tin khách hàng</h2>
                    <dl class="space-y-3">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Tên:</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $customer->name }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Email:</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $customer->email }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Số điện thoại:</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $customer->phone ?? 'N/A' }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Số dư:</dt>
                            <dd class="text-sm font-semibold text-blue-600 col-span-2">{{ number_format($customer->balance, 0, ',', '.') }} đ</dd>
                        </div>
                    </dl>
                </div>
                
            <!-- Thống kê cược -->
            <div class="bg-white rounded-lg shadow p-6 col-span-1">
                    <h2 class="text-lg font-medium text-gray-700 mb-4">Thống kê đặt cược</h2>
                    <dl class="space-y-3">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Tổng vé:</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ $totalBets }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Thắng:</dt>
                            <dd class="text-sm text-green-600 col-span-2">{{ $wonBets }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Thua:</dt>
                            <dd class="text-sm text-red-600 col-span-2">{{ $lostBets }}</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Chờ kết quả:</dt>
                            <dd class="text-sm text-yellow-600 col-span-2">{{ $pendingBets }}</dd>
                        </div>
                    </dl>
                </div>
                
                <!-- Thống kê tài chính -->
                <div class="bg-white rounded-lg shadow p-6 col-span-1">
                    <h2 class="text-lg font-medium text-gray-700 mb-4">Thống kê tài chính</h2>
                    <dl class="space-y-3">
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Tổng cược:</dt>
                            <dd class="text-sm text-gray-900 col-span-2">{{ number_format($totalSpent, 0, ',', '.') }} đ</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Tổng thắng:</dt>
                            <dd class="text-sm text-green-600 col-span-2">{{ number_format($totalWon, 0, ',', '.') }} đ</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <dt class="text-sm font-medium text-gray-500">Lãi/Lỗ:</dt>
                            <dd class="text-sm font-semibold {{ ($totalWon - $totalSpent) >= 0 ? 'text-green-600' : 'text-red-600' }} col-span-2">
                                {{ number_format($totalWon - $totalSpent, 0, ',', '.') }} đ
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <!-- Điều chỉnh số dư -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-700 mb-4">Điều chỉnh số dư</h2>
                <form action="{{ route('customers.adjust-balance', $customer) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Số tiền (đơn vị: đồng)</label>
                            <input type="number" id="amount" name="amount" step="1000" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-2 py-2 border">
                            <p class="mt-1 text-xs text-gray-500">Nhập số dương để nạp tiền, số âm để trừ tiền</p>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Mô tả</label>
                            <input type="text" id="description" name="description" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-2 py-2 border">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cập nhật số dư
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Vé cược gần đây -->
            <div class="bg-white rounded-lg shadow">
                <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-700">Vé cược gần đây</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tỉnh/Khu vực</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kiểu</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiền cược</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kết quả</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentBets as $bet)
                                <tr>
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
                @if($recentBets->isEmpty())
                    <div class="p-6 text-center text-gray-500">
                        Khách hàng chưa có vé cược nào
                    </div>
                @endif
            </div>

            <!-- Hiển thị bảng tỷ lệ cược -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-700">Tỷ lệ cược</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Tỷ lệ Miền Nam -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h3 class="font-medium text-gray-700 mb-3">Miền Nam</h3>
                            <dl class="space-y-2">
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Đề:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->south_head_tail_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Lô:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->south_lo_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">3 Càng:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->south_3_digits_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">4 Càng:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->south_4_digits_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Đá Xiên:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->south_slide_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Đá Thẳng:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->south_straight_win ?? 'N/A' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        
                        <!-- Tỷ lệ Miền Bắc -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h3 class="font-medium text-gray-700 mb-3">Miền Bắc</h3>
                            <dl class="space-y-2">
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Đề:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->north_head_tail_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Lô:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->north_lo_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">3 Càng:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->north_3_digits_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">4 Càng:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->north_4_digits_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Đá:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->north_slide_win ?? 'N/A' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        
                        <!-- Tỷ lệ Miền Trung -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h3 class="font-medium text-gray-700 mb-3">Miền Trung</h3>
                            <dl class="space-y-2">
                            <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Đề:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->central_head_tail_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Lô:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->central_lo_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">3 Càng:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->central_3_digits_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">4 Càng:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->central_4_digits_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Đá Xiên:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->central_slide_win ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="grid grid-cols-3">
                                    <dt class="text-sm text-gray-500 col-span-2">Đá Thẳng:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $customer->setting->central_straight_win ?? 'N/A' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Xiên Miền Bắc -->
                    <div class="mt-6">
                        <h3 class="font-medium text-gray-700 mb-3">Xiên Miền Bắc</h3>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-3">
                                            <dt class="text-sm text-gray-500 col-span-2">Xiên 2:</dt>
                                            <dd class="text-sm text-gray-900">
                                                {{ $customer->setting->north_slide2_win ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="grid grid-cols-3">
                                            <dt class="text-sm text-gray-500 col-span-2">Xiên 3:</dt>
                                            <dd class="text-sm text-gray-900">
                                                {{ $customer->setting->north_slide3_win ?? 'N/A' }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                                <div>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-3">
                                            <dt class="text-sm text-gray-500 col-span-2">Xiên 4:</dt>
                                            <dd class="text-sm text-gray-900">
                                                {{ $customer->setting->north_slide4_win ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="grid grid-cols-3">
                                            <dt class="text-sm text-gray-500 col-span-2">Xiên 5:</dt>
                                            <dd class="text-sm text-gray-900">
                                                {{ $customer->setting->north_slide5_win ?? 'N/A' }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                                <div>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-3">
                                            <dt class="text-sm text-gray-500 col-span-2">Xiên 6:</dt>
                                            <dd class="text-sm text-gray-900">
                                                {{ $customer->setting->north_slide6_win ?? 'N/A' }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cách thức tính tiền -->
                    <div class="mt-6">
                        <h3 class="font-medium text-gray-700 mb-3">Cách thức tính tiền thắng</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Miền Nam -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4 class="font-medium text-gray-700 mb-2">Miền Nam</h4>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm text-gray-500">Đá Thẳng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($customer->setting->south_straight_win_type == 1)
                                                Một lần
                                            @elseif($customer->setting->south_straight_win_type == 2)
                                                Ky rưỡi
                                            @elseif($customer->setting->south_straight_win_type == 3)
                                                Nhiều cặp
                                            @else
                                                N/A
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Đá Xiên:</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($customer->setting->south_slide_win_type == 1)
                                                Một lần
                                            @elseif($customer->setting->south_slide_win_type == 2)
                                                Ky rưỡi
                                            @elseif($customer->setting->south_slide_win_type == 3)
                                                Nhiều cặp
                                            @else
                                                N/A
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Thưởng Đá Thẳng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->south_straight_bonus ? 'Có' : 'Không' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <!-- Miền Bắc -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4 class="font-medium text-gray-700 mb-2">Miền Bắc</h4>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm text-gray-500">Đá:</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($customer->setting->north_slide_win_type == 1)
                                                Một lần
                                            @elseif($customer->setting->north_slide_win_type == 2)
                                                Ky rưỡi
                                            @elseif($customer->setting->north_slide_win_type == 3)
                                                Nhiều cặp
                                            @else
                                                N/A
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Thưởng Đá Thẳng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->north_straight_bonus ? 'Có' : 'Không' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <!-- Miền Trung -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4 class="font-medium text-gray-700 mb-2">Miền Trung</h4>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm text-gray-500">Đá Thẳng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($customer->setting->central_straight_win_type == 1)
                                                Một lần
                                            @elseif($customer->setting->central_straight_win_type == 2)
                                                Ky rưỡi
                                            @elseif($customer->setting->central_straight_win_type == 3)
                                                Nhiều cặp
                                            @else
                                                N/A
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Đá Xiên:</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($customer->setting->central_slide_win_type == 1)
                                                Một lần
                                            @elseif($customer->setting->central_slide_win_type == 2)
                                                Ky rưỡi
                                            @elseif($customer->setting->central_slide_win_type == 3)
                                                Nhiều cặp
                                            @else
                                                N/A
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Thưởng Đá Thẳng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->central_straight_bonus ? 'Có' : 'Không' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Giá cò -->
                    <div class="mt-6">
                        <h3 class="font-medium text-gray-700 mb-3">Giá cò</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Miền Nam -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4 class="font-medium text-gray-700 mb-2">Miền Nam</h4>
                                <dl class="space-y-2">
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Đề:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->south_head_tail_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Lô:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->south_lo_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">3 Càng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->south_3_digits_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">4 Càng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->south_4_digits_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Đá Xiên:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->south_slide_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Đá Thẳng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->south_straight_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <!-- Miền Bắc -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4 class="font-medium text-gray-700 mb-2">Miền Bắc</h4>
                                <dl class="space-y-2">
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Đề:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->north_head_tail_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Lô:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->north_lo_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">3 Càng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->north_3_digits_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">4 Càng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->north_4_digits_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Đá:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->north_slide_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <!-- Miền Trung -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4 class="font-medium text-gray-700 mb-2">Miền Trung</h4>
                                <dl class="space-y-2">
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Đề:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->central_head_tail_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Lô:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->central_lo_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">3 Càng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->central_3_digits_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">4 Càng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->central_4_digits_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Đá Xiên:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->central_slide_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500 col-span-2">Đá Thẳng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $customer->setting->central_straight_rate ?? 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cashback -->
                    <div class="mt-6">
                        <h3 class="font-medium text-gray-700 mb-3">Tỷ lệ cashback</h3>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-3">
                                            <dt class="text-sm text-gray-500 col-span-2">Hồi cả ngày:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->cashback_all ?? 0 }}%</dd>
                                        </div>
                                    </dl>
                                </div>
                                <div>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-3">
                                            <dt class="text-sm text-gray-500 col-span-2">Hồi Miền Nam:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->cashback_south ?? 0 }}%</dd>
                                        </div>
                                    </dl>
                                </div>
                                <div>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-3">
                                            <dt class="text-sm text-gray-500 col-span-2">Hồi Miền Bắc:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->cashback_north ?? 0 }}%</dd>
                                        </div>
                                    </dl>
                                </div>
                                <div>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-3">
                                            <dt class="text-sm text-gray-500 col-span-2">Hồi Miền Trung:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->cashback_central ?? 0 }}%</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Chỉnh sửa cài đặt
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div id="settings-tab" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-800">Cài đặt tỷ lệ cược</h2>
                </div>
                <div class="p-6">
                    <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mb-6">
                        Chỉnh sửa cài đặt
                    </a>
                    
                    <!-- Hiển thị cài đặt chung -->
                    <div class="mb-6">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Cài đặt chung</h3>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Chạy Số (Chủ/Khách)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->setting->is_sync_enabled ? 'Có' : 'Không' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">% Hồi Cả Ngày</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->setting->cashback_all }}%</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">% Hồi Miền Nam</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->setting->cashback_south }}%</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">% Hồi Miền Bắc</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->setting->cashback_north }}%</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">% Hồi Miền Trung</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $customer->setting->cashback_central }}%</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Hiển thị cài đặt miền Nam -->
                    <div class="mb-6">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Cài đặt Miền Nam</h3>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Giá đánh</h4>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">Đầu - Đuôi:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_head_tail_rate }}</dd>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">Lô:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_lo_rate }}</dd>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">3 Con:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_3_digits_rate }}</dd>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">4 Con:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_4_digits_rate }}</dd>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">Đá Xiên:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_slide_rate }}</dd>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">Đá Thẳng:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_straight_rate }}</dd>
                                        </div>
                                    </dl>
                                </div>
                                
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Giá trúng</h4>
                                    <dl class="space-y-2">
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">Đầu - Đuôi:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_head_tail_win }}</dd>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">Lô:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_lo_win }}</dd>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">3 Con:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_3_digits_win }}</dd>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">4 Con:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_4_digits_win }}</dd>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">Đá Xiên:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_slide_win }}</dd>
                                        </div>
                                        <div class="grid grid-cols-2">
                                            <dt class="text-sm text-gray-500">Đá Thẳng:</dt>
                                            <dd class="text-sm text-gray-900">{{ $customer->setting->south_straight_win }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <dl class="space-y-2">
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500">Thưởng Đá Thẳng:</dt>
                                        <dd class="text-sm text-gray-900">{{ $customer->setting->south_straight_bonus ? 'Có' : 'Không' }}</dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500">Cách trúng đá thẳng:</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($customer->setting->south_straight_win_type == 1)
                                                Một lần
                                            @elseif($customer->setting->south_straight_win_type == 2)
                                                Ky rưỡi
                                            @else
                                                Nhiều cặp
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-3">
                                        <dt class="text-sm text-gray-500">Cách trúng đá xiên:</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($customer->setting->south_slide_win_type == 1)
                                                Một lần
                                            @elseif($customer->setting->south_slide_win_type == 2)
                                                Ky rưỡi
                                            @else
                                                Nhiều cặp
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hiển thị cài đặt miền Bắc - tương tự miền Nam -->
                    <!-- Hiển thị cài đặt miền Trung - tương tự miền Nam -->
                </div>
            </div>
        </div>
    </div>
    <!-- Thêm script JavaScript để xử lý tab -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-link');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Ẩn tất cả tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Bỏ active cho tất cả tab link
            tabLinks.forEach(l => {
                l.classList.remove('active', 'border-blue-500', 'text-blue-600');
                l.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Hiển thị tab content được chọn
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).classList.remove('hidden');
            
            // Kích hoạt tab link
            this.classList.add('active', 'border-blue-500', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-gray-500');
        });
    });
    
    // Xử lý hash trong URL
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const tabLink = document.querySelector(`.tab-link[href="#${hash}"]`);
        
        if (tabLink) {
            tabLink.click();
        }
    }
});
</script>
@endsection