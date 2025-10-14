<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  header('Location: ../Admin.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Patients</h4>
          
        </div>
        <div class="mb-3 d-flex gap-2">
            <input class="form-control" id="q" placeholder="Search name/phone/email">
            <button class="btn btn-primary" onclick="load()">Search</button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPatientModal">Add Patient</button>
        </div>
        <button class="btn btn-outline-info" onclick="downloadPatients()">Download List</button>

        <div class="table-responsive">
            <table class="table table-striped" id="tbl"></table>
        </div>
    </div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Patient</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
     
      <div class="modal-body">
        <form id="patientForm">
          <input type="hidden" name="id"/>
          <div class="mb-2"><input class="form-control" name="first_name" placeholder="First name" required></div>
          <div class="mb-2"><input class="form-control" name="last_name" placeholder="Last name"></div>
          <div class="mb-2"><input class="form-control" name="phone" placeholder="Phone"></div>
          <div class="mb-2"><input class="form-control" name="email" placeholder="Email" type="email"></div>
          <div class="mb-2"><input class="form-control" name="address" placeholder="Address"></div>
          <div class="mb-2"><input class="form-control" name="insurance_provider" placeholder="Insurance provider"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" onclick="savePatient()">Save</button>
      </div>
    </div>
  </div>
  </div>

<script>
// Auto-open add patient modal when requested via query (?open=add)
document.addEventListener('DOMContentLoaded', ()=>{
  // Support query param even when loaded inside SPA (hash contains encoded URL)
  let qs = '';
  if ((location.hash || '').startsWith('#!')) {
    const decoded = decodeURIComponent(location.hash.slice(2));
    const idx = decoded.indexOf('?');
    if (idx !== -1) qs = decoded.slice(idx + 1);
  } else {
    qs = (location.search || '').replace(/^\?/,'');
  }
  const params = new URLSearchParams(qs);
  if (params.get('open') === 'add') {
    document.querySelector('#addPatientModal .modal-title').textContent = 'Add Patient';
    new bootstrap.Modal(document.getElementById('addPatientModal')).show();
  }
});
async function load(page=1) {
  const q = document.getElementById('q').value;
  const res = await fetch(`/admin/patient_list.php?q=${encodeURIComponent(q)}&page=${page}&limit=20`, { credentials:'same-origin' });
  const data = await res.json();
  const tbl = document.getElementById('tbl');
  if (data.status !== 'success') { tbl.innerHTML = '<tbody><tr><td>Error loading</td></tr></tbody>'; return; }
  let html = '<thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Insurance</th><th>Registered</th><th>Actions</th></tr></thead><tbody>';
  for (const r of data.data) {
    html += `<tr>
      <td>${r.id}</td>
      <td>${r.first_name} ${r.last_name ?? ''}</td>
      <td>${r.email ?? ''}</td>
      <td>${r.phone ?? ''}</td>
      <td>${r.insurance_provider ?? ''}</td>
      <td>${r.created_at}</td>
      <td>
        <button class="btn btn-sm btn-outline-secondary" onclick="editPatient(${r.id})">Edit</button>
        <button class="btn btn-sm btn-outline-danger" onclick="delPatient(${r.id})">Delete</button>
      </td>
    </tr>`;
  }
  html += '</tbody>';
  tbl.innerHTML = html;
}
async function editPatient(id){
  try {
    const res = await fetch('/admin/patient_get.php?id=' + encodeURIComponent(id), { credentials:'same-origin' });
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch(_) { json = null; }
    if (!res.ok || !json || json.status !== 'success') {
      alert((json && json.message) ? ('Failed to load patient: ' + json.message) : 'Failed to load patient');
      console.error('patient_get response:', text);
      return;
    }
    const r = json.data || {};
    const m = new bootstrap.Modal(document.getElementById('addPatientModal'));
    document.querySelector('#addPatientModal .modal-title').textContent = 'Edit Patient';
    const f = document.getElementById('patientForm');
    f.id.value = r.id||'';
    f.first_name.value = r.first_name||'';
    f.last_name.value = r.last_name||'';
    f.phone.value = r.phone||'';
    f.email.value = r.email||'';
    f.address.value = r.address||'';
    m.show();
  } catch (e) {
    alert('Failed to load patient');
    console.error(e);
  }
}
async function savePatient(){
  const f = document.getElementById('patientForm');
  try {
    const formData = new FormData(f);
    const id = (formData.get('id')||'').trim();
    let res;
    if (id) {
      const payload = Object.fromEntries(formData.entries());
      res = await fetch('/admin/patient_update.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    } else {
      res = await fetch('/admin/patient_create.php', { method:'POST', credentials:'same-origin', body: formData });
    }
    const text = await res.text();
    let json; try { json = JSON.parse(text); } catch(_) {}
    if (!res.ok || (json && json.status === 'error')) {
      alert((json && json.message) ? json.message : 'Save failed');
      console.error('savePatient response:', text);
      return;
    }
    bootstrap.Modal.getInstance(document.getElementById('addPatientModal')).hide();
    f.reset();
    load();
  } catch (e) {
    alert('Save failed');
    console.error(e);
  }
}
async function delPatient(id){
  if (!confirm('Delete patient?')) return;
  await fetch('/admin/patient_delete.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id }) });
  load();
}
load();

function downloadPatients() {
  window.location.href = '/admin/patient_export.php';
}

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

