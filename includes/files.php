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
    <title>Files</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>File Management</h4>
   
        </div>
        <form id="uploadForm" class="row gy-2 gx-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label mb-0">Patient</label>
                <select class="form-select" name="patient_id" id="filePatient" required></select>
            </div>
            <div class="col-auto">
                <input class="form-control" type="number" name="visit_id" placeholder="Visit ID (optional)">
            </div>
            <div class="col-auto">
                <input class="form-control" type="file" name="file" required>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" type="submit">Upload</button>
            </div>
        </form>

        <div class="mb-2">
            <label class="form-label mb-1">Filter by Patient (optional)</label>
            <select id="patientFilter" class="form-select"></select>
        </div>
        <div class="table-responsive">
            <table class="table table-striped" id="tbl"></table>
        </div>
    </div>

<script>
// Initialize both in standalone load and when injected via SPA
async function initFilesPage(){
  async function loadPatientOptions() {
    try {
      const res = await fetch('/admin/patient_list.php?limit=1000', { credentials:'same-origin' });
      const text = await res.text();
      let json; try { json = JSON.parse(text); } catch(_) {}
      if (!res.ok || !json || json.status !== 'success') {
        console.error('patient_list failure:', text);
        alert('Could not load patients. Please ensure you are logged in as admin.');
        return false;
      }
      const opts = ['<option value="">-- All Patients --</option>'].concat(json.data.map(p=>`<option value="${p.id}">${p.first_name} ${p.last_name||''}</option>`));
      const filterSel = document.getElementById('patientFilter');
      const uploadSel = document.getElementById('filePatient');
      if (filterSel) filterSel.innerHTML = opts.join('');
      if (uploadSel) uploadSel.innerHTML = json.data.map(p=>`<option value="${p.id}">${p.first_name} ${p.last_name||''}</option>`).join('');
      return true;
    } catch (e) {
      console.error(e);
      alert('Failed to load patient list.');
      return false;
    }
  }
  const ok = await loadPatientOptions();
  if (ok) {
    const hash = location.hash || '';
    const q = hash.startsWith('#?') ? new URLSearchParams(hash.slice(2)) : null;
    const pid = q ? q.get('patient_id') : null;
    if (pid) {
      const filterSel = document.getElementById('patientFilter');
      const uploadSel = document.getElementById('filePatient');
      if (filterSel) filterSel.value = pid;
      if (uploadSel) uploadSel.value = pid;
    }
    // Populate table after patients load
    listFiles();
  }
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initFilesPage);
} else {
  initFilesPage();
}
async function listFiles() {
  const pid = document.getElementById('patientFilter').value;
  const url = '/admin/file_list.php' + (pid ? ('?patient_id=' + encodeURIComponent(pid)) : '');
  const res = await fetch(url, { credentials:'same-origin' });
  const data = await res.json();
  const tbl = document.getElementById('tbl');
  if (data.status !== 'success') { tbl.innerHTML = '<tbody><tr><td>Error loading</td></tr></tbody>'; return; }
  let html = '<thead><tr><th>Patient</th><th>Type</th><th>Size</th><th>Uploaded</th><th>Actions</th></tr></thead><tbody>';
  for (const r of data.data) {
    html += `<tr>
      <td>${(r.first_name||'')+' '+(r.last_name||'')}</td>
      <td>${r.file_type||''}</td>
      <td>${r.file_size ? (Math.round(r.file_size/1024)+' KB') : ''}</td>
      <td>${r.uploaded_at||''}</td>
      <td>
        <a class="btn btn-sm btn-outline-primary me-1" href="/admin/file_download.php?id=${r.id}">Download</a>
        <button class="btn btn-sm btn-outline-danger" onclick="del(${r.id})">Delete</button>
      </td>
    </tr>`;
  }
  html += '</tbody>';
  tbl.innerHTML = html;
}
async function del(id) {
  if (!confirm('Delete file?')) return;
  await fetch('/admin/file_delete.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id }) });
  listFiles();
}
document.getElementById('uploadForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await fetch('/admin/file_upload.php', { method:'POST', credentials:'same-origin', body: fd });
  const json = await res.json();
  if (json.status === 'success') { e.target.reset(); listFiles(); } else { alert(json.message || 'Upload failed'); }
});
document.getElementById('patientFilter').addEventListener('change', listFiles);
</script>
</body>
</html>

