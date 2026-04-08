  <?php
session_start();
require_once 'class.admin.php';
include_once ('session.php');
if(!isset($_SESSION['email'])){
	
header("Location: login.php");

exit(); 
}

$reg_user = new USER();

if(isset($_GET['id'])){
	
$id=$_GET['id'];
$stmt = $reg_user->runQuery("SELECT * FROM alerts WHERE id='$id'");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
}
if(isset($_POST['delete']))
{

	if($reg_user->del($id))
			{			
			$id=$_GET['id'];
			$deleteuser = $reg_user->runQuery("DELETE FROM alerts WHERE id = '$id'");
			$deleteuser->execute();
			
			
					 header("Location: credit_debit_list.php?success");
			}
			else {
				
					  header("Location: credit_debit_list.php?error");
			}
		
	}
    

                    $pageTitle = 'Delete Uploaded Images';
                    require_once __DIR__ . '/partials/admin-shell-open.php';
                    ?>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-5xl">
                      <h2 class="text-lg font-semibold text-gray-800 mb-1">Delete Uploaded Images</h2>
                      <p class="text-sm text-gray-500 mb-5">Manage files currently stored in the photo directory.</p>

                      <?php
                      if (array_key_exists('delete_file', $_POST)) {
                          $filename = $_POST['delete_file'];
                          if (file_exists($filename)) {
                              unlink($filename);
                              echo '<div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-green-700 text-sm mb-4">File ' . htmlspecialchars($filename) . ' has been deleted.</div>';
                          } else {
                              echo '<div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-red-700 text-sm mb-4">Could not delete ' . htmlspecialchars($filename) . '; file does not exist.</div>';
                          }
                      }

                      $files = glob('foto/*');
                      ?>

                      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <?php foreach ($files as $file): ?>
                          <div class="border border-gray-200 rounded-lg p-3">
                            <img src="<?= htmlspecialchars($file) ?>" alt="Uploaded" class="w-full h-28 object-cover rounded-md mb-3">
                            <form method="post" onsubmit="return confirm('Delete this image file?');">
                              <input type="hidden" value="<?= htmlspecialchars($file) ?>" name="delete_file">
                              <button type="submit" class="w-full inline-flex items-center justify-center gap-1 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-medium px-2 py-1.5 rounded-lg border border-red-200 transition-colors">
                                <i class="fa-solid fa-trash"></i> Delete
                              </button>
                            </form>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>

                    <?php require_once __DIR__ . '/partials/admin-shell-close.php'; ?>
