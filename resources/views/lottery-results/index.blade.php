@extends('layouts.app')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Kết quả xổ số</h1>
        <div class="flex space-x-3">
            <button id="fetchResultsBtn" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Lấy kết quả hôm nay
            </button>
            <a href="{{ route('lottery-results.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Thêm kết quả mới
            </a>
        </div>
    </div>
    
    <!-- Thông báo -->
    <div id="apiMessage" class="mb-4 hidden">
    </div>
    
    <!-- Modal lấy kết quả -->
    <div id="fetchResultsModal" class="fixed inset-0 overflow-y-auto hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Lấy kết quả xổ số
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Chọn vùng miền và ngày cần lấy kết quả.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="fetch_date" class="block text-sm font-medium text-gray-700">Ngày</label>
                            <input type="date" id="fetch_date" name="fetch_date" value="{{ date('Y-m-d') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="fetch_region" class="block text-sm font-medium text-gray-700">Vùng miền</label>
                            <select id="fetch_region" name="fetch_region" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="all">Tất cả</option>
                                <option value="mb">Miền Bắc</option>
                                <option value="mt">Miền Trung</option>
                                <option value="mn">Miền Nam</option>
                            </select>
                        </div>
                        
                        <div id="provinceSelector" class="hidden">
                            <label for="fetch_province" class="block text-sm font-medium text-gray-700">Tỉnh/Thành phố</label>
                            <select id="fetch_province" name="fetch_province" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Chọn tỉnh/thành phố</option>
                            </select>
                        </div>
                        
                        <div class="flex items-center">
                            <input id="auto_process" name="auto_process" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="auto_process" class="ml-2 block text-sm text-gray-700">
                                Tự động xử lý kết quả
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="button" id="fetchBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                        Lấy kết quả
                    </button>
                    <button type="button" id="cancelFetchBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Kết quả đang tải -->
    <div id="loadingResults" class="hidden mt-4 p-4 rounded-md bg-blue-50 border border-blue-200">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="animate-spin h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Đang lấy kết quả xổ số...</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>Vui lòng đợi trong khi hệ thống lấy kết quả từ nguồn bên ngoài.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bộ lọc -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form action="{{ route('lottery-results.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Ngày</label>
                <input type="date" id="date" name="date" value="{{ request('date') }}" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div>
                <label for="region_id" class="block text-sm font-medium text-gray-700 mb-1">Miền</label>
                <select id="region_id" name="region_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">Tất cả</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                            {{ $region->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="province_id" class="block text-sm font-medium text-gray-700 mb-1">Tỉnh/Thành phố</label>
                <select id="province_id" name="province_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">Tất cả</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>
                            {{ $province->name }}
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
    
    <!-- Danh sách kết quả -->
    <div class="bg-white shadow overflow-hidden rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Miền</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tỉnh/Thành phố</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($results as $result)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $result->draw_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $result->region->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $result->province ? $result->province->name : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($result->is_processed)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Đã xử lý
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Chưa xử lý
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-3">
                                    <a href="{{ route('lottery-results.show', $result) }}" class="text-blue-600 hover:text-blue-900">Chi tiết</a>
                                    
                                    @if(!$result->is_processed)
                                        <a href="{{ route('lottery-results.edit', $result) }}" class="text-indigo-600 hover:text-indigo-900">Sửa</a>
                                        
                                        <form action="{{ route('lottery-results.process', $result) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Bạn có chắc muốn xử lý kết quả này?')">
                                                Xử lý
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('lottery-results.destroy', $result) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Bạn có chắc muốn xóa kết quả này?')">
                                                Xóa
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($results->isEmpty())
            <div class="p-6 text-center text-gray-500">
                Không có kết quả xổ số nào
            </div>
        @endif
        
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
            {{ $results->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fetchResultsBtn = document.getElementById('fetchResultsBtn');
        const fetchResultsModal = document.getElementById('fetchResultsModal');
        const cancelFetchBtn = document.getElementById('cancelFetchBtn');
        const fetchBtn = document.getElementById('fetchBtn');
        const fetchRegion = document.getElementById('fetch_region');
        const provinceSelector = document.getElementById('provinceSelector');
        const fetchProvince = document.getElementById('fetch_province');
        const autoProcess = document.getElementById('auto_process');
        const loadingResults = document.getElementById('loadingResults');
        const apiMessage = document.getElementById('apiMessage');
        
        // Hiển thị modal lấy kết quả
        fetchResultsBtn.addEventListener('click', function() {
            fetchResultsModal.classList.remove('hidden');
        });
        
        // Đóng modal
        cancelFetchBtn.addEventListener('click', function() {
            fetchResultsModal.classList.add('hidden');
        });
        
        // Hiển thị/ẩn selector tỉnh tùy theo vùng miền
        fetchRegion.addEventListener('change', function() {
            if (this.value === 'all') {
                provinceSelector.classList.add('hidden');
            } else {
                // Lấy danh sách tỉnh theo vùng miền
                fetchProvincesByRegion(this.value);
                provinceSelector.classList.remove('hidden');
            }
        });
        
        // Lấy danh sách tỉnh theo vùng miền
        function fetchProvincesByRegion(region) {
            // Xóa các option cũ
            fetchProvince.innerHTML = '<option value="">Chọn tỉnh/thành phố</option>';
            
            // Gọi API để lấy danh sách tỉnh
            fetch(`/api/provinces?region=${region}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.code;
                        option.textContent = province.name;
                        fetchProvince.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching provinces:', error);
                });
        }
        
        // Lấy kết quả
        fetchBtn.addEventListener('click', function() {
            const date = document.getElementById('fetch_date').value;
            const region = fetchRegion.value;
            const province = fetchProvince.value;
            const process = autoProcess.checked;
            
            // Hiển thị loading
            loadingResults.classList.remove('hidden');
            
            // Ẩn modal
            fetchResultsModal.classList.add('hidden');
            
            // Gọi API để lấy kết quả
            let apiUrl = '';
            if (region === 'all') {
                apiUrl = `/api/lottery/fetch-all?date=${date}&auto_process=${process}`;
            } else if (province) {
                apiUrl = `/api/lottery/fetch?date=${date}&region=${region}&province=${province}&auto_process=${process}`;
            } else {
                apiUrl = `/api/lottery/fetch?date=${date}&region=${region}&auto_process=${process}`;
            }
            
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    // Ẩn loading
                    loadingResults.classList.add('hidden');
                    
                    // Hiển thị thông báo
                    let messageClass = '';
                    if (data.status === 'success') {
                        messageClass = 'bg-green-50 text-green-700 border-green-200';
                    } else if (data.status === 'partial') {
                        messageClass = 'bg-yellow-50 text-yellow-700 border-yellow-200';
                    } else {
                        messageClass = 'bg-red-50 text-red-700 border-red-200';
                    }
                    
                    apiMessage.className = `mb-4 p-4 rounded-md ${messageClass}`;
                    apiMessage.innerHTML = `<p>${data.message || 'Hoàn thành lấy kết quả xổ số'}</p>`;
                    apiMessage.classList.remove('hidden');
                    
                    // Tải lại trang sau 3 giây
                    setTimeout(function() {
                        window.location.reload();
                    }, 3000);
                })
                .catch(error => {
                    // Ẩn loading
                    loadingResults.classList.add('hidden');
                    
                    // Hiển thị thông báo lỗi
                    apiMessage.className = 'mb-4 p-4 rounded-md bg-red-50 text-red-700 border border-red-200';
                    apiMessage.innerHTML = `<p>Đã xảy ra lỗi: ${error.message}</p>`;
                    apiMessage.classList.remove('hidden');
                });
        });
    });
</script>
@endpush
@endsection