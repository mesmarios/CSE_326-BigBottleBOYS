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
// read/write profile data from localStorage
function getUserData() {
    const stored = localStorage.getItem('userProfileData');
    if (stored) {
        try {
            return JSON.parse(stored);
        } catch (e) {
            console.error('Invalid userProfileData JSON:', e);
        }
    }
    return {
        name: 'Alexander',
        surname: 'Pierce',
        email: 'alexander@example.com',
        address: '123 Main Street'
    };
}

function saveUserData(data) {
    localStorage.setItem('userProfileData', JSON.stringify(data));
}

(function initPage() {
    // update navbar and dropdown with whatever data is available
    const userData = getUserData();
    const navbarUserName = document.getElementById('navbarUserName');
    if (navbarUserName) {
        navbarUserName.textContent = `${userData.name} ${userData.surname}`;
    }
    const userHeaderP = document.querySelector('.user-header p');
    if (userHeaderP) {
        const subtitle = userHeaderP.querySelector('small');
        const subText = subtitle ? `<small>${subtitle.textContent}</small>` : '';
        userHeaderP.innerHTML = `${userData.name} ${userData.surname} - Web Developer${subText}`;
    }
})();

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

// generic edit button handler (profile page only)
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editBtn');
    if (!editBtn) return;
    const userFields = document.querySelectorAll('.user-field');
    let isEditMode = false;
    editBtn.addEventListener('click', function() {
        if (!isEditMode) {
            isEditMode = true;
            editBtn.textContent = 'Save';
            userFields.forEach(elem => {
                elem.setAttribute('contenteditable', 'true');
                elem.classList.add('border', 'border-1', 'border-secondary');
            });
        } else {
            isEditMode = false;
            editBtn.textContent = 'Edit';
            userFields.forEach(elem => {
                elem.removeAttribute('contenteditable');
                elem.classList.remove('border', 'border-1', 'border-secondary');
            });
            const data = {
                name: document.getElementById('nameCell').textContent,
                surname: document.getElementById('surnameCell').textContent,
                email: document.getElementById('emailCell').textContent,
                address: document.getElementById('addressCell').textContent
            };
            saveUserData(data);
        }
    });
});
</script>