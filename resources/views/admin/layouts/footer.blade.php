{{-- </div> --}}
<!--/ Layout Demo -->
</div>


<!-- / Content -->
<!-- Footer -->

<footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
        <div class="mb-2 mb-md-0">
            ©
            <script>
                document.write(new Date().getFullYear());
            </script>
            , Share a Scan
            {{-- <a href="https://themeselection.com" target="_blank" class="footer-link fw-medium">ThemeSelection</a> --}}
        </div>

    </div>
</footer>
<!-- / Footer -->

<div class="content-backdrop fade"></div>
</div>
<!-- Content wrapper -->
</div>
<!-- / Layout page -->
</div>

<!-- Overlay -->
<div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout wrapper -->



<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->

<script src="{{ asset('admin/assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('admin/assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('admin/assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('admin/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('admin/assets/vendor/js/menu.js') }}"></script>

<!-- endbuild -->

<!-- Vendors JS -->

<!-- Main JS -->
<script src="{{ asset('admin/assets/js/main.js') }}"></script>

<!-- Page JS -->

<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

<!-- Datatable JS -->
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('admin/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('admin/assets/js/tables-datatables-advanced.js?123') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
    setTimeout(function() {
        var flashMessages = document.getElementById('flash-messages');
        if (flashMessages) {
            flashMessages.remove();
        }
    }, 5000);
</script>

@stack('scripts')
</body>

</html>
