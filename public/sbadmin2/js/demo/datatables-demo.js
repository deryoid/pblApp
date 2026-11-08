// Call the dataTables jQuery plugin with modern defaults, responsive containers, and safe empty table handling
$(document).ready(function() {
  if ($.fn && $.fn.dataTable) {
    $.fn.dataTable.ext.errMode = 'none';
  }
  $('table#dataTable, table.data-table').each(function() {
    var $tbl = $(this);
    if ($tbl.hasClass('no-datatable')) {
      return;
    }
    if (!$.fn.DataTable.isDataTable($tbl)) {
      // If table body has a single cell with colspan (Blade empty state), clear it to prevent DataTables column mismatch error
      var $emptyColspanRow = $tbl.find('tbody tr td[colspan]');
      if ($emptyColspanRow.length) {
        $tbl.find('tbody').empty();
      }

      // If wrapped in .table-responsive, convert outer wrapper so controls (search/pagination) don't scroll off-screen
      var $parentResponsive = $tbl.parent('.table-responsive');
      if ($parentResponsive.length) {
        $parentResponsive.removeClass('table-responsive').addClass('table-wrapper-outer');
      }

      // Automatically identify non-sortable columns (No, Aksi, Action icons, Atur Sandi)
      var nonSortableTargets = [];
      $tbl.find('thead th').each(function(idx) {
        var $th = $(this);
        var text = $th.text().trim().toLowerCase();
        if ($th.hasClass('no-sort') || text === 'no' || text === '#' || $th.find('.fa-cogs, .fa-cog').length > 0 || $th.hasClass('action-col') || text === 'aksi' || text === 'atur sandi') {
          $th.addClass('no-sort');
          nonSortableTargets.push(idx);
        }
      });

      $tbl.DataTable({
        pageLength: 10,
        autoWidth: false,
        dom: "<'row mb-3 align-items-center'<'col-12 col-md-6'l><'col-12 col-md-6'f>>" +
             "<'row'<'col-12 table-responsive'tr>>" +
             "<'row mt-3 align-items-center'<'col-12 col-md-5'i><'col-12 col-md-7'p>>",
        columnDefs: nonSortableTargets.length ? [
          { targets: nonSortableTargets, orderable: false }
        ] : [],
        language: {
          search: "_INPUT_",
          searchPlaceholder: "Cari data...",
          lengthMenu: "Tampilkan _MENU_ data",
          info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
          infoEmpty: "Menampilkan 0 data",
          infoFiltered: "(disaring dari _MAX_ data)",
          zeroRecords: "Tidak ditemukan data yang cocok",
          emptyTable: "Belum ada data tersedia pada tabel",
          paginate: {
            first: '<i class="fas fa-angle-double-left" aria-hidden="true"></i>',
            last: '<i class="fas fa-angle-double-right" aria-hidden="true"></i>',
            next: '<i class="fas fa-angle-right" aria-hidden="true"></i>',
            previous: '<i class="fas fa-angle-left" aria-hidden="true"></i>'
          }
        }
      });
    }
  });
});
