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
  <link id="themeStylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/darkly/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    .container {max-width: 1360px;margin: 0 auto;min-width: 900px;}
    body {min-width: 900px;background: #343a40;color: #fff;}
    body.light-mode {background: #f8f9fa;color: #212529;}
    .table-container {margin-top: 1.5rem;}
    table.dataTable thead .form-control {color: #fff;background-color: #495057;border: 1px solid #6c757d;}
    body.light-mode table.dataTable thead .form-control {color: #495057;background-color: #ffffff;border: 1px solid #ced4da;}
    th.no-search input {display: none;}
    body:not(.light-mode) .placeholder-text {color: #6c757d !important;}
    body.light-mode .placeholder-text {color: #adb5bd !important;}
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Admin Panel</a>
      <div class="ms-auto">
        <button id="themeToggleBtn" class="btn btn-outline-light"><i class="bi bi-sun-fill"></i> Switch to Light Mode</button>
      </div>
    </div>
  </nav>
  <div class="container my-4">
    <h1 class="mb-4">Received Messages</h1>
    <div class="row g-2 mb-3">
      <div class="col-6 col-md-auto">
        <button id="refreshBtn" class="btn btn-primary w-100"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="exportBtn" class="btn btn-success w-100"><i class="bi bi-download"></i> Export CSV</button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="importCsvBtn" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#importCsvModal"><i class="bi bi-upload"></i> Import CSV</button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="deleteSelectedBtn" class="btn btn-warning w-100" disabled><i class="bi bi-trash"></i> Delete Selected</button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="lockSelectedBtn" class="btn btn-secondary w-100" disabled><i class="bi bi-lock-fill"></i> Lock Selected</button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="clearAllBtn" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#confirmClearModal"><i class="bi bi-trash-fill"></i> Clear All Unlocked</button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="clearOldest10Btn" class="btn btn-secondary w-100"><i class="bi bi-clock-history"></i> Clear Oldest 10 Unlocked</button>
      </div>
      <div class="col-6 col-md-auto">
        <button id="clearOldest50Btn" class="btn btn-secondary w-100"><i class="bi bi-clock-history"></i> Clear Oldest 50 Unlocked</button>
      </div>
    </div>
    <h2>Unlocked Messages</h2>
    <?php if (empty($unlockedMessages)): ?>
      <div class="alert alert-info">No unlocked messages.</div>
    <?php else: ?>
      <div class="table-responsive table-container">
        <table id="unlockedTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;">
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
                <td><input type="checkbox" class="select-entry-unlocked" value="<?= htmlspecialchars($msg['id']) ?>"></td>
                <td><?= htmlspecialchars($msg['time']) ?></td>
                <td class="<?= getFieldClass('name', $msg['name']) ?>"><?= htmlspecialchars($msg['name']) ?></td>
                <td class="<?= getFieldClass('email', $msg['email']) ?>"><?= htmlspecialchars($msg['email']) ?></td>
                <td class="<?= getFieldClass('tel', $msg['tel']) ?>"><?= htmlspecialchars($msg['tel']) ?></td>
                <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                <td><button class="btn btn-sm btn-secondary lock-btn" data-id="<?= htmlspecialchars($msg['id']) ?>">Lock</button></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    <h2 class="mt-5">Locked Messages</h2>
    <?php if (empty($lockedMessages)): ?>
      <div class="alert alert-info">No locked messages.</div>
    <?php else: ?>
      <div class="table-responsive table-container">
        <table id="lockedTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%;">
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
                <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                <td><button class="btn btn-sm btn-danger unlock-btn" data-id="<?= htmlspecialchars($msg['id']) ?>">Unlock</button></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <div class="modal fade" id="confirmClearModal" tabindex="-1" aria-labelledby="confirmClearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="clearForm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmClearModalLabel">Confirm Clear All Unlocked Messages</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">Are you sure you want to clear all unlocked messages? This action cannot be undone.</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Yes, Clear All</button>
          </div>
        </div>
      </form>
    </div>
  </div>
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
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function(){
      var unlockedTable = $('#unlockedTable').DataTable({order: [[1, 'desc']],responsive: true,columnDefs: [{ orderable: false, targets: 0 }],language: {search: "Global Search:",lengthMenu: "Display _MENU_ messages per page",zeroRecords: "No matching messages found",info: "Showing page _PAGE_ of _PAGES_",infoEmpty: "No messages available",infoFiltered: "(filtered from _MAX_ total messages)"}});
      $('#unlockedTable thead input').on('keyup change', function () {var colIndex = $(this).parent().index();unlockedTable.column(colIndex).search(this.value).draw();});
      var lockedTable = $('#lockedTable').DataTable({order: [[0, 'desc']],responsive: true,language: {search: "Global Search:",lengthMenu: "Display _MENU_ messages per page",zeroRecords: "No matching messages found",info: "Showing page _PAGE_ of _PAGES_",infoEmpty: "No messages available",infoFiltered: "(filtered from _MAX_ total messages)"}});
      $('#lockedTable thead input').on('keyup change', function () {var colIndex = $(this).parent().index();lockedTable.column(colIndex).search(this.value).draw();});
      $('#selectAllUnlocked').on('change', function(){var checked = $(this).is(':checked');$('.select-entry-unlocked').prop('checked', checked);toggleActionButtons();});
      $(document).on('change', '.select-entry-unlocked', function(){if(!$(this).is(':checked')){$('#selectAllUnlocked').prop('checked', false);}toggleActionButtons();});
      function toggleActionButtons(){var anyChecked = $('.select-entry-unlocked:checked').length > 0;$('#deleteSelectedBtn').prop('disabled', !anyChecked);$('#lockSelectedBtn').prop('disabled', !anyChecked);}
      $('#refreshBtn').on('click', function(){location.reload();});
      $('#exportBtn').on('click', function(){window.location.href = 'export.php';});
      $('#deleteSelectedBtn').on('click', function(){
        var selectedIds = [];
        $('.select-entry-unlocked:checked').each(function(){selectedIds.push($(this).val());});
        if(selectedIds.length === 0){alert('No entries selected.');return;}
        if(!confirm('Are you sure you want to delete the selected unlocked messages?')){return;}
        $.ajax({url: 'delete_entries.php',method: 'POST',dataType: 'json',contentType: 'application/json',data: JSON.stringify({ids: selectedIds}),success: function(response){if(response.success){location.reload();} else {alert('Failed to delete selected messages.');}},error: function(){alert('Error communicating with server.');}});
      });
      $('#lockSelectedBtn').on('click', function(){
        var selectedIds = [];
        $('.select-entry-unlocked:checked').each(function(){selectedIds.push($(this).val());});
        if(selectedIds.length === 0){alert('No entries selected.');return;}
        if(!confirm('Are you sure you want to lock the selected messages? They will be moved to the locked list.')){return;}
        $.ajax({url: 'lock_entry.php',method: 'POST',dataType: 'json',contentType: 'application/json',data: JSON.stringify({ids: selectedIds}),success: function(response){if(response.success){location.reload();} else {alert('Failed to lock selected messages.');}},error: function(){alert('Error communicating with server.');}});
      });
      $('#clearForm').on('submit', function(e){e.preventDefault();$.ajax({url: 'delete_all.php',method: 'POST',dataType: 'json',success: function(response){if(response.success){location.reload();} else {alert('Failed to clear unlocked messages.');}},error: function(){alert('Error communicating with server.');}});});
      $('#clearOldest10Btn').on('click', function(){
        if(confirm('Are you sure you want to clear the oldest 10 unlocked messages?')){
          $.ajax({url: 'delete_oldest.php',method: 'POST',dataType: 'json',contentType: 'application/json',data: JSON.stringify({limit: 10}),success: function(response){if(response.success){location.reload();} else {alert('Failed to clear oldest 10 unlocked messages.');}},error: function(){alert('Error communicating with server.');}});
        }
      });
      $('#clearOldest50Btn').on('click', function(){
        if(confirm('Are you sure you want to clear the oldest 50 unlocked messages?')){
          $.ajax({url: 'delete_oldest.php',method: 'POST',dataType: 'json',contentType: 'application/json',data: JSON.stringify({limit: 50}),success: function(response){if(response.success){location.reload();} else {alert('Failed to clear oldest 50 unlocked messages.');}},error: function(){alert('Error communicating with server.');}});
        }
      });
      $(document).on('click', '.lock-btn', function(){
        var id = $(this).data('id');
        if(confirm('Lock this message? It will be protected from deletion.')){
          $.ajax({url: 'lock_entry.php',method: 'POST',dataType: 'json',contentType: 'application/json',data: JSON.stringify({id: id}),success: function(response){if(response.success){location.reload();} else {alert('Failed to lock the message.');}},error: function(){alert('Error communicating with server.');}});
        }
      });
      $(document).on('click', '.unlock-btn', function(){
        var id = $(this).data('id');
        if(confirm('Are you sure you want to unlock this message?')){
          $.ajax({url: 'unlock_entry.php',method: 'POST',dataType: 'json',contentType: 'application/json',data: JSON.stringify({id: id}),success: function(response){if(response.success){location.reload();} else {alert('Failed to unlock the message.');}},error: function(){alert('Error communicating with server.');}});
        }
      });
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
