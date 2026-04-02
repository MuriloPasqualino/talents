<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function index(Request $request): Response
    {
        $positions = Position::query()
            ->where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->paginate(30);

        return Inertia::render('Client/Positions/Index', [
            'positions' => $positions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Position::create([
            'company_id' => $request->user()->company_id,
            'name' => $data['name'],
        ]);

        return back()->with('success', 'Cargo cadastrado.');
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        abort_unless($position->company_id === $request->user()->company_id, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $position->update($data);

        return back()->with('success', 'Cargo atualizado.');
    }

    public function destroy(Request $request, Position $position): RedirectResponse
    {
        abort_unless($position->company_id === $request->user()->company_id, 403);

        $position->delete();

        return back()->with('success', 'Cargo removido.');
    }
}
