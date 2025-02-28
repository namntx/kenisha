@extends('layouts.app')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Kết quả xổ số</h1>
        <a href="{{ route('lottery-results.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Thêm kết quả mới
        </a>
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
@endsection