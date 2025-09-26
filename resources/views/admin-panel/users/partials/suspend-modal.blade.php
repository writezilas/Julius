<!-- Suspend User Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suspendModalLabel">
                    <i class="ri-pause-line text-warning me-2"></i>Suspend User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="suspend-form">
                @csrf
                <input type="hidden" id="suspend-user-id" name="user_id">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                <i class="ri-pause-line fs-1"></i>
                            </div>
                        </div>
                        <h5 class="text-warning">Suspend User Account</h5>
                        <p class="text-muted">This will temporarily restrict the user's access to the platform.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="suspend-duration" class="form-label">Suspension Duration <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="suspend-duration" name="duration" 
                                           min="1" max="8760" value="24" required>
                                    <select class="form-select" name="duration_type" style="max-width: 120px;">
                                        <option value="hours">Hours</option>
                                        <option value="days">Days</option>
                                    </select>
                                </div>
                                <small class="text-muted">Maximum: 8760 hours (1 year)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="suspend-reason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="suspend-reason" name="reason" rows="3" 
                                  placeholder="Enter reason for suspension..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning" role="alert">
                        <i class="ri-alert-line me-2"></i>
                        <strong>Note:</strong> The user will be notified about this suspension and will not be able to access their account until the suspension period expires.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="ri-pause-line me-1"></i>Suspend User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
