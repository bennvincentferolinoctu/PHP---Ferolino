<?php
require __DIR__ . '/../includes/init.php';
require_login();

//Read the search / filter values from the URL
$search  = trim(isset($_GET['q']) ? $_GET['q'] : '');
$address = isset($_GET['address']) ? $_GET['address'] : '';
$from    = isset($_GET['from']) ? $_GET['from'] : '';
$to      = isset($_GET['to']) ? $_GET['to'] : '';

//Build the WHERE part of the query
$where  = '1=1';
$params = array();

if ($search != '') {
    $where .= ' and (t.first_name ilike ? or t.last_name ilike ? or t.contact_number ilike ? or t.address ilike ?)';
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($address != '') {
    $where .= ' and t.address = ?';
    $params[] = $address;
}
if (valid_date($from)) {
    $where .= ' and t.date_registered >= ?';
    $params[] = $from;
}
if (valid_date($to)) {
    $where .= ' and t.date_registered <= ?';
    $params[] = $to;
}

//Sorting (only these columns are allowed)
$sort_columns = array(
    'id' => 't.farmer_id', 'first_name' => 't.first_name', 'last_name' => 't.last_name',
    'contact' => 't.contact_number', 'address' => 't.address',
    'registered' => 't.date_registered', 'farms' => 'farm_count'
);
list($sort, $dir) = get_sort($sort_columns, 'id');

//Pagination
$per_page = 10;
$total = get_value('select count(*) from farmer t where ' . $where, $params);
$pages = max(1, ceil($total / $per_page));
$page  = min(get_page(), $pages);
$offset = ($page - 1) * $per_page;

//5. Get the rows for this page
$sql = 'select t.*, (select count(*) from farm x where x.farmer_id = t.farmer_id) as farm_count
        from farmer t
        where ' . $where . '
        order by ' . $sort_columns[$sort] . ' ' . $dir . ', t.farmer_id desc
        limit ' . (int)$per_page . ' offset ' . (int)$offset;
$farmers = get_rows($sql, $params);

$address_options = get_rows('select distinct address as id, address as label from farmer order by 1');

$page_title = 'Farmers';
$active = 'farmers';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Farmers</h1>
  <a class="btn btn-primary" href="farmer_form.php">+ Add Farmer</a>
</div>

<div class="card mb-3"><div class="card-body">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-12 col-md-4 col-lg"><label class="form-label small mb-1">Search</label>
      <input class="form-control form-control-sm" name="q" value="<?php echo e($search); ?>" placeholder="Search..."></div>
    <?php filter_select('address', 'Address', $address_options, $address); ?>
    <div class="col-6 col-md-3 col-lg"><label class="form-label small mb-1">Registered from</label>
      <input type="date" class="form-control form-control-sm" name="from" value="<?php echo e($from); ?>"></div>
    <div class="col-6 col-md-3 col-lg"><label class="form-label small mb-1">to</label>
      <input type="date" class="form-control form-control-sm" name="to" value="<?php echo e($to); ?>"></div>
    <div class="col-12 col-lg-auto d-flex gap-2">
      <button class="btn btn-primary btn-sm">Apply</button>
      <a class="btn btn-light btn-sm" href="farmers.php">Reset</a>
    </div>
  </form>
</div></div>

<div class="card"><div class="card-body">
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead>
        <tr>
          <th><?php echo sort_link('ID', 'id', $sort, $dir); ?></th>
          <th><?php echo sort_link('First name', 'first_name', $sort, $dir); ?></th>
          <th><?php echo sort_link('Last name', 'last_name', $sort, $dir); ?></th>
          <th><?php echo sort_link('Contact', 'contact', $sort, $dir); ?></th>
          <th><?php echo sort_link('Address', 'address', $sort, $dir); ?></th>
          <th><?php echo sort_link('Registered', 'registered', $sort, $dir); ?></th>
          <th><?php echo sort_link('Farms', 'farms', $sort, $dir); ?></th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($farmers as $f) { ?>
        <tr>
          <td><?php echo $f['farmer_id']; ?></td>
          <td><?php echo e($f['first_name']); ?></td>
          <td><?php echo e($f['last_name']); ?></td>
          <td><?php echo e($f['contact_number']); ?></td>
          <td><?php echo e($f['address']); ?></td>
          <td><?php echo show_date($f['date_registered']); ?></td>
          <td><?php echo $f['farm_count']; ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-secondary" href="farmer_view.php?id=<?php echo $f['farmer_id']; ?>">View</a>
            <a class="btn btn-sm btn-outline-primary" href="farmer_form.php?id=<?php echo $f['farmer_id']; ?>">Edit</a>
            <?php echo delete_button('farmer_delete.php?id=' . $f['farmer_id'], $f['first_name'] . ' ' . $f['last_name']); ?>
          </td>
        </tr>
      <?php } ?>
      <?php if (count($farmers) == 0) { ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No records found.</td></tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
    <div class="small text-muted"><?php echo showing_text($total, $page, $per_page); ?></div>
    <?php show_pager($total, $page, $per_page); ?>
  </div>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
