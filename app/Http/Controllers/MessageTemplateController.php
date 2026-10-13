<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = MessageTemplate::latest()->paginate(10);

        return view('message-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('message-templates.create', ['messageTemplate' => new MessageTemplate]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        MessageTemplate::create($this->validated($request));

        return redirect()->route('message-templates.index')->with('status', 'Plantilla creada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MessageTemplate $messageTemplate)
    {
        return redirect()->route('message-templates.edit', $messageTemplate);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MessageTemplate $messageTemplate)
    {
        return view('message-templates.edit', compact('messageTemplate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        $messageTemplate->update($this->validated($request, $messageTemplate));

        return redirect()->route('message-templates.index')->with('status', 'Plantilla actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MessageTemplate $messageTemplate)
    {
        $messageTemplate->delete();

        return redirect()->route('message-templates.index')->with('status', 'Plantilla eliminada.');
    }

    private function validated(Request $request, ?MessageTemplate $template = null): array
    {
        $id = $template?->id ?? 'NULL';
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:message_templates,slug,'.$id],
            'body' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }
}
