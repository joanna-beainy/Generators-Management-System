<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Generator;
use App\Models\MeterCategory;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\MeterReading;

class ShowClients extends Component
{
    public $search = '';
    public $selectedClientId = null;
    public $clients = [];
    public $displayClients = [];

    public $successMessage = null;
    public $errorMessage = null;

    public $showEditModal = false;
    public $editingClient;
    public $first_name, $father_name, $last_name, $phone_number, $address, $generator_id, $meter_category_id, $is_offered, $current_meter;

    public function mount()
    {
        $this->clients = collect();
        $this->displayClients = collect();
        $this->loadClients();
    }

    public function loadClients()
    {
        $this->clients = Client::where('user_id', Auth::id())
            ->when($this->search, function ($query, $search) {
                $query->search($search);
            })
            ->with(['generator', 'meterCategory'])
            ->orderBy('id')
            ->get();

        $this->updateDisplayClients();
    }

    public function handleSearch()
    {
        $this->loadClients();
        $this->errorMessage = null;

        // Auto-select if only one result
        if ($this->clients->count() === 1) {
            $this->selectedClientId = $this->clients->first()->id;
            $this->updateDisplayClients();
        } else {
            $this->selectedClientId = null;
            $this->updateDisplayClients();
        }
    }

    public function updateDisplayClients()
    {
        if ($this->selectedClientId) {
            $this->displayClients = $this->clients->filter(fn($client) => $client->id == $this->selectedClientId);
        } else {
            $this->displayClients = $this->clients;
        }
    }

    public function updatedSelectedClientId($value)
    {
        if ($value) {
            $this->resetValidation();
            $this->errorMessage = null;
            $this->updateDisplayClients();
        } else {
            $this->updateDisplayClients();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedClientId = null;
        $this->loadClients();
    }

    public function toggleActive($id)
    {
        $client = Client::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $client->is_active = !$client->is_active;
        $client->save();

        $this->successMessage = 'تم تغيير حالة المشترك.';
        $this->loadClients();
    }

    public function editClient($id)
    {
        $client = Client::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->editingClient = $client;
        $this->first_name = $client->first_name;
        $this->father_name = $client->father_name;
        $this->last_name = $client->last_name;
        $this->phone_number = $client->phone_number;
        $this->address = $client->address;
        $this->generator_id = $client->generator_id;
        $this->meter_category_id = $client->meter_category_id;
        $this->is_offered = $client->is_offered;
        
        $latestReading = $client->meterReadings()->latest('reading_for_month')->first();
        $this->current_meter = $latestReading?->current_meter ?? $client->initial_meter ?? 0;

        $this->showEditModal = true;
    }

    public function updateClient()
    {
        $this->validate([
            'first_name' => 'required|string|min:2|max:255',
            'father_name' => 'nullable|string|min:2|max:255',
            'last_name' => 'nullable|string|min:2|max:255',
            'phone_number' => 'nullable|regex:/^(\+?\d{1,4})?\d{8,15}$/',
            'address' => 'required|string|min:3',
            'generator_id' => 'required|exists:generators,id',
            'meter_category_id' => 'nullable|exists:meter_categories,id',
            'is_offered' => 'boolean',
            'current_meter' => 'required|numeric|min:0',
        ]);

        if (!$this->is_offered && !$this->meter_category_id) {
            $this->errorMessage = 'يجب اختيار فئة العداد للمشترك غير المعفى.';
            return;
        }

        $updateData = [
            'first_name' => $this->first_name,
            'father_name' => $this->father_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'generator_id' => $this->generator_id,
            'is_offered' => $this->is_offered ?? false,
        ];

        $updateData['meter_category_id'] = $this->is_offered ? null : $this->meter_category_id;

        // ✅ Check if this is a new client (no meter readings yet)
        $hasMeterReadings = MeterReading::where('client_id', $this->editingClient->id)->exists();
        $meterUpdated = false;
        
        if (!$hasMeterReadings) {
            // New client - update initial_meter only if different
            if ($this->editingClient->initial_meter != $this->current_meter) {
                $updateData['initial_meter'] = $this->current_meter;
                $meterUpdated = true;
            }
        } else {
            // Existing client - update current meter only if different
            $latestReading = MeterReading::where('client_id', $this->editingClient->id)
                ->latest('reading_for_month')
                ->first();

            if ($latestReading && $latestReading->current_meter != $this->current_meter) {
                $latestReading->update([
                    'current_meter' => $this->current_meter,
                ]);
                $meterUpdated = true;
            }
        }

        $this->editingClient->update($updateData);

        $this->showEditModal = false;
        
        // ✅ Show appropriate success message
        if (!$hasMeterReadings) {
            if ($meterUpdated) {
                $this->successMessage = 'تم تحديث بيانات المشترك وعداد البداية بنجاح.';
            } else {
                $this->successMessage = 'تم تحديث بيانات المشترك بنجاح.';
            }
        } else {
            if ($meterUpdated) {
                $this->successMessage = 'تم تحديث بيانات المشترك وتعديل العداد بنجاح.';
            } else {
                $this->successMessage = 'تم تحديث بيانات المشترك بنجاح.';
            }
        }
        
        $this->loadClients();
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->resetValidation();
        $this->reset([
            'first_name', 'father_name', 'last_name', 'phone_number',
            'address', 'generator_id', 'meter_category_id', 'is_offered', 'current_meter'
        ]);
    }

    public function render()
    {
        $generators = Generator::where('user_id', Auth::id())->get();
        $categories = MeterCategory::where('user_id', Auth::id())->get();

        return view('livewire.show-clients', compact('generators', 'categories'))
            ->extends('layouts.app')
            ->section('content');
    }
}