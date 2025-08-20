@extends('layout.v2')
@section('content')
    <div id="dashboard-root"></div>
@endsection
@section('scripts')
    @viteReactRefresh
    @vite(['src/pages/dashboard/dashboard.jsx'])
@endsection
