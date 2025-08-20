@extends('layout.v2')

@section('content')
    {{-- This div is the root for the React application. --}}
    <div id="dashboard-root"></div>
@endsection

@section('scripts')
    {{-- This loads the compiled React code. --}}
    @vite(['src/pages/dashboard/dashboard.js'])
@endsection