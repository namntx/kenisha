<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    public function index()
    {
        $provinces = Province::with('region')
            ->orderBy('region_id')
            ->orderBy('draw_day')
            ->orderBy('name')
            ->paginate(20);
            
        return view('provinces.index', compact('provinces'));
    }
    
    public function create()
    {
        $regions = Region::all();
        $drawDays = [
            0 => 'Chủ nhật',
            1 => 'Thứ hai',
            2 => 'Thứ ba',
            3 => 'Thứ tư',
            4 => 'Thứ năm',
            5 => 'Thứ sáu',
            6 => 'Thứ bảy'
        ];
        
        return view('provinces.create', compact('regions', 'drawDays'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:provinces',
            'region_id' => 'required|exists:regions,id',
            'draw_day' => 'required|integer|min:0|max:6',
            'is_active' => 'boolean'
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        
        Province::create($validated);
        
        return redirect()->route('provinces.index')
            ->with('success', 'Đã thêm tỉnh đài thành công');
    }
    
    public function edit(Province $province)
    {
        $regions = Region::all();
        $drawDays = [
            0 => 'Chủ nhật',
            1 => 'Thứ hai',
            2 => 'Thứ ba',
            3 => 'Thứ tư',
            4 => 'Thứ năm',
            5 => 'Thứ sáu',
            6 => 'Thứ bảy'
        ];
        
        return view('provinces.edit', compact('province', 'regions', 'drawDays'));
    }
    
    public function update(Request $request, Province $province)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:provinces,code,' . $province->id,
            'region_id' => 'required|exists:regions,id',
            'draw_day' => 'required|integer|min:0|max:6',
            'is_active' => 'boolean'
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        
        $province->update($validated);
        
        return redirect()->route('provinces.index')
            ->with('success', 'Đã cập nhật tỉnh đài thành công');
    }
    
    public function destroy(Province $province)
    {
        // Kiểm tra trước khi xóa
        $hasBets = $province->bets()->exists();
        $hasResults = $province->lotteryResults()->exists();
        
        if ($hasBets || $hasResults) {
            return back()->with('error', 'Không thể xóa tỉnh đài vì đã có dữ liệu liên quan');
        }
        
        $province->delete();
        
        return redirect()->route('provinces.index')
            ->with('success', 'Đã xóa tỉnh đài thành công');
    }
}