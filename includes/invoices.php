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
    <title>Invoices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Invoices</h4>

        </div>
        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#invModal">Add Invoice</button>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>#</th><th>Patient</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT i.id, i.total, i.paid, i.balance, i.status, i.created_at, p.first_name, p.last_name FROM invoices i JOIN patients p ON i.patient_id=p.id ORDER BY i.created_at DESC LIMIT 50");
                    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<tr>';
                        echo '<td>'.htmlspecialchars($r['id']).'</td>';
                        echo '<td>'.htmlspecialchars(($r['first_name']??'').' '.($r['last_name']??'')).'</td>';
                        echo '<td>'.htmlspecialchars($r['total']).'</td>';
                        echo '<td>'.htmlspecialchars($r['paid']).'</td>';
                        echo '<td>'.htmlspecialchars($r['balance']).'</td>';
                        echo '<td>'.htmlspecialchars($r['status']).'</td>';
                        echo '<td>'.htmlspecialchars($r['created_at']).'</td>';
                        echo '<td>';
                        echo '<button class="btn btn-sm btn-outline-secondary me-1" onclick="editInv('.(int)$r['id'].')">Edit</button>';
                        echo '<button class="btn btn-sm btn-outline-danger me-1" onclick="delInv('.(int)$r['id'].')">Delete</button>';
                        echo '<a class="btn btn-sm btn-outline-primary" href="/admin/invoice_download.php?id='.(int)$r['id'].'" target="_blank">Download</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Invoice Modal -->
        <div class="modal fade" id="invModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header"><h5 class="modal-title">Add Invoice</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <form id="invForm">
                  <input type="hidden" name="id" />
                  <div class="mb-2">
                    <label class="form-label">Patient</label>
                    <select class="form-select" name="patient_id" id="invPatient" required></select>
                  </div>
                  <div class="mb-2"><input class="form-control" name="total" type="number" step="0.01" placeholder="Total" required></div>
                  <div class="mb-2"><input class="form-control" name="paid" id="invPaid" type="number" step="0.01" placeholder="Paid (optional)"></div>
                  <div class="mb-2">
                    <input class="form-control" name="balance" id="invBalance" type="number" step="0.01" placeholder="Balance" readonly>
                  </div>
                  <div class="mb-2">
                    <select class="form-select" name="status">
                      <option value="unpaid">unpaid</option>
                      <option value="partial">partial</option>
                      <option value="paid">paid</option>
                    </select>
                  </div>
                  <div class="mb-2"><textarea class="form-control" name="notes" placeholder="Notes"></textarea></div>
                </form>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" onclick="saveInv()">Save</button>
              </div>
            </div>
          </div>
        </div>

        <script>
        async function loadInvPatients(){
          const res = await fetch('/admin/patient_list.php?limit=1000', { credentials:'same-origin' });
          const json = await res.json();
          if (json.status !== 'success') return;
          const sel = document.getElementById('invPatient');
          sel.innerHTML = json.data.map(p=>`<option value="${p.id}">${p.first_name} ${p.last_name||''}</option>`).join('');
        }
        document.getElementById('invModal').addEventListener('show.bs.modal', loadInvPatients);
        function computeBalance(){
          const t = parseFloat(document.querySelector('#invForm [name="total"]').value || '0') || 0;
          const p = parseFloat(document.querySelector('#invForm [name="paid"]').value || '0') || 0;
          const b = (t - p).toFixed(2);
          document.getElementById('invBalance').value = b;
        }
        document.getElementById('invForm').addEventListener('input', function(e){
          if (e.target && (e.target.name === 'total' || e.target.name === 'paid')) computeBalance();
        });
        async function saveInv(){
          const f = document.getElementById('invForm');
          const data = Object.fromEntries(new FormData(f).entries());
          let res;
          if (data.id) {
            res = await fetch('/admin/invoice_update.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
          } else {
            res = await fetch('/admin/invoice_create.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ patient_id: data.patient_id, total: parseFloat(data.total||0), paid: parseFloat(data.paid||0), status: data.status, notes: data.notes||'' }) });
          }
          if (!res.ok) { const t = await res.text(); alert('Save failed: ' + t); return; }
          location.reload();
        }
        async function editInv(id){
          const res = await fetch('/admin/invoice_get.php?id='+id, { credentials:'same-origin' });
          const json = await res.json(); if (json.status !== 'success') return alert('Load failed');
          await loadInvPatients();
          const f = document.getElementById('invForm');
          f.id.value = json.data.id; f.patient_id.value = json.data.patient_id; f.total.value = json.data.total||0; f.paid.value = json.data.paid||0; f.status.value = json.data.status||'unpaid'; f.notes.value = json.data.notes||''; computeBalance();
          document.querySelector('#invModal .modal-title').textContent = 'Edit Invoice';
          new bootstrap.Modal(document.getElementById('invModal')).show();
        }
        async function delInv(id){
          if (!confirm('Delete invoice?')) return;
          await fetch('/admin/invoice_delete.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id }) });
          location.reload();
        }
        </script>
    </div>
</body>
</html>

