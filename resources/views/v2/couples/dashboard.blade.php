@extends('layout.v2')

@section('content')
    {{-- Root element for the couples React dashboard --}}
    <div id="couples-dashboard-root"></div>
@endsection

@section('scripts')
    {{-- Load the compiled couples dashboard bundle --}}
    @vite(['src/pages/couples/dashboard.jsx'])
@endsection
