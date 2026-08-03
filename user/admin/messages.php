<?php
session_start();
require_once 'class.admin.php';
include_once ('session.php');
if(!isset($_SESSION['email'])){
	
header("Location: login.php");

exit(); 
}
$reg_user = new USER();

// Safe message deletion — operates only on the message table
if (isset($_POST['delete_message_id'])) {
    $del_id = (int)$_POST['delete_message_id'];
    if ($del_id > 0) {
        $del_stmt = $reg_user->runQuery("DELETE FROM message WHERE id = :id");
        $del_stmt->bindParam(':id', $del_id, PDO::PARAM_INT);
        $del_stmt->execute();
    }
    header('Location: messages.php');
    exit();
}

if(isset($_POST['message']))
{
	$sender_name = trim($_POST['sender_name']);
	$sender_name = htmlspecialchars($sender_name);
	
	$reci_name = trim($_POST['reci_name']);
	$reci_name = strip_tags($reci_name);
	$reci_name = htmlspecialchars($reci_name);
	
	$subject = trim($_POST['subject']);
	$subject = strip_tags($subject);
	$subject = htmlspecialchars($subject);
	
	$msg = trim($_POST['msg']);
	$msg = strip_tags($msg);
	$msg = htmlspecialchars($msg);
	
	
	
	
		if($reg_user->message($sender_name,$reci_name,$subject,$msg))
		{			
			$id = $reg_user->lasdID();	
			
			$msg = "
					<div class='alert alert-success'>
						<button class='close' data-dismiss='alert'>&times;</button>
						<strong>Sent!</strong>.
                     
			  		</div>
					";
		}
		else
		{
			echo "Sorry, Message was not sent";
		}		
}
$account = $reg_user->runQuery("SELECT * FROM account");
$account->execute();
$stmt = $reg_user->runQuery("SELECT * FROM message ORDER BY id DESC LIMIT 200");
$stmt->execute();

$pageTitle = 'Messages';
require_once __DIR__ . '/partials/admin-shell-open.php';
?>

<?php if(isset($msg)) echo $msg; ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- Send message -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Send Message</h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Recipient Account No</label>
        <input type="text" name="reci_name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Account number" required>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Subject</label>
        <input type="text" name="subject" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Message</label>
        <textarea name="message" rows="5" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
      </div>
      <button type="submit" name="send" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors cursor-pointer w-full justify-center"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
    </form>
  </div>

  <!-- Messages list -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:col-span-2">
    <h2 class="font-semibold text-gray-800 mb-4">Sent Messages</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border-collapse">
        <thead>
          <tr class="bg-gray-50 border-y border-gray-200">
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">#</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">To</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Subject</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Date</th>
            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php $n=0; while($row = $stmt->fetch(PDO::FETCH_ASSOC)): $n++; ?>
          <tr class="hover:bg-gray-50">
            <td class="px-3 py-3 text-sm text-gray-700 text-gray-400"><?= $n ?></td>
            <td class="px-3 py-3 text-sm text-gray-700 font-mono text-xs"><?= htmlspecialchars($row['reci_name']) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700"><?= htmlspecialchars($row['subject']) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700 text-xs text-gray-500"><?= htmlspecialchars($row['date']) ?></td>
            <td class="px-3 py-3 text-sm text-gray-700">
              <form method="POST" onsubmit="return confirm('Delete this message permanently?')" style="display:inline;">
                <input type="hidden" name="delete_message_id" value="<?= (int)$row['id'] ?>">
                <button type="submit" class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-2 py-1 rounded-lg transition-colors cursor-pointer"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
