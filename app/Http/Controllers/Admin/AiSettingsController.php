<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Services\Nr1AiAnalyzer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiSettingsController extends Controller
{
    public function edit(): Response
    {
        $row = AiSetting::query()->first();
        if (! $row) {
            $row = AiSetting::query()->create([
                'provider' => 'openai',
                'model' => 'gpt-4o-mini',
                'is_enabled' => false,
                'max_tokens' => 2000,
                'temperature' => 0.30,
            ]);
        }

        return Inertia::render('Admin/AiSettings/Edit', [
            'settings' => [
                'id' => $row->id,
                'provider' => $row->provider,
                'model' => $row->model,
                'is_enabled' => $row->is_enabled,
                'max_tokens' => $row->max_tokens,
                'temperature' => (float) $row->temperature,
                'api_key_set' => $row->api_key !== null && $row->api_key !== '',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', 'in:openai,anthropic'],
            'model' => ['required', 'string', 'max:128'],
            'api_key' => ['nullable', 'string', 'max:4000'],
            'max_tokens' => ['required', 'integer', 'min:100', 'max:16000'],
            'temperature' => ['required', 'numeric', 'min:0', 'max:2'],
        ]);

        $row = AiSetting::query()->firstOrFail();
        $apiKey = $data['api_key'] ?? null;
        unset($data['api_key']);

        $row->fill([
            'provider' => $data['provider'],
            'model' => $data['model'],
            'is_enabled' => $request->boolean('is_enabled'),
            'max_tokens' => $data['max_tokens'],
            'temperature' => $data['temperature'],
        ]);

        if ($apiKey !== null && $apiKey !== '') {
            $row->api_key = $apiKey;
        }

        $row->updated_by = $request->user()->id;
        $row->save();

        return redirect()->route('admin.ai-settings.edit')->with('success', 'Configurações de IA salvas.');
    }

    public function test(Request $request, Nr1AiAnalyzer $analyzer): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', 'in:openai,anthropic'],
            'model' => ['required', 'string', 'max:128'],
            'api_key' => ['nullable', 'string', 'max:4000'],
            'max_tokens' => ['required', 'integer', 'min:100', 'max:16000'],
            'temperature' => ['required', 'numeric', 'min:0', 'max:2'],
        ]);

        $setting = new AiSetting([
            'provider' => $data['provider'],
            'model' => $data['model'],
            'max_tokens' => $data['max_tokens'],
            'temperature' => $data['temperature'],
        ]);

        $key = $data['api_key'] ?? null;
        if ($key === null || $key === '') {
            $existing = AiSetting::current();
            if (! $existing?->api_key) {
                return back()->with('error', 'Informe a chave da API para testar.');
            }
            $setting->api_key = $existing->api_key;
        } else {
            $setting->api_key = $key;
        }

        $result = $analyzer->testConnection($setting);

        if ($result['ok']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
