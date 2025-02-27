<?php
ini_set('display_errors', 0);
error_reporting(0);

function getFieldClass($field, $value) {
    if ($field === 'name' && $value === 'John Doe') {
        return 'placeholder-text';
    }
    if ($field === 'email' && $value === 'john@doe.com') {
        return 'placeholder-text';
    }
    if ($field === 'tel' && substr($value, -10) === '5555555555') {
        return 'placeholder-text';
    }
    return '';
}

// Helper function to return truncated HTML with "Show More" button
function getTruncatedMessageHtml($message) {
    $limit = 30; // Number of characters to display before toggling
    $safeFull = nl2br(htmlspecialchars($message)); // Full safe text

    // If under the limit, just return the full text with no toggle
    if (strlen($message) <= $limit) {
        return '<div class="truncate-message not-truncated">'.$safeFull.'</div>';
    }

    // Otherwise, truncated preview + "Show More" button
    $truncatedText = substr($message, 0, $limit) . '...';
    $safeTruncated = nl2br(htmlspecialchars($truncatedText));

    return '
    <div class="truncate-message truncated">
      '.$safeTruncated.'
    </div>
    <button type="button" class="btn btn-link show-more p-0" data-full="'.$safeFull.'" style="cursor: pointer;">
      <i class="bi bi-chevron-down"></i>
    </button>';
}

// Load data from files
$unlockedFile = __DIR__ . '/messages.txt';
$lockedFile   = __DIR__ . '/locked_messages.txt';

$unlockedMessages = [];
if (file_exists($unlockedFile)) {
    $lines = file($unlockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $unlockedMessages[] = $entry;
        }
    }
}

$lockedMessages = [];
if (file_exists($lockedFile)) {
    $lines = file($lockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $lockedMessages[] = $entry;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Panel - Received Messages</title>
  <!-- Bootswatch darkly by default -->
  <link id="themeStylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/darkly/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- We DISABLE the standard DataTables Responsive CSS since we'll control columns manually -->
  <!-- <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet"> -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="icon" type="image/png" sizes="16x16" href="../web/16.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../web/32.png">

  <style>
    .container {
      max-width: 1360px;
      margin: 0 auto;
      min-width: 900px;
    }
    body {
      min-width: 900px;
      background: #343a40;
      color: #fff;
    }
    body.light-mode {
      background: #f8f9fa;
      color: #212529;
    }

    .table-container {
      margin-top: 1.5rem;
      /* Allows horizontal scroll if table is too wide */
      overflow-x: auto;
    }

    /* Force no wrapping in the table so text won't break into multiple lines */
    table.nowrap-table {
      white-space: nowrap;
    }

    table.dataTable thead .form-control {
      color: #fff;
      background-color: #495057;
      border: 1px solid #6c757d;
    }
    body.light-mode table.dataTable thead .form-control {
      color: #495057;
      background-color: #ffffff;
      border: 1px solid #ced4da;
    }
    th.no-search input {
      display: none;
    }
    body:not(.light-mode) .placeholder-text {
      color: #6c757d !important;
    }
    body.light-mode .placeholder-text {
      color: #adb5bd !important;
    }

    /* Single-line truncation with ellipsis for the message snippet */
    .truncate-message {
      display: inline-block;  /* needed for text-overflow to work */
      max-width: 300px;       /* pick preferred width for truncated text */
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: middle;
    }
    /* Remove gradient/fade pseudo-element entirely */
    .truncate-message.truncated::after,
    .truncate-message.not-truncated::after {
      display: none !important;
      content: none !important;
    }

    /* Make child rows visually distinct (optional) */
    tr.child-row td {
      background: #3c4248;
    }
  </style>
</head>
<body>
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
    <div class="row g-2 mb-3">
      <div class="col-6 col-md-auto">
        <button id="refreshBtn" class="btn btn-primary w-100">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="exportBtn" class="btn btn-success w-100">
          <i class="bi bi-download"></i> Export CSV
        </button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="importCsvBtn" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#importCsvModal">
          <i class="bi bi-upload"></i> Import CSV
        </button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="deleteSelectedBtn" class="btn btn-warning w-100" disabled>
          <i class="bi bi-trash"></i> Delete Selected
        </button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="lockSelectedBtn" class="btn btn-secondary w-100" disabled>
          <i class="bi bi-lock-fill"></i> Lock Selected
        </button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="clearAllBtn" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#confirmClearModal">
          <i class="bi bi-trash-fill"></i> Clear All Unlocked
        </button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="clearOldest10Btn" class="btn btn-secondary w-100">
          <i class="bi bi-clock-history"></i> Clear Oldest 10 Unlocked
        </button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="clearOldest50Btn" class="btn btn-secondary w-100">
          <i class="bi bi-clock-history"></i> Clear Oldest 50 Unlocked
        </button>
      </div>
    </div>

    <!-- UNLOCKED MESSAGES -->
    <h2>Unlocked Messages</h2>
    <?php if (empty($unlockedMessages)): ?>
      <div class="alert alert-info">No unlocked messages.</div>
    <?php else: ?>
      <div class="table-responsive table-container">
        <!-- Add "nowrap-table" class to ensure no wrapping -->
        <table id="unlockedTable" class="table table-striped table-bordered nowrap-table" style="width:100%;">
          <thead>
            <tr>
              <th class="no-search"><input type="checkbox" id="selectAllUnlocked"></th>
              <th>Time</th>
              <th>Name</th>
              <th>Email</th>
              <th>Telephone</th>
              <th>Message</th>
              <th class="no-search">Actions</th>
            </tr>
            <tr>
              <th class="no-search"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Time"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Name"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Email"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Telephone"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Message"></th>
              <th class="no-search"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($unlockedMessages as $msg): ?>
              <tr data-message-id="<?= htmlspecialchars($msg['id']) ?>">
                <td>
                  <input type="checkbox" class="select-entry-unlocked" value="<?= htmlspecialchars($msg['id']) ?>">
                </td>
                <td><?= htmlspecialchars($msg['time']) ?></td>
                <td class="<?= getFieldClass('name', $msg['name']) ?>"><?= htmlspecialchars($msg['name']) ?></td>
                <td class="<?= getFieldClass('email', $msg['email']) ?>"><?= htmlspecialchars($msg['email']) ?></td>
                <td class="<?= getFieldClass('tel', $msg['tel']) ?>"><?= htmlspecialchars($msg['tel']) ?></td>
                <td>
                  <?php echo getTruncatedMessageHtml($msg['message']); ?>
                </td>
                <td>
                  <button class="btn btn-sm btn-secondary lock-btn" data-id="<?= htmlspecialchars($msg['id']) ?>">
                    Lock
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <!-- LOCKED MESSAGES -->
    <h2 class="mt-5">Locked Messages</h2>
    <?php if (empty($lockedMessages)): ?>
      <div class="alert alert-info">No locked messages.</div>
    <?php else: ?>
      <div class="table-responsive table-container">
        <table id="lockedTable" class="table table-striped table-bordered nowrap-table" style="width:100%;">
          <thead>
            <tr>
              <th>Time</th>
              <th>Name</th>
              <th>Email</th>
              <th>Telephone</th>
              <th>Message</th>
              <th class="no-search">Actions</th>
            </tr>
            <tr>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Time"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Name"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Email"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Telephone"></th>
              <th><input type="text" class="form-control form-control-sm" placeholder="Search Message"></th>
              <th class="no-search"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lockedMessages as $msg): ?>
              <tr data-message-id="<?= htmlspecialchars($msg['id']) ?>">
                <td><?= htmlspecialchars($msg['time']) ?></td>
                <td class="<?= getFieldClass('name', $msg['name']) ?>"><?= htmlspecialchars($msg['name']) ?></td>
                <td class="<?= getFieldClass('email', $msg['email']) ?>"><?= htmlspecialchars($msg['email']) ?></td>
                <td class="<?= getFieldClass('tel', $msg['tel']) ?>"><?= htmlspecialchars($msg['tel']) ?></td>
                <td>
                  <?php echo getTruncatedMessageHtml($msg['message']); ?>
                </td>
                <td>
                  <button class="btn btn-sm btn-danger unlock-btn" data-id="<?= htmlspecialchars($msg['id']) ?>">
                    Unlock
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Confirm Clear Modal -->
  <div class="modal fade" id="confirmClearModal" tabindex="-1" aria-labelledby="confirmClearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="clearForm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmClearModalLabel">Confirm Clear All Unlocked Messages</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to clear all unlocked messages? This action cannot be undone.
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
            <p class="small text-muted">The CSV file should have columns in order: Time, Name, Email, Telephone, Message, and optionally Locked (Yes/No).</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Import CSV</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- JS Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <!-- We do NOT load the responsive plugin scripts -->
  <!--<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>-->

  <script>
    $(document).ready(function(){

      var unlockedTable = $('#unlockedTable').DataTable({
        order: [[1, 'desc']],
        responsive: false,
        language: {
          search: "Global Search:",
          lengthMenu: "Display _MENU_ messages per page",
          zeroRecords: "No matching messages found",
          info: "Showing page _PAGE_ of _PAGES_",
          infoEmpty: "No messages available",
          infoFiltered: "(filtered from _MAX_ total messages)"
        }
      });
      $('#unlockedTable thead input').on('keyup change', function () {
        var colIndex = $(this).parent().index();
        unlockedTable.column(colIndex).search(this.value).draw();
      });

      var lockedTable = $('#lockedTable').DataTable({
        order: [[0, 'desc']],
        responsive: false,
        language: {
          search: "Global Search:",
          lengthMenu: "Display _MENU_ messages per page",
          zeroRecords: "No matching messages found",
          info: "Showing page _PAGE_ of _PAGES_",
          infoEmpty: "No messages available",
          infoFiltered: "(filtered from _MAX_ total messages)"
        }
      });
      $('#lockedTable thead input').on('keyup change', function () {
        var colIndex = $(this).parent().index();
        lockedTable.column(colIndex).search(this.value).draw();
      });

      // Show/Hide child row for full message
      $(document).on('click', '.show-more', function(e){
        e.stopPropagation();
        var $button = $(this);
        var fullHtml = $button.data('full');
        var tableElem = $button.closest('table').attr('id');
        var table = (tableElem === 'unlockedTable') ? unlockedTable : lockedTable;

        var tr = $button.closest('tr');
        var row = table.row(tr);

        if(row.child.isShown()) {
          // Collapse
          row.child.hide();
          tr.removeClass('shown child-row');
          $button.html('<i class="bi bi-chevron-down"></i>');
        } else {
          // Expand
          row.child('<div class="expanded-message" style="padding: 1em; white-space: pre-wrap;">'
            + fullHtml + '</div>').show();
          tr.addClass('shown child-row');
          $button.html('<i class="bi bi-chevron-up"></i>');
        }
      });

      // Check/uncheck all for unlocked table
      $('#selectAllUnlocked').on('change', function(){
        var checked = $(this).is(':checked');
        $('.select-entry-unlocked').prop('checked', checked);
        toggleActionButtons();
      });
      $(document).on('change', '.select-entry-unlocked', function(){
        if(!$(this).is(':checked')){
          $('#selectAllUnlocked').prop('checked', false);
        }
        toggleActionButtons();
      });
      function toggleActionButtons(){
        var anyChecked = $('.select-entry-unlocked:checked').length > 0;
        $('#deleteSelectedBtn').prop('disabled', !anyChecked);
        $('#lockSelectedBtn').prop('disabled', !anyChecked);
      }

      // Refresh
      $('#refreshBtn').on('click', function(){
        location.reload();
      });

      // Export
      $('#exportBtn').on('click', function(){
        window.location.href = 'export.php';
      });

      // Delete selected unlocked
      $('#deleteSelectedBtn').on('click', function(){
        var selectedIds = [];
        $('.select-entry-unlocked:checked').each(function(){
          selectedIds.push($(this).val());
        });
        if(selectedIds.length === 0){
          alert('No entries selected.');
          return;
        }
        if(!confirm('Are you sure you want to delete the selected unlocked messages?')){
          return;
        }
        $.ajax({
          url: 'delete_entries.php',
          method: 'POST',
          dataType: 'json',
          contentType: 'application/json',
          data: JSON.stringify({ ids: selectedIds }),
          success: function(response){
            if(response.success){
              location.reload();
            } else {
              alert('Failed to delete selected messages.');
            }
          },
          error: function(){
            alert('Error communicating with server.');
          }
        });
      });

      // Lock selected
      $('#lockSelectedBtn').on('click', function(){
        var selectedIds = [];
        $('.select-entry-unlocked:checked').each(function(){
          selectedIds.push($(this).val());
        });
        if(selectedIds.length === 0){
          alert('No entries selected.');
          return;
        }
        if(!confirm('Are you sure you want to lock the selected messages? They will be moved to the locked list.')){
          return;
        }
        $.ajax({
          url: 'lock_entry.php',
          method: 'POST',
          dataType: 'json',
          contentType: 'application/json',
          data: JSON.stringify({ ids: selectedIds }),
          success: function(response){
            if(response.success){
              location.reload();
            } else {
              alert('Failed to lock selected messages.');
            }
          },
          error: function(){
            alert('Error communicating with server.');
          }
        });
      });

      // Clear all unlocked
      $('#clearForm').on('submit', function(e){
        e.preventDefault();
        $.ajax({
          url: 'delete_all.php',
          method: 'POST',
          dataType: 'json',
          success: function(response){
            if(response.success){
              location.reload();
            } else {
              alert('Failed to clear unlocked messages.');
            }
          },
          error: function(){
            alert('Error communicating with server.');
          }
        });
      });

      // Clear oldest 10
      $('#clearOldest10Btn').on('click', function(){
        if(confirm('Are you sure you want to clear the oldest 10 unlocked messages?')){
          $.ajax({
            url: 'delete_oldest.php',
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({ limit: 10 }),
            success: function(response){
              if(response.success){
                location.reload();
              } else {
                alert('Failed to clear oldest 10 unlocked messages.');
              }
            },
            error: function(){
              alert('Error communicating with server.');
            }
          });
        }
      });

      // Clear oldest 50
      $('#clearOldest50Btn').on('click', function(){
        if(confirm('Are you sure you want to clear the oldest 50 unlocked messages?')){
          $.ajax({
            url: 'delete_oldest.php',
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({ limit: 50 }),
            success: function(response){
              if(response.success){
                location.reload();
              } else {
                alert('Failed to clear oldest 50 unlocked messages.');
              }
            },
            error: function(){
              alert('Error communicating with server.');
            }
          });
        }
      });

      // Lock button (single entry)
      $(document).on('click', '.lock-btn', function(){
        var id = $(this).data('id');
        if(confirm('Lock this message? It will be protected from deletion.')){
          $.ajax({
            url: 'lock_entry.php',
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(response){
              if(response.success){
                location.reload();
              } else {
                alert('Failed to lock the message.');
              }
            },
            error: function(){
              alert('Error communicating with server.');
            }
          });
        }
      });

      // Unlock button (single entry)
      $(document).on('click', '.unlock-btn', function(){
        var id = $(this).data('id');
        if(confirm('Are you sure you want to unlock this message?')){
          $.ajax({
            url: 'unlock_entry.php',
            method: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(response){
              if(response.success){
                location.reload();
              } else {
                alert('Failed to unlock the message.');
              }
            },
            error: function(){
              alert('Error communicating with server.');
            }
          });
        }
      });

      // Theme Toggle
      const darkTheme = "https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/darkly/bootstrap.min.css";
      const lightTheme = "https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/flatly/bootstrap.min.css";
      const themeBtn = $("#themeToggleBtn");
      const themeStylesheet = $("#themeStylesheet");
      const currentTheme = localStorage.getItem('theme') || 'dark';

      if(currentTheme === 'light'){
        themeStylesheet.attr('href', lightTheme);
        themeBtn.html('<i class="bi bi-moon-fill"></i> Switch to Dark Mode');
        $("body").addClass("light-mode");
      } else {
        $("body").removeClass("light-mode");
      }

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
