<?php

namespace App\Http\Controllers;

use App\Models\EvolutionSetting;
use Illuminate\Http\Request;

class EvolutionSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = EvolutionSetting::latest()->paginate(10);

        return view('evolution-settings.index', compact('settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('evolution-settings.create', ['evolutionSetting' => new EvolutionSetting]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        EvolutionSetting::create($this->validated($request));

        return redirect()->route('evolution-settings.index')->with('status', 'Configuracion guardada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EvolutionSetting $evolutionSetting)
    {
        return redirect()->route('evolution-settings.edit', $evolutionSetting);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EvolutionSetting $evolutionSetting)
    {
        return view('evolution-settings.edit', compact('evolutionSetting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EvolutionSetting $evolutionSetting)
    {
        $evolutionSetting->update($this->validated($request, $evolutionSetting));

        return redirect()->route('evolution-settings.index')->with('status', 'Configuracion actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EvolutionSetting $evolutionSetting)
    {
        $evolutionSetting->delete();

        return redirect()->route('evolution-settings.index')->with('status', 'Configuracion eliminada.');
    }

    private function validated(Request $request, ?EvolutionSetting $setting = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['nullable', 'url', 'max:255'],
            'instance_name' => ['nullable', 'string', 'max:255'],
            'api_key' => [$setting ? 'nullable' : 'required', 'string'],
            'webhook_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (($data['api_key'] ?? '') === '' && $setting) {
            unset($data['api_key']);
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }
}
