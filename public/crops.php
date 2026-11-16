<?php
require __DIR__ . '/../includes/init.php';
require_login();

$search   = trim(isset($_GET['q']) ? $_GET['q'] : '');
$category = isset($_GET['category']) ? $_GET['category'] : '';
$season   = isset($_GET['season']) ? $_GET['season'] : '';

$where  = '1=1';
$params = array();
if ($search != '') {
    $where .= ' and (t.crop_name ilike ? or t.variety ilike ? or cc.crop_category_name ilike ?)';
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($category != '') { $where .= ' and t.crop_category_id = ?'; $params[] = (int)$category; }
if ($season != '')   { $where .= ' and t.season_id = ?';        $params[] = (int)$season; }

$sort_columns = array(
    'id' => 't.crop_id', 'crop' => 't.crop_name', 'variety' => 't.variety',
    'category' => 'cc.crop_category_name', 'days' => 't.growing_period_days', 'season' => 's.season_name'
);
list($sort, $dir) = get_sort($sort_columns, 'id');

$per_page = 10;
$from_sql = 'from crop t
             join crop_category cc on cc.crop_category_id = t.crop_category_id
             join season s on s.season_id = t.season_id';
$total  = get_value('select count(*) ' . $from_sql . ' where ' . $where, $params);
$pages  = max(1, ceil($total / $per_page));
$page   = min(get_page(), $pages);
$offset = ($page - 1) * $per_page;

$sql = 'select t.*, cc.crop_category_name, s.season_name ' . $from_sql . ' where ' . $where
     . ' order by ' . $sort_columns[$sort] . ' ' . $dir . ', t.crop_id desc'
     . ' limit ' . (int)$per_page . ' offset ' . (int)$offset;
$crops = get_rows($sql, $params);

$page_title = 'Crops';
$active = 'crops';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Crops</h1>
  <a class="btn btn-primary" href="crop_form.php">+ Add Crop</a>
</div>

<div class="card mb-3"><div class="card-body">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-12 col-md-4 col-lg"><label class="form-label small mb-1">Search</label>
      <input class="form-control form-control-sm" name="q" value="<?php echo e($search); ?>" placeholder="Search..."></div>
    <?php
    filter_select('category', 'Category', options_category(), $category);
    filter_select('season', 'Season', options_season(), $season);
    ?>
    <div class="col-12 col-lg-auto d-flex gap-2">
      <button class="btn btn-primary btn-sm">Apply</button>
      <a class="btn btn-light btn-sm" href="crops.php">Reset</a>
    </div>
  </form>
</div></div>

<div class="card"><div class="card-body">
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead>
        <tr>
          <th><?php echo sort_link('ID', 'id', $sort, $dir); ?></th>
          <th><?php echo sort_link('Crop', 'crop', $sort, $dir); ?></th>
          <th><?php echo sort_link('Variety', 'variety', $sort, $dir); ?></th>
          <th><?php echo sort_link('Category', 'category', $sort, $dir); ?></th>
          <th><?php echo sort_link('Growing days', 'days', $sort, $dir); ?></th>
          <th><?php echo sort_link('Season', 'season', $sort, $dir); ?></th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($crops as $c) { ?>
        <tr>
          <td><?php echo $c['crop_id']; ?></td>
          <td><?php echo e($c['crop_name']); ?></td>
          <td><?php echo e($c['variety']); ?></td>
          <td><?php echo e($c['crop_category_name']); ?></td>
          <td><?php echo $c['growing_period_days']; ?></td>
          <td><?php echo e($c['season_name']); ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-secondary" href="crop_view.php?id=<?php echo $c['crop_id']; ?>">View</a>
            <a class="btn btn-sm btn-outline-primary" href="crop_form.php?id=<?php echo $c['crop_id']; ?>">Edit</a>
            <?php echo delete_button('crop_delete.php?id=' . $c['crop_id'], $c['crop_name'] . ' (' . $c['variety'] . ')'); ?>
          </td>
        </tr>
      <?php } ?>
      <?php if (count($crops) == 0) { ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No records found.</td></tr>
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
