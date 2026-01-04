<?php
admin_required();

if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id'])) {
    $id_transaksi = intval($_GET['id']);
    $status = $_GET['new_status'];
    
    // Validasi status
    $valid_statuses = ['lunas', 'pending', 'terlambat'];
    
    if (in_array($status, $valid_statuses)) {
        $query = "UPDATE transaksi SET status_pembayaran = ? WHERE id_transaksi = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $id_transaksi);
        
        if ($stmt->execute()) {
            header("Location: ?page=admin_transaksi&success=Status pembayaran berhasil diubah");
            exit();
        }
    }
}

header("Location: ?page=admin_transaksi");
exit();
?>
