@extends('layouts.app')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
            Chi tiết kết quả xổ số - {{ $lotteryResult->locationName }} - {{ $lotteryResult->draw_date->format('d/m/Y') }}
        </h1>
        <div class="flex space-x-3">
            <a href="{{ route('lottery-results.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Quay lại
            </a>
            @if(!$lotteryResult->is_processed)
            <button id="processResultBtn" data-id="{{ $lotteryResult->id }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Xử lý kết quả
            </button>
            @endif
            <a href="{{ route('lottery-results.edit', $lotteryResult) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Chỉnh sửa
            </a>
        </div>
    </div>
    
    <!-- Thông báo -->
    <div id="apiMessage" class="mb-4 hidden">
    </div>
    
    <!-- Thông tin kết quả -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Thông tin kết quả
            </h3>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">
                        Vùng miền
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $lotteryResult->region->name }}
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">
                        Tỉnh/Thành
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $lotteryResult->province ? $lotteryResult->province->name : 'Không áp dụng' }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">
                        Ngày mở thưởng
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $lotteryResult->draw_date->format('d/m/Y') }}
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">
                        Trạng thái xử lý
                    </dt>
                    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                        @if($lotteryResult->is_processed)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Đã xử lý
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Chưa xử lý
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
    
    <!-- Bảng kết quả -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Kết quả chi tiết
            </h3>
        </div>
        
        @if($lotteryResult->region->code === 'mb')
            <!-- Kết quả Miền Bắc -->
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Giải Đặc biệt -->
                    <div class="bg-red-50 p-3 rounded-md">
                        <h4 class="text-red-800 font-bold mb-2">Giải Đặc biệt</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-white p-2 rounded border border-red-200 text-center text-xl font-bold text-red-800">
                                {{ $lotteryResult->results['DB'] ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Giải Nhất -->
                    <div class="bg-orange-50 p-3 rounded-md">
                        <h4 class="text-orange-800 font-bold mb-2">Giải Nhất</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-white p-2 rounded border border-orange-200 text-center text-lg font-bold">
                                {{ $lotteryResult->results['G1'] ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Giải Nhì -->
                    <div class="bg-yellow-50 p-3 rounded-md">
                        <h4 class="text-yellow-800 font-bold mb-2">Giải Nhì</h4>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($lotteryResult->results['G2'] ?? [] as $number)
                                <div class="bg-white p-2 rounded border border-yellow-200 text-center font-bold">
                                    {{ $number }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Giải Ba -->
                    <div class="bg-green-50 p-3 rounded-md">
                        <h4 class="text-green-800 font-bold mb-2">Giải Ba</h4>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($lotteryResult->results['G3'] ?? [] as $number)
                                <div class="bg-white p-2 rounded border border-green-200 text-center font-bold">
                                    {{ $number }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Giải Tư -->
                    <div class="bg-blue-50 p-3 rounded-md">
                        <h4 class="text-blue-800 font-bold mb-2">Giải Tư</h4>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($lotteryResult->results['G4'] ?? [] as $number)
                                <div class="bg-white p-2 rounded border border-blue-200 text-center font-bold">
                                    {{ $number }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Giải Năm -->
                    <div class="bg-indigo-50 p-3 rounded-md">
                        <h4 class="text-indigo-800 font-bold mb-2">Giải Năm</h4>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($lotteryResult->results['G5'] ?? [] as $number)
                                <div class="bg-white p-2 rounded border border-indigo-200 text-center font-bold">
                                    {{ $number }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Giải Sáu -->
                    <div class="bg-purple-50 p-3 rounded-md">
                        <h4 class="text-purple-800 font-bold mb-2">Giải Sáu</h4>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($lotteryResult->results['G6'] ?? [] as $number)
                                <div class="bg-white p-2 rounded border border-purple-200 text-center font-bold">
                                    {{ $number }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Giải Bảy -->
                    <div class="bg-pink-50 p-3 rounded-md">
                        <h4 class="text-pink-800 font-bold mb-2">Giải Bảy</h4>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach($lotteryResult->results['G7'] ?? [] as $number)
                                <div class="bg-white p-2 rounded border border-pink-200 text-center font-bold">
                                    {{ $number }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Kết quả Miền Nam/Miền Trung -->
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-4">
                    <!-- Giải Tám -->
                    <div class="bg-gray-50 p-3 rounded-md">
                        <h4 class="text-gray-800 font-bold mb-2">Giải Tám</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-white p-2 rounded border border-gray-200 text-center font-bold">
                                {{ $lotteryResult->results['G8'] ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Giải Bảy -->
                    <div class="bg-pink-50 p-3 rounded-md">
                        <h4 class="text-pink-800 font-bold mb-2">Giải Bảy</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-white p-2 rounded border border-pink-200 text-center font-bold">
                                {{ $lotteryResult->results['G7'] ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Giải Sáu -->
                    <div class="bg-purple-50 p-3 rounded-md">
                        <h4 class="text-purple-800 font-bold mb-2">Giải Sáu</h4>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($lotteryResult->results['G6'] ?? [] as $number)
                                <div class="bg-white p-2 rounded border border-purple-200 text-center font-bold">
                                    {{ $number }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Giải Năm -->
                    <div class="bg-indigo-50 p-3 rounded-md">
                        <h4 class="text-indigo-800 font-bold mb-2">Giải Năm</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-white p-2 rounded border border-indigo-200 text-center font-bold">
                                {{ $lotteryResult->results['G5'] ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Giải Tư -->
                    <div class="bg-blue-50 p-3 rounded-md">
                        <h4 class="text-blue-800 font-bold mb-2">Giải Tư</h4>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($lotteryResult->results['G4'] ?? [] as $number)
                                <div class="bg-white p-2 rounded border border-blue-200 text-center font-bold">
                                    {{ $number }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Giải Ba -->
                    <div class="bg-green-50 p-3 rounded-md">
                        <h4 class="text-green-800 font-bold mb-2">Giải Ba</h4>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($lotteryResult->results['G3'] ?? [] as $number)
                                <div class="bg-white p-2 rounded border border-green-200 text-center font-bold">
                                    {{ $number }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Giải Nhì -->
                    <div class="bg-yellow-50 p-3 rounded-md">
                        <h4 class="text-yellow-800 font-bold mb-2">Giải Nhì</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-white p-2 rounded border border-yellow-200 text-center font-bold">
                                {{ $lotteryResult->results['G2'] ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Giải Nhất -->
                    <div class="bg-orange-50 p-3 rounded-md">
                        <h4 class="text-orange-800 font-bold mb-2">Giải Nhất</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-white p-2 rounded border border-orange-200 text-center font-bold">
                                {{ $lotteryResult->results['G1'] ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Giải Đặc biệt -->
                    <div class="bg-red-50 p-3 rounded-md">
                        <h4 class="text-red-800 font-bold mb-2">Giải Đặc biệt</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-white p-2 rounded border border-red-200 text-center text-xl font-bold text-red-800">
                                {{ $lotteryResult->results['DB'] ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const processResultBtn = document.getElementById('processResultBtn');
        const apiMessage = document.getElementById('apiMessage');
        
        if (processResultBtn) {
            processResultBtn.addEventListener('click', function() {
                const resultId = this.getAttribute('data-id');
                
                // Hiển thị thông báo đang xử lý
                apiMessage.className = 'mb-4 p-4 rounded-md bg-blue-50 text-blue-700 border border-blue-200';
                apiMessage.innerHTML = '<p>Đang xử lý kết quả...</p>';
                apiMessage.classList.remove('hidden');
                
                // Gửi yêu cầu xử lý
                fetch(`/lottery-results/${resultId}/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Hiển thị thông báo thành công
                        apiMessage.className = 'mb-4 p-4 rounded-md bg-green-50 text-green-700 border border-green-200';
                        apiMessage.innerHTML = `<p>${data.message}</p>`;
                        
                        // Cập nhật trạng thái trang
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Hiển thị thông báo lỗi
                        apiMessage.className = 'mb-4 p-4 rounded-md bg-red-50 text-red-700 border border-red-200';
                        apiMessage.innerHTML = `<p>Đã xảy ra lỗi: ${data.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error(error);
                    // Hiển thị thông báo lỗi
                    apiMessage.className = 'mb-4 p-4 rounded-md bg-red-50 text-red-700 border border-red-200';
                    apiMessage.innerHTML = `<p>Đã xảy ra lỗi: ${error.message}</p>`;
                });
            });
        }
    });
</script>
@endpush
@endsection
