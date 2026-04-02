<?php
// common PHP utilities can be defined here. for example:
function ensure_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user'])) {
        header('Location: /BigBottleBOYS/login.php');
        exit;
    }
}

// Add other reusable PHP functions below as needed.
?>

<!-- shared JavaScript helper functions -->
<script>
// small-box profile image logic
(function () {
    const defaultPic = '../../recruitment/assets/images/user2-160x160.jpg';
    const img = document.getElementById('profilePicSmallBox');
    if (!img) return;
    const url = (window.currentUser && window.currentUser.profilePic) ||
                (window.CareerTrack && window.CareerTrack.profilePic) ||
                defaultPic;
    img.src = url;
})();
</script>
