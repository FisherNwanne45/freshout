<?php  //Start the Session

session_start();

 $connection = $GLOBALS['connection'] ?? null;
 require_once 'connectdb.php';
 require_once 'class.admin.php';

if (!($connection instanceof mysqli)) {
  $connection = $GLOBALS['connection'] ?? null;
}
//3. If the form is submitted or not.
//3.1 If the form is submitted
if (isset($_POST['uname']) and isset($_POST['upass'])){
//3.1.1 Assigning posted values to variables.
$login = trim((string)$_POST['uname']);
$upass = (string)$_POST['upass'];
$legacyMd5 = md5($upass);
//3.1.2 Checking the values are existing in the database or not
$stmt = $connection->prepare("SELECT * FROM admin WHERE (uname=? OR email=?) LIMIT 1");
$stmt->bind_param("ss", $login, $login);
$stmt->execute();
$result = $stmt->get_result();
$count = 0;
//3.1.2 If the posted values are equal to the database values, then session will be created for the user.
if ($result && $result->num_rows === 1){
$row = $result->fetch_assoc();
$storedHash = (string)($row['upass'] ?? '');
$passwordOk = false;

if ($storedHash !== '' && password_verify($upass, $storedHash)) {
  $passwordOk = true;
} elseif ($storedHash !== '' && hash_equals($legacyMd5, $storedHash)) {
  $passwordOk = true;
  // Upgrade legacy MD5 hash to bcrypt on successful login.
  try {
    $rehash = password_hash($upass, PASSWORD_BCRYPT);
    if ($rehash !== '') {
      $up = $connection->prepare("UPDATE admin SET upass = ? WHERE id = ? LIMIT 1");
      if ($up) {
        $id = (int)($row['id'] ?? 0);
        $up->bind_param("si", $rehash, $id);
        $up->execute();
      }
    }
  } catch (Throwable $e) {}
}

if ($passwordOk) {
  session_regenerate_id(true);
  $_SESSION['uname'] = $row['uname'];
  $_SESSION['email'] = $row['email'];
} else {
  $msg = "<div class='alert alert-danger'>
						<button class='close' data-dismiss='alert'>&times;</button>
						  Invalid Email or Password!
                   
			  </div>";
}
}else{
//3.1.3 If the login credentials doesn't match, he will be shown with an error message.
$msg = "<div class='alert alert-danger'>
						<button class='close' data-dismiss='alert'>&times;</button>
						  Invalid Email or Password!
                   
			  </div>";
}
}
//3.1.4 if the user is logged in Greets the user with message
if (isset($_SESSION['uname'])){
$uname = $_SESSION['uname'];
header('Location: index.php');
 
}else{}
//3.2 When the user visits the page first time, simple login form will be displayed.
include_once dirname(__DIR__, 2) . '/private/shared-favicon-url.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <?php if (!empty($sharedFaviconUrl)): ?>
  <link rel="icon" href="<?= htmlspecialchars($sharedFaviconUrl) ?>" type="image/png">
  <?php endif; ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="inline-flex w-16 h-16 rounded-2xl bg-blue-600 items-center justify-center mb-4">
        <?php if (!empty($sharedFaviconUrl)): ?>
        <img src="<?= htmlspecialchars($sharedFaviconUrl) ?>" alt="Site icon" class="w-9 h-9 rounded-md object-contain">
        <?php endif; ?>
      </div>
      <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
      <p class="text-slate-400 text-sm mt-1">Sign in to manage the system</p>
    </div>
    <div class="bg-white rounded-2xl shadow-2xl p-8">
      <?php if(isset($msg)) echo $msg; ?>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Username or Email</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
              <i class="fa-solid fa-user text-sm"></i>
            </span>
            <input type="text" name="uname" autofocus required
              class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="admin or admin@example.com">
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
              <i class="fa-solid fa-lock text-sm"></i>
            </span>
            <input type="password" name="upass" required
              class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Password">
          </div>
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm mt-2">
          Sign In
        </button>
      </form>
    </div>
  </div>
</body>
</html>
