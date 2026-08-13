<?php

namespace App\Services;

use App\Models\SystemConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * LINE Messaging API — Push Message
 *
 * ขั้นตอนการตั้งค่า:
 * 1. สร้าง LINE Official Account ที่ https://manager.line.biz/
 * 2. เปิด Messaging API ใน LINE Developers Console
 * 3. Copy "Channel access token (long-lived)" มาใส่ในระบบ
 * 4. หา User ID หรือ Group ID ของปลายทาง:
 *    - User ID: เพิ่มบัญชีเป็นเพื่อน → ดู User ID ใน Webhook event
 *    - Group ID: เพิ่มบอทเข้ากลุ่ม → ดู Group ID ใน Webhook event
 */
class LineMessagingService
{
    private const PUSH_URL = 'https://api.line.me/v2/bot/message/push';

    /**
     * ส่งข้อความไปยัง User ID หรือ Group ID ที่ตั้งค่าไว้
     */
    public static function send(string $message): bool
    {
        $token    = SystemConfig::get('line_channel_token');
        $targetId = SystemConfig::get('line_target_id');
        $enabled  = SystemConfig::get('line_notify_enabled', '0');

        if ($enabled !== '1') {
            Log::info('[LINE] ข้ามการส่ง: ปิดการแจ้งเตือนอยู่');
            return false;
        }

        if (empty($token) || empty($targetId)) {
            Log::info('[LINE] ข้ามการส่ง: ยังไม่ได้ตั้งค่า Channel Token หรือ Target ID');
            return false;
        }

        return static::pushMessage($token, $targetId, $message);
    }

    /**
     * ทดสอบด้วย token + targetId ที่ส่งเข้ามาตรงๆ (ไม่ใช้ค่าใน DB)
     */
    public static function test(string $token, string $targetId): bool
    {
        return static::pushMessage(
            $token,
            $targetId,
            "🌿 [GreenPaw] ทดสอบการแจ้งเตือน LINE Messaging API สำเร็จ!\nหากได้รับข้อความนี้แสดงว่าตั้งค่าถูกต้องแล้ว ✅"
        );
    }

    // ---------------------------------------------------------------

    private static function pushMessage(string $token, string $targetId, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ])
            ->timeout(15)
            ->post(self::PUSH_URL, [
                'to'       => $targetId,
                'messages' => [
                    ['type' => 'text', 'text' => $message],
                ],
            ]);

            if ($response->successful()) {
                Log::info('[LINE Messaging] ส่งสำเร็จ → ' . $targetId);
                return true;
            }

            Log::warning('[LINE Messaging] API error', [
                'status'   => $response->status(),
                'body'     => $response->body(),
                'targetId' => $targetId,
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('[LINE Messaging] Exception: ' . $e->getMessage());
            return false;
        }
    }
}
