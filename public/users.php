<?php
// User list - administrators only
require __DIR__ . '/../includes/init.php';
require_admin();

$users = get_rows('select u.user_id, u.username, u.full_name, r.role_name, u.is_active, u.created_at
                   from app_user u
                   join user_role r on r.role_id = u.role_id
                   order by u.user_id');

$page_title = 'Users';
$active = 'users';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Users</h1>
  <a class="btn btn-primary" href="user_form.php">+ Add User</a>
</div>
<div class="card"><div class="card-body"><div class="table-responsive">
  <table class="table table-striped align-middle mb-0">
    <thead><tr><th>ID</th><th>Username</th><th>Full name</th><th>Role</th><th>Status</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u) { ?>
      <tr>
        <td><?php echo $u['user_id']; ?></td>
        <td><?php echo e($u['username']); ?></td>
        <td><?php echo e($u['full_name']); ?></td>
        <td><?php echo e($u['role_name']); ?></td>
        <td>
          <?php if ($u['is_active']) { ?><span class="badge text-bg-success">Active</span>
          <?php } else { ?><span class="badge text-bg-secondary">Inactive</span><?php } ?>
        </td>
        <td><?php echo show_date($u['created_at']); ?></td>
        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="user_form.php?id=<?php echo $u['user_id']; ?>">Edit</a></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
