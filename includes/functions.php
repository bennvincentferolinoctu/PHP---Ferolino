<?php
// Run a SELECT and return ALL rows
function get_rows($sql, $params = array()) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Run a SELECT and return ONE row (or null if nothing found)
function get_row($sql, $params = array()) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    if ($row) {
        return $row;
    }
    return null;
}

// Run a SELECT and return a single value, e.g. a count
function get_value($sql, $params = array()) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row) {
        return $row[0];
    }
    return null;
}

// Run INSERT / UPDATE / DELETE
function run_query($sql, $params = array()) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

// ===== Output helpers =====

// Make text safe to print in HTML
function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function go_to($url) {
    header('Location: ' . $url);
    exit;
}

// Flash messages: saved in the session, shown once on the next page
function set_message($type, $text) {
    $_SESSION['messages'][] = array($type, $text);
}

function show_messages() {
    if (isset($_SESSION['messages'])) {
        foreach ($_SESSION['messages'] as $m) {
            echo '<div class="alert alert-' . e($m[0]) . ' alert-dismissible fade show" role="alert">';
            echo e($m[1]);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }
        unset($_SESSION['messages']);
    }
}

// Formatting
function show_date($d) {
    if ($d == '') { return '-'; }
    return date('M d, Y', strtotime($d));
}
function show_datetime($d) {
    if ($d == '') { return '-'; }
    return date('M d, Y h:i A', strtotime($d));
}
function show_money($n) {
    return '&#8369;' . number_format((float)$n, 2);
}
function show_number($n) {
    return number_format((float)$n, 2);
}

// Colored badge for status and grade names
function badge($name) {
    $colors = array('Planted' => 'secondary', 'Growing' => 'success', 'Harvested' => 'primary',
                    'Failed' => 'danger', 'Grade A' => 'success', 'Grade B' => 'info', 'Grade C' => 'warning');
    $color = 'light';
    if (isset($colors[$name])) {
        $color = $colors[$name];
    }
    return '<span class="badge text-bg-' . $color . '">' . e($name) . '</span>';
}

// ===== Login / roles =====

function require_login() {
    if (!isset($_SESSION['user'])) {
        go_to('login.php');
    }
}

function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role_id'] == 1;
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        set_message('danger', 'You do not have permission to open that page.');
        go_to('index.php');
    }
}

function current_user_id() {
    if (isset($_SESSION['user'])) {
        return $_SESSION['user']['user_id'];
    }
    return null;
}

// ===== CSRF protection (stops fake forms from other websites) =====

function csrf_field() {
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function check_csrf() {
    $sent = '';
    if (isset($_POST['csrf'])) {
        $sent = $_POST['csrf'];
    }
    if (!isset($_SESSION['csrf']) || $sent != $_SESSION['csrf']) {
        set_message('danger', 'Your form expired. Please try again.');
        go_to('index.php');
    }
}

// ===== Validation helpers =====

function valid_date($text) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
        return false;
    }
    $p = explode('-', $text);
    if (!checkdate((int)$p[1], (int)$p[2], (int)$p[0])) {
        return false;
    }
    return $text >= '2000-01-01' && $text <= '2100-12-31';
}

// A number with at most 2 decimals, e.g. 12 or 12.5 or 12.55
function valid_decimal($text) {
    return preg_match('/^\d{1,9}(\.\d{1,2})?$/', $text) == 1;
}

function valid_whole_number($text) {
    return preg_match('/^\d{1,9}$/', $text) == 1;
}

// Is the chosen dropdown value one of the allowed options?
function in_options($options, $value) {
    foreach ($options as $o) {
        if ((string)$o['id'] === (string)$value) {
            return true;
        }
    }
    return false;
}

// Turn a database error into a message that is safe to show
function friendly_db_error($e, $duplicate_text) {
    error_log('DB error: ' . $e->getMessage());
    $code = $e->getCode();
    if ($code == '23505') {
        return $duplicate_text;
    }
    if ($code == '23503') {
        return 'This record is linked to other records, or the selected related record does not exist.';
    }
    if ($code == '23514') {
        // our own trigger messages (e.g. harvest before planting) are safe to show
        if (preg_match('/ERROR:\s*(.+?)(\n|CONTEXT|$)/s', $e->getMessage(), $m)) {
            if (strpos($m[1], 'violates check constraint') === false) {
                return trim($m[1]);
            }
        }
        return 'One of the values breaks a data rule. Check the numbers and dates.';
    }
    return 'The operation could not be completed. Please check your input and try again.';
}

// ===== Dropdown lists (value stored = id, text shown = label) =====

function options_soil()       { return get_rows('select soil_type_id as id, soil_type_name as label from soil_type order by 2'); }
function options_irrigation() { return get_rows('select irrigation_type_id as id, irrigation_type_name as label from irrigation_type order by 2'); }
function options_category()   { return get_rows('select crop_category_id as id, crop_category_name as label from crop_category order by 2'); }
function options_season()     { return get_rows('select season_id as id, season_name as label from season order by 1'); }
function options_status()     { return get_rows('select status_id as id, status_name as label from planting_status order by 1'); }
function options_grade()      { return get_rows('select grade_id as id, grade_name as label from quality_grade order by 1'); }
function options_remark()     { return get_rows('select remark_id as id, remark_description as label from harvest_remark order by 1'); }
function options_farmer()     { return get_rows("select farmer_id as id, last_name || ', ' || first_name as label from farmer order by 2"); }
function options_crop()       { return get_rows("select crop_id as id, crop_name || ' - ' || variety as label from crop order by 2"); }
function options_farm() {
    return get_rows("select f.farm_id as id, f.farm_name || ' (' || fr.last_name || ')' as label
                     from farm f join farmer fr on fr.farmer_id = f.farmer_id order by 2");
}
function options_planting() {
    return get_rows("select p.planting_id as id,
                            '#' || p.planting_id || ' ' || f.farm_name || ' / ' || c.crop_name || ' (' || to_char(p.planting_date, 'YYYY-MM-DD') || ')' as label
                     from planting p join farm f on f.farm_id = p.farm_id join crop c on c.crop_id = p.crop_id
                     order by p.planting_id desc");
}

// ===== Form field helpers (print one labelled input with its error message) =====

function field_text($name, $label, $value, $errors, $maxlength, $help = '') {
    echo '<div class="col-md-6"><label class="form-label" for="' . $name . '">' . e($label) . ' <span class="text-danger">*</span></label>';
    $bad = isset($errors[$name]) ? ' is-invalid' : '';
    echo '<input type="text" class="form-control' . $bad . '" id="' . $name . '" name="' . $name . '" value="' . e($value) . '" maxlength="' . $maxlength . '" required>';
    field_footer($name, $errors, $help);
}

function field_number($name, $label, $value, $errors, $min, $max, $step, $help = '') {
    echo '<div class="col-md-6"><label class="form-label" for="' . $name . '">' . e($label) . ' <span class="text-danger">*</span></label>';
    $bad = isset($errors[$name]) ? ' is-invalid' : '';
    echo '<input type="number" class="form-control' . $bad . '" id="' . $name . '" name="' . $name . '" value="' . e($value) . '" min="' . $min . '" max="' . $max . '" step="' . $step . '" required>';
    field_footer($name, $errors, $help);
}

function field_date($name, $label, $value, $errors) {
    echo '<div class="col-md-6"><label class="form-label" for="' . $name . '">' . e($label) . ' <span class="text-danger">*</span></label>';
    $bad = isset($errors[$name]) ? ' is-invalid' : '';
    echo '<input type="date" class="form-control' . $bad . '" id="' . $name . '" name="' . $name . '" value="' . e($value) . '" min="2000-01-01" max="2100-12-31" required>';
    field_footer($name, $errors, '');
}

function field_select($name, $label, $options, $value, $errors) {
    echo '<div class="col-md-6"><label class="form-label" for="' . $name . '">' . e($label) . ' <span class="text-danger">*</span></label>';
    $bad = isset($errors[$name]) ? ' is-invalid' : '';
    echo '<select class="form-select' . $bad . '" id="' . $name . '" name="' . $name . '" required>';
    echo '<option value="">-- Select --</option>';
    foreach ($options as $o) {
        $selected = ((string)$o['id'] === (string)$value) ? ' selected' : '';
        echo '<option value="' . e($o['id']) . '"' . $selected . '>' . e($o['label']) . '</option>';
    }
    echo '</select>';
    field_footer($name, $errors, '');
}

// error text (red) or help text (grey) under a field, then close the column
function field_footer($name, $errors, $help) {
    if (isset($errors[$name])) {
        echo '<div class="invalid-feedback d-block">' . e($errors[$name]) . '</div>';
    } elseif ($help != '') {
        echo '<div class="form-text">' . e($help) . '</div>';
    }
    echo '</div>';
}

// ===== List page helpers: sorting, filters, pagination =====

// Build a link to the same page with some query values changed
function url_with($changes) {
    $params = $_GET;
    foreach ($changes as $key => $value) {
        $params[$key] = $value;
    }
    return '?' . http_build_query($params);
}

// Decide the sort column and direction from the URL (only allowed columns)
function get_sort($allowed, $default) {
    $sort = $default;
    if (isset($_GET['sort']) && isset($allowed[$_GET['sort']])) {
        $sort = $_GET['sort'];
    }
    $dir = 'desc';
    if (isset($_GET['sort']) && isset($_GET['dir']) && $_GET['dir'] == 'asc') {
        $dir = 'asc';
    }
    return array($sort, $dir);
}

// A clickable column heading
function sort_link($label, $key, $sort, $dir) {
    $new_dir = 'asc';
    $arrow = '';
    if ($sort == $key) {
        if ($dir == 'asc') { $new_dir = 'desc'; $arrow = ' &#9650;'; }
        else               { $arrow = ' &#9660;'; }
    }
    return '<a href="' . e(url_with(array('sort' => $key, 'dir' => $new_dir, 'page' => 1))) . '">' . e($label) . $arrow . '</a>';
}

// Dropdown filter in the search bar
function filter_select($name, $label, $options, $current) {
    echo '<div class="col-6 col-md-4 col-lg"><label class="form-label small mb-1">' . e($label) . '</label>';
    echo '<select class="form-select form-select-sm" name="' . $name . '"><option value="">All</option>';
    foreach ($options as $o) {
        $selected = ((string)$o['id'] === (string)$current) ? ' selected' : '';
        echo '<option value="' . e($o['id']) . '"' . $selected . '>' . e($o['label']) . '</option>';
    }
    echo '</select></div>';
}

// Page number links under a table
function show_pager($total, $page, $per_page) {
    $pages = (int)ceil($total / $per_page);
    if ($pages <= 1) {
        return;
    }
    echo '<nav><ul class="pagination pagination-sm mb-0 flex-wrap">';
    for ($i = 1; $i <= $pages; $i++) {
        $active = ($i == $page) ? ' active' : '';
        echo '<li class="page-item' . $active . '"><a class="page-link" href="' . e(url_with(array('page' => $i))) . '">' . $i . '</a></li>';
    }
    echo '</ul></nav>';
}

// "Showing 1-10 of 30" text
function showing_text($total, $page, $per_page) {
    $first = 0;
    if ($total > 0) { $first = ($page - 1) * $per_page + 1; }
    $last = min($total, $page * $per_page);
    return 'Showing ' . $first . '&ndash;' . $last . ' of ' . $total;
}

// Delete button for a table row (only administrators see it)
function delete_button($url, $name) {
    if (!is_admin()) {
        return '';
    }
    return '<form method="post" action="' . e($url) . '" class="d-inline" onsubmit="return confirm(\'Delete ' . e(addslashes($name)) . '? This cannot be undone.\');">'
         . csrf_field() . '<button class="btn btn-sm btn-outline-danger">Delete</button></form>';
}

// Read the page number from the URL
function get_page() {
    $page = 1;
    if (isset($_GET['page']) && ctype_digit($_GET['page']) && $_GET['page'] > 0) {
        $page = (int)$_GET['page'];
    }
    return $page;
}

// Read one value from a submitted form (empty text if the field was not sent)
function posted($name) {
    if (isset($_POST[$name])) {
        return trim($_POST[$name]);
    }
    return '';
}
