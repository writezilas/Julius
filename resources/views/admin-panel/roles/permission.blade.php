@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
@section('content')

    @component('components.breadcrumb')
        @slot('li_1') @lang('translation.dashboard') @endslot
        @slot('title') {{$pageTitle}} @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0"> {{ $pageTitle }} </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.role.permission.save', $role->id) }}" method="post">
                        @csrf
                        @method('PATCH')

                        <div class="role-permission-list">
                            <div class="module-wise-permission-single pt-3 pb-3 text-center">
                                <div class="module-name font-medium d-inline-block">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="select-all-permission" onchange="selectAllPermission(this)">
                                        <label class="form-check-label" for="select-all-permission">Select all permission</label>
                                    </div>

                                </div>
                            </div>

                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th scope="col">Module name</th>
                                    <th scope="col">Permissions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($permissions as $permission)
                                    <tr>
                                        <td style="width: 40%; vertical-align: middle">
                                            <div class="d-flex justify-content-start">
                                                <span>
                                                {{ ucfirst(str_replace('-', ' ', $permission['module_name'])) }}
                                            </span>
                                                <div class="form-check form-switch mb-2 ms-2">
                                                    <input
                                                        class="form-check-input checkbox-module-name parent-{{ $permission['module_name'] }}"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="select-{{ $permission['module_name'] }}-module-permission"
                                                        @checked(isAllPermissionOfModuleActive($permission['permission'], $allPermission))
                                                        onchange="selectModuleWisePermission(this, '{{ $permission['module_name'] }}')"
                                                    >
                                                    <label class="form-check-label" for="select-{{ $permission['module_name'] }}-module-permission">Select All</label>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($permission['permission'] as $permissionItem)
                                                <div class="form-check form-switch mb-2">
                                                    <input
                                                        class="form-check-input permissions {{ $permission['module_name'] }}"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="check-permission-{{ $permissionItem->name }}"
                                                        @checked(in_array($permissionItem->name, $allPermission))
                                                        name="{{ $permissionItem->name }}"
                                                        onclick="handlePermissionCheck('{{ $permission['module_name'] }}', this)"
                                                    >
                                                    <label class="form-check-label" for="check-permission-{{ $permissionItem->name }}">
                                                        {{ ucfirst(str_replace('-',' ', $permissionItem->name)) }}
                                                        {{ in_array($permissionItem->name, $allPermission) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="float-end" >
                                <button type="submit" class="btn btn-primary">
                                    Save
                                </button>
                            </div>
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
        function selectAllPermission(el) {
            const elements =  document.getElementsByClassName('permissions');
            const moduleElements = document.getElementsByClassName('checkbox-module-name');
            if(el.checked) {
                for (let i = 0; i < elements.length; ++i) { elements[i].checked = true; }
                for (let i = 0; i < moduleElements.length; ++i) { moduleElements[i].checked = true; }

            }else {
                for (let i = 0; i < elements.length; ++i) { elements[i].checked = false; }
                for (let i = 0; i < moduleElements.length; ++i) { moduleElements[i].checked = false; }
            }
        }

        function selectModuleWisePermission(el, moduleName) {
            const elements =  document.getElementsByClassName(moduleName);
            if(el.checked) {
                for (let i = 0; i < elements.length; ++i) { elements[i].checked = true; }
            }else {
                for (let i = 0; i < elements.length; ++i) { elements[i].checked = false; }
            }
        }


        function handlePermissionCheck(moduleName, el) {

            const elements =  document.getElementsByClassName(moduleName);
            let status = true;
            for (let i = 0; i < elements.length; ++i) {
                if(!elements[i].checked) {
                    status = false;
                }
            }
            const parentElement = document.getElementsByClassName(`parent-${moduleName}`)[0].checked = status;
        }

    </script>
@endsection
