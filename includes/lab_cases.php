<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
   header('Location: ../Admin.html');
    exit;
}
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lab Cases</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
    <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Lab Cases</h4>
    <div>
        <a href="lab_export_all.php" class="btn btn-outline-success btn-sm" target="_blank">Download All (PDF)</a>
    </div>
</div>

    <div class="d-flex justify-content-end mb-2">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#labModal">Add Lab Case</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead><tr><th>#</th><th>Patient</th><th>Description</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT l.id, l.patient_id, l.description, l.status, l.created_at, p.first_name, p.last_name FROM lab_cases l LEFT JOIN patients p ON l.patient_id=p.id ORDER BY l.created_at DESC LIMIT 50");
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($r['id']).'</td>';
                    echo '<td>'.htmlspecialchars(($r['first_name']??'').' '.($r['last_name']??'')).'</td>';
                    echo '<td>'.htmlspecialchars($r['description']??'').'</td>';
                    echo '<td>'.htmlspecialchars($r['status']??'').'</td>';
                    echo '<td>'.htmlspecialchars($r['created_at']??'').'</td>';
                    echo '<td>';
                    echo '<button class="btn btn-sm btn-outline-secondary me-1" onclick="editCase('.(int)$r['id'].')">Edit</button>';
                    echo '<button class="btn btn-sm btn-outline-danger" onclick="delCase('.(int)$r['id'].')">Delete</button>';
                    echo '<a href="lab_export_single.php?id='.(int)$r['id'].'" class="btn btn-sm btn-outline-success" target="_blank">Download</a>';

                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Lab Case Modal -->
    <div class="modal fade" id="labModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Add Lab Case</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <form id="labForm">
              <input type="hidden" name="id" />
              <div class="mb-2">
                <label class="form-label">Patient</label>
                <select class="form-select" name="patient_id" id="labPatient" required></select>
              </div>
              <div class="mb-2"><input class="form-control" name="description" placeholder="Description" required></div>
              <div class="mb-2">
                <select class="form-select" name="status">
                  <option value="open">open</option>
                  <option value="in-progress">in-progress</option>
                  <option value="done">done</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button class="btn btn-primary" onclick="saveCase()">Save</button>
          </div>
        </div>
      </div>
    </div>

    <script>
    async function loadPatients() {
      const res = await fetch('/admin/patient_list.php?limit=1000', { credentials:'same-origin' });
      const json = await res.json();
      if (json.status !== 'success') return;
      const sel = document.getElementById('labPatient');
      sel.innerHTML = json.data.map(p=>`<option value="${p.id}">${p.first_name} ${p.last_name||''}</option>`).join('');
    }
    document.getElementById('labModal').addEventListener('show.bs.modal', loadPatients);
    async function saveCase(){
      const f = document.getElementById('labForm');
      const data = Object.fromEntries(new FormData(f).entries());
      if (data.id) {
        await fetch('/admin/lab_case_update.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
      } else {
        // send description/status/patient_id as JSON
        await fetch('/admin/lab_case_create.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ patient_id: data.patient_id, description: data.description, status: data.status }) });
      }
      bootstrap.Modal.getInstance(document.getElementById('labModal')).hide();
      location.reload();
    }
    async function editCase(id){
      const res = await fetch('/admin/lab_case_get.php?id=' + id, { credentials:'same-origin' });
      const json = await res.json(); if (json.status !== 'success') return alert('Load failed');
      await loadPatients();
      const f = document.getElementById('labForm');
      f.id.value = json.data.id; f.patient_id.value = json.data.patient_id; f.description.value = json.data.description||''; f.status.value = json.data.status||'open';
      document.querySelector('#labModal .modal-title').textContent = 'Edit Lab Case';
      new bootstrap.Modal(document.getElementById('labModal')).show();
    }
    async function delCase(id){
      if (!confirm('Delete case?')) return;
      await fetch('/admin/lab_case_delete.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id }) });
      location.reload();
    }
    </script>
    </div>
</body>
</html>






