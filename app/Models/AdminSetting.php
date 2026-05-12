<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'setting_key',
        'setting_value',
        'data_type'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the typed value based on data_type
     */
    public function getTypedValueAttribute()
    {
        return match ($this->data_type) {
            'boolean' => filter_var($this->setting_value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->setting_value,
            'float' => (float) $this->setting_value,
            'json' => json_decode($this->setting_value, true),
            default => $this->setting_value, // string
        };
    }

    /**
     * Set the value with appropriate type conversion
     */
    public function setTypedValue($value, $dataType = null)
    {
        if ($dataType) {
            $this->data_type = $dataType;
        }

        $this->setting_value = match ($this->data_type) {
            'boolean' => $value ? 'true' : 'false',
            'integer' => (string) (int) $value,
            'float' => (string) (float) $value,
            'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get settings by category
     */
    public static function getByCategory(string $category): array
    {
        $settings = self::where('category', $category)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->setting_key] = $setting->typed_value;
        }

        return $result;
    }

    /**
     * Get all settings grouped by category
     */
    public static function getAllGrouped(): array
    {
        $settings = self::all();
        $result = [];

        foreach ($settings as $setting) {
            if (!isset($result[$setting->category])) {
                $result[$setting->category] = [];
            }
            $result[$setting->category][$setting->setting_key] = $setting->typed_value;
        }

        return $result;
    }

    /**
     * Update or create a setting
     */
    public static function setSetting(string $category, string $key, $value, string $dataType = 'string'): self
    {
        $setting = self::updateOrCreate(
            ['category' => $category, 'setting_key' => $key],
            ['data_type' => $dataType]
        );

        $setting->setTypedValue($value, $dataType);
        $setting->save();

        return $setting;
    }

    /**
     * Get a specific setting value
     */
    public static function getSetting(string $category, string $key, $default = null)
    {
        $setting = self::where('category', $category)
            ->where('setting_key', $key)
            ->first();

        return $setting ? $setting->typed_value : $default;
    }

    /**
     * Delete settings by category
     */
    public static function deleteCategory(string $category): int
    {
        return self::where('category', $category)->delete();
    }
}
