<?php

use Illuminate\Support\Facades\Route;
use App\Models\Job;

Route::get('/', action: function () {
    return view('home');
});

Route::get('/jobs', function () {
    $jobs = Job::with('employer')->get();
    // $jobs = Job::all();

    return view('jobs', [
        'jobs' => $jobs,
    ]);
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/jobs/{id}', function ($id) {
    $job = Job::find($id);

    return view('job', ['job' => $job]);
});
