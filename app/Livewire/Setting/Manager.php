<?php

namespace App\Livewire\Setting;

use App\Models\Airline;
use App\Models\Setting;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
class Manager extends Component
{
    use WithPagination;

    public Airline $airline;

    #[Url]
    public $activeTab = 'settings.general';
    public $editingSetting = null;
    public $selectedType = null;

    // Setting form
    public $key = '';
    public $value = '';
    public $description = '';
    public $type = 'string';

    // Grouped settings
    public $settings = [
        'general' => [
            'standard_passenger_weight' => '',
            'standard_crew_weight' => '',
            'standard_baggage_weight' => '',
            'standard_fuel_density' => '',
        ],
        'operations' => [
            'standard_checkin_open_time' => '',
            'standard_checkin_close_time' => '',
            'standard_boarding_open_time' => '',
            'standard_boarding_close_time' => '',
        ],
        'cargo' => [
            'cargo_capacity' => '',
            'cargo_volume' => '',
            'dangerous_goods_allowed' => '',
            'live_animals_allowed' => '',
        ],
        'notifications' => [
            'enable_email_notifications' => '',
            'enable_sms_notifications' => '',
            'email_notifications_recipient' => '',
            'sms_notifications_recipient' => '',
        ]
    ];

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
        $this->loadSettings();
    }

    public function render()
    {
        return view('livewire.setting.manager', [
            'settings' => $this->airline->settings()->orderBy('key')->get(),
            'aircraftTypes' => $this->airline->aircraftTypes()->orderBy('code')->get()
        ])->layout('components.layouts.app');
    }

    public function loadSettings()
    {
        foreach ($this->settings as $category => $items) {
            foreach ($items as $key => $value) {
                $setting = $this->airline->settings()->where('key', $key)->first();
                $this->settings[$category][$key] = $setting?->value ?? '';
            }
        }
    }

    public function saveSettings($category)
    {
        foreach ($this->settings[$category] as $key => $value) {
            if (!empty($value)) {
                $this->airline->settings()->updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'type' => $this->getSettingType($key),
                        'description' => $this->getSettingDescription($key)
                    ]
                );
            }
        }

        $this->dispatch(
            'alert',
            icon: 'success',
            message: ucfirst($category) . ' settings updated successfully.'
        );
    }

    private function containsAny(string $key, array $words): bool
    {
        return collect($words)->some(fn($word) => str_contains($key, $word));
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function editSetting(Setting $setting)
    {
        $this->editingSetting = $setting->id;
        $this->key = $setting->key;
        $this->value = $setting->value;
        $this->description = $setting->description;
        $this->type = $setting->type;
    }

    public function selectType($id)
    {
        $this->selectedType = $this->airline->aircraftTypes()->findOrFail($id);
        $this->loadTypeSettings();
    }

    public function editSettings()
    {
        $this->loadTypeSettings();
        $this->dispatch('show-settings-modal');
    }

    public function loadTypeSettings()
    {
        if (!$this->selectedType)
            return;

        foreach ($this->settings['aircraft'] as $key => $value) {
            $setting = $this->selectedType->settings()
                ->where('key', $key)
                ->where('airline_id', $this->airline->id)
                ->first();
            $this->settings['aircraft'][$key] = $setting?->value ?? '';
        }
    }

    public function saveTypeSettings()
    {
        foreach ($this->settings['aircraft'] as $key => $value) {
            if (!empty($value)) {
                $this->selectedType->settings()
                    ->updateOrCreate(
                        [
                            'key' => $key,
                            'airline_id' => $this->airline->id
                        ],
                        [
                            'value' => $value,
                            'type' => $this->getSettingType($key),
                            'description' => $this->getSettingDescription($key)
                        ]
                    );
            }
        }

        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Aircraft type settings updated successfully.'
        );
    }

    private function getSettingDescription($key): string
    {
        return match ($key) {
            // MAC and Balance
            'ref_sta_at' => 'Reference station (meters)',
            'k_constant' => 'K constant',
            'c_constant' => 'C constant',
            'length_of_mac_rc' => 'Length of MAC (meters)',
            'lemac_at' => 'Leading Edge MAC (meters)',
            default => ucwords(str_replace('_', ' ', $key)),
        };
    }

    private function getInputType($key): string
    {
        return match (true) {
            str_contains($key, 'email') => 'email',
            $this->containsAny($key, ['weight', 'volume', 'position', 'length', 'capacity', 'limit', 'correction']) => 'number',
            $this->containsAny($key, ['allowed', 'enable']) => 'checkbox',
            default => 'text',
        };
    }

    private function getSettingType($key): string
    {
        return match (true) {
            $this->containsAny($key, ['weight', 'volume', 'position', 'length', 'capacity', 'limit', 'correction', 'threshold', 'max']) => 'integer',
            $this->containsAny($key, ['allowed', 'enable']) => 'boolean',
            str_contains($key, 'time') => 'time',
            default => 'string',
        };
    }
}