<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class UserImportController extends Controller
{
    public function showForm(): View
    {
        return view('admin.import-users');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new UsersImport();
        Excel::import($import, $request->file('file'));

        $message = "Berhasil import {$import->imported} pengguna.";
        if ($import->skipped > 0) {
            $message .= " {$import->skipped} baris dilewati.";
        }

        return back()
            ->with('success', $message)
            ->with('import_errors', $import->errors);
    }

    public function downloadTemplate(): Response
    {
        $headers = [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_import_user.xlsx"',
        ];

        $export = new \App\Exports\UserTemplateExport();

        return Excel::download($export, 'template_import_user.xlsx');
    }
}
