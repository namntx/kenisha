@extends('layouts.app')

@section('content')
<div>
    <div class="flex items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Chỉnh sửa khách hàng: {{ $customer->name }}</h1>
    </div>
    
    <div class="bg-white rounded-lg shadow-lg">
        <form action="{{ route('customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800 mb-4">Thông tin cơ bản</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tên khách hàng -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Tên khách hàng</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $customer->name) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $customer->email) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Số điện thoại -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Ghi chú -->
                    <div>
                        <label for="note" class="block text-sm font-medium text-gray-700">Ghi chú</label>
                        <input type="text" id="note" name="note" value="{{ old('note', $customer->settings['note'] ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Mật khẩu -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu mới (để trống nếu không thay đổi)</label>
                        <input type="password" id="password" name="password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Xác nhận mật khẩu -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Xác nhận mật khẩu mới</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                </div>
            </div>
            
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800 mb-4">Cài đặt chung</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Chạy số (Chủ/Khách) -->
                    <div>
                        <div class="flex items-center">
                            <input id="is_sync_enabled" name="is_sync_enabled" type="checkbox" 
                                {{ old('is_sync_enabled', $customer->setting->is_sync_enabled ?? false) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="is_sync_enabled" class="ml-2 block text-sm text-gray-700">
                                Chạy Số (Chủ/Khách)
                            </label>
                        </div>
                    </div>
                    
                    <!-- % Hồi Cả Ngày -->
                    <div>
                        <label for="cashback_all" class="block text-sm font-medium text-gray-700">% Hồi Cả Ngày</label>
                        <input type="text" id="cashback_all" name="cashback_all" 
                            value="{{ old('cashback_all', $customer->setting->cashback_all ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- % Hồi Miền Nam -->
                    <div>
                        <label for="cashback_south" class="block text-sm font-medium text-gray-700">% Hồi Miền Nam</label>
                        <input type="text" id="cashback_south" name="cashback_south" 
                            value="{{ old('cashback_south', $customer->setting->cashback_south ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- % Hồi Miền Bắc -->
                    <div>
                        <label for="cashback_north" class="block text-sm font-medium text-gray-700">% Hồi Miền Bắc</label>
                        <input type="text" id="cashback_north" name="cashback_north" 
                            value="{{ old('cashback_north', $customer->setting->cashback_north ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- % Hồi Miền Trung -->
                    <div>
                        <label for="cashback_central" class="block text-sm font-medium text-gray-700">% Hồi Miền Trung</label>
                        <input type="text" id="cashback_central" name="cashback_central" 
                            value="{{ old('cashback_central', $customer->setting->cashback_central ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                </div>
            </div>
            
            <!-- Cài đặt Miền Nam -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800 mb-4">Cài đặt Miền Nam</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Giá cò 2 Con Đầu - Đuôi MN -->
                    <div>
                        <label for="south_head_tail_rate" class="block text-sm font-medium text-gray-700">Giá cò 2 Con Đầu - Đuôi MN</label>
                        <input type="text" id="south_head_tail_rate" name="south_head_tail_rate" 
                            value="{{ old('south_head_tail_rate', $customer->setting->south_head_tail_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá Cò 2 Con lô MN -->
                    <div>
                        <label for="south_lo_rate" class="block text-sm font-medium text-gray-700">Giá Cò 2 Con lô MN</label>
                        <input type="text" id="south_lo_rate" name="south_lo_rate" 
                            value="{{ old('south_lo_rate', $customer->setting->south_lo_rate ?? 21.9) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá cò 3 Con MN -->
                    <div>
                        <label for="south_3_digits_rate" class="block text-sm font-medium text-gray-700">Giá cò 3 Con MN</label>
                        <input type="text" id="south_3_digits_rate" name="south_3_digits_rate" 
                            value="{{ old('south_3_digits_rate', $customer->setting->south_3_digits_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá cò Xỉu Chủ MN -->
                    <div>
                        <label for="south_3_head_tail_rate" class="block text-sm font-medium text-gray-700">Giá cò Xỉu Chủ MN (3 con đầu đuôi)</label>
                        <input type="text" id="south_3_head_tail_rate" name="south_3_head_tail_rate" 
                            value="{{ old('south_3_head_tail_rate', $customer->setting->south_3_head_tail_rate ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá cò 4 Con MN -->
                    <div>
                        <label for="south_4_digits_rate" class="block text-sm font-medium text-gray-700">Giá cò 4 Con MN</label>
                        <input type="text" id="south_4_digits_rate" name="south_4_digits_rate" 
                            value="{{ old('south_4_digits_rate', $customer->setting->south_4_digits_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá Cò Ðá Xiên MN -->
                    <div>
                        <label for="south_slide_rate" class="block text-sm font-medium text-gray-700">Giá Cò Ðá Xiên MN</label>
                        <input type="text" id="south_slide_rate" name="south_slide_rate" 
                            value="{{ old('south_slide_rate', $customer->setting->south_slide_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá Cò Đá Thẳng MN -->
                    <div>
                        <label for="south_straight_rate" class="block text-sm font-medium text-gray-700">Giá Cò Đá Thẳng MN</label>
                        <input type="text" id="south_straight_rate" name="south_straight_rate" 
                            value="{{ old('south_straight_rate', $customer->setting->south_straight_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 2 Con Đầu - Đuôi MN -->
                    <div>
                        <label for="south_head_tail_win" class="block text-sm font-medium text-gray-700">Trúng 2 Con Đầu - Đuôi MN</label>
                        <input type="text" id="south_head_tail_win" name="south_head_tail_win" 
                            value="{{ old('south_head_tail_win', $customer->setting->south_head_tail_win ?? 75) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 2 Con lô MN -->
                    <div>
                        <label for="south_lo_win" class="block text-sm font-medium text-gray-700">Trúng 2 Con lô MN</label>
                        <input type="text" id="south_lo_win" name="south_lo_win" 
                            value="{{ old('south_lo_win', $customer->setting->south_lo_win ?? 75) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 3 Con MN -->
                    <div>
                        <label for="south_3_digits_win" class="block text-sm font-medium text-gray-700">Trúng 3 Con MN</label>
                        <input type="text" id="south_3_digits_win" name="south_3_digits_win" 
                            value="{{ old('south_3_digits_win', $customer->setting->south_3_digits_win ?? 650) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng Xỉu Chủ MN -->
                    <div>
                        <label for="south_3_head_tail_win" class="block text-sm font-medium text-gray-700">Trúng Xỉu Chủ MN (3 con đầu đuôi)</label>
                        <input type="text" id="south_3_head_tail_win" name="south_3_head_tail_win" 
                            value="{{ old('south_3_head_tail_win', $customer->setting->south_3_head_tail_win ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 4 Con MN -->
                    <div>
                        <label for="south_4_digits_win" class="block text-sm font-medium text-gray-700">Trúng 4 Con MN</label>
                        <input type="text" id="south_4_digits_win" name="south_4_digits_win" 
                            value="{{ old('south_4_digits_win', $customer->setting->south_4_digits_win ?? 5500) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng Đá Xiên MN -->
                    <div>
                        <label for="south_slide_win" class="block text-sm font-medium text-gray-700">Trúng Đá Xiên MN</label>
                        <input type="text" id="south_slide_win" name="south_slide_win" 
                            value="{{ old('south_slide_win', $customer->setting->south_slide_win ?? 550) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng Đá Thẳng MN -->
                    <div>
                        <label for="south_straight_win" class="block text-sm font-medium text-gray-700">Trúng Đá Thẳng MN</label>
                        <input type="text" id="south_straight_win" name="south_straight_win" 
                            value="{{ old('south_straight_win', $customer->setting->south_straight_win ?? 750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Thưởng Đá Thẳng MN -->
                    <div>
                        <div class="flex items-center">
                            <input id="south_straight_bonus" name="south_straight_bonus" type="checkbox" 
                                {{ old('south_straight_bonus', $customer->setting->south_straight_bonus ?? false) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="south_straight_bonus" class="ml-2 block text-sm text-gray-700">
                                Thưởng Đá Thẳng MN
                            </label>
                        </div>
                    </div>
                    
                    <!-- Cách trúng đá thẳng MN -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cách trúng đá thẳng MN</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="south_straight_win_type_1" name="south_straight_win_type" value="1" 
                                    {{ old('south_straight_win_type', $customer->setting->south_straight_win_type ?? 2) == 1 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="south_straight_win_type_1" class="ml-2 block text-sm text-gray-700">Một lần</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="south_straight_win_type_2" name="south_straight_win_type" value="2" 
                                    {{ old('south_straight_win_type', $customer->setting->south_straight_win_type ?? 2) == 2 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="south_straight_win_type_2" class="ml-2 block text-sm text-gray-700">Ky rưỡi</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="south_straight_win_type_3" name="south_straight_win_type" value="3" 
                                    {{ old('south_straight_win_type', $customer->setting->south_straight_win_type ?? 2) == 3 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="south_straight_win_type_3" class="ml-2 block text-sm text-gray-700">Nhiều cặp</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cách trúng đá xiên MN -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cách trúng đá xiên MN</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="south_slide_win_type_1" name="south_slide_win_type" value="1" 
                                    {{ old('south_slide_win_type', $customer->setting->south_slide_win_type ?? 3) == 1 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="south_slide_win_type_1" class="ml-2 block text-sm text-gray-700">Một lần</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="south_slide_win_type_2" name="south_slide_win_type" value="2" 
                                    {{ old('south_slide_win_type', $customer->setting->south_slide_win_type ?? 3) == 2 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="south_slide_win_type_2" class="ml-2 block text-sm text-gray-700">Ky rưỡi</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="south_slide_win_type_3" name="south_slide_win_type" value="3" 
                                    {{ old('south_slide_win_type', $customer->setting->south_slide_win_type ?? 3) == 3 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="south_slide_win_type_3" class="ml-2 block text-sm text-gray-700">Nhiều cặp</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cài đặt Miền Bắc -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800 mb-4">Cài đặt Miền Bắc</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Giá cò 2 Con Đầu - Đuôi MB -->
                    <div>
                        <label for="north_head_tail_rate" class="block text-sm font-medium text-gray-700">Giá cò 2 Con Đầu - Đuôi MB</label>
                        <input type="text" id="north_head_tail_rate" name="north_head_tail_rate" 
                            value="{{ old('north_head_tail_rate', $customer->setting->north_head_tail_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá Cò 2 Con lô MB -->
                    <div>
                        <label for="north_lo_rate" class="block text-sm font-medium text-gray-700">Giá Cò 2 Con lô MB</label>
                        <input type="text" id="north_lo_rate" name="north_lo_rate" 
                            value="{{ old('north_lo_rate', $customer->setting->north_lo_rate ?? 21.9) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá cò 3 Con MB -->
                    <div>
                        <label for="north_3_digits_rate" class="block text-sm font-medium text-gray-700">Giá cò 3 Con MB</label>
                        <input type="text" id="north_3_digits_rate" name="north_3_digits_rate" 
                            value="{{ old('north_3_digits_rate', $customer->setting->north_3_digits_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá cò Xỉu Chủ MB -->
                    <div>
                        <label for="north_3_head_tail_rate" class="block text-sm font-medium text-gray-700">Giá cò Xỉu Chủ MB (3 con đầu đuôi)</label>
                        <input type="text" id="north_3_head_tail_rate" name="north_3_head_tail_rate" 
                            value="{{ old('north_3_head_tail_rate', $customer->setting->north_3_head_tail_rate ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá cò 4 Con MB -->
                    <div>
                        <label for="north_4_digits_rate" class="block text-sm font-medium text-gray-700">Giá cò 4 Con MB</label>
                        <input type="text" id="north_4_digits_rate" name="north_4_digits_rate" 
                            value="{{ old('north_4_digits_rate', $customer->setting->north_4_digits_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá Cò Đá MB -->
                    <div>
                        <label for="north_slide_rate" class="block text-sm font-medium text-gray-700">Giá Cò Đá MB</label>
                        <input type="text" id="north_slide_rate" name="north_slide_rate" 
                            value="{{ old('north_slide_rate', $customer->setting->north_slide_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 2 Con Đầu - Đuôi MB -->
                    <div>
                        <label for="north_head_tail_win" class="block text-sm font-medium text-gray-700">Trúng 2 Con Đầu - Đuôi MB</label>
                        <input type="text" id="north_head_tail_win" name="north_head_tail_win" 
                            value="{{ old('north_head_tail_win', $customer->setting->north_head_tail_win ?? 75) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 2 Con lô MB -->
                    <div>
                        <label for="north_lo_win" class="block text-sm font-medium text-gray-700">Trúng 2 Con lô MB</label>
                        <input type="text" id="north_lo_win" name="north_lo_win" 
                            value="{{ old('north_lo_win', $customer->setting->north_lo_win ?? 75) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 3 Con MB -->
                    <div>
                        <label for="north_3_digits_win" class="block text-sm font-medium text-gray-700">Trúng 3 Con MB</label>
                        <input type="text" id="north_3_digits_win" name="north_3_digits_win" 
                            value="{{ old('north_3_digits_win', $customer->setting->north_3_digits_win ?? 650) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng Xỉu Chủ MB -->
                    <div>
                        <label for="north_3_head_tail_win" class="block text-sm font-medium text-gray-700">Trúng Xỉu Chủ MB (3 con đầu đuôi)</label>
                        <input type="text" id="north_3_head_tail_win" name="north_3_head_tail_win" 
                            value="{{ old('north_3_head_tail_win', $customer->setting->north_3_head_tail_win ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 4 Con MB -->
                    <div>
                        <label for="north_4_digits_win" class="block text-sm font-medium text-gray-700">Trúng 4 Con MB</label>
                        <input type="text" id="north_4_digits_win" name="north_4_digits_win" 
                            value="{{ old('north_4_digits_win', $customer->setting->north_4_digits_win ?? 5500) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng Đá MB -->
                    <div>
                        <label for="north_slide_win" class="block text-sm font-medium text-gray-700">Trúng Đá MB</label>
                        <input type="text" id="north_slide_win" name="north_slide_win" 
                            value="{{ old('north_slide_win', $customer->setting->north_slide_win ?? 650) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Thưởng Đá Thẳng MB -->
                    <div>
                        <div class="flex items-center">
                            <input id="north_straight_bonus" name="north_straight_bonus" type="checkbox" 
                                {{ old('north_straight_bonus', $customer->setting->north_straight_bonus ?? false) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="north_straight_bonus" class="ml-2 block text-sm text-gray-700">
                                Thưởng Đá Thẳng MB
                            </label>
                        </div>
                    </div>
                    
                    <!-- Cách trúng đá MB -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cách trúng đá MB</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="north_slide_win_type_1" name="north_slide_win_type" value="1" 
                                    {{ old('north_slide_win_type', $customer->setting->north_slide_win_type ?? 2) == 1 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="north_slide_win_type_1" class="ml-2 block text-sm text-gray-700">Một lần</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="north_slide_win_type_2" name="north_slide_win_type" value="2" 
                                    {{ old('north_slide_win_type', $customer->setting->north_slide_win_type ?? 2) == 2 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="north_slide_win_type_2" class="ml-2 block text-sm text-gray-700">Ky rưỡi</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="north_slide_win_type_3" name="north_slide_win_type" value="3" 
                                    {{ old('north_slide_win_type', $customer->setting->north_slide_win_type ?? 2) == 3 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="north_slide_win_type_3" class="ml-2 block text-sm text-gray-700">Nhiều cặp</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Xiên Miền Bắc -->
                <div class="mt-8">
                    <h3 class="text-md font-medium text-gray-700 mb-4">Xiên Miền Bắc</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Xiên 2 -->
                        <div>
                            <label for="north_slide2_rate" class="block text-sm font-medium text-gray-700">Giá cò xiên 2 MB</label>
                            <input type="text" id="north_slide2_rate" name="north_slide2_rate" 
                                value="{{ old('north_slide2_rate', $customer->setting->north_slide2_rate ?? 0.750) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="north_slide2_win" class="block text-sm font-medium text-gray-700">Trúng xiên 2 MB</label>
                            <input type="text" id="north_slide2_win" name="north_slide2_win" 
                                value="{{ old('north_slide2_win', $customer->setting->north_slide2_win ?? 75) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        
                        <!-- Xiên 3 -->
                        <div>
                            <label for="north_slide3_rate" class="block text-sm font-medium text-gray-700">Giá cò xiên 3 MB</label>
                            <input type="text" id="north_slide3_rate" name="north_slide3_rate" 
                                value="{{ old('north_slide3_rate', $customer->setting->north_slide3_rate ?? 0.750) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="north_slide3_win" class="block text-sm font-medium text-gray-700">Trúng xiên 3 MB</label>
                            <input type="text" id="north_slide3_win" name="north_slide3_win" 
                                value="{{ old('north_slide3_win', $customer->setting->north_slide3_win ?? 75) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        
                        <!-- Xiên 4 -->
                        <div>
                            <label for="north_slide4_rate" class="block text-sm font-medium text-gray-700">Giá cò xiên 4 MB</label>
                            <input type="text" id="north_slide4_rate" name="north_slide4_rate" 
                                value="{{ old('north_slide4_rate', $customer->setting->north_slide4_rate ?? 0.75) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="north_slide4_win" class="block text-sm font-medium text-gray-700">Trúng xiên 4 MB</label>
                            <input type="text" id="north_slide4_win" name="north_slide4_win" 
                                value="{{ old('north_slide4_win', $customer->setting->north_slide4_win ?? 75) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        
                        <!-- Xiên 5 -->
                        <div>
                            <label for="north_slide5_rate" class="block text-sm font-medium text-gray-700">Giá cò xiên 5 MB</label>
                            <input type="text" id="north_slide5_rate" name="north_slide5_rate" 
                                value="{{ old('north_slide5_rate', $customer->setting->north_slide5_rate ?? 0.750) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="north_slide5_win" class="block text-sm font-medium text-gray-700">Trúng xiên 5 MB</label>
                            <input type="text" id="north_slide5_win" name="north_slide5_win" 
                                value="{{ old('north_slide5_win', $customer->setting->north_slide5_win ?? 75) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        
                        <!-- Xiên 6 -->
                        <div>
                            <label for="north_slide6_rate" class="block text-sm font-medium text-gray-700">Giá cò xiên 6 MB</label>
                            <input type="text" id="north_slide6_rate" name="north_slide6_rate" 
                                value="{{ old('north_slide6_rate', $customer->setting->north_slide6_rate ?? 0.750) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="north_slide6_win" class="block text-sm font-medium text-gray-700">Trúng xiên 6 MB</label>
                            <input type="text" id="north_slide6_win" name="north_slide6_win" 
                                value="{{ old('north_slide6_win', $customer->setting->north_slide6_win ?? 75) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cài đặt Miền Trung -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800 mb-4">Cài đặt Miền Trung</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Giá cò 2 Con Đầu - Đuôi MT -->
                    <div>
                        <label for="central_head_tail_rate" class="block text-sm font-medium text-gray-700">Giá cò 2 Con Đầu - Đuôi MT</label>
                        <input type="text" id="central_head_tail_rate" name="central_head_tail_rate" 
                            value="{{ old('central_head_tail_rate', $customer->setting->central_head_tail_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá Cò 2 Con lô MT -->
                    <div>
                        <label for="central_lo_rate" class="block text-sm font-medium text-gray-700">Giá Cò 2 Con lô MT</label>
                        <input type="text" id="central_lo_rate" name="central_lo_rate" 
                            value="{{ old('central_lo_rate', $customer->setting->central_lo_rate ?? 21.9) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá cò 3 Con MT -->
                    <div>
                        <label for="central_3_digits_rate" class="block text-sm font-medium text-gray-700">Giá cò 3 Con MT</label>
                        <input type="text" id="central_3_digits_rate" name="central_3_digits_rate" 
                            value="{{ old('central_3_digits_rate', $customer->setting->central_3_digits_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá cò Xỉu Chủ MT -->
                    <div>
                        <label for="central_3_head_tail_rate" class="block text-sm font-medium text-gray-700">Giá cò Xỉu Chủ MT (3 con đầu đuôi)</label>
                        <input type="text" id="central_3_head_tail_rate" name="central_3_head_tail_rate" 
                            value="{{ old('central_3_head_tail_rate', $customer->setting->central_3_head_tail_rate ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá cò 4 Con MT -->
                    <div>
                        <label for="central_4_digits_rate" class="block text-sm font-medium text-gray-700">Giá cò 4 Con MT</label>
                        <input type="text" id="central_4_digits_rate" name="central_4_digits_rate" 
                            value="{{ old('central_4_digits_rate', $customer->setting->central_4_digits_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá Cò Ðá Xiên MT -->
                    <div>
                        <label for="central_slide_rate" class="block text-sm font-medium text-gray-700">Giá Cò Ðá Xiên MT</label>
                        <input type="text" id="central_slide_rate" name="central_slide_rate" 
                            value="{{ old('central_slide_rate', $customer->setting->central_slide_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Giá Cò Đá Thẳng MT -->
                    <div>
                        <label for="central_straight_rate" class="block text-sm font-medium text-gray-700">Giá Cò Đá Thẳng MT</label>
                        <input type="text" id="central_straight_rate" name="central_straight_rate" 
                            value="{{ old('central_straight_rate', $customer->setting->central_straight_rate ?? 0.750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 2 Con Đầu - Đuôi MT -->
                    <div>
                        <label for="central_head_tail_win" class="block text-sm font-medium text-gray-700">Trúng 2 Con Đầu - Đuôi MT</label>
                        <input type="text" id="central_head_tail_win" name="central_head_tail_win" 
                            value="{{ old('central_head_tail_win', $customer->setting->central_head_tail_win ?? 75) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 2 Con lô MT -->
                    <div>
                        <label for="central_lo_win" class="block text-sm font-medium text-gray-700">Trúng 2 Con lô MT</label>
                        <input type="text" id="central_lo_win" name="central_lo_win" 
                            value="{{ old('central_lo_win', $customer->setting->central_lo_win ?? 75) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 3 Con MT -->
                    <div>
                        <label for="central_3_digits_win" class="block text-sm font-medium text-gray-700">Trúng 3 Con MT</label>
                        <input type="text" id="central_3_digits_win" name="central_3_digits_win" 
                            value="{{ old('central_3_digits_win', $customer->setting->central_3_digits_win ?? 650) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng Xỉu Chủ MT -->
                    <div>
                        <label for="central_3_head_tail_win" class="block text-sm font-medium text-gray-700">Trúng Xỉu Chủ MT (3 con đầu đuôi)</label>
                        <input type="text" id="central_3_head_tail_win" name="central_3_head_tail_win" 
                            value="{{ old('central_3_head_tail_win', $customer->setting->central_3_head_tail_win ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng 4 Con MT -->
                    <div>
                        <label for="central_4_digits_win" class="block text-sm font-medium text-gray-700">Trúng 4 Con MT</label>
                        <input type="text" id="central_4_digits_win" name="central_4_digits_win" 
                            value="{{ old('central_4_digits_win', $customer->setting->central_4_digits_win ?? 5500) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng Đá Xiên MT -->
                    <div>
                        <label for="central_slide_win" class="block text-sm font-medium text-gray-700">Trúng Đá Xiên MT</label>
                        <input type="text" id="central_slide_win" name="central_slide_win" 
                            value="{{ old('central_slide_win', $customer->setting->central_slide_win ?? 550) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Trúng Đá Thẳng MT -->
                    <div>
                        <label for="central_straight_win" class="block text-sm font-medium text-gray-700">Trúng Đá Thẳng MT</label>
                        <input type="text" id="central_straight_win" name="central_straight_win" 
                            value="{{ old('central_straight_win', $customer->setting->central_straight_win ?? 750) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    
                    <!-- Thưởng Đá Thẳng MT -->
                    <div>
                        <div class="flex items-center">
                            <input id="central_straight_bonus" name="central_straight_bonus" type="checkbox" 
                                {{ old('central_straight_bonus', $customer->setting->central_straight_bonus ?? false) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="central_straight_bonus" class="ml-2 block text-sm text-gray-700">
                                Thưởng Đá Thẳng MT
                            </label>
                        </div>
                    </div>
                    
                    <!-- Cách trúng đá thẳng MT -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cách trúng đá thẳng MT</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="central_straight_win_type_1" name="central_straight_win_type" value="1" 
                                    {{ old('central_straight_win_type', $customer->setting->central_straight_win_type ?? 2) == 1 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="central_straight_win_type_1" class="ml-2 block text-sm text-gray-700">Một lần</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="central_straight_win_type_2" name="central_straight_win_type" value="2" 
                                    {{ old('central_straight_win_type', $customer->setting->central_straight_win_type ?? 2) == 2 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="central_straight_win_type_2" class="ml-2 block text-sm text-gray-700">Ky rưỡi</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="central_straight_win_type_3" name="central_straight_win_type" value="3" 
                                    {{ old('central_straight_win_type', $customer->setting->central_straight_win_type ?? 2) == 3 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="central_straight_win_type_3" class="ml-2 block text-sm text-gray-700">Nhiều cặp</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cách trúng đá xiên MT -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cách trúng đá xiên MT</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" id="central_slide_win_type_1" name="central_slide_win_type" value="1" 
                                    {{ old('central_slide_win_type', $customer->setting->central_slide_win_type ?? 3) == 1 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="central_slide_win_type_1" class="ml-2 block text-sm text-gray-700">Một lần</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="central_slide_win_type_2" name="central_slide_win_type" value="2" 
                                    {{ old('central_slide_win_type', $customer->setting->central_slide_win_type ?? 3) == 2 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="central_slide_win_type_2" class="ml-2 block text-sm text-gray-700">Ky rưỡi</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="central_slide_win_type_3" name="central_slide_win_type" value="3" 
                                    {{ old('central_slide_win_type', $customer->setting->central_slide_win_type ?? 3) == 3 ? 'checked' : '' }} 
                                    class="h-4 w-4 text-blue-600 border-gray-300">
                                <label for="central_slide_win_type_3" class="ml-2 block text-sm text-gray-700">Nhiều cặp</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-6 flex justify-end">
                <a href="{{ route('customers.show', $customer) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-3">
                    Hủy
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cập nhật khách hàng
                </button>
            </div>
        </form>
    </div>
</div>
@endsection