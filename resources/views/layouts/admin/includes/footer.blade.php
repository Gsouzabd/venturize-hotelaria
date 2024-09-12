<nav class="layout-footer footer bg-footer-theme">
    <div class="container-fluid d-flex flex-wrap justify-content-end container-p-x pb-3">
        <div class="pt-3">
            Copyright &copy; 2024{{ date('Y') > 2024 ? '-' . date('Y') : '' }}
            <span class="footer-text font-weight-bolder">{{ config('app.name') }}</span>
        </div>
    </div>
</nav>
