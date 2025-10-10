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
    <title>Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4> All Messages</h4>
            <div>
                <a href="dashboard.php" class="btn btn-sm btn-secondary me-2">Back</a>
                <button class="btn btn-sm btn-outline-danger" onclick="delAll()">Delete All</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped" id="tbl"></table>
        </div>
    </div>

<script>
async function loadMsgs(){
  const res = await fetch('/admin/messages_list.php', { credentials:'same-origin' });
  const json = await res.json();
  const tbl = document.getElementById('tbl');
  if (json.status !== 'success') { tbl.innerHTML = '<tbody><tr><td>Error loading</td></tr></tbody>'; return; }
  let html = '<thead><tr><th>#</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Created</th><th>Action</th></tr></thead><tbody>';
  for (const r of json.data){
    html += `<tr>
      <td>${r.id}</td>
      <td>${r.full_name||''}</td>
      <td>${r.email||''}</td>
      <td>${r.subject||''}</td>
      <td>${(r.message||'').toString().slice(0,120)}</td>
      <td>${r.created_at||''}</td>
      <td><button class="btn btn-sm btn-outline-danger" onclick="delOne(${r.id})">Delete</button></td>
    </tr>`;
  }
  html += '</tbody>';
  tbl.innerHTML = html;
}
async function delOne(id){
  if (!confirm('Delete this message?')) return;
  await fetch('/admin/messages_delete.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id }) });
  loadMsgs();
}
async function delAll(){
  if (!confirm('Delete ALL messages?')) return;
  await fetch('/admin/messages_delete_all.php', { method:'POST', credentials:'same-origin' });
  loadMsgs();
}
loadMsgs();
</script>
</body>
</html>

