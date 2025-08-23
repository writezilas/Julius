@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') @lang('translation.dashboard') @endslot
        @slot('title') {{$pageTitle}} @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('policy.update', $policy->id) }}" method="post">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" value="{{ $policy->title }}">
                            @error('title')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Heading one</label>
                            <input type="text" class="form-control" name="heading_one" value="{{ $policy->heading_one }}">
                            @error('heading_one')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content one</label>
                            <textarea class="form-control" id="editor1" name="content_one">{{ $policy->content_one }}</textarea>
                            @error('content_one')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        @if($policy->slug === 'how-it-work')
                        <div class="mb-3">
                            <label class="form-label">Heading two</label>
                            <input type="text" class="form-control" name="heading_two" value="{{ $policy->heading_two }}">
                            @error('heading_two')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content two</label>
                            <textarea class="form-control" id="editor2" name="content_two">{{ $policy->content_two }}</textarea>
                            @error('content_two')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        @endif

                        <div class="form-group float-end">
                            <button type="submit" class="btn btn-primary">Update content</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

@endsection
@section('script')
    <script src="//cdn.ckeditor.com/ckeditor5/38.0.1/classic/ckeditor.js"></script>

    <script type="text/javascript">
        ClassicEditor
            .create( document.querySelector( '#editor1' ), {
                removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload', 'MediaEmbed'],
            } )
            .catch( error => {
                console.error( error );
            } );
        ClassicEditor
            .create( document.querySelector( '#editor2' ), {
                removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload', 'MediaEmbed'],
            } )
            .catch( error => {
                console.error( error );
            } );

    </script>
@endsection
