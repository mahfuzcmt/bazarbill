<?php

namespace App\Http\Controllers;

use App\Services\PdfService;

class ManualController extends Controller
{
    public function index()
    {
        app()->setLocale('bn');

        return view('manual.index');
    }

    public function pdf()
    {
        app()->setLocale('bn');

        return PdfService::fromView('manual.pdf')->download('DueTap-User-Guide-BN.pdf');
    }
}
