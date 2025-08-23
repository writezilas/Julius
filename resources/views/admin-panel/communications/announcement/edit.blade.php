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
                    <form action="{{ route('announcement.update', $announcement->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" value="{{ $announcement->title }}">
                            @error('title')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Excerpt (short description) [Please try to keep it in 150 to 170 characters]</label>
                            <textarea class="form-control" name="excerpt">{{ $announcement->excerpt }}</textarea>
                            @error('excerpt')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" id="editor1" name="description">{{ $announcement->description }}</textarea>
                            @error('description')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Image <small>(Optional)</small></label>
                                    <input type="file" class="form-control" name="image">
                                    @error('image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror

                                    @if($announcement->image)
                                        <div class="announce-image mt-3">
                                            <img src="{{ asset($announcement->image) }}" alt="{{ $announcement->name }}" width="100%">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Video URL <small>(optional)</small></label>
                                    <input type="text" class="form-control" name="video_url" value="{{ $announcement->video_url }}">
                                    @error('video_url')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                @if($announcement->video_url)
                                    <div class="announce-video">
                                        <iframe width="100%" height="480" src="{{ $announcement->video_url }}" frameborder="0" allowfullscreen></iframe>

                                    </div>
                                @endif
                            </div>
                        </div>
                        @can('announcement-update')
                        <div class="form-group float-end">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                        @endcan
                    </form>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

@endsection
@section('script')
    <script src="//cdn.ckeditor.com/ckeditor5/38.0.1/classic/ckeditor.js"></script>
{{--<script src="//cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>--}}

    <script type="text/javascript">
        ClassicEditor
            .create( document.querySelector( '#editor1' ), {
                removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload', 'MediaEmbed'],
            })
            .catch( error => {
                console.error( error );
            } );
    </script>
@endsection
