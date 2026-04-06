@extends('layouts.app')

@section('page-title', __('site.users'))

@section('breadcrumb')
    <li class="breadcrumb-item active"><a href="{{ route('dashboard.users.index') }}">@lang('site.users')</a></li>
@endsection

@section('content')

    <div class="card card-primary">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="mb-2 mb-md-0">@lang('site.users') <small>{{ $users->total() }}</small></h3>
            </div>
            &nbsp;

            <form action="{{ route('dashboard.users.index') }}" method="get">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="@lang('site.search')">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> @lang('site.search')</button>
                        @if (auth()->user()?->hasPermission(config('permissions.users.create')))
                            <a href="{{ route('dashboard.users.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> @lang('site.add')</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if ($users->count() > 0)
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center">@lang('site.profile_image')</th>
                            <th>@lang('site.first_name')</th>
                            <th>@lang('site.last_name')</th>
                            <th>@lang('site.email')</th>
                            <th>@lang('site.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td class="text-center align-middle">
                                    <img src="{{ $user->avatar_url }}" alt="" class="img-circle border" style="width:36px;height:36px;object-fit:cover;">
                                </td>
                                <td>{{ $user->first_name }}</td>
                                <td>{{ $user->last_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if (! auth()->user()->hasPermission(config('permissions.users.update')) && ! auth()->user()->hasPermission(config('permissions.users.delete')))
                                        <span class="text-muted">--</span>
                                    @elseif (auth()->user()->hasPermission(config('permissions.users.update')) || (auth()->user()->hasPermission(config('permissions.users.delete')) && auth()->id() !== $user->id))
                                        @if (auth()->user()->hasPermission(config('permissions.users.update')))
                                            <a href="{{ route('dashboard.users.edit', $user->id) }}" class="btn btn-info btn-sm">
                                                @lang('site.edit')
                                            </a>
                                        @endif
                                        @if (auth()->user()->hasPermission(config('permissions.users.delete')) && auth()->id() !== $user->id)
                                            <form class="form-delete-confirm d-inline-block"
                                                  action="{{ route('dashboard.users.destroy', $user->id) }}"
                                                  method="post">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    @lang('site.delete')
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <h2>@lang('site.no_data_found')</h2>
            @endif
        </div>
        @if ($users->hasPages())
            <div class="card-footer clearfix">
                {{ $users->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/sweetalert2/sweetalert2.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('adminlte/plugins/sweetalert2/sweetalert2.all.js') }}"></script>
    <script>
        document.querySelectorAll('form.form-delete-confirm').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                Swal.fire({
                    text: 'هل تريد تأكيد الحذف؟',
                    icon: 'warning',
                    showCancelButton: true,
                    focusCancel: true,
                    confirmButtonText: 'تأكيد الحذف',
                    cancelButtonText: 'إلغاء',
                    confirmButtonColor: '#c82333',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                    rtl: true,
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
        
    </script>

    
        <script src="{{asset ('dashboard/plugins/ckeditor-sample.html') }}"></script> 
@endpush
