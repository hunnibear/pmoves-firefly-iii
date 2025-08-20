@extends('layout.v2')

@section('content')
    {{-- This div is the root for the React application. --}}
    <div id="dashboard-root"></div>
@endsection

@section('scripts')
    {{-- This loads the compiled React code. --}}
    @vite(['resources/assets/v2/src/pages/dashboard/dashboard.jsx'])
@endsection