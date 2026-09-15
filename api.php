<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Worker.php';
require_once __DIR__ . '/models/Payment.php';
require_once __DIR__ . '/models/Setting.php';
require_once __DIR__ . '/models/LotTask.php';

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    echo json_encode(["success" => false, "message" => "Error de conexion a la base de datos"]);
    exit();
}

$action = $_GET['action'] ?? '';

// AUTH
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->username) && !empty($data->password)) {
        $user = new User($db);
        if ($user->login($data->username, $data->password)) {
            echo json_encode(["success" => true, "user_id" => $user->id, "username" => $user->username]);
        } else {
            echo json_encode(["success" => false, "message" => "Credenciales incorrectas"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    }
    exit();
}

// WORKERS
if ($action === 'workers_list') {
    $w = new Worker($db);
    $stmt = $w->readAll($_GET['role'] ?? null);
    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}
if ($action === 'workers_roles') {
    $w = new Worker($db);
    echo json_encode(["success" => true, "data" => $w->getUniqueRoles()]);
    exit();
}
if ($action === 'worker_get' && isset($_GET['id'])) {
    $w = new Worker($db); $w->id = $_GET['id'];
    if ($w->readOne()) {
        echo json_encode(["success" => true, "data" => ["id"=>$w->id,"full_name"=>$w->full_name,"id_number"=>$w->id_number,"address"=>$w->address,"position"=>$w->position,"lote"=>$w->lote,"status"=>$w->status]]);
    } else { echo json_encode(["success" => false, "message" => "No encontrado"]); }
    exit();
}
if ($action === 'worker_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $w = new Worker($db);
    $w->full_name=$data->full_name; $w->id_number=$data->id_number;
    $w->address=$data->address??''; $w->position=$data->position;
    $w->lote=$data->lote??''; $w->status='activo';
    try {
        echo json_encode(["success" => $w->create()]);
    } catch(PDOException $e) {
        echo json_encode(["success"=>false,"message"=>$e->getCode()==23000?"Cedula duplicada":$e->getMessage()]);
    }
    exit();
}
if ($action === 'worker_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $w = new Worker($db);
    $w->id=$data->id; $w->full_name=$data->full_name; $w->id_number=$data->id_number;
    $w->address=$data->address??''; $w->position=$data->position;
    $w->lote=$data->lote??''; $w->status=$data->status??'activo';
    try { echo json_encode(["success" => $w->update()]); }
    catch(PDOException $e) { echo json_encode(["success"=>false,"message"=>$e->getMessage()]); }
    exit();
}
if ($action === 'worker_delete' && isset($_GET['id'])) {
    $w = new Worker($db);
    echo json_encode(["success" => $w->delete($_GET['id'])]);
    exit();
}

// PAYMENTS
if ($action === 'payments_list') {
    $p = new Payment($db);
    $stmt = $p->readAllGlobal($_GET['date']??null, $_GET['role']??null);
    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}
if ($action === 'payments_by_worker' && isset($_GET['worker_id'])) {
    $p = new Payment($db);
    $stmt = $p->readByWorker($_GET['worker_id']);
    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}
if ($action === 'payment_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $p = new Payment($db);
    $p->worker_id=$data->worker_id; $p->payment_date=$data->payment_date;
    $p->week_worked=$data->week_worked; $p->days_worked=$data->days_worked;
    $p->amount_earned=$data->amount_earned; $p->amount_paid=$data->amount_paid;
    echo json_encode(["success" => $p->create()]);
    exit();
}
if ($action === 'payment_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $p = new Payment($db);
    $p->id=$data->id; $p->payment_date=$data->payment_date; $p->week_worked=$data->week_worked;
    $p->days_worked=$data->days_worked; $p->amount_earned=$data->amount_earned; $p->amount_paid=$data->amount_paid;
    echo json_encode(["success" => $p->update()]);
    exit();
}
if ($action === 'payment_delete' && isset($_GET['id'])) {
    $p = new Payment($db);
    echo json_encode(["success" => $p->delete($_GET['id'])]);
    exit();
}

// DASHBOARD
if ($action === 'dashboard_stats') {
    $p = new Payment($db); $w = new Worker($db);
    echo json_encode(["success"=>true,"data"=>[
        "expenses_by_month" => $p->getExpensesByMonth()->fetchAll(PDO::FETCH_ASSOC),
        "expenses_by_role"  => $p->getExpensesByRole()->fetchAll(PDO::FETCH_ASSOC),
        "monthly_summary"   => $p->getMonthlySummary()->fetchAll(PDO::FETCH_ASSOC),
        "workers"           => $w->readAll()->fetchAll(PDO::FETCH_ASSOC),
    ]]);
    exit();
}

// SETTINGS
if ($action === 'settings_get') {
    $s = new Setting($db); $s->read();
    echo json_encode(["success"=>true,"data"=>["id"=>$s->id,"admin_name"=>$s->admin_name,"admin_email"=>$s->admin_email,"daily_rate"=>$s->daily_rate,"currency"=>$s->currency]]);
    exit();
}
if ($action === 'settings_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $s = new Setting($db);
    $s->id=$data->id; $s->admin_name=$data->admin_name; $s->admin_email=$data->admin_email;
    $s->daily_rate=$data->daily_rate; $s->currency=$data->currency;
    echo json_encode(["success" => $s->update()]);
    exit();
}

// LOT TASKS
if ($action === 'lottasks_list') {
    $lt = new LotTask($db);
    echo json_encode(["success"=>true,"data"=>$lt->readAll()->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}
if ($action === 'lottask_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $lt = new LotTask($db);
    $lt->date_task=$data->date_task; $lt->lot_name=$data->lot_name;
    $lt->task_name=$data->task_name; $lt->workers_desc=$data->workers_desc;
    $lt->calculated_value=$data->calculated_value; $lt->original_value_notes=$data->original_value_notes??'';
    echo json_encode(["success" => $lt->create()]);
    exit();
}
if ($action === 'lottask_delete' && isset($_GET['id'])) {
    $lt = new LotTask($db); $lt->id = $_GET['id'];
    echo json_encode(["success" => $lt->delete()]);
    exit();
}

echo json_encode(["success" => false, "message" => "Accion no encontrada"]);
