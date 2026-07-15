@extends('components.layout')

@section('title', 'Sunburst Server')

@push('styles')
    @vite(['resources/css/home.css'])
@endpush

@section('content')
<div class="status-card">
    <div class="status-badge">
        <span class="pulse-wrapper">
            <span class="pulse-ping"></span>
            <span class="pulse-dot"></span>
        </span>
        <span class="status-text">System Online</span>
    </div>

    <h1 class="card-title">Sunburst API</h1>
    <p class="card-description">
        The backend server environment is compiled and running successfully.
    </p>

    <div class="specs-grid">
        <div class="spec-item">
            <span class="spec-label">Laravel Version</span>
            <span class="spec-value">v{{ app()->version() }}</span>
        </div>
        <div class="spec-item">
            <span class="spec-label">PHP Version</span>
            <span class="spec-value">v{{ PHP_VERSION }}</span>
        </div>
        <div class="spec-item">
            <span class="spec-label">Environment</span>
            <span class="spec-value text-highlight">{{ app()->environment() }}</span>
        </div>
        <div class="spec-item">
            <span class="spec-label">Response Status</span>
            <span class="spec-value text-success">200 OK</span>
        </div>
    </div>

    <div class="card-footer">
        <span class="footer-meta">Active Contributors</span>
        <div class="contributors-list">
            <a href="#" class="contributor-link">Tin Phan</a>,
            <a href="#" class="contributor-link">Gia Bao</a>,
            <a href="#" class="contributor-link">Tuan Tran</a>
        </div>
    </div>
</div>
@endsection
