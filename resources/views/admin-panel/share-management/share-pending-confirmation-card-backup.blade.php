{{-- 
    BACKUP OF ORIGINAL SHARE PENDING CONFIRMATION CARD
    Date: 2025-08-24
    Original Location: resources/views/index.blade.php (lines ~710-829)
    
    This is the original card implementation from the admin dashboard
    that was moved to a dedicated page under share management.
--}}

@can('view-share-pending-confirmation')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Share pending confirmation</h4>
                    
                </div><!-- end card header -->

                <div class="card-body">
                    <div class="table-responsive table-card">
                        {{-- <table class="table table-borderless table-centered align-middle table-nowrap mb-0"> --}}
                        <table id="scroll-vertical" class="table table-bordered dt-responsive nowrap align-middle mdl-data-table" style="width:100%">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th scope="col">Ticket no</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Share type</th>
                                    <th scope="col">Share bought</th>
                                    <th scope="col">Payment status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($pendingShares as $share)
                                @php
                                $payment = $share->payment;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="#" class="fw-medium link-primary">{{ $share->pairedShare->ticket_no }}</a>
                                    </td>
                                    <td>
                                        <a href="#" class="text-decoration-underline text-blue">
                                            {{ $share->pairedShare->user->name }}
                                        </a>
                                    </td>
                                    <td> {{ $share->pairedShare->trade->name }} </td>
                                    <td>
                                        <span class="text-success">
                                            {{ $share->share }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-danger">Pending confirmation</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic example"></a>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#soldShareDetails{{ $share->pairedShare->id }}">
                                                Details
                                            </button>
                                            <div class="modal fade" id="soldShareDetails{{ $share->pairedShare->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="staticBackdropLabel">Payment confirmation</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <table class="table table-bordered">
                                                                <tbody>
                                                                    <tr>
                                                                        <th>Sender name</th>
                                                                        <td>{{ $payment->name }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Amount sent from</th>
                                                                        <td>{{ $payment->number }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Amount received from</th>
                                                                        <td>{{ $payment->number }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Amount</th>
                                                                        <td>{{ formatPrice($payment->amount) }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Transaction no</th>
                                                                        <td>{{ $payment->txs_id }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Note by sender</th>
                                                                        <td>{{ $payment->note_by_sender }}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            @can('accept-share-pending-confirmation')
                                                            
                                                            <!-- Confirm Payment Form -->
                                                            <div id="confirmForm{{$payment->id}}" style="display: block;">
                                                                <form id="paymentApproveForm{{$payment->id}}" action="{{ route('share.paymentApprove') }}" method="post">
                                                                    @csrf
                                                                    <div class="form-group">
                                                                        <label>Comment <small>if any</small></label>
                                                                        <textarea name="note_by_receiver" class="form-control"></textarea>
                                                                        <input type="hidden" value="{{ $payment->id }}" name="paymentId">
                                                                        <input type="hidden" value="1" name="by_admin">
                                                                    </div>
                                                                    <div class="mt-3">
                                                                        <button type="button" onclick="toggleForms({{ $payment->id }}, 'decline')" class="btn btn-secondary me-2">Decline</button>
                                                                        <button type="button" onclick="handlePaymentConformSubmit({{ $payment->id }})" class="btn btn-success subBtn-{{$payment->id}}">Confirm payment</button>
                                                                    </div>
                                                                </form>
                                                            </div>

                                                            <!-- Decline Payment Form -->
                                                            <div id="declineForm{{$payment->id}}" style="display: none;">
                                                                <form id="paymentDeclineForm{{$payment->id}}" action="{{ route('share.paymentDecline') }}" method="post">
                                                                    @csrf
                                                                    <div class="form-group">
                                                                        <label class="text-danger">Decline Reason <small class="text-muted">(required)</small></label>
                                                                        <textarea name="admin_comment" class="form-control" placeholder="Please provide a reason for declining this payment..." required></textarea>
                                                                        <input type="hidden" value="{{ $payment->id }}" name="paymentId">
                                                                    </div>
                                                                    <div class="mt-3">
                                                                        <button type="button" onclick="toggleForms({{ $payment->id }}, 'confirm')" class="btn btn-secondary me-2">Back to Confirm</button>
                                                                        <button type="button" onclick="handlePaymentDeclineSubmit({{ $payment->id }})" class="btn btn-danger declineBtn-{{$payment->id}}">Decline Payment</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                                <!-- end tr -->
                                @endforeach
                            </tbody><!-- end tbody -->
                        </table><!-- end table -->
                    </div>
                </div>
            </div> <!-- .card-->
        </div> <!-- .col-->
    </div> 
@endcan<!-- end row-->

<script>
    function handlePaymentConformSubmit(paymentId) {
        $('.subBtn-' + paymentId).prop('disabled', true);
        $('#paymentApproveForm' + paymentId).submit();
    }

    function handlePaymentDeclineSubmit(paymentId) {
        $('.declineBtn-' + paymentId).prop('disabled', true);
        $('#paymentDeclineForm' + paymentId).submit();
    }

    function toggleForms(paymentId, formType) {
        if (formType === 'decline') {
            $('#confirmForm' + paymentId).hide();
            $('#declineForm' + paymentId).show();
        } else {
            $('#declineForm' + paymentId).hide();
            $('#confirmForm' + paymentId).show();
        }
    }
</script>
