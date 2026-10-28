// Call the dataTables jQuery plugin with modern defaults
$(document).ready(function() {
  if ($('#dataTable').length && !$.fn.DataTable.isDataTable('#dataTable')) {
    $('#dataTable').DataTable({
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
          first: '<i class="fas fa-angle-double-left"></i>',
          last: '<i class="fas fa-angle-double-right"></i>',
          next: '<i class="fas fa-angle-right"></i>',
          previous: '<i class="fas fa-angle-left"></i>'
        }
      }
    });
  }
});
