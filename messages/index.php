<?php
// index.php

// Disable error reporting (production mode – no file names/line numbers)
ini_set('display_errors', 0);
error_reporting(0);

$messagesFile = __DIR__ . '/messages.txt';
$messages = [];

if (file_exists($messagesFile)) {
    $lines = file($messagesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $messages[] = $entry;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Received Messages</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Theme Stylesheet: Default is Darkly (dark mode) -->
  <link id="themeStylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/darkly/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  
  <style>
    /* Default dark mode styles */
    body {
      background: #343a40;
      color: #fff;
    }
    /* Light mode overrides */
    body.light-mode {
      background: #f8f9fa;
      color: #212529;
    }
    .table-container {
      margin-top: 1.5rem;
    }
    /* Dark mode: search boxes use a dark background */
    table.dataTable thead .form-control {
      color: #fff;
      background-color: #495057;
      border: 1px solid #6c757d;
    }
    /* Light mode: override search box styles with a brighter background */
    body.light-mode table.dataTable thead .form-control {
      color: #495057;
      background-color: #ffffff;
      border: 1px solid #ced4da;
    }
    /* Hide search input for columns marked as no-search */
    th.no-search input {
      display: none;
    }
  </style>
</head>
<body>
  <!-- Navigation Bar with Theme Toggle -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Admin Panel</a>
      <div class="ms-auto">
        <button id="themeToggleBtn" class="btn btn-outline-light">
          <i class="bi bi-sun-fill"></i> Switch to Light Mode
        </button>
      </div>
    </div>
  </nav>
  
  <div class="container my-4">
    <h1 class="mb-4">Received Messages</h1>
    
    <!-- Display import messages if available -->
    <?php if(isset($_GET['import_success'])): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($_GET['import_success']); ?></div>
    <?php elseif(isset($_GET['import_error'])): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['import_error']); ?></div>
    <?php endif; ?>
    
    <div class="mb-3">
      <button id="refreshBtn" class="btn btn-primary me-2">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </button>
      <button id="exportBtn" class="btn btn-success me-2">
        <i class="bi bi-download"></i> Export CSV
      </button>
      <!-- New Import CSV button -->
      <button id="importCsvBtn" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#importCsvModal">
        <i class="bi bi-upload"></i> Import CSV
      </button>
      <button id="deleteSelectedBtn" class="btn btn-warning me-2" disabled>
        <i class="bi bi-trash"></i> Delete Selected
      </button>
      <button id="clearAllBtn" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#confirmClearModal">
        <i class="bi bi-trash-fill"></i> Clear All
      </button>
      <button id="clearOldest10Btn" class="btn btn-secondary me-2">
        <i class="bi bi-clock-history"></i> Clear Oldest 10
      </button>
      <button id="clearOldest50Btn" class="btn btn-secondary">
        <i class="bi bi-clock-history"></i> Clear Oldest 50
      </button>
    </div>
    
    <?php if (empty($messages)): ?>
      <div class="alert alert-info">No messages received yet.</div>
    <?php else: ?>
      <div class="table-responsive table-container">
        <table id="messagesTable" class="table table-striped table-bordered">
          <thead>
            <!-- Header row with checkboxes for manual deletion -->
            <tr>
              <th class="no-search"><input type="checkbox" id="selectAll"></th>
              <th>Time</th>
              <th>Name</th>
              <th>Email</th>
              <th>Telephone</th>
              <th>Message</th>
            </tr>
            <!-- Header row with column-specific search inputs -->
            <tr>
              <th class="no-search"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Time"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Name"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Email"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Telephone"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Message"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($messages as $msg): ?>
              <tr data-message-id="<?= htmlspecialchars($msg['id']) ?>">
                <td><input type="checkbox" class="select-entry" value="<?= htmlspecialchars($msg['id']) ?>"></td>
                <td><?= htmlspecialchars($msg['time']) ?></td>
                <td><?= htmlspecialchars($msg['name']) ?></td>
                <td><?= htmlspecialchars($msg['email']) ?></td>
                <td><?= htmlspecialchars($msg['tel']) ?></td>
                <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Clear All Confirmation Modal -->
  <div class="modal fade" id="confirmClearModal" tabindex="-1" aria-labelledby="confirmClearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="clearForm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmClearModalLabel">Confirm Clear All Messages</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to clear <strong>all</strong> messages? This action cannot be undone.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Yes, Clear All</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Import CSV Modal -->
  <div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="importCsvForm" method="POST" action="import_csv.php" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="importCsvModalLabel">Import CSV</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="csvFileInput" class="form-label">Select CSV file</label>
              <input type="file" class="form-control" id="csvFileInput" name="csv_file" accept=".csv" required>
            </div>
            <p class="small text-muted">The CSV file should have the following columns in order: Time, Name, Email, Telephone, Message. The header row is optional.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Import CSV</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <!-- jQuery (required for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  
  <script>
    $(document).ready(function(){
      // Initialize DataTable with column-specific search inputs
      var table = $('#messagesTable').DataTable({
        order: [[1, 'desc']],
        columnDefs: [
          { orderable: false, targets: 0 }
        ],
        language: {
          search: "Global Search:",
          lengthMenu: "Display _MENU_ messages per page",
          zeroRecords: "No matching messages found",
          info: "Showing page _PAGE_ of _PAGES_",
          infoEmpty: "No messages available",
          infoFiltered: "(filtered from _MAX_ total messages)"
        }
      });
      
      // Apply column search using header inputs
      $('#messagesTable thead input').on('keyup change', function () {
        var colIndex = $(this).parent().index();
        table.column(colIndex).search(this.value).draw();
      });
      
      // Select/Deselect all checkboxes
      $('#selectAll').on('change', function(){
        var checked = $(this).is(':checked');
        $('.select-entry').prop('checked', checked);
        toggleDeleteSelectedBtn();
      });
      
      // Toggle delete button based on checkbox selection
      $(document).on('change', '.select-entry', function(){
        if(!$(this).is(':checked')){
          $('#selectAll').prop('checked', false);
        }
        toggleDeleteSelectedBtn();
      });
      
      function toggleDeleteSelectedBtn(){
        var anyChecked = $('.select-entry:checked').length > 0;
        $('#deleteSelectedBtn').prop('disabled', !anyChecked);
      }
      
      // Refresh button
      $('#refreshBtn').on('click', function(){
        location.reload();
      });
      
      // Export CSV button
      $('#exportBtn').on('click', function(){
        window.location.href = 'export.php';
      });
      
      // Delete Selected button – AJAX call to delete_entries.php
      $('#deleteSelectedBtn').on('click', function(){
        var selectedIds = [];
        $('.select-entry:checked').each(function(){
          selectedIds.push($(this).val());
        });
        if(selectedIds.length === 0){
          alert('No entries selected.');
          return;
        }
        if(!confirm('Are you sure you want to delete the selected entries?')){
          return;
        }
        $.ajax({
          url: 'delete_entries.php',
          method: 'POST',
          dataType: 'json',
          contentType: 'application/json',
          data: JSON.stringify({ids: selectedIds}),
          success: function(response) {
            if(response.success) {
              location.reload();
            } else {
              alert('Failed to delete selected entries.');
            }
          },
          error: function() {
            alert('Error communicating with server.');
          }
        });
      });
      
      // Clear All form submission via AJAX
      $('#clearForm').on('submit', function(e){
        e.preventDefault();
        $.ajax({
          url: 'delete_all.php',
          method: 'POST',
          dataType: 'json',
          success: function(response) {
            if(response.success) {
              location.reload();
            } else {
              alert('Failed to clear messages.');
            }
          },
          error: function() {
            alert('Error communicating with server.');
          }
        });
      });
      
      // Clear Oldest 10 button
      $('#clearOldest10Btn').on('click', function(){
        if(confirm('Are you sure you want to clear the oldest 10 messages?')){
          $.ajax({
            url: 'delete_oldest.php',
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({limit: 10}),
            success: function(response) {
              if(response.success) {
                location.reload();
              } else {
                alert('Failed to clear oldest 10 messages.');
              }
            },
            error: function() {
              alert('Error communicating with server.');
            }
          });
        }
      });
      
      // Clear Oldest 50 button
      $('#clearOldest50Btn').on('click', function(){
        if(confirm('Are you sure you want to clear the oldest 50 messages?')){
          $.ajax({
            url: 'delete_oldest.php',
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({limit: 50}),
            success: function(response) {
              if(response.success) {
                location.reload();
              } else {
                alert('Failed to clear oldest 50 messages.');
              }
            },
            error: function() {
              alert('Error communicating with server.');
            }
          });
        }
      });
      
      // Theme Toggle Logic
      const darkTheme = "https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/darkly/bootstrap.min.css";
      const lightTheme = "https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/flatly/bootstrap.min.css";
      const themeBtn = $("#themeToggleBtn");
      const themeStylesheet = $("#themeStylesheet");
      
      // On page load, check localStorage for a saved theme and update the body class accordingly
      const currentTheme = localStorage.getItem('theme') || 'dark';
      if(currentTheme === 'light'){
        themeStylesheet.attr('href', lightTheme);
        themeBtn.html('<i class="bi bi-moon-fill"></i> Switch to Dark Mode');
        $("body").addClass("light-mode");
      } else {
        $("body").removeClass("light-mode");
      }
      
      // Toggle theme on button click and update body class for light mode styles
      themeBtn.on('click', function(){
        if(themeStylesheet.attr('href') === darkTheme){
          themeStylesheet.attr('href', lightTheme);
          themeBtn.html('<i class="bi bi-moon-fill"></i> Switch to Dark Mode');
          localStorage.setItem('theme', 'light');
          $("body").addClass("light-mode");
        } else {
          themeStylesheet.attr('href', darkTheme);
          themeBtn.html('<i class="bi bi-sun-fill"></i> Switch to Light Mode');
          localStorage.setItem('theme', 'dark');
          $("body").removeClass("light-mode");
        }
      });
    });
  </script>
</body>
</html>
