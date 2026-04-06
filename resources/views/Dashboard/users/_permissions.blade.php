@php
    /** @var array<int, string> $selectedPermissions Effective names (direct + roles from controller). */
    $effectiveFromModel = isset($selectedPermissions) && is_array($selectedPermissions)
        ? array_values(array_unique(array_filter($selectedPermissions, fn ($n) => is_string($n) && $n !== '')))
        : [];
    $posted = old('permissions');
    // After validation redirect: use posted permissions[]. First load / no old flash: use effective (allPermissions + direct).
    $checkedPermissions = is_array($posted)
        ? array_values(array_unique(array_filter($posted, fn ($n) => is_string($n) && $n !== '')))
        : $effectiveFromModel;
    $displayPermissionModels = collect(array_keys(config('permissions', [])))
        ->merge(array_keys($permissionMatrix ?? []))
        ->unique()
        ->values()
        ->all();
    $resolvedActivePermissionGroup = collect(array_keys($permissionMatrix ?? []))->first(function ($group) use ($permissionMatrix, $checkedPermissions) {
        foreach ($permissionMatrix[$group] as $permission) {
            if (in_array($permission['name'], $checkedPermissions, true)) {
                return true;
            }
        }

        return false;
    }) ?? ($activePermissionGroup ?? ($displayPermissionModels[0] ?? null));
@endphp

<div class="form-group permissions-panel mb-4">
    <label class="d-block font-weight-bold mb-3">@lang('site.permissions')</label>

    @if (! empty($permissionMatrix))
        <div class="card card-outline card-primary permissions-card mb-0">
            <div class="card-header p-2">
                <ul class="nav nav-pills nav-fill permissions-tabs" role="tablist">
                    @foreach ($displayPermissionModels as $model)
                        @php
                            $tabId = 'permission-tab-' . $model;
                            $paneId = 'permission-pane-' . $model;
                            $isActive = $model === $resolvedActivePermissionGroup;
                        @endphp

                        <li class="nav-item">
                            <a
                                class="nav-link {{ $isActive ? 'active' : '' }}"
                                id="{{ $tabId }}"
                                data-toggle="tab"
                                href="#{{ $paneId }}"
                                role="tab"
                                aria-controls="{{ $paneId }}"
                                aria-selected="{{ $isActive ? 'true' : 'false' }}"
                            >
                                @lang('site.' . $model)
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content permissions-tab-content">
                    @foreach ($displayPermissionModels as $model)
                        @php
                            $permissions = $permissionMatrix[$model] ?? [];
                            $paneId = 'permission-pane-' . $model;
                            $tabId = 'permission-tab-' . $model;
                            $isActive = $model === $resolvedActivePermissionGroup;
                        @endphp

                        <div
                            class="tab-pane fade {{ $isActive ? 'show active' : '' }}"
                            id="{{ $paneId }}"
                            role="tabpanel"
                            aria-labelledby="{{ $tabId }}"
                        >
                            <div class="row">
                                @if (! empty($permissions))
                                    @foreach ($permissions as $permission)
                                        @php
                                            $permissionId = 'permission_' . $model . '_' . $permission['action'];
                                        @endphp

                                        <div class="col-sm-6 col-lg-3 mb-3">
                                            <div class="border rounded h-100 px-3 py-2 permissions-option">
                                                <div class="custom-control custom-checkbox">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $permission['name'] }}"
                                                        id="{{ $permissionId }}"
                                                        class="custom-control-input"
                                                        {{ in_array($permission['name'], $checkedPermissions, true) ? 'checked' : '' }}
                                                    >
                                                    <label class="custom-control-label" for="{{ $permissionId }}">
                                                        @lang('site.' . $permission['action'])
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <span class="text-muted">@lang('site.no_data_found')</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning mb-0">
            @lang('site.no_data_found')
        </div>
    @endif
</div>