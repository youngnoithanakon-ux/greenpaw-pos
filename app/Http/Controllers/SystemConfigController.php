<?php

namespace App\Http\Controllers;

use App\Models\SystemConfig;
use App\Services\LineMessagingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemConfigController extends Controller
{
    private array $configKeys = [
        'store_name'            => ['label' => 'ชื่อร้าน',                          'group' => 'general'],
        'line_notify_enabled'   => ['label' => 'เปิดใช้งาน LINE แจ้งเตือน',         'group' => 'line'],
        'line_channel_token'    => ['label' => 'LINE Channel Access Token',          'group' => 'line'],
        'line_target_id'        => ['label' => 'LINE Target ID (User ID / Group ID)', 'group' => 'line'],
        'stock_alert_threshold' => ['label' => 'แจ้งเตือนสต็อกต่ำกว่า (ชิ้น)',       'group' => 'stock'],
    ];

    public function index(): View
    {
        $configs = SystemConfig::many(array_keys($this->configKeys));

        $configs += [
            'store_name'            => 'GreenPaw Systems',
            'line_notify_enabled'   => '0',
            'line_channel_token'    => '',
            'line_target_id'        => '',
            'stock_alert_threshold' => '5',
        ];

        return view('admin.settings.index', compact('configs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'store_name'            => 'required|string|max:100',
            'line_notify_enabled'   => 'nullable|boolean',
            'line_channel_token'    => 'nullable|string|max:512',
            'line_target_id'        => 'nullable|string|max:100',
            'stock_alert_threshold' => 'required|integer|min:0|max:999',
        ]);

        foreach ($this->configKeys as $key => $meta) {
            $value = match ($key) {
                'line_notify_enabled' => $request->boolean('line_notify_enabled') ? '1' : '0',
                default               => $request->input($key, ''),
            };
            SystemConfig::set($key, $value, $meta['label'], $meta['group']);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'บันทึกการตั้งค่าสำเร็จ');
    }

    public function testLine(Request $request): RedirectResponse
    {
        $token    = $request->input('test_token')     ?: SystemConfig::get('line_channel_token');
        $targetId = $request->input('test_target_id') ?: SystemConfig::get('line_target_id');

        if (empty($token)) {
            return back()->with('error', 'กรุณากรอก Channel Access Token ก่อนทดสอบ');
        }
        if (empty($targetId)) {
            return back()->with('error', 'กรุณากรอก Target ID (User ID / Group ID) ก่อนทดสอบ');
        }

        $ok = LineMessagingService::test($token, $targetId);

        return back()->with(
            $ok ? 'success' : 'error',
            $ok
                ? '✅ ส่งข้อความทดสอบไปที่ LINE สำเร็จ!'
                : '❌ ส่งไม่สำเร็จ — ตรวจสอบ Token และ Target ID ให้ถูกต้อง'
        );
    }
}
