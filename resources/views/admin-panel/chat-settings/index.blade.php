@extends('layouts.master')
@section('title') {{ $pageTitle }} @endsection

@section('css')
<link href="{{ URL::asset('assets/libs/switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />
<style>
.setting-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    background: #fff;
}

.setting-title {
    font-size: 16px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.setting-description {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 15px;
}

.switch-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-indicator {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-enabled {
    background: #d4edda;
    color: #155724;
}

.status-disabled {
    background: #f8d7da;
    color: #721c24;
}

.quick-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.stats-row {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #495057;
}

.stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
@endsection

@section('content')
@component('components.breadcrumb')
@slot('li_1') @lang('translation.dashboard') @endslot
@slot('title') {{ $pageTitle }} @endslot
@endcomponent

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">{{ $pageTitle }}</h4>
                <div class="quick-actions">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="toggleChatBtn">
                        <i class="ri-power-line"></i> 
                        <span id="toggleText">
                            {{ $settings['chat_enabled']['current_value'] ? 'Disable Chat' : 'Enable Chat' }}
                        </span>
                    </button>
                    <a href="{{ route('admin.chat-settings.reset') }}" class="btn btn-outline-warning btn-sm" 
                       onclick="return confirm('Are you sure you want to reset all settings to default values?')">
                        <i class="ri-restart-line"></i> Reset to Default
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Chat System Status -->
                <div class="stats-row">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">
                                    <span class="status-indicator {{ $settings['chat_enabled']['current_value'] ? 'status-enabled' : 'status-disabled' }}">
                                        {{ $settings['chat_enabled']['current_value'] ? 'ENABLED' : 'DISABLED' }}
                                    </span>
                                </div>
                                <div class="stat-label">Chat System Status</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">{{ $settings['chat_character_limit']['current_value'] }}</div>
                                <div class="stat-label">Character Limit</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">
                                    {{ number_format($settings['chat_max_file_size']['current_value'] / 1024, 1) }} MB
                                </div>
                                <div class="stat-label">Max File Size</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number">
                                    <span class="status-indicator {{ $settings['chat_file_upload_enabled']['current_value'] ? 'status-enabled' : 'status-disabled' }}">
                                        {{ $settings['chat_file_upload_enabled']['current_value'] ? 'ON' : 'OFF' }}
                                    </span>
                                </div>
                                <div class="stat-label">File Uploads</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Form -->
                <form action="{{ route('admin.chat-settings.update') }}" method="POST" id="chatSettingsForm">
                    @csrf
                    @method('PUT')

                    <!-- Chat System Enable/Disable -->
                    <div class="setting-card">
                        <div class="setting-title">
                            <i class="ri-chat-3-line text-primary me-2"></i>
                            Enable Chat System
                        </div>
                        <div class="setting-description">
                            {{ $settings['chat_enabled']['description'] }}
                        </div>
                        <div class="switch-container">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="chat_enabled" 
                                       name="chat_enabled" value="1" 
                                       {{ $settings['chat_enabled']['current_value'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="chat_enabled">
                                    <span class="switch-status">
                                        {{ $settings['chat_enabled']['current_value'] ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                        @error('chat_enabled')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Character Limit -->
                    <div class="setting-card">
                        <div class="setting-title">
                            <i class="ri-text text-primary me-2"></i>
                            Message Character Limit
                        </div>
                        <div class="setting-description">
                            {{ $settings['chat_character_limit']['description'] }}
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="number" class="form-control" id="chat_character_limit" 
                                           name="chat_character_limit" 
                                           value="{{ old('chat_character_limit', $settings['chat_character_limit']['current_value']) }}"
                                           min="10" max="2000" required>
                                    <span class="input-group-text">characters</span>
                                </div>
                                <small class="text-muted">Range: 10 - 2000 characters</small>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-2">
                                    <strong>Current:</strong> {{ $settings['chat_character_limit']['current_value'] }} characters
                                </div>
                            </div>
                        </div>
                        @error('chat_character_limit')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- File Upload Settings -->
                    <div class="setting-card">
                        <div class="setting-title">
                            <i class="ri-file-upload-line text-primary me-2"></i>
                            File Upload Settings
                        </div>
                        <div class="setting-description">
                            {{ $settings['chat_file_upload_enabled']['description'] }}
                        </div>
                        
                        <!-- Enable File Uploads -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="chat_file_upload_enabled" 
                                       name="chat_file_upload_enabled" value="1" 
                                       {{ $settings['chat_file_upload_enabled']['current_value'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="chat_file_upload_enabled">
                                    Allow file uploads in chat
                                </label>
                            </div>
                        </div>

                        <!-- Max File Size -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="chat_max_file_size" class="form-label">Maximum File Size</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="chat_max_file_size" 
                                           name="chat_max_file_size" 
                                           value="{{ old('chat_max_file_size', $settings['chat_max_file_size']['current_value']) }}"
                                           min="100" max="10240" required>
                                    <span class="input-group-text">KB</span>
                                </div>
                                <small class="text-muted">Range: 100KB - 10MB (10240KB)</small>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-4">
                                    <strong>Current:</strong> 
                                    {{ number_format($settings['chat_max_file_size']['current_value'] / 1024, 1) }} MB
                                    ({{ $settings['chat_max_file_size']['current_value'] }} KB)
                                </div>
                            </div>
                        </div>
                        @error('chat_file_upload_enabled')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                        @error('chat_max_file_size')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('assets/libs/switchery/switchery.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle chat system
    document.getElementById('toggleChatBtn').addEventListener('click', function() {
        const btn = this;
        const toggleText = document.getElementById('toggleText');
        
        btn.disabled = true;
        
        fetch('{{ route("admin.chat-settings.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button text
                toggleText.textContent = data.enabled ? 'Disable Chat' : 'Enable Chat';
                
                // Update form checkbox
                document.getElementById('chat_enabled').checked = data.enabled;
                
                // Update switch status text
                document.querySelector('.switch-status').textContent = data.enabled ? 'Enabled' : 'Disabled';
                
                // Update status indicator
                const statusIndicator = document.querySelector('.status-indicator');
                statusIndicator.className = 'status-indicator ' + (data.enabled ? 'status-enabled' : 'status-disabled');
                statusIndicator.textContent = data.enabled ? 'ENABLED' : 'DISABLED';
                
                // Reload page to reflect changes everywhere
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        })
        .finally(() => {
            btn.disabled = false;
        });
    });

    // Update character limit display in real-time
    document.getElementById('chat_character_limit').addEventListener('input', function() {
        const value = this.value;
        // You can add real-time validation here if needed
    });

    // Update file size display in real-time
    document.getElementById('chat_max_file_size').addEventListener('input', function() {
        const value = this.value;
        const mbValue = (value / 1024).toFixed(1);
        // You can update a display element here if needed
    });
});
</script>
@endsection
