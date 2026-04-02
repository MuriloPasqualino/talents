<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(Request $request): Response
    {
        $departments = Department::query()
            ->where('company_id', $request->user()->company_id)
            ->orderBy('name')
            ->paginate(30);

        return Inertia::render('Client/Departments/Index', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Department::create([
            'company_id' => $request->user()->company_id,
            'name' => $data['name'],
        ]);

        return back()->with('success', 'Setor cadastrado.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorizeCompany($request, $department);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $department->update($data);

        return back()->with('success', 'Setor atualizado.');
    }

    public function destroy(Request $request, Department $department): RedirectResponse
    {
        $this->authorizeCompany($request, $department);

        $department->delete();

        return back()->with('success', 'Setor removido.');
    }

    private function authorizeCompany(Request $request, Department $department): void
    {
        abort_unless($department->company_id === $request->user()->company_id, 403);
    }
}
