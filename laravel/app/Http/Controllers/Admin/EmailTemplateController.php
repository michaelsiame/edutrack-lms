<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::latest()->paginate(15);
        return view('admin.templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:100|unique:email_templates',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'template_type' => 'required|in:welcome,enrollment,payment,certificate,password_reset,notification,general',
            'is_active' => 'nullable|boolean',
        ]);

        EmailTemplate::create([
            'template_name' => $validated['template_name'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'template_type' => $validated['template_type'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Template created successfully.');
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:100|unique:email_templates,template_name,' . $template->template_id . ',template_id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'template_type' => 'required|in:welcome,enrollment,payment,certificate,password_reset,notification,general',
            'is_active' => 'nullable|boolean',
        ]);

        $template->update([
            'template_name' => $validated['template_name'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'template_type' => $validated['template_type'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Template updated successfully.');
    }

    public function destroy(EmailTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Template deleted successfully.');
    }
}
