
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
                <span>{{$pairedS->pairedWithThis->share}}</span>
                <span class="status-badge">
                    @if($pairedS->pairedWithThis->is_paid == 1)
                        <span class="badge bg-success">Paid and confirmed</span>
                    @elseif($pairedS->pairedWithThis->payment)
                        <span class="badge bg-info">Paid, waiting for confirmation</span>
                    @elseif(\Carbon\Carbon::parse($pairedS->pairedWithThis->created_at)->addHour(3) >= now() && $pairedS->pairedWithThis->is_paid == 0)
                        <span class="badge bg-primary">Waiting for payment</span>
                    @else
                        <span class="badge bg-danger">Payment time expired</span>
                    @endif
                </span>
            </div>
        @endforeach
    </div>
</div>
