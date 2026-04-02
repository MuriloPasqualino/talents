<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function departments(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['file' => 'Não foi possível ler o arquivo.']);
        }

        $first = fgetcsv($handle);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[0] ?? '');
            if ($name === '') {
                continue;
            }
            Department::firstOrCreate(
                [
                    'company_id' => $request->user()->company_id,
                    'name' => $name,
                ]
            );
            $count++;
        }
        fclose($handle);

        return back()->with('success', "Importação concluída: {$count} setores processados.");
    }
}
