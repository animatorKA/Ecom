<?php
// Include required files
require_once __DIR__ . '/admin_functions.php';  // Include first for utility functions
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';

// Verify admin role
requireRole('admin');

// Set page info
$page_title = "Messages";
$page_icon = "bi-envelope";
$active_nav = "messages";
$page_subtitle = "Manage Customer Messages";

// Get messages from session
$success = getSuccessMessage();
$error = getErrorMessage();

// Handle marking messages as read/replied
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken()) {
        setErrorMessage("Invalid form submission, please try again.");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    try {
        if (isset($_POST['mark_read']) && !empty($_POST['message_id'])) {
            $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE message_id = ?");
        $stmt->execute([(int)$_POST['message_id']]);
    } elseif (isset($_POST['mark_replied']) && !empty($_POST['message_id'])) {
        $stmt = $pdo->prepare("UPDATE messages SET status = 'replied' WHERE message_id = ?");
        $stmt->execute([(int)$_POST['message_id']]);
    } elseif (isset($_POST['delete']) && !empty($_POST['message_id'])) {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE message_id = ?");
        $stmt->execute([(int)$_POST['message_id']]);
    }
}

// Get messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count for pagination
$total = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$total_pages = ceil($total / $limit);

// Get messages for current page
$messages = $pdo->prepare("
    SELECT * FROM messages 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$messages->execute([$limit, $offset]);
$messages = $messages->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <div class="col-lg-2 px-0">
            <div class="admin-sidebar">
                <div class="nav flex-column">
                    <a href="/pns_store/admin/dashboard.php" class="nav-link">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                    <a href="/pns_store/admin/products.php" class="nav-link">
                        <i class="bi bi-box"></i>
                        Products
                    </a>
                    <a href="/pns_store/admin/categories.php" class="nav-link">
                        <i class="bi bi-tags"></i>
                        Categories
                    </a>
                    <a href="/pns_store/admin/organizations.php" class="nav-link">
                        <i class="bi bi-buildings"></i>
                        Organizations
                    </a>
                    <a href="/pns_store/admin/messages.php" class="nav-link active">
                        <i class="bi bi-envelope"></i>
                        Messages
                    </a>
                    <a href="/pns_store/admin/orders.php" class="nav-link">
                        <i class="bi bi-cart"></i>
                        Orders
                    </a>
                    <a href="/pns_store/admin/users.php" class="nav-link">
                        <i class="bi bi-people"></i>
                        Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-10">
            <div class="admin-content">
                <!-- Page Header -->
                <div class="admin-page-header d-flex align-items-center justify-content-between">
                    <div>
                        <h2>Messages</h2>
                        <p>View and manage contact form messages</p>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb admin-breadcrumb">
                            <li class="breadcrumb-item"><a href="/pns_store/admin/dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Messages</li>
            </ol>
        </nav>
    </div>

    <!-- Messages Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">All Messages</h5>
                </div>
                <div class="col-auto">
                    <span class="badge bg-primary"><?= $total ?> Total Messages</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($messages)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">No messages found</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="fw-medium">Status</th>
                                <th class="fw-medium">From</th>
                                <th class="fw-medium">Subject</th>
                                <th class="fw-medium">Date</th>
                                <th class="fw-medium text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?php 
                                            echo match($message['status']) {
                                                'new' => 'bg-danger',
                                                'read' => 'bg-warning',
                                                'replied' => 'bg-success'
                                            };
                                        ?>">
                                            <?= ucfirst($message['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($message['name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($message['email']) ?></div>
                                    </td>
                                    <td class="text-break" style="max-width: 300px;">
                                        <?= htmlspecialchars($message['subject']) ?>
                                    </td>
                                    <td>
                                        <?= date('M j, Y g:i A', strtotime($message['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm float-end">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewMessageModal" 
                                                    data-message='<?= json_encode([
                                                        'id' => $message['message_id'],
                                                        'name' => $message['name'],
                                                        'email' => $message['email'],
                                                        'subject' => $message['subject'],
                                                        'message' => $message['message'],
                                                        'created_at' => date('M j, Y g:i A', strtotime($message['created_at'])),
                                                        'status' => $message['status']
                                                    ]) ?>'>
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <?php if ($message['status'] === 'new'): ?>
                                                <form action="" method="POST" class="d-inline" style="margin:0">
                                                    <input type="hidden" name="message_id" value="<?= $message['message_id'] ?>">
                                                    <button type="submit" name="mark_read" class="btn btn-outline-warning">
                                                        <i class="bi bi-check2"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($message['status'] === 'read'): ?>
                                                <form action="" method="POST" class="d-inline" style="margin:0">
                                                    <input type="hidden" name="message_id" value="<?= $message['message_id'] ?>">
                                                    <button type="submit" name="mark_replied" class="btn btn-outline-success">
                                                        <i class="bi bi-reply"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form action="" method="POST" class="d-inline" style="margin:0" 
                                                  onsubmit="return confirm('Are you sure you want to delete this message?')">
                                                <input type="hidden" name="message_id" value="<?= $message['message_id'] ?>">
                                                <button type="submit" name="delete" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white">
                        <nav aria-label="Messages pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Message Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold" style="font-size: 1.25rem;">Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Sender Information -->
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="bi bi-person fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1" id="modalName" style="font-size: 1.1rem;"></h6>
                        <p class="text-muted mb-0" id="modalEmail" style="font-size: 0.9rem;"></p>
                    </div>
                    <div class="ms-auto text-end">
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">Received</p>
                        <p class="mb-0 fw-medium" id="modalDate" style="font-size: 0.9rem;"></p>
                    </div>
                </div>

                <!-- Subject -->
                <div class="mb-4">
                    <label class="form-label text-muted mb-2" style="font-size: 0.875rem;">Subject</label>
                    <div id="modalSubject" class="fw-semibold" style="font-size: 1.1rem;"></div>
                </div>

                <!-- Message Content -->
                <div class="mb-4">
                    <label class="form-label text-muted mb-2" style="font-size: 0.875rem;">Message</label>
                    <div id="modalMessage" class="p-3 bg-light rounded" style="white-space: pre-wrap; min-height: 150px; font-size: 1rem; line-height: 1.6;"></div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Close</button>
                <a id="modalReplyBtn" href="#" class="btn btn-primary px-4" target="_blank">
                    <i class="bi bi-reply me-2"></i>Reply via Email
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewMessageModal = document.getElementById('viewMessageModal');
    if (viewMessageModal) {
        viewMessageModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const messageData = JSON.parse(button.getAttribute('data-message'));
            
            // Update modal content
            document.getElementById('modalName').textContent = messageData.name;
            document.getElementById('modalEmail').textContent = messageData.email;
            document.getElementById('modalSubject').textContent = messageData.subject;
            document.getElementById('modalMessage').textContent = messageData.message;
            document.getElementById('modalDate').textContent = messageData.created_at;
            
            // Update reply button
            document.getElementById('modalReplyBtn').href = `mailto:${messageData.email}?subject=Re: ${encodeURIComponent(messageData.subject)}`;

            // If message is new, mark it as read
            if (messageData.status === 'new') {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="message_id" value="${messageData.id}">
                    <input type="hidden" name="mark_read" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>