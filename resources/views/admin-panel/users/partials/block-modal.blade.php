<!-- Block User Modal -->
<div class="modal fade" id="blockModal" tabindex="-1" aria-labelledby="blockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="blockModalLabel">
                    <i class="ri-forbid-line text-danger me-2"></i>Block User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="block-form">
                @csrf
                <input type="hidden" id="block-user-id" name="user_id">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-danger-subtle text-danger rounded-circle">
                                <i class="ri-forbid-line fs-1"></i>
                            </div>
                        </div>
                        <h5 class="text-danger">Block User Account</h5>
                        <p class="text-muted">This will permanently restrict the user's access to the platform.</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="block-reason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="block-reason" name="reason" rows="3" 
                                  placeholder="Enter reason for blocking this user..."></textarea>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="ri-information-line me-2"></i>
                        <strong>Note:</strong> Admin blocks are permanent and will remain in effect until manually unblocked.
                    </div>
                    
                    <div class="alert alert-danger" role="alert">
                        <i class="ri-alert-line me-2"></i>
                        <strong>Warning:</strong> This action will immediately block the user from accessing their account. The user will be notified about this action.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ri-forbid-line me-1"></i>Block User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

