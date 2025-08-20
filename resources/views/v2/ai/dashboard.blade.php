@extends('layout.v2')

@section('content')
    {{-- Root element for the AI dashboard React app --}}
    <div id="ai-dashboard-root"></div>
@endsection

@section('scripts')
    {{-- Load the compiled AI dashboard bundle --}}
    @vite(['resources/assets/v2/src/pages/ai-agent/dashboard.js'])
@endsection
