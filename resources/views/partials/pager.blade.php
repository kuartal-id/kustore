@if ($paginator->hasPages())
    <nav class="pager" aria-label="Pagination">
        @if ($paginator->onFirstPage())<span class="btn btn-secondary btn-sm opacity-50">Previous</span>@else<a href="{{ $paginator->previousPageUrl() }}" class="btn btn-secondary btn-sm">Previous</a>@endif
        <span class="muted">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" class="btn btn-secondary btn-sm">Next</a>@else<span class="btn btn-secondary btn-sm opacity-50">Next</span>@endif
    </nav>
@endif
