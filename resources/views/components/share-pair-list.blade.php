
<div class="table-container m-4">
    <div class="table-header">
        <span>SI</span>
        <span>Ticket No</span>
        <span>Paired share quantity</span>
        <span>Status</span>
    </div>
    <div class="table-body">
        @foreach($pairedShare as $pairedS)
            <div class="table-row">
                <span>{{$loop->iteration}}</span>
                <span>{{$pairedS->ticket_no}}</span>
                <span>{{$pairedS->pairedWithThis->first()->share ?? 0}}</span>
                <span class="status-badge">
                    @if($pairedS->pairedWithThis->first() && $pairedS->pairedWithThis->first()->is_paid == 1)
                        <span class="badge bg-success">Paid and confirmed</span>
                    @elseif($pairedS->pairedWithThis->first() && $pairedS->pairedWithThis->first()->payment)
                        <span class="badge bg-info">Paid, waiting for confirmation</span>
                    @elseif($pairedS->pairedWithThis->first() && \Carbon\Carbon::parse($pairedS->pairedWithThis->first()->created_at)->addMinutes($share->payment_deadline_minutes ?? 60) >= now() && $pairedS->pairedWithThis->first()->is_paid == 0)
                        <span class="badge bg-primary">Waiting for payment</span>
                    @else
                        <span class="badge bg-danger">Payment time expired</span>
                    @endif
                </span>
            </div>
        @endforeach
    </div>
</div>
