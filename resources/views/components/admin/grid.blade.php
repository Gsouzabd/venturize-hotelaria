<div class="card grid-card">
    <div class="table-responsive">
        {{ $slot }}

        @if(isset($pagination))
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-md-center w-100 flex-column flex-md-row">
                    @if($pagination instanceof Illuminate\Pagination\LengthAwarePaginator)
                        {!! $pagination->appends(request()->query())->links('vendor.admin.pagination.bootstrap-4') !!}
                    @else
                        <div class="pagination-info">
                            <strong>{{ $pagination->count() }}</strong>{{ $pagination->count() > 1 ? ' registros encontrados' : ' registro encontrado' }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
