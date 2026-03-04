<?php include('../../includes/layout.php'); include('../../includes/header.php'); include('../../includes/nav.php'); ?>
<style>
  /* ── Profile page custom styles ─────────────────────────────── */

  /* Banner card */
  .profile-banner {
    background: linear-gradient(135deg, #1a6fc4 0%, #0d47a1 100%);
    border-radius: .75rem;
    padding: 2rem 2rem 1.5rem;
    color: #fff;
    position: relative;
    overflow: hidden;
  }
  .profile-banner::after {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
  }

  /* Avatar */
  .profile-avatar-wrap {
    position: relative;
    display: inline-block;
    flex-shrink: 0;
  }
  .profile-avatar-wrap img {
    width: 110px;
    height: 110px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,.85);
    box-shadow: 0 4px 16px rgba(0,0,0,.25);
    display: block;
  }

  /* Info card shared styles */
  .profile-card {
    border: none;
    border-radius: .75rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
  }
  .profile-card .card-header {
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    border-radius: .75rem .75rem 0 0 !important;
    padding: .9rem 1.25rem;
  }
  .profile-card .card-header h5 {
    font-size: .95rem;
    font-weight: 600;
    color: #1a1a2e;
    letter-spacing: .01em;
  }
  .profile-card .card-body {
    padding: 1.1rem 1.25rem;
  }

  /* Two-column info rows */
  .info-row {
    display: flex;
    align-items: flex-start;
    padding: .5rem 0;
    border-bottom: 1px solid #f1f3f5;
  }
  .info-row:last-child { border-bottom: none; }
  .info-label {
    flex: 0 0 40%;
    max-width: 40%;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    padding-top: .1rem;
  }
  .info-value {
    flex: 1;
    font-size: .9rem;
    color: #212529;
    font-weight: 500;
    word-break: break-word;
  }
  .info-value:empty::before,
  .info-value[data-empty]::before { content: '—'; color: #adb5bd; }

  /* Edit button */
  .btn-edit {
    font-size: .78rem;
    padding: .3rem .8rem;
    border-radius: 20px;
    font-weight: 500;
  }

  /* Responsive */
  @media (max-width: 576px) {
    .profile-banner { padding: 1.25rem; }
    .profile-avatar-wrap img { width: 80px; height: 80px; }
    .info-label { flex: 0 0 45%; max-width: 45%; }
  }
</style>

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">My Profile</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <div class="container-fluid">
            <div class="row justify-content-center">
              <div class="col-xl-8 col-lg-10 col-12">

                <!-- ── Profile Banner ─────────────────────────── -->
                <div class="profile-banner mb-4 d-flex align-items-center gap-4">
                  <div class="profile-avatar-wrap">
                    <img
                      id="profilePicSmallBox"
                      src="../../recruitment/assets/images/user2-160x160.jpg"
                      alt="User Profile"
                    />
                  </div>
                  <div>
                    <h4 class="mb-0 fw-bold" id="bannerFullName">Gio Kay</h4>
                    <p class="mb-0 opacity-75" style="font-size:.85rem;">
                      <i class="bi bi-envelope me-1"></i><span id="bannerEmail">bigbottleboy@gmail.com</span>
                    </p>
                  </div>
                </div>

                <!-- ── User Information Card ──────────────────── -->
                <div class="card profile-card mb-4">
                  <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="bi bi-person-fill me-2 text-primary"></i>User Information</h5>
                    <button id="editBtn" class="btn btn-sm btn-outline-primary btn-edit">
                      <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                  </div>
                  <div class="card-body" id="userTable">
                    <div class="info-row">
                      <span class="info-label">Name</span>
                      <span id="nameCell" class="info-value user-field" data-field="name">Gio</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Surname</span>
                      <span id="surnameCell" class="info-value user-field" data-field="surname">Kay</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Email</span>
                      <span id="emailCell" class="info-value">bigbottleboy@gmail.com</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Address</span>
                      <span id="addressCell" class="info-value user-field" data-field="address">New Jersey, USA</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Phone</span>
                      <span id="phoneCell" class="info-value user-field" data-field="phone"></span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Date of Birth</span>
                      <span id="dobCell" class="info-value user-field" data-field="dob"></span>
                    </div>
                  </div>
                </div>

                <!-- ── Academic / Professional Card ──────────── -->
                <div class="card profile-card mb-4">
                  <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>Academic / Professional Information</h5>
                    <button id="editAcademicBtn" class="btn btn-sm btn-outline-primary btn-edit">
                      <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                  </div>
                  <div class="card-body" id="academicTable">
                    <div id="academicValidationAlert" class="alert alert-danger d-none py-2 mb-3" role="alert"></div>
                    <div class="info-row">
                      <span class="info-label">Highest Degree</span>
                      <span id="degreeCell" class="info-value academic-field" data-field="degree">—</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Institution</span>
                      <span id="institutionCell" class="info-value academic-field" data-field="institution">—</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Specialization</span>
                      <span id="specializationCell" class="info-value academic-field" data-field="specialization">—</span>
                    </div>
                    <div class="info-row">
                      <span class="info-label">Experience</span>
                      <span id="experienceCell" class="info-value academic-field" data-field="experience">—</span>
                    </div>
                    <div class="info-row" style="align-items:flex-start;">
                      <span class="info-label" style="padding-top:.15rem;">Summary</span>
                      <span id="summaryCell" class="info-value academic-field" data-field="summary" style="white-space:pre-wrap;">—</span>
                    </div>
                  </div>
                </div>

              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.container-fluid -->
        </div>
        <!--end::App Content-->

    <script>
      const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

      function formatDob(value) {
        if (!value) return '';
        const parts = value.split('-');
        if (parts.length !== 3) return value;
        const [y, m, d] = parts;
        const monthName = MONTHS[parseInt(m, 10) - 1] || m;
        return `${monthName} ${parseInt(d, 10)}, ${y}`;
      }

      function dobSelectsHTML(stored) {
        let selDay = '', selMonth = '', selYear = '';
        if (stored) {
          const p = stored.split('-');
          if (p.length === 3) { selYear = p[0]; selMonth = String(parseInt(p[1],10)); selDay = String(parseInt(p[2],10)); }
        }
        let monthOpts = '<option value="">Month</option>';
        MONTHS.forEach((mn, i) => {
          const v = i + 1;
          monthOpts += `<option value="${v}"${selMonth === String(v) ? ' selected' : ''}>${mn}</option>`;
        });
        let dayOpts = '<option value="">Day</option>';
        for (let d = 1; d <= 31; d++) dayOpts += `<option value="${d}"${selDay === String(d) ? ' selected' : ''}>${d}</option>`;
        const curYear = new Date().getFullYear();
        let yearOpts = '<option value="">Year</option>';
        for (let y = curYear; y >= 1900; y--) yearOpts += `<option value="${y}"${selYear === String(y) ? ' selected' : ''}>${y}</option>`;
        return `<div class="d-flex gap-1" style="max-width:340px;">
          <select class="form-select form-select-sm dob-month" style="width:120px;">${monthOpts}</select>
          <select class="form-select form-select-sm dob-day" style="width:80px;">${dayOpts}</select>
          <select class="form-select form-select-sm dob-year" style="width:90px;">${yearOpts}</select>
        </div>`;
      }

      function getUserData() {
        const stored = localStorage.getItem('userProfileData');
        if (stored) return JSON.parse(stored);
        return { name:'Alexander', surname:'Pierce', email:'alexander@example.com', address:'123 Main Street', phone:'', dob:'', degree:'', institution:'', specialization:'', experience:'', summary:'' };
      }

      function saveUserData(data) {
        localStorage.setItem('userProfileData', JSON.stringify(data));
      }

      (function initPage() {
        const userData = getUserData();
        document.getElementById('nameCell').textContent = userData.name;
        document.getElementById('surnameCell').textContent = userData.surname;
        document.getElementById('emailCell').textContent = userData.email;
        document.getElementById('addressCell').textContent = userData.address;
        document.getElementById('phoneCell').textContent = userData.phone || '';
        document.getElementById('dobCell').textContent = formatDob(userData.dob);
        // update banner
        const banner = document.getElementById('bannerFullName');
        if (banner) banner.textContent = `${userData.name} ${userData.surname}`;
        const bannerEmail = document.getElementById('bannerEmail');
        if (bannerEmail) bannerEmail.textContent = userData.email;
        const acad = [
          ['degreeCell', userData.degree],
          ['institutionCell', userData.institution],
          ['specializationCell', userData.specialization],
          ['experienceCell', userData.experience !== '' ? userData.experience + (userData.experience === '1' ? ' year' : ' years') : ''],
          ['summaryCell', userData.summary],
        ];
        acad.forEach(([id, val]) => { document.getElementById(id).textContent = val || '\u2014'; });
        const navbarUserName = document.getElementById('navbarUserName');
        if (navbarUserName) navbarUserName.textContent = `${userData.name} ${userData.surname}`;
        const userHeaderP = document.querySelector('.user-header p');
        if (userHeaderP) {
          const subtitle = userHeaderP.querySelector('small');
          const subText = subtitle ? `<small>${subtitle.textContent}</small>` : '';
          userHeaderP.innerHTML = `${userData.name} ${userData.surname} - Web Developer${subText}`;
        }
      })();

      (function () {
        const defaultPic = '../../recruitment/assets/images/user2-160x160.jpg';
        const img = document.getElementById('profilePicSmallBox');
        if (!img) return;
        const url = (window.currentUser && window.currentUser.profilePic) || (window.CareerTrack && window.CareerTrack.profilePic) || defaultPic;
        img.src = url;
      })();

      const editBtn = document.getElementById('editBtn');
      const userFields = document.querySelectorAll('.user-field');
      let isEditMode = false;

      editBtn.addEventListener('click', function() {
        if (!isEditMode) {
          isEditMode = true;
          userFields.forEach(field => {
            const fieldName = field.getAttribute('data-field');
            if (fieldName === 'dob') {
              field.innerHTML = dobSelectsHTML(getUserData().dob);
            } else {
              const originalText = field.textContent;
              const inputType = fieldName === 'phone' ? 'tel' : 'text';
              field.innerHTML = `<input type="${inputType}" class="form-control form-control-sm" value="${originalText}" style="max-width:300px;">`;
            }
          });
          editBtn.textContent = 'Save';
          editBtn.classList.replace('btn-primary', 'btn-success');
        } else {
          const nameInput    = document.querySelector('#nameCell input');
          const surnameInput = document.querySelector('#surnameCell input');
          const addressInput = document.querySelector('#addressCell input');
          const phoneInput   = document.querySelector('#phoneCell input');
          const dobMonth = document.querySelector('#dobCell .dob-month');
          const dobDay   = document.querySelector('#dobCell .dob-day');
          const dobYear  = document.querySelector('#dobCell .dob-year');
          let newName = nameInput ? nameInput.value : 'Alexander';
          let newSurname = surnameInput ? surnameInput.value : 'Pierce';
          let newAddress = addressInput ? addressInput.value : '123 Main Street';
          let newPhone = phoneInput ? phoneInput.value : '';
          let newDob = '';
          if (dobMonth && dobDay && dobYear && dobMonth.value && dobDay.value && dobYear.value) {
            const m = String(parseInt(dobMonth.value,10)).padStart(2,'0');
            const d = String(parseInt(dobDay.value,10)).padStart(2,'0');
            newDob = `${dobYear.value}-${m}-${d}`;
          } else {
            newDob = getUserData().dob || '';
          }
          userFields.forEach(field => {
            const fieldName = field.getAttribute('data-field');
            if (fieldName === 'dob') { field.textContent = formatDob(newDob); }
            else { const input = field.querySelector('input'); if (input) field.textContent = input.value; }
          });
          const userData = getUserData();
          userData.name = newName; userData.surname = newSurname; userData.address = newAddress;
          userData.phone = newPhone; userData.dob = newDob;
          saveUserData(userData);
          const newFullName = `${newName} ${newSurname}`;
          const navbarUserName = document.getElementById('navbarUserName');
          if (navbarUserName) navbarUserName.textContent = newFullName;
          const bannerFullName = document.getElementById('bannerFullName');
          if (bannerFullName) bannerFullName.textContent = newFullName;
          const bannerEmailEl = document.getElementById('bannerEmail');
          if (bannerEmailEl) bannerEmailEl.textContent = userData.email;
          const userHeaderP = document.querySelector('.user-header p');
          if (userHeaderP) {
            const subtitle = userHeaderP.querySelector('small');
            const subText = subtitle ? `<small>${subtitle.textContent}</small>` : '';
            userHeaderP.innerHTML = `${newFullName} - Web Developer${subText}`;
          }
          isEditMode = false;
          editBtn.textContent = 'Edit';
          editBtn.classList.replace('btn-success', 'btn-primary');
        }
      });

      const DEGREE_OPTIONS = ['', 'High School Diploma', "Associate's Degree", "Bachelor's Degree", "Master's Degree", 'Doctoral Degree (PhD)', 'Professional Degree (MD / JD / etc.)', 'Other'];
      const editAcademicBtn = document.getElementById('editAcademicBtn');
      const academicFields  = document.querySelectorAll('.academic-field');
      let isAcademicEditMode = false;

      editAcademicBtn.addEventListener('click', function () {
        const alertBox = document.getElementById('academicValidationAlert');
        alertBox.classList.add('d-none'); alertBox.textContent = '';
        if (!isAcademicEditMode) {
          isAcademicEditMode = true;
          const acadData = getUserData();
          academicFields.forEach(field => {
            const f = field.getAttribute('data-field');
            if (f === 'degree') {
              let opts = DEGREE_OPTIONS.map(o => `<option value="${o}"${acadData.degree === o ? ' selected' : ''}>${o || '\u2014 Select degree \u2014'}</option>`).join('');
              field.innerHTML = `<select class="form-select form-select-sm" style="max-width:300px;" id="degreeSelect">${opts}</select>`;
            } else if (f === 'experience') {
              field.innerHTML = `<input type="number" id="experienceInput" min="0" max="60" class="form-control form-control-sm" style="max-width:120px;" value="${acadData.experience || ''}" placeholder="0">`;
            } else if (f === 'summary') {
              field.innerHTML = `<textarea id="summaryInput" class="form-control form-control-sm" rows="4" style="max-width:500px;" placeholder="Write a short professional bio\u2026">${acadData.summary || ''}</textarea>`;
            } else {
              field.innerHTML = `<input type="text" class="form-control form-control-sm" value="${acadData[f] || ''}" style="max-width:300px;" placeholder="Enter ${f}">`;
            }
          });
          editAcademicBtn.textContent = 'Save';
          editAcademicBtn.classList.replace('btn-primary', 'btn-success');
        } else {
          const expInput = document.getElementById('experienceInput');
          if (expInput) {
            const expVal = expInput.value.trim();
            if (expVal !== '' && (isNaN(expVal) || Number(expVal) < 0 || !Number.isInteger(Number(expVal)))) {
              alertBox.textContent = 'Years of Professional Experience must be a non-negative whole number.';
              alertBox.classList.remove('d-none');
              expInput.classList.add('is-invalid');
              return;
            }
            expInput.classList.remove('is-invalid');
          }
          const degreeEl  = document.getElementById('degreeSelect');
          const instInput = document.querySelector('#institutionCell input');
          const specInput = document.querySelector('#specializationCell input');
          const sumInput  = document.getElementById('summaryInput');
          const newDegree  = degreeEl  ? degreeEl.value  : '';
          const newInst    = instInput ? instInput.value : '';
          const newSpec    = specInput ? specInput.value : '';
          const newExp     = expInput  ? expInput.value.trim() : '';
          const newSummary = sumInput  ? sumInput.value  : '';
          document.getElementById('degreeCell').textContent         = newDegree  || '\u2014';
          document.getElementById('institutionCell').textContent    = newInst    || '\u2014';
          document.getElementById('specializationCell').textContent = newSpec    || '\u2014';
          document.getElementById('experienceCell').textContent     = newExp !== '' ? newExp + (newExp === '1' ? ' year' : ' years') : '\u2014';
          document.getElementById('summaryCell').textContent        = newSummary || '\u2014';
          const userData = getUserData();
          userData.degree = newDegree; userData.institution = newInst;
          userData.specialization = newSpec; userData.experience = newExp; userData.summary = newSummary;
          saveUserData(userData);
          isAcademicEditMode = false;
          editAcademicBtn.textContent = 'Edit';
          editAcademicBtn.classList.replace('btn-success', 'btn-primary');
        }
      });
    </script>

<?php include('../../includes/footer.php'); ?>
