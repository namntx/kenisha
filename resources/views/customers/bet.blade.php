@extends('layouts.app')

@section('content')
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Đặt cược cho khách hàng: {{ $customer->name }}</h1>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Số dư:</span>
                <span class="text-lg font-medium text-blue-600">{{ number_format($customer->balance, 0, ',', '.') }} đ</span>
            </div>
        </div>
        
        <form action="{{ route('customers.bet.store', $customer) }}" method="POST">
            @csrf
            
            <div class="space-y-6">
                <!-- Ngày đặt cược -->
                <div>
                    <label for="bet_date" class="block text-sm font-medium text-gray-700">Ngày đặt cược</label>
                    <input type="date" id="bet_date" name="bet_date" value="{{ date('Y-m-d') }}" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                
                <!-- Tỉnh đài mở thưởng hôm nay -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tỉnh đài mở thưởng hôm nay:</label>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex flex-wrap gap-2" id="province_list">
                            <div class="inline-flex items-center">
                                <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="ml-2 text-sm text-gray-500">Đang tải...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cú pháp đặt cược -->
                <div>
                    <label for="bet_string" class="block text-sm font-medium text-gray-700">Nhập cú pháp đặt cược</label>
                    <input type="text" id="bet_string" name="bet_string" 
                        placeholder="Ví dụ: 78 de hcm 100k" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <p class="mt-1 text-sm text-gray-500">
                        Cú pháp: [số] [kiểu đánh] [tỉnh/khu vực] [tiền cược]
                    </p>
                </div>
                
                <!-- Xem trước đặt cược -->
                <div id="bet_preview" class="hidden mt-6 bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Xem trước đặt cược</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Số đánh</dt>
                                <dd class="mt-1 text-sm text-gray-900" id="preview_numbers"></dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Kiểu đánh</dt>
                                <dd class="mt-1 text-sm text-gray-900" id="preview_type"></dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Tỉnh/Khu vực</dt>
                                <dd class="mt-1 text-sm text-gray-900" id="preview_province"></dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Tiền đặt</dt>
                                <dd class="mt-1 text-sm text-gray-900" id="preview_amount"></dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Tiền thắng tiềm năng</dt>
                                <dd class="mt-1 text-sm text-green-600 font-semibold" id="preview_potential"></dd>
                            </div>
                        </dl>
                    </div>
                </div>
                
                <!-- Thông báo lỗi -->
                <div id="error_message" class="hidden px-4 py-3 rounded-md bg-red-50 text-red-700 border border-red-200"></div>
                
                <!-- Nút submit -->
                <div class="flex justify-end">
                    <a href="{{ route('customers.show', $customer) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-3">
                        Quay lại
                    </a>
                    <button type="submit" id="submit_button" disabled
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                        Đặt cược
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const betDateInput = document.getElementById('bet_date');
        const betStringInput = document.getElementById('bet_string');
        const submitButton = document.getElementById('submit_button');
        const betPreview = document.getElementById('bet_preview');
        const errorMessage = document.getElementById('error_message');
        
        // Tải danh sách tỉnh đài cho ngày hiện tại
        loadProvinces(betDateInput.value);
        
        // Khi ngày thay đổi, tải lại danh sách tỉnh
        betDateInput.addEventListener('change', function() {
            loadProvinces(this.value);
            
            // Xóa cú pháp đặt cược hiện tại vì có thể không còn hợp lệ
            betStringInput.value = '';
            betPreview.classList.add('hidden');
            errorMessage.classList.add('hidden');
            submitButton.disabled = true;
        });
        
        // Khi cú pháp đặt cược thay đổi, phân tích và hiển thị xem trước
        betStringInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                betPreview.classList.add('hidden');
                errorMessage.classList.add('hidden');
                submitButton.disabled = true;
                return;
            }
            
            parseBetString(this.value, betDateInput.value);
        });
        
        // Hàm tải danh sách tỉnh đài theo ngày
        function loadProvinces(date) {
            const provinceList = document.getElementById('province_list');
            provinceList.innerHTML = `
                <div class="inline-flex items-center">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2 text-sm text-gray-500">Đang tải...</span>
                </div>
            `;
            
            fetch(`{{ route('provinces.by-date') }}?date=${date}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(response => response.json())
                .then(data => {
                    provinceList.innerHTML = '';
                    
                    // Nhóm tỉnh theo khu vực
                    const regionMap = {
                        1: 'Miền Bắc',
                        2: 'Miền Trung',
                        3: 'Miền Nam'
                    };
                    
                    const provincesGrouped = {};
                    
                    data.forEach(province => {
                        if (!provincesGrouped[province.region_id]) {
                            provincesGrouped[province.region_id] = [];
                        }
                        provincesGrouped[province.region_id].push(province);
                    });
                    
                    // Hiển thị các tỉnh theo nhóm
                    for (const regionId in provincesGrouped) {
                        const regionDiv = document.createElement('div');
                        regionDiv.className = 'w-full mb-2';
                        regionDiv.innerHTML = `<strong class="text-sm text-gray-700">${regionMap[regionId]}:</strong>`;
                        provinceList.appendChild(regionDiv);
                        
                        const badgesDiv = document.createElement('div');
                        badgesDiv.className = 'flex flex-wrap gap-2 mt-1 mb-3';
                        
                        provincesGrouped[regionId].forEach(province => {
                            const badge = document.createElement('span');
                            badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
                            badge.textContent = `${province.name} (${province.code})`;
                            badgesDiv.appendChild(badge);
                        });
                        
                        provinceList.appendChild(badgesDiv);
                    }
                    
                    if (Object.keys(provincesGrouped).length === 0) {
                        provinceList.innerHTML = '<p class="text-sm text-gray-500">Không có tỉnh đài nào mở thưởng vào ngày này</p>';
                    }
                })
                .catch(error => {
                    provinceList.innerHTML = '<p class="text-sm text-red-500">Không thể tải danh sách tỉnh đài</p>';
                    console.error('Error:', error);
                });
        }
        
        // Hàm phân tích cú pháp đặt cược
        function parseBetString(betString, betDate) {
            fetch('{{ route('bets.parse') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    bet_string: betString,
                    bet_date: betDate
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.is_valid) {
                    // Hiển thị xem trước đặt cược
                    document.getElementById('preview_numbers').textContent = data.numbers.join(', ');
                    document.getElementById('preview_type').textContent = data.type;
                    
                    // Hiển thị tỉnh hoặc khu vực tùy thuộc vào dữ liệu
                    if (data.province) {
                        document.getElementById('preview_province').textContent = data.province;
                    } else {
                        document.getElementById('preview_province').textContent = data.region;
                    }
                    
                    document.getElementById('preview_amount').textContent = new Intl.NumberFormat('vi-VN', {
                        style: 'currency',
                        currency: 'VND'
                    }).format(data.amount);
                    
                    document.getElementById('preview_potential').textContent = new Intl.NumberFormat('vi-VN', {
                        style: 'currency',
                        currency: 'VND'
                    }).format(data.potential_win);
                    
                    betPreview.classList.remove('hidden');
                    errorMessage.classList.add('hidden');
                    submitButton.disabled = false;
                } else {
                    // Hiển thị thông báo lỗi
                    errorMessage.textContent = data.error;
                    errorMessage.classList.remove('hidden');
                    betPreview.classList.add('hidden');
                    submitButton.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorMessage.textContent = 'Đã xảy ra lỗi khi phân tích cú pháp đặt cược';
                errorMessage.classList.remove('hidden');
                betPreview.classList.add('hidden');
                submitButton.disabled = true;
            });
        }
    });
    </script>
@endsection