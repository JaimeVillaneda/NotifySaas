<?php

namespace App\Http\Controllers;

use App\Models\MessageAutomation;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageAutomationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $automations = MessageAutomation::with('template')->latest()->paginate(10);

        return view('message-automations.index', compact('automations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('message-automations.create', [
            'messageAutomation' => new MessageAutomation(['frequency' => 'once']),
            'templates' => MessageTemplate::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        MessageAutomation::create($this->validated($request));

        return redirect()->route('message-automations.index')->with('status', 'Automatizacion creada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MessageAutomation $messageAutomation)
    {
        return redirect()->route('message-automations.edit', $messageAutomation);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MessageAutomation $messageAutomation)
    {
        return view('message-automations.edit', [
            'messageAutomation' => $messageAutomation,
            'templates' => MessageTemplate::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MessageAutomation $messageAutomation)
    {
        $messageAutomation->update($this->validated($request));

        return redirect()->route('message-automations.index')->with('status', 'Automatizacion actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MessageAutomation $messageAutomation)
    {
        $messageAutomation->delete();

        return redirect()->route('message-automations.index')->with('status', 'Automatizacion eliminada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'message_template_id' => ['required', 'exists:message_templates,id'],
            'name' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'in:once,daily,weekly,interval'],
            'send_time' => ['nullable', 'date_format:H:i'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'interval_value' => ['nullable', 'integer', 'min:1'],
            'interval_unit' => ['nullable', 'in:minutes,hours,days'],
            'week_days' => ['nullable', 'array'],
            'week_days.*' => ['integer', 'between:1,7'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }
}
