// Call the dataTables jQuery plugin with modern defaults and safe empty table handling
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

      $tbl.DataTable({
        responsive: true,
        pageLength: 10,
        autoWidth: false,
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
