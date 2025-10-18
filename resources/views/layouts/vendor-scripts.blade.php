<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="{{ URL::asset('assets/libs/bootstrap/bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/node-waves/node-waves.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/feather-icons/feather-icons.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/pages/plugins/lord-icon-2.1.0.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/plugins.min.js') }}"></script>
{{-- <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script> --}}
{{-- <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script> --}}

{{-- <script src="{{ URL::asset('assets/js/pages/datatables.init.js') }}"></script> --}}

{{--sweetalert--}}
<script src="{{ URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('/assets/js/pages/sweetalerts.init.js') }}"></script>
{{--select2 js --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    base_url = "{{ url('/') }}";
</script>
<script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>

<script>
    @if(count($errors) > 0)
        @foreach($errors->all() as $error)
            toastr.error("{{ $error }}");
        @endforeach  @toastr_css
    @endif
</script>
<script>
    $(document).on("click", ".delete_two", function(e){
        e.preventDefault();

        var link = $(this).attr("href");

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'delete'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
            }
        })
    });

$(document).ready(function() {
        // Initialize Feather Icons
        if (typeof feather !== 'undefined') {
            feather.replace();
            console.log('✅ Feather icons initialized successfully');
        } else {
            console.warn('⚠️ Feather icons library not loaded');
        }
        
        $('.select2').select2();

        $('.select2').on('select2:open', function () {
            if ($('#selectall').length === 0) {
                $('.select2-dropdown').prepend('<button id="selectall" class="btn btn-sm btn-soft-success m-2">Select All</button><button id="deselectall" class="btn btn-sm btn-soft-danger m-2">Deselect All</button>');
            }
        });

        $(document).on('click', '#selectall', function() {
            $(".select2 > option").prop("selected","selected");
            $(".select2").trigger("change");
        });
        $(document).on('click', '#deselectall', function() {
            $('.select2').val(null).trigger('change');
        });
        
        // Global function to reinitialize Feather icons (useful for dynamic content)
        window.reinitializeFeatherIcons = function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
                console.log('♾ Feather icons reinitialized');
            }
        };
        
        // Also reinitialize icons after a short delay to catch any dynamically loaded content
        setTimeout(function() {
            window.reinitializeFeatherIcons();
        }, 1000);
    });
    
</script>
@yield('script')
@yield('script-bottom')

