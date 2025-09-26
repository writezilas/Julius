@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
@section('content')

    @component('components.breadcrumb')
        @slot('li_1') @lang('translation.dashboard') @endslot
        @slot('title') {{$pageTitle}} @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0"> {{ $pageTitle }} </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.saveSupportFormSettings') }}">
                        @csrf
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold text-dark fs-5">Support Form Visibility Control</label>
                            <div class="alert alert-info border-info" role="alert">
                                <i class="mdi mdi-information-outline me-2"></i>
                                <strong>Info:</strong> Control whether users can see and submit the support form on the support page. 
                                <strong class="text-primary">The Telegram channel links will always remain visible to users.</strong>
                            </div>
                            
                            <div class="bg-light p-4 rounded border">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">Current Status:</h6>
                                        <span id="currentStatus" class="badge fs-6 px-3 py-2 {{ $supportFormEnabled ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                            {{ $supportFormEnabled ? 'FORM ENABLED' : 'FORM DISABLED' }}
                                        </span>
                                    </div>
                                    <div class="form-check form-switch form-check-lg">
                                        <input class="form-check-input" type="checkbox" name="support_form_enabled" value="1" 
                                               id="supportFormSwitch" {{ $supportFormEnabled ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="supportFormSwitch">
                                            <span id="switchLabel" class="text-dark">{{ $supportFormEnabled ? 'Toggle to Disable' : 'Toggle to Enable' }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Preview Section -->
                            <div class="mt-4">
                                <h6 class="fw-bold text-dark mb-3">User Experience Preview:</h6>
                                <div id="userPreview">
                                    <div class="card border-2 {{ $supportFormEnabled ? 'border-success' : 'border-danger' }}" id="previewCard">
                                        <div class="card-body text-center">
                                            <div id="enabledPreview" class="{{ $supportFormEnabled ? '' : 'd-none' }}">
                                                <i class="mdi mdi-form-select text-success fs-1 mb-2"></i>
                                                <h6 class="text-success fw-bold">Form is Visible</h6>
                                                <p class="text-muted small mb-0">Users can see and submit the support form along with Telegram links.</p>
                                            </div>
                                            <div id="disabledPreview" class="{{ $supportFormEnabled ? 'd-none' : '' }}">
                                                <i class="mdi mdi-form-select-off text-danger fs-1 mb-2"></i>
                                                <h6 class="text-danger fw-bold">Form is Hidden</h6>
                                                <p class="text-muted small mb-2">Users see an informative message and are directed to use Telegram channels.</p>
                                                <div class="alert alert-info small mb-0">
                                                    <strong>Message shown to users:</strong><br>
                                                    "The support form is currently disabled. Please use our official Telegram channels above to reach us."
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @error('support_form_enabled')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        
                        <div class="mt-3">
                            <input type="submit" value="Save Settings" class="btn btn-primary float-end">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

@endsection

@section('script')
    <script>
        // Update label text, status badge, and preview when switch is toggled
        document.getElementById('supportFormSwitch').addEventListener('change', function() {
            const label = document.getElementById('switchLabel');
            const statusBadge = document.getElementById('currentStatus');
            const enabledPreview = document.getElementById('enabledPreview');
            const disabledPreview = document.getElementById('disabledPreview');
            const previewCard = document.getElementById('previewCard');
            
            if (this.checked) {
                // Form enabled state
                label.textContent = 'Toggle to Disable';
                label.className = 'text-dark';
                statusBadge.textContent = 'FORM ENABLED';
                statusBadge.className = 'badge fs-6 px-3 py-2 bg-success text-white';
                
                // Show enabled preview
                enabledPreview.classList.remove('d-none');
                disabledPreview.classList.add('d-none');
                previewCard.className = 'card border-2 border-success';
            } else {
                // Form disabled state
                label.textContent = 'Toggle to Enable';
                label.className = 'text-dark';
                statusBadge.textContent = 'FORM DISABLED';
                statusBadge.className = 'badge fs-6 px-3 py-2 bg-danger text-white';
                
                // Show disabled preview
                enabledPreview.classList.add('d-none');
                disabledPreview.classList.remove('d-none');
                previewCard.className = 'card border-2 border-danger';
            }
        });
    </script>
@endsection
