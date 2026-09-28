<?php

namespace App\Http\Controllers;

/**
 * Retired workflow for the separate `uploadapps` table.
 * Current routes use UploadApplicationController and `upload_applications`.
 * Keep historical rows intact; legacy IDs must never resolve to new applications.
 */
class UploadAppController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return redirect()->route('uploadapps.index');
    }

    public function create()
    {
        return redirect()->route('uploadapps.create');
    }

    public function show()
    {
        return redirect()->route('uploadapps.index')->with('error', 'This legacy application workflow has been retired.');
    }

    public function edit()
    {
        abort(410, 'The legacy uploader application workflow has been retired.');
    }

    public function store()
    {
        abort(410, 'Please submit through the current uploader application form.');
    }

    public function update()
    {
        abort(410, 'The legacy uploader application workflow has been retired.');
    }

    public function updateStatus()
    {
        abort(410, 'The legacy uploader application workflow has been retired.');
    }

    public function destroy()
    {
        abort(410, 'Historical uploader applications are retained.');
    }
}
