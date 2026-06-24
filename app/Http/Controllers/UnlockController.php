<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UnlockController extends Controller
{
    public function step1()
    {
        return view('unlock.step1');
    }

  public function step1Submit(Request $request)
{
    if ($request->answer !== 'Arufaa') {
        return back()->with('error', 'Jawaban salah.');
    }

    session(['unlock_step1' => true]);

    return redirect()->route('unlock.step2');
}


    public function step2()
    {
        if (!session('unlock_step1')) {
            return redirect()->route('unlock.step1');
        }

        return view('unlock.step2');
    }

  public function step2Submit(Request $request)
{
    if ($request->answer !== 'rufa rufa namanya') {
        return back()->with('error', 'Jawaban salah.');
    }

    session(['unlock_step2' => true]);

    return redirect('/');
}
}