<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $people = Person::latest()->paginate(10);

        return view('people.index', compact('people'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('people.create', ['person' => new Person]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Person::create($this->validated($request));

        return redirect()->route('people.index')->with('status', 'Persona creada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Person $person)
    {
        return redirect()->route('people.edit', $person);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Person $person)
    {
        return view('people.edit', compact('person'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Person $person)
    {
        $person->update($this->validated($request, $person));

        return redirect()->route('people.index')->with('status', 'Persona actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Person $person)
    {
        $person->delete();

        return redirect()->route('people.index')->with('status', 'Persona eliminada.');
    }

    private function validated(Request $request, ?Person $person = null): array
    {
        $id = $person?->id ?? 'NULL';

        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40', 'unique:people,phone,'.$id],
            'email' => ['nullable', 'email', 'max:255'],
            'document' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }
}
