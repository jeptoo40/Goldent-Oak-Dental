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
    <title>Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Appointments</h4>
          
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <form class="row gy-2 gx-2" id="filters">
                <div class="col-auto"><input type="date" name="from" class="form-control" placeholder="From"></div>
                <div class="col-auto"><input type="date" name="to" class="form-control" placeholder="To"></div>
                <div class="col-auto">
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option>pending</option>
                        <option>confirmed</option>
                        <option>completed</option>
                        <option>cancelled</option>
                    </select>
                </div>
                <div class="col-auto"><input type="text" name="q" class="form-control" placeholder="Search patient"></div>
                <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
            </form>
            
        </div>
        <div class="table-responsive">
            <table class="table table-striped" id="tbl"></table>
        </div>
    </div>

<script>
async function load() {
  const form = document.getElementById('filters');
  const params = new URLSearchParams(new FormData(form));
  const res = await fetch('/admin/appointment_list.php?' + params.toString(), { credentials:'same-origin' });
  const data = await res.json();
  const tbl = document.getElementById('tbl');
  if (data.status !== 'success') { tbl.innerHTML = '<tbody><tr><td>Error loading</td></tr></tbody>'; return; }
  let html = '<thead><tr><th>Patient</th><th>Service</th><th>Appointment Date</th><th>Time</th><th>Note</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
  for (const r of data.data) {
    html += `<tr>
      <td>${r.first_name} ${r.last_name || ''}</td>
      <td>${r.service ?? ''}</td>
      <td>${r.appointment_date}</td>
      <td>${r.time_slot ?? ''}</td>
      <td>${(r.notes ?? '').toString().slice(0,80)}</td>
      <td>${r.status}</td>
      <td>
        <a class="btn btn-sm btn-outline-primary" href="${(await contactLink(r.id,'whatsapp'))}">WhatsApp</a>
        <button class="btn btn-sm btn-outline-secondary" onclick="reschedule(${r.id})">Reschedule</button>
        <button class="btn btn-sm btn-outline-danger" onclick="del(${r.id})">Delete</button>
        <a class="btn btn-sm btn-outline-dark" href="files.php#?patient_id=${encodeURIComponent(r.patient_id||'')}">Files</a>
      </td>
    </tr>`;
  }
  html += '</tbody>';
  tbl.innerHTML = html;
}
async function contactLink(id, kind) {
  const res = await fetch('/admin/appointment_contact_links.php?appointment_id=' + id, { credentials:'same-origin' });
  const json = await res.json();
  return (json.data && json.data[kind]) || '#';
}
async function del(id) {
  if (!confirm('Delete appointment?')) return;
  await fetch('/admin/appointment_delete.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ appointment_id:id }) });
  load();
}
async function reschedule(id) {
  const date = prompt('New date (YYYY-MM-DD):'); if (!date) return;
  const time = prompt('New time slot:'); if (!time) return;
  await fetch('/admin/appointment_reschedule.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ appointment_id:id, appointment_date:date, time_slot:time }) });
  load();
}

document.getElementById('filters').addEventListener('submit', e => { e.preventDefault(); load(); });
load();
</script>
</body>
</html>

