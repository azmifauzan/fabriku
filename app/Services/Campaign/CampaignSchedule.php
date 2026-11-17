<?php

namespace App\Services\Campaign;

use App\Models\SystemSetting;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Jadwal pengiriman campaign otomatis yang diatur dari admin panel (disimpan di system_settings).
 */
class CampaignSchedule
{
    public const FREQUENCIES = ['daily', 'weekly', 'monthly'];

    public const TIMEZONES = [
        'Asia/Jakarta' => 'WIB',
        'Asia/Makassar' => 'WITA',
        'Asia/Jayapura' => 'WIT',
    ];

    public const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public const DEFAULTS = [
        'frequency' => 'weekly',
        'day_of_week' => 1,
        'day_of_month' => 1,
        'time' => '10:00',
        'timezone' => 'Asia/Jakarta',
    ];

    /**
     * @return array{frequency: string, day_of_week: int, day_of_month: int, time: string, timezone: string}
     */
    public function config(): array
    {
        $frequency = (string) SystemSetting::get('campaign_schedule_frequency', self::DEFAULTS['frequency']);
        $timezone = (string) SystemSetting::get('campaign_schedule_timezone', self::DEFAULTS['timezone']);
        $time = (string) SystemSetting::get('campaign_schedule_time', self::DEFAULTS['time']);

        return [
            'frequency' => in_array($frequency, self::FREQUENCIES, true) ? $frequency : self::DEFAULTS['frequency'],
            'day_of_week' => (int) SystemSetting::get('campaign_schedule_day_of_week', self::DEFAULTS['day_of_week']),
            'day_of_month' => (int) SystemSetting::get('campaign_schedule_day_of_month', self::DEFAULTS['day_of_month']),
            'time' => preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) ? $time : self::DEFAULTS['time'],
            'timezone' => array_key_exists($timezone, self::TIMEZONES) ? $timezone : self::DEFAULTS['timezone'],
        ];
    }

    /**
     * @param  array{frequency: string, day_of_week: int, day_of_month: int, time: string, timezone: string}  $config
     */
    public function save(array $config): void
    {
        SystemSetting::set('campaign_schedule_frequency', $config['frequency'], 'string');
        SystemSetting::set('campaign_schedule_day_of_week', (int) $config['day_of_week'], 'number');
        SystemSetting::set('campaign_schedule_day_of_month', (int) $config['day_of_month'], 'number');
        SystemSetting::set('campaign_schedule_time', $config['time'], 'string');
        SystemSetting::set('campaign_schedule_timezone', $config['timezone'], 'string');
    }

    /**
     * Dipanggil scheduler setiap menit; true hanya pada menit yang cocok dengan jadwal.
     */
    public function isDue(?CarbonInterface $now = null): bool
    {
        $config = $this->config();
        $local = Carbon::instance($now ?? now())->setTimezone($config['timezone']);

        if ($local->format('H:i') !== $config['time']) {
            return false;
        }

        return match ($config['frequency']) {
            'daily' => true,
            'weekly' => $local->dayOfWeek === $config['day_of_week'],
            'monthly' => $local->day === $config['day_of_month'],
        };
    }

    public function describe(): string
    {
        $config = $this->config();
        $time = $config['time'].' '.self::TIMEZONES[$config['timezone']];

        return match ($config['frequency']) {
            'daily' => "Setiap hari, {$time}",
            'weekly' => 'Setiap '.(self::DAY_NAMES[$config['day_of_week']] ?? 'Senin').", {$time}",
            'monthly' => "Setiap tanggal {$config['day_of_month']}, {$time}",
        };
    }
}
